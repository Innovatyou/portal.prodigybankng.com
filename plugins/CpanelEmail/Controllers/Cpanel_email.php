<?php

namespace CpanelEmail\Controllers;

use App\Controllers\Security_Controller;
use CpanelEmail\Libraries\Cpanel_api;

class Cpanel_email extends Security_Controller {

    protected $Cpanel_email_settings_model;

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->Cpanel_email_settings_model = new \CpanelEmail\Models\Cpanel_email_settings_model();
    }

    function index() {
        return $this->template->rander("CpanelEmail\Views\index");
    }

    //--------------------------------------------------------------
    // Connection settings
    //--------------------------------------------------------------

    function settings_modal_form() {
        return $this->template->view('CpanelEmail\Views\settings_modal_form');
    }

    function save_settings() {
        $this->validate_submitted_data(array(
            "cpanel_email_host" => "required",
            "cpanel_email_username" => "required",
            "cpanel_email_port" => "required|numeric",
        ));

        $host = $this->request->getPost("cpanel_email_host");
        $port = $this->request->getPost("cpanel_email_port");
        $username = $this->request->getPost("cpanel_email_username");
        $verify_ssl = $this->request->getPost("cpanel_email_verify_ssl") ? "1" : "0";
        $token = $this->request->getPost("cpanel_email_api_token");

        if ($token === "******") {
            //not changed, keep the existing one
            $token = decode_password(get_cpanel_email_setting("cpanel_email_api_token"), "cpanel_email_api_token");
        }

        if (!$token) {
            echo json_encode(array("success" => false, 'message' => app_lang('field_required') . ": " . app_lang('cpanel_email_api_token')));
            return false;
        }

        $this->Cpanel_email_settings_model->save_setting("cpanel_email_host", $host);
        $this->Cpanel_email_settings_model->save_setting("cpanel_email_port", $port);
        $this->Cpanel_email_settings_model->save_setting("cpanel_email_username", $username);
        $this->Cpanel_email_settings_model->save_setting("cpanel_email_verify_ssl", $verify_ssl);
        $this->Cpanel_email_settings_model->save_setting("cpanel_email_api_token", encode_id($token, "cpanel_email_api_token"));

        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }

    //test the connection, either with the saved settings or with the values currently typed in the settings form
    function test_connection() {
        $token = $this->request->getPost("cpanel_email_api_token");
        if ($token === "******" || $token === null) {
            $token = decode_password(get_cpanel_email_setting("cpanel_email_api_token"), "cpanel_email_api_token");
        }

        $override = array(
            "host" => $this->request->getPost("cpanel_email_host"),
            "port" => $this->request->getPost("cpanel_email_port"),
            "username" => $this->request->getPost("cpanel_email_username"),
            "api_token" => $token,
            "verify_ssl" => $this->request->getPost("cpanel_email_verify_ssl") ? "1" : "0",
        );

        $cpanel = new Cpanel_api($override);
        $domains = $cpanel->list_domains();

        if ($domains === false) {
            echo json_encode(array("success" => false, 'message' => $cpanel->get_last_error()));
            return false;
        }

        echo json_encode(array("success" => true, 'message' => app_lang('cpanel_email_connection_successful') . ' (' . implode(", ", $domains) . ')'));
    }

    //--------------------------------------------------------------
    // Email accounts
    //--------------------------------------------------------------

    function list_data() {
        $cpanel = new Cpanel_api();

        if (!$cpanel->is_configured()) {
            echo json_encode(array("data" => array(), "cpanel_email_not_configured" => true));
            return false;
        }

        $accounts = $cpanel->list_email_accounts();

        if ($accounts === false) {
            echo json_encode(array("data" => array(), "error" => $cpanel->get_last_error()));
            return false;
        }

        $result = array();
        foreach ($accounts as $account) {
            $result[] = $this->_make_row($account);
        }

        echo json_encode(array("data" => $result));
    }

    function modal_form() {
        $cpanel = new Cpanel_api();
        $view_data['domains'] = $cpanel->is_configured() ? $cpanel->list_domains() : array();
        $view_data['config_error'] = $cpanel->is_configured() ? "" : app_lang("cpanel_email_not_configured");

        return $this->template->view('CpanelEmail\Views\modal_form', $view_data);
    }

    function save() {
        $this->validate_submitted_data(array(
            "domain" => "required",
            "user" => "required|regex_match[/^[a-zA-Z0-9._+-]+$/]",
            "password" => "required|min_length[8]",
        ));

        $domain = $this->request->getPost("domain");
        $user = $this->request->getPost("user");
        $password = $this->request->getPost("password");
        $quota = $this->request->getPost("quota");
        $quota = ($quota === "" || $quota === null) ? 250 : (int) $quota;

        $cpanel = new Cpanel_api();
        $result = $cpanel->create_email_account($domain, $user, $password, $quota);

        if ($result === false) {
            echo json_encode(array("success" => false, 'message' => $cpanel->get_last_error()));
            return false;
        }

        $token = cpanel_email_make_token($domain, $user);
        $row = $this->_make_row(array(
            "domain" => $domain,
            "user" => $user,
            "email" => $user . "@" . $domain,
            "humandiskused" => "0",
            "humandiskquota" => $quota ? $quota . " MB" : "",
            "suspended_login" => "0",
            "suspended_incoming" => "0",
        ));

        echo json_encode(array("success" => true, "data" => $row, "id" => $token, 'message' => app_lang('record_saved')));
    }

    function reset_password_modal_form($token = "") {
        $info = cpanel_email_parse_token($token);
        $view_data['token'] = $token;
        $view_data['email'] = $info->user . "@" . $info->domain;

        return $this->template->view('CpanelEmail\Views\reset_password_modal_form', $view_data);
    }

    function save_reset_password() {
        $this->validate_submitted_data(array(
            "token" => "required",
            "password" => "required|min_length[8]",
        ));

        $info = cpanel_email_parse_token($this->request->getPost("token"));
        if (!$info->domain || !$info->user) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            return false;
        }

        $cpanel = new Cpanel_api();
        $result = $cpanel->reset_password($info->domain, $info->user, $this->request->getPost("password"));

        if ($result === false) {
            echo json_encode(array("success" => false, 'message' => $cpanel->get_last_error()));
            return false;
        }

        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }

    function quota_modal_form($token = "") {
        $info = cpanel_email_parse_token($token);
        $view_data['token'] = $token;
        $view_data['email'] = $info->user . "@" . $info->domain;

        return $this->template->view('CpanelEmail\Views\quota_modal_form', $view_data);
    }

    function save_quota() {
        $this->validate_submitted_data(array(
            "token" => "required",
        ));

        $info = cpanel_email_parse_token($this->request->getPost("token"));
        if (!$info->domain || !$info->user) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            return false;
        }

        $quota = $this->request->getPost("quota");
        $quota = ($quota === "" || $quota === null) ? 0 : (int) $quota;

        $cpanel = new Cpanel_api();
        $result = $cpanel->edit_quota($info->domain, $info->user, $quota);

        if ($result === false) {
            echo json_encode(array("success" => false, 'message' => $cpanel->get_last_error()));
            return false;
        }

        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }

    //restrict login (suspend_login) - triggered as a plain [data-action=update] table row link
    function restrict_login($token = "") {
        $this->_toggle($token, "suspend_login");
    }

    function unrestrict_login($token = "") {
        $this->_toggle($token, "unsuspend_login");
    }

    function restrict_incoming($token = "") {
        $this->_toggle($token, "suspend_incoming");
    }

    function unrestrict_incoming($token = "") {
        $this->_toggle($token, "unsuspend_incoming");
    }

    private function _toggle($token, $action) {
        $info = cpanel_email_parse_token($token);
        if (!$info->domain || !$info->user) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            return false;
        }

        $full_email = $info->user . "@" . $info->domain;

        $cpanel = new Cpanel_api();
        $result = $cpanel->$action($full_email);

        if ($result === false) {
            echo json_encode(array("success" => false, 'message' => $cpanel->get_last_error()));
            return false;
        }

        //fetch the fresh row so the table can be updated in place
        $accounts = $cpanel->list_email_accounts();
        $row = array();
        if (is_array($accounts)) {
            foreach ($accounts as $account) {
                if (get_array_value($account, "email") === $full_email) {
                    $row = $this->_make_row($account);
                    break;
                }
            }
        }

        echo json_encode(array("success" => true, "data" => $row, "id" => $token, 'message' => app_lang('record_updated')));
    }

    function delete() {
        $this->validate_submitted_data(array(
            "id" => "required"
        ));

        $info = cpanel_email_parse_token($this->request->getPost("id"));
        if (!$info->domain || !$info->user) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            return false;
        }

        $cpanel = new Cpanel_api();
        $result = $cpanel->delete_email_account($info->domain, $info->user);

        if ($result === false) {
            echo json_encode(array("success" => false, 'message' => $cpanel->get_last_error()));
            return false;
        }

        echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
    }

    //--------------------------------------------------------------
    // Forwarders & aliases
    //
    // An "alias" (an address with no mailbox that just delivers into an
    // existing mailbox) is created exactly like a forwarder - just point
    // the destination at an address that already has a mailbox.
    //--------------------------------------------------------------

    function forwarders() {
        return $this->template->rander("CpanelEmail\Views\forwarders_index");
    }

    function forwarders_list_data() {
        $cpanel = new Cpanel_api();

        if (!$cpanel->is_configured()) {
            echo json_encode(array("data" => array(), "cpanel_email_not_configured" => true));
            return false;
        }

        $forwarders = $cpanel->list_all_forwarders();

        if ($forwarders === false) {
            echo json_encode(array("data" => array(), "error" => $cpanel->get_last_error()));
            return false;
        }

        $result = array();
        foreach ($forwarders as $forwarder) {
            $result[] = $this->_make_forwarder_row($forwarder);
        }

        echo json_encode(array("data" => $result));
    }

    function forwarder_modal_form() {
        $cpanel = new Cpanel_api();
        $view_data['domains'] = $cpanel->is_configured() ? $cpanel->list_domains() : array();
        $view_data['config_error'] = $cpanel->is_configured() ? "" : app_lang("cpanel_email_not_configured");

        return $this->template->view('CpanelEmail\Views\forwarder_modal_form', $view_data);
    }

    function save_forwarder() {
        $this->validate_submitted_data(array(
            "domain" => "required",
            "user" => "required|regex_match[/^[a-zA-Z0-9._+-]+$/]",
            "destination" => "required|valid_email",
        ));

        $domain = $this->request->getPost("domain");
        $user = $this->request->getPost("user");
        $destination = $this->request->getPost("destination");

        $cpanel = new Cpanel_api();
        $result = $cpanel->add_forwarder($domain, $user, $destination);

        if ($result === false) {
            echo json_encode(array("success" => false, 'message' => $cpanel->get_last_error()));
            return false;
        }

        $source = $user . "@" . $domain;
        $token = cpanel_email_make_forwarder_token($source, $destination);
        $row = $this->_make_forwarder_row(array(
            "domain" => $domain,
            "source" => $source,
            "destination" => $destination,
        ));

        echo json_encode(array("success" => true, "data" => $row, "id" => $token, 'message' => app_lang('record_saved')));
    }

    function delete_forwarder() {
        $this->validate_submitted_data(array(
            "id" => "required"
        ));

        $info = cpanel_email_parse_forwarder_token($this->request->getPost("id"));
        if (!$info->source || !$info->destination) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            return false;
        }

        $cpanel = new Cpanel_api();
        $result = $cpanel->delete_forwarder($info->source, $info->destination);

        if ($result === false) {
            echo json_encode(array("success" => false, 'message' => $cpanel->get_last_error()));
            return false;
        }

        echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
    }

    private function _make_forwarder_row($forwarder) {
        $domain = get_array_value($forwarder, "domain");
        $source = get_array_value($forwarder, "source");
        $destination = get_array_value($forwarder, "destination");

        $token = cpanel_email_make_forwarder_token($source, $destination);

        $options = array();
        $options[] = js_anchor("<i data-feather='x' class='icon-16'></i> " . app_lang("delete"), array("title" => app_lang("delete"), "class" => "dropdown-item delete", "data-id" => $token, "data-undo" => "0", "data-action-url" => get_uri("cpanel_email/delete_forwarder"), "data-action" => "delete-confirmation"));

        $action_menu = "<span class='dropdown inline-block'>
                <button class='btn btn-default dropdown-toggle caret mt0 mb0' type='button' data-bs-toggle='dropdown' aria-expanded='true' data-bs-display='static'>
                    <i data-feather='tool' class='icon-16'></i>
                </button>
                <ul class='dropdown-menu dropdown-menu-end' role='menu'><li role='presentation'>" . implode("</li><li role='presentation'>", $options) . "</li></ul>
            </span>";

        return array(
            "<b data-post-id='" . $token . "'>" . $source . "</b>",
            "<i data-feather='arrow-right' class='icon-16'></i> " . $destination,
            $domain,
            $action_menu
        );
    }

    //--------------------------------------------------------------
    private function _make_row($account) {
        $domain = get_array_value($account, "domain");
        $user = get_array_value($account, "user");
        $email = get_array_value($account, "email");
        if (!$email) {
            $email = $user . "@" . $domain;
        }

        $token = cpanel_email_make_token($domain, $user);

        $quota = get_array_value($account, "humandiskquota");
        $used = get_array_value($account, "humandiskused");
        $disk_usage = ($used ? $used : "0") . " / " . (($quota && strtolower($quota) !== "unlimited") ? $quota : app_lang("unlimited"));

        $login_suspended = (string) get_array_value($account, "suspended_login") === "1";
        $incoming_suspended = (string) get_array_value($account, "suspended_incoming") === "1";

        $login_status = $login_suspended ?
            "<span class='mt0 badge bg-danger'>" . app_lang("cpanel_email_restricted") . "</span>" :
            "<span class='mt0 badge bg-success'>" . app_lang("cpanel_email_active") . "</span>";

        $incoming_status = $incoming_suspended ?
            "<span class='mt0 badge bg-danger'>" . app_lang("cpanel_email_restricted") . "</span>" :
            "<span class='mt0 badge bg-success'>" . app_lang("cpanel_email_active") . "</span>";

        $options = array();

        $options[] = modal_anchor(get_uri("cpanel_email/reset_password_modal_form/" . $token), "<i data-feather='key' class='icon-16'></i> " . app_lang("cpanel_email_reset_password"), array("title" => app_lang("cpanel_email_reset_password"), "data-post-id" => $token, "class" => "dropdown-item"));

        $options[] = modal_anchor(get_uri("cpanel_email/quota_modal_form/" . $token), "<i data-feather='hard-drive' class='icon-16'></i> " . app_lang("cpanel_email_edit_quota"), array("title" => app_lang("cpanel_email_edit_quota"), "class" => "dropdown-item"));

        if ($login_suspended) {
            $options[] = js_anchor("<i data-feather='unlock' class='icon-16'></i> " . app_lang("cpanel_email_allow_login"), array("title" => app_lang("cpanel_email_allow_login"), "class" => "dropdown-item", "data-action" => "update", "data-action-url" => get_uri("cpanel_email/unrestrict_login/" . $token)));
        } else {
            $options[] = js_anchor("<i data-feather='lock' class='icon-16'></i> " . app_lang("cpanel_email_restrict_login"), array("title" => app_lang("cpanel_email_restrict_login"), "class" => "dropdown-item", "data-action" => "update", "data-action-url" => get_uri("cpanel_email/restrict_login/" . $token)));
        }

        if ($incoming_suspended) {
            $options[] = js_anchor("<i data-feather='inbox' class='icon-16'></i> " . app_lang("cpanel_email_allow_incoming"), array("title" => app_lang("cpanel_email_allow_incoming"), "class" => "dropdown-item", "data-action" => "update", "data-action-url" => get_uri("cpanel_email/unrestrict_incoming/" . $token)));
        } else {
            $options[] = js_anchor("<i data-feather='slash' class='icon-16'></i> " . app_lang("cpanel_email_restrict_incoming"), array("title" => app_lang("cpanel_email_restrict_incoming"), "class" => "dropdown-item", "data-action" => "update", "data-action-url" => get_uri("cpanel_email/restrict_incoming/" . $token)));
        }

        $options[] = js_anchor("<i data-feather='x' class='icon-16'></i> " . app_lang("delete"), array("title" => app_lang("delete"), "class" => "dropdown-item delete", "data-id" => $token, "data-undo" => "0", "data-action-url" => get_uri("cpanel_email/delete"), "data-action" => "delete-confirmation"));

        $action_menu = "<span class='dropdown inline-block'>
                <button class='btn btn-default dropdown-toggle caret mt0 mb0' type='button' data-bs-toggle='dropdown' aria-expanded='true' data-bs-display='static'>
                    <i data-feather='tool' class='icon-16'></i>
                </button>
                <ul class='dropdown-menu dropdown-menu-end' role='menu'><li role='presentation'>" . implode("</li><li role='presentation'>", $options) . "</li></ul>
            </span>";

        return array(
            "<b data-post-id='" . $token . "'>" . $email . "</b>",
            $domain,
            $disk_usage,
            $login_status,
            $incoming_status,
            $action_menu
        );
    }

}
