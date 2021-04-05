<?php

include __DIR__ . '/../vendor/autoload.php';

use Formr\Formr;
use VaccineNotifier\Config;
use VaccineNotifier\Email;
use VaccineNotifier\Template;
use VaccineNotifier\UserDatabase;

$error = false;
$message = '';

$fromEmail = isset($_GET['token']);

$id = null;
$token = null;

if ($fromEmail) {
    $token = $_GET['token'];
    $id = UserDatabase::getIdFromPasswordResetToken($token);
    if (!$id) {
        $message = "Invalid token.";
        $error = true;
    }
}

function verifyEmailForm(Formr $form) {
    $email = $form->post('email', 'Email', 'email');
    if (!$form->errors()) {
        $id = UserDatabase::getIdByEmail($email);
        if ($id) {
            $token = UserDatabase::createPasswordResetToken($id);
            $link = Config::get('base_url') . "/login/reset.php?token=$token";
            Email::sendHTMLMail($email, 'Password Reset',
                'Please use the link below to reset your password: <br>'
                . "<a href='$link'>$link</a>",
                "Please use the link to reset your password: $link");
        }
        $form->info_message("Sent an email for password reset if an account is associated with it.");
    }
}

function verifyPasswordForm(Formr $form) {
    global $id, $token;
    $password = $form->post('password','Password','min_length[8]|max_length[64]');
    $form->post('confirm_password', 'Password Confirmation', 'matches[password]');
    if (!$form->errors()) {
        UserDatabase::updatePassword($id, $password);
        UserDatabase::deletePasswordResetToken($token);
        $form->info_message("Updated password.");
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('Vaccine Notifier - Password Reset');
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
            <br>
            <div class="text-center">
                <?php
                if ($error) {
                ?>
                <div class="alert alert-danger" role="alert">
                    <?=$message?>
                </div>
                <?php
                } else {
                    ?>
                <div class="card">
                    <div class="card-body">
                    <?php
                    if (!$fromEmail) {
                        // Ask for email
                        $emailForm = new Formr('bootstrap');
                        $emailForm->action = "/login/reset.php";

                        if ($emailForm->submitted()) {
                            verifyEmailForm($emailForm);
                        }

                        echo $emailForm->messages();
                        echo $emailForm->form_open();
                        ?>
                        <div class="form-group col-md-12">
                            <?=$emailForm->input_email('email', 'Email Address')?>
                            <p class="form-text text-muted text-left">
                                If there's an account associated with this email address, a link to reset the password will be sent to it.
                            </p>
                        </div>
                        <div id="_submit" class="form-group">
                            <label class="sr-only" for="submit">Reset Password</label>
                            <input type="submit" name="submit" value="Reset Password" id="submit" class="btn btn-primary btn-block">
                        </div>
                        <?=$emailForm->form_close()?>
                        <?php
                    } else {
                        // Link from email, ask for new password
                        $passwordForm = new Formr('bootstrap');
                        $passwordForm->action = "/login/reset.php?token=" . $token;

                        if ($passwordForm->submitted()) {
                            verifyPasswordForm($passwordForm);
                        }

                        echo $passwordForm->messages();
                        echo $passwordForm->form_open();
                        ?>
                        <div class="form-group col-md-12">
                            <?=$passwordForm->input_password('password', 'Password')?>
                        </div>
                        <div class="form-group col-md-12">
                            <?=$passwordForm->input_password('confirm_password', 'Password Confirm')?>
                        </div>
                        <div id="_submit" class="form-group">
                            <label class="sr-only" for="submit">Reset Password</label>
                            <input type="submit" name="submit" value="Reset Password" id="submit" class="btn btn-primary btn-block">
                        </div>
                        <?=$passwordForm->form_close()?>
                        <?php
                    }
                }
                    ?>
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