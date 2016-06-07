<?php

if (!class_exists("MobileId")) {
    require_once('logincommon.php');
    class MobileId {

        function midLoginStatus() {
            if (array_key_exists("mid_login_start", $_POST) && $_POST['mid_login_start'] == "yes") {
                return MobileId::midLoginStart();
            } elseif (array_key_exists("mid_login_start_form", $_POST) && $_POST['mid_login_start_form'] == "yes") {
                $params = [
                    "idcode" => $_POST['idcode'],
                    "mobileno" => $_POST['mobileno']
                ];
                return MobileId::midLoginStartSubmit($params);
            } elseif (array_key_exists("mid_login_refresh_form", $_POST) && $_POST['mid_login_refresh_form'] == "yes") {
                return MobileId::midLoginRefresh();
            } else {
                return NULL;
            }
        }

        function midLoginStart() {
            $midDataFrom = '<form id="mid_login_start_form" action="" method="post">'
                    . '<input type="hidden" name="mid_login_start_form" value="yes">'
                    . '<input type="text" name="idcode" placeholder="Idcode">'
                    . '<input type="text" name="mobileno" placeholder="+3725xxxxx">'
                    . '<input type="submit" value="Start Mobile-ID login">'
                    . '</form>';
            return $midDataFrom;
        }

        function midLoginStartSubmit($params) {
            $params['sess_id'] = session_id();
            $challengeResponse = IdCardLogin::curlCall("api/v1/mid/loginstart", $params);
            if (array_key_exists("error", $challengeResponse)) {
                return "Mobile-ID login failed because of " . $challengeResponse["error"];
            }
            $_SESSION['challenge'] = $challengeResponse['challenge'];
            return MobileId::getRefreshCode($challengeResponse['challenge']);
        }


        function getRefreshCode($challengeCode) {
            $redirect_url = strlen(array_key_exists('redirect_to', $_GET)) > 0 ? "?redirect_to=" . urlencode($_GET['redirect_to']) : "";
            $refreshForm = '<div>Please enter PIN1 in your mobile. Challenge code is <b>' . $challengeCode . '</b></div>'
                    . '<form id="mid_login_refresh_form" action="'.IdCardLogin::getPluginBaseUrl() . '/midlogin.php'.$redirect_url.'" method="post">'
                    . '<input type="hidden" name="mid_login_refresh_form" value="yes">'
                    . '</form>'
                    . '<script>'
                    . 'var form = document.getElementById("mid_login_refresh_form");'
                    . 'setTimeout(function(){ form.submit(); }, 5000);'
                    . '</script>';
            return $refreshForm;
        }

    }

}
