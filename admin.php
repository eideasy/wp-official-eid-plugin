<?php
if (!class_exists("IdcardAdmin")) {

    class IdcardAdmin {

        static function id_settings_page() {
            add_menu_page('ID-API settins', 'ID-API settings', 'manage_options', 'id-signing-settings', 'IdcardAdmin::create_id_settings_page');
        }

        //Tekitab html millega konfida ID allkirjastamise pluginat
        static function create_id_settings_page() {
            echo "<h2> ID-API settings </h2>";
            //must check that the user has the required capability 
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

//            if (isset($_POST["status"]) && $_POST["status"] == 'reset_site_secret') {
//                update_option("site_secret", null);
//                update_option("site_client_id", null);
//            }


            //Check what is registration status for this domain
            if (isset($_POST["status"]) && $_POST["status"] == 'register_api') {
                $params = [
                    "domain" => get_site_url()
                ];
                $registerResult = IdCardLogin::curlCall("api/v1/register_api", $params);

                if ($registerResult["status"] == "error") {
                    ?>
                    <div class="updated"><p><strong>Failed to activate registration <?php echo $registerResult["message"] ?></strong></p></div>                
                    <div class="updated"><p><strong>Manual activation available at <a href="https://api.smartid.dev/register_api?auth_key=<?php echo $_SESSION["auth_key"]; ?>">here</a></strong></p></div>    
                    <?php
                    return;
                }
                $verification = $registerResult["verification"];
                $client_id = $registerResult["client_id"];
                if (strlen($verification) === 32 && $_SESSION['auth_key'] != NULL) {
                    $path = get_home_path();
                    $file = fopen("$path$verification.html", "w");
                    fwrite($file, htmlentities($verification));
                    fclose($file);
                }

                $verifyParams = [
                    "client_id" => $client_id
                ];

                $verifyResult = IdCardLogin::curlCall("api/v1/verify_domain", $verifyParams);

                if ($verifyResult["status"] == "error") {
                    ?>
                    <div class="updated"><p><strong>Failed to verify domain. <?php echo $verifyResult["messsage"] ?></strong></p></div>                
                    return;
                    <?php
                }

                // Save the posted value in the database
                update_option("site_secret", $verifyResult['secret']);
                update_option("site_client_id", $verifyResult['client_id']);


                // Show confirmation
                ?>
                <div class="updated"><p><strong>Client registration done. client_id=<?php echo $verifyResult['client_id'] ?></strong></p></div>                
                <?php
            }

            //Check what is registration status for this domain
            if (isset($_POST["status"]) && $_POST["status"] == 'admin_login_done') {
                $_SESSION['auth_key'] = $_POST['auth_key'];
            }


            //Site has not activated ID-API yet
            if (get_option("site_client_id") == null) {
                ?>        
                <div class="wrap">
                    <?php include("api_register.php"); ?>
                </div>

                <?php
            } else {
                echo "This site ID-API is active. client_id=" . get_option("site_client_id");
                ?>
<!--                <form name = "form1" method = "post" action = "">
                    <input type = "hidden" name = "status" value = "reset_site_secret">
                    <input type = "submit" name = "Submit" class = "button-primary" value = "Reset secret" />
                </form>-->
                <br>                
                <br> 
                ID-API has shortcode that wordpress will replace on runtime:
                <ol>
                    <li>
                        <b>[id_login]</b> - creates login ID-card and Mobile-ID login buttons
                    </li>
<!--                    <li>
                        <b>[show_contract_form]</b> - shows contract template where user can enter fill blanks and sign the contract.
                    </li>-->
                </ol>

                <?php
//                echo "<b>Enter your contract template below</b>. <br>You can use tags for customers to fill in values in the format {{tag=Tag visible name}}. Tag must contain lowercase latin letters and Tag visible name can be anything<br>";
//                echo "For example {{firstname=Your first name}} and {{phoneno=Phone number}}";
//                include('adminform.php');
                ?>
                <br>
                All questions and support at <a href="mailto:help@idapi.ee">help@idapi.ee</a>
                <?php
            }
        }

    }

}
    