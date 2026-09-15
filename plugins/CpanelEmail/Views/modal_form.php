<?php if ($config_error) { ?>
    <div class="modal-body clearfix">
        <div class="alert alert-warning mt15">
            <i data-feather="alert-triangle" class="icon-16"></i> <?php echo $config_error; ?>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    </div>
    <?php return; ?>
<?php } ?>

<?php echo form_open(get_uri("cpanel_email/save"), array("id" => "add-cpanel-email-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <div class="form-group">
            <div class="row">
                <label for="user" class="col-md-3"><?php echo app_lang('cpanel_email_username_local_part'); ?></label>
                <div class="col-md-9">
                    <div class="input-group">
                        <?php
                        echo form_input(array(
                            "id" => "user",
                            "name" => "user",
                            "value" => "",
                            "class" => "form-control",
                            "placeholder" => app_lang('cpanel_email_username_local_part'),
                            "autofocus" => true,
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                        <span class="input-group-text">@</span>
                        <?php
                        $domain_options = array();
                        foreach ($domains as $domain) {
                            $domain_options[$domain] = $domain;
                        }
                        echo form_dropdown("domain", $domain_options, "", "class='select2' id='domain' data-rule-required='true' style='min-width:180px'");
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="password" class="col-md-3"><?php echo app_lang('password'); ?></label>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-8 pr0">
                            <div class="input-group">
                                <?php
                                echo form_input(array(
                                    "id" => "password",
                                    "name" => "password",
                                    "type" => "password",
                                    "value" => "",
                                    "class" => "form-control",
                                    "placeholder" => app_lang('password'),
                                    "autocomplete" => "off",
                                    "data-rule-required" => true,
                                    "data-rule-minlength" => 8,
                                    "data-msg-required" => app_lang("field_required"),
                                    "data-msg-minlength" => app_lang("cpanel_email_password_too_short"),
                                ));
                                ?>
                                <button type="button" class="input-group-text clickable no-border" id="generate_password"><span data-feather="key" class="icon-16"></span> <?php echo app_lang('generate'); ?></button>
                            </div>
                        </div>
                        <div class="col-md-1 p0">
                            <a href="#" id="show_hide_password" class="btn btn-default" title="<?php echo app_lang('show_text'); ?>"><span data-feather="eye" class="icon-16"></span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="quota" class="col-md-3">
                    <?php echo app_lang('cpanel_email_quota_mb'); ?>
                    <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cpanel_email_quota_help_message'); ?>"><i data-feather='help-circle' class="icon-16"></i></span>
                </label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "quota",
                        "name" => "quota",
                        "type" => "number",
                        "min" => "0",
                        "value" => "250",
                        "class" => "form-control",
                        "placeholder" => app_lang('cpanel_email_quota_mb'),
                    ));
                    ?>
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
        $("#add-cpanel-email-form").appForm({
            onSuccess: function (result) {
                $("#cpanel-email-table").appTable({newData: result.data, dataId: result.id});
            }
        });

        $("#add-cpanel-email-form .select2").select2();

        $('[data-bs-toggle="tooltip"]').tooltip();

        $("#generate_password").click(function () {
            $("#password").val(getRndomString(14));
        });

        $("#show_hide_password").click(function (e) {
            e.preventDefault();
            var $target = $("#password"),
                type = $target.attr("type");
            if (type === "password") {
                $(this).attr("title", "<?php echo app_lang("hide_text"); ?>");
                $(this).html("<span data-feather='eye-off' class='icon-16'></span>");
                feather.replace();
                $target.attr("type", "text");
            } else {
                $(this).attr("title", "<?php echo app_lang("show_text"); ?>");
                $(this).html("<span data-feather='eye' class='icon-16'></span>");
                feather.replace();
                $target.attr("type", "password");
            }
        });

        setTimeout(function () {
            $("#user").focus();
        }, 200);
    });
</script>
