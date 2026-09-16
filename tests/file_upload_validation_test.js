// node tests/file_upload_validation_test.js -- no files or network requests.
const fs = require('fs'), vm = require('vm'), assert = require('assert');
const source = fs.readFileSync('app/Views/includes/multi_file_uploader.php', 'utf8');
const start = source.indexOf('accept: function(file, done) {');
const end = source.indexOf('            processing:', start);
const callback = source.slice(start + 'accept: '.length, end).trim().replace(/,$/, '')
    .replace(/<\?php[\s\S]*?\?>/g, '/uploader/validate_file');
for (const mode of ['accept', 'reject', 'network', 'long-name']) {
    let request, doneCount = 0, doneError, removed = false;
    const element = {find() {return this;}, remove() {removed = true; return this;}, attr() {return this;}, append() {return this;}, val() {return this;}};
    const $ = () => element;
    $.ajax = options => {request = options;};
    const context = {$, fileSerial: 0, AppLanguage: {somethingWentWrong: 'Upload validation failed'}};
    const accept = vm.runInNewContext('(' + callback + ')', context);
    accept({name: mode === 'long-name' ? 'x'.repeat(201) : 'example.txt', size: 12, previewTemplate: {}}, error => {doneCount++; doneError = error;});
    if (mode === 'long-name') {
        assert(!request);
        assert(doneError);
    } else {
        assert.equal(request.type, 'POST');
        assert.equal(request.data.file_name, 'example.txt');
        assert.equal(request.timeout, 30000);
        if (mode === 'network') {request.error();} else {request.success({success: mode === 'accept', message: 'Rejected'});}
        assert.equal(context.fileSerial, mode === 'accept' ? 1 : 0);
        assert.equal(Boolean(doneError), mode !== 'accept');
        assert.equal(removed, mode !== 'accept');
    }
    assert.equal(doneCount, 1);
    console.log('PASS: ' + mode);
}
