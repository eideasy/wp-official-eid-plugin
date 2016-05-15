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

        //check if id code was returned and set session data accordign to login success or not
        if (strlen($identityCode) == 11) {
            $_SESSION['identitycode'] = $identityCode;
            $_SESSION['id_session_id'] = $result->session_id;
            $_SESSION['admin_id_verified'] = true;
            $_SESSION['admin_auth_failed'] = false;
            $_SESSION['admin_firstname'] = $result->firstname;
            $_SESSION['admin_lastname'] = $result->lastname;
        } else {
            $_SESSION['admin_id_verified'] = false;
            $_SESSION['admin_auth_failed'] = true;
        }

        //Jätame admini andmed sessiooni meelde        
        if (array_key_exists('redirect_to', $_GET)) {
            header('Location: ' . $_GET['redirect_to']);
        } else {
            header('Location: ' . home_url());
        }
    }

    //Kontrollime proxyst kasutaja andmeid
    function getUserFromIdid($token) {
        $curl = curl_init();
        $url = "http://localhost:8000/api/v1/verifytoken/" . $token;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

}
