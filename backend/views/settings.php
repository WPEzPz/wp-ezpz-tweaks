<?php $fragment = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'customizing-branding'; ?>

<div class="wrap ezpz-tweaks-tabs">
	<h2><img src="<?php echo EZPZ_TWEAKS_PLUGIN_ROOT_URL . 'assets/img/EzPzTweaks-logo.svg' ?>" style="width: 50px;vertical-align: middle;padding: 15px;"><?php echo EZPZ_TWEAKS_NAME ?></h2>

	<!-- Start tabs -->
	<ul class="wp-tab-bar">
		<li class="<?php echo $fragment == 'customizing-branding' ? 'wp-tab-active' : '' ?>"><a href="#customizing-branding"><?php _e( 'Customizing & Branding', EZPZ_TWEAKS_TEXTDOMAIN ) ?></a></li>
		<li class="<?php echo $fragment == 'performance' ? 'wp-tab-active' : '' ?>"><a href="#performance"><?php _e( 'Performance', EZPZ_TWEAKS_TEXTDOMAIN ) ?></a></li>
		<li class="<?php echo $fragment == 'security' ? 'wp-tab-active' : '' ?>"><a href="#security"><?php _e( 'Security', EZPZ_TWEAKS_TEXTDOMAIN ) ?></a></li>
		<li class="<?php echo $fragment == 'import-export' ? 'wp-tab-active' : '' ?>"><a href="#import-export"><?php _e( 'Import & Export', EZPZ_TWEAKS_TEXTDOMAIN ) ?></a></li>
		<li class="<?php echo $fragment == 'about' ? 'wp-tab-active' : '' ?>"><a href="#about"><?php _e( 'About', EZPZ_TWEAKS_TEXTDOMAIN ) ?></a></li>
	</ul>
	<div id="customizing-branding" class="wp-tab-panel" style="<?php echo $fragment != 'customizing-branding' ? 'display: none' : '' ?>">
		<?php
		$locale        = get_locale();
		$settings_page = new EZPZ_TWEAKS\Backend\Settings_Page();
		$cmb           = new_cmb2_box(
			array(
				'id'           => EZPZ_TWEAKS_TEXTDOMAIN . '_options_customizing_branding',
				'object_types' => array( 'options-page' ),
				'hookup'       => true,
				'show_names'   => true,
				'show_on'      => array(
					'key'      => 'options-page',
					'value'    => array( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding' )
				),
			)
		);

		if ( $locale == 'fa_IR' ) {
			$cmb->add_field(
				array(
					'name'             => __( 'Admin Font', EZPZ_TWEAKS_TEXTDOMAIN ),
					'desc'             => __( 'Change WordPress admin font', EZPZ_TWEAKS_TEXTDOMAIN ),
					'id'               => 'admin-font-fa',
					'type'             => 'select',
					'show_option_none' => false,
					'options'          => $settings_page->custom_fonts(),
				)
			);

			$cmb->add_field(
				array(
					'name'             => __( 'Editor Font', EZPZ_TWEAKS_TEXTDOMAIN ),
					'desc'             => __( 'Change WordPress editor font', EZPZ_TWEAKS_TEXTDOMAIN ),
					'id'               => 'editor-font-fa',
					'type'             => 'select',
					'show_option_none' => false,
					'options'          => $settings_page->custom_fonts(),
				)
			);
		} else {
			$cmb->add_field(
				array(
					'name'       => __( 'Admin Font', EZPZ_TWEAKS_TEXTDOMAIN ),
					'desc'       => __( 'Change WordPress admin font', EZPZ_TWEAKS_TEXTDOMAIN ),
					'id'         => 'admin-font',
					'type'       => 'text',
					'attributes' => array( 'data-placeholder' => __( 'Choose a font', EZPZ_TWEAKS_TEXTDOMAIN ), 'data-placeholder_search' => __( 'Type to search...', EZPZ_TWEAKS_TEXTDOMAIN ) )
				)
			);

			$cmb->add_field(
				array(
					'name'       => __( 'Editor Font', EZPZ_TWEAKS_TEXTDOMAIN ),
					'desc'       => __( 'Change WordPress editor font', EZPZ_TWEAKS_TEXTDOMAIN ),
					'id'         => 'editor-font',
					'type'       => 'text',
					'attributes' => array( 'data-placeholder' => __( 'Choose a font', EZPZ_TWEAKS_TEXTDOMAIN ), 'data-placeholder_search' => __( 'Type to search...', EZPZ_TWEAKS_TEXTDOMAIN ) )
				)
			);
		}

		$cmb->add_field(
			array(
				'name'         => __( 'Change WordPress Logo', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc'         => __( 'Upload an image or enter an URL.', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'           => 'custom_logo',
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
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Login Page Custom Text', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'Add custom text to wordpress admin login page', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'login_custom_text',
				'type' => 'textarea',
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Remove Welcome Panel', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'The welcome panel is a meta box added to the dashboard screen of the admin area. It shows shortcuts to different sections of your WordPress website', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'remove_welcome_panel',
				'type' => 'checkbox',
			)
		);

		$user_roles = ezpz_tweaks_wp_roles_array();

		$cmb->add_field(
			array(
				'name'    => __( 'Hide Admin Bar', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc'    => __( 'Hide admin bar for user roles', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'      => 'hide_admin_bar',
				'type'    => 'multicheck',
				'options' => $user_roles,
			)
		);

		$cmb->add_field(
			array(
				'name'    => __( 'Remove Dashboard Widgets', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc'    => __( 'Check widgets to remove from dashboard', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'      => 'remove_dashboard_widgets',
				'type'    => 'multicheck',
				'options' => $settings_page->dashboard_widgets_options(),
			)
		);

		$cmb->add_field(
			array(
				'before_row' => '<h2 class="title">'. __( 'Admin Footer', EZPZ_TWEAKS_TEXTDOMAIN ) .'</h2>',
				'name'    => __( 'Footer Text', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc'    => __( 'Change footer text', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'      => 'footer_text',
				'type'    => 'wysiwyg',
				'options' => array(
					'wpautop' => true, // use wpautop?
					'textarea_rows' => get_option('default_post_edit_rows', 10)
				),
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Visibility', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'Hide the entire admin footer', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'footer_visibility',
				'type' => 'checkbox',
			)
		);

		$cmb->add_field(
			array(
				'before_row' => '<h2 class="title">'. __( 'Brand Custom Menu', EZPZ_TWEAKS_TEXTDOMAIN ) .'</h2>',
				'name' => __( 'Enable Branding', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'By selecting this option, you can add your brand to the WordPress menu and customize how it displays.', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'enable_branding',
				'type' => 'checkbox',
				'sanitization_cb'  => 'sanitize_checkbox',
				'default'          => false,
				'active_value'     => true,
				'inactive_value'   => false
			)
		);

		$cmb->add_field(
			array(
				'name'         => __( 'Menu Title', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'           => 'menu_title',
				'type'         => 'text',
				'attributes'    => array(
					'data-conditional-id'     => 'enable_branding',
					'data-conditional-value'  => 'on',
					'required' => true,
				),
			)
		);

		$cmb->add_field(
			array(
				'name'         => __( 'Menu Slug', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'           => 'menu_slug',
				'type'         => 'text',
				'attributes'    => array(
					'data-conditional-id'     => 'enable_branding',
					'data-conditional-value'  => 'on',
					'required' => true,
				),
			)
		);

		$cmb->add_field(
			array(
				'name'         => __( 'Menu Logo', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc'         => __( 'Upload an image or enter an URL.', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'           => 'branding_menu_logo',
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
			)
		);

		$cmb->add_field(
			array(
				'name'    => __( 'Page Content', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc'    => __( 'You can use HTML to design branding page', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'      => 'page_content',
				'type'    => 'wysiwyg',
				'before' => '<div data-conditional-id="enable_branding" data-conditional-value="on">',
    			'after' => '</div>',
				'options' => array(
					'wpautop' => true, // use wpautop?
					'textarea_rows' => get_option('default_post_edit_rows', 10)
				),
			)
		);

		cmb2_metabox_form( EZPZ_TWEAKS_TEXTDOMAIN . '_options_customizing_branding', EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding' );
		?>
	</div>
	<div id="performance" class="wp-tab-panel" style="<?php echo $fragment != 'performance' ? 'display: none' : '' ?>">
		<?php
		$cmb = new_cmb2_box(
			array(
				'id'         => EZPZ_TWEAKS_TEXTDOMAIN . '_options_performance',
				'hookup'     => false,
				'show_on'    => array(
					'key'    => 'options-page',
					'value'  => array( EZPZ_TWEAKS_TEXTDOMAIN . '-performance' )
				),
				'show_names' => true,
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Disable Comment Website Field', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'Remove the website field from the comment form', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'disable_website_field',
				'type' => 'checkbox',
			)
		);
		$cmb->add_field(
			array(
				'name' => __( 'Disable Emojis', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'Remove wp-emoji-release.min.js file', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'disable_wp_emoji',
				'type' => 'checkbox',
			)
		);
		$cmb->add_field(
			array(
				'name' => __( 'Disable Embeds', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'Remove wp-embed.min.js file and reduce HTTP requests', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'disable_wp_embed',
				'type' => 'checkbox',
			)
		);
		$cmb->add_field(
			array(
				'name' => __( 'Remove Shortlink', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'Use this to create a shortlink to your pages and posts. However, if you are already using pretty permalinks, then there is no reason to keep this', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'remove_shortlink',
				'type' => 'checkbox',
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'WordPress Dashboard Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( '', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'disable_dashboard_heartbeat',
				'type' => 'radio',
				'default' => 'allow',
				'options'          => array(
					'allow'		 => __( 'Allow Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
					'disable'    => __( 'Disable Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
					'modify'     => __( 'Modify Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
				),
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Override Heartbeat frequency', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( '', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'range_modify_dashboard_heartbeat',
				'type' => 'range',
				'min' 	=> 15,
				'max'   => 300,
				'step' => 5,
				'attributes'    => array(
					'data-conditional-id'     => 'disable_dashboard_heartbeat',
					'data-conditional-value'  => 'modify',
				),
			)
		);


		$cmb->add_field(
			array(
				'name' => __( 'Frontend Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( '', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'disable_frontend_heartbeat',
				'type' => 'radio',
				'default' => 'allow',
				'options'          => array(
					'allow'		 => __( 'Allow Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
					'disable'    => __( 'Disable Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
					'modify'     => __( 'Modify Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
				),
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Override Heartbeat frequency', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( '', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'range_modify_frontend_heartbeat',
				'type' => 'range',
				'min' 	=> 15,
				'max'   => 300,
				'step' => 5,
				'attributes'    => array(
					'data-conditional-id'     => 'disable_frontend_heartbeat',
					'data-conditional-value'  => 'modify',
				),
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Post editor Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( '', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'disable_post_editor_heartbeat',
				'type' => 'radio',
				'default' => 'allow',
				'options'          => array(
					'allow'		 => __( 'Allow Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
					'disable'    => __( 'Disable Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
					'modify'     => __( 'Modify Heartbeat', EZPZ_TWEAKS_TEXTDOMAIN ),
				),
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Override Heartbeat frequency', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( '', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'range_modify_post_editor_heartbeat',
				'type' => 'range',
				'min' 	=> 15,
				'max'   => 300,
				'step' => 5,
				'attributes'    => array(
					'data-conditional-id'     => 'disable_post_editor_heartbeat',
					'data-conditional-value'  => 'modify',
				),
			)
		);


		

		cmb2_metabox_form( EZPZ_TWEAKS_TEXTDOMAIN . '_options_performance', EZPZ_TWEAKS_TEXTDOMAIN . '-performance' );
		?>
	</div>

	<div id="security" class="wp-tab-panel" style="<?php echo $fragment != 'security' ? 'display: none' : '' ?>">
	<?php
		$cmb = new_cmb2_box(
			array(
				'id'         => EZPZ_TWEAKS_TEXTDOMAIN . '_options_security',
				'hookup'     => false,
				'show_on'    => array(
					'key'    => 'options-page',
					'value'  => array( EZPZ_TWEAKS_TEXTDOMAIN . '-security' )
				),
				'show_names' => true,
			)
		);

		$cmb->add_field(
			array(
				'name'         => __( 'Change WP Login URL', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc'         => __( 'Change the WordPress Login URL and prevent access to the "/wp-admin" and "/wp-login.php" and "/wp-login.php?action=register" to protect your website from hackers and strangers.', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'           => 'custom_login_url',
				'type'         => 'text',
				'before_field' => '<span>' . trailingslashit( home_url() ) . '</span>',
			)
		);

		
		if ( current_user_can( 'administrator' ) ) {
			$cmb->add_field(
				array(
					'name' 		=> __( 'Who can access this plugin', EZPZ_TWEAKS_TEXTDOMAIN ),
					'id'   		=> 'plugin_access',
					'type' 		=> 'radio',
					'options'   => array(
						'super_admin'   	  => __( 'Super Admin', EZPZ_TWEAKS_TEXTDOMAIN ),
						'manage_options'	  => __( 'Anyone with the "mange_options" capability', EZPZ_TWEAKS_TEXTDOMAIN ),
						get_current_user_id() => __( 'Only the current user', EZPZ_TWEAKS_TEXTDOMAIN ),
					),
					'default' 	=> 'manage_options',
				)
			);
		}

		if ( current_user_can( 'administrator' ) ) {
			$cmb->add_field(
				array(
					'name' => __( 'Disable Public Access to WP REST API', EZPZ_TWEAKS_TEXTDOMAIN ),
					'desc' => __( ' API consumers be authenticated, which effectively prevents anonymous external access.', EZPZ_TWEAKS_TEXTDOMAIN ),
					'id'   => 'disable_rest_api',
					'type' => 'checkbox',
				)
			);
		}

		$cmb->add_field(
			array(
				'name' => __( 'Disable XML-RPC', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'Disabling this feature makes your site more secure', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'disable_xmlrpc',
				'type' => 'checkbox',
			)
		);

		
		$cmb->add_field(
			array(
				'name' => __( 'Remove WP Version', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'Remove the WordPress version number from different sections of your WordPress website', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'remove_wp_version',
				'type' => 'checkbox',
			)
		);

		$cmb->add_field(
			array(
				'name' => __( 'Disable Theme & Plugin File Editor', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc' => __( 'Disable to remove the ability for users to edit theme and plugin files', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'   => 'deactivate_file_editor',
				'type' => 'checkbox',
			)
		);

		$cmb->add_field(
			array(
				'name'    => __( 'Hide Update Notifications', EZPZ_TWEAKS_TEXTDOMAIN ),
				'desc'    => __( 'Hide update notifications for user roles', EZPZ_TWEAKS_TEXTDOMAIN ),
				'id'      => 'hide_update_notifications',
				'type'    => 'multicheck',
				'options' => $user_roles,
			)
		);

		cmb2_metabox_form( EZPZ_TWEAKS_TEXTDOMAIN . '_options_security', EZPZ_TWEAKS_TEXTDOMAIN . '-security' );
		?>
	</div>

	<div id="import-export" class="wp-tab-panel" style="<?php echo $fragment != 'import-export' ? 'display: none' : '' ?>">
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
	</div>

	<div id="about" class="wp-tab-panel" style="<?php echo $fragment != 'about' ? 'display: none' : '' ?>">
		<?php echo nl2br( __( "EzPz Tweaks is an all-in-one WordPress plugin that helps you personalize the admin panel appearances, clean your site code and remove unwanted features to increase its security and improve performance.\nLearn more at <a href='https://wpezpzdev.com/' target='_blank'>WPEzPzdev.com</a>", EZPZ_TWEAKS_TEXTDOMAIN ) ); ?>
	</div>
	<!-- End tabs -->

</div>
