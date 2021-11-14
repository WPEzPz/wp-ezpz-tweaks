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

/**
 * Manage the WordPress admin menu using a simple drag & drop interface.
 */
class Admin_Menu_Editor {

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() {
		
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 30 );

		// Handle form submissions
		add_action( 'wp_ajax_ezpz_tweaks_update_menu', array( $this, 'update_menu' ) );

		// Modify admin menu
		add_action( 'admin_menu', array( $this, 'alter_admin_menu' ), 999 );

		// Tell WordPress we're changing the menu order
		add_filter( 'custom_menu_order', '__return_true' );

		// Add our filter way later, after other plugins have defined the menu
		add_filter( 'menu_order', array( $this, 'alter_admin_menu_order' ), 9999 );
	}

	/**
	 * Load our JavaScript and CSS if the user has enough capabilities to edit the menu.
	 */
	public function admin_enqueue_scripts() {
		global $_wp_admin_css_colors;

		$current_color = get_user_option( 'admin_color' );
		if ( isset( $_wp_admin_css_colors[ $current_color ] ) ) {
			$border     = $_wp_admin_css_colors[ $current_color ]->icon_colors['base'];
			$background = $_wp_admin_css_colors[ $current_color ]->colors[0];
			$base       = $_wp_admin_css_colors[ $current_color ]->icon_colors['base'];
			$focus      = $_wp_admin_css_colors[ $current_color ]->icon_colors['focus'];
			$current    = $_wp_admin_css_colors[ $current_color ]->icon_colors['current'];
			$inline_css = "
			#adminmenu:not(.ui-sortable-disabled) .wp-menu-separator.ui-sortable-handle { background-color: $background; border-color: $border !important; }
			#admin-menu-editor-edit .menu-top { color: $base; }
			#admin-menu-editor-edit .menu-top:focus,
			#admin-menu-editor-edit .menu-top:focus div.wp-menu-image:before { color: $focus !important; }
			#admin-menu-editor-edit:hover .menu-top,
			#admin-menu-editor-edit:hover div.wp-menu-image:before { color: $current !important; }
			";
			wp_add_inline_style( EZPZ_TWEAKS_TEXTDOMAIN . '-admin-styles', $inline_css );
		}

		wp_localize_script( EZPZ_TWEAKS_TEXTDOMAIN . '-admin-script', 'AdminMenuManager', array(
			'buttonEdit'   => __( 'Edit Menu', EZPZ_TWEAKS_TEXTDOMAIN ),
			'buttonSave'   => __( 'Save', EZPZ_TWEAKS_TEXTDOMAIN ),
			'buttonSaving' => __( 'Saving&hellip;', EZPZ_TWEAKS_TEXTDOMAIN ),
			'buttonSaved'  => __( 'Saved!', EZPZ_TWEAKS_TEXTDOMAIN ),
			'adminMenu'    => self::get_admin_menu(),
		) );
	}

	/**
	 * Grab a list of all registered admin pages.
	 *
	 * @since 1.0.0
	 */
	public function get_admin_menu() {
		global $menu, $submenu;

		if ( null === $menu ) {
			$menu = array();
		}

		$menu_items = array();

		foreach ( $menu as $menu_item ) {
			if ( ! empty( $submenu[ $menu_item[2] ] ) ) {
				foreach ( $submenu[ $menu_item[2] ] as $key => &$value ) {
					if ( '' === $key && '' === $value[0] ) {
						unset( $submenu[ $menu_item[2] ][ $key ] );
						continue;
					}
					$value[] = $key;
				}
				$menu_item[] = array_values( $submenu[ $menu_item[2] ] );
			} else {
				$menu_item[] = array();
			}

			$menu_items[] = $menu_item;
		}

		return $menu_items;
	}

	/**
	 * Ajax Handler to update the menu.
	 *
	 * The passed array is splitted up in a menu and submenu array,
	 * just like WordPress uses it in the backend.
	 */
	public function update_menu() {
		$menu    = ezpz_tweaks_recursive_sanitize( $_REQUEST['adminMenu'] );
		$items   = array();
		$submenu = array();

		$separatorIndex = 1;
		$lastSeparator  = null;

		foreach ( $menu as $index => $item ) {
			$item[0] = wp_unslash( $item[0] );

			if ( isset( $item[7] ) ) {
				$submenu[ $item[2] ] = array();
				foreach ( $item[7] as $subitem ) {
					$subitem[0]            = wp_unslash( $subitem[0] );
					$subitem               = array_slice( $subitem, 0, 4 );
					$submenu[ $item[2] ][] = $subitem;
				}
				unset( $item[7] );
			}

			// Store separators in correct order
			if ( false !== strpos( $item[2], 'separator' ) ) {
				$item[2]       = 'separator' . $separatorIndex ++;
				$item[4]       = 'wp-menu-separator';
				$lastSeparator = count( $items );
			}

			$items[] = $item;
		}

		$items[ $lastSeparator ][2] = 'separator-last';

		// Note: The third autoload parameter was introduced in WordPress 4.2.0
		update_option( 'ezpz_tweaks_menu', $items, false );
		update_option( 'ezpz_tweaks_submenu', $submenu, false );

		die( 1 );
	}

	public function disable_translations( $translated_text, $text, $domain ) {
		return $text;
	}

	/**
	 * Here's where the magic happens!
	 *
	 * Compare our menu structure with the original.
	 * Essentially it uses the new order but with the original values,
	 * so translated strings and icons still work.
	 *
	 * 0 = menu_title, 1 = capability, 2 = menu_slug, 3 = page_title, 4 = classes
	 */
	public function alter_admin_menu() {
		$ezpz_tweaks_menu    = get_option( 'ezpz_tweaks_menu', array() );
		$ezpz_tweaks_submenu = get_option( 'ezpz_tweaks_submenu', array() );

		if ( empty( $ezpz_tweaks_menu ) || empty( $ezpz_tweaks_submenu ) ) {
			return;
		}

		global $menu, $submenu, $wp_filter, $admin_page_hooks;

		$temp_menu             = $menu;
		$temp_submenu          = $submenu;
		$temp_admin_page_hooks = $admin_page_hooks;

		$menu    = null;
		$submenu = null;

		// Iterate on the top level items
		foreach ( $ezpz_tweaks_menu as $priority => &$item ) {
			// It was originally a top level item as well. It's a match!
			foreach ( $temp_menu as $key => $m_item ) {
				if ( $item[2] === $m_item[2] ) {
					if ( 'wp-menu-separator' == $m_item[4] ) {
						$menu[ $priority ] = $m_item;
					} else {
						add_menu_page(
							$m_item[3], // Page title
							$m_item[0], // Menu title
							$m_item[1], // Capability
							$m_item[2], // Slug
							'', // Function
							$m_item[6], // Icon
							$priority // Position
						);
					}

					unset( $temp_menu[ $key ] );
					continue 2;
				}
			}

			// It must be a submenu item moved to the top level
			foreach ( $temp_submenu as $key => &$parent ) {
				foreach ( $parent as $sub_key => &$sub_item ) {
					if ( $item[2] === $sub_item[2] ) {
						$hook_name = get_plugin_page_hookname( $sub_item[2], $key );

						$old_filters = array();

						foreach ( $wp_filter as $filter => $value ) {
							if ( false !== strpos( $filter, $hook_name ) ) {
								$old_filters[ $filter ] = $value;
								unset( $wp_filter[ $filter ] );
							}
						}

						if ( ! isset( $sub_item[3] ) ) {
							$sub_item[3] = $sub_item[0];
						}

						$new_page = add_menu_page(
							$sub_item[3], // Page title
							$sub_item[0], // Menu title
							$sub_item[1], // Capability
							$sub_item[2], // Slug
							'', // Function
							$item[6], // Icon
							$priority // Position
						);

						// Add hook name of the former parent as CSS class to the new item
						$menu[ $priority ][4] .= ' ' . get_plugin_page_hookname( $key, $key );

						foreach ( $old_filters as $filter => $value ) {
							$wp_filter[ str_replace( $hook_name, $new_page, $filter ) ] = $value;
						}

						unset( $temp_submenu[ $key ][ $sub_key ] );

						continue 3;
					}
				}
			}

			// Still no match, menu item must have been removed.
			unset( $temp_menu[ $priority ] );
		}

		// Iterate on all our submenu items
		foreach ( $ezpz_tweaks_submenu as $parent_page => &$page ) {
			foreach ( $page as $priority => &$item ) {
				// Iterate on original submenu items
				foreach ( $temp_submenu as $s_parent_page => &$s_page ) {
					foreach ( $s_page as $s_priority => &$s_item ) {
						if ( $item[2] === $s_item[2] && $parent_page == $s_parent_page ) {
							$new_page = add_submenu_page(
								$s_parent_page, // Parent Slug
								isset( $s_item[3] ) ? $s_item[3] : $s_item[0], // Page title
								$s_item[0], // Menu title
								$s_item[1], // Capability
								$s_item[2] // SLug
							);

							unset( $temp_submenu[ $s_parent_page ][ $s_priority ] );

							continue 2;
						}
					}
				}

				// It must be a top level item moved to submenu
				foreach ( $temp_menu as $m_key => &$m_item ) {
					if ( $item[2] === $m_item[2] ) {
						$hook_name = get_plugin_page_hookname( $m_item[2], $parent_page );

						$old_filters = array();

						foreach ( $wp_filter as $filter => $value ) {
							if ( false !== strpos( $filter, $hook_name ) ) {
								$old_filters[ $filter ] = $value;
								unset( $wp_filter[ $filter ] );
							}
						}

						$new_page = add_submenu_page(
							$parent_page, // Parent Slug
							$m_item[0], // Page title
							$m_item[0], // Menu title
							$m_item[1], // Capability
							$m_item[2] // Slug
						);

						foreach ( $old_filters as $filter => $value ) {
							$wp_filter[ str_replace( $hook_name, $new_page, $filter ) ] = $value;
						}

						unset( $temp_menu[ $m_key ] );

						continue 2;
					}
				}

				// Still no match, menu item must have been removed.
			}
		}

		/**
		 * Append elements that haven't been added to a menu yet.
		 *
		 * This happens when installing a new plugin for example.
		 */
		$menu = array_merge( $menu, $temp_menu );

		foreach ( $temp_submenu as $parent => $item ) {
			if ( '' === $parent || empty( $item ) || ! is_array( $item ) ) {
				continue;
			}

			if ( isset( $submenu[ $parent ] ) ) {
				$submenu[ $parent ] = array_merge( $submenu[ $parent ], $item );
			} else {
				$submenu[ $parent ] = $item;
			}
		}

		/**
		 * Loop through admin page hooks.
		 *
		 * We want to keep the original, untranslated values.
		 */
		foreach ( $admin_page_hooks as $key => &$value ) {
			if ( isset( $temp_admin_page_hooks[ $key ] ) ) {
				$value = $temp_admin_page_hooks[ $key ];
			}
		}
	}

	/**
	 * Make sure our menu order is kept.
	 *
	 * Some plugins (I'm looking at you, Jetpack!) want to always be on top,
	 * let's fix this.
	 *
	 * @param array $menu_order WordPress admin menu order.
	 *
	 * @return array
	 */
	public function alter_admin_menu_order( $menu_order ) {
		global $menu;

		if ( ! get_option( 'ezpz_tweaks_menu', false ) ) {
			return $menu_order;
		}

		$new_order = array();
		foreach ( $menu as $item ) {
			$new_order[] = $item[2];
		}

		return $new_order;
	}
}
