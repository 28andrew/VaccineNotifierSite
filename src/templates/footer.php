<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<?php
foreach($scripts as $script) {
    ?>
    <script src="<?=$script?>"></script>
    <?php
}
?>
<footer class="text-muted">
    <div class="container">
        <br>
        <p class="float-left">Website created by <a href="/about/">Andrew Tran</a></p>
        <p class="float-right"><a href="//paypal.me/andrewtran28">Help with website hosting costs or buy me a coffee.</a> <br>Also consider donating to <a href="https://www.vaccinespotter.org/#donate" target="_blank">Vaccine Spotter</a> and the charities that it lists.</p>
    </div>
</footer>
