<?php
use EZPZ_TWEAKS\Engine\MenuEditor\Walker_Admin_Bar_Edit;
use EZPZ_TWEAKS\Engine\MenuEditor\Admin_Bar_Edit;
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

// The menu id of the current menu being edited.
$nav_menu_selected_id = isset( $_REQUEST['preset'] ) ? (int) $_REQUEST['preset'] : 0;


// Allowed actions: add, update, delete.
$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'edit';

$walker = new Walker_Admin_Bar_Edit();

$Walker_Nav_Menu_Edit = Admin_Bar_Edit::get_walker();

$result = '<ul class="menu" id="menu-to-edit"> ';
$result .= $walker->walk( Admin_Bar_Edit::get_nodes(), 4 );
$result .= ' </ul> ';


// echo '<pre>';
// var_export( Admin_Bar_Edit::get_nodes() );
// print_r($menu_items );

// echo '</pre>';
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
					<input type="hidden" name="menu" id="nav-menu-meta-object-id" value="menu-bar">
					<input type="hidden" name="action" value="add-menu-item">
					<input type="hidden" id="menu-settings-column-nonce" name="menu-settings-column-nonce" value="<?php wp_create_nonce('ezpz-edit-admin-bar') ?>">
					<input type="hidden" name="_wp_http_referer" value="/wp-admin/admin.php?page=wpezpz-tweaks-edit-admin-bar">
					<h2><?php _e('Add admin bar items', EZPZ_TWEAKS_TEXTDOMAIN) ?></h2>
					<div id="side-sortables" class="accordion-container">
						<ul class="outer-border">
							<li class="control-section accordion-section  open add-post-type-page" id="add-post-type-page">
								<h3 class="accordion-section-title hndle" tabindex="0">
									Pages							<span class="screen-reader-text">Press return or enter to open this section</span>
								</h3>
								<div class="accordion-section-content ">
									<div class="inside">
										<div id="posttype-page" class="posttypediv">
											<ul id="posttype-page-tabs" class="posttype-tabs add-menu-item-tabs">
												<li class="tabs">
													<a class="nav-tab-link" data-type="tabs-panel-posttype-page-most-recent" href="/wp-admin/nav-menus.php?page-tab=most-recent#tabs-panel-posttype-page-most-recent">
														Most Recent				</a>
												</li>
												<li>
													<a class="nav-tab-link" data-type="page-all" href="/wp-admin/nav-menus.php?page-tab=all#page-all">
														View All				</a>
												</li>
												<li>
													<a class="nav-tab-link" data-type="tabs-panel-posttype-page-search" href="/wp-admin/nav-menus.php?page-tab=search#tabs-panel-posttype-page-search">
														Search				</a>
												</li>
											</ul><!-- .posttype-tabs -->

											<div id="tabs-panel-posttype-page-most-recent" class="tabs-panel tabs-panel-active" role="region" aria-label="Most Recent" tabindex="0">
												<ul id="pagechecklist-most-recent" class="categorychecklist form-no-clear">
													<li><label class="menu-item-title"><input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="253"> hello</label><input type="hidden" class="menu-item-db-id" name="menu-item[-1][menu-item-db-id]" value="0"><input type="hidden" class="menu-item-object" name="menu-item[-1][menu-item-object]" value="page"><input type="hidden" class="menu-item-parent-id" name="menu-item[-1][menu-item-parent-id]" value="0"><input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="post_type"><input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="hello"><input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="http://ezpz.test/hello/"><input type="hidden" class="menu-item-target" name="menu-item[-1][menu-item-target]" value=""><input type="hidden" class="menu-item-attr-title" name="menu-item[-1][menu-item-attr-title]" value=""><input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value=""><input type="hidden" class="menu-item-xfn" name="menu-item[-1][menu-item-xfn]" value=""></li>
													<li><label class="menu-item-title"><input type="checkbox" class="menu-item-checkbox" name="menu-item[-2][menu-item-object-id]" value="187"> My account</label><input type="hidden" class="menu-item-db-id" name="menu-item[-2][menu-item-db-id]" value="0"><input type="hidden" class="menu-item-object" name="menu-item[-2][menu-item-object]" value="page"><input type="hidden" class="menu-item-parent-id" name="menu-item[-2][menu-item-parent-id]" value="0"><input type="hidden" class="menu-item-type" name="menu-item[-2][menu-item-type]" value="post_type"><input type="hidden" class="menu-item-title" name="menu-item[-2][menu-item-title]" value="My account"><input type="hidden" class="menu-item-url" name="menu-item[-2][menu-item-url]" value="http://ezpz.test/my-account/"><input type="hidden" class="menu-item-target" name="menu-item[-2][menu-item-target]" value=""><input type="hidden" class="menu-item-attr-title" name="menu-item[-2][menu-item-attr-title]" value=""><input type="hidden" class="menu-item-classes" name="menu-item[-2][menu-item-classes]" value=""><input type="hidden" class="menu-item-xfn" name="menu-item[-2][menu-item-xfn]" value=""></li>
												</ul>
											</div><!-- /.tabs-panel -->

											<p class="button-controls wp-clearfix" data-items-type="posttype-page">
								<span class="list-controls hide-if-no-js">
									<input type="checkbox" id="page-tab" class="select-all">
									<label for="page-tab">Select All</label>
								</span>

																	<span class="add-to-menu">
									<input type="submit" class="button submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-posttype-page">
									<span class="spinner"></span>
								</span>
											</p>

										</div><!-- /.posttypediv -->
									</div><!-- .inside -->
								</div><!-- .accordion-section-content -->
							</li><!-- .accordion-section -->
							<li class="control-section accordion-section   add-custom-links" id="add-custom-links">
								<h3 class="accordion-section-title hndle" tabindex="0">
									Custom Links							<span class="screen-reader-text">Press return or enter to open this section</span>
								</h3>
								<div class="accordion-section-content ">
									<div class="inside">
										<div class="customlinkdiv" id="customlinkdiv">
											<input type="hidden" value="custom" name="menu-item[-53][menu-item-type]">
											<p id="menu-item-url-wrap" class="wp-clearfix">
												<label class="howto" for="custom-menu-item-url">URL</label>
												<input id="custom-menu-item-url" name="menu-item[-53][menu-item-url]" type="text" class="code menu-item-textbox form-required" placeholder="https://">
											</p>

											<p id="menu-item-name-wrap" class="wp-clearfix">
												<label class="howto" for="custom-menu-item-name">Link Text</label>
												<input id="custom-menu-item-name" name="menu-item[-53][menu-item-title]" type="text" class="regular-text menu-item-textbox">
											</p>

											<p class="button-controls wp-clearfix">
								<span class="add-to-menu">
									<input type="submit" class="button submit-add-to-menu right" value="Add to Menu" name="add-custom-menu-item" id="submit-customlinkdiv">
									<span class="spinner"></span>
								</span>
											</p>

										</div><!-- /.customlinkdiv -->
									</div><!-- .inside -->
								</div><!-- .accordion-section-content -->
							</li><!-- .accordion-section -->
						</ul><!-- .outer-border -->
					</div><!-- .accordion-container -->
				</form>

			</div><!-- /#menu-settings-column -->
			<div id="menu-management-liquid">
				<div id="menu-management">
					<form id="update-nav-menu" method="post" enctype="multipart/form-data">
						<h2><?php _e('Admin Bar structure', EZPZ_TWEAKS_TEXTDOMAIN); ?></h2>
						<div class="menu-edit">
							<input type="hidden" name="nav-menu-data">
							<input type="hidden" id="closedpostboxesnonce" name="closedpostboxesnonce" value="06e7a7caef"><input type="hidden" id="meta-box-order-nonce" name="meta-box-order-nonce" value="2cb532fd0b"><input type="hidden" id="update-nav-menu-nonce" name="update-nav-menu-nonce" value="92da1f3018"><input type="hidden" name="_wp_http_referer" value="/wp-admin/nav-menus.php">					<input type="hidden" name="action" value="update">
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

									<div class="menu-settings">
										<h3>Menu Settings</h3>

										<fieldset class="menu-settings-group auto-add-pages">
											<legend class="menu-settings-group-name howto">Auto add pages</legend>
											<div class="menu-settings-input checkbox-input">
												<input type="checkbox" name="auto-add-pages" id="auto-add-pages" value="1"> <label for="auto-add-pages">Automatically add new top-level pages to this menu</label>
											</div>
										</fieldset>


											<fieldset class="menu-settings-group menu-theme-locations">
												<legend class="menu-settings-group-name howto">Display location</legend>
																							<div class="menu-settings-input checkbox-input">
														<input type="checkbox" checked="checked" name="menu-locations[primary_navigation]" id="locations-primary_navigation" value="2">
														<label for="locations-primary_navigation">Primary Navigation</label>
																									</div>
																					</fieldset>


									</div>
								</div><!-- /#post-body-content -->
							</div><!-- /#post-body -->
							<div id="nav-menu-footer">
								<div class="major-publishing-actions wp-clearfix">

																		<span class="delete-action">
											<a class="submitdelete deletion menu-delete" href="
											http://ezpz.test/wp-admin/nav-menus.php?action=delete&amp;menu=2&amp;_wpnonce=ee38eefd3b									">Delete Menu</a>
										</span><!-- END .delete-action -->

																<div class="publishing-action">
										<input type="submit" name="save_menu" id="save_menu_footer" class="button button-primary button-large menu-save" value="Save Menu">							</div><!-- END .publishing-action -->
								</div><!-- END .major-publishing-actions -->
							</div><!-- /#nav-menu-footer -->
						</div><!-- /.menu-edit -->
					</form><!-- /#update-nav-menu -->
				</div><!-- /#menu-management -->
			</div><!-- /#menu-management-liquid -->
		</div>
	</div>

</div>
