<?php

/**
 * Plugin Name: ID-card signing
 * Plugin URI: http://marguspala.com/
 * Description: This plugin allows you to login to wordpress with Estonian ID-card
 * Version: 0.1
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

if (!class_exists("IdCardLogin")) {

    class IdCardLogin {

        //tekitame login nupu
        static function show_id_login() {
            $pUrl = plugins_url();
            $redirect_url = strlen(array_key_exists('redirect_to', $_GET)) > 0 ? "&redirect_to=" . urlencode($_GET['redirect_to']) : "";
            echo '<div id="idid"></div>'
            . '<script src="https://idid.ee/js/button.js"></script>'
            . '<script>'
            . "new Button({ img: 5, width: 240, clientId: '022f8d04772c174a926572a125871156bb5ec12e361268407dd63530ce2523e5' }, function(token) { "
            . "console.log(token); "
            . 'window.location="' . $pUrl . '/id-card-login/securelogin.php?token="+token+"' . $redirect_url . '"'
            . "});</script>";
        }

        //konfime andmebaasi
        static function idcard_install() {
            global $wpdb;

            $table_name = $wpdb->prefix . "idcard_users";

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,                
                firstname tinytext NOT NULL,
                lastname tinytext NOT NULL,
                identitycode VARCHAR(11) NOT NULL,
                userid bigint(20) unsigned NOT NULL,
                created_at datetime NOT NULL,
                UNIQUE KEY id (id),
                UNIQUE KEY identitycode (identitycode)
                  );";

            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }

        function startSession() {
            if (!session_id()) {
                session_start();
            }
        }

        function endSession() {
            session_destroy();
        }

    }

    //registreerime wordpressiga integratsioonipunktid
    add_action('login_form', 'IdCardLogin::show_id_login');
    add_action('init', 'IdCardLogin::startSession', 1);
    add_action('wp_logout', 'IdCardLogin::endSession');
    add_action('wp_login', 'IdCardLogin::endSession');


    register_activation_hook(__FILE__, 'IdCardLogin::idcard_install');

    // Hook for adding admin menus
    add_action('admin_menu', 'IdcardAdmin::id_settings_page');

    add_shortcode('id_login', 'IdCardLogin::show_id_login');
}
