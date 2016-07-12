<?php

require_once('logincommon.php');

class IdcardAuthenticate {

    static function login($token) {
        $result = IdcardAuthenticate::getUserData($token);
        $firstName = $result['firstname'];
        $lastName = $result['lastname'];
        $identityCode = $result['id'];
        $email = $result['email'];
        $authKey = $result['auth_key'];
        $loginSource = $result['login_source'];
        if (strlen($identityCode) != 11) {
            echo "ERROR: Idcode not received from the login. Please contact help@smartid.ee <br>";
            var_dump($result);
            die();
        }
        LoginCommon::login($identityCode, $firstName, $lastName, $email, $authKey, $loginSource);
    }

    function getUserData($token) {
        $params = [
            "auth_key" => $token
        ];

        $result = IdCardLogin::curlCall("api/v1/user_data", $params);

        return $result;
    }

}
