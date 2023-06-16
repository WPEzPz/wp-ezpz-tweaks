<?php
/**
 * EZPZ_TWEAKS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpz.dev>
 * @copyright 2020 WP EzPz
 * @license   GPL 2.0+
 * @link      https://wpezpzdev.com/
 */

namespace EZPZ_TWEAKS\Backend;

use EZPZ_TWEAKS\Engine\Settings\Settings;

use function add_action;
use function plugins_url;
use function wp_enqueue_script;
use function wp_enqueue_style;

/**
 * This class contain the Enqueue stuff for the backend
 */
class Enqueue {

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() {
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}


	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_styles() {
		if( get_locale() == 'fa_IR' ) {
			wp_enqueue_style( EZPZ_TWEAKS_TEXTDOMAIN . '-persianfonts', plugins_url( 'assets/css/persianfonts.css', EZPZ_TWEAKS_PLUGIN_ABSOLUTE ), '', EZPZ_TWEAKS_VERSION );
		}

		wp_enqueue_style( EZPZ_TWEAKS_TEXTDOMAIN . '-admin-styles', plugins_url( 'assets/css/admin.css', EZPZ_TWEAKS_PLUGIN_ABSOLUTE ), array( 'dashicons' ), EZPZ_TWEAKS_VERSION );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts($hook_suffix) {
		if( $hook_suffix == 'toplevel_page_' . EZPZ_TWEAKS_TEXTDOMAIN ) {
			wp_enqueue_script( EZPZ_TWEAKS_TEXTDOMAIN . 'cmb2_conditional_logic', plugins_url( 'assets/js/cmb2-conditional-logic.js', EZPZ_TWEAKS_PLUGIN_ABSOLUTE ), array( 'jquery' ), '1.0.0' );
		}

		wp_enqueue_script(
			EZPZ_TWEAKS_TEXTDOMAIN . '-admin-script',
			plugins_url( 'assets/js/admin.js', EZPZ_TWEAKS_PLUGIN_ABSOLUTE ),
			array( 'jquery', 'jquery-ui-sortable', 'underscore' ),
			EZPZ_TWEAKS_VERSION,
			false
		);
		$cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
  		wp_localize_script('jquery', 'cm_settings', $cm_settings);

		wp_enqueue_script( EZPZ_TWEAKS_TEXTDOMAIN . '-admin-script', plugins_url( 'assets/js/admin.js', EZPZ_TWEAKS_PLUGIN_ABSOLUTE ), array( 'jquery', 'jquery-ui-sortable', 'underscore' ), EZPZ_TWEAKS_VERSION, false );
		wp_enqueue_code_editor( array( 'type' => 'text/css' ) );

		wp_localize_script(
			EZPZ_TWEAKS_TEXTDOMAIN . '-admin-script',
			'ezpz_object',
			[
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'security'  => wp_create_nonce( 'ezpz-nonce' ),
				'is_rtl'	=> is_rtl(),
				'strings'	=> [
					'delete' 	=> __( 'Delete', EZPZ_TWEAKS_TEXTDOMAIN ),
					'restore'	=> __( 'Restore', EZPZ_TWEAKS_TEXTDOMAIN ),
					'restoreConfirm'	=> __( 'Are you sure you want to restore this this backup? Your current configuration will be overwritten.', EZPZ_TWEAKS_TEXTDOMAIN ),
				]
			],
		);
	}

}
