<?php
// Run: php tests/team_member_invitation_test.php
// Execute the real handler with isolated services; no database or mail access.
function app_lang($key) { return $key; }
function clean_data($value) { return $value; }
function get_uri($path = '') { return 'https://example.test/' . $path; }
function get_logo_url() { return 'https://example.test/logo.png'; }
function make_random_string() { static $id = 0; return 'code-' . ++$id; }
function log_message($level, $message, $context) { $GLOBALS['logs'][] = $context; }
function send_app_mail($to, $subject, $message) {
    $GLOBALS['attempts']++;
    if ($GLOBALS['mode'] === 'mail_throw') { throw new RuntimeException('SECRET SMTP PASSWORD'); }
    return !($GLOBALS['mode'] === 'mail_false' || ($GLOBALS['mode'] === 'partial' && $GLOBALS['attempts'] === 2));
}
class InvitationFixture {
    public $request, $Users_model, $Verification_model, $Email_templates_model, $parser, $login_user;
    protected function access_only_admin_or_member_creator() {}
    protected function validate_submitted_data($rules) {}
}
$source = file_get_contents(__DIR__ . '/../app/Controllers/Team_members.php');
$start = strpos($source, '    function send_invitation() {');
$end = strpos($source, '    //prepere the data for members list', $start);
if ($start === false || $end === false) { throw new RuntimeException('Handler not found'); }
eval('class InvitationUnderTest extends InvitationFixture {' . substr($source, $start, $end - $start) . '}');
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
foreach (['success', 'multiple', 'missing', 'scalar', 'duplicate', 'template_missing', 'template_throw', 'parser_throw', 'storage', 'mail_false', 'mail_throw', 'partial'] as $mode) {
    $GLOBALS['mode'] = $mode;
    $GLOBALS['attempts'] = 0;
    $GLOBALS['logs'] = [];
    $handler = new InvitationUnderTest();
    $handler->request = new class {
        function getPost($key) {
            if ($key === 'role') { return '2'; }
            return match ($GLOBALS['mode']) {
                'missing' => null,
                'scalar' => 'person@example.test',
                'multiple', 'partial', 'duplicate' => ['one@example.test', 'two@example.test', 'one@example.test'],
                default => ['one@example.test']
            };
        }
    };
    $handler->Users_model = new class {
        function is_email_exists($email) { return $GLOBALS['mode'] === 'duplicate' && $email === 'two@example.test'; }
    };
    $handler->Verification_model = new class {
        public $records = [];
        function ci_save(&$data) { $this->records[] = $data; return $GLOBALS['mode'] !== 'storage'; }
    };
    $handler->Email_templates_model = new class {
        function get_final_template($name) {
            if ($GLOBALS['mode'] === 'template_throw') { throw new RuntimeException('template error'); }
            return $GLOBALS['mode'] === 'template_missing' ? null : (object)['subject' => 'Invitation', 'message' => 'Join', 'signature' => 'Team'];
        }
    };
    $handler->parser = new class {
        function setData($data) { return $this; }
        function renderString($text) {
            if ($GLOBALS['mode'] === 'parser_throw') { throw new TypeError('parser error'); }
            return $text;
        }
    };
    $handler->login_user = (object)['first_name' => 'Test', 'last_name' => 'Admin'];
    ob_start();
    $handler->send_invitation();
    $raw = ob_get_clean();
    $result = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    check($result['success'] === in_array($mode, ['success', 'multiple'], true), $mode . ': result');
    check(!str_contains($raw, 'SECRET'), $mode . ': leaked credential');
    if (in_array($mode, ['missing', 'scalar', 'duplicate', 'template_missing', 'template_throw', 'parser_throw', 'storage'], true)) {
        check($GLOBALS['attempts'] === 0, $mode . ': mail must not be attempted');
    }
    if ($mode === 'multiple') {
        check($GLOBALS['attempts'] === 2, 'Deduplicate recipients');
        check(count(array_unique(array_column($handler->Verification_model->records, 'code'))) === 2, 'Unique invitation codes');
    }
    if ($mode === 'partial') { check(str_contains($result['message'], 'Invitations sent: 1'), 'Partial count'); }
    if (in_array($mode, ['mail_false', 'mail_throw'], true)) { check(str_contains($result['message'], 'Email Settings'), 'Mail guidance'); }
    if ($GLOBALS['logs']) { check(str_contains($result['message'], $GLOBALS['logs'][0]['reference']), 'Reference correlates'); }
    echo "PASS: $mode\n";
}
