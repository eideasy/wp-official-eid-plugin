<?php
/**
 * Plugin Name: SMART-ID
 * Plugin URI: https://smartid.ee/
 * Description: Allow your visitors to login to wordpress and sign contracts with Estonian ID-card and mobile-ID
 * Version: 1.4
 * Author: Smart ID Estonia
 * Author URI: https://smartid.ee/
 * License: GPLv2 or later
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if ( ! class_exists( "IdCardLogin" ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin.php' );


	class IdCardLogin {
		static function getSupportedMethods() {
			$smartid_supportedMethods = [
				"smartid_lt-mobile-id_enabled",
				"smartid_lt-id-card_enabled",
				"lveid_enabled",
				"smartid_idcard_enabled",
				"smartid_mobileid_enabled",
				"smartid_smartid_enabled",
				"smartid_facebook_enabled",
				"smartid_google_enabled",
			];

			return $smartid_supportedMethods;
		}

		static function deleteUserCleanUp( $user_id ) {
			global $wpdb;
			$wpdb->delete( $wpdb->prefix . "idcard_users", array( 'userid' => $user_id ) );
		}

		static function getStoredUserData() {
			global $wpdb;
			$current_user = wp_get_current_user();
			$user         = $wpdb->get_row(
				$wpdb->prepare(
					"select * from $wpdb->prefix" . "idcard_users
		 WHERE userid=%s", $current_user->ID
				)
			);

			return $user;
		}

		static function isLogin() {
			return array_key_exists( 'code', $_GET ) && strlen( $_GET['code'] ) === 40;
		}

		static function wpInitProcess() {
			if ( IdCardLogin::isLogin() ) {
				require_once( plugin_dir_path( __FILE__ ) . 'securelogin.php' );
				IdcardAuthenticate::login( $_GET['code'] );
				wp_register_script( 'login_refresh', plugins_url( 'login_refresh.js', __FILE__ ) );
				wp_enqueue_script( "login_refresh" );
			}
		}

		static function wpHeadProcess() {
			IdCardLogin::echoJsRedirectCode();
		}

		static function echoJsRedirectCode() {
			if ( IdCardLogin::isLogin() ) {
				if ( array_key_exists( 'redirect_to', $_GET ) ) {
					$redirectUrl = $_GET['redirect_to'];
				} else {
					$redirectUrl = home_url( "/" );
				}
				if ( strpos( $redirectUrl, "wp-login" ) > 0 ) {
					$redirectUrl = home_url( "/" );
				}
				echo "echoing redirect";
				?>

                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        window.location = "<?php echo $redirectUrl ?>";
                    });
                </script>
				<?php
			}
		}

		static function admin_notice() {
			if ( get_option( "smartid_client_id" ) == null && array_key_exists( "page", $_GET ) && $_GET['page'] !== "smart-id-settings" ) {
				?>
                <div class="notice notice-success is-dismissible">
                    <p>Your Smart-ID is almost ready! Please open <a
                                href="<?php echo esc_url( get_admin_url( null, 'admin.php?page=smart-id-settings' ) ) ?>">Smart-ID
                            Settings</a> to Activate.</p>
                </div>
				<?php
			}
		}

		static function get_settings_url( $links ) {
			$links[] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=smart-id-settings' ) ) . '">Smart-ID Settings</a>';

			return $links;
		}

		static function echo_id_login() {
			echo '<div style="margin:auto" align="center">'
			     . IdCardLogin::getLoginButtonCode() . IdCardLogin::echoJsRedirectCode()
			     . "</div>";
		}

		static function return_id_login() {
			return IdCardLogin::getLoginButtonCode();
		}

		static function display_contract_to_sign( $atts ) {
			if ( get_option( "smartid_client_id" ) == null ) {
				return "<b>Smart-ID service not activated, cannot sign the contract";
			}
			if ( ! array_key_exists( "id", $atts ) ) {
				return "<b>Contract ID missing, cannot show signing page</b>";
			}
			$code = '<iframe src="https://id.smartid.ee/sign_contract?client_id='
			        . get_option( "smartid_client_id" ) . "&contract_id=" . $atts["id"] . '"'
			        . 'style="height: 100vh; width: 100vw" frameborder="0"></iframe>';

			return $code;
		}

		/**
		 * @return false if login button needs to be shown. Happens when auth_key is missing
		 * or auth key is present but WP user is not logged in.
		 */
		static function isUserIdLogged() {
			if ( ! is_user_logged_in() ) {
				return false;
			} else {
				return IdCardLogin::getStoredUserData() != null;
			}
		}

		static function getLoginButtonCode() {
			if ( IdCardLogin::isUserIdLogged() ) {
				return null;
			}

			if ( get_option( "smartid_client_id" ) == null ) {
				return "<b>ID login not activated yet. Login will be available as soon as admin has activated it.</b>";
			}

			$allDisabled = true;
			foreach ( IdCardLogin::getSupportedMethods() as $method ) {
				if ( get_option( $method ) != false ) {
					$allDisabled = false;
					break;
				}
			}
			if ( $allDisabled ) {
				return "<b>No Secure login methods enabled yet in Wordpress admin, please contact administrator to enable these from Smart ID config</b>";
			}
			$redirectUri = urlencode( get_option( "smartid_redirect_uri" ) );
			$clientId    = get_option( "smartid_client_id" );
			$loginUri    = 'https://id.smartid.ee/oauth/authorize'
			               . '?client_id=' . $clientId
			               . '&redirect_uri=' . $redirectUri
			               . '&response_type=code';

			$loginCode = '<script src="' . IdCardLogin::getPluginBaseUrl() . '/smartid_functions.js"></script>';
			if ( get_option( "smartid_idcard_enabled" ) ) {
				$loginCode .= '<iframe src="https://id.smartid.ee/oauth/authorize?client_id=' . $clientId . '&redirect_uri=' . $redirectUri . '&response_type=code&method=ee-id-card" allowtransparency="true" width="137" height="52" frameborder="0" scrolling="no" style="padding: 3px;"></iframe>';
			}
			if ( get_option( "smartid_mobileid_enabled" ) ) {
				$loginCode .= '<img id="smartid-mid-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/mobile-id.svg" height="46" width="130" style="display:inline; padding: 3px">';
			}
			if ( get_option( "lveid_enabled" ) ) {
				$loginCode .= '<iframe src="https://id.smartid.ee/oauth/authorize?client_id=' . $clientId . '&redirect_uri=' . $redirectUri . '&response_type=code&method=lv-id-card" allowtransparency="true" width="137" height="52" frameborder="0" scrolling="no"  style="padding: 3px;"></iframe>';
			}
			if ( get_option( "smartid_lt-id-card_enabled" ) ) {
				$loginCode .= '<iframe src="https://id.smartid.ee/oauth/authorize?client_id=' . $clientId . '&redirect_uri=' . $redirectUri . '&response_type=code&method=lt-id-card" allowtransparency="true" width="137" height="52" frameborder="0" scrolling="no"  style="padding: 3px;"></iframe>';
			}
			if ( get_option( "smartid_lt-mobile-id_enabled" ) ) {
				$loginCode .= '<img id="smartid-lt-mobile-id-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/lt-mobile-id.png" height="46" width="130" style="display:inline; padding: 3px">';
			}
			if ( get_option( "smartid_smartid_enabled" ) ) {
				$loginCode .= '<img id="smartid-smartid-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/smart-id-white.png" height="46" width="46" style="display:inline; padding: 3px">';
			}
			if ( get_option( "smartid_google_enabled" ) ) {
				$loginCode .= '<img id="smartid-gp-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/gp.png" height="46" width="46" style="display:inline; padding: 3px">';
			}
			if ( get_option( "smartid_facebook_enabled" ) ) {
				$loginCode .= '<img id="smartid-fb-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/fb.png" height="46" width="46" style="display:inline; padding: 3px">';
			}

			$loginCode .= '<script>' .
			              '    if(document.getElementById("smartid-mid-login")) document.getElementById("smartid-mid-login").addEventListener("click", function () {' .
			              '        startSmartIdLogin("' . $loginUri . '&method=ee-mobile-id");' .
			              '    });' .
			              '    if(document.getElementById("smartid-lt-mobile-id-login")) document.getElementById("smartid-lt-mobile-id-login").addEventListener("click", function () {' .
			              '        startSmartIdLogin("' . $loginUri . '&method=lt-mobile-id");' .
			              '    });' .
			              '    if(document.getElementById("smartid-smartid-login")) document.getElementById("smartid-smartid-login").addEventListener("click", function () {' .
			              '        startSmartIdLogin("' . $loginUri . '&method=smart-id");' .
			              '    });' .
			              '    if(document.getElementById("smartid-gp-login")) document.getElementById("smartid-gp-login").addEventListener("click", function () {' .
			              '        startSmartIdLogin("' . $loginUri . '&method=facebook-login");' .
			              '    });' .
			              '    if(document.getElementById("smartid-fb-login")) document.getElementById("smartid-fb-login").addEventListener("click", function () {' .
			              '        startSmartIdLogin("' . $loginUri . '&method=google-login");' .
			              '    });' .
			              '</script>';


			return $loginCode;
		}

		static function getPluginBaseUrl() {
			$pUrl         = plugins_url();
			$baseName     = plugin_basename( __FILE__ );
			$pluginFolder = explode( DIRECTORY_SEPARATOR, $baseName )[0];

			return $pUrl . '/' . $pluginFolder;
		}

		static function curlCall( $apiPath, $params, $postParams = null ) {
			$paramString = "?client_id=" . get_option( "smartid_client_id" );
			if ( $params != null ) {
				foreach ( $params as $key => $value ) {
					$paramString .= "&$key=$value";
				}
			}

			$postParamString = "";
			if ( $postParams != null ) {
				foreach ( $postParams as $key => $value ) {
					$postParamString .= "$key=$value&";
				}
			}

			$ch  = curl_init();
			$url = "https://id.smartid.ee/" . $apiPath . $paramString;
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			if ( $postParams != null ) {
				curl_setopt( $ch, CURLOPT_POST, 1 );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $postParamString );
			}

			$curlResult = curl_exec( $ch );

			$result = json_decode( $curlResult, true );
			curl_close( $ch );

			return $result;
		}

		static function idcard_install() {
			foreach ( IdCardLogin::getSupportedMethods() as $value ) {
				add_option( $value, true );
			}

			global $wpdb;

			$table_name = $wpdb->prefix . "idcard_users";

			$sqlCreate = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,                
                firstname tinytext NOT NULL,
                lastname tinytext NOT NULL,
                identitycode VARCHAR(11) NOT NULL,
                userid bigint(20) unsigned NOT NULL,
                created_at datetime NOT NULL,
		        access_token VARCHAR(32),
                UNIQUE KEY id (id),
                UNIQUE KEY identitycode (identitycode)
                  );";

			require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
			dbDelta( $sqlCreate );

			return "Thank you for installing Smart-ID. Open Smart-ID settings to activate the service";
		}

		static function enqueueJquery() {
			wp_enqueue_script( 'jquery' );
		}

	}

	add_action( 'delete_user', 'IdCardLogin::deleteUserCleanUp' );

	add_action( 'login_footer', 'IdCardLogin::echo_id_login' );
	add_action( 'login_enqueue_scripts', 'IdCardLogin::enqueueJquery' );

	add_action( 'init', 'IdCardLogin::wpInitProcess' );
	add_action( 'wp_head', 'IdCardLogin::wpHeadProcess' );

	register_activation_hook( __FILE__, 'IdCardLogin::idcard_install' );
	add_action( 'plugins_loaded', 'IdCardLogin::idcard_install' );
	add_action( 'admin_notices', 'IdCardLogin::admin_notice' );

	add_action( 'admin_menu', 'IdcardAdmin::id_settings_page' );

	add_shortcode( 'smart_id', 'IdCardLogin::return_id_login' );
	add_shortcode( 'contract', 'IdCardLogin::display_contract_to_sign' );

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'IdCardLogin::get_settings_url' );
} 
