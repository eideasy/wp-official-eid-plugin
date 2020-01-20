<?php

if ( ! class_exists("LoginCommon")) {

    class LoginCommon
    {
        static function login($identityCode, $firstName, $lastName, $email, $country)
        {
            $userName = $country . "_" . $identityCode;

            $user_id = null;
            if (strlen($identityCode) > 5) {
                $user = LoginCommon::getUser($identityCode, $country);
                if ($user == null) {
                    if (get_option('smartid_registration_disabled')) {
                        wp_die("User with ID code $identityCode not found and registration disabled. Contact site admin");
                    } else {
                        $user_id = LoginCommon::createUser($userName, $firstName, $lastName, $email, $identityCode,
                            $country);
                    }
                } else {
                    if (get_option('smartid_debug_mode')) {
                        file_get_contents("https://id.smartid.ee/confirm_progress?message=" . urlencode("WP login user already exists $identityCode"));
                    }
                    $user_id = $user->userid;
                }
            } else {
                if (get_option('smartid_debug_mode')) {
                    file_get_contents("https://id.smartid.ee/confirm_progress?message=" . urlencode("WP login. Idcode not received from the login. Please try again $identityCode, $firstName, $lastName, $email"));
                }
                wp_die("ERROR: Idcode not received from the login. Please try again $identityCode, $firstName, $lastName, $email");
            }
            if (is_multisite()) {
                add_user_to_blog(get_current_blog_id(), $user_id, get_option('default_role'));
            }
            if (get_option('smartid_debug_mode')) {
                file_get_contents("https://id.smartid.ee/confirm_progress?message=" . urlencode("WP login Authenticating WP user $identityCode"));
            }
            wp_set_auth_cookie($user_id);

            usleep(20*1000); //give some time for Javascript to process the login;
            return $user_id;
        }

        private static function createUser($userName, $firstName, $lastName, $email, $identityCode, $country = "EE")
        {
            global $wpdb;
            $user_data = [
                'user_pass'    => wp_generate_password(64, true),
                'user_login'   => $userName,
                'display_name' => "$firstName $lastName",
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'user_email'   => $email,
                'role'         => get_option('default_role') // Use default role or another role, e.g. 'editor'
            ];

            if (username_exists($userName)) {
                if (get_option('smartid_debug_mode')) {
                    file_get_contents("https://id.smartid.ee/confirm_progress?message=" . urlencode("WP login Cannot create user. Username $userName exists"));
                }
                wp_die("Cannot create user. Username $userName exists");
            }

            $user_id = wp_insert_user($user_data);

            if (is_wp_error($user_id)) {
                include 'iframe_break_free_errorhandler.php';
                if (get_option('smartid_debug_mode')) {
                    file_get_contents("https://id.smartid.ee/confirm_progress?message=" . urlencode("WP login cannot create user. Message=" . $user_id->get_error_message() . ". Email: " . $email));
                }

                wp_die("Cannot create user. Message=" . $user_id->get_error_message() . ". Email: " . $email);
            }

            $prefix     = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;
            $table_name = $prefix . "idcard_users";
            $wpdb->insert($table_name, [
                    'firstname'    => $firstName,
                    'lastname'     => $lastName,
                    'identitycode' => $country . "_" . $identityCode,
                    'userid'       => $user_id,
                    'created_at'   => current_time('mysql')
                ]
            );

            if (get_option('smartid_debug_mode')) {
                file_get_contents("https://id.smartid.ee/confirm_progress?message=" . urlencode("WP login new ID user created"));
            }

            return $user_id;
        }

        private static function getUser($identityCode, $country = "EE")
        {
            global $wpdb;

            $prefix = is_multisite() ? $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE) : $wpdb->prefix;

            $user = $wpdb->get_row(
                $wpdb->prepare("select * from $prefix" . "idcard_users WHERE identitycode=%s",
                    $country . "_" . $identityCode)
            );

            //backward compatibility
            if ( ! $user) {
                $user = $wpdb->get_row(
                    $wpdb->prepare("select * from $prefix" . "idcard_users WHERE identitycode=%s",
                        $identityCode)
                );
            }

            return $user;
        }


    }

}

