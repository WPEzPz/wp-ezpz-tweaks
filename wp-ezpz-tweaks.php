<?php
/**
 * @package   WPEzPz Tweaks
 * @author    WP EzPz <info@wpezpzdev.com>
 * @license   GPL 3.0
 * @link      https://wpezpzdev.com/
 *
 * Plugin Name:     WPEzPz Tweaks
 * Description:     WPEzPz Tweaks is an all-in-one WordPress plugin that helps you personalize the admin panel appearances, clean your site code and remove unwanted features to increase its security and improve performance.
 * Version:         1.2.0
 * Author:          WP EzPz
 * Author URI:      https://wpezpzdev.com/
 * Text Domain:     wpezpz-tweaks
 * License:         GPL 3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:     /languages
 * Requires PHP:    7.0
 */

// If this file is called directly, abort.
use EZPZ_TWEAKS\Engine\Initialize;
use EZPZ_TWEAKS\Engine\Backups\Import_Export;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

define( 'EZPZ_TWEAKS_VERSION', '1.2.0' );
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
	ezpz_tweaks_admin_set_install_time();
	
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ezpz_tweaks_activate' );

function ezpz_tweaks_deactivate() {
	delete_option( 'wpezpz_dashboard_widgets' );

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

	$version = get_option( 'wpezpz_version' );

	if ( ! version_compare( EZPZ_TWEAKS_VERSION, $version, '>' ) ) {
		return;
	}

	update_option( 'wpezpz_version', EZPZ_TWEAKS_VERSION );
	delete_option( EZPZ_TWEAKS_TEXTDOMAIN . '_fake-meta' );
}

add_action( 'admin_init', 'ezpz_tweaks_upgrade_procedure' );


function ezpz_tweaks_admin_notice() {
	if ( ! is_admin() ) {
		return;
	}
	
	$install_date = get_option( 'wpezpz_tweaks_install_time' );
	
	if ( ! $install_date ) {
		return;
	}
	
	$install_date = strtotime( $install_date );
	$install_date = strtotime( '+7 day', $install_date );
	$install_date = date( 'Y-m-d H:i:s', $install_date );
	$user_id = get_current_user_id();
	$is_dissmissed = (bool)get_user_meta( $user_id, 'ezpz_tweaks_review_notice_dismissed', true );
	if ( $is_dissmissed ) {
		return;
	} else if ($install_date > date( 'Y-m-d H:i:s')) {
		return;
	}

	$notice = __( 'Thank you for using <strong>WP EzPz Tweaks</strong>! You have been using it for over a week. What do you think about it? ', EZPZ_TWEAKS_TEXTDOMAIN );
	$notice .= '<a href="https://wordpress.org/support/plugin/wpezpz-tweaks/reviews/?rate=5#new-post" target="_blank" class="button button-primary">' . __( 'Rate it', EZPZ_TWEAKS_TEXTDOMAIN ) . '</a>';

	echo '<div class="notice notice-success is-dismissible"><p>' . $notice . ' <a href="?ezpz-tweaks-review-dismissed">Dismiss</a></p></div>';
}

function ezpz_tweaks_admin_notice_dissmiss() {
	if ( ! is_admin() ) {
		return;
	}

	$user_id = get_current_user_id();
    if ( isset( $_GET['ezpz-tweaks-review-dismissed'] ) ) {
        add_user_meta( $user_id, 'ezpz_tweaks_review_notice_dismissed', 'true', true );
	}
}


add_action( 'admin_notices', 'ezpz_tweaks_admin_notice' );
add_action( 'admin_init', 'ezpz_tweaks_admin_notice_dissmiss' );

function ezpz_tweaks_admin_set_install_time() {
	if ( ! is_admin() ) {
		return;
	}

	if ( empty(get_option( 'wpezpz_tweaks_install_time')) ) {
		add_option( 'wpezpz_tweaks_install_time', date('Y-m-d H:i:s') );
	} else {
		update_option( 'wpezpz_tweaks_install_time', date('Y-m-d H:i:s') );
	}
}

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

$backup = (new Import_Export())->register_ajax();

function cmb2_render_range( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ){

	$slider = $field_type_object->input( array(
	  'type'  => 'range',
	  'class' => 'cmb2-range',
	  'start' => absint( $field_escaped_value ),
	  'min'   => $field->min(),
	  'step'  => $field->step(),
	  'max'   => $field->max(),
	  'desc'  => '',
	) );

	$slider .= '<span class="range-text">' . $field->value_label() . ' <span class="range-value"></span></span>';
	$slider .= $field_type_object->_desc(true);
	echo $slider;
}
add_filter( 'cmb2_render_range', 'cmb2_render_range', 10, 5 );
