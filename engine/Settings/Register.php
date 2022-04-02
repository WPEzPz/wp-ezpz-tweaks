<?php

/**
 * EZPZ_TWEAKS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpz.dev>
 * @copyright 2022 WP EzPz
 * @license   GPL 3.0+
 * @link      https://wpezpzdev.com/
 */
namespace EZPZ_TWEAKS\Engine\Settings;

use EZPZ_TWEAKS\Backend\Settings_Page;


class Register extends Settings {
    public function init() {
        do_action( 'ezpz_register_pages' );


        self::add_tabs(
            [
                [
                    'id' => 'customizing-branding',
                    'title' => __('Customizing & Branding', EZPZ_TWEAKS_TEXTDOMAIN),
                    'icon_url' => '',
                    'callback' => '',
                    'is_cmb2'	=> true,
                    'priority' => 10,
                ],
                [
                    'id' => 'performance',
                    'title' => __('Performance', EZPZ_TWEAKS_TEXTDOMAIN),
                    'icon_url' => '',
                    'callback' => '',
                    'is_cmb2'	=> true,
                    'priority' => 20,
                ],
                [
                    'id' => 'security',
                    'title' => __('Security', EZPZ_TWEAKS_TEXTDOMAIN),
                    'icon_url' => '',
                    'callback' => '',
                    'is_cmb2'	=> true,
                    'priority' => 30,
                ],
                [
                    'id' => 'import-export',
                    'title' => __('Import & Export', EZPZ_TWEAKS_TEXTDOMAIN),
                    'icon_url' => '',
                    'callback' => '',
                    'is_cmb2'	=> false,
                    'priority' => 40,
                ],
                [
                    'id' => 'about',
                    'title' => __('About', EZPZ_TWEAKS_TEXTDOMAIN),
                    'icon_url' => '',
                    'callback' => '',
                    'is_cmb2'	=> false,
                    'priority' => 100,
                ],
            ],
			'wpezpz-tweaks'
        );
        do_action( 'ezpz_register_tabs' );

        $locale        = get_locale();
		$settings_page = new Settings_Page();
		$user_roles = ezpz_tweaks_wp_roles_array();

		self::add_fields([
			[
				'field_id' => 'custom_logo',
				'title' => __( 'Change WordPress Logo', EZPZ_TWEAKS_TEXTDOMAIN ),
				'description' => __( 'Upload an image or enter an URL.', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'type'         => 'file',
					'options'      => array(
						'url' => true, // Hide the text input for the url
					),
					'text'         => array(
						'add_upload_file_text' => __( 'Add File', EZPZ_TWEAKS_TEXTDOMAIN )
					),
					'query_args'   => array(
						'type' => array(
							'image/jpeg',
							'image/png',
						),
					),
					'preview_size' => array( 150, 150 ),
				),
				'priority' => 10,
			],
			[
				'field_id' => 'login_custom_text',
				'title' => __( 'Login Page Custom Text', EZPZ_TWEAKS_TEXTDOMAIN ),
				'description' => __( 'Add custom text to wordpress admin login page', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'type' => 'textarea',
				),
				'priority' => 20,
			],
			[
				'field_id' => 'remove_welcome_panel',
				'title' => __( 'Remove Welcome Panel', EZPZ_TWEAKS_TEXTDOMAIN ),
				'description' => __( 'The welcome panel is a meta box added to the dashboard screen of the admin area. It shows shortcuts to different sections of your WordPress website', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'type' => 'checkbox',
				),
				'priority' => 30,
			],
			[
				'field_id' => 'hide_admin_bar',
				'title' => __( 'Hide Admin Bar', EZPZ_TWEAKS_TEXTDOMAIN ),
				'description' => __( 'Hide admin bar for user roles', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'type'    => 'multicheck',
					'options' => $user_roles,
				),
				'priority' => 40,
			],
			[
				'field_id' => 'remove_dashboard_widgets',
				'title' => __( 'Remove Dashboard Widgets', EZPZ_TWEAKS_TEXTDOMAIN ),
				'description' => __( 'Check widgets to remove from dashboard', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'type'    => 'multicheck',
					'options' => $settings_page->dashboard_widgets_options(),
				),
				'priority' => 50,
			],
			[
				'field_id' => 'footer_text',
				'title' => __( 'Footer Text', EZPZ_TWEAKS_TEXTDOMAIN ),
				'description' => __( 'Change footer text', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'before_row' => '<h2 class="title">'. __( 'Admin Footer', EZPZ_TWEAKS_TEXTDOMAIN ) .'</h2>',
					'type'    => 'wysiwyg',
					'options' => array(
						'wpautop' => true, // use wpautop?
						'textarea_rows' => get_option('default_post_edit_rows', 10)
					),
				),
				'priority' => 60,
			],
			[
				'field_id' => 'footer_visibility',
				'title' => __( 'Visibility', EZPZ_TWEAKS_TEXTDOMAIN ),
				'description' => __( 'Hide the entire admin footer', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'type' => 'checkbox',
				),
				'priority' => 70,
			],
			[
				'field_id' => 'enable_branding',
				'title' => __( 'Enable Branding', EZPZ_TWEAKS_TEXTDOMAIN ),
				'description' => __( 'By selecting this option, you can add your brand to the WordPress menu and customize how it displays.', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'before_row' => '<h2 class="title">'. __( 'Brand Custom Menu', EZPZ_TWEAKS_TEXTDOMAIN ) .'</h2>',
					'type' => 'checkbox',
					'sanitization_cb'  => 'sanitize_checkbox',
					'default'          => false,
					'active_value'     => true,
					'inactive_value'   => false
				),
				'priority' => 80,
			],
			[
				'field_id' => 'menu_title',
				'title' => __( 'Menu Title', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'type'         => 'text',
					'attributes'    => array(
						'data-conditional-id'     => 'enable_branding',
						'data-conditional-value'  => 'on',
						'required' => true,
					),
				),
				'priority' => 90,
			],
			[
				'field_id' => 'menu_slug',
				'title' => __( 'Menu Slug', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'type'         => 'text',
					'attributes'    => array(
						'data-conditional-id'     => 'enable_branding',
						'data-conditional-value'  => 'on',
						'required' => true,
					),
				),
				'priority' => 100,
			],
			[
				'field_id' => 'branding_menu_logo',
				'title' => __( 'Menu Logo', EZPZ_TWEAKS_TEXTDOMAIN ),
				'description' => __( 'Upload an image or enter an URL.', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'type'         => 'file',
					'attributes'    => array(
						'data-conditional-id'     => 'enable_branding',
						'data-conditional-value'  => 'on',
						'required' => true,
					),
					'options'      => array(
						'url' => true, // Hide the text input for the url
					),
					'text'         => array(
						'add_upload_file_text' => __( 'Add File', EZPZ_TWEAKS_TEXTDOMAIN )
					),
					'query_args'   => array(
						'type' => array(
							'image/jpeg',
							'image/png',
						),
					),
					'preview_size' => array( 150, 150 ),
				),
				'priority' => 110,
			],
			[
				'field_id' => 'page_content',
				'title' => __( 'Page Content', EZPZ_TWEAKS_TEXTDOMAIN ),
				'description' => __( 'You can use HTML to design branding page', EZPZ_TWEAKS_TEXTDOMAIN ),
				'cmb2_args' => array(
					'type'    => 'wysiwyg',
					'before' => '<div data-conditional-id="enable_branding" data-conditional-value="on">',
					'after' => '</div>',
					'options' => array(
						'wpautop' => true, // use wpautop?
						'textarea_rows' => get_option('default_post_edit_rows', 10)
					),
				),
				'priority' => 120,
			],
			],
			'wpezpz-tweaks',
			'customizing-branding'
		);


		if ( $locale == 'fa_IR' ) {
			self::add_fields(
				[
					[
						'field_id' => 'admin-font-fa',
						'title' => __( 'Admin Font', EZPZ_TWEAKS_TEXTDOMAIN ),
						'description' => __( 'Change WordPress admin font', EZPZ_TWEAKS_TEXTDOMAIN ),
						'cmb2_args' => array(
							'type'             => 'select',
							'show_option_none' => false,
							'options'          => $settings_page->custom_fonts(),
						),
						'priority' => 5,
					],
					[
						'field_id' => 'editor-font-fa',
						'title' => __( 'Editor Font', EZPZ_TWEAKS_TEXTDOMAIN ),
						'description' => __( 'Change WordPress editor font', EZPZ_TWEAKS_TEXTDOMAIN ),
						'cmb2_args' => array(
							'type'             => 'select',
							'show_option_none' => false,
							'options'          => $settings_page->custom_fonts(),
						),
						'priority' => 5,
					],
				],
				'wpezpz-tweaks',
				'customizing-branding'
			);
		} else {
			self::add_fields(
				[
					[
						'field_id' => 'admin-font',
						'title' => __( 'Admin Font', EZPZ_TWEAKS_TEXTDOMAIN ),
						'description' => __( 'Change WordPress admin font', EZPZ_TWEAKS_TEXTDOMAIN ),
						'cmb2_args' => array(
							'type'       => 'text',
							'attributes' => array( 'data-placeholder' => __( 'Choose a font', EZPZ_TWEAKS_TEXTDOMAIN ),
							'data-placeholder_search' => __( 'Type to search...', EZPZ_TWEAKS_TEXTDOMAIN ) )
						),
						'priority' => 5,
					],
					[
						'field_id' => 'editor-font',
						'title' => __( 'Editor Font', EZPZ_TWEAKS_TEXTDOMAIN ),
						'description' => __( 'Change WordPress editor font', EZPZ_TWEAKS_TEXTDOMAIN ),
						'cmb2_args' => array(
							'type'       => 'text',
							'attributes' => array( 'data-placeholder' => __( 'Choose a font', EZPZ_TWEAKS_TEXTDOMAIN ),
							'data-placeholder_search' => __( 'Type to search...', EZPZ_TWEAKS_TEXTDOMAIN ) )
						),
						'priority' => 5,
					],
				],
				'wpezpz-tweaks',
				'customizing-branding'
			);
		}

        // performance fields
        self::add_fields(
            [
                [
                    'field_id' => 'disable_website_field',
                    'title' => __( 'Disable Comment Website Field', EZPZ_TWEAKS_TEXTDOMAIN ),
                    'description' => __( 'Remove the website field from the comment form', EZPZ_TWEAKS_TEXTDOMAIN ),
                    'cmb2_args' => array(
                        'type' => 'checkbox',
                    ),
                    'priority' => 10,
                ],
                [
                    'field_id' => 'disable_wp_emoji',
                    'title' => __( 'Disable Emojis', EZPZ_TWEAKS_TEXTDOMAIN ),
                    'description' => __( 'Remove wp-emoji-release.min.js file', EZPZ_TWEAKS_TEXTDOMAIN ),
                    'cmb2_args' => array(
                        'type' => 'checkbox',
                    ),
                    'priority' => 20,
                ],
                [
                    'field_id' => 'disable_wp_embed',
                    'title' => __( 'Disable Embeds', EZPZ_TWEAKS_TEXTDOMAIN ),
                    'description' => __( 'Remove wp-embed.min.js file and reduce HTTP requests', EZPZ_TWEAKS_TEXTDOMAIN ),
                    'cmb2_args' => array(
                        'type' => 'checkbox',
                    ),
                    'priority' => 30,
                ],
                [
                    'field_id' => 'remove_shortlink',
                    'title' => __( 'Remove Shortlink', EZPZ_TWEAKS_TEXTDOMAIN ),
                    'description' => __( 'Use this to create a shortlink to your pages and posts. However, if you are already using pretty permalinks, then there is no reason to keep this', EZPZ_TWEAKS_TEXTDOMAIN ),
                    'cmb2_args' => array(
                        'type' => 'checkbox',
                    ),
                    'priority' => 40,
                ],
            ],
            'wpezpz-tweaks',
            'performance'
        );

        // security fields
        self::add_fields(
			[
				[
					'field_id' => 'custom_login_url',
					'title' => __( 'Change WP Login URL', EZPZ_TWEAKS_TEXTDOMAIN ),
					'description' => __( 'Change the WordPress Login URL and prevent access to the "/wp-admin" and "/wp-login.php" and "/wp-login.php?action=register" to protect your website from hackers and strangers.', EZPZ_TWEAKS_TEXTDOMAIN ),
					'cmb2_args' => array(
						'type'         => 'text',
						'before_field' => '<span>' . trailingslashit( home_url() ) . '</span>',
					),
					'priority' => 10,
				],
				[
					'field_id' => 'disable_xmlrpc',
					'title' => __( 'Disable XML-RPC', EZPZ_TWEAKS_TEXTDOMAIN ),
					'description' => __( 'Disabling this feature makes your site more secure', EZPZ_TWEAKS_TEXTDOMAIN ),
					'cmb2_args' => array(
						'type' => 'checkbox',
					),
					'priority' => 50,
				],
				[
					'field_id' => 'remove_wp_version',
					'title' => __( 'Remove WP Version', EZPZ_TWEAKS_TEXTDOMAIN ),
					'description' => __( 'Remove the WordPress version number from different sections of your WordPress website', EZPZ_TWEAKS_TEXTDOMAIN ),
					'cmb2_args' => array(
						'type' => 'checkbox',
					),
					'priority' => 60,
				],
				[
					'field_id' => 'hide_update_notifications',
					'title' => __( 'Hide Update Notifications', EZPZ_TWEAKS_TEXTDOMAIN ),
					'description' => __( 'Hide update notifications for user roles', EZPZ_TWEAKS_TEXTDOMAIN ),
					'cmb2_args' => array(
						'type'    => 'multicheck',
						'options' => $user_roles,
					),
					'priority' => 60,
				],
			],
			'wpezpz-tweaks',
			'security'
		);
		if ( current_user_can( 'administrator' ) ) {
			self::add_fields([
				[
					'field_id' => 'plugin_access',
					'title' => __( 'Who can access this plugin', EZPZ_TWEAKS_TEXTDOMAIN ),
					'cmb2_args' => array(
						'type' 		=> 'radio',
						'options'   => array(
							'super_admin'   	  => __( 'Super Admin', EZPZ_TWEAKS_TEXTDOMAIN ),
							'manage_options'	  => __( 'Anyone with the "mange_options" capability', EZPZ_TWEAKS_TEXTDOMAIN ),
							get_current_user_id() => __( 'Only the current user', EZPZ_TWEAKS_TEXTDOMAIN ),
						),
						'default' 	=> 'manage_options',
					),
					'priority' => 10,
				],
				[

					'field_id' => 'disable_rest_api',
					'title' => __( 'Disable Public Access to WP REST API', EZPZ_TWEAKS_TEXTDOMAIN ),
					'description' => __( 'API consumers be authenticated, which effectively prevents anonymous external access.', EZPZ_TWEAKS_TEXTDOMAIN ),
					'cmb2_args' => array(
						'type' 		=> 'checkbox',
					),
					'priority' => 10,
				],
			],
			'wpezpz-tweaks',
			'security'
		);
		}

        // import-export fields
        self::add_section(
            'wpezpz-tweaks',
            'import-export',
            'export_settings',
            __( 'Export Settings', EZPZ_TWEAKS_TEXTDOMAIN ),
            10
        );

        self::add_field(
            'wpezpz-tweaks',
            'import-export',
            'export_settings',
            'export_settings',
            __( 'Export Settings', EZPZ_TWEAKS_TEXTDOMAIN ),
            __( 'Export the plugin\'s settings for this site as a .json file. This will allows you to easily import the configuration to another installation.', EZPZ_TWEAKS_TEXTDOMAIN ),
            function () {
                ?>
                <div class="postbox">
                    <h3 class="hndle"><span><?php _e( 'Export Settings', EZPZ_TWEAKS_TEXTDOMAIN ); ?></span></h3>
                    <div class="inside">
                        <p><?php _e( 'Export the plugin\'s settings for this site as a .json file. This will allows you to easily import the configuration to another installation.', EZPZ_TWEAKS_TEXTDOMAIN ); ?></p>
                        <form method="post">
                            <p><input type="hidden" name="w_action" value="export_settings"/></p>
                            <p>
                                <?php wp_nonce_field( 'w_export_nonce', 'w_export_nonce' ); ?>
                                <?php submit_button( __( 'Export', EZPZ_TWEAKS_TEXTDOMAIN ), 'secondary', 'submit', false ); ?>
                            </p>
                        </form>
                    </div>
                </div>
            <?php
            },
            true,
            10
        );

        self::add_field(
            'wpezpz-tweaks',
            'import-export',
            'import_settings',
            'import_settings',
            __( 'Import Settings', EZPZ_TWEAKS_TEXTDOMAIN ),
            __( 'Import the plugin\'s settings from a .json file. This file can be retrieved by exporting the settings from another installation.', EZPZ_TWEAKS_TEXTDOMAIN ),
            function () {
                ?>
                <div class="postbox">
                    <h3 class="hndle"><span><?php _e( 'Import Settings', EZPZ_TWEAKS_TEXTDOMAIN ); ?></span></h3>
                    <div class="inside">
                        <p><?php _e( 'Import the plugin\'s settings from a .json file. This file can be retrieved by exporting the settings from another installation.', EZPZ_TWEAKS_TEXTDOMAIN ); ?></p>
                        <form method="post" enctype="multipart/form-data">
                            <p>
                                <input type="file" name="w_import_file"/>
                            </p>
                            <p>
                                <input type="hidden" name="w_action" value="import_settings"/>
                                <?php wp_nonce_field( 'w_import_nonce', 'w_import_nonce' ); ?>
                                <?php submit_button( __( 'Import', EZPZ_TWEAKS_TEXTDOMAIN ), 'secondary', 'submit', false ); ?>
                            </p>
                        </form>
                    </div>
                </div>
            <?php
            },
            true,
            20
        );

        // about fields
        self::add_field(
			'wpezpz-tweaks',
			'about',
			'about',
			'about',
			__( 'About', EZPZ_TWEAKS_TEXTDOMAIN ),
			__( "EzPz Tweaks is an all-in-one WordPress plugin that helps you personalize the admin panel appearances, clean your site code and remove unwanted features to increase its security and improve performance.\nLearn more at <a href='https://wpezpzdev.com/' target='_blank'>WPEzPzdev.com</a>", EZPZ_TWEAKS_TEXTDOMAIN ),
			function () {
				echo nl2br( __( "EzPz Tweaks is an all-in-one WordPress plugin that helps you personalize the admin panel appearances, clean your site code and remove unwanted features to increase its security and improve performance.\nLearn more at <a href='https://wpezpzdev.com/' target='_blank'>WPEzPzdev.com</a>", EZPZ_TWEAKS_TEXTDOMAIN ) );
			},
			true,
			10
		);

        do_action( 'ezpz_register_fields' );
    }
}
