<?php

namespace CpanelEmail\Libraries;

class Cpanel_email_permissions {
    public static function can_manage($user): bool {
        return $user->user_type === 'staff' && (
            !empty($user->is_admin)
            || !empty($user->permissions['can_manage_all_kinds_of_settings'])
            || ($user->permissions['can_manage_cpanel_email'] ?? '') === '1'
        );
    }
}
