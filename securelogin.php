<?php

require_once('logincommon.php');

class IdcardAuthenticate
{
    static function login($token)
    {
        if (IdcardAuthenticate::isAlreadyLogged() && !get_option('eideasy_only_identify')) {
            return null; // Login already completed.
        }

        $result = IdcardAuthenticate::getUserData($token);
        do_action('eideasy_user_identified', $result);
        if (get_option('eideasy_only_identify')) {
            return "eideasy_only_identify";
        }

        $firstName    = $result['firstname'];
        $lastName     = $result['lastname'];
        $identityCode = $result['idcode'];
        $country      = array_key_exists("country", $result) ? $result["country"] : "EE";

        if ($country === "EE") {
            $email = "$identityCode@eesti.ee";
        } else {
            $email = "$identityCode@local.localhost";
        }

        $email = apply_filters('smartid_new_user_email', $email);
        $email = apply_filters('eideasy_new_user_email', $email);

        return LoginCommon::login($identityCode, $firstName, $lastName, $email, $country, $result);
    }

    static function getUserData($token)
    {
        $postParams = [
            "code"          => $token,
            "grant_type"    => "authorization_code",
            "client_id"     => get_option("smartid_client_id"),
            'redirect_uri'  => get_option("smartid_redirect_uri"),
            "client_secret" => get_option("smartid_secret")
        ];

        $accessTokenResult = IdCardLogin::apiCall("oauth/access_token", [], $postParams);

        $accessToken = $accessTokenResult["access_token"];

        $params = [
            "access_token" => $accessToken
        ];
        return IdCardLogin::apiCall("api/v2/user_data", $params);
    }

    static function isAlreadyLogged()
    {
        global $wpdb;

        if (wp_get_current_user() === null) {
            return false;
        }

        $prefix     = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;
        $table_name = $prefix . "idcard_users";

        $user = $wpdb->get_row(
            $wpdb->prepare("select * from " . esc_sql($table_name) . " WHERE userid=%s", wp_get_current_user()->ID)
        );

        return wp_get_current_user()->ID != '' && $user !== null;
    }
}