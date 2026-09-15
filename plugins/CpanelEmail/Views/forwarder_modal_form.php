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

<?php echo form_open(get_uri("cpanel_email/save_forwarder"), array("id" => "add-cpanel-email-forwarder-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <p class="text-muted"><?php echo app_lang("cpanel_email_forwarders_help_message"); ?></p>
        <div class="form-group">
            <div class="row">
                <label for="user" class="col-md-3"><?php echo app_lang('cpanel_email_forward_from'); ?></label>
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
                <label for="destination" class="col-md-3"><?php echo app_lang('cpanel_email_forward_to'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "destination",
                        "name" => "destination",
                        "type" => "email",
                        "value" => "",
                        "class" => "form-control",
                        "placeholder" => app_lang('cpanel_email_forward_to_placeholder'),
                        "data-rule-required" => true,
                        "data-rule-email" => true,
                        "data-msg-required" => app_lang("field_required"),
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
        $("#add-cpanel-email-forwarder-form").appForm({
            onSuccess: function (result) {
                $("#cpanel-email-forwarders-table").appTable({newData: result.data, dataId: result.id});
            }
        });

        $("#add-cpanel-email-forwarder-form .select2").select2();

        setTimeout(function () {
            $("#user").focus();
        }, 200);
    });
</script>
