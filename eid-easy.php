<?php
/**
 * Plugin Name: eID Easy
 * Plugin URI: https://eideasy.com/
 * Description: Allow your visitors to login to Wordpress ID-card, Mobile-ID, Smart-ID mobile app, eParaksts, ZealID and other methods.
 * Version: 5.0
 * Author: EID Easy OÜ
 * Text Domain: eid-easy
 * Domain path /languages
 * License: GPLv2 or later
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

require_once 'EidEasy/IdcardAdmin.php';
require_once 'EidEasy/IdCardLogin.php';
require_once 'EidEasy/WooIntegration.php';

add_action('delete_user', [\EidEasy\IdCardLogin::class, 'deleteUserCleanUp']);

if (!get_option('eideasy_only_identify')) {
    add_action('login_footer', [\EidEasy\IdCardLogin::class, 'echo_id_login']);
    add_action('login_enqueue_scripts', [\EidEasy\IdCardLogin::class, 'enqueueJquery']);
}

add_action('init', [\EidEasy\IdCardLogin::class, 'wpInitProcess']);

register_activation_hook(__FILE__, [\EidEasy\IdCardLogin::class, 'idcard_install']);

add_action('admin_notices', [\EidEasy\IdCardLogin::class, 'admin_notice']);

add_action('show_user_profile', [\EidEasy\IdCardLogin::class, 'custom_user_profile_fields']);
add_action('edit_user_profile', [\EidEasy\IdCardLogin::class, 'custom_user_profile_fields']);
add_action('profile_update', [\EidEasy\IdCardLogin::class, 'save_custom_user_profile_fields']);

add_shortcode('eid_easy', [\EidEasy\IdCardLogin::class, 'return_id_login']);
add_shortcode('contract', [\EidEasy\IdCardLogin::class, 'display_contract_to_sign']);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), [\EidEasy\IdCardLogin::class, 'get_settings_url']);

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
function eideasy_main_plugin_init()
{
    if (is_plugin_active('woocommerce/woocommerce.php') && get_option('eideasy_woo_age_check_enabled')) {
        add_action('woocommerce_before_checkout_form', [\EidEasy\WooIntegration::class, 'identifyUserIfNeeded']);
        add_action('woocommerce_before_checkout_process', [\EidEasy\WooIntegration::class, 'validateAge']);
        add_action('woocommerce_checkout_order_processed', [\EidEasy\WooIntegration::class, 'addOrderNote']);
        add_action('woocommerce_checkout_update_order_review', [\EidEasy\WooIntegration::class, 'updateOrderReview']);
        add_action('eideasy_user_identified', [\EidEasy\WooIntegration::class, 'saveUserAge']);
    }

    if (is_admin()) {
        add_action('admin_init', [\EidEasy\IdcardAdmin::class, 'registerSettings']);
        add_action('admin_menu', [\EidEasy\IdcardAdmin::class, 'idSettingsPage']);
    }
}

add_action('plugins_loaded', 'eideasy_main_plugin_init');
