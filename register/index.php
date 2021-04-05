<?php
include __DIR__ . '/../vendor/autoload.php';

use Formr\Formr;
use VaccineNotifier\Config;
use VaccineNotifier\Geography;
use VaccineNotifier\Template;
use VaccineNotifier\TimeHelper;
use VaccineNotifier\UserDatabase;
use VaccineNotifier\Utilities;


$_USER = UserDatabase::getUserFromBrowser();

if (isset($_USER)) {
    Utilities::redirect(Config::get('base_url'));
}

function verify_form(Formr $form) {
    $error = false;

    $email = $form->post('email', 'Email', 'valid_email|sanitize_email');
    $form->post('confirm_email', 'Email Confirmation', 'matches[email]');
    if (UserDatabase::doesEmailExist($email)) {
        $form->error_message('Email is already in use');
        $error = true;
    }
    $password = $form->post('password','Password','min_length[8]|max_length[64]');
    $form->post('confirm_password', 'Password Confirmation', 'matches[password]');
    $state = $form->post('state', 'State');
    if (!in_array($state, array_keys(Geography::$states))) {
        $form->error_message('Invalid state');
        $error = true;
    }

    if (!$form->errors() && !$error) {
        $passwordHash = UserDatabase::hashPassword($password);

        $id = UserDatabase::createUser([
            'email' => $email,
            'password' => $passwordHash,
            'state' => $state,
            'register_date' => TimeHelper::getUTCTimestamp(),
            'signup_ip' => Utilities::getIP(),
            'verified' => false
        ]);

        UserDatabase::sendConfirmationEmail($id, $email);

        $form->success_message("Your account has been successfully created. Please verify by clicking the link in the verification email that has just been sent to $email. CHECK YOUR SPAM!! The email may take up to 30 minutes to arrive. If you would like for it to be resent, go to the login page and login.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('Vaccine Notifier - Register');
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER);
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container">
            <div class="text-center">
                <h1 class="display-4 d-none d-lg-block">Register</h1>
                <h1 class="d-lg-none d-xl-none">Register</h1>
                <div class="card">
                    <div class="card-body">
                        <?php
                        $form = new Formr('bootstrap');
                        $form->action = "./";

                        if ($form->submitted()) {
                            verify_form($form);
                        }

                        echo $form->messages();
                        echo $form->form_open();
                        ?>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <?=$form->input_email('email', 'Email Address')?>
                            </div>
                            <div class="form-group col-md-6">
                                <?= $form->input_email('confirm_email', 'Confirm Email Address') ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <?= $form->input_password('password', 'Password') ?>
                                <p class="form-text text-muted text-left">
                                    Must be at least 8 characters
                                </p>
                            </div>
                            <div class="form-group col-md-6">
                                <?= $form->input_password('confirm_password', 'Confirm Password') ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <?= $form->select('state', 'State', '', '', '', '', '', Geography::$states) ?>
                                <p class="form-text text-muted text-left">
                                    <b>Remember to select your state</b>
                                </p>
                            </div>
                        </div>
                        <div id="_submit" class="form-group">
                            <label class="sr-only" for="submit">Submit</label>
                            <input type="submit" name="submit" value="Submit" id="submit" class="btn btn-primary btn-block">
                        </div>
                        <?php
                        echo $form->form_close();
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