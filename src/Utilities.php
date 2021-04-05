<?php


namespace VaccineNotifier;


class Utilities {
    public static function getIP() {
        if (array_key_exists('HTTP_CF_CONNECTING_IP', $_SERVER)) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        $keys = ['HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR', 'HTTP_CLIENT_IP'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                return $_SERVER[$key];
            }
        }
        return null;
    }

    public static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public static function generateToken($length = 32): string {
        return bin2hex(random_bytes($length));
    }

    public static function cutToLength($str, $length) {
        if (strlen($str) > $length) {
            return substr($str, 0, $length);
        }
        return $str;
    }

    public static function deleteCookie($cookie) {
        setcookie($cookie, '', TimeHelper::getUTCTimestamp() - 3600);
    }

    public static function redirect($url) {
        header("Location: $url");
        exit();
    }

    public static function capitalizeName($name): string {
        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    public static function ellipsis($string, $length): string {
        if (strlen($string) > $length) {
            return substr($string, 0, $length,) . '...';
        }
        return $string;
    }

    public static function isInt(string $string) {
        return preg_match("/^(-)?\d+$/m", $string);
    }

    public static function getVarDump($var) {
        ob_start();
        var_dump($var);
        return ob_get_clean();
    }

    /**
     * From https://stackoverflow.com/a/14751773
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public static function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    public static function haversineMiles($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo) {
        return self::haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo) / 1609.34;
    }
}