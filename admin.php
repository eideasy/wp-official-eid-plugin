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
            if ( ! function_exists('curl_version')) {
                echo "cURL PHP module not installed or disabled, please enable it before starting to use eID Easy secure logins";

                return;
            }

            if ( ! current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            if (isset($_GET["error"])) {
                ?>
                <div class="notice notice-error"><p><strong>Failed to register API.
                            Error=<?php echo $_GET["error"] ?></strong></p></div>
                <?php
            }

            //get domain data from the server
            if (isset($_GET["data_key"]) && get_option("smartid_client_id") == false) {
                $params = [
                    "data_key" => $_GET["data_key"]
                ];

                $registerResult = IdCardLogin::curlCall("admin/api_client_info", $params);
                if ($registerResult["status"] == "error") {
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
                update_option("smartid_client_id", $clientId);
                update_option("smartid_secret", $secret);
                update_option("smartid_redirect_uri", $redirect_uri);

                // Show confirmation
                ?>
                <div class="updated"><p><strong>Client registration done!</strong></p></div>
            <?php }
            ?>

            <?php
            //Site has not activated eID Easy yet
            if (get_option("smartid_client_id") == null) {
                ?>
                <div class="wrap">
                    <?php include("api_register.php"); ?>
                </div>

                <?php
            } else {
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

                    if (array_key_exists("facebook_enabled", $_POST) && $_POST["facebook_enabled"] == "yes") {
                        update_option("smartid_facebook_enabled", true);
                    } else {
                        update_option("smartid_facebook_enabled", false);
                    }

                    if (array_key_exists("google_enabled", $_POST) && $_POST["google_enabled"] == "yes") {
                        update_option("smartid_google_enabled", true);
                    } else {
                        update_option("smartid_google_enabled", false);
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

                    if (array_key_exists("agrello_enabled", $_POST) && $_POST["agrello_enabled"] == "yes") {
                        update_option("smartid_agrello_enabled", true);
                    } else {
                        update_option("smartid_agrello_enabled", false);
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

                    <h3> Configure visible login method icons</h3>
                    Make sure all of these are allowed in Smart ID admin site at <a href="https://id.eideasy.com">https://id.eideasy.com</a>

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
                            <td>
                                <input type="checkbox" name="facebook_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_facebook_enabled") ? "checked" : "" ?>>
                                <label for="facebook_enabled">Facebook</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="google_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_google_enabled") ? "checked" : "" ?>>
                                <label for="google_enabled">Google</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="agrello_enabled" class="column-cb"
                                       value="yes" <?php echo get_option("smartid_agrello_enabled") ? "checked" : "" ?>>
                                <label for="agrello_enabled">Agrello .ID</label>
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
