<?php echo form_open(get_uri("cpanel_email/save_quota"), array("id" => "cpanel-email-quota-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="token" value="<?php echo $token; ?>" />
        <div class="form-group">
            <div class="row">
                <label class="col-md-3"><?php echo app_lang('email'); ?></label>
                <div class="col-md-9">
                    <p class="form-control-static"><b><?php echo $email; ?></b></p>
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
                        "value" => "",
                        "class" => "form-control",
                        "placeholder" => app_lang('cpanel_email_quota_mb'),
                        "autofocus" => true,
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
        $("#cpanel-email-quota-form").appForm();
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
