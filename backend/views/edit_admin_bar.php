<?php
use EZPZ_TWEAKS\Engine\MenuEditor\Walker_Admin_Bar_Edit;
use EZPZ_TWEAKS\Engine\MenuEditor\Admin_Bar_Edit;
require_once ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php';

if (!function_exists('wp_initial_nav_menu_meta_boxes')) {
	require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
}

$page = 'wpezpz-tweaks-edit-admin-bar';
$current_tab = isset( $_GET['user_role'] ) ? sanitize_text_field($_GET['user_role']) : 'general';
$user_role = $current_tab;

do_action('wpezpz_tweaks_admin_bar_edit_before_render');
// Permissions check.
if ( ! current_user_can( 'edit_theme_options' ) ) {
	wp_die(
		'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
		'<p>' . __( 'Sorry, you are not allowed to edit theme options on this site.' ) . '</p>',
		403
	);
}

wp_enqueue_script( 'nav-menu' );

if ( wp_is_mobile() ) {
	wp_enqueue_script( 'jquery-touch-punch' );
}

// Container for any messages displayed to the user.
$messages = array();

// Container that stores the name of the active preset.
$nav_menu_selected_preset = '';

// wp_nav_menu_setup();

wp_nav_menu_post_type_meta_boxes();
add_meta_box( 'add-custom-links', __( 'Custom Links' ), array('EZPZ_TWEAKS\Engine\MenuEditor\Admin_Bar_Helper', 'nav_menu_item_link_meta_box'), 'admin-bar-editor', 'side', 'default' );
// $taxonomies = get_taxonomies( array( 'show_in_nav_menus' => true ), 'object' );

// foreach ( $taxonomies as $tax ) {
// 	/** This filter is documented in wp-admin/includes/nav-menu.php */
// 	$tax = apply_filters( 'nav_menu_meta_box_object', $tax );
// 	if ( $tax ) {
// 		$id = $tax->name;
// 		add_meta_box( "add-{$id}", $tax->labels->name, 'admin_bar_item_taxonomy_meta_box', 'admin-bar-editor', 'side', 'default', $tax );
// 	}
// }
// Register advanced menu items (columns).
add_filter( 'manage_nav-menus_columns', function () {
	return array(
		'_title'          => __( 'Show advanced menu properties' ),
		'title-attribute' => __( 'Title Attribute' ),
		'css-classes'     => __( 'CSS Classes' ),
		'xfn'             => __( 'Link Relationship (XFN)' ),
	);
});

wp_initial_nav_menu_meta_boxes();
// Allowed actions: add, update, delete.
$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'edit';

$walker = new Walker_Admin_Bar_Edit();

$Walker_Nav_Menu_Edit = Admin_Bar_Edit::get_walker();
// delete_option( 'wpezpz_tweaks_admin_bar_edit' );


if ( empty(get_option('wpezpz_tweaks_admin_bar_edit-' . $user_role) ) ) {
	update_option(
		'wpezpz_tweaks_admin_bar_edit-' . $user_role,
		Admin_Bar_Edit::sort_nodes_by_periority(Admin_Bar_Edit::get_nodes()),
		true
	);
}
$data = get_option('wpezpz_tweaks_admin_bar_edit-' . $user_role);

foreach ($data as $node) {
	$node->visibility = isset($node->visibility) ? $node->visibility : 'default';
}

$result = '<ul class="menu" id="menu-to-edit"> ';
$result .= $walker->walk( $data, 4 );
$result .= ' </ul> ';

?>
<script id='nav-menu-js-extra'>
	var menus = {
		"oneThemeLocationNoMenus": "",
		"moveUp": "Move up one",
		"moveDown": "Move down one",
		"moveToTop": "Move to the top",
		"moveUnder": "Move under %s",
		"moveOutFrom": "Move out from under %s",
		"under": "Under %s",
		"outFrom": "Out from under %s",
		"menuFocus": "%1$s. Menu item %2$d of %3$d.",
		"subMenuFocus": "%1$s. Sub item number %2$d under %3$s.",
		"menuItemDeletion": "item %s",
		"itemsDeleted": "Deleted menu item: %s.",
		"itemAdded": "Menu item added",
		"itemRemoved": "Menu item removed",
		"movedUp": "Menu item moved up",
		"movedDown": "Menu item moved down",
		"movedTop": "Menu item moved to the top",
		"movedLeft": "Menu item moved out of submenu",
		"movedRight": "Menu item is now a sub-item"
	};

