<?php
/**
 * EZPZ_TWEAKS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpzdev.com>
 * @license   GPL 2.0+
 * @link      https://wpezpzdev.com/
 */

namespace EZPZ_TWEAKS\Integrations;

class Custom_Login_Url {

	private $wp_login_php;
	private $security_option;

	public function initialize() {

		$this->security_option = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-security' );

		if( isset( $this->security_option['custom_login_url'] ) ) {
			if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) || ! function_exists( 'is_plugin_active' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}

			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 9999 );
			add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
			add_action( 'template_redirect', array( $this, 'login_redirect_page_email_woocommerce' ) );

			add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
			add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
			add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );
			add_filter( 'site_option_welcome_email', array( $this, 'welcome_email' ) );
			add_filter( 'login_url', array( $this, 'login_url' ), 10, 3 );

			remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
		}

	}

	private function use_trailing_slashes() {

		return ( '/' === substr( get_option( 'permalink_structure' ), - 1, 1 ) );

	}

	private function user_trailingslashit( $string ) {

		return $this->use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );

	}

	private function wp_template_loader() {

		global $pagenow;

		$pagenow = 'index.php';

		if ( ! defined( 'WP_USE_THEMES' ) ) {

			define( 'WP_USE_THEMES', true );

		}

		wp();

		if ( $_SERVER['REQUEST_URI'] === $this->user_trailingslashit( str_repeat( '-/', 10 ) ) ) {

			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/wp-login-php/' );

		}

		require_once( ABSPATH . WPINC . '/template-loader.php' );

		die;

	}

	private function new_login_slug() {
		if( isset( $_POST['submit-cmb'] ) ) {
			$this->security_option['custom_login_url'] = sanitize_text_field( $_POST['custom_login_url'] );
		}

		if ( !empty( $this->security_option['custom_login_url'] ) ) {
			return $this->security_option['custom_login_url'];
		}

		return '';
	}

	public function new_login_url( $scheme = null ) {
		if ( get_option( 'permalink_structure' ) ) {
			return $this->user_trailingslashit( home_url( '/', $scheme ) . $this->new_login_slug() );
		} else {
			return home_url( '/', $scheme ) . '?' . $this->new_login_slug();
		}

	}

	public function plugins_loaded() {

		global $pagenow;

		if ( ! is_multisite()
		     && ( strpos( $_SERVER['REQUEST_URI'], 'wp-signup' ) !== false
		          || strpos( $_SERVER['REQUEST_URI'], 'wp-activate' ) !== false ) && apply_filters( 'custom_login_signup_enable', false ) === false ) {

			wp_die( __( 'This feature is not enabled.', EZPZ_TWEAKS_TEXTDOMAIN ) );

		}

		$request = parse_url( $_SERVER['REQUEST_URI'] );

		if ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false
		       || untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) )
		     && ! is_admin() ) {

			$this->wp_login_php = true;

			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php';

		} elseif ( untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' )
		           || ( ! get_option( 'permalink_structure' )
		                && isset( $_GET[ $this->new_login_slug() ] )
		                && empty( $_GET[ $this->new_login_slug() ] ) ) ) {

			$pagenow = 'wp-login.php';

		} elseif ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false
		             || untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) )
		           && ! is_admin() ) {

			$this->wp_login_php = true;

			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php';
		}

	}

	public function wp_loaded() {
		global $pagenow;

		$request = parse_url( $_SERVER['REQUEST_URI'] );

		if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) && $pagenow !== 'admin-post.php' && ( isset( $_GET ) && empty( $_GET['adminhash'] ) && $request['path'] !== '/wp-admin/options.php' ) ) {
			wp_safe_redirect( home_url( '/404' ) );
			die();
		}

		if ( $pagenow === 'wp-login.php'
		     && $request['path'] !== $this->user_trailingslashit( $request['path'] )
		     && get_option( 'permalink_structure' ) ) {

			wp_safe_redirect( $this->user_trailingslashit( $this->new_login_url() )
			                  . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

			die;

		} elseif ( $this->wp_login_php ) {

			if ( ( $referer = wp_get_referer() )
			     && strpos( $referer, 'wp-activate.php' ) !== false
			     && ( $referer = parse_url( $referer ) )
			     && ! empty( $referer['query'] ) ) {

				parse_str( $referer['query'], $referer );

				if ( ! empty( $referer['key'] )
				     && ( $result = wpmu_activate_signup( $referer['key'] ) )
				     && is_wp_error( $result )
				     && ( $result->get_error_code() === 'already_active'
				          || $result->get_error_code() === 'blog_taken' ) ) {

					wp_safe_redirect( $this->new_login_url()
					                  . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

					die;

				}

			}

			$this->wp_template_loader();

		} elseif ( $pagenow === 'wp-login.php' ) {
			global $error, $interim_login, $action, $user_login;

			if ( is_user_logged_in() && ! isset( $_REQUEST['action'] ) ) {
				wp_safe_redirect( admin_url() );
				die();
			}

			@require_once ABSPATH . 'wp-login.php';

			die;

		}
	}

	public function site_url( $url, $path, $scheme, $blog_id ) {

		return $this->filter_wp_login_php( $url, $scheme );

	}

	public function network_site_url( $url, $path, $scheme ) {

		return $this->filter_wp_login_php( $url, $scheme );

	}

	public function wp_redirect( $location, $status ) {

		return $this->filter_wp_login_php( $location );

	}

	public function filter_wp_login_php( $url, $scheme = null ) {

		if ( strpos( $url, 'wp-login.php' ) !== false ) {

			if ( is_ssl() ) {

				$scheme = 'https';

			}

			$args = explode( '?', $url );

			if ( isset( $args[1] ) ) {

				parse_str( $args[1], $args );

				if ( isset( $args['login'] ) ) {
					$args['login'] = rawurlencode( $args['login'] );
				}

				$url = add_query_arg( $args, $this->new_login_url( $scheme ) );

			} else {

				$url = $this->new_login_url( $scheme );

			}

		}

		return $url;

	}

	public function welcome_email( $value ) {

		return $value = str_replace( 'wp-login.php', trailingslashit( $this->security_option['custom_login_url'] ), $value );

	}

	/**
	 * Update redirect for Woocommerce email notification
	 */
	public function login_redirect_page_email_woocommerce() {

		if ( ! class_exists( 'WC_Form_Handler' ) ) {
			return false;
		}

		if ( ! empty( $_GET ) && isset( $_GET['action'] ) && 'rp' === $_GET['action'] && isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
			wp_redirect( $this->new_login_url() );
			exit();
		}
	}

	/**
	 *
	 * Update url redirect : wp-admin/options.php
	 *
	 * @param $login_url
	 * @param $redirect
	 * @param $force_reauth
	 *
	 * @return string
	 */
	public function login_url( $login_url, $redirect, $force_reauth ) {

		if ( $force_reauth === false ) {
			return $login_url;
		}

		if ( empty( $redirect ) ) {
			return $login_url;
		}

		$redirect = explode( '?', $redirect );

		if ( $redirect[0] === admin_url( 'options.php' ) ) {
			$login_url = admin_url();
		}

		return $login_url;
	}

}
