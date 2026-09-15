<?php

namespace CpanelEmail\Config;

use CodeIgniter\Events\Events;

Events::on('pre_system', function () {
    helper("cpanel_email_general");
});
