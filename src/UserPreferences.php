<?php


namespace VaccineNotifier;


class UserPreferences {
    public $user, $latitude, $longitude, $radius_miles, $vaccine_type, $enabled, $carrier, $phone, $repeat_seconds;

    /**
     * UserPreferences constructor.
     * @param $user
     * @param $latitude
     * @param $longitude
     * @param $radius_miles
     * @param $vaccine_type
     * @param $enabled
     * @param $carrier
     * @param $phone
     * @param $repeat_seconds
     */
    public function __construct($user, $latitude, $longitude, $radius_miles, $vaccine_type, $enabled, $carrier, $phone, $repeat_seconds)
    {
        $this->user = $user;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->radius_miles = $radius_miles;
        $this->vaccine_type = $vaccine_type;
        $this->enabled = $enabled;
        $this->carrier = $carrier;
        $this->phone = $phone;
        $this->repeat_seconds = $repeat_seconds;
    }

    public static function createFromDatabase(User $user, $data) : UserPreferences {
        return new UserPreferences(
            $user,
            $data['latitude'] + 0, // + 0 to truncate zeroes
            $data['longitude'] + 0,
            $data['radius_miles'] + 0,
            $data['vaccine_type'],
            $data['enabled'],
            $data['carrier'],
            $data['phone'],
            $data['repeat_seconds']
        );
    }

    public static function getDefault(User $user) : UserPreferences {
        $coords = Geography::getStateCoordinates($user->state);
        return new UserPreferences(
            $user,
            $coords['latitude'],
            $coords['longitude'],
            -1,
            VaccineTypes::getSelectorArray()[VaccineTypes::$NAME_OF_ALL],
            false,
            SMS::getSelectorArray()[SMS::$DISABLED_NAME],
            '',
            7200
        );
    }
}