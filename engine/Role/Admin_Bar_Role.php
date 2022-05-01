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

use EZPZ_TWEAKS\Engine\Role\Role;

class Admin_Bar_Role {

    public function __construct() {
        add_filter( 'wpezpz_tweaks_admin_bar_tabs', array( $this, 'get_tabs' ) );
        add_filter( 'wpezpz_tweaks_admin_bar_inputs', array( $this, 'add_user_role_input' ) );
    }

    public static function get_edit_link( $user_role ) {
        return wp_nonce_url(
            add_query_arg(
                array(
                    'action'    => 'edit',
                    'user_role' => $user_role,
                ),
                remove_query_arg( [
                    'action',
                    'user_role',
                ], admin_url( 'admin.php?page='. EZPZ_TWEAKS_TEXTDOMAIN . '-edit-admin-bar' ) )
            ),
            'move-menu_item'
        );
    }

    public static function get_tabs() : string {

        $current_tab = isset( $_GET['user_role'] ) ? sanitize_text_field($_GET['user_role']) : 'general';
        $tabs = '';
        foreach(Role::get_key_value_rules() as $rule) {
            $class = $current_tab == $rule['key'] ? 'wp-tab-active' : '';
            $tabs .= '<li class="'. $class .'"><a href="'. Admin_Bar_Role::get_edit_link( $rule['key'] ) .'">'. $rule['name'] .'</a></li>';
        }

        return $tabs;
    }

    public static function add_user_role_input() {
        $user_role = isset( $_GET['user_role'] ) ? sanitize_text_field($_GET['user_role']) : 'general';
        return '<input type="hidden" name="user_role" value="' . $user_role . '" />';
    }
}