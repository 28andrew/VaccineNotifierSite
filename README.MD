# Vaccine Notifier

[Vaccine Notifier](https://vaccine.andrewtran.xyz) is a quick site I threw together that provides notifications for vaccine availability information provided by the service [Vaccine Spotter](https://www.vaccinespotter.org/). I would like to rewrite this website in something more modern like Node sometime.

# Self-Host Usage
- A web server with PHP is required. A MySQL database will also be required. Ensure PHP >= 7.4 is installed.
- You will also need credentials to an email account, a Google Maps API key, and a [VAPID key](https://tools.reactpwa.com/vapid) 
- Run `composer install` to install dependencies
- Prevent web access to `src/`, `composer.json`, `composer.lock`, `vendor`, `README.MD`, `.git`, `.idea` and `.gitignore` in your web server configuration (or make sure these are not uploaded to your web root).
- Run `src/vaccine.sql` in your MySQL server to generate the `vaccine` database
- Copy and fill in `config.example.php`, saving to `config.php`
- Setup a cronjob to access `run.php` at your desired frequency. Ensure `self_ip` is correct in `config.php` so that `run.php` will not reject your request. Below is an example for every minute:
```
* * * * * curl https://vaccine.andrewtran.xyz/run.php
```