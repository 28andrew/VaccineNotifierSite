<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="Providing vaccine availability notifications in the US, using data from Vaccine Spotter.">
<link rel="stylesheet" href="https://bootswatch.com/4/cosmo/bootstrap.min.css" crossorigin="anonymous">

<?php

use VaccineNotifier\Config;

foreach($stylesheets as $stylesheet) {
    ?>
    <link rel="stylesheet" href="<?=$stylesheet?>">
    <?php
}
?>

<title><?=$title?></title>

<?php
if (Config::exists('google_analytics_id')) {
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?=Config::get('google_analytics_id')?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', '<?=Config::get('google_analytics_id')?>');
</script>
<?php
}