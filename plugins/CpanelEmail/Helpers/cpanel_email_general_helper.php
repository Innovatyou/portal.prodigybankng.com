<?php

use CpanelEmail\Config\CpanelEmail as CpanelEmail_config;

/**
 * get the defined config value by a key
 * @param string $key
 * @return config value
 */
if (!function_exists('get_cpanel_email_setting')) {

    function get_cpanel_email_setting($key = "") {
        $config = new CpanelEmail_config();

        $setting_value = get_array_value($config->app_settings_array, $key);
        if ($setting_value !== NULL) {
            return $setting_value;
        } else {
            return "";
        }
    }
}

/**
 * encode a domain + mailbox username pair into a url safe token.
 * cPanel email accounts don't have a local numeric id, so we use this
 * token (instead of a database id) to identify a row in the accounts table.
 *
 * @param string $domain
 * @param string $user local part of the email address (without @domain)
 * @return string
 */
if (!function_exists('cpanel_email_make_token')) {

    function cpanel_email_make_token($domain, $user) {
        $encoded = base64_encode($domain . "|" . $user);
        return rtrim(strtr($encoded, '+/', '-_'), '=');
    }
}

/**
 * decode a token created by cpanel_email_make_token()
 *
 * @param string $token
 * @return stdClass with ->domain and ->user
 */
if (!function_exists('cpanel_email_parse_token')) {

    function cpanel_email_parse_token($token) {
        $result = new stdClass();
        $result->domain = "";
        $result->user = "";

        $padded = str_pad(strtr($token, '-_', '+/'), strlen($token) % 4 === 0 ? strlen($token) : strlen($token) + (4 - strlen($token) % 4), '=', STR_PAD_RIGHT);
        $decoded = base64_decode($padded, true);

        if ($decoded === false || strpos($decoded, "|") === false) {
            return $result;
        }

        $parts = explode("|", $decoded, 2);
        $result->domain = get_array_value($parts, 0);
        $result->user = get_array_value($parts, 1);

        return $result;
    }
}
