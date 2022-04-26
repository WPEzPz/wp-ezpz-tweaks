<?php
use EZPZ_TWEAKS\Engine\MenuEditor\Walker_Admin_Bar_Edit;
use EZPZ_TWEAKS\Engine\MenuEditor\Admin_Bar_Edit;
require_once ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php';

if (!function_exists('wp_initial_nav_menu_meta_boxes')) {
	require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
}

require_once ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php';
$page = 'wpezpz-tweaks-edit-admin-bar';


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
add_meta_box( 'add-custom-links', __( 'Custom Links' ), 'wp_nav_menu_item_link_meta_box', 'admin-bar-editor', 'side', 'default' );
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

if ( empty(get_option('wpezpz_tweaks_admin_bar_edit') ) ) {
	update_option('wpezpz_tweaks_admin_bar_edit', Admin_Bar_Edit::get_nodes(), true);
}

$data = get_option('wpezpz_tweaks_admin_bar_edit');


$result = '<ul class="menu" id="menu-to-edit"> ';
$result .= $walker->walk( $data, 4 );
$result .= ' </ul> ';

?>
<script id='nav-menu-js-extra'>
var menus = {"oneThemeLocationNoMenus":"","moveUp":"Move up one","moveDown":"Move down one","moveToTop":"Move to the top","moveUnder":"Move under %s","moveOutFrom":"Move out from under %s","under":"Under %s","outFrom":"Out from under %s","menuFocus":"%1$s. Menu item %2$d of %3$d.","subMenuFocus":"%1$s. Sub item number %2$d under %3$s.","menuItemDeletion":"item %s","itemsDeleted":"Deleted menu item: %s.","itemAdded":"Menu item added","itemRemoved":"Menu item removed","movedUp":"Menu item moved up","movedDown":"Menu item moved down","movedTop":"Menu item moved to the top","movedLeft":"Menu item moved out of submenu","movedRight":"Menu item is now a sub-item"};
</script>
<div class="wrap ezpz-tweaks-tabs">

	<h2>
		<img src="<?php echo EZPZ_TWEAKS_PLUGIN_ROOT_URL . 'assets/img/EzPzTweaks-logo.svg' ?>" style="width: 50px;vertical-align: middle;padding: 15px;">
		<?php echo EZPZ_TWEAKS_NAME ?>
	</h2>

	<!-- Start tabs -->
	<ul class="wp-tab-bar">
		<li class="wp-tab-active"><a href="#edit-menus">Edit Menus</a></li>
	</ul>
	<!-- End tabs -->

	<div id="edit-menus" class="wp-tab-panel" >
		EzPz Tweaks is an all-in-one WordPress plugin that helps you personalize the admin panel appearances, clean your site code and remove unwanted features to increase its security and improve performance.<br>
		Learn more at <a href="https://wpezpzdev.com/" target="_blank">WPEzPzdev.com</a>


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
							<input type="hidden" id="closedpostboxesnonce" name="closedpostboxesnonce" value="06e7a7caef"><input type="hidden" id="meta-box-order-nonce" name="meta-box-order-nonce" value="2cb532fd0b"><input type="hidden" id="update-nav-menu-nonce" name="update-nav-menu-nonce" value="92da1f3018"><input type="hidden" name="_wp_http_referer" value="/wp-admin/admin.php?page=wpezpz-tweaks-edit-admin-bar">					<input type="hidden" name="action" value="update">
							<input type="hidden" name="menu" id="menu" value="2">
							<div id="nav-menu-header">
								<div class="major-publishing-actions wp-clearfix">
									<label class="menu-name-label" for="menu-name">Menu Name</label>
									<input name="menu-name" id="menu-name" type="text" class="menu-name regular-text menu-item-textbox form-required" required="required" value="Menu 1">
									<div class="publishing-action">
										<input type="submit" name="save_menu" id="save_menu_header" class="button button-primary button-large menu-save" value="Save Menu">							</div><!-- END .publishing-action -->
								</div><!-- END .major-publishing-actions -->
							</div><!-- END .nav-menu-header -->
							<div id="post-body">
								<div id="post-body-content" class="wp-clearfix">
																									<div class="drag-instructions post-body-plain">
											<p>Drag the items into the order you prefer. Click the arrow on the right of the item to reveal additional configuration options.</p>
										</div>

																			<div id="nav-menu-bulk-actions-top" class="bulk-actions">
												<label class="bulk-select-button" for="bulk-select-switcher-top">
													<input type="checkbox" id="bulk-select-switcher-top" name="bulk-select-switcher-top" class="bulk-select-switcher">
													<span class="bulk-select-button-label">Bulk Select</span>
												</label>
											</div>

										<div id="menu-instructions" class="post-body-plain menu-instructions-inactive"><p>Add menu items from the column on the left.</p></div>
<?php echo $result; ?>


																	<div id="nav-menu-bulk-actions-bottom" class="bulk-actions">
											<label class="bulk-select-button" for="bulk-select-switcher-bottom">
												<input type="checkbox" id="bulk-select-switcher-bottom" name="bulk-select-switcher-top" class="bulk-select-switcher">
												<span class="bulk-select-button-label">Bulk Select</span>
											</label>
											<input type="button" class="deletion menu-items-delete disabled" value="Remove Selected Items">
											<div id="pending-menu-items-to-delete">
												<p>List of menu items selected for deletion:</p>
												<ul></ul>
											</div>
										</div>

								</div><!-- /#post-body-content -->
							</div><!-- /#post-body -->
							<div id="nav-menu-footer">
								<div class="major-publishing-actions wp-clearfix">

																		<span class="delete-action">
											<a class="submitdelete deletion menu-delete" href="
											http://ezpz.test/wp-admin/nav-menus.php?action=delete&amp;menu=2&amp;_wpnonce=ee38eefd3b">Delete Menu</a>
										</span><!-- END .delete-action -->

									<div class="publishing-action">
										<input type="submit" name="save_menu" id="save_menu_admin_bar" class="button button-primary button-large menu-save" value="Save Menu">
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
