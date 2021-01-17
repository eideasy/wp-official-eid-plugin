<?php

namespace EidEasy;

class IdcardAdmin
{
    static function idSettingsPage()
    {
        add_menu_page('eID Easy Settings', 'eID Easy', 'manage_options', 'eid-easy-settings', [IdcardAdmin::class, 'createIdSettingsPage']);
    }

    static function createIdSettingsPage()
    {
        if (!function_exists('curl_version')) {
            echo "cURL PHP module not installed or disabled, please enable it before starting to use eID Easy secure logins";

            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }
        $default_tab = null;
        $tab         = sanitize_text_field(isset($_GET['tab']) ? $_GET['tab'] : $default_tab);
        ?>

        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>

            <?php if (!get_option('eideasy_client_id') || !get_option('eideasy_secret')) { ?>
                <p>
                    Sign up for client_id/secret from <a href="https://id.eideasy.com/signup?source=wp_plugin&domain=<?php echo home_url(); ?>" target="_blank">https://id.eideasy.com/signup</a>
                </p>
            <?php } ?>

            <nav class="nav-tab-wrapper">
                <a href="?page=eid-easy-settings"
                   class="nav-tab <?php if (strlen($tab) === 0) { ?>nav-tab-active<?php } ?>">
                    Settings
                </a>
                <a href="?page=eid-easy-settings&tab=woo-integration"
                   class="nav-tab <?php if ($tab === 'woo-integration') { ?>nav-tab-active<?php } ?>">
                    WooCommerce age validator settings
                </a>
            </nav>

            <?php
            if (strlen($tab) === 0) {
                self::mainSettingsPage();
            } else {
                self::wooIntegrationSettings();
            }
            ?>
        </div>
        <?php
    }

    static function mainSettingsPage()
    {
        ?>
        <form action="options.php" method="post">
            <?php
            if (get_option('eideasy_client_id')) {
                self::settingsForm();
            }

            self::apiCredentials();

            settings_fields('eideasy');
            do_settings_sections('eideasy');

            // output save settings button
            submit_button('Save settings');

            ?>
        </form>
        <?php
    }

    public static function getCategories()
    {
        $categoryList = [];

        $allCategories = get_terms('product_cat');

        if ($allCategories) {
            foreach ($allCategories as $cat) {
                $categoryList[] = [
                    'id'   => $cat->term_id,
                    'name' => $cat->name,
                ];
            }
        }

        return $categoryList;
    }

    static function wooIntegrationSettings()
    {
        $allCategories = self::getCategories();
        ?>
        <h3>WooCommerce restricted age verification category settings</h3>
        <form action="options.php" method="post">
            <table>
                <tr>
                    <td>
                        <input type="checkbox" name="eideasy_woo_age_check_enabled" class="column-cb"
                               value="1" <?php checked('1', get_option('eideasy_woo_age_check_enabled')); ?> />
                        <label for="eideasy_woo_age_check_enabled">Age check enabled</label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="eideasy_woo_min_age">Minimum age for restricted categories</label>
                    </td>
                    <td>
                        <input type="text" name="eideasy_woo_min_age" class="column-cb" value="<?php echo get_option('eideasy_woo_min_age'); ?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="eideasy_woo_age_restricted_categories">Restricted categories</label>
                    </td>
                    <td>
                        <select name="eideasy_woo_age_restricted_categories[]" multiple size="<?php echo count($allCategories) ?>">
                            <?php
                            $selectedOptions = get_option('eideasy_woo_age_restricted_categories');
                            foreach ($allCategories as $category) {
                                if (in_array($category['id'], $selectedOptions)) {
                                    echo '<option value="' . $category['id'] . '" selected>' . $category['name'] . '</option>';
                                } else {
                                    echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                                }
                            }
                            ?>
                            ?>
                        </select>
                    </td>
                </tr>
            </table>

            <?php

            settings_fields('eideasy_woo');
            do_settings_sections('eideasy_woo');

            // output save settings button
            submit_button('Save settings');

            ?>
        </form>

        <br>
        <h3>eID Easy shortcode</h3>
        <ol>
            <li>
                <b>[eid_easy]</b> - Creates configured login buttons to the location .
            </li>
        </ol>

        <br>
        All questions and support at <a href="mailto:info@eideasy.com">info@eideasy.com</a>
        <?php
    }

