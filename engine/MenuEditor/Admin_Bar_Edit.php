<?php
namespace EZPZ_TWEAKS\Engine\MenuEditor;

/**
 * Class Walker_Admin_Bar_Edit
 * @package EZPZ_TWEAKS\Engine\MenuEditor
 */
class Admin_Bar_Edit {


    protected static $_instance;
    protected static $_nodes;

    public function __construct() {
        add_action( 'admin_bar_menu', array( $this, 'set_instance' ), 999 );

        $option_data = get_option( 'wpezpz_tweaks_admin_bar_edit' );

        if ( !empty( $option_data ) && ! is_object( $option_data ) ) {
            add_action( 'wp_before_admin_bar_render', array( $this, 'remove_all_menus' ) );
    
            add_action( 'wp_before_admin_bar_render', array( $this, 'add_menus_based_on_option' ), 999 );
        }
    }

    public function set_instance( \WP_Admin_Bar $walker ) {
        self::$_instance = $walker;
        self::$_nodes = $walker->get_nodes();
    }

    public static function get_walker() : \WP_Admin_Bar {
        return self::$_instance;
    }

    public static function get_nodes() {
        return self::$_nodes;
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

        return $default_data;
    }


    public static function remove_all_menus() {
        $walker = self::get_walker();
        $nodes = self::get_nodes();
        foreach ( $nodes as $node ) {
            $walker->remove_node( $node->id );
        }

        return true;
    }

    public static function add_menus_based_on_option() {

        if (isset($_POST['nav-menu-data'])) {
            update_option('wpezpz_tweaks_admin_bar_edit', self::merge_data( json_decode( stripslashes( $_POST['nav-menu-data'] ), true ), self::get_nodes()), true);

        }

        $option_data = get_option( 'wpezpz_tweaks_admin_bar_edit' );

        if ( !empty( $option_data ) && ! is_object( $option_data ) ) {
            $default_data = $option_data;
        } else {
            $default_data = self::get_nodes();
        }

        foreach ( $default_data as $node ) {
            self::get_walker()->add_node( $node );
        }
    }

}
