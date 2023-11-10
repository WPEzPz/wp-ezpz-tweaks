<?php
/**
 * EZPZ_TWEAKS
 * Related feature: Custom Admin CSS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpz.dev>
 * @copyright 2020 WP EzPz
 * @license   GPL 2.0+
 * @link      https://wpezpzdev.com/
 */

namespace EZPZ_TWEAKS\Engine\Features;

class Custom_Admin_CSS {

	public $customizing_option;

	public function __construct() {
		$this->customizing_option = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding' );
	}

    /**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() {

		add_action( 'admin_footer', array( $this, 'custom_admin_css' ), 9999 );

	}


	/**
	 * Load custom admin css that user entered into branding options
	 */
	public function custom_admin_css() {
		if (isset( $_POST['custom_admin_css'] ) && !empty($_POST['custom_admin_css']) && isset($_POST['custom_admin_css']['cm_code'])) {
			echo '<style type="text/css">' . $_POST['custom_admin_css']['cm_code'] . '</style>';
		} else if ( isset( $this->customizing_option['custom_admin_css'] ) && isset($this->customizing_option['custom_admin_css']['cm_code']) ) {
			echo '<style type="text/css">' . $this->customizing_option['custom_admin_css']['cm_code'] . '</style>';
		}
	}
}