</script>
<div class="wrap ezpz-tweaks-tabs">

	<h2>
		<img src="<?php echo EZPZ_TWEAKS_PLUGIN_ROOT_URL . 'assets/img/EzPzTweaks-logo.svg' ?>"
			style="width: 50px;vertical-align: middle;padding: 15px;">
		<?php echo EZPZ_TWEAKS_NAME ?>
	</h2>
	<p class="ezpz-description">
		<?php _e('EZPZ Tweaks allows you to edit the admin bar based on user role.', EZPZ_TWEAKS_TEXTDOMAIN) ?>
	</p>
	<!-- Start tabs -->
	<ul class="wp-admin-bar-tab-bar">
		<?php 
		$tabs = apply_filters('wpezpz_tweaks_admin_bar_tabs', '');
		$defualt_tab =  empty($tabs) ? __('WordPress Admin Bar Editor', EZPZ_TWEAKS_TEXTDOMAIN) : __('General', EZPZ_TWEAKS_TEXTDOMAIN);
		?>
		<li class="<?php echo $current_tab == 'general' ? 'wp-tab-active' : ''; ?>">
			<a href="<?php echo admin_url( 'admin.php?page='. $page ); ?>"><?php echo $defualt_tab ?></a>
		</li>
		<?php echo $tabs; ?>
	</ul>
	<!-- End tabs -->

	<div id="edit-menus" class="wp-tab-panel">

		<div id="nav-menus-frame" class="wp-clearfix">
			<div id="menu-settings-column" class="metabox-holder">

				<div class="clear"></div>

				<form id="nav-menu-meta" class="nav-menu-meta" method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="add-menu-item" />
					<?php wp_nonce_field( 'add-menu_item', 'menu-settings-column-nonce' ); ?>
					<h2><?php _e( 'Add menu items' ); ?></h2>
					<?php do_accordion_sections( 'admin-bar-editor', 'side', null ); ?>
				</form>

			</div><!-- /#menu-settings-column -->
			<div id="menu-management-liquid">
				<div id="menu-management">
					<form id="update-nav-menu" method="post" enctype="multipart/form-data">
						<h2><?php _e('Admin Bar structure', EZPZ_TWEAKS_TEXTDOMAIN); ?></h2>
						<div class="menu-edit">
						<input type="hidden" name="nav-menu-data">
						<?php echo apply_filters('wpezpz_tweaks_admin_bar_inputs', ''); ?>
							<div id="nav-menu-header">
								<div class="major-publishing-actions wp-clearfix">
									<label class="menu-name-label" for="menu-name"><?php _e('User Role', EZPZ_TWEAKS_TEXTDOMAIN) ?></label>
									<input name="menu-name" id="menu-name" type="text"
										class="menu-name regular-text menu-item-textbox form-required"
										required="required" value="<?php echo ucwords($current_tab); ?>" disabled>
									<div class="publishing-action">
										<input type="submit" name="save_menu" id="save_menu_header"
											class="button button-primary button-large menu-save" value="Save Menu">
									</div><!-- END .publishing-action -->
								</div><!-- END .major-publishing-actions -->
							</div><!-- END .nav-menu-header -->
							<div id="post-body">
								<div id="post-body-content" class="wp-clearfix">
									<div class="drag-instructions post-body-plain">
										<p>Drag the items into the order you prefer. Click the arrow on the right of the
											item to reveal additional configuration options.</p>
									</div>

									<div id="menu-instructions" class="post-body-plain menu-instructions-inactive">
										<p>Add menu items from the column on the left.</p>
									</div>
									<?php echo $result; ?>

								</div><!-- /#post-body-content -->
							</div><!-- /#post-body -->
							<div id="nav-menu-footer">
								<div class="major-publishing-actions wp-clearfix">
									<div class="publishing-action">
										<input type="submit" name="save_menu" id="save_menu_admin_bar"
											class="button button-primary button-large menu-save" value="Save Menu">
									</div><!-- END .publishing-action -->
								</div><!-- END .major-publishing-actions -->
							</div><!-- /#nav-menu-footer -->
						</div><!-- /.menu-edit -->
					</form><!-- /#update-nav-menu -->
				</div><!-- /#menu-management -->
			</div><!-- /#menu-management-liquid -->
		</div>
	</div>

</div>
