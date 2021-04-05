<?php

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use VaccineNotifier\Config;
use VaccineNotifier\Database;
use VaccineNotifier\Email;
use VaccineNotifier\SMS;
use VaccineNotifier\TimeHelper;
use VaccineNotifier\UserDatabase;
use VaccineNotifier\UserPreferences;
use VaccineNotifier\Utilities;
use VaccineNotifier\VaccineLocation;
use VaccineNotifier\VaccineTypes;

include __DIR__ . '/vendor/autoload.php';

$ip = Utilities::getIP();
if ($ip !== Config::get('self_ip')) {
    http_response_code(403);
    return;
}

// Find states of users
Database::connect();
$states = DB::queryFirstColumn("SELECT DISTINCT `state` FROM `users`");

// Iterate states
foreach ($states as $state) {
    $userIds = DB::queryFirstColumn("SELECT `id` FROM `users` WHERE `state`=%s", $state);
    $userPreferences = array();
    foreach($userIds as $id) {
        $userPreferences[$id] = UserDatabase::getPreferencesById($id);
    }

    $url = "https://www.vaccinespotter.org/api/v0/states/$state.json";
    $json = json_decode(file_get_contents($url));

    $locationsWithVaccine = array();
    foreach ($json->features as $feature) {
        if ($feature->properties->appointments_available === true) {
            $location = VaccineLocation::fromFeature($feature);
            array_push($locationsWithVaccine, $location);
        }
    }

    //var_dump($locationsWithVaccine);

    // Loop through every user & notify as needed
    foreach($userIds as $userId) {
        try {
            $preferences = $userPreferences[$userId];
            // Skip user if their notifications are not enabled
            if (!$preferences->enabled) {
                continue;
            }

            $locationsSorted = array();
            $locationIdToDistance = array();

            foreach($locationsWithVaccine as $location) {
                // Radius check
                $needToCheckRadius = $preferences->radius_miles > 0;
                $distanceMiles = Utilities::haversineMiles($preferences->latitude, $preferences->longitude, $location->latitude, $location->longitude);
                if ($needToCheckRadius) {
                    $radiusMiles = $preferences->radius_miles;
                    if ($distanceMiles > $radiusMiles) {
                        //echo "radius fail for" . $location->id;
                        continue;
                    }
                }
                // Vaccine Type
                $needToCheckType = $preferences->vaccine_type !== VaccineTypes::$NAME_OF_ALL;
                if ($needToCheckType) {
                    if (!in_array($preferences->vaccine_type, $location->vaccine_types)) {
                        //echo "type fail for" . $location->id;
                        continue;
                    }
                }
                // Filter out recent notifications
                $repeatSeconds = $preferences->repeat_seconds;
                $lastNotification = UserDatabase::getLastNotificationTime($userId, $location->id);
                if ($lastNotification && (TimeHelper::getUTCTimestamp() - $lastNotification < $repeatSeconds)) {
                    //echo "last notification fail for" . $location->id;
                    // Last notification sent too soon, do not send right now
                    continue;
                }
                // All good, put in array
                array_push($locationsSorted, $location);
                $locationIdToDistance[$location->id] = $distanceMiles;
            }
            // Skip if there's no locations
            if (sizeof($locationsSorted) == 0) {
                continue;
            }
            // Sort locations by closest to furthest
            usort($locationsSorted, function(VaccineLocation $a, VaccineLocation $b) {
                global $locationIdToDistance;
                return $locationIdToDistance[$a->id] - $locationIdToDistance[$b->id];
            });
            // Send notifications
            sendNotifications($userId, $preferences, $locationsSorted, $locationIdToDistance, $state);
        } catch (Exception $e) {
            error_log("For user id " . $userId . ", we got error: " . $e);
        }
    }

}

echo "Done.";

