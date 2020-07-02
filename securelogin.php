<?php

require_once('logincommon.php');

class IdcardAuthenticate
{
    static function login($token)
    {
        if (IdcardAuthenticate::isAlreadyLogged()) {
            return; // login already completed
        }

        $result = IdcardAuthenticate::getUserData($token);
        if ($result == null) {
            if (IdcardAuthenticate::isAlreadyLogged()) {
                return; // Maybe logged in during API call
            }
            if (get_option('smartid_debug_mode')) {
                $current_user = wp_get_current_user();
                if ( ! ($current_user instanceof WP_User)) {
                    $extraMessage = "Current user is not WP_User" . print_r($current_user, true);
                    file_get_contents("https://id.eideasy.com/confirm_progress?message=" . urlencode("WP login failed: $token - $extraMessage"));
                } else {
                    global $wpdb;
                    $prefix = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;

                    $table_name = $prefix . "idcard_users";
                    $user       = $wpdb->get_row(
                        $wpdb->prepare("select * from $table_name WHERE userid=%s", $current_user->ID)
                    );

                    $extraMessage = "Logged in user is $user->identitycode";
                    file_get_contents("https://id.eideasy.com/confirm_progress?message=" . urlencode("WP login already completed $token - $extraMessage"));
                }
            }
            if (get_option('smartid_registration_disabled')) {
                wp_die("User not found and registration disabled. Go back and contact site admin. ");
            } else {
                wp_die("Login failed, please contact site admin.");
            }
        }
        $firstName    = $result['firstname'];
        $lastName     = $result['lastname'];
        $identityCode = $result['idcode'];
        $country      = array_key_exists("country", $result) ? $result["country"] : "EE";

        $email = "$identityCode@local.localhost";

        $email = apply_filters('smartid_new_user_email', $email);
        $email = apply_filters('eideasy_new_user_email', $email);

        return LoginCommon::login($identityCode, $firstName, $lastName, $email, $country);
    }

    static function getUserData($token)
    {
        $postParams = [
            "code"          => $token,
            "grant_type"    => "authorization_code",
            "client_id"     => get_option("smartid_client_id"),
            'redirect_uri'  => urlencode(get_option("smartid_redirect_uri")),
            "client_secret" => get_option("smartid_secret")
        ];

        $accessTokenResult = IdCardLogin::curlCall("oauth/access_token", [], $postParams);
        $accessToken       = $accessTokenResult["access_token"];

        if (strlen($accessToken) < 20) {
            return null; //login already completed
        }

        $params         = [
            "access_token" => $accessToken
        ];
        $userDataResult = IdCardLogin::curlCall("api/v2/user_data", $params);

        return $userDataResult;
    }

    static function isAlreadyLogged()
    {
        return wp_get_current_user() != null && wp_get_current_user()->ID != '';
    }
}
