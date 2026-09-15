<?php echo form_open(get_uri("cpanel_email/save_settings"), array("id" => "cpanel-email-settings-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <div class="form-group">
            <div class="row">
                <label class="col-md-12">
                    <?php echo app_lang("cpanel_email_settings_help_message"); ?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="cpanel_email_host" class="col-md-3"><?php echo app_lang('cpanel_email_host'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "cpanel_email_host",
                        "name" => "cpanel_email_host",
                        "value" => get_cpanel_email_setting("cpanel_email_host"),
                        "class" => "form-control",
                        "placeholder" => "server.example.com",
                        "autofocus" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="cpanel_email_port" class="col-md-3"><?php echo app_lang('cpanel_email_port'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "cpanel_email_port",
                        "name" => "cpanel_email_port",
                        "value" => get_cpanel_email_setting("cpanel_email_port") ? get_cpanel_email_setting("cpanel_email_port") : "2083",
                        "class" => "form-control",
                        "placeholder" => "2083",
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="cpanel_email_username" class="col-md-3"><?php echo app_lang('cpanel_email_username'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "cpanel_email_username",
                        "name" => "cpanel_email_username",
                        "value" => get_cpanel_email_setting("cpanel_email_username"),
                        "class" => "form-control",
                        "placeholder" => app_lang('cpanel_email_username'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="cpanel_email_api_token" class="col-md-3"><?php echo app_lang('cpanel_email_api_token'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_password(array(
                        "id" => "cpanel_email_api_token",
                        "name" => "cpanel_email_api_token",
                        "value" => get_cpanel_email_setting("cpanel_email_api_token") ? "******" : "",
                        "class" => "form-control",
                        "placeholder" => app_lang('cpanel_email_api_token'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                    <span class="mt10 d-inline-block"><i data-feather='info' class="icon-16"></i> <?php echo app_lang("cpanel_email_api_token_help_message"); ?></span>
                </div>
            </div>
        </div>

        <div class="form-group form-switch">
            <div class="row">
                <label for="cpanel_email_verify_ssl" class="col-md-3"><?php echo app_lang('cpanel_email_verify_ssl'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_checkbox("cpanel_email_verify_ssl", "1", get_cpanel_email_setting("cpanel_email_verify_ssl") !== "0", "id='cpanel_email_verify_ssl' class='form-check-input'");
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-9 offset-md-3">
                    <button type="button" id="cpanel_email_test_connection" class="btn btn-default"><i data-feather="wifi" class="icon-16"></i> <?php echo app_lang('cpanel_email_test_connection'); ?></button>
                    <span id="cpanel_email_test_connection_result"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    "use strict";

    $(document).ready(function () {
        $("#cpanel-email-settings-form").appForm();

        $("#cpanel_email_test_connection").click(function () {
            var $btn = $(this),
                $result = $("#cpanel_email_test_connection_result");

            $btn.addClass("spinning").prop("disabled", true);
            $result.html("");

            appAjaxRequest({
                url: '<?php echo_uri("cpanel_email/test_connection") ?>',
                type: "POST",
                dataType: "json",
                data: $("#cpanel-email-settings-form").serialize(),
                success: function (response) {
                    $btn.removeClass("spinning").prop("disabled", false);
                    if (response.success) {
                        $result.html("<span class='text-success'><i data-feather='check-circle' class='icon-16'></i> " + response.message + "</span>");
                    } else {
                        $result.html("<span class='text-danger'><i data-feather='alert-triangle' class='icon-16'></i> " + response.message + "</span>");
                    }
                    feather.replace();
                },
                error: function () {
                    $btn.removeClass("spinning").prop("disabled", false);
                    $result.html("<span class='text-danger'>" + AppLanguage.somethingWentWrong + "</span>");
                }
            });
        });
    });
</script>
