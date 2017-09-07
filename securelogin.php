<?php

require_once( 'logincommon.php' );

class IdcardAuthenticate {

	static function login( $token ) {
		$result = IdcardAuthenticate::getUserData( $token );
		if ( $result == null ) {
			return; // login already completed
		}
		$firstName    = $result['firstname'];
		$lastName     = $result['lastname'];
		$identityCode = $result['idcode'];
		$email        = $result['email'];
		$country      = array_key_exists( "country", $result ) ? $result["country"] : "ee";

		LoginCommon::login( $identityCode, $firstName, $lastName, $email, $country );
	}

	function getUserData( $token ) {

		$postParams = [
			"code"          => $token,
			"grant_type"    => "authorization_code",
			"client_id"     => get_option( "smartid_client_id" ),
			'redirect_uri'  => urlencode( get_option( "smartid_redirect_uri" ) ),
			"client_secret" => get_option( "smartid_secret" )
		];

		$accessTokenResult = IdCardLogin::curlCall( "oauth/access_token", [], $postParams );
		$accessToken       = $accessTokenResult["access_token"];
		if ( strlen( $accessToken ) != 40 ) {
			return null; //login already completed
		}

		$params         = [
			"access_token" => $accessToken
		];
		$userDataResult = IdCardLogin::curlCall( "api/v2/user_data", $params );

		return $userDataResult;
	}

}
