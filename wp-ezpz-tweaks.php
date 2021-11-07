<?php

/**
 * @package   WPEzPz Tweaks
 * @author    WP EzPz <info@wpezpzdev.com/>
 * @copyright 2020 WP EzPz
 * @license   GPL 3.0
 * @link      https://wpezpzdev.com/
 *
 * Plugin Name:     WPEzPz Tweaks
 * Description:     EzPz Tweaks is an all-in-one WordPress plugin that helps you personalize the admin panel appearances, clean your site code and remove unwanted features to increase its security and improve performance.
 * Version:         1.0.3
 * Author:          WP EzPz
 * Author URI:      https://wpezpzdev.com/
 * Text Domain:     wpezpz-tweaks
 * License:         GPL 3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:     /languages
 * Requires PHP:    7.0
 * WordPress-Plugin-Boilerplate-Powered: v3.2.0
 */

// If this file is called directly, abort.
use EZPZ_TWEAKS\Engine\Initialize;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

define( 'EZPZ_TWEAKS_VERSION', '1.0.3' );
define( 'EZPZ_TWEAKS_TEXTDOMAIN', 'wpezpz-tweaks' );
define( 'EZPZ_TWEAKS_NAME', __( 'WPEzPz Tweaks', EZPZ_TWEAKS_TEXTDOMAIN ) );
define( 'EZPZ_TWEAKS_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'EZPZ_TWEAKS_PLUGIN_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'EZPZ_TWEAKS_PLUGIN_ABSOLUTE', __FILE__ );
define( 'EZPZ_TWEAKS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );


add_action(
	'init',
	static function () {
		load_plugin_textdomain( EZPZ_TWEAKS_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

if ( version_compare( PHP_VERSION, '7.0.0', '<=' ) ) {
	add_action(
		'admin_init',
		static function () {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	add_action(
		'admin_notices',
		static function () {
			echo wp_kses_post(
				sprintf(
					'<div class="notice notice-error"><p>%s</p></div>',
					__( '"WP EzPz Tweaks" requires PHP 7 or newer.', EZPZ_TWEAKS_TEXTDOMAIN )
				)
			);
		}
	);

	// Return early to prevent loading the plugin.
	return;
}

$ezpz_tweaks_libraries = require_once EZPZ_TWEAKS_PLUGIN_ROOT . 'vendor/autoload.php';

require_once EZPZ_TWEAKS_PLUGIN_ROOT . 'vendor/cmb2/init.php';

require_once EZPZ_TWEAKS_PLUGIN_ROOT . 'functions/functions.php';

$requirements = new \Micropackage\Requirements\Requirements(
	EZPZ_TWEAKS_TEXTDOMAIN,
	array(
		'php' => '7.0',
		'wp'  => '5.3',
	)
);

if ( ! $requirements->satisfied() ) {
	$requirements->print_notice();

	return;
}

if ( ! wp_installing() ) {
	add_action(
		'plugins_loaded',
		static function () use ( $ezpz_tweaks_libraries ) {
			new Initialize( $ezpz_tweaks_libraries );
		}
	);
}

function ezpz_tweaks_activate() {
	ezpz_tweaks_upgrade_procedure();
	
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ezpz_tweaks_activate' );

function ezpz_tweaks_deactivate() {
	delete_option( 'ezpz_tweaks_dashboard_widgets' );

	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ezpz_tweaks_deactivate' );

function change_login_logo() {
	$customizing_option = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding' );

	if ( isset( $customizing_option['custom_logo'] ) ) {
		echo '<style type="text/css">h1 a {background-image: url( "' . esc_url( $customizing_option['custom_logo'] ) . '" ) !important; }</style>';
	}
}

add_action( 'login_head', 'change_login_logo' );

/**
 * Upgrade procedure
 *
 * @return void
 */
function ezpz_tweaks_upgrade_procedure() {
	if ( ! is_admin() ) {
		return;
	}

	$version = get_option( 'ezpz-tweaks-version' );

	if ( ! version_compare( EZPZ_TWEAKS_VERSION, $version, '>' ) ) {
		return;
	}

	update_option( 'ezpz-tweaks-version', EZPZ_TWEAKS_VERSION );
	delete_option( EZPZ_TWEAKS_TEXTDOMAIN . '_fake-meta' );
}

add_action( 'admin_init', 'ezpz_tweaks_upgrade_procedure' );

function ezpz_tweaks_change_plugin_priority() {
	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	
    array_splice($active_plugins, $this_plugin_key, 1);
    array_unshift($active_plugins, $this_plugin);

    update_option('active_plugins', $active_plugins);
}
add_action( 'activated_plugin', 'ezpz_tweaks_change_plugin_priority' );
