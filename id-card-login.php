<?php

/**
 * Plugin Name: ID-card signing
 * Plugin URI: http://marguspala.com/
 * Description: This plugin allows you to login to wordpress with Estonian ID-card
 * Version: 0.7
 * Author: Margus Pala
 * Author URI: http://marguspala.com/
 * License: GPLv2 or later

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
include( plugin_dir_path(__FILE__) . 'admin.php');
include( plugin_dir_path(__FILE__) . 'contract.php');

if (!class_exists("IdCardLogin")) {

    class IdCardLogin {

        //tekitame login nupu
        static function echo_id_login() {
            echo IdCardLogin::getLoginButtonCode();
        }

        static function return_id_login() {
            return IdCardLogin::getLoginButtonCode();
        }

        public function isUserIdLogged() {
            return array_key_exists("auth_key", $_SESSION) && strlen($_SESSION['auth_key']) == 32;
        }

        static function getLoginButtonCode() {
            if (IdCardLogin::isUserIdLogged()) {
                return null;
            }
            $pUrl = plugins_url();
            $baseName = plugin_basename(__FILE__);
            $pluginFolder = explode(DIRECTORY_SEPARATOR, $baseName)[0];
            $redirect_url = strlen(array_key_exists('redirect_to', $_GET)) > 0 ? "&redirect_to=" . urlencode($_GET['redirect_to']) : "";
            return '<span id="idid"></span>'
                    . '<script src="' . $pUrl . '/' . $pluginFolder . '/js/button.js"></script>'
                    . '<script>'
                    . "new Button({ img: 5, width: 240, clientId: '022f8d04772c174a926572a125871156bb5ec12e361268407dd63530ce2523e5' }, function(token) { "
                    . 'window.location="' . $pUrl . '/' . $pluginFolder . '/securelogin.php?token="+token+"' . $redirect_url . '"'
                    . "});</script>";
        }

        //konfime andmebaasi
        static function idcard_install() {
            global $wpdb;

            $table_name = $wpdb->prefix . "idcard_users";

            $sql = "CREATE TABLE if not exists $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,                
                firstname tinytext NOT NULL,
                lastname tinytext NOT NULL,
                identitycode VARCHAR(11) NOT NULL,
                userid bigint(20) unsigned NOT NULL,
                created_at datetime NOT NULL,
                UNIQUE KEY id (id),
                UNIQUE KEY identitycode (identitycode)
                  );";


            $contractHtmlTable = "CREATE TABLE if not exists " . $wpdb->prefix . "contract_html (
                id mediumint(9) NOT NULL AUTO_INCREMENT,                
                html text NOT NULL,                
                created_at datetime NOT NULL,
                active boolean default true,
                UNIQUE KEY id (id)        
                  );";

            $contractFieldsTable = "CREATE TABLE if not exists " . $wpdb->prefix . "contract_fields (
                id mediumint(9) NOT NULL AUTO_INCREMENT,                
                contract_id mediumint(9) NOT NULL,
                tag varchar(255) NOT NULL,        
                name varchar(255) NOT NULL,
                created_at datetime NOT NULL,
                UNIQUE KEY id (id),          
                FOREIGN KEY (contract_id) REFERENCES " . $wpdb->prefix . "contract_html(id)
                  );";

            $responsesTable = "CREATE TABLE if not exists " . $wpdb->prefix . "contract_responses (
                id mediumint(9) NOT NULL AUTO_INCREMENT,                
                contract_id mediumint(9) NOT NULL,
                identitycode VARCHAR(11) NOT NULL,
                signing_time datetime NOT NULL,
                response_id mediumint(9) NOT NULL,
                UNIQUE KEY id (id),    
                UNIQUE KEY response_id (response_id),                   
                FOREIGN KEY (contract_id) REFERENCES " . $wpdb->prefix . "contract_html(id)
                  );";

            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
            dbDelta($contractHtmlTable);
            dbDelta($contractFieldsTable);
            dbDelta($responsesTable);
        }

        function startSession() {
            if (!session_id()) {
                session_start();
            }
        }

        function endSession() {
            session_destroy();
        }

        function disable_password_reset() {
            return false;
        }

    }

    //registreerime wordpressiga integratsioonipunktid
    add_action('login_form', 'IdCardLogin::echo_id_login');
    add_action('init', 'IdCardLogin::startSession', 1);
    add_action('wp_logout', 'IdCardLogin::endSession');
    add_action('wp_login', 'IdCardLogin::endSession');

    //database install
    register_activation_hook(__FILE__, 'IdCardLogin::idcard_install');
    add_action('plugins_loaded', 'IdCardLogin::idcard_install');

    // Hook for adding admin menus
    add_action('admin_menu', 'IdcardAdmin::id_settings_page');

    add_shortcode('id_login', 'IdCardLogin::return_id_login');
    add_shortcode('show_contract_form', 'IdContract::showContract');

    //disable password reset
    add_filter('allow_password_reset', 'IdCardLogin::disable_password_reset');
    add_filter('login_errors', create_function('$a', "return 'Not allowed!';"));
}
