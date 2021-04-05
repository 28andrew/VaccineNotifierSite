<?php


namespace VaccineNotifier;


use Exception;

class Config {
    public static function get($path) {
        if(!@include($_SERVER["DOCUMENT_ROOT"] . "/config.php")) throw new Exception("Missing config.php. Copy config.example.php to config.php.");
        if (!@$config) throw new Exception("Missing $config object in config.php");

        $arr = $config;
        $keys = explode(".", $path);
        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }
        return $arr;
    }

    public static function exists($path): bool {
        return !empty(self::get($path));
    }
}