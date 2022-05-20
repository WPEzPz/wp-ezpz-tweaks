<?php
namespace EZPZ_TWEAKS\Engine\MenuEditor;

/**
 * Class Admin_Bar_Edit
 * @package EZPZ_TWEAKS\Engine\MenuEditor
 */

use EZPZ_TWEAKS\Engine\Role\Role;

class Admin_Bar_Edit {


    protected static $_instance;
    protected static $_nodes;

    public function __construct() {
        if ( !is_user_logged_in()) {
            return;
        }

        add_action( 'admin_bar_menu', array( $this, 'set_instance' ), 999 );

        // get current user role
        $user_role = Role::get_current_user_role();

        $admin_bar_based_on_user_role = get_option( 'wpezpz_tweaks_admin_bar_edit-' . $user_role );

        if (!empty($admin_bar_based_on_user_role)) {
            $option_data = $admin_bar_based_on_user_role;
        } else {
            $option_data = get_option( 'wpezpz_tweaks_admin_bar_edit-general' );
        }

        if ( !empty( $option_data ) && ! is_object( $option_data ) ) {
            add_action( 'wp_before_admin_bar_render', array( $this, 'remove_all_menus' ) );
            add_action( 'wp_before_admin_bar_render', array( $this, 'add_menus_based_on_option' ), 999 );
        }

    }
    
    public function set_instance( \WP_Admin_Bar $walker ) {
        self::$_instance = $walker;
        self::$_nodes = $walker->get_nodes();
        $this->add_extra_data_to_nodes();
    }

    public static function get_walker() : \WP_Admin_Bar {
        return self::$_instance;
    }

    public static function get_nodes() {
        return self::$_nodes;
    }

    public static function sort_nodes_by_periority ( $nodes ) {
        // Set position
        foreach ( $nodes as $node ) {
            $node->position = isset( $node->position ) ? $node->position : 1;

            // move top-secondary node to end
            if ( $node->id == 'top-secondary' ) {
                $node->position = 999;
            }
        }

        // Sort nodes based on priority
        usort( $nodes, function( $a, $b ) {
            return $a->position - $b->position;
        } );

        return $nodes;
    }


    public static function merge_data( $custom_data, $default_data ) {

        if ( ! is_array( $custom_data ) || $custom_data === $default_data || !isset($custom_data[0]['name']) ) {
            return $default_data;
        }


        foreach ( $custom_data as $value ) {
            preg_match('/(?<=[\[]).*(?=[\]])/', $value['name'], $matches);
            if ( !isset( $matches[0] ) ) {
                continue;
            }
            $menu_item = $matches[0];
            $matches = [];
            preg_match('/.+?(?=\[)/', $value['name'], $matches);
            if ( !isset( $matches[0] ) ) {
                continue;
            }
            $menu_data = str_replace('menu-item-', '', $matches[0]);
            $menu_data = ($menu_data == 'parent-id') ? 'parent' : $menu_data;

            if (!isset($default_data[$menu_item])) {
                $default_data[$menu_item] = new \stdClass();
                $default_data[$menu_item]->id = $menu_item;
            }

            if ($menu_data == 'classes') {
                $default_data[$menu_item]->classes = explode(' ', $value['value']);
                if (empty($default_data[$menu_item]->meta['class'])) {
                    $default_data[$menu_item]->meta['class'] = $value['value'];
                } else {
                    $default_data[$menu_item]->meta['class'] .= ' ' . $value['value'];
                }
                continue;
            }

            if ($menu_data == 'url') {
                $default_data[$menu_item]->href = $value['value'];
                continue;
            }

            if (isset($default_data[$menu_item])) {
                $default_data[$menu_item]->$menu_data = $value['value'];
            } else {
                $default_data[$menu_item]->$menu_data = $value['value'];
            }

        }

        $default_data = self::sort_nodes_by_periority( $default_data );

        return $default_data;
    }


    public static function remove_all_menus() {
        foreach ( self::get_nodes() as $node ) {
            self::get_walker()->remove_node( $node->id );
        }

        return true;
    }

    private static function maybe_save_menu_data() {

        $user_role = ( isset( $_POST['user_role'] ) ) ? sanitize_key($_POST['user_role']) : 'general';
        if (isset($_POST['nav-menu-data']) && current_user_can( 'edit_theme_options' )) {

            $nodes_data = self::merge_data( json_decode( stripslashes( $_POST['nav-menu-data'] ), true ), self::get_nodes());
            // if visibility is not set, set to off
            foreach ( $nodes_data as $node ) {
                if ( !isset( $node->visibility ) ) {
                    $node->visibility = 'off';
                }
            }

            return update_option(
                'wpezpz_tweaks_admin_bar_edit-' . $user_role,
                $nodes_data,
                true
            );
        }
    }

    public static function add_menus_based_on_option() {

        $user_role = ( isset( $_POST['user_role'] ) ) ? sanitize_key($_POST['user_role']) : 'general';

        self::maybe_save_menu_data();

        $user_role = Role::get_current_user_role();

        $admin_bar_based_on_user_role = get_option( 'wpezpz_tweaks_admin_bar_edit-' . $user_role );

        if (!empty($admin_bar_based_on_user_role)) {
            $option_data = $admin_bar_based_on_user_role;
        } else {
            $option_data = get_option( 'wpezpz_tweaks_admin_bar_edit-general' );
        }

        if ( !empty( $option_data ) && ! is_object( $option_data ) ) {
            $default_data = $option_data;
        } else {
            $default_data = self::get_nodes();
        }

        foreach (array_reverse($default_data) as $node ) {
            // Hide node if visibility is not eauql to on or default
            if ( !isset($node->visibility) || !in_array($node->visibility, array('on', 'default'))) {
                continue;
            }

            self::get_walker()->add_node( $node );
        }

    }

    private function add_extra_data_to_nodes() {
        foreach ( self::get_nodes() as $node ) {
            $node->type = ( !isset($node->type) ) ? 'admin_bar_default' : '';
        }
    }

}
