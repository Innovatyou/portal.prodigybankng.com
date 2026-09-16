<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "cpanel_email_accounts";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="card">
                <div class="page-title clearfix">
                    <h4 class="mt15"><?php echo app_lang('cpanel_email_accounts'); ?></h4>
                    <div class="title-button-group">
                        <?php if ($login_user->is_admin || get_array_value($login_user->permissions, 'can_manage_all_kinds_of_settings')) { ?>
                        <?php echo modal_anchor(get_uri("cpanel_email/settings_modal_form"), "<i data-feather='settings' class='icon-16'></i> " . app_lang('cpanel_email_connection_settings'), array("class" => "btn btn-default", "title" => app_lang('cpanel_email_connection_settings'))); ?>
                        <?php } ?>
                        <?php echo modal_anchor(get_uri("cpanel_email/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('cpanel_email_add_account'), array("class" => "btn btn-default", "title" => app_lang('cpanel_email_add_account'))); ?>
                    </div>
                </div>

                <?php if (!(get_cpanel_email_setting("cpanel_email_host") && get_cpanel_email_setting("cpanel_email_username") && get_cpanel_email_setting("cpanel_email_api_token"))) { ?>
                    <div class="alert alert-warning mt15">
                        <i data-feather="alert-triangle" class="icon-16"></i>
                        <?php echo app_lang("cpanel_email_not_configured_help_message"); ?>
                    </div>
                <?php } ?>

                <div class="table-responsive">
                    <table id="cpanel-email-table" class="display" cellspacing="0" width="100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    "use strict";

    $(document).ready(function () {
        $("#cpanel-email-table").appTable({
            source: '<?php echo_uri("cpanel_email/list_data") ?>',
            columns: [
                {title: '<?php echo app_lang("email"); ?>'},
                {title: '<?php echo app_lang("domain"); ?>'},
                {title: '<?php echo app_lang("cpanel_email_disk_usage"); ?>'},
                {title: '<?php echo app_lang("cpanel_email_login"); ?>', "class": "text-center"},
                {title: '<?php echo app_lang("cpanel_email_incoming_mail"); ?>', "class": "text-center"},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ]
        });
    });
</script>
