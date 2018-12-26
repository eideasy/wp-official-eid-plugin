<?php
/**
 * Plugin Name: SMART-ID
 * Plugin URI: https://smartid.ee/
 * Description: Allow your visitors to login to Wordpress ID-card, Mobile-ID, Smart-ID mobile app and other methods.
 * Version: 3.3
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

if ( ! class_exists("IdCardLogin")) {
    require_once(plugin_dir_path(__FILE__) . 'admin.php');


    class IdCardLogin
    {

        public static function save_custom_user_profile_fields($user_id)
        {
            if ( ! current_user_can('administrator')) {
                return;
            }

            global $wpdb;
            $prefix = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;

            $table_name = $prefix . "idcard_users";

            $idcode = esc_attr($_POST['smartid_user_idcode']);

            $existingUser = $wpdb->get_row(
                $wpdb->prepare("select * from $table_name WHERE identitycode=%s", $idcode)
            );

            if ($existingUser != null && $existingUser->userid == $user_id) {
                return; // same user updated, no need to do anything
            }

            if ($existingUser != null) {
                $wpdb->delete($table_name, ['identitycode' => $idcode]);
                $wpdb->update($table_name, ['identitycode' => $idcode], ['userid' => $user_id]);
            } else {
                $wpdb->delete($table_name, ['userid' => $user_id]);
                $wpdb->insert($table_name, array(
                        'firstname'    => "",
                        'lastname'     => "",
                        'identitycode' => $idcode,
                        'userid'       => $user_id,
                        'created_at'   => current_time('mysql')
                    )
                );
            }
        }


        public static function custom_user_profile_fields($user)
        {
            if ( ! current_user_can('administrator')) {
                return;
            }
            ?>

            <table class="form-table">
                <tbody>
                <tr class="user-email-wrap">
                    <th><label for="smartid_user_idcode">Country + ID code (EE_47102281234)</label></th>
                    <td>
                        <input name="smartid_user_idcode"
                               value="<?php echo esc_attr(IdCardLogin::getIdcodeByUserId($user->ID)); ?>"
                               class='regular-text'/>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php
        }

        public static function getIdcodeByUserId($userId)
        {
            global $wpdb;
            $prefix = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;

            $table_name = $prefix . "idcard_users";
            $user       = $wpdb->get_row(
                $wpdb->prepare("select * from $table_name WHERE userid=%s", $userId)
            );

            if ($user == null) {
                return "";
            } else {
                return $user->identitycode;
            }

        }

        public static function getSupportedMethods()
        {
            $smartid_supportedMethods = [
                "smartid_lt-mobile-id_enabled",
                "smartid_lt-id-card_enabled",
                "smartid_pt-id-card_enabled",
                "lveid_enabled",
                "smartid_idcard_enabled",
                "smartid_mobileid_enabled",
                "smartid_smartid_enabled",
                "smartid_facebook_enabled",
                "smartid_google_enabled",
            ];

            return $smartid_supportedMethods;
        }

        static function deleteUserCleanUp($user_id)
        {
            global $wpdb;
            $prefix = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;
            $wpdb->delete($prefix . "idcard_users", array('userid' => $user_id));
        }

        static function getStoredUserData()
        {
            global $wpdb;
            $current_user = wp_get_current_user();
            $prefix       = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;
            $user         = $wpdb->get_row(
                $wpdb->prepare("select * from $prefix" . "idcard_users		 WHERE userid=%s", $current_user->ID)
            );

            return $user;
        }

        static function isLogin()
        {
            return array_key_exists('code', $_GET) && strlen($_GET['code']) === 40;
        }

        static function wpInitProcess()
        {
            if (IdCardLogin::isLogin()) {
                if (IdcardAuthenticate::isAlreadyLogged()) {
                    $loginUrl = apply_filters('smartid_login', home_url());
                    wp_redirect($loginUrl);
                    exit;
                }
                if (get_option('smartid_debug_mode')) {
                    file_get_contents("https://id.smartid.ee/confirm_progress?message=" . urlencode("WP plugin login with code=" . $_GET['code']));
                }
                require_once(plugin_dir_path(__FILE__) . 'securelogin.php');
                IdcardAuthenticate::login($_GET['code']);

                wp_register_script('login_refresh', plugins_url('login_refresh.js', __FILE__));
                wp_enqueue_script("login_refresh", false, ["jquery"]);
                wp_localize_script("login_refresh",
                    'settings', ['debugMode' => get_option('smartid_debug_mode') ? "true" : "false"]
                );
            }
        }

        static function admin_notice()
        {
            if (get_option("smartid_client_id") == null && array_key_exists("page",
                    $_GET) && $_GET['page'] !== "smart-id-settings") {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>Your Smart-ID is almost ready! Please open
                        <a href="<?php echo esc_url(get_admin_url(null, 'admin.php?page=smart-id-settings')) ?>">Smart-ID
                            Settings
                        </a> to Activate.
                    </p>
                </div>
                <?php
            }
        }

        static function get_settings_url($links)
        {
            $links[] = '<a href="' . esc_url(get_admin_url(null,
                    'admin.php?page=smart-id-settings')) . '">Smart-ID Settings</a>';

            return $links;
        }

        static function echo_id_login()
        {
            echo '<div style="margin:auto" align="center">'
                 . IdCardLogin::getLoginButtonCode()
                 . "</div>";
        }

        static function return_id_login()
        {
            return IdCardLogin::getLoginButtonCode();
        }

        static function display_contract_to_sign($atts)
        {
            if (get_option("smartid_client_id") == null) {
                return "<b>Smart-ID service not activated, cannot sign the contract";
            }
            if ( ! array_key_exists("id", $atts)) {
                return "<b>Contract ID missing, cannot show signing page</b>";
            }
            $code = '<iframe src="https://id.smartid.ee/sign_contract?client_id='
                    . get_option("smartid_client_id") . "&contract_id=" . $atts["id"] . '"'
                    . 'style="height: 100vh; width: 100vw" frameborder="0"></iframe>';

            return $code;
        }

        /**
         * @return false if login button needs to be shown. Happens when auth_key is missing
         * or auth key is present but WP user is not logged in.
         */
        static function isUserIdLogged()
        {
            if ( ! is_user_logged_in()) {
                return false;
            } else {
                return IdCardLogin::getStoredUserData() != null;
            }
        }

        static function getLoginButtonCode()
        {
            if (IdCardLogin::isUserIdLogged()) {
                return null;
            }

            if (get_option("smartid_client_id") == null) {
                return "<b>ID login not activated yet. Login will be available as soon as admin has activated it.</b>";
            }

            $allDisabled = true;
            foreach (IdCardLogin::getSupportedMethods() as $method) {
                if (get_option($method) != false) {
                    $allDisabled = false;
                    break;
                }
            }
            if ($allDisabled) {
                return "<b>No Secure login methods enabled yet in Wordpress admin, please contact administrator to enable these from Smart ID config</b>";
            }
            $redirectUri = urlencode(get_option("smartid_redirect_uri"));
            $clientId    = get_option("smartid_client_id");
            $loginUri    = 'https://id.smartid.ee/oauth/authorize'
                           . '?client_id=' . $clientId
                           . '&redirect_uri=' . $redirectUri
                           . '&response_type=code';

            $loginCode = '<script src="' . IdCardLogin::getPluginBaseUrl() . '/smartid_functions.js"></script>';
            if (get_option("smartid_idcard_enabled")) {
                $loginCode .= '<img id="smartid-id-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/id-card.svg" height="46" width="130" style="display:inline; margin: 3px">';
            }
            if (get_option("smartid_mobileid_enabled")) {
                $loginCode .= '<img id="smartid-mid-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/mobile-id.svg" height="46" width="130" style="display:inline; margin: 3px">';
            }
            if (get_option("lveid_enabled")) {
                $loginCode .= '<img id="smartid-lveid-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/latvia_eid.png" height="46" width="130" style="display:inline; margin: 3px">';
            }
            if (get_option("smartid_lt-id-card_enabled")) {
                $loginCode .= '<img id="smartid-lt-id-card-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/lithuania_eid.png" height="46" width="130" style="display:inline; margin: 3px">';
            }
            if (get_option("smartid_lt-mobile-id_enabled")) {
                $loginCode .= '<img id="smartid-lt-mobile-id-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/lt-mobile-id.png" height="46" width="130" style="display:inline; margin: 3px">';
            }
            if (get_option("smartid_pt-id-card_enabled")) {
                $loginCode .= '<img id="smartid-pt-id-card-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/portugal-id-card.png" height="46" width="200" style="display:inline; margin: 3px">';
            }
            if (get_option("smartid_smartid_enabled")) {
                $loginCode .= '<img id="smartid-smartid-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/smart-id-white.png" height="46" width="46" style="display:inline; margin: 3px">';
            }
            if (get_option("smartid_google_enabled")) {
                $loginCode .= '<img id="smartid-gp-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/gp.png" height="46" width="46" style="display:inline; margin: 3px">';
            }
            if (get_option("smartid_facebook_enabled")) {
                $loginCode .= '<img id="smartid-fb-login" src="' . IdCardLogin::getPluginBaseUrl() . '/img/fb.png" height="46" width="46" style="display:inline; margin: 3px">';
            }

            $loginCode .= '<script>' .
                          '    if(document.getElementById("smartid-id-login")) document.getElementById("smartid-id-login").addEventListener("click", function () {' .
                          '        startSmartIdLogin("' . $loginUri . '&start=ee-id-card");' .
                          '    });' .
                          '    if(document.getElementById("smartid-mid-login")) document.getElementById("smartid-mid-login").addEventListener("click", function () {' .
                          '        startSmartIdLogin("' . $loginUri . '&method=ee-mobile-id");' .
                          '    });' .
                          '    if(document.getElementById("smartid-lveid-login")) document.getElementById("smartid-lveid-login").addEventListener("click", function () {' .
                          '        startSmartIdLogin("' . $loginUri . '&start=lv-id-card");' .
                          '    });' .
                          '    if(document.getElementById("smartid-lt-id-card-login")) document.getElementById("smartid-lt-id-card-login").addEventListener("click", function () {' .
                          '        startSmartIdLogin("' . $loginUri . '&start=lt-id-card");' .
                          '    });' .
                          '    if(document.getElementById("smartid-pt-id-card-login")) document.getElementById("smartid-pt-id-card-login").addEventListener("click", function () {' .
                          '        startSmartIdLogin("' . $loginUri . '&start=pt-id-card");' .
                          '    });' .
                          '    if(document.getElementById("smartid-lt-mobile-id-login")) document.getElementById("smartid-lt-mobile-id-login").addEventListener("click", function () {' .
                          '        startSmartIdLogin("' . $loginUri . '&method=lt-mobile-id");' .
                          '    });' .
                          '    if(document.getElementById("smartid-smartid-login")) document.getElementById("smartid-smartid-login").addEventListener("click", function () {' .
                          '        startSmartIdLogin("' . $loginUri . '&method=smart-id");' .
                          '    });' .
                          '    if(document.getElementById("smartid-gp-login")) document.getElementById("smartid-gp-login").addEventListener("click", function () {' .
                          '        startSmartIdLogin("' . $loginUri . '&start=google-login");' .
                          '    });' .
                          '    if(document.getElementById("smartid-fb-login")) document.getElementById("smartid-fb-login").addEventListener("click", function () {' .
                          '        startSmartIdLogin("' . $loginUri . '&start=facebook-login");' .
                          '    });' .
                          '</script>';

            return $loginCode;
        }

        static function getPluginBaseUrl()
        {
            $pUrl         = plugins_url();
            $baseName     = plugin_basename(__FILE__);
            $pluginFolder = explode(DIRECTORY_SEPARATOR, $baseName)[0];

            return $pUrl . '/' . $pluginFolder;
        }

        static function curlCall($apiPath, $params, $postParams = null)
        {
            $paramString = "?client_id=" . get_option("smartid_client_id");
            if ($params != null) {
                foreach ($params as $key => $value) {
                    $paramString .= "&$key=$value";
                }
            }

            $postParamString = "";
            if ($postParams != null) {
                foreach ($postParams as $key => $value) {
                    $postParamString .= "$key=$value&";
                }
            }

            $ch  = curl_init();
            $url = "https://id.smartid.ee/" . $apiPath . $paramString;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            if ($postParams != null) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postParamString);
            }

            $curlResult = curl_exec($ch);

            $result = json_decode($curlResult, true);
            curl_close($ch);

            return $result;
        }

        static function idcard_install()
        {
            foreach (IdCardLogin::getSupportedMethods() as $value) {
                add_option($value, true);
            }

            global $wpdb;

            $prefix = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;

            $table_name = $prefix . "idcard_users";

            $sqlCreate = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,                
                firstname tinytext NOT NULL,
                lastname tinytext NOT NULL,
                identitycode VARCHAR(21) NOT NULL,
                userid bigint(20) unsigned NOT NULL,
                created_at datetime NOT NULL,
		        access_token VARCHAR(32),
                UNIQUE KEY id (id),
                UNIQUE KEY identitycode (identitycode)
                  );";

            require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
            dbDelta($sqlCreate);

            return "Thank you for installing Smart-ID. Open Smart-ID settings to activate the service";
        }

        static function enqueueJquery()
        {
            wp_enqueue_script('jquery');
        }

    }

    add_action('delete_user', 'IdCardLogin::deleteUserCleanUp');

    add_action('login_footer', 'IdCardLogin::echo_id_login');
    add_action('login_enqueue_scripts', 'IdCardLogin::enqueueJquery');

    add_action('init', 'IdCardLogin::wpInitProcess');

    register_activation_hook(__FILE__, 'IdCardLogin::idcard_install');
    add_action('plugins_loaded', 'IdCardLogin::idcard_install');
    add_action('admin_notices', 'IdCardLogin::admin_notice');

    add_action('admin_menu', 'IdcardAdmin::id_settings_page');

    add_action('show_user_profile', 'IdCardLogin::custom_user_profile_fields');
    add_action('edit_user_profile', 'IdCardLogin::custom_user_profile_fields');
    add_action('profile_update', 'IdCardLogin::save_custom_user_profile_fields');

    add_shortcode('smart_id', 'IdCardLogin::return_id_login');
    add_shortcode('contract', 'IdCardLogin::display_contract_to_sign');

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'IdCardLogin::get_settings_url');
} 
