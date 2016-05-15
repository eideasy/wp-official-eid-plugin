<?php

class IdcardAdmin {

    static function id_settings_page() {
        add_menu_page('ID Signing plugin settins', 'ID signing settings', 'manage_options', 'id-signing-settings', 'IdcardAdmin::create_id_settings_page');
    }

    //Tekitab html millega konfida ID allkirjastamise pluginat
    static function create_id_settings_page() {
        echo "<h2> ID signing plugins settings </h2>";
        //must check that the user has the required capability 
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // variables for the field and option names 
        $opt_name = 'mt_favorite_color';
        $hidden_field_name = 'status';
        $data_field_name = 'mt_favorite_color';

        // Read in existing option value from database
        $opt_val = get_option($opt_name);

        // See if the user has posted us some information
        // If they did, this hidden field will be set to 'Y'
        if (isset($_POST["status"]) && $_POST["status"] == 'sign_start') {
            // Read their posted value
            $opt_val = $_POST[$data_field_name];

            //send api call to proxy to register this WP instance
            // Save the posted value in the database
            update_option($opt_name, $opt_val);

            // Put a "settings saved" message on the screen
            ?>
            <div class="updated"><p><strong><?php _e('settings saved.', 'id-sign'); ?></strong></p></div>
            <?php
        }
        ?>

        <div class="wrap">

            <form name="form1" method="post" action="">
                <input type="hidden" name="status" value="sign_start">

                <div>
                    This is the contract you are about to sign. Better read it carefully before signing. 
                </div>


                <?php if ($_SESSION['admin_id_verified'] != true) { ?>
                    <p>You are not yet authenticated digitally, please authenticate yourself</p>
                    <div id="idid"></div>
                    <script src="https://idid.ee/js/button.js"></script>
                    <script>
                        new Button({img: 5, width: 240, clientId: '022f8d04772c174a926572a125871156bb5ec12e361268407dd63530ce2523e5'}, function (token) {
                            console.log(token);
                            window.location = '<?php echo plugins_url() ?>/id-card-login/adminlogin.php?token=' + token + '&redirect_to=' + window.location.href;
                        });
                    </script>

                <?php
                } else {
                    echo "<p>You are authenticated as " . $_SESSION['admin_firstname'] . " " . $_SESSION['admin_lastname']."</p>";
                }
                ?>

                <?php if ($_SESSION['admin_auth_failed'] == true) { ?>
                    <p>Authentication failed. Please try again or contact Heikki Visnapuu</p>
        <?php } ?>
                <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Sign') ?>" />


            </form>
        </div>

        <?php
    }

    static function admin_login() {
        echo "Is php curl module installed?";
        $token = $_GET['token'];

        //tõmbame sisselogitud inimese andmed
        $result = json_decode(IdcardAuthenticate::getUserFromIdid($token));
        $firstName = $result->firstname;
        $lastName = $result->lastname;
        $identityCode = $result->id;
        $userName = "EE" . $identityCode;

        //Otsime üles sisselogitud inimese või tekitame, kui teda vare polnud
        $user = IdcardAuthenticate::getUser($identityCode);
        if (($user == NULL) and ( NULL == username_exists($userName))) {
            $user_id = IdcardAuthenticate::createUser($userName, $firstName, $lastName, $identityCode);
        } else {
            $user_id = $user->userid;
        }

        //logime inimese ka wordpressi sisse
        IdcardAuthenticate::setSession($identityCode, $firstName, $lastName);
        wp_set_auth_cookie($user_id);
        if (array_key_exists('redirect_to', $_GET)) {
            header('Location: ' . $_GET['redirect_to']);
        } else {
            header('Location: ' . home_url());
        }
    }

}
