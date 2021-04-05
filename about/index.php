<?php

use VaccineNotifier\Template;
use VaccineNotifier\UserDatabase;

include __DIR__ . '/../vendor/autoload.php';

$_USER = UserDatabase::getUserFromBrowser();

?>
<!doctype html>
<html lang="en">
<head>
    <?php
    Template::head('Vaccine Notifier - About')
    ?>
</head>
<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false, 'about');
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container">
            <br>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Purpose</h2>
                    <p class="card-text">I created this website to help those all over the US find vaccine appointments. I found <a href="https://www.vaccinespotter.org/">Vaccine Spotter</a> very helpful in finding appointments for relatives but it lacked a notification system, so my goal is to fill that gap. </p>
                </div>
            </div>
            <br>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">About Me - Andrew Tran</h2>
                    <p class="card-text">I'm a high schooler that's very interested in programming and computer science in general.</p>
                    <br>
                    <p class="card-text">Email: <a href="mailto:andrewtran312@gmail.com">andrewtran312@gmail.com</a></p>
                    <p class="card-text">LinkedIn: <a href="//www.linkedin.com/in/andrew-tran-mn/">andrew-tran-mn</a></p>
                </div>
            </div>
            <br>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Source Code</h2>
                    <p class="card-text">All the code to this site is available on <a href="//github.com/28andrew/VaccineNotifierSite">Github</a>. Contributions are welcome!</p>
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