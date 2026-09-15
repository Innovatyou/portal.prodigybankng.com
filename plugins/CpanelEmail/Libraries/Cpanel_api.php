<?php

namespace CpanelEmail\Libraries;

/**
 * Thin wrapper around the cPanel UAPI (Email + DomainInfo modules), authenticated
 * with a cPanel API Token (Security > Manage API Tokens inside cPanel).
 *
 * Docs referenced: UAPI Email::list_pops_with_disk, add_pop, passwd_pop, delete_pop,
 * edit_pop_quota, suspend_login, unsuspend_login, suspend_incoming, unsuspend_incoming
 * and DomainInfo::list_domains.
 */
class Cpanel_api {

    private $host;
    private $port;
    private $username;
    private $token;
    private $verify_ssl;
    private $last_error = "";

    /**
     * @param array $override optionally override the stored settings, used to
     * test a connection with values that haven't been saved yet.
     *        keys: host, port, username, api_token, verify_ssl
     */
    function __construct($override = array()) {
        $this->host = get_array_value($override, "host");
        if ($this->host === null) {
            $this->host = get_cpanel_email_setting("cpanel_email_host");
        }

        $this->port = get_array_value($override, "port");
        if (!$this->port) {
            $this->port = get_cpanel_email_setting("cpanel_email_port") ? get_cpanel_email_setting("cpanel_email_port") : 2083;
        }

        $this->username = get_array_value($override, "username");
        if ($this->username === null) {
            $this->username = get_cpanel_email_setting("cpanel_email_username");
        }

        $this->token = get_array_value($override, "api_token");
        if ($this->token === null) {
            $this->token = decode_password(get_cpanel_email_setting("cpanel_email_api_token"), "cpanel_email_api_token");
        }

        $verify_ssl = get_array_value($override, "verify_ssl");
        if ($verify_ssl === null) {
            $verify_ssl = get_cpanel_email_setting("cpanel_email_verify_ssl");
        }
        $this->verify_ssl = ((string) $verify_ssl) !== "0";
    }

    function get_last_error() {
        return $this->last_error;
    }

    function is_configured() {
        return $this->host && $this->username && $this->token;
    }

    //---------------------------------------------------------------------
    // Domains
    //---------------------------------------------------------------------

    function list_domains() {
        $result = $this->_call("DomainInfo", "list_domains");
        if ($result === false) {
            return false;
        }

        $data = get_array_value($result, "data");
        $domains = array();

        if (get_array_value($data, "main_domain")) {
            $domains[] = $data["main_domain"];
        }

        foreach (array("addon_domains", "sub_domains", "parked_domains") as $key) {
            $list = get_array_value($data, $key);
            if (is_array($list)) {
                foreach ($list as $domain) {
                    if (!in_array($domain, $domains)) {
                        $domains[] = $domain;
                    }
                }
            }
        }

        return $domains;
    }

    //---------------------------------------------------------------------
    // Email accounts
    //---------------------------------------------------------------------

    function list_email_accounts() {
        $result = $this->_call("Email", "list_pops_with_disk");
        if ($result === false) {
            return false;
        }

        $data = get_array_value($result, "data");
        return is_array($data) ? $data : array();
    }

    function create_email_account($domain, $user, $password, $quota = 250) {
        return $this->_call("Email", "add_pop", array(
            "domain" => $domain,
            "email" => $user,
            "password" => $password,
            "quota" => (int) $quota,
        ));
    }

    function reset_password($domain, $user, $password) {
        return $this->_call("Email", "passwd_pop", array(
            "domain" => $domain,
            "email" => $user,
            "password" => $password,
        ));
    }

    function delete_email_account($domain, $user) {
        return $this->_call("Email", "delete_pop", array(
            "domain" => $domain,
            "email" => $user,
        ));
    }

    //pass 0 for unlimited
    function edit_quota($domain, $user, $quota) {
        return $this->_call("Email", "edit_pop_quota", array(
            "domain" => $domain,
            "email" => $user,
            "quota" => (int) $quota,
        ));
    }

    //login restriction ("Restrict Login" toggle on cPanel's Email Accounts page)
    function suspend_login($full_email) {
        return $this->_call("Email", "suspend_login", array("email" => $full_email));
    }

