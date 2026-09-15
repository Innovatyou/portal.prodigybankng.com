<?php

namespace Config;

$routes = Services::routes();

$routes->get('cpanel_email', 'Cpanel_email::index', ['namespace' => 'CpanelEmail\Controllers']);
$routes->get('cpanel_email/(:segment)', 'Cpanel_email::$1', ['namespace' => 'CpanelEmail\Controllers']);
$routes->add('cpanel_email/(:segment)', 'Cpanel_email::$1', ['namespace' => 'CpanelEmail\Controllers']);
$routes->get('cpanel_email/(:segment)/(:any)', 'Cpanel_email::$1/$2', ['namespace' => 'CpanelEmail\Controllers']);
$routes->add('cpanel_email/(:segment)/(:any)', 'Cpanel_email::$1/$2', ['namespace' => 'CpanelEmail\Controllers']);
