<?php
require_once( plugin_dir_path(__FILE__) . 'securelogin.php');
if (!class_exists("IdcardAdmin")) {

    class IdcardAdmin {

        static function id_settings_page() {
            add_menu_page('Smart-ID', 'Smart-ID', 'manage_options', 'smart-id-settings', 'IdcardAdmin::create_id_settings_page');
        }

        static function create_id_settings_page() {
            echo "<h2> Smart-ID </h2>";
//           update_option("smartid_client_id", null);
            if (!function_exists('curl_version')) {
                echo "cURL PHP module not installed or disabled, please enable it before starting to use Smart-ID secure logins";
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            if (isset($_GET["error"])) {
                ?>
                <div class="notice notice-error"><p><strong>Failed to register API. Error=<?php echo $_GET["error"] ?></strong></p></div>       
                <?php
            }

            //get domain data from the server
            if (isset($_GET["data_key"])) {
                $params = [
                    "data_key" => $_GET["data_key"]
                ];

                $registerResult = IdCardLogin::curlCall("admin/api_client_info", $params);
                if ($registerResult["status"] == "error") {
                    ?>
                    <div class="notice notice-error"><p><strong>Failed to activate registration <?php echo $registerResult["message"] ?></strong></p></div>                                    
                    <?php
                    return;
                }
                $clientId = $registerResult["client_id"];
                $secret = $registerResult["secret"];
                $redirect_uri = $registerResult["redirect_uri"];

                // Save the posted value in the database
                update_option("smartid_client_id", $clientId);
                update_option("smartid_secret", $secret);
                update_option("smartid_redirect_uri", $redirect_uri);

                // Show confirmation
                ?>
                <div class="updated"><p><strong>Client registration done. client_id=<?php echo $clientId ?></strong></p></div>                
                <?php
            }


            //Site has not activated Smart-ID yet
            if (get_option("smartid_client_id") == null) {
                ?>        
                <div class="wrap">
                    <?php include("api_register.php"); ?>
                </div>

                <?php
            } else {
                echo "This site Smart-ID is active. client_id=" . get_option("smartid_client_id");
                ?>
                <br>                
                <br> 
                Smart-ID has shortcode that wordpress will replace on runtime:
                <ol>
                    <li>
                        <b>[smart_id]</b> - Creates login ID-card and Mobile-ID (Premium) login buttons
                    </li>
                    <li>
                        <b>[contract id="123"]</b> - Creates iframe where contract can be signed. Contract tempalte and ID can be acquired from 
                        <a href="https://api.smartid.ee/admin/contract_template" target="_blank">https://api.smartid.ee</a>
                    </li>
                </ol>

                <br>
                All questions and support at <a href="mailto:help@smartid.ee">help@smartid.ee</a>
                <?php
            }
        }

    }

}
    