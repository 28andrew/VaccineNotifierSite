<?php


namespace VaccineNotifier;


class Template {
    public static function head($title = '', $stylesheets = []) {
        include 'templates/head.php';
    }

    public static function footer($scripts = []) {
        include 'templates/footer.php';
    }

    public static function navbar($_USER, $hideTopRight = false, $active = '') {
        include 'templates/navbar.php';
    }

    public static function breadcrumb($linkArray) {
        ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <?php
                foreach ($linkArray as $name => $link) {
                    if ($name !== array_key_last($linkArray)) {
                        ?>
                        <li class="breadcrumb-item"><a href="<?=$link?>"><?=$name?></a></li>
                        <?php
                    } else {
                        ?>
                        <li class="breadcrumb-item active" aria-current="page"><?=$link?></li>
                        <?php
                    }
                    ?>
                    <?php
                }
                ?>
            </ol>
        </nav>
        <?php
    }
}