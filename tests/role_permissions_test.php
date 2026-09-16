<?php
// php tests/role_permissions_test.php -- no real database or permission writes.
function validate_numeric_value($id) { if (!is_numeric($id)) { throw new RuntimeException('Invalid id'); } }
function get_array_value($data, $key) { return $data[$key] ?? null; }
function log_message($level, $message, $context) { $GLOBALS['log'] = $context; }
function get_team_members_and_teams_select2_data_list() {
    if ($GLOBALS['mode'] === 'members') { throw new RuntimeException('private database details'); }
    return [];
}
function model($class) {
    return new class {
        function get_id_and_text_dropdown(...$args) {
            if ($GLOBALS['mode'] === 'ai') { throw new RuntimeException('private database details'); }
            return [];
        }
    };
}
class RolePermissionsFixture {
    public $Roles_model, $Ticket_types_model, $Client_groups_model, $template, $response;
}
$source = file_get_contents(__DIR__ . '/../app/Controllers/Roles.php');
$start = strpos($source, '    function permissions($role_id) {');
$end = strpos($source, '    //save a role', $start);
if ($start === false || $end === false) { throw new RuntimeException('Missing permissions handler'); }
eval('class RolePermissionsUnderTest extends RolePermissionsFixture {' . substr($source, $start, $end - $start) . '}');
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
set_error_handler(function ($level, $message, $file, $line) { throw new ErrorException($message, 0, $level, $file, $line); });
foreach (['empty', 'saved', 'missing', 'deleted', 'members', 'ticket', 'client', 'ai', 'malformed', 'object', 'render'] as $mode) {
    $GLOBALS['mode'] = $mode;
    $GLOBALS['log'] = [];
    $handler = new RolePermissionsUnderTest();
    $handler->Roles_model = new class {
        function get_one($id) {
            return (object)['id' => $GLOBALS['mode'] === 'missing' ? '' : $id,
                'deleted' => $GLOBALS['mode'] === 'deleted',
                'permissions' => match ($GLOBALS['mode']) {
                    'saved' => serialize(['can_create_tasks' => '1', 'operations_approve' => '1']),
                    'malformed' => 'broken', 'object' => serialize((object)['x' => 1]), default => ''
                }];
        }
    };
    foreach (['Ticket_types_model' => 'ticket', 'Client_groups_model' => 'client'] as $property => $failure) {
        $handler->$property = new class($failure) {
            function __construct(public $failure) {}
            function get_all_where($where) {
                if ($GLOBALS['mode'] === $this->failure) { throw new RuntimeException('private database details'); }
                return $this;
            }
            function getResult() { return []; }
        };
    }
    $handler->template = new class {
        public $data;
        function view($name, $data) {
            if ($GLOBALS['mode'] === 'render') { throw new TypeError('plugin error'); }
            $this->data = $data;
            return 'editor';
        }
    };
    $handler->response = new class {
        public $status, $body;
        function setStatusCode($code) { $this->status = $code; return $this; }
        function setJSON($body) { $this->body = $body; return $this; }
    };
    $result = $handler->permissions(7);
    if (in_array($mode, ['empty', 'saved'], true)) {
        check($result === 'editor', 'Must render valid role');
        if ($mode === 'saved') {
            check($handler->template->data['can_create_tasks'] === '1', 'Core permission preserved');
            check($handler->template->data['permissions']['operations_approve'] === '1', 'Plugin permission preserved');
        }
    } else {
        check($result->status === (in_array($mode, ['missing', 'deleted'], true) ? 404 : 500), 'Expected failure status');
        check($result->body['success'] === false, 'Never render partial editor');
        check(!str_contains($result->body['message'], 'private'), 'No exception detail leak');
        if ($GLOBALS['log']) { check(str_contains($result->body['message'], $GLOBALS['log']['reference']), 'Log reference'); }
    }
    echo "PASS: $mode\n";
}
