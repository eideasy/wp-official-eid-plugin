<?php
require_once('../../../wp-load.php');

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
        $userName = "EE" . $identityCode;

        //Otsime üles sisselogitud inimese või tekitame, kui teda varem polnud
        $user = IdcardAuthenticate::getUser($identityCode);
        if (($user == NULL) and ( NULL == username_exists($userName))) {
            $user_id = IdcardAuthenticate::createUser($userName, $firstName, $lastName, $identityCode);
        } else {
            $user_id = $user->userid;
        }
        
        //logime inimese ka wordpressi sisse
        IdcardAuthenticate::setSession($identityCode, $firstName, $lastName);
        wp_set_auth_cookie($user_id);
        if (array_key_exists('redirect_to', $_GET)) {
            header('Location: ' . $_GET['redirect_to']);
        } else {
            header('Location: ' . home_url());
        }
    }

    //küsime idid käest inimese andmeid
    function getUserFromIdid($token) {
        $curl = curl_init();
        $url = "http://localhost:8000/api/v1/verifytoken/" . $token;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    //sisestame inimese andmebaasi
    private static function createUser($userName, $firstName, $lastName, $identityCode) {
        global $wpdb;
        $user_data = array(
            'user_pass' => wp_generate_password(64, true),
            'user_login' => $userName,
            'display_name' => "$firstName $lastName",
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => get_option('default_role') // Use default role or another role, e.g. 'editor'
        );
        $user_id = wp_insert_user($user_data);
        $wpdb->insert($wpdb->prefix . "idcard_users", array(
            'firstname' => $firstName,
            'lastname' => $lastName,
            'identitycode' => $identityCode,
            'userid' => $user_id,
            'created_at' => current_time('mysql'),
                )
        );
        return $user_id;
    }

    //vaatame, kas selle isikukoodiga inimene on juba baasis olemas
    private static function getUser($identityCode) {
        global $wpdb;
        $user = $wpdb->get_row(
                $wpdb->prepare(
                        "select * from $wpdb->prefix" . "idcard_users
		 WHERE identitycode=%s		 
		", $identityCode
                )
        );
        return $user;
    }

    //jätame kasutaja andmed sessiooni meelde
    private static function setSession($identityCode, $firstName, $lastName) {
        $_SESSION['identitycode'] = $identityCode;
        $_SESSION['firstname'] = $firstName;
        $_SESSION['lastname'] = $lastName;
    }

}
