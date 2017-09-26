<?php

if ( ! class_exists( "LoginCommon" ) ) {

	class LoginCommon {

		static function login( $identityCode, $firstName, $lastName, $email, $country ) {
			$userName = $country . "_" . $identityCode;

			if ( strlen( $identityCode ) > 5 ) {
				$user = LoginCommon::getUser( $identityCode );
				if ( $user == null ) {
					$user_id = LoginCommon::createUser( $userName, $firstName, $lastName, $email, $identityCode );
				} else {
					$user_id = $user->userid;
				}
			} else {
				wp_die( "ERROR: Idcode not received from the login. Please try again $identityCode, $firstName, $lastName, $email" );
			}
			if ( is_multisite() ) {
				add_user_to_blog( get_current_blog_id(), $user_id, get_option( 'default_role' ) );
			}
			wp_set_auth_cookie( $user_id );
		}

		private static function createUser( $userName, $firstName, $lastName, $email, $identityCode ) {
			global $wpdb;
			$user_data = array(
				'user_pass'    => wp_generate_password( 64, true ),
				'user_login'   => $userName,
				'display_name' => "$firstName $lastName",
				'first_name'   => $firstName,
				'last_name'    => $lastName,
				'user_email'   => $email,
				'role'         => get_option( 'default_role' ) // Use default role or another role, e.g. 'editor'
			);

			if ( username_exists( $userName ) ) {
				wp_die( "Cannot create user. Username $userName exists" );
			}

			$user_id = wp_insert_user( $user_data );

			if ( is_wp_error( $user_id ) ) {
				include 'iframe_break_free_errorhandler.php';
				wp_die( "Cannot create user. Message=" . $user_id->get_error_message() . ". Email: " . $email );
			}

			$prefix     = is_multisite() ? $wpdb->get_blog_prefix( BLOG_ID_CURRENT_SITE ) : $wpdb->prefix;
			$table_name = $prefix . "idcard_users";
			$wpdb->insert( $table_name, array(
					'firstname'    => $firstName,
					'lastname'     => $lastName,
					'identitycode' => $identityCode,
					'userid'       => $user_id,
					'created_at'   => current_time( 'mysql' )
				)
			);

			return $user_id;
		}

		private static function getUser( $identityCode ) {
			global $wpdb;

			$prefix = is_multisite() ? $wpdb->get_blog_prefix( BLOG_ID_CURRENT_SITE ) : $wpdb->prefix;

			$user = $wpdb->get_row(
				$wpdb->prepare( "select * from $prefix" . "idcard_users WHERE identitycode=%s", $identityCode )
			);

			return $user;
		}

	}

}

