<?php


namespace VaccineNotifier;

use PHPMailer\PHPMailer\PHPMailer;

class Email {
    private static function createPHPMailer(): PHPMailer {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = Config::get('email.host');
        $mail->Port = Config::get('email.port');
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = Config::get('email.username');
        $mail->Password = Config::get('email.password');
        $mail->setFrom(Config::get('email.from_address'), Config::get('email.from_name'));
        return $mail;
    }

    public static function sendMail($recipient, $subject, $body) {
        $mail = self::createPHPMailer();
        $mail->addAddress($recipient);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
    }

    public static function sendHTMLMail($recipient, $subject, $body, $altBody = '', $replyToEmail = '', $replyToName = '') {
        $mail = self::createPHPMailer();
        $mail->addAddress($recipient);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->AltBody = $altBody;
        if (!empty($replyToEmail)) {
            $mail->clearReplyTos();
            $mail->addReplyTo($replyToEmail, $replyToName);
        }
        $mail->send();
    }
}