<?php

defined('PLUGINPATH') or exit('No direct script access allowed');

/*
  Plugin Name: cPanel Email Manager
  Description: Create, secure and manage cPanel webmail accounts (create accounts, reset passwords, restrict login/incoming mail, manage quota) directly from the CRM using a cPanel API Token.
  Version: 1.0
  Requires at least: 3.0
  Author: Prodigy Bank
 */

//add admin setting menu item (Settings > Plugins > cPanel Email Accounts)
app_hooks()->add_filter('app_filter_admin_settings_menu', function ($settings_menu) {
    $settings_menu["plugins"][] = array("name" => "cpanel_email_accounts", "url" => "cpanel_email");
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
