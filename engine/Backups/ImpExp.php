<?php
/**
 * EZPZ_TWEAKS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpzdev.com>
 * @license   GPL 2.0+
 * @link      https://wpezpzdev.com/
 */

namespace EZPZ_TWEAKS\Engine\Backups;
/**
 * Provide Import and Export of the settings of the plugin
 */
class ImpExp {

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() {

		// Add the export settings method
		\add_action( 'admin_init', array( $this, 'settings_export' ) );
		// Add the import settings method
		\add_action( 'admin_init', array( $this, 'settings_import' ) );
	}

	/**
	 * Process a settings export from config
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function settings_export() {
		if ( empty( $_POST[ 'w_action' ] ) || 'export_settings' !== \sanitize_text_field( \wp_unslash( $_POST[ 'w_action' ] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		if ( !\wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST[ 'w_export_nonce' ] ) ), 'w_export_nonce' ) ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			return;
		}

		if ( !\current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings      = array();
		$settings[ 0 ] = \get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding' );
		$settings[ 1 ] = \get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-performance' );
		$settings[ 2 ] = \get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-security' );

		\ignore_user_abort( true );

		\nocache_headers();
		\header( 'Content-Type: application/json; charset=utf-8' );
		\header( 'Content-Disposition: attachment; filename=ezpz-tweaks-settings-export-' . \gmdate( 'm-d-Y' ) . '.json' );
		\header( 'Expires: 0' );

		echo \wp_json_encode( $settings, JSON_PRETTY_PRINT );

		exit;
	}

	/**
	 * Process a settings import from a json file
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function settings_import() {
		if ( empty( $_POST[ 'w_action' ] ) || 'import_settings' !== \sanitize_text_field( \wp_unslash( $_POST[ 'w_action' ] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		if ( !\wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST[ 'w_import_nonce' ] ) ), 'w_import_nonce' ) ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			return;
		}

		if ( !\current_user_can( 'manage_options' ) ) {
			return;
		}

		$file_name_parts = \explode( '.', $_FILES[ 'w_import_file' ][ 'name' ] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$extension       = \end( $file_name_parts );

		if ( 'json' !== $extension ) {
			\wp_die( \esc_html__( 'Please upload a valid .json file', EZPZ_TWEAKS_TEXTDOMAIN ) );
		}

		$import_file = $_FILES[ 'w_import_file' ][ 'tmp_name' ]; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( empty( $import_file ) ) {
			\wp_die( \esc_html__( 'Please upload a file to import', EZPZ_TWEAKS_TEXTDOMAIN ) );
		}

		// Retrieve the settings from the file and convert the json object to an array.
		$settings_file = file_get_contents( $import_file );// phpcs:ignore

		if ( $settings_file ) {
			$settings = \json_decode( (string) $settings_file );

			\update_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding', \get_object_vars( $settings[ 0 ] ) );
			\update_option( EZPZ_TWEAKS_TEXTDOMAIN . '-performance', \get_object_vars( $settings[ 1 ] ) );
			\update_option( EZPZ_TWEAKS_TEXTDOMAIN . '-security', \get_object_vars( $settings[ 2 ] ) );

			\wp_safe_redirect( \admin_url( 'admin.php?page=' . EZPZ_TWEAKS_TEXTDOMAIN ) );
			exit;
		}

		new \WP_Error(
			'ezpz-tweaks_import_settings_failed',
			\__( 'Failed to import the settings.', EZPZ_TWEAKS_TEXTDOMAIN )
		);

	}

}
