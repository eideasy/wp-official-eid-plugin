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
            $_SESSION['auth_key'] = $result->auth_key;
            $_SESSION['admin_id_verified'] = true;
            $_SESSION['admin_auth_failed'] = false;
            $_SESSION['admin_firstname'] = $result->firstname;
            $_SESSION['admin_lastname'] = $result->lastname;
        } else {
            $_SESSION['admin_id_verified'] = false;
            $_SESSION['admin_auth_failed'] = true;
        }

        //Jätame admini andmed sessiooni meelde        
        if (array_key_exists('redir_to', $_GET)) {
            header('Location: ' . $_GET['redir_to']);
        } else {
            header('Location: ' . home_url());
        }
    }

    //Kontrollime proxyst kasutaja andmeid
    function getUserFromIdid($token) {
        $ch = curl_init();
        $url = "https://idiotos.eu/api/v1/verifytoken/" . $token;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