    static function apiCredentials()
    {
        ?>
        <div id="loginBlock" class="col-md-offset-3 col-md-6">
            <h2>Add or edit eID Easy Oauth2.0 credentials</h2>
            <p>Activate the service and get client_id / secret from
                <a href="https://id.eideasy.com/signup?source=wp_plugin&domain=<?php echo home_url(); ?>" target="_blank">https://id.eideasy.com/signup</a>.</p>

            <table>
                <tr>
                    <td>
                        <label for="eideasy_client_id">Client ID</label>
                    </td>
                    <td>
                        <input type="text" name="eideasy_client_id" class="column-cb" value="<?php echo get_option('eideasy_client_id'); ?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="eideasy_secret">Secret</label>
                    </td>
                    <td>
                        <input type="password" name="eideasy_secret" class="column-cb" value="<?php echo get_option('eideasy_secret'); ?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="eideasy_redirect_uri">Redirect URI</label>
                    </td>
                    <td>
                        <input type="text" name="eideasy_redirect_uri" class="column-cb"
                               value="<?php echo strlen(get_option('eideasy_redirect_uri')) > 5 ? get_option('eideasy_redirect_uri') : home_url(); ?>">
                    </td>
                </tr>
            </table>
            <br>
        </div>
        <?php
    }

    static function settingsForm()
    {
        ?>

        <h3>Registration disabled</h3>

        <input type="checkbox" name="eideasy_registration_disabled" class="column-cb"
               value="1" <?php checked('1', get_option('eideasy_registration_disabled')); ?> />
        <label for="eideasy_registration_disabled">Disable automatic registration. In this case admin must add idcode
            to each user manually to allow ID card login.</label>

        <h3>Only identify users (no user login)</h3>

        <input type="checkbox" name="eideasy_only_identify" class="column-cb"
               value="yes" <?php echo get_option("eideasy_only_identify") ? "checked" : "" ?> >
        <label for="eideasy_only_identify">No accounts are created nor any users are logged in. You can get users details using action "eideasy_only_identify.</label>

        <h3> Configure visible login method icons</h3>
        Make sure all of these are allowed in eID Easy admin site at <a href="https://id.eideasy.com">https://id.eideasy.com</a>

        <table>
            <?php
            foreach (IdCardLogin::getSupportedMethods() as $method => $params) {
                ?>
                <tr>
                    <td>
                        <input type="checkbox" name="<?php echo $method ?>" class="column-cb"
                               value="1" <?php checked('1', get_option($method)); ?> />
                        <label for="<?php echo $method ?> "><?php echo $params['name'] ?></label>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="eideasy_debug_mode" class="column-cb"
                           value="yes" <?php echo get_option("eideasy_debug_mode") ? "checked" : "" ?>>
                    <label for="eideasy_debug_mode">Enable debug mode. Sends login progress to server if
                        there are login issues.</label>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function registerSettings()
    {
        register_setting('eideasy_woo', 'eideasy_woo_age_check_enabled');
        register_setting('eideasy_woo', 'eideasy_woo_min_age');
        register_setting('eideasy_woo', 'eideasy_woo_age_restricted_categories');

        register_setting('eideasy', 'eideasy_registration_disabled');
        register_setting('eideasy', 'eideasy_client_id');
        register_setting('eideasy', 'eideasy_secret');
        register_setting('eideasy', 'eideasy_redirect_uri');
        register_setting('eideasy', 'eideasy_only_identify');
        register_setting('eideasy', 'eideasy_debug_mode');
        register_setting('eideasy', 'eideasy_google_enabled');
        register_setting('eideasy', 'eideasy_facebook_enabled');
        register_setting('eideasy', 'eideasy_smartid_enabled');
        register_setting('eideasy', 'eideasy_ee_mobileid_enabled');
        register_setting('eideasy', 'eideasy_lt_mobileid_enabled');
        register_setting('eideasy', 'eideasy_eparaksts_mobile_enabled');
        register_setting('eideasy', 'eideasy_ee_idcard_enabled');
        register_setting('eideasy', 'eideasy_lv_idcard_enabled');
        register_setting('eideasy', 'eideasy_lt_idcard_enabled');
        register_setting('eideasy', 'eideasy_pt_idcard_enabled');
        register_setting('eideasy', 'eideasy_be_idcard_enabled');
        register_setting('eideasy', 'eideasy_zealid_enabled');
    }
}
