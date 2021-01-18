<?php

namespace EidEasy;

use DateTime;

class WooIntegration
{
    public static function saveUserAge($userData)
    {
        if (!$userData) {
            return;
        }
        $birthDayInfo = [
            'idcode'   => $userData['idcode'],
            'birthday' => DateTime::createFromFormat('Y-m-d', $userData['birth_date'])->setTime(0, 0, 0)
        ];

        WC()->session->set('eideasy_birthday_data', $birthDayInfo);
        wp_redirect(wc_get_checkout_url());
        die();
    }

    public static function validateAge()
    {
        $birthDayInfo = self::getBirthDay();
        if (!$birthDayInfo) {
            wc_add_notice(__('Age cannot be determined, please contact shop admins', 'eid-easy'), 'error');
            return;
        }

        if (self::isOldEnough($birthDayInfo)) {
            error_log("User is old enough");
            WC()->session->set('eideasy_birthday_data', $birthDayInfo);
            return null; // User is already old enough;
        } else {
            wc_add_notice(__('You are not old enough to buy all products in the cart', 'eid-easy'), 'error');
            return;
        }
    }

    public static function addOrderNote($orderId)
    {
        $sessionBirthday = WC()->session->get('eideasy_birthday_data');
        if ($sessionBirthday) {
            $order = wc_get_order($orderId);
            $order->add_order_note("Age verified using eID Easy. IdCode " . $sessionBirthday['idcode']);
            update_post_meta($orderId, '_eid_easy_verified_idcode', $sessionBirthday['idcode']);
        }
    }

    public static function isOldEnough($birthDayInfo)
    {
        error_log("Birthday info: " . print_r($birthDayInfo, true));
        if ($birthDayInfo && isset($birthDayInfo['birthday']) && get_class($birthDayInfo['birthday']) === \DateTime::class) {
            $age = (int)date_diff(new DateTime(), $birthDayInfo['birthday'])->format('%y');
            if ($age >= get_option('eideasy_woo_min_age')) {
                error_log("User is old enough");
                return true; // User is already old enough;
            } else {
                error_log("User too young");
                return false;
            }
        }
        error_log("User age cannot be determined");
        return false;
    }

    public static function identifyUserIfNeeded()
    {
        $identificationNeeded = self::isCartContainsRestrictedItems();
        if (!$identificationNeeded) {
            return;
        }

        $birthDayInfo = self::getBirthDay();
        if (self::isOldEnough($birthDayInfo)) {
            WC()->session->set('eideasy_birthday_data', $birthDayInfo);
            error_log("User age known and is old enough");
            $message = get_option('eideasy_woo_age_verified_message');
            if (!$message) {
                $message = 'Age verified, your are ready to proceed';
            }
            $message = __($message, 'eid-easy');
            echo "<div class='woocommerce-info'>$message</div>";
        } else {
            $message = get_option('eideasy_woo_verification_requirement_message');
            if (!$message) {
                $message = 'Restricted items in cart, age verification required';
            }
            $message = __($message, 'eid-easy');
            if (get_option("eideasy_woo_more_info_link")) {
                echo "<div class='woocommerce-info'><a href=\"" . get_option("eideasy_woo_more_info_link") . "\" target=\"_blank\"><strong>$message</strong></a></div>";
            } else {
                echo "<div class='woocommerce-info'>$message</div>";
            }
        }

        if ($identificationNeeded) {
            error_log("User identification needed, restricted items in cart");
            if (!get_option('eideasy_woo_default_buttons_disabled')) {
                echo IdCardLogin::return_id_login() . "<br>";
            } else {
                error_log('eID Easy - custom login buttons config');
            }
        }
    }

    public static function getBirthDay()
    {
        // Get user birthday from the cart if exists
        $sessionBirthday = WC()->session->get('eideasy_birthday_data');
        if ($sessionBirthday) {
            return $sessionBirthday;
        }

        // Calculate the birthday from the identity code
        $userData = IdCardLogin::getStoredUserData();
        if (!$userData) {
            return null;
        }

        $countryIdCode = $userData->identitycode;
        if (!$countryIdCode) {
            return null;
        }

        $countryCode = substr($countryIdCode, 0, 2);
        $idCode      = substr($countryIdCode, 3, strlen($countryIdCode));
        if ($countryCode === "EE" || $countryCode === "LT") {
            $datePart = substr($idCode, 1, 6);
            return [
                'idcode'   => $idCode,
                'birthday' => DateTime::createFromFormat('ymd', $datePart)
            ];
        } elseif ($countryCode === "LV") {
            $normIdCode = str_replace("-", "", $idCode);
            $datePart   = substr($idCode, 0, 7);
            if (substr($datePart, 0, 2) === 32) {
                error_log("Birthdate not known");
                return null; // Latvian ID code does not have birthday since 2017
            }

            $idDate      = substr($normIdCode, 0, 4);
            $year        = substr($normIdCode, 4, 2);
            $centuryChar = substr($normIdCode, 6, 1);
            if ($centuryChar === "1") {
                $year = "19$year";
            } else {
                $year = "20$year";
            }
            return [
                'idcode'   => $idCode,
                'birthday' => DateTime::createFromFormat('dmY', $idDate . $year)
            ];
        }
        return null;
    }

    public static function isCartContainsRestrictedItems()
    {
        $restrictedCategories = get_option('eideasy_woo_age_restricted_categories');
        if (!$restrictedCategories) {
            return false;
        }

        foreach (WC()->cart->get_cart_contents() as $cartItem) {
            $categories = $cartItem['data']->get_category_ids();
            if (array_intersect($categories, $restrictedCategories)) {
                return true;
            }
        }
        return false;
    }
}
