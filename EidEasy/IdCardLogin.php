<?php

namespace EidEasy;
require_once 'IdcardAuthenticate.php';

class IdCardLogin
{
    public static function save_custom_user_profile_fields($user_id)
    {
        if (!current_user_can('administrator')) {
            return;
        }

        if (!array_key_exists('eideasy_user_idcode', $_POST)) {
            return; // New idcode not included in post, not changing the idcode field.
        }

        $idcode = esc_attr($_POST['eideasy_user_idcode']);
        if (!$idcode || strlen($idcode) === 0) {
            return; // Not allowing to completely remove idcode.
        }

        global $wpdb;
        $prefix = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;

        $table_name = $prefix . "idcard_users";

        $existingUser = $wpdb->get_row(
            $wpdb->prepare("select * from $table_name WHERE identitycode=%s", $idcode)
        );

        if ($existingUser != null && $existingUser->userid == $user_id) {
            return; // same user updated, no need to do anything
        }

        if ($existingUser != null) {
            if ($idcode != "-") {
                $wpdb->delete($table_name, ['identitycode' => $idcode]);
            }
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
        if (!current_user_can('administrator')) {
            return;
        }
        ?>

        <table class="form-table">
            <tbody>
            <tr class="user-email-wrap">
                <th><label for="eideasy_user_idcode">Country + ID code (EE_47102281234)</label></th>
                <td>
                    <input name="eideasy_user_idcode"
                           value="<?php echo esc_attr(IdCardLogin::getIdcodeByUserId($user->ID)); ?>"
                           class='regular-text'/>
                    <br>
                    <small>To remove ID code value write here dash without quotes "-". Empty field will be
                        ignored</small>
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
        $supportedMethods = [
            'eideasy_smartid_enabled'          => [
                'name'         => 'Smart-ID',
                'icon'         => 'img/Smart-ID_login_btn.png',
                'filter'       => 'smart-id-login',
                'class'        => 'login-middle-w',
                'start_action' => 'smart-id-login',
            ],
            'eideasy_ee_mobileid_enabled'      => [
                'name'         => 'Estonian Mobile-ID',
                'icon'         => 'img/eid_mobiilid_mark.png',
                'filter'       => 'ee-mobile-id-login',
                'start_action' => 'ee-mid-login',
                'login_extra'  => '&country=EE',
            ],
            'eideasy_ee_idcard_enabled'        => [
                'name'         => 'Estonian ID card',
                'icon'         => 'img/eid_idkaart_mark.png',
                'filter'       => 'ee-id-card-login',
                'class'        => 'login-middle-w',
                'start_action' => 'ee-id-card',
            ],
            'eideasy_eparaksts_mobile_enabled' => [
                'name'         => 'Latvia eParaksts Mobile',
                'icon'         => 'img/eparaksts-mobile.png',
                'filter'       => 'eideasy-eparaksts-mobile-login',
                'start_action' => 'lv-eparaksts-mobile-login',
            ],
            'eideasy_lv_idcard_enabled'        => [
                'name'         => 'Latvian ID card',
                'icon'         => 'img/latvia-id-card.png',
                'filter'       => 'lv-id-card-login',
                'class'        => 'login-middle-w',
                'start_action' => 'lv-id-card',
            ],
            'eideasy_lt_mobileid_enabled'      => [
                'name'         => 'Lithuanian Mobile-ID',
                'icon'         => 'img/lt-mobile-id.png',
                'filter'       => 'lt-mobile-id-login',
                'start_action' => 'lt-mid-login',
                'login_extra'  => '&country=LT',
            ],
            'eideasy_lt_idcard_enabled'        => [
                'name'         => 'Lithuanian ID card',
                'icon'         => 'img/lithuania_eid.png',
                'filter'       => 'lt-id-card-login',
                'class'        => 'login-middle-w',
                'start_action' => 'lt-id-card',
            ],
            'eideasy_pt_idcard_enabled'        => [
                'name'         => 'Portugese ID card',
                'icon'         => 'img/portugal-id-card.png',
                'filter'       => 'pt-id-card-login',
                'class'        => 'login-wide-w',
                'start_action' => 'pt-id-card',
            ],
            'eideasy_be_idcard_enabled'        => [
                'name'         => 'Belgian ID card',
                'icon'         => 'img/belgia-id-card.svg',
                'filter'       => 'be-id-card-login',
                'class'        => 'login-middle-w',
                'start_action' => 'be-id-card',
            ],
            'eideasy_google_enabled'           => [
                'name'         => 'Google',
                'icon'         => 'img/gp.png',
                'filter'       => 'google-login',
                'start_action' => 'google-login',
                'class'        => 'login-square-w',
            ],
            'eideasy_facebook_enabled'         => [
                'name'         => 'Facebook',
                'icon'         => 'img/fb.png',
                'filter'       => 'facebook-login',
                'start_action' => 'facebook-login',
                'class'        => 'login-square-w',
            ],
            'eideasy_zealid_enabled'           => [
                'name'         => 'ZealID',
                'icon'         => 'img/zealid.svg',
                'filter'       => 'eideasy-zealid-login',
                'start_action' => 'zealid-login',
                'class'        => 'login-middle-w',
            ],
        ];

        return $supportedMethods;
    }

    public static function deleteUserCleanUp($user_id)
    {
        global $wpdb;
        $prefix = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;
        $wpdb->delete($prefix . "idcard_users", array('userid' => $user_id));
    }

    public static function getStoredUserData()
    {
        global $wpdb;
        $current_user = wp_get_current_user();
        if (!$current_user) {
            return null;
        }
        $prefix = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;
        $user   = $wpdb->get_row(
            $wpdb->prepare("select * from $prefix" . "idcard_users WHERE userid=%s", $current_user->ID)
        );

        return $user;
    }

    public static function isLogin()
    {
        return array_key_exists('code', $_GET) && strlen($_GET['code']) > 20;
    }

    public static function wpInitProcess()
    {
        $pluginVersion = get_plugin_data(__FILE__);
        if (isset($pluginVersion['Version'])) {
            update_option('eideasy_plugin_version', $pluginVersion['Version']);
        }

        $version = isset($pluginVersion['Version']) ? $pluginVersion['Version'] : date("ymd-Gis", filemtime(plugin_dir_path(__FILE__)));
        wp_register_script('eideasy_functions_js', plugins_url('../eideasy_functions.js', __FILE__), [], $version);

        if (IdCardLogin::isLogin()) {
            $loginUrl = apply_filters('eideasy_login', get_option('eideasy_redirect_uri'));
            if (IdcardAuthenticate::isAlreadyLogged() && !get_option('eideasy_only_identify')) {
                wp_redirect($loginUrl);
                exit;
            }
            if (get_option('eideasy_debug_mode')) {
                file_get_contents("https://id.eideasy.com/confirm_progress?message=" . urlencode("WP plugin login with code=" . $_GET['code']));
            }

            $userId = IdcardAuthenticate::login($_GET['code']);
            if ($userId) {
                wp_redirect($loginUrl);
                exit;
            }
        }
    }

    public static function admin_notice()
    {
        if (get_option("eideasy_client_id") == null && array_key_exists("page", $_GET) && $_GET['page'] !== "eid-easy-settings") {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Your eID Easy is almost ready! Please open
                    <a href="<?php echo esc_url(get_admin_url(null, 'admin.php?page=eid-easy-settings')) ?>">
                        eID Easy Settings </a> to activate.
                </p>
            </div>
            <?php
        }
    }

    public static function get_settings_url($links)
    {
        $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=eid-easy-settings')) . '">eID Easy Settings</a>';

        return $links;
    }

    public static function echo_id_login()
    {
        if (!get_option('eideasy_identity_only')) {
            echo '<div style="margin:auto" align="center">'
                . IdCardLogin::getLoginButtonCode()
                . "</div>";
        }
    }

    public static function return_id_login()
    {
        return IdCardLogin::getLoginButtonCode();
    }

    static function display_contract_to_sign($atts)
    {
        if (get_option("eideasy_client_id") == null) {
            return "<b>eID Easy service not activated, cannot sign the contract";
        }
        if (!array_key_exists("id", $atts)) {
            return "<b>Contract ID missing, cannot show signing page</b>";
        }
        $code = '<iframe src="https://id.eideasy.com/sign_contract?client_id='
            . get_option("eideasy_client_id") . "&contract_id=" . $atts["id"] . '"'
            . 'style="height: 100vh; width: 100vw" frameborder="0"></iframe>';

        return $code;
    }

    /**
     * @return false if login button needs to be shown. Happens when auth_key is missing
     * or auth key is present but WP user is not logged in.
     */
    static function isUserIdLogged()
    {
        if (!is_user_logged_in()) {
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

        if (get_option("eideasy_client_id") == null) {
            return "<b>ID login not activated yet. Login will be available as soon as admin has activated it.</b>";
        }

        $allDisabled = true;
        foreach (array_keys(IdCardLogin::getSupportedMethods()) as $method) {
            if (get_option($method) != false) {
                $allDisabled = false;
                break;
            }
        }
        if ($allDisabled) {
            return "<b>No Secure login methods enabled yet in Wordpress admin, please contact administrator to enable these from eID Easy config</b>";
        }
        $redirectUri = urlencode(get_option("eideasy_redirect_uri"));
        $clientId    = get_option("eideasy_client_id");
        $urlParams   = '?client_id=' . $clientId
            . '&redirect_uri=' . $redirectUri
            . '&response_type=code';
        $baseUri     = 'https://id.eideasy.com';
        $loginUri    = $baseUri . "/oauth/authorize" . $urlParams;

        wp_enqueue_script("eideasy_functions_js");

        $loginCode = '<style>
                #eideasy-login-block .login-button {
                    display:inline;
                    margin-left: 5px;
                    margin-right: 5px;
                }
                #eideasy-login-block .login-button img {                    
                    margin: 3px;
                    height: 46px;
                }
                #eideasy-login-block .login-square-w img {
                    width: 46px;
                }
                #eideasy-login-block .login-middle-w img {
                    width: 130px;
                }
                #eideasy-login-block .login-wide-w img {
                    width: 200px;
                }                
            </style><div id="eideasy-login-block">';

        foreach (IdCardLogin::getSupportedMethods() as $method => $params) {
            if (get_option($method)) {
                $loginCode .= '<div id="' . $method . '"  class="login-button">' .
                    apply_filters($params['filter'], '<img src="' . IdCardLogin::getPluginBaseUrl() . "/" . $params['icon']) . '">' .
                    '</div>';
                $loginCode .= '<script>if(document.getElementById("' . $method . '")) document.getElementById("' . $method . '").addEventListener("click", function () {' .
                    '        startEidEasyLogin("' . $loginUri . '&start=' . $params['start_action'] . ($params['login_extra'] ?? "") . '&lang=' . get_locale() . '");' .
                    '    });</script>';
            }
        }

        $loginCode .= '</div>';

        return $loginCode;
    }

    static function getPluginBaseUrl()
    {
        $pUrl     = plugins_url();
        $baseName = plugin_basename(__FILE__);
        // Remove script name and keep only path. DIRECTORY_SEPARATOR is having trouble in IIS
        $pluginFolder = substr($baseName, 0, -24);
        return $pUrl . '/' . $pluginFolder;
    }

    static function curlCall($apiPath, $params, $postParams = null)
    {
        $paramString = "?client_id=" . get_option("eideasy_client_id");
        if ($params != null) {
            foreach ($params as $key => $value) {
                if ($key === "access_token") {
                    $token = "authorization: Bearer $value";
                } else {
                    $paramString .= "&$key=$value";
                }
            }
        }

        $postParamString = "";
        if ($postParams != null) {
            foreach ($postParams as $key => $value) {
                $postParamString .= "$key=$value&";
            }
        }

        $ch  = curl_init();
        $url = "https://id.eideasy.com/" . $apiPath . $paramString;
        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($token)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [$token]);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($postParams != null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postParamString);
        }

        $curlResult = curl_exec($ch);

        $result = json_decode($curlResult, true);
        curl_close($ch);

        return $result;
    }

    public static function idcard_install()
    {
        // Migrate from old options.
        if (!get_option('eideasy_client_id') && get_option('smartid_client_id')) {
            self::migrateOptions();
        }

        $alreadyUsed = false;
        foreach (array_keys(IdCardLogin::getSupportedMethods()) as $method) {
            if (get_option($method)) {
                $alreadyUsed = true;
                break;
            }
        }

        // Activate all methods only on first install.
        if (!$alreadyUsed) {
            foreach (array_keys(IdCardLogin::getSupportedMethods()) as $method) {
                add_option($method, true);
            }
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

        return "Thank you for installing eID Easy. Open eID Easy settings to activate the service";
    }

    static function migrateOptions()
    {
        $toMigrate = [
            'smartid_client_id'                => 'eideasy_client_id',
            'smartid_secret'                   => 'eideasy_secret',
            'smartid_redirect_uri'             => 'eideasy_redirect_uri',
            'smartid_registration_disabled'    => 'eideasy_registration_disabled',
            'smartid_debug_mode'               => 'eideasy_debug_mode',
            'smartid_google_enabled'           => 'eideasy_google_enabled',
            'smartid_facebook_enabled'         => 'eideasy_facebook_enabled',
            'smartid_smartid_enabled'          => 'eideasy_smartid_enabled',
            'smartid_mobileid_enabled'         => 'eideasy_ee_mobileid_enabled',
            'eideasy_lt-mobile-id_enabled'     => 'eideasy_lt_mobileid_enabled',
            'smartid_idcard_enabled'           => 'eideasy_ee_idcard_enabled',
            'smartid_be-id-card_enabled'       => 'eideasy_be_idcard_enabled',
            'smartid_pt-id-card_enabled'       => 'eideasy_pt_idcard_enabled',
            'lveid_enabled'                    => 'eideasy_lv_idcard_enabled',
            'smartid_lt-id-card_enabled'       => 'eideasy_lt_idcard_enabled',
            'eideasy-eparaksts-mobile_enabled' => 'eideasy_eparaksts_mobile_enabled',

        ];

        foreach ($toMigrate as $old => $new) {
            if (get_option($old)) {
                update_option($new, get_option($old));
                delete_option($old);
            }
        }
    }

    static function enqueueJquery()
    {
        wp_enqueue_script('jquery');
    }
}
