<?php

use VaccineNotifier\Template;
use VaccineNotifier\UserDatabase;

include __DIR__ . '/vendor/autoload.php';

$_USER = UserDatabase::getUserFromBrowser();

?>
<!doctype html>
<html lang="en">
<head>
    <?php
    Template::head('Vaccine Notifier')
    ?>
</head>
<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false, 'home');
        ?>
    </nav>
    <main class="flex-fill">
        <div class="px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
            <h1 class="display-1 d-none d-lg-block">Vaccine Notifier</h1>
            <h1 class="d-lg-none d-xl-none">Vaccine Notifier</h1>
            <h2 class="font-weight-lighter">A website that notifies you of vaccine appointments utilizing data from <a href="https://www.vaccinespotter.org/">Vaccine Spotter</a>.</h2>
            <br>
            <div class="container main-container">
                <div class="text-center">
                    <?php
                    if (!isset($_USER)) {
                    ?>
                    <a href="/register/" class="btn btn-secondary btn-lg active" role="button">Register for Notifications</a>
                    <?php
                    } else {
                    ?>
                    <a href="/dashboard/" class="btn btn-primary btn-lg active" role="button">Access Dashboard</a>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
</div>
<?php
Template::footer();
?>
</body>
</html>