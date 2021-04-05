<?php


namespace VaccineNotifier;


use DateTime;
use DateTimeZone;

class TimeHelper {

    /**
     * @return int UTC timestamp in seconds
     */
    public static function getUTCTimestamp(): int {
        return self::getTimestamp('UTC');
    }

    public static function getTimestamp($timezone): int {
        $now = new DateTime('now', new DateTimeZone($timezone));
        return $now->getTimestamp();
    }
}