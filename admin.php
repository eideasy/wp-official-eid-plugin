<?php

require_once(plugin_dir_path(__FILE__) . 'securelogin.php');

if (!class_exists("IdcardAdmin")) {

    class IdcardAdmin
    {

        static function id_settings_page()
        {
            add_menu_page('eID Easy', 'eID Easy', 'manage_options', 'eid-easy-settings',
                'IdcardAdmin::create_id_settings_page');
        }

        static function create_id_settings_page()
        {
            echo wp_kses_post("<h1> eID Easy </h1>");

            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wp-official-eid-easy'));
            }

            if (get_option("smartid_client_id")) {
                if (
                    array_key_exists("smartid_change_settings", $_POST) &&
                    $_POST["smartid_change_settings"] == "yes" &&
                    isset($_POST['_wpnonce']) &&
                    wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'smartid_change_settings_action')
                ) {
                    foreach (eideasyOptions()['methods'] as $method) {
                        if (array_key_exists($method['inputName'], $_POST) && $_POST[$method['inputName']] == "yes") {
                            update_option($method['optionName'], true);
                        } else {
                            update_option($method['optionName'], false);
                        }
                    }
                    if (array_key_exists("smartid_debug_mode", $_POST) && $_POST["smartid_debug_mode"] == "yes") {
                        update_option("smartid_debug_mode", true);
                    } else {
                        update_option("smartid_debug_mode", false);
                    }
                    if (array_key_exists("smartid_registration_disabled", $_POST) && $_POST["smartid_registration_disabled"] == "yes") {
                        update_option("smartid_registration_disabled", true);
                    } else {
                        update_option("smartid_registration_disabled", false);
                    }
                    if (array_key_exists("eideasy_only_identify", $_POST) && $_POST["eideasy_only_identify"] == "yes") {
                        update_option("eideasy_only_identify", true);
                    } else {
                        update_option("eideasy_only_identify", false);
                    }
                    if (array_key_exists("eideasy_test_mode", $_POST) && $_POST["eideasy_test_mode"] == "yes") {
                        update_option("eideasy_test_mode", true);
                    } else {
                        update_option("eideasy_test_mode", false);
                    }
                }
                ?>
                <h3> This site eID Easy is now active!</h3>
                eID Easy shortcodes:
                <ol>
                    <li>
                        <b>[eid_easy]</b> - Creates configured login buttons.
                    </li>
                    <li>
                        <b>[smart_id]</b> - Alternative shortcode for login buttons (legacy).
                    </li>
                    <li>
                        <b>[contract id="123ABC"]</b> - Creates document signing page. Replace 123ABC with actual contract ID from <?= eideasyGetBaseUrl() ?>
                    </li>
                </ol>
                <br>
                All questions and support at <a href="mailto:info@eideasy.com">info@eideasy.com</a>
                <form method="post"
                      action="<?php echo esc_url((isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . (isset($_SERVER['HTTP_HOST']) ? esc_attr(sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']))) : '') . (isset($_SERVER['REQUEST_URI']) ? esc_attr(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))) : '')); ?>">
                    <?php wp_nonce_field('smartid_change_settings_action'); ?>
                    <input type="hidden" name="smartid_change_settings" value="yes">

                    <h3>Registration disabled</h3>
                    <?php
                    echo eideasyTemplate(eideasyTemplateFiles()['checkbox-template'], [
                        'label' => 'Disable automatic registration. In this case admin must add idcode to each user manually to allow ID card login.',
                        'name' => 'smartid_registration_disabled',
                        'id' => 'smartid_registration_disabled',
                        'checked' => get_option("smartid_registration_disabled"),
                    ]);
                    ?>

                    <h3>Only identify users (no user login)</h3>
                    <?php
                    echo eideasyTemplate(eideasyTemplateFiles()['checkbox-template'], [
                        'label' => 'No accounts are created nor any users are logged in.',
                        'name' => 'eideasy_only_identify',
                        'id' => 'eideasy_only_identify',
                        'checked' => get_option("eideasy_only_identify"),
                    ]);
                    ?>
                    <small>You can get users details using action "eideasy_only_identify"</small>

                    <h3> Configure visible login method icons</h3>
                    Make sure all of selected methods are allowed in eID Easy admin site at <a href="<?= eideasyGetBaseUrl() ?>"><?= eideasyGetBaseUrl() ?></a>

                    <table>
                        <?php foreach (eideasyOptions()['methods'] as $method) : ?>
                            <tr>
                                <td>
                                    <?php
                                    echo eideasyTemplate(eideasyTemplateFiles()['checkbox-template'], [
                                        'label' => $method['label'],
                                        'name' => $method['inputName'],
                                        'id' => $method['label'],
                                        'checked' => get_option($method['optionName']),
                                    ]);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <table>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                echo eideasyTemplate(eideasyTemplateFiles()['checkbox-template'], [
                                    'label' => 'Enable debug mode. Sends login progress to server if there are login issues.',
                                    'name' => 'smartid_debug_mode',
                                    'id' => 'smartid_debug_mode',
                                    'checked' => get_option('smartid_debug_mode'),
                                ]);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                echo eideasyTemplate(eideasyTemplateFiles()['checkbox-template'], [
                                    'label' => 'Enable test mode. All requests will be sent to test.eideasy.com instead of id.eideasy.com',
                                    'name' => 'eideasy_test_mode',
                                    'id' => 'eideasy_test_mode',
                                    'checked' => get_option('eideasy_test_mode'),
                                ]);
                                ?>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(); ?>
                </form>
                <?php
            }
            ?>
            <div class="wrap">
                <?php include("api_manual_setup.php");
                ?>
            </div>
            <?php
        }
    }
}
