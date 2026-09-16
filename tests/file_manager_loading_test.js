// node tests/file_manager_loading_test.js -- no network or database access.
const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const source = fs.readFileSync('app/Views/app_folders/index.php', 'utf8');
const script = source.match(/<script type="text\/javascript">([\s\S]*?)<\/script>/)[1]
    .replace(/<\?php[\s\S]*?\?>/g, 'stub');
new vm.Script(script);
const html = {};
let request, hidden = false, error = false;
const $ = selector => ({html(value) { html[selector] = value; return this; }});
$.ajax = options => { request = options; };
const context = {
    $, history: null, appLoader: {show() {}, hide() {hidden = true;}},
    appAlert: {error() {error = true;}}, AppLanguage: {somethingWentWrong: 'Error'},
    feather: {replace() {}}, setFolderWindowHeight() {}
};
vm.createContext(context);
for (const [name, next] of [['openFolderWindow', 'showFilePreviewAppModal'], ['updateFavoritesSection', 'showContextMenu']]) {
    vm.runInContext(script.slice(script.indexOf('function ' + name), script.indexOf('function ' + next)), context);
    context[name](12);
    assert(request, name + ' must issue a direct request');
    assert.equal(request.timeout, 30000);
    request.success({success: true, window_content: 'new folder', title_bar_content: 'title', content: 'favourites'});
    request.error();
    request.complete();
    assert(hidden && error, name + ' must recover from failure');
    hidden = error = false;
}
assert.equal(html['#file-manager-container'], 'new folder');
assert.equal(html['#favourite-folders'], 'favourites');
assert(!source.includes('appAjaxRequest('));
console.log('PASS: script syntax, folder rendering, favourites, direct requests, timeout and failure cleanup');
