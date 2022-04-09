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
        // add_action( 'wp_before_admin_bar_render', array( $this, 'wp_before_admin_bar_render' ) );
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


}
