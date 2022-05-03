<?php

/**
 * EZPZ_TWEAKS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpz.dev>
 * @copyright 2022 WP EzPz
 * @license   GPL 3.0+
 * @link      https://wpezpzdev.com/
 */
namespace EZPZ_TWEAKS\Engine\Role;

class Role {


    public static function get_roles() {
        global $wp_roles;
        $all_roles = $wp_roles->roles;
        return $all_roles;
    }

    public static function get_key_value_rules() {
        $all_roles = self::get_roles();
        $key_value_rules = array();
        foreach ($all_roles as $key => $value) {
            $key_value_rules[] = [
                'name' => $value['name'],
                'key' => $key,
            ];
        }
        return $key_value_rules;
    }

    public static function get_current_user_role() {
        $user = wp_get_current_user();
        $user_roles = ( array ) $user->roles;
        $all_rules = self::get_key_value_rules();
        $user_role = '';
        foreach ( $all_rules as $role ) {
            if ( $user_roles[0] === $role['key'] ) {
                $user_role = $role['key'];
                break;
            }
        }

        return $user_role;
    }
}