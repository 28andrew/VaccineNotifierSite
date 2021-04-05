<?php
include __DIR__ . '/../vendor/autoload.php';

use Formr\Formr;
use VaccineNotifier\Config;
use VaccineNotifier\SMS;
use VaccineNotifier\Template;
use VaccineNotifier\UserDatabase;
use VaccineNotifier\Utilities;
use VaccineNotifier\VaccineTypes;

$_USER = UserDatabase::getUserFromBrowser();

if (!isset($_USER)) {
    Utilities::redirect(Config::get('base_url') . "/login");
}

$repeatMapping = [
    3600 => '1 hour',
    3600 * 2 => '2 hours',
    3600 * 4 => '4 hours',
    3600 * 8 => '8 hours',
    3600 * 24 => '24 hours'
];

$preferences = UserDatabase::getPreferences($_USER);

function verifyLocationForm(Formr $form) {
    global $_USER, $preferences;
    if (!isset($_POST['latitude'])) {
        return;
    }

    $error = false;
    $latitude = $form->post('latitude', 'Latitude', 'numeric');
    $longitude = $form->post('longitude', 'Longitude', 'numeric');
    $radiusMiles = $form->post('radius', 'Radius', 'numeric');
    $vaccineType = $form->post('vaccine_type');
    if (!in_array($vaccineType, VaccineTypes::getSelectorArray())) {
        $form->error_message("Invalid vaccine type");
        $error = true;
    }

    if (!$form->errors() && !$error) {
        UserDatabase::updatePreferences($_USER, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_miles' => $radiusMiles,
            'vaccine_type' => $vaccineType,
            'enabled' => $preferences->enabled,
            'carrier' => $preferences->carrier,
            'phone' => $preferences->phone,
            'repeat_seconds' => $preferences->repeat_seconds
        ]);
        $form->info_message("Saved search preferences successfully.");
        $preferences = UserDatabase::getPreferences($_USER);
    }
}

