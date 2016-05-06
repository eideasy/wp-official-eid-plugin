<?php

require_once('../../../wp-load.php');

IdAdminLogin::login();

class IdAdminLogin {

    static function login() {
        echo "Is php curl module installed?";
        $token = $_GET['token'];

        //tõmbame sisselogitud inimese andmed
        $result = json_decode(IdAdminLogin::getUserFromIdid($token));
        $identityCode = $result->id;

        //check if id code was returned
        if (strlen($identityCode) == 11) {
            $_SESSION['identitycode'] = $identityCode;
            $_SESSION['admin_id_verified'] = true;
        }

        //Jätame admini andmed sessiooni meelde        
        if (array_key_exists('redirect_to', $_GET)) {
            header('Location: ' . $_GET['redirect_to']);
        } else {
            header('Location: ' . home_url());
        }
    }

    //küsime idid käest inimese andmeid
    function getUserFromIdid($token) {
        $curl = curl_init();
        $url = "https://idid.ee/oauth2/getUser.php?secret=6868b692897ef36042c46295fe51f080&token=" . $token;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

}