function sendNotifications($userId, UserPreferences $preferences, array $locations, array $locationIdToDistance, $state) {
    $timestamp = TimeHelper::getUTCTimestamp();
    foreach ($locations as $location) { // todo: change to one large INSERT
        UserDatabase::setLastNotificationTime($userId, $location->id, $timestamp);
    }
    // Email
    $email = UserDatabase::getUserFromId($userId)->email;
    sendEmailNotification($email, $locations, $locationIdToDistance, $state);
    // SMS
    $carrier = $preferences->carrier;
    if ($carrier !== SMS::$DISABLED_NAME) {
        sendTextNotification($carrier, $preferences->phone, $locations, $locationIdToDistance, $state);
    }
    // Browser
    $browsers = UserDatabase::getPushNotificationBrowers($userId);
    if (!empty($browsers)) {
        sendBrowserNotification($browsers, $locations, $locationIdToDistance, $state);
    }
}

function sendEmailNotification($email, $locations, $locationIdToDistance, $state) {
    $emailTitle = "Vaccine Notification";
    $emailBodyNormal = "There are some available vaccine appointment(s).\n\n";
    $emailBodyHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Vaccine Notification</title>
</head>
<body>
    <p>There are some available vaccine appointment(s):</p>
    <ol>
HTML;
    foreach ($locations as $location) {
        $name = $location->getHumanName($state);
        $distance = round($locationIdToDistance[$location->id], 2);
        $urlEscaped = htmlspecialchars($location->url);

        $emailBodyNormal .= "- $name ($distance miles) $location->url\n";
        $emailBodyHtml .= "<li><a href=\"$urlEscaped\">$name</a> ($distance miles)</li>";
    }
    $manageUrl = Config::get('base_url') . '/dashboard/';
    $manageUrlEscaped = htmlspecialchars($manageUrl);
    $emailBodyNormal .= "You may manage your notification preferences at $manageUrl";
    $emailBodyHtml .= "</ol><p>You may manage your notification preferences in <a href=\"$manageUrlEscaped\">your dashboard</a>. </p></body></html>";
    Email::sendHTMLMail($email, $emailTitle, $emailBodyHtml, $emailBodyNormal,
        Config::get('email.reply_to.email'), Config::get('email.reply_to.name'));
}

function sendTextNotification($carrier, $phone, $locations, $locationIdToDistance, $state) {
    $text = "Vaccines found at:\n";
    foreach ($locations as $location) {
        $name = $location->getHumanName($state);
        $distance = round($locationIdToDistance[$location->id], 2);
        $url = $location->url;
        $text .= "- $name ($distance miles) $url\n";
    }
    $manageUrl = Config::get('base_url') . '/dashboard/';
    $text .= "Manage notifications at $manageUrl";
    SMS::sendText($carrier, $phone, $text);
}

function sendBrowserNotification($browsers, $locations, $locationIdToDistance, $state) {
    $auth = [
        'VAPID' => [
            'subject' => Config::get('web_push.subject'),
            'publicKey' => Config::get('web_push.public_key'),
            'privateKey' => Config::get('web_push.private_key')
        ]
    ];
    $webPush = new WebPush($auth);
    $time = TimeHelper::getUTCTimestamp();
    foreach ($locations as $location) {
        $name = $location->getHumanName($state);
        $distance = round($locationIdToDistance[$location->id], 2);
        $title = "Vaccines Available at $name";
        $body = "Vaccines found at $name. It is $distance miles away.";
        foreach ($browsers as $browserInfo) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $browserInfo['endpoint'],
                    'publicKey' => $browserInfo['public_key'],
                    'authToken' => $browserInfo['auth_token']
                ]),
                json_encode([
                    'title' => $title,
                    'body' => $body,
                    'tag' => $location->id . $time,
                    'url' => $location->url
                ])
            );
        }
    }

    foreach ($webPush->flush() as $report) {
        $endpoint = $report->getRequest()->getUri()->__toString();
        if (!$report->isSuccess()) {
            $reason = $report->getReason();
            if (strpos($reason, "410 Gone") !== false) {
                error_log("Failed to sent browser notification for endpoint $endpoint: {}");
            }
        }
    }
}