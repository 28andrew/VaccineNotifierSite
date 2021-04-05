<?php


namespace VaccineNotifier;


class VaccineTypes {
    public static string $NAME_OF_ALL = 'All';
    public static array $ALL_TYPES = [
        'All',
        'Johnson & Johnson',
        'Moderna',
        'Pfizer'
    ];

    public static $mappingToAPI = [
        //https://github.com/GUI/covid-vaccine-spotter/blob/b0098a1354d5da700e54934e2793fc0c8dc9ea38/src/normalizedVaccineTypes.js
        "Johnson & Johnson" => 'jj',
        'Moderna' => 'moderna',
        "Pfizer" => 'pfizer'
    ];

    public static function getSelectorArray() : array {
        $array = array();
        foreach (self::$ALL_TYPES as $value) {
            $array[$value] = $value;
        }
        return $array;
    }
}