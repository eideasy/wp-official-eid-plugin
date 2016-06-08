<?php

require_once('../../../wp-load.php');
require_once('logincommon.php');

IdcardAuthenticate::login();

class IdcardAuthenticate {

    static function login() {
        echo "Is php curl module installed?";
        $token = $_GET['token'];

        //tõmbame sisselogitud inimese andmed
        $result = json_decode(IdcardAuthenticate::getUserFromIdid($token));
        $firstName = $result->firstname;
        $lastName = $result->lastname;
        $identityCode = $result->id;
        $email = $result->email;
        $authKey = $result->auth_key;
        $_SESSION['login_source'] = "id";
        LoginCommon::login($identityCode, $firstName, $lastName, $email, $authKey);
    }

    //küsime idid käest inimese andmeid
    function getUserFromIdid($token) {
        $ch = curl_init();
        $url = "https://api.idapi.ee/api/v1/verifytoken/" . $token;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }  

}
