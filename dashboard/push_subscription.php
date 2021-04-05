<?php
include __DIR__ . '/../vendor/autoload.php';

use VaccineNotifier\Database;
use VaccineNotifier\UserDatabase;

Database::connect();

$_USER = UserDatabase::getUserFromBrowser();

if (!isset($_USER)) {
    http_response_code(403);
    return;
}

$json = file_get_contents('php://input');
$data = json_decode($json);
$endpoint = $data->endpoint;
$publicKey = $data->publicKey;
$authToken = $data->authToken;

UserDatabase::addPushNotificationBrowser($_USER, $endpoint, $publicKey, $authToken);
