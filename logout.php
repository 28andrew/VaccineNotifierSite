<?php

include __DIR__ . '/vendor/autoload.php';

use VaccineNotifier\Config;
use VaccineNotifier\UserDatabase;
use VaccineNotifier\Utilities;

if (isset($_COOKIE['login_token'])) {
    UserDatabase::deleteToken($_COOKIE['login_token']);
}
Utilities::deleteCookie('login_token');
Utilities::redirect(Config::get('base_url'));