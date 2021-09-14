<?php

require_once(plugin_dir_path(__FILE__) . 'securelogin.php');
if ( ! class_exists("IdcardAdmin")) {

    class IdcardAdmin
    {

        static function id_settings_page()
        {
            add_menu_page('eID Easy', 'eID Easy', 'manage_options', 'eid-easy-settings',
                'IdcardAdmin::create_id_settings_page');
        }

        static function create_id_settings_page()
        {
            echo "<h1> eID Easy </h1>";

            if ( ! current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            ?>

            <?php

            if (get_option("smartid_client_id")) {
                if (array_key_exists("smartid_change_settings", $_POST) && $_POST["smartid_change_settings"] == "yes") {
                    if (array_key_exists("pt-id-card_enabled", $_POST) && $_POST["pt-id-card_enabled"] == "yes") {
                        update_option("smartid_pt-id-card_enabled", true);
                    } else {
                        update_option("smartid_pt-id-card_enabled", false);
                    }
                    if (array_key_exists("be-id-card_enabled", $_POST) && $_POST["be-id-card_enabled"] == "yes") {
                        update_option("smartid_be-id-card_enabled", true);
                    } else {
                        update_option("smartid_be-id-card_enabled", false);
                    }

                    if (array_key_exists("lt-mobile-id_enabled", $_POST) && $_POST["lt-mobile-id_enabled"] == "yes") {
                        update_option("smartid_lt-mobile-id_enabled", true);
                    } else {
                        update_option("smartid_lt-mobile-id_enabled", false);
                    }
                    if (array_key_exists("lt-id-card_enabled", $_POST) && $_POST["lt-id-card_enabled"] == "yes") {
                        update_option("smartid_lt-id-card_enabled", true);
                    } else {
                        update_option("smartid_lt-id-card_enabled", false);
                    }
                    if (array_key_exists("lv-id-card_enabled", $_POST) && $_POST["lv-id-card_enabled"] == "yes") {
                        update_option("lveid_enabled", true);
                    } else {
                        update_option("lveid_enabled", false);
                    }
                    if (array_key_exists("eparaksts-mobile_enabled", $_POST) && $_POST["eparaksts-mobile_enabled"] == "yes") {
                        update_option("eideasy-eparaksts-mobile_enabled", true);
                    } else {
                        update_option("eideasy-eparaksts-mobile_enabled", false);
                    }
                    if (array_key_exists("ee-id-card_enabled", $_POST) && $_POST["ee-id-card_enabled"] == "yes") {
                        update_option("smartid_idcard_enabled", true);
                    } else {
                        update_option("smartid_idcard_enabled", false);
                    }

                    if (array_key_exists("ee-mobile-id_enabled", $_POST) && $_POST["ee-mobile-id_enabled"] == "yes") {
                        update_option("smartid_mobileid_enabled", true);
                    } else {
                        update_option("smartid_mobileid_enabled", false);
                    }

                    if (array_key_exists("smart-id_enabled", $_POST) && $_POST["smart-id_enabled"] == "yes") {
                        update_option("smartid_smartid_enabled", true);
                    } else {
                        update_option("smartid_smartid_enabled", false);
                    }

                    if (array_key_exists("smartid_debug_mode", $_POST) && $_POST["smartid_debug_mode"] == "yes") {
                        update_option("smartid_debug_mode", true);
                    } else {
                        update_option("smartid_debug_mode", false);
                    }

                    if (array_key_exists("smartid_registration_disabled",
                            $_POST) && $_POST["smartid_registration_disabled"] == "yes") {
                        update_option("smartid_registration_disabled", true);
                    } else {
                        update_option("smartid_registration_disabled", false);
                    }

                    if (array_key_exists("eideasy_only_identify",
                            $_POST) && $_POST["eideasy_only_identify"] == "yes") {
                        update_option("eideasy_only_identify", true);
                    } else {
                        update_option("eideasy_only_identify", false);
                    }
                }
                ?>
                <h3> This site eID Easy is now active!</h3>
                eID Easy shortcode:
                <ol>
                    <li>
                        <b>[eid_easy]</b> - Creates configured login buttons.
                    </li>
                </ol>

                <br>
                All questions and support at <a href="mailto:info@eideasy.com">info@eideasy.com</a>


                <form method="post"
                      action="<?php echo (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
                    <input type="hidden" name="smartid_change_settings" value="yes">

                    <h3>Registration disabled</h3>

                    <input type="checkbox" name="smartid_registration_disabled" class="column-cb"
                           value="yes" <?php echo get_option("smartid_registration_disabled") ? "checked" : "" ?> >
                    <label for="lt-mobile-id_enabled">Disable automatic registration. In this case admin must add idcode
                        to each user manually to allow ID card login.</label>

                    <h3>Only identify users (no user login)</h3>

                    <input type="checkbox" name="eideasy_only_identify" class="column-cb"
                           value="yes" <?php echo get_option("eideasy_only_identify") ? "checked" : "" ?> >
                    <label for="lt-mobile-id_enabled">No accounts are created nor any users are logged in.</label>
                    <small>You can get users details using action "eideasy_only_identify"</small>

                    <h3> Configure visible login method icons</h3>
                    Make sure all of these are allowed in eID Easy admin site at <a href="https://id.eideasy.com">https://id.eideasy.com</a>

                    <table>
                        <tr>
                            <td>
                                <input type="checkbox" name="lt-mobile-id_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_lt-mobile-id_enabled") ? "checked" : "" ?> >
                                <label for="lt-mobile-id_enabled">Lithuanian mobile ID</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="lt-id-card_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_lt-id-card_enabled") ? "checked" : "" ?> >
                                <label for="lt-id-card_enabled">Lithuanian ID-card</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="lv-id-card_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("lveid_enabled") ? "checked" : "" ?> >
                                <label for="lv-id-card_enabled">Latvian ID-card</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="eparaksts-mobile_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("eideasy-eparaksts-mobile_enabled") ? "checked" : "" ?> >
                                <label for="eparaksts-mobile_enabled">Latvian eParaksts Mobile</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="pt-id-card_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_pt-id-card_enabled") ? "checked" : "" ?> >
                                <label for="pt-id_card_enabled">Portugal ID-card</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="be-id-card_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_be-id-card_enabled") ? "checked" : "" ?> >
                                <label for="be-id-card_enabled">Belgium ID-card</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="ee-id-card_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_idcard_enabled") ? "checked" : "" ?> >
                                <label for="ee-id-card_enabled">Estonian ID-card</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="ee-mobile-id_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_mobileid_enabled") ? "checked" : "" ?>>
                                <label for="ee-mobile-id_enabled">Estonian Mobile-ID</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="smart-id_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_smartid_enabled") ? "checked" : "" ?>>
                                <label for="smart-id_enabled">Smart-ID</label>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="smartid_debug_mode" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_debug_mode") ? "checked" : "" ?>>
                                <label for="smartid_debug_mode">Enable debug mode. Sends login progress to server if
                                    there are login issues.</label>
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
