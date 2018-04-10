<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>
<div class="container">
    <div id="loginBlock" class="col-md-offset-3 col-md-6">
        <h2>Add or edit Smart ID Oauth2.0 credentials manually</h2>
        <small>Credentials can be reviewed and generated manually at <a href="https://id.smartid.dev" target="_blank">https://id.smartid.dev</a>.
            Look for credentials to website <?php echo home_url(); ?></small>
        <br>
		<?php
		if ( array_key_exists( "smartid_manual_api_credentials", $_POST ) && $_POST["smartid_manual_api_credentials"] === "yes" ) {
			update_option( "smartid_client_id", sanitize_text_field( $_POST["client_id"] ) );
			update_option( "smartid_secret", sanitize_text_field( $_POST["secret"] ) );
			update_option( "smartid_redirect_uri", home_url() );
			?>
            <div class="updated"><p><strong>Credentials manually changed, you can try the login now.</strong></p></div>
			<?php
		} else {
			?>
            <form method="post"
                  action="<?php echo ( isset( $_SERVER['HTTPS'] ) ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
                <input type="hidden" name="smartid_manual_api_credentials" value="yes">
                <table>
                    <tr>
                        <td>
                            <label for="client_id">Client ID</label>
                        </td>
                        <td>
                            <input type="text" name="client_id" class="column-cb">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="secret">Secret</label>
                        </td>
                        <td>
                            <input type="text" name="secret" class="column-cb">
                        </td>
                    </tr>
                </table>

				<?php submit_button(); ?>
            </form>
			<?php
		}
		?>
    </div>











