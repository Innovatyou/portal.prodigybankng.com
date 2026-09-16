<?php
// php tests/cpanel_email_permissions_test.php (no cPanel requests)
require __DIR__ . '/../plugins/CpanelEmail/Libraries/Cpanel_email_permissions.php';
use CpanelEmail\Libraries\Cpanel_email_permissions;
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
foreach ([
    ['admin', 'staff', 1, [], true],
    ['settings administrator', 'staff', 0, ['can_manage_all_kinds_of_settings' => '1'], true],
    ['assigned role', 'staff', 0, ['can_manage_cpanel_email' => '1'], true],
    ['unassigned role', 'staff', 0, [], false],
    ['revoked role', 'staff', 0, ['can_manage_cpanel_email' => '0'], false],
    ['client', 'client', 0, ['can_manage_cpanel_email' => '1'], false],
] as [$name, $type, $admin, $permissions, $expected]) {
    check(Cpanel_email_permissions::can_manage((object)['user_type' => $type, 'is_admin' => $admin, 'permissions' => $permissions]) === $expected, $name);
    echo "PASS: $name\n";
}
define('PLUGINPATH', __DIR__ . '/../plugins/');
function app_hooks() {
    static $hooks;
    return $hooks ??= new class {
        public $filters = [], $actions = [];
        function add_filter($name, $callback) { $this->filters[$name] = $callback; }
        function add_action($name, $callback) { $this->actions[$name] = $callback; }
    };
}
function register_activation_hook(...$args) {}
function register_uninstallation_hook(...$args) {}
function service($name) { return new class { function getPost($key) { return $GLOBALS['posted_permission']; } }; }
require __DIR__ . '/../plugins/CpanelEmail/index.php';
$save = app_hooks()->filters['app_filter_role_permissions_save_data'];
foreach (['1', null] as $posted) {
    $GLOBALS['posted_permission'] = $posted;
    $result = $save(['can_create_tasks' => '1']);
    check($result['can_manage_cpanel_email'] === ($posted ? '1' : '0'), 'Save checkbox state');
    check($result['can_create_tasks'] === '1', 'Preserve other permissions');
}
check(isset(app_hooks()->actions['app_hook_role_permissions_extension']), 'Register role UI');
echo "PASS: role UI registration and grant/revoke persistence\n";
$controller = file_get_contents(__DIR__ . '/../plugins/CpanelEmail/Controllers/Cpanel_email.php');
foreach (['settings_modal_form', 'save_settings', 'test_connection'] as $method) {
    check((bool) preg_match('/function ' . $method . '\(\)\s*\{\s*\$this->access_only_admin_or_settings_admin\(\);/', $controller), 'Connection settings guard: ' . $method);
}
echo "PASS: connection settings retain administrator guards\n";
