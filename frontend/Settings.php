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

namespace EZPZ_TWEAKS\Frontend;

use EZPZ_TWEAKS\Engine\Backups\Import_Export;
use EZPZ_TWEAKS\Backend\Settings_Page;
use EZPZ_TWEAKS\Engine\Features\Font\Font;

class Settings {
	/**
	 * @var false|mixed|void
	 */
	private $customizing_option;
	private $performance_option;
	private $security_option;

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() {

		$this->get_locale		  = get_locale();
		$this->customizing_option = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding' );
		$this->performance_option = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-performance' );
		$this->security_option 	  = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-security' );

		$Backups = new Import_Export();
		$font = new Font();

		add_action( 'init', array( $this, 'disable_emojis' ) );
		add_action( 'init', array( $this, 'disable_embeds_code_init' ), 9999 );
		add_action( 'init', array( $this, 'disable_xmlrpc' ) );
		add_action( 'init', array( $this, 'hide_admin_bar' ), 9999 );
		add_action( 'init', array( $this, 'limit_post_revisions' ));
		add_action( 'wp_head', array( $this, 'change_adminbar_font' ), 30 );
		add_action( 'admin_enqueue_scripts', array( $this, 'adminbar_logo' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'adminbar_logo' ) );
		add_action( 'after_setup_theme', array( $this, 'remove_shortlink' ) );
		add_filter( 'after_setup_theme', array( $this, 'remove_wp_version_from_head' ) );
		add_action( 'login_head', array( $font, 'wp_change_login_font' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $font, 'render_fonts_css' ), 30 );

		add_filter( 'rest_authentication_errors', array( $this, 'disable_wp_rest_api' ) );
		add_filter( 'comment_form_default_fields', array( $this, 'remove_website_field' ) );
		add_filter( 'login_message', array( $this, 'add_login_page_custom_text' ) );

		// Backups
		add_action( 'ezpz_register_fields', array( $Backups, 'add_options' ) );

		add_filter( 'login_errors', array( $this, 'no_wordpress_errors') );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_disable_heartbeat' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_disable_heartbeat' ), 99 );
		add_filter( 'heartbeat_settings', array( $this, 'maybe_modify_heartbeat' ), 99, 1 );
	}

	public function disable_emojis() {
		if ( isset( $this->performance_option['disable_wp_emoji'] ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
			add_filter( 'wp_resource_hints', array( $this, 'disable_emojis_remove_dns_prefetch' ), 10, 2 );
		}
	}

	public function limit_post_revisions() {
		if ( isset( $this->performance_option['limit_post_revisions'] ) ) {

			add_filter( 'wp_revisions_to_keep', function ( $num, $post ) {

				$max_revisions = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-performance' )['limit_post_revisions'];
				if ( $max_revisions ) {
					return $max_revisions;
				}

			}, 10, 2 );

		}
	}

	public function disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		} else {
			return array();
		}
	}

	public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' == $relation_type ) {
			/** This filter is documented in wp-includes/formatting.php */
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
			$urls          = array_diff( $urls, array( $emoji_svg_url ) );
		}

		return $urls;
	}

	function no_wordpress_errors( $errors ) {
		if ( isset( $this->security_option['hide_login_error_messages'] ) ) {
			return sprintf(
				/* translators: %s: URL that allows the user to retrieve the lost password */
				__( '<strong>Error:</strong> The username or password you entered is incorrect. <a href="%s">Lost your password?</a>', EZPZ_TWEAKS_TEXTDOMAIN ),
				wp_lostpassword_url()
			);
		}

		return $errors;
	}

	public function disable_wp_rest_api( $access ) {
		if ( !is_user_logged_in() && !is_admin() && isset( $this->security_option['disable_rest_api'] ) ) {
			// Remove REST API info from head and headers
			remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );

			$error_message = esc_html__( 'Public access to the REST API has been limited.', EZPZ_TWEAKS_TEXTDOMAIN );

			if ( is_wp_error( $access ) ) {
				$access->add( 'rest_cannot_access', $error_message, array( 'status' => rest_authorization_required_code() ) );

				return $access;
			}

			$access = new \WP_Error( 'rest_cannot_access', $error_message, array( 'status' => rest_authorization_required_code() ) );

		}

		return $access;
	}

	public function remove_website_field( $fields ) {
		if ( isset( $this->performance_option['disable_website_field'] ) ) {
			if ( isset( $fields['url'] ) ) {
				unset( $fields['url'] );
			}
		}

		return $fields;
	}

	public function disable_embeds_code_init() {
		if ( isset( $this->performance_option['disable_wp_embed'] ) ) {
			// Remove the REST API endpoint.
			remove_action( 'rest_api_init', 'wp_oembed_register_route' );

			// Turn off oEmbed auto discovery.
			add_filter( 'embed_oembed_discover', '__return_false' );

			// Don't filter oEmbed results.
			remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

			// Remove oEmbed discovery links.
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

			// Remove oEmbed-specific JavaScript from the front-end and back-end.
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
			add_filter( 'tiny_mce_plugins', array( $this, 'disable_embeds_tiny_mce_plugin' ) );

			// Remove all embeds rewrite rules.
			add_filter( 'rewrite_rules_array', array( $this, 'disable_embeds_rewrites' ) );

			// Remove filter of the oEmbed result before any HTTP requests are made.
			remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
		}
	}

	public function disable_embeds_tiny_mce_plugin( $plugins ) {
		return array_diff( $plugins, array( 'wpembed' ) );
	}

	public function disable_embeds_rewrites( $rules ) {
		foreach ( $rules as $rule => $rewrite ) {
			if ( false !== strpos( $rewrite, 'embed=true' ) ) {
				unset( $rules[ $rule ] );
			}
		}

		return $rules;
	}

	public function disable_xmlrpc() {
		if ( isset( $this->security_option['disable_xmlrpc'] ) ) {
			// Remove RSD link from head
			remove_action( 'wp_head', 'rsd_link' );

			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'xmlrpc_methods', '__return_empty_array', PHP_INT_MAX );
			add_filter( 'wp_headers', array( $this, 'remove_x_pingback' ) );
			add_filter( 'bloginfo_url', array( $this, 'remove_pingback_url' ), 1, 2 );
			add_filter( 'bloginfo', array( $this, 'remove_pingback_url' ), 1, 2 );

			// Force to uncheck pingbck and trackback options
			add_filter( 'pre_option_default_ping_status', '__return_zero' );
			add_filter( 'pre_option_default_pingback_flag', '__return_zero' );

			$this->set_disabled_header();
		}
	}

	public function set_disabled_header() {
		// Return immediately if SCRIPT_FILENAME not set
		if ( ! isset( $_SERVER['SCRIPT_FILENAME'] ) ) {
			return;
		}

		$file = basename( $_SERVER['SCRIPT_FILENAME'] );

		// Break only if xmlrpc.php file was requested.
		if ( 'xmlrpc.php' !== $file ) {
			return;
		}

		$header = 'HTTP/1.1 403 Forbidden';

		header( $header );
		echo esc_url( $header );
		die();
	}

	public function remove_x_pingback( $headers ) {
		unset( $headers['X-Pingback'] );

		return $headers;
	}

	public function remove_pingback_url( $output, $show ) {
		if ( $show == 'pingback_url' ) {
			$output = '';
		}

		return $output;
	}

	public function hide_admin_bar() {
		if ( isset( $this->customizing_option['hide_admin_bar'] ) ) {
			$user_roles = ezpz_tweaks_wp_roles_array();

			foreach ( $user_roles as $role => $name ) {
				if ( current_user_can( $role ) ) {
					show_admin_bar( false );
					break;
				}
			}
		}
	}

	public function change_adminbar_font() {
		if( is_admin_bar_showing() ) {
			$field_name  = $this->get_locale == 'fa_IR' ? 'admin-font-fa': 'admin-font';
			$admin_font  = $this->customizing_option[ $field_name ] ?? false;

			if ( isset( $admin_font ) && $admin_font != 'wp-default' ) {
				if ( $this->get_locale == 'fa_IR' ) {
					wp_register_style( EZPZ_TWEAKS_TEXTDOMAIN . '-' . $field_name, '' );
					wp_enqueue_style( EZPZ_TWEAKS_TEXTDOMAIN . '-' . $field_name );
				} else {
					wp_enqueue_style( EZPZ_TWEAKS_TEXTDOMAIN . '-' . $field_name, 'https://fonts.googleapis.com/css?family=' . esc_attr( $admin_font ) );
					$admin_font = ezpz_tweaks_get_google_font_name( $admin_font );
				}

				wp_add_inline_style( EZPZ_TWEAKS_TEXTDOMAIN . '-' . $field_name, '#wpadminbar *:not([class="ab-icon"]) {font-family:"' . esc_html( $admin_font ) . '" !important;}' );
			}
		}
	}

	public function adminbar_logo() {
		if (!is_admin_bar_showing()) {
			return;
		}
		if ( ( isset( $this->customizing_option['custom_logo'] ) && !isset( $_POST['custom_logo'] ) ) || ( isset( $_POST['custom_logo'] ) && !empty( $_POST['custom_logo'] ) ) ) {
			$custom_logo = isset( $_POST['custom_logo'] ) ? sanitize_text_field( $_POST['custom_logo'] ) : $this->customizing_option['custom_logo'];

			wp_register_style( EZPZ_TWEAKS_TEXTDOMAIN . '-adminbar-logo', false );

			wp_add_inline_style( EZPZ_TWEAKS_TEXTDOMAIN . '-adminbar-logo',
				'#wpadminbar #wp-admin-bar-wp-logo>.ab-item {
					padding: 0 7px;
					background-image: url(' . esc_url( $custom_logo ) . ') !important;
					background-size: 50%;
					background-position: center;
					background-repeat: no-repeat;
					opacity: 1;
				}
				#wpadminbar #wp-admin-bar-wp-logo>.ab-item .ab-icon:before {
					content: " ";
					top: 2px;
				}'
			);
			wp_enqueue_style( EZPZ_TWEAKS_TEXTDOMAIN . '-adminbar-logo' );
		}
	}

	public function change_login_font() {
		if( !is_user_logged_in() ) {
			$field_name  = $this->get_locale == 'fa_IR' ? 'admin-font-fa': 'admin-font';
			if (!isset($this->customizing_option[ $field_name ])) {
				return;
			}
			$admin_font  = $this->customizing_option[ $field_name ];

			if ( isset( $admin_font ) && $admin_font != 'wp-default' ) {
				if ( $this->get_locale == 'fa_IR' ) {
					wp_enqueue_style( EZPZ_TWEAKS_TEXTDOMAIN . '-' . $field_name, EZPZ_TWEAKS_PLUGIN_ROOT_URL . 'assets/css/persianfonts.css' );
				} else {
					wp_enqueue_style( EZPZ_TWEAKS_TEXTDOMAIN . '-' . $field_name, 'https://fonts.googleapis.com/css?family=' . esc_attr( $admin_font ) );
					$admin_font = ezpz_tweaks_get_google_font_name( $admin_font );
				}

				wp_add_inline_style( EZPZ_TWEAKS_TEXTDOMAIN . '-' . $field_name, '* {font-family:"' . esc_html( $admin_font ) . '" !important;}' );
			}
		}
	}

	public function remove_shortlink() {
		if ( isset( $this->performance_option['remove_shortlink'] ) ) {
			// remove HTML meta tag
			// <link rel='shortlink' href='http://example.com/?p=25' />
			remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

			// remove HTTP header
			// Link: <https://example.com/?p=25>; rel=shortlink
			remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
		}
	}

	public function remove_wp_version_from_head() {
		if ( isset( $this->security_option['remove_wp_version'] ) ) {
			// remove version from head
			remove_action( 'wp_head', 'wp_generator' );

			// remove version from rss
			add_filter( 'the_generator', '__return_empty_string' );

			// remove version from scripts and styles
			add_filter( 'style_loader_src', array( $this, 'remove_version_scripts_styles' ), 9999 );
			add_filter( 'script_loader_src', array( $this, 'remove_version_scripts_styles' ), 9999 );
		}
	}



	public function remove_version_scripts_styles( $src ) {
		if ( strpos( $src, 'ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}

	public function add_login_page_custom_text()
	{
		if ( isset( $this->customizing_option['login_custom_text'] ) ) {
			$message = '<div style="box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.2); background: #e6f6fb; color: #444; border-top: 4px solid #00a0d2; margin: 0 0 1em; padding: 12px; font-size: 14px; text-align: center;">
							<p><strong>'. $this->customizing_option['login_custom_text'] .'</strong></p>
						</div>';

			return $message;
		}
	}

	public function check_location_for_heartbeat( $location ) {

		$location_test = array(
			'dashboard'   => function() {
				return is_admin();
			},
			'frontend'  => function() {
				return ! is_admin();
			},
			'post_editor' => function() {
				$_query_string = filter_input( INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_URL );
				$_request_uri  = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );

				if ( $_query_string && $_request_uri ) {
					$current_url = wp_unslash( $_query_string . '?' . $_request_uri );
				} elseif ( $_query_string ) {
					$current_url = wp_unslash( $_request_uri );
				} else {
					$current_url = admin_url();
				}
				return ( '/wp-admin/post.php' === wp_parse_url( $current_url )['path'] );
			},
		);

		if ( isset( $location_test[ $location ] ) ) {
			return $location_test[ $location ]();
		}

		return false;
	}

	public function maybe_disable_heartbeat() {
		$settings = $this->get_heartbeat_settings();
		if (!empty($settings)) {
			foreach ( $settings as $location => $rule ) {
				if ( array_key_exists( 'value', $rule ) && 'disable' === $rule['value'] ) {
					if ( $this->check_location_for_heartbeat( $location ) ) {
						wp_deregister_script( 'heartbeat' );
						return;
					}
				}
			}
		}
	}


	public function maybe_modify_heartbeat( $s ) {
		$settings = $this->get_heartbeat_settings();

		if (!empty($settings)) {
			foreach ( $settings as $location => $rule ) {
				if ( array_key_exists( 'value', $rule ) && 'modify' === $rule['value'] ) {
					if ( $this->check_location_for_heartbeat( $location ) ) {
						$s['interval'] = intval( $rule['range'] );

						return $s;
					}
				}
			}
		}


		return $s;
	}

	public function get_heartbeat_settings( ) {

		if (!isset($this->performance_option['disable_dashboard_heartbeat'])) {
			return;
		}

		return [
			'dashboard' => [
				'value'	=> $this->performance_option['disable_dashboard_heartbeat'],
				'range'	=> $this->performance_option['range_modify_dashboard_heartbeat'],
			],
			'frontend' => [
				'value'	=> $this->performance_option['disable_frontend_heartbeat'],
				'range'	=> $this->performance_option['range_modify_frontend_heartbeat'],
			],
			'post_editor' => [
				'value'	=> $this->performance_option['disable_post_editor_heartbeat'],
				'range'	=> $this->performance_option['range_modify_post_editor_heartbeat'],
			],
		];
	}
}
