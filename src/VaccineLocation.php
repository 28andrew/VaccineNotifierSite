<?php


namespace VaccineNotifier;


class VaccineLocation {
    public $id, $latitude, $longitude, $url, $address, $city, $name, $zip, $provider_name, $vaccine_types;

    public function __construct($id, $latitude, $longitude, $url, $address, $city, $name, $zip, $provider_name, $vaccine_types)
    {
        $this->id = $id;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->address = $address;
        $this->url = $url;
        $this->city = $city;
        $this->name = $name;
        $this->zip = $zip;
        $this->provider_name = $provider_name;
        $this->vaccine_types = $vaccine_types;
    }

    public function getHumanName($state): string {
        return $this->provider_name . ' ' . $this->name . " (" . $this->address . ', ' . $this->city . ', ' . $state . ' ' . $this->zip . ")";
    }

    public static function fromFeature($featureJSON): ?VaccineLocation {
        try {
            $id = $featureJSON->properties->id;
            $latitude = $featureJSON->geometry->coordinates[1];
            $longitude = $featureJSON->geometry->coordinates[0];
            $url = $featureJSON->properties->url;
            $address = $featureJSON->properties->address;
            $city = $featureJSON->properties->city;
            $name = $featureJSON->properties->name;
            $zip = $featureJSON->properties->postal_code;
            $provider_name = $featureJSON->properties->provider_brand_name;
            $vaccine_types = array();
            foreach (VaccineTypes::$mappingToAPI as $ourName => $apiName) {
                if (property_exists($featureJSON->properties->appointment_vaccine_types, $apiName)) {
                    array_push($vaccine_types, $ourName);
                }
            }

            return new VaccineLocation($id, $latitude, $longitude, $url, $address, $city, $name, $zip, $provider_name, $vaccine_types);

        } catch (\Exception $e) {
            return null;
        }
    }
}