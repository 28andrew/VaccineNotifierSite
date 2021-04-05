<?php
include __DIR__ . '/../vendor/autoload.php';

use Formr\Formr;
use VaccineNotifier\Config;
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

    $email = $form->post('identifier', 'Email');
    $password = $form->post('password', 'Password');

    $user = UserDatabase::getUserFromLogin($email, $password);
    if ($user == null) {
        $form->error_message('Invalid credentials ' . $user);
        return;
    }

    if (!$user->verified) {
        UserDatabase::sendConfirmationEmail($user->id, $user->email);
        $form->error_message('You have not verified your account yet. Another verification email with 
        a link has been sent to ' . $user->email . '. If this is the wrong email, simply register again. CHECK YOUR SPAM!');
        return;
    }

    if (!$form->errors() && !$error) {
        $token = UserDatabase::createToken($user->id);
        setcookie('login_token', $token, TimeHelper::getUTCTimestamp() + UserDatabase::$LOGIN_EXPIRATION_SECONDS, '/');
        $form->success_message('You have successfully logged in.');
        Utilities::redirect(Config::get('base_url') . '/dashboard/');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('Vaccine Notifier - Login');
    ?>
    <style>
    .login-container {
        max-width: 700px;
    }
    </style>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER);
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container login-container">
            <div class="text-center">
                <h1 class="display-4 d-none d-lg-block">Login</h1>
                <h1 class="d-lg-none d-xl-none">Login</h1>
                <div class="card">
                    <div class="card-body">
                        <?php
                        $form = new Formr('bootstrap');
                        $form->action = "./";
                        $form->required = '*';

                        if ($form->submitted()) {
                            verify_form($form);
                        }

                        echo $form->messages();
                        echo $form->form_open();
                        ?>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <?= $form->input_text('identifier', 'Username/Email') ?>
                            </div>
                            <div class="form-group col-md-12">
                                <?= $form->input_password('password', 'Password') ?>
                                <a class="form-text text-left" href="reset.php">Forgot Password?</a>
                            </div>
                        </div>
                        <div id="_submit" class="form-group">
                            <label class="sr-only" for="submit">Login</label>
                            <input type="submit" name="submit" value="Login" id="submit" class="btn btn-primary btn-block">
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