    function unsuspend_login($full_email) {
        return $this->_call("Email", "unsuspend_login", array("email" => $full_email));
    }

    //incoming mail restriction
    function suspend_incoming($full_email) {
        return $this->_call("Email", "suspend_incoming", array("email" => $full_email));
    }

    function unsuspend_incoming($full_email) {
        return $this->_call("Email", "unsuspend_incoming", array("email" => $full_email));
    }

    //---------------------------------------------------------------------
    // Forwarders
    //
    // cPanel has no separate "alias" concept - an alias (an address with no
    // mailbox of its own that just delivers into an existing mailbox) is
    // created the same way as a forwarder, just pointed at an address that
    // already has a mailbox instead of an outside address.
    //---------------------------------------------------------------------

    //list_forwarders requires a domain, unlike list_pops_with_disk
    function list_forwarders($domain) {
        $result = $this->_call("Email", "list_forwarders", array("domain" => $domain));
        if ($result === false) {
            return false;
        }

        $data = get_array_value($result, "data");
        return is_array($data) ? $data : array();
    }

    //aggregate forwarders across every domain on the account
    function list_all_forwarders() {
        $domains = $this->list_domains();
        if ($domains === false) {
            return false;
        }

        $forwarders = array();
        foreach ($domains as $domain) {
            $list = $this->list_forwarders($domain);
            if (is_array($list)) {
                foreach ($list as $row) {
                    $source = get_array_value($row, "dest");
                    if (!$source) {
                        $source = get_array_value($row, "source");
                    }

                    $destination = get_array_value($row, "forward");
                    if (!$destination) {
                        $destination = get_array_value($row, "fwdemail");
                    }

                    $type = get_array_value($row, "type");
                    if (!$type) {
                        $type = "fwd";
                    }

                    //skip system-generated rows (blackhole/fail/pipe) - this
                    //table only manages plain address-to-address forwarding
                    if ($type !== "fwd" || !$source || !$destination) {
                        continue;
                    }

                    $forwarders[] = array(
                        "domain" => $domain,
                        "source" => $source,
                        "destination" => $destination,
                    );
                }
            }
        }

        return $forwarders;
    }

    function add_forwarder($domain, $user, $destination) {
        return $this->_call("Email", "add_forwarder", array(
            "domain" => $domain,
            "email" => $user,
            "fwdopt" => "fwd",
            "fwdemail" => $destination,
        ));
    }

    function delete_forwarder($full_source_email, $destination) {
        return $this->_call("Email", "delete_forwarder", array(
            "address" => $full_source_email,
            "forwarder" => $destination,
        ));
    }

    //---------------------------------------------------------------------
    // Internals
    //---------------------------------------------------------------------

    private function _call($module, $function, $params = array()) {
        $this->last_error = "";

        if (!$this->is_configured()) {
            $this->last_error = app_lang("cpanel_email_not_configured");
            return false;
        }

        $host = preg_replace('#^https?://#i', '', rtrim(trim($this->host), "/"));
        $url = "https://" . $host . ":" . $this->port . "/execute/" . $module . "/" . $function;

        //params (which can include plaintext passwords) are sent as a POST body
        //instead of a query string so they don't end up in the server's access logs
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: cpanel " . $this->username . ":" . $this->token,
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl ? 1 : 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verify_ssl ? 2 : 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        if ($response === false) {
            $this->last_error = curl_error($ch);
            curl_close($ch);
            log_message('error', 'cPanel API connection error: ' . $this->last_error);
            return false;
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($http_code === 401 || $http_code === 403) {
            $this->last_error = app_lang("cpanel_email_authentication_failed");
            return false;
        }

        if (!is_array($result)) {
            $this->last_error = app_lang("cpanel_email_unexpected_response") . " (HTTP $http_code)";
            log_message('error', 'cPanel API raw response: ' . substr((string) $response, 0, 1000));
            return false;
        }

        if (array_key_exists("status", $result) && !$result["status"]) {
            $errors = get_array_value($result, "errors");
            if (is_array($errors) && $errors) {
                $this->last_error = implode(" ", $errors);
            } else {
                $this->last_error = app_lang("error_occurred");
            }
            return false;
        }

        return $result;
    }

}
