<?php

/* Don't change or add any new config in this file */

namespace CpanelEmail\Config;

use CodeIgniter\Config\BaseConfig;
use CpanelEmail\Models\Cpanel_email_settings_model;

class CpanelEmail extends BaseConfig {

    public $app_settings_array = array(
        "cpanel_email_port" => "2083",
        "cpanel_email_verify_ssl" => "1",
    );

    public function __construct() {
        $settings_model = new Cpanel_email_settings_model();

        $settings = $settings_model->get_all_settings()->getResult();
        foreach ($settings as $setting) {
            $this->app_settings_array[$setting->setting_name] = $setting->setting_value;
        }
    }

}
