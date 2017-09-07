<?php
require_once( plugin_dir_path( __FILE__ ) . 'securelogin.php' );
if ( ! class_exists( "IdcardAdmin" ) ) {

	class IdcardAdmin {

		static function id_settings_page() {
			add_menu_page( 'Smart ID', 'Smart ID', 'manage_options', 'smart-id-settings', 'IdcardAdmin::create_id_settings_page' );
		}

		static function create_id_settings_page() {
			echo "<h1> Smart ID </h1>";
//            update_option("smartid_client_id", null);
			if ( ! function_exists( 'curl_version' ) ) {
				echo "cURL PHP module not installed or disabled, please enable it before starting to use Smart ID secure logins";

				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}

			if ( isset( $_GET["error"] ) ) {
				?>
                <div class="notice notice-error"><p><strong>Failed to register API.
                            Error=<?php echo $_GET["error"] ?></strong></p></div>
				<?php
			}

			//get domain data from the server
			if ( isset( $_GET["data_key"] ) && get_option( "smartid_client_id" ) == false ) {
				$params = [
					"data_key" => $_GET["data_key"]
				];

				$registerResult = IdCardLogin::curlCall( "admin/api_client_info", $params );
				if ( $registerResult["status"] == "error" ) {
					?>
                    <div class="notice notice-error"><p><strong>Failed to activate
                                registration <?php echo $registerResult["message"] ?></strong></p></div>
					<?php
					return;
				}
				$clientId     = $registerResult["client_id"];
				$secret       = $registerResult["secret"];
				$redirect_uri = $registerResult["redirect_uri"];

				// Save the posted value in the database
				update_option( "smartid_client_id", $clientId );
				update_option( "smartid_secret", $secret );
				update_option( "smartid_redirect_uri", $redirect_uri );

				// Show confirmation
				?>
                <div class="updated"><p><strong>Client registration done!</strong></p></div>
			<?php }
			?>

			<?php
			//Site has not activated Smart-ID yet
			if ( get_option( "smartid_client_id" ) == null ) {
				?>
                <div class="wrap">
					<?php include( "api_register.php" ); ?>
                </div>

				<?php
			} else {
				if ( $_POST["smartid_change_settings"] == "yes" ) {
					if ( $_POST["pt-id-card_enabled"] == "yes" ) {
						update_option( "smartid_pt-id-card_enabled", true );
					} else {
						update_option( "smartid_pt-id-card_enabled", false );
					}
					if ( $_POST["lt-mobile-id_enabled"] == "yes" ) {
						update_option( "smartid_lt-mobile-id_enabled", true );
					} else {
						update_option( "smartid_lt-mobile-id_enabled", false );
					}
					if ( $_POST["lt-id-card_enabled"] == "yes" ) {
						update_option( "smartid_lt-id-card_enabled", true );
					} else {
						update_option( "smartid_lt-id-card_enabled", false );
					}
					if ( $_POST["lv-id-card_enabled"] == "yes" ) {
						update_option( "lveid_enabled", true );
					} else {
						update_option( "lveid_enabled", false );
					}
					if ( $_POST["ee-id-card_enabled"] == "yes" ) {
						update_option( "smartid_idcard_enabled", true );
					} else {
						update_option( "smartid_idcard_enabled", false );
					}

					if ( $_POST["ee-mobile-id_enabled"] == "yes" ) {
						update_option( "smartid_mobileid_enabled", true );
					} else {
						update_option( "smartid_mobileid_enabled", false );
					}

					if ( $_POST["smart-id_enabled"] == "yes" ) {
						update_option( "smartid_smartid_enabled", true );
					} else {
						update_option( "smartid_smartid_enabled", false );
					}

					if ( $_POST["facebook_enabled"] == "yes" ) {
						update_option( "smartid_facebook_enabled", true );
					} else {
						update_option( "smartid_facebook_enabled", false );
					}

					if ( $_POST["google_enabled"] == "yes" ) {
						update_option( "smartid_google_enabled", true );
					} else {
						update_option( "smartid_google_enabled", false );
					}
				}
				?>
                <h3> This site Smart ID is now active!</h3>
                Smart ID shortcode:
                <ol>
                    <li>
                        <b>[smart_id]</b> - Creates configured login buttons.
                    </li>
                </ol>

                <br>
                All questions and support at <a href="mailto:help@smartid.ee">help@smartid.ee</a>

                <h3> Configure visible login method icons</h3>
                Make sure all of these are allowed in Smart ID admin site at <a href="https://id.smartid.dev">https://id.smartid.dev</a>
                <form method="post"
                      action="<?php echo ( isset( $_SERVER['HTTPS'] ) ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
                    <input type="hidden" name="smartid_change_settings" value="yes">
                    <table>
                        <tr>
                            <td>
                                <input type="checkbox" name="lt-mobile-id_enabled" class="column-cb"
                                       value="yes" <?php echo get_option( "smartid_lt-mobile-id_enabled" ) ? "checked" : "" ?> >
                                <label for="lt-mobile-id_enabled">Lithuanian mobile ID</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="lt-id-card_enabled" class="column-cb"
                                       value="yes" <?php echo get_option( "smartid_lt-id-card_enabled" ) ? "checked" : "" ?> >
                                <label for="lt-id-card_enabled">Lithuanian ID-card</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="lv-id-card_enabled" class="column-cb"
                                       value="yes" <?php echo get_option( "lveid_enabled" ) ? "checked" : "" ?> >
                                <label for="lv-id-card_enabled">Latvian ID-card</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="pt-id-card_enabled" class="column-cb"
                                       value="yes" <?php echo get_option( "smartid_pt-id-card_enabled" ) ? "checked" : "" ?> >
                                <label for="pt-mobile-id_enabled">Portugal ID-card</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="ee-id-card_enabled" class="column-cb"
                                       value="yes" <?php echo get_option( "smartid_idcard_enabled" ) ? "checked" : "" ?> >
                                <label for="ee-id-card_enabled">Estonian ID-card</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="ee-mobile-id_enabled" class="column-cb"
                                       value="yes" <?php echo get_option( "smartid_mobileid_enabled" ) ? "checked" : "" ?>>
                                <label for="ee-mobile-id_enabled">Estonian Mobile-ID</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="smart-id_enabled" class="column-cb"
                                       value="yes" <?php echo get_option( "smartid_smartid_enabled" ) ? "checked" : "" ?>>
                                <label for="smart-id_enabled">Smart-ID</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="facebook_enabled" class="column-cb"
                                       value="yes" <?php echo get_option( "smartid_facebook_enabled" ) ? "checked" : "" ?>>
                                <label for="facebook_enabled">Facebook</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="google_enabled" class="column-cb"
                                       value="yes" <?php echo get_option( "smartid_google_enabled" ) ? "checked" : "" ?>>
                                <label for="google_enabled">Google</label>
                            </td>
                        </tr>
                    </table>

					<?php submit_button(); ?>
                </form>
				<?php
			}
			?>
            <div class="wrap">
				<?php include( "api_manual_setup.php" );
				?>
            </div>
			<?php
		}

	}

}
    