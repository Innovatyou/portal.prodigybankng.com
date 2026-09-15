<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "cpanel_email_forwarders";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="card">
                <div class="page-title clearfix">
                    <h4 class="mt15"><?php echo app_lang('cpanel_email_forwarders'); ?></h4>
                    <div class="title-button-group">
                        <?php echo modal_anchor(get_uri("cpanel_email/forwarder_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('cpanel_email_add_forwarder'), array("class" => "btn btn-default", "title" => app_lang('cpanel_email_add_forwarder'))); ?>
                    </div>
                </div>

                <p class="text-muted mt10"><?php echo app_lang("cpanel_email_forwarders_help_message"); ?></p>

                <?php if (!(get_cpanel_email_setting("cpanel_email_host") && get_cpanel_email_setting("cpanel_email_username") && get_cpanel_email_setting("cpanel_email_api_token"))) { ?>
                    <div class="alert alert-warning mt15">
                        <i data-feather="alert-triangle" class="icon-16"></i>
                        <?php echo app_lang("cpanel_email_not_configured_help_message"); ?>
                    </div>
                <?php } ?>

                <div class="table-responsive">
                    <table id="cpanel-email-forwarders-table" class="display" cellspacing="0" width="100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    "use strict";

    $(document).ready(function () {
        $("#cpanel-email-forwarders-table").appTable({
            source: '<?php echo_uri("cpanel_email/forwarders_list_data") ?>',
            columns: [
                {title: '<?php echo app_lang("cpanel_email_forward_from"); ?>'},
                {title: '<?php echo app_lang("cpanel_email_forward_to"); ?>'},
                {title: '<?php echo app_lang("domain"); ?>'},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ]
        });
    });
</script>
