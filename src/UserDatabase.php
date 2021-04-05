<?php


namespace VaccineNotifier;


use DB;

Database::connect();

class UserDatabase {
    public static $LOGIN_EXPIRATION_SECONDS = 60 * 24 * 60 * 60; // Two months
    private static $userCache = [];

    public static function getIdByEmail($email) {
        $row = DB::queryFirstRow('SELECT `id` FROM `users` WHERE `email`=%s', $email);
        return $row ? $row['id'] : null;
    }

    public static function doesEmailExist($email) {
        return self::getIdByEmail($email) != null;
    }

    public static function createUser($data) {
        DB::insert('users', $data);
        return DB::insertId();
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function sendConfirmationEmail($id, $email) {
        $token = Utilities::generateToken(32);
        DB::insert('user_verify',[
            'id' => $id,
            'token' => $token
        ]);
        $link = Config::get('base_url') . "/register/verify.php?token=$token";
        Email::sendHTMLMail($email, 'Please verify your Vaccine Notifier account',
            'Please use the link below to verify your account: <br>'
            . "<a href='$link'>$link</a>",
            "Please verify your account with this link: $link");
    }

    public static function verifyWithToken($token) {
        $row = DB::queryFirstRow("SELECT `id` FROM `user_verify` WHERE `token`=%s", $token);
        if ($row == null) {
            return false;
        }

        $userId = $row['id'];

        $row = DB::queryFirstRow("SELECT `email`, `verified` FROM `users` WHERE `id`=%i", $userId);
        if ($row == null) {
            return false;
        }

        $email = $row['email'];
        $verified = $row['verified'];

        if ($verified) {
            return false;
        }

        DB::update('users', ['verified' => true], "`id`=%i", $userId);
        DB::delete('user_verify', '`id`=%i', $userId);
        return $email;
    }

    public static function createFromDatabase($row) {
        $id = $row['id'];
        return User::createFromDatabase($row);
    }

    public static function getUserFromLogin($email, $password) {
        $hashedPassword = self::hashPassword($password);
        $row = DB::queryFirstRow("SELECT * FROM `users` WHERE `email`=%s", $email);
        if ($row == null) {
            return null;
        }
        $storedHash = $row['password'];
        if ($hashedPassword != $storedHash) {
            if (!password_verify($password, $storedHash)) {
                return null;
            }
        }

        return self::createFromDatabase($row);
    }

    public static function getUserFromId($userId) {
        if (isset(self::$userCache[$userId])) {
            return self::$userCache[$userId];
        }
        $row = DB::queryFirstRow("SELECT * FROM `users` WHERE `id`=%i", $userId);
        if ($row == null) {
            return null;
        }

        $user = self::createFromDatabase($row);
        self::$userCache[$userId] = $user;
        return $user;
    }

    public static function deleteExpiredTokens($userId) {
        $oldestPossibleCreation = TimeHelper::getUTCTimestamp() - self::$LOGIN_EXPIRATION_SECONDS;
        DB::delete('user_tokens', '`id`=%i AND `creation_date`<%i', $userId, $oldestPossibleCreation);
    }

    public static function createToken($userId) {
        $token = Utilities::generateToken(64);
        DB::insert('user_tokens', [
            'id' => $userId,
            'token' => $token,
            'ip' => Utilities::getIP(),
            'creation_date' => TimeHelper::getUTCTimestamp(),
            'user_agent' => Utilities::cutToLength(Utilities::getUserAgent(), 256)
        ]);
        return $token;
    }

    public static function getUserFromToken($token) {
        self::deleteExpiredTokens($token);
        $row = DB::queryFirstRow("SELECT `id` FROM `user_tokens` WHERE `token`=%s", $token);
        if ($row == null) {
            return null;
        }

        $userId = $row['id'];
        return self::getUserFromId($userId);
    }

    public static function getUserFromBrowser() {
        if (isset($_COOKIE['login_token'])) {
            $token = $_COOKIE['login_token'];
            $user = null;
            if ($token == null || ($user = self::getUserFromToken($token)) == null) {
                // Delete token
                Utilities::deleteCookie('login_token');
            }
            return $user;
        }
        return null;
    }

    public static function deleteToken($token) {
        DB::delete('user_tokens', '`token`=%s', $token);
    }

    public static function getPreferences(User $user): UserPreferences {
        $row = DB::queryFirstRow("SELECT * FROM `user_preferences` WHERE `id`=%i", $user->id);
        if ($row == null) {
            return UserPreferences::getDefault($user);
        }
        return UserPreferences::createFromDatabase($user, $row);
    }

    public static function getPreferencesById($id): UserPreferences {
        return self::getPreferences(self::getUserFromId($id));
    }

    public static function updatePreferences(User $user, $data) {
        $data['id'] = $user->id;
        DB::insertUpdate('user_preferences', $data);
    }

    public static function createPasswordResetToken($id) {
        $token = Utilities::generateToken(32);
        DB::insertUpdate('pw_tokens', [
            'id' => $id,
            'token' => $token
        ]);
        return $token;
    }

    public static function getIdFromPasswordResetToken($token) {
        $row = DB::queryFirstRow("SELECT `id` FROM `pw_tokens` WHERE `token`=%s", $token);
        return $row == null ? null : $row['id'];
    }

    public static function updatePassword($id, $password) {
        DB::update('users', [
            'password' => self::hashPassword($password)
        ], 'id=%i', $id);
    }

    public static function deletePasswordResetToken($token) {
        DB::delete('pw_tokens', 'token=%s', $token);
    }

    public static function addPushNotificationBrowser(User $user, $endpoint, $publicKey, $authToken) {
        DB::insertUpdate('push_notifications', [
            'id' => $user->id,
            'endpoint' => $endpoint,
            'public_key' => $publicKey,
            'auth_token' => $authToken
        ]);
    }

    public static function getPushNotificationBrowers($userId): array {
        return DB::query("SELECT * FROM `push_notifications` WHERE `id`=%i", $userId);
    }

    public static function getPushNotificationBrowsers(User $user) {
        return DB::query("SELECT * FROM `push_notifications` WHERE `id`=%i", $user->id);
    }

    public static function getLastNotificationTime($userId, $locationId) {
        $row = DB::queryFirstRow("SELECT * FROM `last_notification` WHERE `id`=%i AND `location_id`=%i", $userId, $locationId);
        return $row == null ? null : $row['timestamp'];
    }

    public static function setLastNotificationTime($userId, $locationId, $time) {
        DB::insertUpdate('last_notification', [
           'id' => $userId,
           'location_id' => $locationId,
           'timestamp' => $time
        ]);
    }
}