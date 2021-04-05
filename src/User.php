<?php


namespace VaccineNotifier;


class User {
    public $id, $email, $verified, $signup_ip, $signup_date, $state;

    /**
     * User constructor.
     * @param $id
     * @param $email
     * @param $verified
     * @param $signup_ip
     * @param $signup_date
     * @param $state
     */
    public function __construct($id, $email, $verified, $signup_ip, $signup_date, $state)
    {
        $this->id = $id;
        $this->email = $email;
        $this->verified = $verified;
        $this->signup_ip = $signup_ip;
        $this->signup_date = $signup_date;
        $this->state = $state;
    }

    public static function createFromDatabase($data): User {
        return new User(
            $data['id'],
            $data['email'],
            $data['verified'],
            $data['signup_ip'],
            $data['register_date'],
            $data['state']
        );
    }
}