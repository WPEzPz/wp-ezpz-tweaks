(function ($) {
	'use strict';
	$(function () {
		jQuery(document).ready(function ($) {
			$('#admin-font').fontselect({
				systemFonts: false,
				placeholderSearch: $('#admin-font').data('placeholder_search'),
				placeholder: $('#admin-font').data('placeholder'),
				lookahead: 4
			});

			$('#editor-font').fontselect({
				systemFonts: false,
				placeholderSearch: $('#editor-font').data('placeholder_search'),
				placeholder: $('#editor-font').data('placeholder'),
				lookahead: 4
			});

			$('.wp-tab-bar a').click(function (event) {
				event.preventDefault();

				// Limit effect to the container element.
				var context = $(this).closest('.wp-tab-bar').parent();
				setGetParameter( 'tab', $(this).attr("href" ).replace('#','') );
				// window.location.hash = $(this).attr("href");
				$('.wp-tab-bar li', context).removeClass('wp-tab-active');
				$(this).closest('li').addClass('wp-tab-active');
				$('.wp-tab-panel', context).hide();
				$($(this).attr('href'), context).show();
			});

			// // Make setting wp-tab-active optional.
			// $('.wp-tab-bar').each(function () {
			// 	if ($('.wp-tab-active', this).length)
			// 		$('.wp-tab-active', this).click();
			// 	else
			// 		$('a', this).first().click();
			// });

			function setGetParameter(paramName, paramValue)
			{
				var url = window.location.href;
				if (url.indexOf(paramName + "=") >= 0)
				{
					var prefix = url.substring(0, url.indexOf(paramName));
					var suffix = url.substring(url.indexOf(paramName));
					suffix = suffix.substring(suffix.indexOf("=") + 1);
					suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";
					url = prefix + paramName + "=" + paramValue + suffix;
				}
				else
				{
					if (url.indexOf("?") < 0)
						url += "?" + paramName + "=" + paramValue;
					else
						url += "&" + paramName + "=" + paramValue;
				}

				history.pushState({}, null, url);
			}

			var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
			editorSettings.codemirror = _.extend(
				{},
				editorSettings.codemirror,
				{
					indentUnit: 2,
					tabSize: 2,
					mode: 'css',
				}
			);
			if( $('#custom_css').length ) wp.codeEditor.initialize( $('#custom_css') );

			// Admin Menu Editor
			var isEditing = false;

			// Edit Button
			$('a[href="wpezpz-tweaks-edit-menu"]').click(function (e) {
				e.preventDefault();

				var button = $(this);

				isEditing = !isEditing;

				if (isEditing) {
					// Add submenu <ul> to all elements so we could add items to every menu if we want
					_.each($('#adminmenu > .menu-top:not(.wp-has-submenu)'), function (el) {
						var $el = $(el);
						$el.addClass('wp-has-submenu');
						if ($el.hasClass('current')) {
							$el.addClass('wp-has-current-submenu');
						}
						$el.append('<ul class="wp-submenu wp-submenu-wrap"><li class="wp-submenu-head">' + $(el).find('.wp-menu-name').html() + '</li></ul>');
					});
				} else {
					// Remove unneccessary classes again from the menu items
					_.each($('#adminmenu > .menu-top.wp-has-submenu'), function (el) {
						var $el = $(el);
						if ($el.find('li').length <= 1) {
							$el.removeClass('wp-has-current-submenu wp-has-submenu');
						}
					});
				}

				$('#adminmenuwrap ul').sortable({
					disabled   : !isEditing,
					cancel     : '#admin-menu-editor-edit, #collapse-menu',
					connectWith: '#adminmenuwrap ul',
					// This event is triggered when (surprise) sortable starts
					create     : function (event, ui) {
						// On init, store each menu item's initial state
						var separatorIndex = 0;

						_.each($('#adminmenu li:not(.wp-submenu-head)'), function (el, index) {
							var $el = $(el);

							$el.attr('data-ezpz-tweaks-class', $el.attr('class'));
							$el.attr('data-ezpz-tweaks-index', $el.index());

							if ($el.parent('.wp-submenu').length > 0) {
								$el.attr('data-ezpz-tweaks-parent', $el.parents('li').find('a').attr('href'));
								$el.attr('data-ezpz-tweaks-index', $el.index() - 1);
							}

							// Add this data attribute to separators to make things easier when sorting
							if ($el.hasClass('wp-menu-separator')) {
								$el.attr('data-ezpz-tweaks-separator', 'separator' + (++separatorIndex));
							}
						});

						$('[data-ezpz-tweaks-separator=separator' + separatorIndex + ']').attr('data-ezpz-tweaks-separator', 'separator-last');

					},
					// This event is triggered when the user stopped sorting and the DOM position has changed.
					update     : changeMenu,
					beforeStop : function (e, ui) {
						// return false if this is an element that shouldn't be dragged to a specific location
					},
					change     : function (e, ui) {
						// show the submenu items of an element close to the current item so we could move it there

						// Items can't be moved after the collapse and edit buttons
						var $fixed = $('#admin-menu-editor-edit, #collapse-menu', this).detach();
						$(this).append($fixed);
					}
				});

				if (isEditing) {
					button.text(AdminMenuManager.buttonSave);
					button.attr('title', AdminMenuManager.buttonSave);
				} else {
					var data = {
						action   : 'ezpz_tweaks_update_menu',
						adminMenu: AdminMenuManager.adminMenu
					};

					$.post(ajaxurl, data, function () {
						button.text(AdminMenuManager.buttonSaving).fadeOut(1000, function () {
							button.text(AdminMenuManager.buttonSaved)
								.fadeIn()
								.delay(1000)
								.fadeOut(50, function () {
									button.text(AdminMenuManager.buttonEdit).fadeIn(50);
								});
						});
					});
				}
			});

			function changeMenu(e, ui) {
				var itemHref = ui.item.find('a').attr('href'),
					newPosition = ui.item.index(),
					isSeparator = ui.item.is('.wp-menu-separator'),
					separator = ui.item.attr('data-ezpz-tweaks-separator'),
					currentPosition = [ui.item.index()],
					item,
					oldItem,
					oldIcon;

				// It's a submenu item
				if (ui.item.parent('.wp-submenu').length > 0) {
					newPosition = newPosition > 0 ? --newPosition : 0;
					var parentPosition = $('#adminmenu > li').index(ui.item.parents('li'));
					currentPosition = [parentPosition, newPosition];
				}

				// Add CSS classes
				if (ui.item.index() > 0) {
					ui.item.removeClass('wp-first-item');
				}

				/**
				 * Iterate through the admin menu object.
				 *
				 * Find the item's last position and move it to the new one.
				 */
				_.find(AdminMenuManager.adminMenu, function (value, index) {
					// Acommodate for different structures
					var isSame = ( value[2] && itemHref && value[2] === itemHref );
					if (!isSame && value[2].indexOf('.') === -1 && value[2] && itemHref) {
						isSame = 'admin.php?page=' + value[2] === itemHref;
					}

					if (isSame || ( isSeparator && value[4] === 'wp-menu-separator' && value[2] === separator )) {
						oldItem = [index];
						return true;
					}

					// Iterate on sub menu items
					_.find(value[7], function (v, k) {
						// Acommodate for different structures
						var isSame = ( v[2] && itemHref && v[2] === itemHref );
						if (!isSame && v[2].indexOf('.') === -1 && v[2] && itemHref) {
							isSame = 'admin.php?page=' + v[2] === itemHref || this.parent[2] + '?page=' + v[2] === itemHref;
						}

						if (isSame || ( isSeparator && v[4] === 'wp-menu-separator' && v[2] === separator )) {
							oldItem = [index, k];
							return true;
						}
					}, {parent: value});
				});

				// Get the item object from the old position
				if (oldItem) {
					oldIcon = AdminMenuManager.adminMenu[oldItem[0]][6];

					if (oldItem.length === 1) {
						item = AdminMenuManager.adminMenu[oldItem[0]];
						AdminMenuManager.adminMenu.splice(oldItem[0], 1);
					} else if (oldItem.length === 2) {
						item = AdminMenuManager.adminMenu[oldItem[0]][7][oldItem[1]];
						AdminMenuManager.adminMenu[oldItem[0]][7].splice(oldItem[1], 1);
					}
				}

				// Move it to the new position. Add icon if not existing
				if (currentPosition.length === 1) {
					if (!isSeparator) {
						item[4] = 'menu-top';
					}

					// Copy from the parent item if available
					item[5] = item[5] ? item[5] : (!!oldItem ? AdminMenuManager.adminMenu[oldItem[0]][5] : '');
					item[6] = oldIcon ? oldIcon : 'dashicons-admin-generic';
					AdminMenuManager.adminMenu.splice(currentPosition[0], 0, item);
				} else if (currentPosition.length === 2) {
					item[4] = '';

					if (AdminMenuManager.adminMenu[currentPosition[0]][7].length > 0) {
						AdminMenuManager.adminMenu[currentPosition[0]][7].splice(currentPosition[1], 0, item);
					} else {
						// This means the menu item hasn't had any children before.
						AdminMenuManager.adminMenu[currentPosition[0]][7].push(item);
					}
				}

				// Was this item moved to the top level?
				if (ui.item.parent('#adminmenu').length > 0) {
					// Is this a separator or not?
					if (!isSeparator) {
						// Is this originally a top level item or not?
						if (!ui.item.attr('data-ezpz-tweaks-parent')) {
							ui.item.removeClass().addClass(ui.item.attr('data-ezpz-tweaks-class')).addClass('ui-sortable-handle');
						} else {
							ui.item.addClass('menu-top');
							ui.item.find('a').addClass('menu-top');
						}

						ui.item.addClass(item[5]);
						ui.item.find('.ezpz-tweaks-wp-menu-name').removeClass('ezpz-tweaks-wp-menu-name').addClass('wp-menu-name');

						// Item doesn't yet have the structure that is needed for a top level item
						if (ui.item.find('a div').length === 0) {
							ui.item.find('a').wrapInner('<div class="wp-menu-name"></div>');

							// Add the menu icon depending on context (dashicon/svg/div)
							if (item[6].indexOf('dashicons') > -1) {
								ui.item.find('a').prepend('<div class="wp-menu-image dashicons-before ' + item[6] + '"><br></div>');
							} else if (item[6].indexOf('image/svg') > -1 || item[6].indexOf('http') > -1) {
								ui.item.find('a').prepend('<div class="wp-menu-image svg" style="background-image:url(' + item[6] + ') !important;"><br></div>');
							} else if ('div' === item[6] || 'none' === item[6]) {
								ui.item.find('a').prepend('<div class="wp-menu-image dashicons-before"><br></div>');
							} else {
								ui.item.find('a').prepend('<div class="wp-menu-image dashicons-before dashicons-admin-generic"><br></div>');
							}
						}

						// Showtime!
						ui.item.find('.wp-menu-arrow').removeClass('hidden');
						ui.item.find('.wp-menu-image').removeClass('hidden');
						ui.item.find('.wp-submenu').removeClass('hidden');
					}
				} else {
					// Submenu item, hide stuff that isn't needed
					ui.item.removeClass('menu-top').removeClass(ui.item.attr('class').match(/toplevel_[\w-]*\b/));
					ui.item.find('.menu-top').removeClass('menu-top');
					ui.item.find('.wp-menu-arrow').addClass('hidden');
					ui.item.find('.wp-menu-image').addClass('hidden');
					ui.item.find('.wp-submenu').addClass('hidden');
					if (ui.item.find('.wp-menu-name').length > 0) {
						ui.item.find('.wp-menu-name').removeClass('wp-menu-name').addClass('ezpz-tweaks-wp-menu-name');
					}
				}
			}

			// CMB2 range
			$('.cmb2-range').each(function() {
				var span = $(this).siblings('.range-text').children('.range-value');
				// Append the range value to each slider 
				span.html('<strong>' + $(this).val() + '</strong>' );
				// update to the dynamic value
				$(this).on('input', function() {
					span.html('<strong>' + $(this).val() + '</strong>' );
				});           
			});
		})
	});
})(jQuery);
