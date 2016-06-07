<?php
if (!class_exists("IdcardAdmin")) {

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
            if (isset($_POST["status"]) && $_POST["status"] == 'reset_site_secret') {
                update_option("site_secret", null);
            }

            $siteSecret = get_option("site_secret");
//        update_option('site_secret', null);
            // See if the user has posted us some information
            // If they did, this hidden field will be set to 'Y'

            if (isset($_POST["status"]) && $_POST["status"] == 'activation_start') {
                // Read their posted value
                $opt_val = $_POST[$data_field_name];

                //send api call to proxy to register this WP instance
                $registerResponse = IdcardAdmin::registerSite();
                //show results based on the registration response
                if (array_key_exists("error", $registerResponse)) {
                    ?>
                    <p>Site registration failed because of: <?php echo $registerResponse['error'] ?></p>
                    <?php
                } else {
                    // Save the posted value in the database
                    update_option("site_secret", $registerResponse['site_secret']);
                    update_option("site_owner_id", $registerResponse['site_owner_id']);
                    $siteSecret = $registerResponse['site_secret'];

                    // Show confirmation
                    ?>
                    <div class="updated"><p><strong><?php _e('Site registered to ' . get_option("site_owner_id"), 'id-sign'); ?></strong></p></div>                
                    <?php
                }
            }
            ?>

            <?php
            if (strlen($siteSecret) == 0) {
                ?>        
                <div class="wrap">

                    <form name="form1" method="post" action="">
                        <input type="hidden" name="status" value="activation_start">

                        <div>
                            <?php include("terms.html"); ?>
                        </div>


                        <?php if ($_SESSION['admin_id_verified'] != true) { ?>
                            <p>You are not yet authenticated digitally, please authenticate yourself</p>
                            <div id="idid"></div>
                            <script src="https://idid.ee/js/button.js"></script>
                            <script>
                                new Button({img: 5, width: 240, clientId: '022f8d04772c174a926572a125871156bb5ec12e361268407dd63530ce2523e5'}, function (token) {
                    <?php
                    $baseName = plugin_basename(__FILE__);
                    $pluginFolder = explode(DIRECTORY_SEPARATOR, $baseName)[0];
                    ?>
                                    if (JSON.stringify(token).length == 34) {
                                        window.location = '<?php echo plugins_url() . "/" . $pluginFolder ?>/adminlogin.php?token=' + token + '&redir_to=' + window.location.href;
                                    }
                                });
                            </script>

                            <?php
                        } else {
                            echo "<p>You are authenticated as " . $_SESSION['admin_firstname'] . " " . $_SESSION['admin_lastname'] . "</p>";
                            ?>
                            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Activate this site') ?>" />
                            <?php
                        }
                        ?>

                        <?php if ($_SESSION['admin_auth_failed'] == true) { ?>
                            <p>Authentication failed. Please try again or contact Heikki Visnapuu and tell the error time <?php echo date(DATE_RFC2822); ?></p>
                        <?php } ?>

                    </form>
                </div>

                <?php
            } else {
                echo "This site is registered to " . get_option("site_owner_id") . " and site secret is " . get_option("site_secret");
                ?>
                <form name = "form1" method = "post" action = "">
                    <input type = "hidden" name = "status" value = "reset_site_secret">
                    <input type = "submit" name = "Submit" class = "button-primary" value = "<?php esc_attr_e('Reset secret') ?>" />
                </form>
                <br>
                <?php
                echo "Enter your contract template below. You can use tags for customers to fill in values in the format {{tag=Tag visible name}}. Tag must contain lowercase latin letters and Tag visible name can be anything<br>";
                echo "For example {{firstname=Your first name}} and {{phoneno=Phone number}}";
                include('adminform.php');
            }
        }

        static function registerSite() {

            $ch = curl_init();
            $url = "https://api.idapi.ee/api/v1/registerapp?siteurl=" . urlencode(explode("://",get_site_url())[1]) . "&idcode=" . $_SESSION['identitycode'] . "&auth_key=" . $_SESSION['auth_key'];
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $curlResult = curl_exec($ch);
            $result = json_decode($curlResult, true);
            curl_close($ch);
            return $result;
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

}