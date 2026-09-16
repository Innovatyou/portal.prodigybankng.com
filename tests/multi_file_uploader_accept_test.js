// node tests/multi_file_uploader_accept_test.js -- no network or database access.
//
// Regression test for the Dropzone accept() callback in
// app/Views/includes/multi_file_uploader.php (the shared drag-and-drop
// upload widget used by File Manager and every other file upload modal in
// the app). Two bugs made files get permanently stuck as "uploading" with
// no feedback:
//   1. The over-long-filename branch called done() but didn't return, so
//      execution fell through and fired the validation request too,
//      calling done() a second time for the same file.
//   2. The validation ajax call had no error handler and no timeout, so
//      any failed/timed-out request left done() never called - the file
//      just sat there forever looking like it was "not uploading".
const fs = require('fs');
const vm = require('vm');
const assert = require('assert');

const source = fs.readFileSync('app/Views/includes/multi_file_uploader.php', 'utf8');

const startMarker = 'accept: function(file, done) {';
const start = source.indexOf(startMarker);
assert(start !== -1, 'accept callback not found');

const braceStart = source.indexOf('{', start);
let depth = 1;
let j = braceStart + 1;
while (depth > 0) {
    if (source[j] === '{') depth++;
    else if (source[j] === '}') depth--;
    j++;
}
// the validation url itself is server-rendered (e.g. url: "<?php echo $validation_url; ?>") -
// stub it out to a plain string; everything else in this callback is plain JS
const body = source.slice(braceStart + 1, j - 1).replace(/<\?php[\s\S]*?\?>/g, 'validate-url-stub');
assert(!/<\?php/.test(body), 'accept callback must be plain JS (aside from the stubbed validation url) with no other embedded PHP');

vm.runInNewContext('stub_result = (function(file, done) {' + body + '});', globalThis, { filename: 'accept.js' });

function makeFakePreview() {
    const state = { removedInputs: false, removedDescription: false, attrs: {}, appended: [], fileCountVal: null };
    return {
        state,
        find(selector) {
            if (selector === 'input') {
                return { remove() { state.removedInputs = true; } };
            }
            if (selector === '.description-field') {
                return {
                    remove() { state.removedDescription = true; },
                    attr(name, value) { state.attrs[name] = value; }
                };
            }
            if (selector === '.file-count-field') {
                return { val(v) { state.fileCountVal = v; } };
            }
            throw new Error('unexpected selector ' + selector);
        },
        append(html) { state.appended.push(html); }
    };
}

let capturedAjaxOptions = null;
global.$ = (el) => el;
global.appAjaxRequest = (options) => { capturedAjaxOptions = options; };
global.AppLanguage = { somethingWentWrong: 'Something went wrong' };
global.fileSerial = 0;

const accept = global.stub_result;

// Scenario 1: over-long filename must reject once and never fire a validation request
{
    const doneArgs = [];
    const preview = makeFakePreview();
    const file = { name: 'a'.repeat(201), previewTemplate: preview };
    capturedAjaxOptions = null;
    global.fileSerial = 0;
    accept(file, (msg) => doneArgs.push(msg));
    assert.strictEqual(doneArgs.length, 1, 'done must be called exactly once for an over-long filename');
    assert.strictEqual(doneArgs[0], 'Filename is too long.');
    assert.strictEqual(capturedAjaxOptions, null, 'must not fire a validation request for a rejected filename');
    assert.strictEqual(preview.state.removedDescription, true);
}

// Scenario 2: normal validation success
{
    const doneArgs = [];
    const preview = makeFakePreview();
    const file = { name: 'ok.pdf', size: 100, previewTemplate: preview };
    capturedAjaxOptions = null;
    global.fileSerial = 0;
    accept(file, (msg) => doneArgs.push(msg));
    assert(capturedAjaxOptions, 'validation request must be sent for a normal filename');
    assert.strictEqual(capturedAjaxOptions.timeout, 30000, 'validation request must have a timeout so it cannot hang forever');
    capturedAjaxOptions.success({ success: true });
    assert.deepStrictEqual(doneArgs, [undefined]);
    assert.strictEqual(preview.state.fileCountVal, 1);
}

// Scenario 3: server rejects the file (e.g. disallowed extension)
{
    const doneArgs = [];
    const preview = makeFakePreview();
    const file = { name: 'bad.exe', size: 100, previewTemplate: preview };
    capturedAjaxOptions = null;
    global.fileSerial = 0;
    accept(file, (msg) => doneArgs.push(msg));
    capturedAjaxOptions.success({ success: false, message: 'Invalid file type' });
    assert.deepStrictEqual(doneArgs, ['Invalid file type']);
    assert.strictEqual(preview.state.removedInputs, true);
}

// Scenario 4 (the actual reported bug): validation request errors out or times out
{
    const doneArgs = [];
    const preview = makeFakePreview();
    const file = { name: 'ok2.pdf', size: 100, previewTemplate: preview };
    capturedAjaxOptions = null;
    global.fileSerial = 0;
    accept(file, (msg) => doneArgs.push(msg));
    assert.strictEqual(typeof capturedAjaxOptions.error, 'function', 'validation request must handle ajax errors/timeouts');
    capturedAjaxOptions.error();
    assert.strictEqual(doneArgs.length, 1, 'a failed validation request must still resolve done() instead of leaving the file stuck forever');
    assert.strictEqual(doneArgs[0], 'Something went wrong');
    assert.strictEqual(preview.state.removedInputs, true);
}

console.log('PASS: accept() rejects long filenames without double-calling done, validates normally, and recovers from a failed/timed-out validation request instead of leaving files stuck');
