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
            if (isset($_POST["status"]) && $_POST["status"] == 'reset_site_secret') {
                update_option("site_secret", null);
                update_option("site_client_id", null);
            }

            //resposne from site activation is here
            if (isset($_POST["status"]) && $_POST["status"] == 'activation_done') {

                // Save the posted value in the database
                update_option("site_secret", $_POST['form_secret']);
                update_option("site_client_id", $_POST['form_client_id']);
                $_SESSION['auth_key'] = $_POST['form_auth_key'];

                // Show confirmation
                ?>
                <div class="updated"><p><strong>Client registration done. client_id=<?php echo $_POST['form_client_id'] ?>, secret=<?php echo $_POST['form_secret'] ?></strong></p></div>                
                <?php
            }

            //Site has not activated ID-API yet
            if (get_option("site_client_id") == null) {
                ?>        
                <div class="wrap">
                    <?php include("api_register.php"); ?>
                </div>

                <?php
            } else {
                echo "This site ID-API is active. client_id=" . get_option("site_client_id") . ", secret=" . get_option("site_secret");
                ?>
                <form name = "form1" method = "post" action = "">
                    <input type = "hidden" name = "status" value = "reset_site_secret">
                    <input type = "submit" name = "Submit" class = "button-primary" value = "<?php esc_attr_e('Reset secret') ?>" />
                </form>
                <br>                
                ID-API has 2 different shortcodes that wordpress will replace on runtime:
                <ol>
                    <li>
                        <b>[id_login]</b> - creates login ID-card and Mobile-ID login buttons
                    </li>
                    <li>
                        <b>[show_contract_form]</b> - shows contract template where user can enter fill blanks and sign the contract.
                    </li>
                </ol>
                
                <?php
                echo "<b>Enter your contract template below</b>. <br>You can use tags for customers to fill in values in the format {{tag=Tag visible name}}. Tag must contain lowercase latin letters and Tag visible name can be anything<br>";
                echo "For example {{firstname=Your first name}} and {{phoneno=Phone number}}";
                include('adminform.php');
                ?>
                <br>
                All questions and support at <a href="mailto:help@idapi.ee">help@idapi.ee</a>
                <?php
            }
        }

    }

}