<?php

require_once('logincommon.php');

class IdcardAuthenticate {

    static function login($token) {
        $result = IdcardAuthenticate::getUserData($token);
        $firstName = $result['firstname'];
        $lastName = $result['lastname'];
        $identityCode = $result['idcode'];
        $email = $result['email'];
        if (strlen($identityCode) != 11) {
            echo "ERROR: Idcode not received from the login. Please contact help@smartid.ee <br>";
            var_dump($result);
            die();
        }
        LoginCommon::login($identityCode, $firstName, $lastName, $email);
    }

    function getUserData($token) {

        $postParams = [
            "code" => $token,
            "grant_type" => "authorization_code",
            "client_id" => get_option("smartid_client_id"),
            'redirect_uri' => urlencode(get_option("smartid_redirect_uri")),
            "client_secret" => get_option("smartid_secret")
        ];


        $accessTokenResult = IdCardLogin::curlCall("oauth/access_token", [], $postParams);
        $accessToken = $accessTokenResult["access_token"];

        $params = [
            "access_token" => $accessToken
        ];
        $userDataResult = IdCardLogin::curlCall("api/v2/user_data", $params);

        return $userDataResult;
    }

}
