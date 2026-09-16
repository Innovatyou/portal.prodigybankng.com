<?php

defined('PLUGINPATH') or exit('No direct script access allowed');

/*
  Plugin Name: cPanel Email Manager
  Description: Create, secure and manage cPanel webmail accounts (create accounts, reset passwords, restrict login/incoming mail, manage quota) directly from the CRM using a cPanel API Token.
  Version: 1.0
  Requires at least: 3.0
  Author: Prodigy Bank
 */

use App\Controllers\Security_Controller;
use CpanelEmail\Libraries\Cpanel_email_permissions;

app_hooks()->add_action('app_hook_role_permissions_extension', function () {
    echo view('CpanelEmail\Views\roles\permissions');
});

app_hooks()->add_filter('app_filter_role_permissions_save_data', function ($permissions) {
    $permissions['can_manage_cpanel_email'] = service('request')->getPost('can_manage_cpanel_email') ? '1' : '0';
    return $permissions;
});

//add "Email" to the sidebar (Settings > Left Menu > available items), visible only
//to staff with cPanel email management access.
app_hooks()->add_filter('app_filter_staff_left_menu', function ($sidebar_menu) {
    $instance = new Security_Controller();

    if (Cpanel_email_permissions::can_manage($instance->login_user)) {
        $sidebar_menu["cpanel_email"] = array(
            "name" => "cpanel_email",
            "url" => "cpanel_email",
            "class" => "mail",
            "sub_pages" => array(
                "cpanel_email/forwarders"
            )
        );
    }

    return $sidebar_menu;
});

//add admin setting menu items (Settings > Plugins > cPanel Email Accounts / Forwarders)
app_hooks()->add_filter('app_filter_admin_settings_menu', function ($settings_menu) {
    $settings_menu["plugins"][] = array("name" => "cpanel_email_accounts", "url" => "cpanel_email");
    $settings_menu["plugins"][] = array("name" => "cpanel_email_forwarders", "url" => "cpanel_email/forwarders");
    return $settings_menu;
});

//add a quick link under the plugin list on the Settings > Plugins page
app_hooks()->add_filter('app_filter_action_links_of_CpanelEmail', function ($action_links_array) {
    $action_links_array[] = anchor(get_uri("cpanel_email"), app_lang("manage"));
    return $action_links_array;
});

//create the settings table when this plugin is activated
register_activation_hook("CpanelEmail", function () {
    $dbprefix = get_db_prefix();
    $db = db_connect('default');

    $sql_query = "CREATE TABLE IF NOT EXISTS `" . $dbprefix . "cpanel_email_settings` (
        `setting_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `setting_value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
        `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'app',
        `deleted` tinyint(1) NOT NULL DEFAULT '0',
        UNIQUE KEY `setting_name` (`setting_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    $db->query($sql_query);
});

//remove the settings table when this plugin is uninstalled
register_uninstallation_hook("CpanelEmail", function () {
    $dbprefix = get_db_prefix();
    $db = db_connect('default');

    $sql_query = "DROP TABLE IF EXISTS `" . $dbprefix . "cpanel_email_settings`;";
    $db->query($sql_query);
});
