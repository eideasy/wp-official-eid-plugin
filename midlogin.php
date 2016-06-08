<?php

require_once('../../../wp-load.php');
include_once('id-card-login.php');
require_once('logincommon.php');
require_once('mobileid.php');
require_once('id-card-login.php');

MidLogin::midLoginRefresh();

class MidLogin {

    function midLoginRefresh() {
        $params = [];
        $params['sess_id'] = session_id();
        $challengeResponse = IdCardLogin::curlCall("api/v1/mid/loginrefresh", $params);

        if (array_key_exists("error", $challengeResponse)) {
            return "Mobile-ID login failed because of " . $challengeResponse["error"];
        }

        if (array_key_exists("status", $challengeResponse) && $challengeResponse['status'] == "OUTSTANDING_TRANSACTION") {
            echo MobileId::getRefreshCode($_SESSION['challenge']);
        } elseif (array_key_exists("status", $challengeResponse) && $challengeResponse['status'] == "OK") {
            $firstName = $challengeResponse['firstname'];
            $lastName = $challengeResponse['lastname'];
            $identityCode = $challengeResponse['idcode'];
            $email = $challengeResponse['email'];
            $authKey = $challengeResponse['auth_key'];
            $_SESSION['login_source'] = "mid";
            LoginCommon::login($identityCode, $firstName, $lastName, $email, $authKey);
        } else {
            echo "This should not happen. Mobile-ID login failed because ";
        }
    }

}
