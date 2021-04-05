<?php

include __DIR__ . '/../vendor/autoload.php';

use VaccineNotifier\Template;
use VaccineNotifier\UserDatabase;

$message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    if ($email = UserDatabase::verifyWithToken($token)) {
        $message = "Successfully verified your account, $email. You may now login to access your account.";
    } else {
        $message = 'An invalid token has been provided or the account has already been verified.';
    }
} else {
    $message = 'No token provided.';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('Vaccine Notifier - Verify Email');
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar(null);
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container">
            <div class="text-center">
                <div class="card">
                    <div class="card-body">
                        <p class="card-text"><?=$message?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    Template::footer();
    ?>
</div>
</body>
</html>