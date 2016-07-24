<?php
/**
 * Plugin Name: SMART-ID
 * Plugin URI: https://smartid.ee/
 * Description: Allow your visitors to login to wordpress with Estonian ID-card and mobile-ID
 * Version: 1.1.1
 * Author: Smart ID Estonia
 * Author URI: https://smartid.ee/
 * License: GPLv2 or later

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
if (!class_exists("IdCardLogin")) {
    require_once( plugin_dir_path(__FILE__) . 'admin.php');

    class IdCardLogin {

        static function setAuthKey($authKey, $userId = null) {
            global $wpdb;
            if ($userId == null) {
                $current_user = wp_get_current_user();
                $userId = $current_user->ID;
            }

            $wpdb->update(
                    "$wpdb->prefix" . "idcard_users", [
                'auth_key' => $authKey
                    ], [
                'userid' => $userId
                    ]
            );
        }

        static function getAuthKey() {
            $user = IdCardLogin::getStoredUserData();
            if ($user != null) {
                return $user->auth_key;
            } else {
                return NULL;
            }
        }

        static function getStoredUserData() {
            global $wpdb;
            $current_user = wp_get_current_user();
            $user = $wpdb->get_row(
                    $wpdb->prepare(
                            "select * from $wpdb->prefix" . "idcard_users
		 WHERE userid=%s", $current_user->ID
                    )
            );
            return $user;
        }

        static function endSession() {
            IdCardLogin::setAuthKey(NULL);
            set_transient("site_temp_key", NULL, 300);
        }

        static function isLogin() {
            return array_key_exists('code', $_GET) && strlen($_GET['code']) === 40;
        }

        static function wpInitProcess() {
            if (IdCardLogin::isLogin()) {
                require_once( plugin_dir_path(__FILE__) . 'securelogin.php');
                IdcardAuthenticate::login($_GET['code']);
                wp_redirect(home_url('/'));
            }
        }

        static function wpHeadProcess() {
            IdCardLogin::echoJsRedirectCode();
        }

        static function echoJsRedirectCode() {
            if (IdCardLogin::isLogin()) {
                if (array_key_exists('redirect_to', $_GET)) {
                    $redirectUrl = $_GET['redirect_to'];
                } else {
                    $redirectUrl = home_url("/");
                }
                if (strpos($redirectUrl, "wp-login") > 0) {
                    $redirectUrl = home_url("/");
                }
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
            if (get_option("smartid_client_id") == null && array_key_exists("page", $_GET) && $_GET['page'] !== "smart-id-settings") {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>Your ID-API is almost ready! Please open <a href="<?php echo esc_url(get_admin_url(null, 'admin.php?page=smart-id-settings')) ?>">Smart-ID Settings</a> to Activate.</p>
                </div>
                <?php
            }
        }

        static function get_settings_url($links) {
            $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=smart-id-settings')) . '">Smart-ID Settings</a>';
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

        /**
         * @return false if login button needs to be shown. Happens when auth_key is missing 
         * or auth key is present but WP user is not logged in.
         */
        static function isUserIdLogged() {
            if (!is_user_logged_in()) {
                return false;
            } else {
                return IdCardLogin::getStoredUserData() != NULL;
            }
        }

        static function getLoginButtonCode() {
            if (IdCardLogin::isUserIdLogged()) {
                return null;
            }

            if (get_option("smartid_client_id") == NULL) {
                return "<b>ID login not activated yet. Login will be available as soon as admin has activated it.</b>";
            }

            //need to think how to use redirect_to
//            $redirect_to = strlen(array_key_exists('redirect_to', $_GET)) > 0 ?
//                    "" . urlencode($_GET['redirect_to']) :
//                    'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
//
//            if (strpos($redirect_to, "wp-login") > 0) {
//                $redirect_to = home_url("/");
//            }
//            $redirect_url = "&redirect_to=$redirect_to";

            return '<a href="https://api.smartid.ee/oauth/authorize'
                    . '?client_id=' . get_option("smartid_client_id")
                    . '&redirect_uri=' . urlencode(get_option("smartid_redirect_uri"))
                    . '&response_type=code"><img src="' . IdCardLogin::getPluginBaseUrl() . '/img/idkaart.gif"></img></a>';
        }

        static function getPluginBaseUrl() {
            $pUrl = plugins_url();
            $baseName = plugin_basename(__FILE__);
            $pluginFolder = explode(DIRECTORY_SEPARATOR, $baseName)[0];
            return $pUrl . '/' . $pluginFolder;
        }

        /**
         * 
         * @param type $apiPath API path where to send the request
         * @param type $params GET parameters in array format
         * @param type $postParams if not null then call will be post and these params will be added to the POST 
         * @return type
         */
        static function curlCall($apiPath, $params, $postParams = null) {
            $paramString = "?client_id=" . get_option("smartid_client_id");
            if ($params != NULL) {
                foreach ($params as $key => $value) {
                    $paramString.="&$key=$value";
                }
            }

            if ($postParams != NULL) {
                foreach ($postParams as $key => $value) {
                    $postParamString.="$key=$value&";
                }
            }

            $ch = curl_init();
            $url = "https://api.smartid.ee/" . $apiPath . $paramString;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            if ($postParams != NULL) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postParamString);
            }

            $curlResult = curl_exec($ch);

            $result = json_decode($curlResult, true);
            curl_close($ch);
            return $result;
        }

        static function idcard_install() {

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
            dbDelta($sqlCreate);
            return "Thank you for installing Smart-ID. Open Smart-ID settings to activate the service";
        }

        static function disable_password_reset() {
            return false;
        }

        static function authCookieExpiration() {
            return 30 * 60;
        }

        static function enqueueJquery() {
            wp_enqueue_script('jquery');
        }

    }

    add_action('login_footer', 'IdCardLogin::echo_id_login');
    add_action('login_enqueue_scripts', 'IdCardLogin::enqueueJquery');

    add_action('init', 'IdCardLogin::wpInitProcess');
    add_action('wp_head', 'IdCardLogin::wpHeadProcess');
    add_action('wp_logout', 'IdCardLogin::endSession');


    register_activation_hook(__FILE__, 'IdCardLogin::idcard_install');
    add_action('plugins_loaded', 'IdCardLogin::idcard_install');
    add_action('admin_notices', 'IdCardLogin::admin_notice');


    add_action('admin_menu', 'IdcardAdmin::id_settings_page');

    add_shortcode('smart-id', 'IdCardLogin::return_id_login');

    add_filter('allow_password_reset', 'IdCardLogin::disable_password_reset');
    add_filter('login_errors', create_function('$a', "return 'Not allowed!';"));
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'IdCardLogin::get_settings_url');
    add_filter('auth_cookie_expiration', 'IdCardLogin::authCookieExpiration');
} 
