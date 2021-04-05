<?php


namespace VaccineNotifier;


class SMS {
    public static string $DISABLED_NAME = "Disabled";

    public static array $CARRIER_EMAILS = [
        'T-Mobile' => 'tmomail.net',
        'AT&T' => 'txt.att.net',
        'Sprint' => 'messaging.sprintpcs.com',
        'Verizon' => 'vtext.com',
        'Virgin Mobile' => 'vmobl.com',
        'Tracfone' => 'mmst5.tracfone.com',
        'Ting' => 'message.ting.com',
        'Boost Mobile' => 'myboostmobile.com',
        'U.S Cellular' => 'email.uscc.net',
        'Metro PCS' => 'mymetropcs.com'
    ];

    public static function getSelectorArray(): array {
        $array = array();
        $array[self::$DISABLED_NAME] = self::$DISABLED_NAME;
        foreach (self::$CARRIER_EMAILS as $key => $value) {
            $array[$key] = $key;
        }
        return $array;
    }

    public static function sendText($carrier, $phone, $text) {
        $domain = self::$CARRIER_EMAILS[$carrier];
        $recipient = $phone . '@' . $domain;
        Email::sendMail($recipient, '', $text);
    }
}