function verifyContactForm(Formr $form) {
    global $_USER, $preferences;
    if (!isset($_POST['carrier'])) {
        return;
    }

    $error = false;
    $enabled = isset($_POST['enabled']);
    $carrier = $form->post('carrier', 'Carrier');
    if (!in_array($carrier, SMS::getSelectorArray())) {
        $form->error_message("Invalid carrier");
        $error = true;
    }
    $phone = '';
    if ($carrier !== 'Disabled') {
        $phone = $form->post('phone', 'Phone');
        if (preg_match("/^[6-9][0-9]{9}$/", $phone) !== 1) {
            $form->error_message("Please provide a 10 digit phone number without any symbols.");
            $error = true;
        }
    }
    $repeat_seconds = $form->post('repeat_interval', 'Repeat Interval', 'int');

    if (!$form->errors() && !$error) {
        UserDatabase::updatePreferences($_USER, [
            'latitude' => $preferences->latitude,
            'longitude' => $preferences->longitude,
            'radius_miles' => $preferences->radius_miles,
            'vaccine_type' => $preferences->vaccine_type,
            'enabled' => $enabled,
            'carrier' => $carrier,
            'phone' => $phone,
            'repeat_seconds' => $repeat_seconds
        ]);
        $form->info_message("Saved contact preferences successfully.");
        $preferences = UserDatabase::getPreferences($_USER);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('Vaccine Notifier - Dashboard', ['/assets/css/dashboard.css']);
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false, 'dashboard');
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container">
            <div class="text-center">
                <br>
                <?php
                if (!$preferences->enabled) {
                    ?>
                <div class="alert alert-danger" role="alert">
                    Welcome! Notifications are not enabled unless you enable them below in contact preferences.
                </div>
                    <?php
                }
                ?>
                <div class="alert alert-secondary" role="alert">
                    Your state is set to <a class="font-weight-bold"><?=$_USER->state?></a>, so be sure to set the map marker within that state. <b>Remember to hit Save for each section.</b>
                </div>
                <?php
                        $locationForm = new Formr('bootstrap');
                        $locationForm->action = "./#search-preferences";

                        if ($locationForm->submitted()) {
                            verifyLocationForm($locationForm);
                        }

                        echo $locationForm->messages();
                ?>
                <div class="card">
                    <div class="card-body">
                        <div class="card">
                            <div class="card-body" id="search-preferences">
                                <h5 class="card-title">Search Preferences</h5>
                                <div class="container">
                                    <?=$locationForm->form_open()?>
                                    <div class="form-row">
                                        <div class="form-group col-12 col-md-4">
                                            <?=$locationForm->input_text('latitude', 'Latitude', $preferences->latitude)?>
                                        </div>
                                        <div class="form-group col-12 col-md-4">
                                            <?=$locationForm->input_text('longitude', 'Longitude', $preferences->longitude)?>
                                        </div>
                                        <div class="form-group col-12 col-md-4">
                                            <?=$locationForm->input_text('radius', 'Radius (miles)', $preferences->radius_miles)?>
                                            <p class="form-text text-muted text-left" style="font-size: 15px;">
                                                Set to -1 for no distance requirement.
                                            </p>
                                        </div>
                                    </div>
                                    <input
                                            id="pac-input"
                                            class="controls"
                                            type="text"
                                            placeholder="Enter your address here..."
                                    />
                                    <div id="map" class="w-100"></div>
                                    <br>
                                    <div class="form-row">
                                        <div class="form-group col-12">
                                            <?=$locationForm->input_select('vaccine_type', 'Vaccine Type', '', '', '', '', $preferences->vaccine_type, VaccineTypes::getSelectorArray())?>
                                        </div>
                                    </div>
                                    <br>
                                    <div id="_submit" class="form-group">
                                        <label class="sr-only" for="submit">Submit</label>
                                        <input type="submit" name="submit" value="Save Search Preferences" id="submit" class="btn btn-primary btn-block">
                                    </div>
                                    <?=$locationForm->form_close()?>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="card">
                            <div class="card-body" id="contact-preferences">
                                <h5 class="card-title">Contact Preferences</h5>
                                <div class="container">
                                    <?php
                                    $contactForm = new Formr('bootstrap');
                                    $contactForm->action = "./#contact-preferences";

                                    if ($contactForm->submitted()) {
                                        verifyContactForm($contactForm);
                                    }

                                    echo $contactForm->messages();
                                    ?>
                                    <?=$contactForm->form_open()?>
                                    <div class="form-row">
                                        <div class="form-group col-12 text-left" style="font-size: 25px;">
                                            <?=$contactForm->input_checkbox_inline('enabled', 'Enable Vaccine Notifications', 'enabled', '', '', '', $preferences->enabled ? 'enabled' : '')?>
                                            <p class="form-text text-muted text-left" style="font-size: 15px;">
                                                Notifications will be sent via email, SMS (if phone number & carrier are saved), and browser (with enable button below).
                                            </p>
                                        </div>
                                        <div class="form-group col-12">
                                            <?=$contactForm->input_email('email', 'Email', $_USER->email, '', 'disabled')?>
                                            <p class="form-text text-muted text-left" style="font-size: 15px;">
                                                Add <?=Config::get('email.from_address')?> to your email contact list to avoid missing emails due to them going to spam.
                                            </p>
                                        </div>
                                        <div class="form-group col-12 col-md-4">
                                            <?=$contactForm->input_select('carrier', 'SMS Carrier', '', '', '', '', $preferences->carrier, SMS::getSelectorArray()) ?>
                                        </div>
                                        <div class="form-group col-12 col-md-8">
                                            <?=$contactForm->input_number('phone', 'Phone Number', $preferences->phone)?>
                                            <p class="form-text text-muted text-left" style="font-size: 15px;">
                                                Texts may be delayed or not arrive due to limitations of this service. Browser and email notifications are more reliable, with desktop browser notifications being the quickest and most reliable.
                                            </p>
                                        </div>
                                        <div class="form-group col-12">
                                            <?=$contactForm->input_select('repeat_interval', 'Repeat Interval', '', '', '', '', $preferences->repeat_seconds, $repeatMapping)?>
                                            <p class="form-text text-muted text-left" style="font-size: 15px;">
                                                This is how often to send a notification again for the same location if it still has appointments or has another appointment available.
                                            </p>
                                        </div>
                                        <div class="form-group col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <label class="control-label">Web Browser Notifications</label>
                                                    <button type="button" class="btn btn-secondary form-control" onclick="enableNotifications();" id="enable-notifications">Enable Browser Notifications For This Device</button>
                                                    <p class="form-text text-muted text-left" style="font-size: 15px;">
                                                        These are the most reliable notifications. They are not supported on iOS devices like iPhones yet due to lack of browser support.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                    </div>
                                    <br>
                                    <div id="_submit" class="form-group">
                                        <label class="sr-only" for="submit">Submit</label>
                                        <input type="submit" name="submit" value="Save Contact Preferences" id="submit" class="btn btn-primary btn-block">
                                    </div>
                                    <?=$contactForm->form_close()?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    Template::footer();
    ?>
    <script>
        var latitudeField = $("#latitude");
        var longitudeField = $("#longitude");
        var radiusField = $("#radius");
        var carrierField = $("#carrier");
        var phoneField = $("#phone");
        var notificationsButton = $("#enable-notifications");
        var map, draggableMarker, circle;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: new google.maps.LatLng(0, 0),
                zoom: <?=$preferences->radius_miles < 0 ? 12 : 6?>,
                mapTypeId: "roadmap"
            });

            draggableMarker = new google.maps.Marker({
                position: new google.maps.LatLng(<?=$preferences->latitude?>, <?=$preferences->longitude?>),
                draggable: true
            });

            map.setCenter(draggableMarker.position);
            draggableMarker.setMap(map);

            circle = new google.maps.Circle({
                strokeColor: "#FF0000",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#FF0000",
                fillOpacity: 0.35,
                map,
                center: draggableMarker.position,
                radius: <?=$preferences->radius_miles * 1609.34?>
            });

            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            // Bias the SearchBox results towards current map's viewport.
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });
            let markers = [];
            // Listen for the event fired when the user selects a prediction and retrieve
            // more details for that place.
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }
                // Clear out the old markers.
                markers.forEach((marker) => {
                    marker.setMap(null);
                });
                markers = [];
                // For each place, get the icon, name and location.
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                    setPosition(place.geometry.location);
                    updateFields(place.geometry.location.lat(), place.geometry.location.lng());
                    if (place.geometry.viewport) {
                        // Only geocodes have viewport.
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                bounds.union(circle.getBounds());
                map.fitBounds(bounds);
            });

            resizeToCircle();

            google.maps.event.addListener(draggableMarker, 'click', function () {
                updateFields(this.getPosition().lat(), this.getPosition().lng());
                setCirclePosition(this.getPosition());
            });

            google.maps.event.addListener(draggableMarker, 'dragend', function () {
                updateFields(this.getPosition().lat(), this.getPosition().lng());
                setCirclePosition(this.getPosition());
            });

            latitudeField.on('change', function() {
               updateMarker(latitudeField.val(), longitudeField.val());
            });

            longitudeField.on('change', function() {
                updateMarker(latitudeField.val(), longitudeField.val());
            });

            radiusField.on('change', function() {
                // miles to meters
                updateCircleRadius(radiusField.val() * 1609.34);
            });

            updatePhoneDisabled();
            updateCircleRadius(<?=$preferences->radius_miles?> * 1609.34);

            resizeToCircle();
        }

        function resizeToCircle() {
            if (circle.getRadius() > 0) {
                const bounds = new google.maps.LatLngBounds();
                bounds.union(circle.getBounds());
                map.fitBounds(bounds);
            }
        }

        function setCirclePosition(position) {
            circle.setCenter(position);
        }

        function setPosition(position) {
            draggableMarker.setPosition(position);
            setCirclePosition(position);
        }

        function updateFields(lat, long) {
            latitudeField.val(lat);
            longitudeField.val(long);
        }

        function updateMarker(lat, long) {
            var latlng = new google.maps.LatLng(lat, long);
            draggableMarker.setPosition(latlng);
        }

        function updateCircleRadius(radiusMeters) {
            circle.setRadius(radiusMeters);
            // Only make visible if radius is positive
            circle.setOptions({visible: radiusMeters > 0});
            resizeToCircle();
        }

        carrierField.on('change', function() {
            updatePhoneDisabled();
        });

        function updatePhoneDisabled() {
            phoneField.prop('disabled', carrierField.val() === 'Disabled');
        }

        // From: https://github.com/Minishlink/web-push-php-example/blob/master/src/app.js
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        function subscribe() {
            return checkNotificationPermission()
                .then(() => navigator.serviceWorker.ready)
                .then(serviceWorkerRegistration =>
                    serviceWorkerRegistration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array("<?=Config::get('web_push.public_key')?>"),
                    })
                )
                .then(subscription => {
                    return push_sendSubscriptionToServer(subscription, 'POST');
                })
                .catch(e => {
                    if (Notification.permission === 'denied') {
                        console.warn('Notifications are denied by the user.');
                    } else {
                        // A problem occurred with the subscription; common reasons
                        // include network errors or the user skipped the permission
                        console.error('Impossible to subscribe to push notifications', e);
                    }
                });
        }

        function enableNotifications() {
            subscribe();
        }

        function sendSubscriptionToServer(subscription, method) {
            console.log('test');
            const key = subscription.getKey('p256dh');
            const token = subscription.getKey('auth');
            const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

            return fetch('push_subscription.php', {
                method,
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                    authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                    contentEncoding,
                }),
            }).then(() => subscription);
        }

        function checkNotificationPermission() {
            return new Promise((resolve, reject) => {
                if (Notification.permission === 'denied') {
                    return reject(new Error('Push messages are blocked.'));
                }

                if (Notification.permission === 'granted') {
                    return resolve();
                }

                if (Notification.permission === 'default') {
                    return Notification.requestPermission().then(result => {
                        if (result !== 'granted') {
                            reject(new Error('Bad permission result'));
                        } else {
                            resolve();
                        }
                    });
                }

                return reject(new Error('Unknown permission'));
            });
        }

        $(document).ready(function() {
            navigator.serviceWorker.register('/serviceWorker.js').then(
                () => {
                    console.log('[SW] Service worker has been registered');
                    push_updateSubscription();
                },
                e => {
                    console.error('[SW] Service worker registration failed', e);
                }
            );
            updateNotificationsButton();
        });

        function push_updateSubscription() {
            navigator.serviceWorker.ready
                .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
                .then(subscription => {
                    if (!subscription) {
                        // We aren't subscribed to push, so set UI to allow the user to enable push
                        return;
                    }

                    // Keep your server in sync with the latest endpoint
                    return push_sendSubscriptionToServer(subscription, 'PUT');
                })
                .catch(e => {
                    console.error('Error when updating the subscription', e);
                });
        }

        function push_sendSubscriptionToServer(subscription, method) {
            updateNotificationsButton();
            const key = subscription.getKey('p256dh');
            const token = subscription.getKey('auth');
            const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

            return fetch('/dashboard/push_subscription.php', {
                method,
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                    authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                    contentEncoding,
                }),
            }).then(() => subscription);
        }

        function updateNotificationsButton() {
            Notification.requestPermission().then(function(result) {
                if (result === 'denied' || result === 'default' ) {
                    notificationsButton.prop('disabled', false);
                    notificationsButton.html('Enable Browser Notifications For This Device');
                    return;
                }
                notificationsButton.html('Browser Notifications For This Device Are Enabled (Disable in browser settings if no longer wanted)');
                notificationsButton.prop('disabled', true);
            });
        }
    </script>
    <script async
            src="https://maps.googleapis.com/maps/api/js?key=<?=Config::get('google_maps.api_key')?>&callback=initMap&libraries=places">
    </script>
</div>
</body>
</html>