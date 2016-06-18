<?php

require_once('../../../wp-load.php');
require_once('logincommon.php');

IdcardAuthenticate::login();

class IdcardAuthenticate {

    static function login() {
        echo "Is php curl module installed?";
        $token = $_GET['token'];

        //tõmbame sisselogitud inimese andmed
        $result = IdcardAuthenticate::getUserData($token);
        $firstName = $result['firstname'];
        $lastName = $result['lastname'];
        $identityCode = $result['id'];
        $email = $result['email'];
        $authKey = $result['auth_key'];
        $loginSource = $result['login_source'];
        LoginCommon::login($identityCode, $firstName, $lastName, $email, $authKey, $loginSource);
    }

    //küsime serverist käest inimese andmeid
    function getUserData($token) {

        $result = IdCardLogin::curlCall("api/v1/verifytoken/" . $token, null);

        return $result;
    }

}
