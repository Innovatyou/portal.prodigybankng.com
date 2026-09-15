<?php

$lang["manage"] = "Manage";
$lang["domain"] = "Domain";
$lang["unlimited"] = "Unlimited";

$lang["cpanel_email"] = "Email";

$lang["cpanel_email_accounts"] = "cPanel Email Accounts";
$lang["cpanel_email_connection_settings"] = "Connection Settings";
$lang["cpanel_email_add_account"] = "Add Email Account";
$lang["cpanel_email_not_configured_help_message"] = "cPanel connection isn't configured yet. Click \"Connection Settings\" and enter your cPanel hostname, username and API Token to start managing email accounts.";

$lang["cpanel_email_disk_usage"] = "Disk Usage";
$lang["cpanel_email_login"] = "Login";
$lang["cpanel_email_incoming_mail"] = "Incoming Mail";
$lang["cpanel_email_active"] = "Active";
$lang["cpanel_email_restricted"] = "Restricted";

$lang["cpanel_email_reset_password"] = "Reset Password";
$lang["cpanel_email_edit_quota"] = "Edit Quota";
$lang["cpanel_email_allow_login"] = "Allow Login";
$lang["cpanel_email_restrict_login"] = "Restrict Login";
$lang["cpanel_email_allow_incoming"] = "Allow Incoming Mail";
$lang["cpanel_email_restrict_incoming"] = "Restrict Incoming Mail";

$lang["cpanel_email_rename"] = "Rename / Change Username";
$lang["cpanel_email_current_username"] = "Current Address";
$lang["cpanel_email_new_username"] = "New Username";
$lang["cpanel_email_keep_old_forwarding"] = "Keep the old address working by forwarding it to the new mailbox";
$lang["cpanel_email_delete_old_account"] = "Delete the old mailbox after creating the new one (mail already stored in it will be lost)";
$lang["cpanel_email_rename_help_message"] = "cPanel does not support renaming a mailbox directly. This creates a brand new mailbox under the new username; messages already stored in the old mailbox are not moved automatically.";
$lang["cpanel_email_rename_same_username"] = "The new username must be different from the current one.";
$lang["cpanel_email_rename_created"] = "New mailbox created:";
$lang["cpanel_email_rename_forward_failed"] = "The new mailbox was created, but forwarding the old address failed:";
$lang["cpanel_email_rename_delete_failed"] = "The new mailbox was created, but deleting the old one failed:";

$lang["cpanel_email_forwarders"] = "Email Forwarders";
$lang["cpanel_email_add_forwarder"] = "Add Forwarder";
$lang["cpanel_email_forwarders_help_message"] = "Forward mail from one address to another. To create an email alias, forward the address to an existing mailbox instead of an outside address - no separate mailbox or password is needed for the alias itself.";
$lang["cpanel_email_forward_from"] = "Forward From";
$lang["cpanel_email_forward_to"] = "Forward To";
$lang["cpanel_email_forward_to_placeholder"] = "destination@example.com";

$lang["cpanel_email_username_local_part"] = "Username";
$lang["cpanel_email_quota_mb"] = "Quota (MB)";
$lang["cpanel_email_quota_help_message"] = "Maximum mailbox size in megabytes. Leave as 0 for unlimited.";
$lang["cpanel_email_new_password"] = "New Password";
$lang["cpanel_email_password_too_short"] = "Password must be at least 8 characters.";

$lang["cpanel_email_host"] = "cPanel Hostname";
$lang["cpanel_email_port"] = "Port";
$lang["cpanel_email_username"] = "cPanel Username";
$lang["cpanel_email_api_token"] = "API Token";
$lang["cpanel_email_api_token_help_message"] = "Generate this from cPanel > Security > Manage API Tokens. The token is stored encrypted.";
$lang["cpanel_email_verify_ssl"] = "Verify SSL Certificate";
$lang["cpanel_email_test_connection"] = "Test Connection";
$lang["cpanel_email_settings_help_message"] = "Enter the cPanel account that hosts your email (e.g. the Prodigy Bank hosting account) and an API Token generated from cPanel > Security > Manage API Tokens.";
$lang["cpanel_email_connection_successful"] = "Connection successful! Domains found";

$lang["cpanel_email_not_configured"] = "cPanel connection isn't configured yet. Please save your connection settings first.";
$lang["cpanel_email_authentication_failed"] = "Authentication failed. Please check the cPanel username and API Token.";
$lang["cpanel_email_unexpected_response"] = "Received an unexpected response from the cPanel server.";

return $lang;
