(function ($) {
	'use strict';
	$(function () {
		jQuery(document).ready(function ($) {

			$('.wp-tab-bar a').click(function (event) {
				event.preventDefault();

				// Limit effect to the container element.
				var context = $(this).closest('.wp-tab-bar').parent().parent();
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



			$('#import-export').on('click', 'button[data-action="createBackup"]', function() {
				$('#ezpz-tweeks-no-backup-message').hide();
				$.ajax({
					url: ezpz_object.ajax_url,
					data: {
					  'action'  : 'ezpz_tweaks_create_backup',
					  'security': ezpz_object.security,
					},
					success:function(data) {
						add_backup_to_table(data.data);
					}
				})
			});

			$('.ezpz-tweeks-settings-backup-form').on('click', 'button[data-action="deleteBackup"]', function() {
				const key = $(this).attr('data-key');
				$.ajax({
					url: ezpz_object.ajax_url,
					data: {
					  'action'  : 'ezpz_tweaks_delete_backup',
					  'security': ezpz_object.security,
					  'key'		: $(this).attr('data-key')
					},
					success:function(data) {
						remove_backup_from_table(key);
					}
				})
			});

			$('.ezpz-tweeks-settings-backup-form').on('click', 'button[data-action="restoreBackup"]', function() {
				if (confirm(ezpz_object.strings.restoreConfirm)) {
					const key = $(this).attr('data-key');
					$.ajax({
						url: ezpz_object.ajax_url,
						data: {
						'action'  : 'ezpz_tweaks_restore_backup',
						'security': ezpz_object.security,
						'key'		: $(this).attr('data-key')
						},
						success:function(data) {
							alert(data.data.message);
							location.reload();
						}
					})
				}
			});

			function add_backup_to_table( data ) {
				$('.ezpz-tweeks-settings-backup-form table tbody').prepend('<tr data-key="'+ data.key +'"><th> '+ data.backup +' </th> <td style="width:195px;padding-left:0;"><button type="button" class="button button-secondary button-small ezpz-tweeks-action" data-action="restoreBackup" data-key="'+ data.key +'">'+ ezpz_object.strings.restore +'</button> <button type="button" class="button button-link-delete button-small ezpz-tweeks-action" data-action="deleteBackup" data-key="'+ data.key +'">'+ ezpz_object.strings.delete +'</button></td></tr>');
			}

			function remove_backup_from_table( key ) {
				$('.ezpz-tweeks-settings-backup-form table tbody tr[data-key="'+ key +'"]').remove();
			}

			$('.ezpz-install-editor').on('click', function() {
				const selected = $('select[name="disable_block_editor"] option:selected')				
				var data = {
					action: 'wpezpz_change_page_editor',
					_ajax_nonce: ezpz_object.security_update,
					slug: $(selected).val()
				};
				jQuery.post( ezpz_object.ajax_url, data, function(response) {
					if ( response.success && typeof response.data !== 'undefined' ) {
						jQuery.post( ezpz_object.ajax_url, data, function(newResponse) {
							let str = newResponse.success ? ezpz_object.strings.changeEditorSuccess : ezpz_object.strings.changeEditorFailed;
							alert(str);
						});
					} else if ( response.success ) {
						alert( ezpz_object.strings.changeEditorSuccess );
					} else {
						alert( response.data.errorMessage )
					}
				});
			})
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

			$('.ezpz-search input[type=search]').keyup(function () {
				var searchField = $(this).val();
				if (searchField === '') {
					$('#ezpz-search-res').html('');
					$('#ezpz-search-res').hide();
					return;
				}

				var regex = new RegExp(searchField, "i");
				var output = ' ';
				var count = 0;
				$.each(searchData, function (key, val) {
					if (count > 6) {
						return;
					}

					if ((val.title.search(regex) != -1) || (val.description.search(regex) != -1)) {
						output += '<a href="#'+ val.tab +'" data-id="'+ val.id +'" class="ezpz-search-res-item">';
						output += '<h5>' + val.title + '</h5>';
						output += '<p>' + val.description + '</p>'
						output += '</a>';
						count++;
					}

				});
				$('#ezpz-search-res').html(output);
				$('#ezpz-search-res').show();
			});
			$('.ezpz-search input[type=search]').on( 'input', function () {
				var searchField = $(this).val();
				if (searchField === '') {
					$('#ezpz-search-res').html('');
					$('#ezpz-search-res').hide();
					return;
				}
			})

			$('#ezpz-search-res').on('click', '.ezpz-search-res-item', function(e) {
				e.preventDefault();
				var tab = $(this).attr('href');
				$('.ezpz-search input[type=search]').val('');
				$('#ezpz-search-res').html('');
				$('#ezpz-search-res').hide();

				$('.wp-tab-bar li a').each(function() {
					console.log($(this).attr('href'));
					if ($(this).attr('href') == tab) {
						$(this).click();
						return;
					}
				})
				var id = $(this).attr('data-id')

				if($('.cmb2-id-'+ id.replace(/_/gi, '-')).length > 0) {
					$('html, body').animate({
						scrollTop: $('.cmb2-id-'+ id.replace(/_/gi, '-')).offset().top - 100
					}, 500);
				}
			})
			if ($('.cmb2_select2_multiselect').length > 0) {
				$('.cmb2_select2_multiselect').select2({
					closeOnSelect: false
				});
			}
			if ($('.cmb2_type_select2_select').length > 0) {
				$('.cmb2_type_select2_select').select2();
			}
			if ($('.ezpz_css_editor').length > 0) {
				wp.codeEditor.initialize($('.ezpz_css_editor'), cm_settings);
			}
		})
	});
})(jQuery);
