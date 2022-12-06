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

use EZPZ_TWEAKS\Engine\MenuEditor\Admin_Bar_Edit;
use EZPZ_TWEAKS\Engine\Font\Font;
/**
 * Create the settings page in the backend
 */
class Settings_Page {

	/**
	 * @var false|mixed|void
	 */
	public $get_locale;
	public $customizing_option;
	public $performance_option;
	public $security_option;

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() {

		$font = new Font();

		add_action( 'admin_enqueue_scripts', array( $font, 'change_admin_font' ), 30 );
		add_action( 'admin_enqueue_scripts', array( $font, 'change_editor_font' ), 30 );
		add_action( 'admin_enqueue_scripts', array( $font, 'render_fonts_css' ), 30 );
        add_action( 'wp_enqueue_scripts', array( $font, 'render_fonts_css' ), 30 );
        add_action( 'login_head', array( $font, 'wp_change_login_font' ), 999 );

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_head', array( $this, 'hide_core_update_notifications_from_users' ), 1 );
		add_action( 'admin_init', array( $this, 'remove_welcome_panel' ) );
		add_action( 'admin_init', array( $this, 'dashboard_widgets_options' ) );
		add_action( 'admin_init', array( $this, 'disable_block_editor' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
		add_action( "cmb2_save_options-page_fields", array( $this, 'show_notices_on_custom_url_change' ), 30, 3 );
		add_action( "admin_notices", array( $this, 'show_notices_on_performance_change' ), 30 );
		add_action( "admin_footer_text", array( $this, 'custom_footer' ), 30, 1 );
		add_action( 'init', array( $this, 'deactivate_file_editor' ), 1 );


		add_filter( 'upload_mimes', array( $this, 'allowed_wp_upload_mimes' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'maybe_update_mime_types' ), 10, 4 );
		add_action( 'pre_user_query',array( $this, 'ezpz_pre_user_query') );
		add_action( 'views_users',array( $this, 'reset_count_of_visible_users') );

		add_filter( 'plugin_action_links_' . EZPZ_TWEAKS_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );

		add_filter( 'admin_body_class', array($this, 'maybe_add_body_class') );
		add_filter( 'admin_enqueue_scripts', array($this, 'maybe_enqueue_nav_menu_editor_scripts') );

		new Admin_Bar_Edit();
		
	}

	// get options data and set to variables
	public function __construct() {
		$this->get_locale         = get_locale();
		$this->customizing_option = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding' );
		$this->performance_option = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-performance' );
		$this->security_option 	  = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-security' );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_plugin_admin_menu() {
		/*
		 * Add a settings page for this plugin to the main menu
		 *
		 */

		$plugin_access = isset( $this->security_option['plugin_access'] ) ? $this->security_option['plugin_access'] : 'manage_options';
		$capability	   = $plugin_access == 'super_admin' ? 'delete_users' : 'manage_options';

		if( $plugin_access == 'super_admin' || $plugin_access == 'manage_options' || $plugin_access == get_current_user_id() ) {
			$ezpz_menu = menu_page_url( 'wp-ezpz', false );

			if ( !$ezpz_menu ) {
				add_menu_page(
					__( 'WP EzPz', EZPZ_TWEAKS_TEXTDOMAIN ),
					__( 'WP EzPz', EZPZ_TWEAKS_TEXTDOMAIN ),
					$capability, EZPZ_TWEAKS_TEXTDOMAIN,
					[ $this, 'display_plugin_settings_page' ],
					EZPZ_TWEAKS_PLUGIN_ROOT_URL . 'assets/img/EzPzTweaks-icon.png' );

				add_submenu_page(
					EZPZ_TWEAKS_TEXTDOMAIN,
					__( 'Tweaks', EZPZ_TWEAKS_TEXTDOMAIN ),
					__( 'Tweaks', EZPZ_TWEAKS_TEXTDOMAIN ),
					$capability,
					EZPZ_TWEAKS_TEXTDOMAIN,
					[ $this, 'display_plugin_settings_page' ]
				);

				add_submenu_page(
						EZPZ_TWEAKS_TEXTDOMAIN,
						__( 'Edit Admin Bar', EZPZ_TWEAKS_TEXTDOMAIN ),
						__( 'Edit Admin Bar', EZPZ_TWEAKS_TEXTDOMAIN ),
						$capability,
						EZPZ_TWEAKS_TEXTDOMAIN . '-edit-admin-bar',
						[ $this, 'display_plugin_admin_bar_edit_page' ]
				);
			}

			add_submenu_page( EZPZ_TWEAKS_TEXTDOMAIN, __( 'Edit Menu', EZPZ_TWEAKS_TEXTDOMAIN ), __( 'Edit Menu', EZPZ_TWEAKS_TEXTDOMAIN ), $capability, EZPZ_TWEAKS_TEXTDOMAIN . '-edit-menu', '' );
		}

		if( ( isset( $this->customizing_option['enable_branding'] ) && !isset( $_POST['object_id'] ) && !isset( $_POST['enable_branding'] ) ) || ( isset( $_POST['enable_branding'] ) && !empty( $_POST['enable_branding'] ) ) ) {
			$menu_title 		= isset( $_POST['menu_title'] ) ? sanitize_text_field( $_POST['menu_title'] ) : $this->customizing_option['menu_title'];
			$menu_slug 			= isset( $_POST['menu_slug'] ) ? sanitize_text_field( $_POST['menu_slug'] ) : $this->customizing_option['menu_slug'];
			$branding_menu_logo = isset( $_POST['branding_menu_logo'] ) ? sanitize_text_field( $_POST['branding_menu_logo'] ) : $this->customizing_option['branding_menu_logo'];

			add_menu_page(
				$menu_title,
				$menu_title,
				'manage_options',
				$menu_slug,
				[ $this, 'display_branding_page' ],
				$branding_menu_logo, 79 );

			add_action( 'admin_enqueue_scripts', function() use( $menu_slug ) {
				wp_add_inline_style( EZPZ_TWEAKS_TEXTDOMAIN . '-admin-styles', '#toplevel_page_' . $menu_slug . ' img { width: 16px !important; }' );
			}, 30 );
		}
	}

	/**
	 * Render the branding page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function display_branding_page() {
		if( isset( $this->customizing_option['page_content'] ) ) {
			echo $this->customizing_option['page_content'];
		} else {
			?>
			<div class="notice notice-info is-dismissible">
				<p>
					<?php _e( 'Edit this page from: Settings > EzPz Tweaks > Branding tab > Page Content.', EZPZ_TWEAKS_TEXTDOMAIN ) ?>
					<br>
					<a target="_blank" href="<?php echo admin_url( '/admin.php?page=ezpz-tweaks&tab=customizing-branding' ) ?>">
						<?php _e( 'Edit Page', EZPZ_TWEAKS_TEXTDOMAIN ) ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function display_plugin_settings_page() {
		include EZPZ_TWEAKS_PLUGIN_ROOT . "backend/views/settings.php";
	}

	public function maybe_enqueue_nav_menu_editor_scripts() {
		$page = !empty($_GET['page']) ? sanitize_text_field($_GET['page']) : '' ;
		if ($page == EZPZ_TWEAKS_TEXTDOMAIN . '-edit-admin-bar') {
			wp_enqueue_script( 'nav-menu');
			wp_enqueue_style( 'nav-menus');
			wp_enqueue_style( 'wp-color-picker');
			wp_enqueue_style( 'wp-codemirror');

			wp_enqueue_script( EZPZ_TWEAKS_TEXTDOMAIN . '-admin-bar', plugins_url( 'assets/js/admin_bar_editor.js', EZPZ_TWEAKS_PLUGIN_ABSOLUTE ), array( 'jquery' ), EZPZ_TWEAKS_VERSION, false );

		}
	}

	public function display_plugin_admin_bar_edit_page() {

		include EZPZ_TWEAKS_PLUGIN_ROOT . "backend/views/edit_admin_bar.php";
	}

	public function maybe_add_body_class($classes): string
	{
		$new_classes = '';
		$page = !empty($_GET['page']) ? sanitize_text_field($_GET['page']) : '' ;
		if ($page == EZPZ_TWEAKS_TEXTDOMAIN . '-edit-admin-bar') {
			$new_classes .= 'nav-menus-php';
		}

		return "$classes $new_classes";
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @param array $links Array of links.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function add_action_links( array $links ) {
		return array_merge( array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=' . EZPZ_TWEAKS_TEXTDOMAIN ) . '">' . __( 'Settings', EZPZ_TWEAKS_TEXTDOMAIN ) . '</a>',
		), $links );
	}

	public function custom_footer( $text ) {

		// earlly exit if footer text not on the settings page
		if( !isset( $this->customizing_option['footer_visibility'] ) && !isset( $_POST['footer_text'] ) ) {
			return $text;
		}

		if( isset( $_POST['submit-cmb'] ) ) {
			$this->customizing_option['footer_visibility'] = sanitize_text_field( $_POST['footer_visibility'] );
		}

		if( isset( $this->customizing_option['footer_visibility'] ) && $this->customizing_option['footer_visibility'] == 'on' ) {
			return;
		} else {
			if ( ( isset( $this->customizing_option['footer_text'] ) && !isset( $_POST['footer_text'] ) ) || ( isset( $_POST['footer_text'] ) && !empty( $_POST['footer_text'] ) ) ) {
				$footer_text = isset( $_POST['footer_text'] ) ? sanitize_text_field( $_POST['footer_text'] ) : $this->customizing_option['footer_text'];
				return wp_kses_post( $footer_text );
			} else {
				return $text;
			}
		}
	}

	public function deactivate_file_editor() {
		if ( (isset($_POST['deactivate_file_editor']) && sanitize_text_field($_POST['deactivate_file_editor']) == 'on') || !isset($_POST['deactivate_file_editor']) && isset($_POST['object_id']) && sanitize_text_field($_POST['object_id']) != 'wpezpz-tweaks-security' && isset($this->security_option['deactivate_file_editor']) && $this->security_option['deactivate_file_editor'] == 'on' ) {
			define( 'DISALLOW_FILE_EDIT', true );
		} else {
			define( 'DISALLOW_FILE_EDIT', false );
		}
	}

	public function remove_google_fonts() {
		// Unload Open Sans
		wp_deregister_style( 'open-sans' );
		wp_register_style( 'open-sans', false );

	}

	public function hide_core_update_notifications_from_users() {
		if ( isset( $this->security_option['hide_update_notifications'] ) ) {
			$user_roles = $this->security_option['hide_update_notifications'];
			$user = wp_get_current_user();

			foreach ( $user_roles as $role ) {
				if ( in_array( $role, (array) $user->roles ) ) {
					remove_action( 'admin_notices', 'update_nag', 3 );
					break;
				}
			}
		}
	}

	public function remove_welcome_panel() {
		if ( isset( $this->customizing_option['remove_welcome_panel'] ) ) {
			remove_action( 'welcome_panel', 'wp_welcome_panel' );
		}
	}

	function get_dashboard_widgets() {
		global $wp_meta_boxes;

		$widgets = array();

		if ( isset( $wp_meta_boxes['dashboard'] ) ) {
			foreach( $wp_meta_boxes['dashboard'] as $context => $data ) {
				foreach( $data as $priority => $data ) {
					foreach( $data as $widget=>$data ) {
						$widgets[$widget] = [
							'id' 	   => $widget,
							'title'    => strip_tags( preg_replace( '/ <span.*span>/im', '', $data['title'] ) ),
							'context'  => $context,
							'priority' => $priority
						];
					}
				}
			}
		}

		return $widgets;
	}

	function dashboard_widgets_options() {
		$widgets = get_option('ezpz_tweaks_dashboard_widgets');
		$options = [];

		if( $widgets ) {
			foreach( $widgets as $widget ) {
				$options[$widget['id']] = $widget['title'];
			}
		}

		return $options;
	}

	function remove_dashboard_widgets() {
		$widgets = $this->get_dashboard_widgets();

		update_option('ezpz_tweaks_dashboard_widgets', $widgets);

		if ( isset( $this->customizing_option['remove_dashboard_widgets'] ) ) {
			global $wp_meta_boxes;

			$selected_widgets = $this->customizing_option['remove_dashboard_widgets'];

			foreach ( $widgets as $widget ) {
				if( in_array( $widget['id'], $selected_widgets ) ) {
					unset( $wp_meta_boxes['dashboard'][$widget['context']][$widget['priority']][$widget['id']] );
				}
			}
		}
	}

	public function show_notices_on_custom_url_change( $object_id, $updated, $cmb ) {
		if( in_array( 'custom_login_url', $cmb ) ) {
			$hide_login = new \EZPZ_TWEAKS\Integrations\Custom_Login_Url();

			echo '<div class="updated notice is-dismissible"><p>' . sprintf( __( 'Your login page is now here: <strong><a href="%1$s">%2$s</a></strong>. Bookmark this page!', EZPZ_TWEAKS_TEXTDOMAIN ), $hide_login->new_login_url(), $hide_login->new_login_url() ) . '</p></div>';
		}
	}

	public function disable_block_editor() {
		if (!is_admin() && !current_user_can('administrator')) {
			return;
		}

		$action = isset($_GET['action']) ? sanitize_key( $_GET['action'] ) : '';
		$plugin_name = isset($_GET['plugin']) ? sanitize_text_field( $_GET['plugin'] ) : '';

		if ( $action !== 'activate' || empty($plugin_name) ) {
			return;
		}
		$plugin_list = get_option( 'active_plugins' );

		if ( $plugin_name === 'block-editor' ) {
			if (file_exists( WP_PLUGIN_DIR . '/tinymce-advanced/tinymce-advanced.php' )) {
				deactivate_plugins( WP_PLUGIN_DIR . '/tinymce-advanced/tinymce-advanced.php' );
			}
			if (file_exists( WP_PLUGIN_DIR . '/classic-editor/classic-editor.php' )) {
				deactivate_plugins( WP_PLUGIN_DIR . '/classic-editor/classic-editor.php' );
			}
			return;
		} else if ( $plugin_name === 'classic-editor' ) {
			if (file_exists( WP_PLUGIN_DIR . '/tinymce-advanced/tinymce-advanced.php' )) {
				deactivate_plugins( WP_PLUGIN_DIR . '/tinymce-advanced/tinymce-advanced.php' );
			}
			if (!in_array( 'classic-editor/classic-editor.php' , $plugin_list )) {
				if (file_exists( WP_PLUGIN_DIR . '/classic-editor/classic-editor.php' )) {
					$url = $options['classic']['install'] = wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'activate',
								'plugin' => 'classic-editor/classic-editor.php',
								'plugin_status' => 'all',
								'paged' => '1',
							),
							admin_url( 'plugins.php' )
						),
						'activate-plugin' .'_'.'classic-editor/classic-editor.php'
					);
				} else {
					$url = wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'install-plugin',
								'plugin' => 'classic-editor'
							),
							admin_url( 'update.php' )
						),
						'install-plugin' .'_'. 'classic-editor'
					);
				}
				wp_safe_redirect( html_entity_decode($url) );
				exit;

			}


			return;
		}


		return;
  }

	public function allowed_wp_upload_mimes( $mimes ) {
		$mimes['woff']  = 'application/x-font-woff';
		$mimes['woff2'] = 'application/x-font-woff2';
		$mimes['ttf']   = 'application/x-font-ttf';

		return $mimes;
	}

	public function maybe_update_mime_types( $data, $file, $filename, $mimes ) {
		$filetype = wp_check_filetype( $filename, $mimes );
  
		return [
			'ext'             => $filetype['ext'],
			'type'            => $filetype['type'],
			'proper_filename' => $data['proper_filename']
		];
	}
	public function show_notices_on_performance_change( ) {
		if( isset($_POST['disable_wp_emoji']) ) {
			echo '<div class="updated notice is-dismissible"><p>' .  __( 'Your performance settings saved.', EZPZ_TWEAKS_TEXTDOMAIN ) . '</p></div>';
		}

	}
	function ezpz_pre_user_query($user_search) {
		global $pagenow;
		if (!isset($this->security_option['hide_user_in_admin']) || empty($this->security_option['hide_user_in_admin'])) {
			return $user_search;
		}
		$userids = $this->security_option['hide_user_in_admin'];

		// if current user is hidden, do nothing
		$current_user_id = get_current_user_id();
		if (in_array($current_user_id, $userids)) {
			return $user_search;
		}

		if ( !empty($userids) && !(( $pagenow == 'admin.php' ) && ( sanitize_text_field($_GET['page']) == 'wpezpz-tweaks'))) {
			global $wpdb;
			$query = '';

			foreach ($userids as $userid) {
				$query .= " AND {$wpdb->users}.ID != '$userid'";
			}

			$user_search->query_where = str_replace('WHERE 1=1',
			"WHERE 1=1$query",
			$user_search->query_where);
		}
	}

	function reset_count_of_visible_users($views){
		if (!isset($this->security_option['hide_user_in_admin']) || empty($this->security_option['hide_user_in_admin'])) {
			return $views;
		}
		$hidden_userids = $this->security_option['hide_user_in_admin'];
		$hidden_userids_count = count($hidden_userids);

		// if current user is hidden, do nothing
		$current_user_id = get_current_user_id();
		if (in_array($current_user_id, $hidden_userids)) {
			return $views;
		}

		if ( $hidden_userids_count > 0 ) {
			
			$users = count_users();
			$avail_roles = array_keys($users['avail_roles']);
			
			foreach ($hidden_userids as $hidden_userid) {
				$hidden_user_roles = implode(', ', \get_userdata( $hidden_userid )->roles);

				foreach ($avail_roles as $role) {
					if ( $hidden_user_roles == $role ) {
						$users['avail_roles'][$role]--;
					}
				}
			}

			
			foreach($views as $key => $view) {
				if ($key == 'all') {
					$count = $users['total_users'] - $hidden_userids_count;
				} else {
					$count = $users['avail_roles'][$key];
				}

				if ($count == 0) {
					unset($views[$key]);
					break;
				}
				$views[$key] = preg_replace('/\d+/', $count, $views[$key]);
			}
		}

		return $views;
	}

}
