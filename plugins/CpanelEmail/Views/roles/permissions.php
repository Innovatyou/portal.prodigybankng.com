<li>
    <span data-feather="mail" class="icon-14 ml-20"></span>
    <h5><?php echo app_lang('cpanel_email_accounts'); ?>:</h5>
    <div>
        <?php echo form_checkbox('can_manage_cpanel_email', '1', get_array_value($permissions, 'can_manage_cpanel_email') === '1', "id='can_manage_cpanel_email' class='form-check-input'"); ?>
        <label for="can_manage_cpanel_email"><?php echo app_lang('can_manage_cpanel_email'); ?></label>
    </div>
</li>
