<?php echo form_open(get_uri("cpanel_email/save_rename"), array("id" => "cpanel-email-rename-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="token" value="<?php echo $token; ?>" />

        <div class="alert alert-warning">
            <i data-feather="alert-triangle" class="icon-16"></i> <?php echo app_lang("cpanel_email_rename_help_message"); ?>
        </div>

        <div class="form-group">
            <div class="row">
                <label class="col-md-3"><?php echo app_lang('cpanel_email_current_username'); ?></label>
                <div class="col-md-9">
                    <p class="form-control-static"><b><?php echo $email; ?></b></p>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="new_user" class="col-md-3"><?php echo app_lang('cpanel_email_new_username'); ?></label>
                <div class="col-md-9">
                    <div class="input-group">
                        <?php
                        echo form_input(array(
                            "id" => "new_user",
                            "name" => "new_user",
                            "value" => "",
                            "class" => "form-control",
                            "placeholder" => app_lang('cpanel_email_username_local_part'),
                            "autofocus" => true,
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                        <span class="input-group-text">@<?php echo $domain; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="password" class="col-md-3"><?php echo app_lang('cpanel_email_new_password'); ?></label>
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
                                    "placeholder" => app_lang('cpanel_email_new_password'),
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
                <div class="col-md-9 col-md-offset-3">
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" id="keep_old_forwarding" name="keep_old_forwarding" value="1" checked="checked">
                        <label for="keep_old_forwarding"><?php echo app_lang('cpanel_email_keep_old_forwarding'); ?></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-9 col-md-offset-3">
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" id="delete_old_account" name="delete_old_account" value="1">
                        <label for="delete_old_account"><?php echo app_lang('cpanel_email_delete_old_account'); ?></label>
                    </div>
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
        $("#cpanel-email-rename-form").appForm({
            onSuccess: function (result) {
                if (result.reload) {
                    $("#cpanel-email-table").appTable({reload: true});
                }
            }
        });

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
    });
</script>
