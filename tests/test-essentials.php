<?php

class EssentialsTest extends WP_UnitTestCase {

	function testClientNotActivated() {
		$loginCode=IdCardLogin::getLoginButtonCode();
		$this->assertContains( "not activated", $loginCode );
	}

	function testClientActivated() {		
		update_option("site_client_id", "asdzxc");
		$loginCode=IdCardLogin::getLoginButtonCode();
		$this->assertContains( "asdzxc", $loginCode );
		$this->assertContains( "button.js", $loginCode );
		$this->assertContains( "redirect_to", $loginCode );
	}

	function testIsUserIdLoggedNotLogged() {
		$logged=IdCardLogin::isUserIdLogged();
		$this->assertFalse( $logged );
	}

	function testIsUserIdLoggedLogged() {
		$userId = $this->factory->user->create( array( 'user_login' => 'michael' ) );
		wp_set_current_user($userId);
		$authKey="temp_auth_key";
		global $wpdb;            
		$wpdb->insert($wpdb->prefix . "idcard_users", array(
		        'firstname' => "eesnimi",
		        'lastname' => "perenimi",
		        'identitycode' => "123123123",
		        'userid' => $userId,
		        'created_at' => current_time('mysql'),
		        'auth_key' => $authKey,
                 )
	        );
		$logged=IdCardLogin::isUserIdLogged();
		$this->assertTrue( $logged);
	}

}

