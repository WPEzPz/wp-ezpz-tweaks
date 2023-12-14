<?php
/**
 * EZPZ_TWEAKS
 * Related feature: Switch editor between block editor and classic editor
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpzdev.com>
 * @license   GPL 2.0+
 * @link      https://wpezpzdev.com/
 */

namespace EZPZ_TWEAKS\Engine\Features;

class Editor_Changer {
    /**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() {
        add_action("wp_ajax_wpezpz_change_page_editor" , [ $this, 'ajax_change_editor']);
	}

    public function ajax_change_editor() {

        check_ajax_referer( 'updates' );

        if ( empty( $_POST['slug'] ) ) {
            wp_send_json_error(
                array(
                    'slug'         => '',
                    'errorCode'    => 'no_plugin_specified',
                    'errorMessage' => __( 'No plugin specified.' ),
                )
            );
        }

        $plugin_slug = sanitize_key( wp_unslash( $_POST['slug'] ) );
        $classic_editor_path = 'classic-editor/classic-editor.php';
        $is_installed = file_exists( WP_PLUGIN_DIR . '/' . $classic_editor_path );

        if ( $plugin_slug === 'classic-editor' ) {
            if ( ! $is_installed ) {
                return wp_ajax_install_plugin();
            }
        
            require_once(ABSPATH .'/wp-admin/includes/plugin.php');
        
            if( ! is_plugin_active( $classic_editor_path ) ) {
                $r = activate_plugin( $classic_editor_path );
                if ( is_wp_error( $r ) ) {
                    wp_send_json_error();
                }
            }
        } else {
            if ( $is_installed ) {
                require_once(ABSPATH .'/wp-admin/includes/plugin.php');	
                if( is_plugin_active( $classic_editor_path ) ) {
                    deactivate_plugins( $classic_editor_path );
                }
            }
        }

        wp_send_json_success();
    }
}