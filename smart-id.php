<?php
/**
 * Plugin Name: SMART-ID
 * Plugin URI: https://smartid.ee/
 * Description: Allow your visitors to login to wordpress with Estonian ID-card and mobile-ID
 * Version: 1.0.1
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

        function isLogin() {
            return array_key_exists('id-login', $_GET) && $_GET['id-login'] === "yes";
        }

        function wpInitProcess() {
            if (IdCardLogin::isLogin()) {
                require_once( plugin_dir_path(__FILE__) . 'securelogin.php');
                IdcardAuthenticate::login();
            }
        }

        function wpHeadProcess() {
            if (IdCardLogin::isLogin()) {
                if (array_key_exists('redirect_to', $_GET)) {
                    $redirectUrl = $_GET['redirect_to'];
                } else {
                    $redirectUrl = home_url();
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
            if (get_option("site_client_id") == null && array_key_exists("page", $_GET) && $_GET['page'] !== "id-signing-settings") {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>Your ID-API is almost ready! Please open <a href="<?php echo esc_url(get_admin_url(null, 'admin.php?page=id-signing-settings')) ?>">ID-API Settings</a> to Activate.</p>
                </div>
                <?php
            }
        }

        static function get_settings_url($links) {
            $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=id-signing-settings')) . '">Smart-ID</a>';
            return $links;
        }

        static function echo_id_login() {
            echo '<div style="margin:auto" align="center">'
            . IdCardLogin::getLoginButtonCode()
            . "</div>";
        }

        static function return_id_login() {
            return IdCardLogin::getLoginButtonCode();
        }

        /**
         * Use session only when it is absolutely nessecary
         * @return false if login button needs to be shown. Happens when auth_key is missing 
         * or auth key is present but WP user is not logged in.
         */
        public function isUserIdLogged() {
            if (!is_user_logged_in()) {
                return false;
            } else {
                if (!session_id()) {
                    session_start(); //use session when user has logged in but has not logged in with ID-card yet for some reason
                }
                return array_key_exists("auth_key", $_SESSION) && strlen($_SESSION['auth_key']) == 32;
            }
        }

        static function getLoginButtonCode() {
            if (IdCardLogin::isUserIdLogged()) {
                return null;
            }

            if (get_option("site_client_id") == NULL) {
                return "<b>ID login not activated yet. Login will be available as soon as admin has activated it.</b>";
            }

            $redirect_url = strlen(array_key_exists('redirect_to', $_GET)) > 0 ?
                    "&redirect_to=" . urlencode($_GET['redirect_to']) :
                    '&redirect_to=http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}";
            return '<div id="idlogin">'
                    . '<script src="https://api.smartid.dev/js/idbutton.js"></script>'
                    . '<script>'
                    . "new Button({clientId: '" . get_option("site_client_id") . "' }, function(auth_token) { "
                    . 'window.location=window.location.href+"?id-login=yes&token="+auth_token+"' . $redirect_url . '"'
                    . "});</script>";
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
            
            if (!session_id()) {
                session_start(); //API calls need to access and save data to the session
            }
            $paramString = "?site_url=" . urlencode(urlencode(explode("://", get_site_url())[1]));
            $paramString = $paramString . '&idcode=' . (array_key_exists("identitycode", $_SESSION) ? $_SESSION['identitycode'] : "");
            $paramString.= (array_key_exists("auth_key", $_SESSION) ? "&auth_key=" . $_SESSION['auth_key'] : "");
            $paramString.= "&site_secret=" . get_option("site_secret");
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
            $url = "https://api.smartid.dev/" . $apiPath . $paramString;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            if ($postParams != NULL) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postParamString);
            }
//            if ($apiPath == "api/v1/verify_domains") {
//                echo curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
//                die();
//            }


            $curlResult = curl_exec($ch);

            $result = json_decode($curlResult, true);
            curl_close($ch);
            return $result;
        }

        static function idcard_install() {

            global $wpdb;

            $table_name = $wpdb->prefix . "idcard_users";

            $sql = "CREATE TABLE if not exists $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,                
                firstname tinytext NOT NULL,
                lastname tinytext NOT NULL,
                identitycode VARCHAR(11) NOT NULL,
                userid bigint(20) unsigned NOT NULL,
                created_at datetime NOT NULL,
                UNIQUE KEY id (id),
                UNIQUE KEY identitycode (identitycode)
                  );";


            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
            return "Thank you for installing Smart-ID. Open Smart-ID settings to activate the service";
        }

        function endSession() {
            session_destroy();
        }

        function disable_password_reset() {
            return false;
        }

        function authCookieExpiration() {
            return 30 * 60;
        }

        //hack for smartid.ee page only, not affecting anybody else with the sessions nor caching
        function apiRegisterEasifier() {
            if ($_SERVER['HTTP_HOST'] != "smartid.ee") {
                return;
            }

            if (!IdCardLogin::isUserIdLogged()) {
                return;
            }

            //session already established by isUserIdLogged () if needed
            $authKey = $_SESSION['auth_key'];
            if (strlen($authKey) != 32) {
                return;
            }
            ?>
            <script type="text/javascript">
                var authKey = "<?php echo $authKey ?>";
                window.onload = function (e) {
                    var elems = document.getElementsByTagName("iframe");
                    for (var i = 0; i < elems.length; i++)
                        elems[i]["src"] = elems[i]["src"].replace('https://api.smartid.dev/register_api', 'https://api.smartid.dev/register_api?auth_key=' + authKey);
                };
            </script>
            <?php
        }

    }

    add_action('login_footer', 'IdCardLogin::echo_id_login');
    add_action('init', 'IdCardLogin::wpInitProcess');
    add_action('wp_head', 'IdCardLogin::wpHeadProcess');
    add_action('wp_logout', 'IdCardLogin::endSession');
    add_action('wp_login', 'IdCardLogin::endSession');
    add_action('wp_head', 'IdCardLogin::apiRegisterEasifier');


    register_activation_hook(__FILE__, 'IdCardLogin::idcard_install');
    add_action('plugins_loaded', 'IdCardLogin::idcard_install');
    add_action('admin_notices', 'IdCardLogin::admin_notice');


    add_action('admin_menu', 'IdcardAdmin::id_settings_page');

    add_shortcode('id_login', 'IdCardLogin::return_id_login');


    add_filter('allow_password_reset', 'IdCardLogin::disable_password_reset');
    add_filter('login_errors', create_function('$a', "return 'Not allowed!';"));
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'IdCardLogin::get_settings_url');
    add_filter('auth_cookie_expiration', 'IdCardLogin::authCookieExpiration');
} 