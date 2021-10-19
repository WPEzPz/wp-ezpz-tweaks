/*
 * jQuery.fontselect - A font selector for system fonts, local fonts and Google Web Fonts
 *
 * Made by Arjan Haverkamp, https://www.webgear.nl
 * Based on original by Tom Moor, http://tommoor.com
 * Copyright (c) 2011 Tom Moor, 2019-2020 Arjan Haverkamp
 * MIT Licensed
 * @version 1.0 - 2020-02-26
 * @url https://github.com/av01d/fontselect-jquery-plugin
 */

(function($){

	var fontsLoaded = {};

	$.fn.fontselect = function(options) {
		var __bind = function(fn, me) { return function(){ return fn.apply(me, arguments); }; };

		var settings = {
			style: 'font-select',
			placeholder: 'Select a font',
			placeholderSearch: 'Search...',
			searchable: true,
			lookahead: 2,
			googleApi: 'https://fonts.googleapis.com/css?family=',
			localFontsUrl: '/fonts/',
			systemFonts: 'Arial|Helvetica+Neue|Courier+New|Times+New+Roman|Comic+Sans+MS|Verdana|Impact'.split('|'),

			googleFonts: [
				"Alegreya:400", 
                "B612:400", 
                "Muli:400", 
                "Titillium+Web:400", 
                "Varela:400", 
                "Vollkorn:400", 
                "IBM+Plex:400", 
                "Crimson+Text:400", 
                "Cairo:400", 
                "BioRhyme:400", 
                "Karla:400",
                "Lora:400", 
                "Frank+Ruhl+Libre:400", 
                "Playfair+Display:400", 
                "Archivo:400", 
                "Spectral:400", 
                "Fjalla+One:400", 
                "Roboto:400", 
                "Montserrat:400", 
                "Rubik:400", 
                "Source+Sans:400", 
                "Cardo:400", 
                "Cormorant:400", 
                "Work+Sans:400", 
                "Rakkas:400", 
                "Concert+One:400", 
                "Yatra+One:400", 
                "Arvo:400", 
                "Lato:400", 
                "Abril+FatFace:400", 
                "Ubuntu:400", 
                "PT+Serif:400", 
                "Old+Standard+TT:400",
                "Oswald:400", 
                "PT+Sans:400", 
                "Poppins:400", 
                "Fira+Sans:400", 
                "Nunito:400", 
                "Oxygen:400", 
                "Exo+2:400", 
                "Open+Sans:400", 
                "Merriweather:400", 
                "Noto+Sans:400", 
                "Source+Sans+Pro:400"

			]
		};

		var Fontselect = (function(){

			function Fontselect(original, o) {
				if (!o.systemFonts) { o.systemFonts = []; }
				if (!o.localFonts) { o.localFonts = []; }
				if (!o.googleFonts) { o.googleFonts = []; }

				var googleFonts = [];
				for (var i = 0; i < o.googleFonts.length; i++) {
					var item = o.googleFonts[i].split(':'); // Unna:regular,italic,700,700italic
					var fontName = item[0], fontVariants = item[1] ? item[1].split(',') : [];
					for (var v = 0; v < fontVariants.length; v++) {
						googleFonts.push(fontName + ':' + fontVariants[v]);
					}
				}
				o.googleFonts = googleFonts;

				this.options = o;
				this.$original = $(original);
				this.setupHtml();
				this.getVisibleFonts();
				this.bindEvents();
				this.query = '';
				this.keyActive = false;
				this.searchBoxHeight = 0;

				var font = this.$original.val();
				if (font) {
					this.updateSelected();
					this.addFontLink(font);
				}
			}

			Fontselect.prototype = {
				keyDown: function(e) {

					function stop(e) {
						e.preventDefault();
						e.stopPropagation();
					}

					this.keyActive = true;
					if (e.keyCode == 27) {// Escape
						stop(e);
						this.toggleDropdown('hide');
						return;
					}
					if (e.keyCode == 38) {// Cursor up
						stop(e);
						var $li = $('li.active', this.$results), $pli = $li.prev('li');
						if ($pli.length > 0) {
							$li.removeClass('active');
							this.$results.scrollTop($pli.addClass('active')[0].offsetTop - this.searchBoxHeight);
						}
						return;
					}
					if (e.keyCode == 40) {// Cursor down
						stop(e);
						var $li = $('li.active', this.$results), $nli = $li.next('li');
						if ($nli.length > 0) {
							$li.removeClass('active');
							this.$results.scrollTop($nli.addClass('active')[0].offsetTop - this.searchBoxHeight);
						}
						return;
					}
					if (e.keyCode == 13) {// Enter
						stop(e);
						$('li.active', this.$results).trigger('click');
						return;
					}
					this.query += String.fromCharCode(e.keyCode).toLowerCase();
					var $found = $("li[data-query^='"+ this.query +"']").first();
					if ($found.length > 0) {
						$('li.active', this.$results).removeClass('active');
						this.$results.scrollTop($found.addClass('active')[0].offsetTop);
					}
				},

				keyUp: function(e) {
					this.keyActive = false;
				},

				bindEvents: function() {
					var self = this;

					$('li', this.$results)
					.click(__bind(this.selectFont, this))
					.mouseover(__bind(this.activateFont, this));

					this.$select.click(__bind(function() { self.toggleDropdown('show') }, this));

					// Call like so: $("input[name='ffSelect']").trigger('setFont', [fontFamily, fontWeight]);
					this.$original.on('setFont', function(evt, fontFamily, fontWeight) {
						fontWeight = fontWeight || 400;

						var fontSpec = fontFamily.replace(/ /g, '+') + ':' + fontWeight;

						var $li = $("li[data-value='"+ fontSpec +"']", self.$results);
						if ($li.length == 0) {
							fontSpec = fontFamily.replace(/ /g, '+');
						}
						console.log(fontSpec);
						$li = $("li[data-value='"+ fontSpec +"']", self.$results);
						$('li.active', self.$results).removeClass('active');
						$li.addClass('active');

						self.$original.val(fontSpec);
						self.updateSelected();
						self.addFontLink($li.data('value'));
						//$li.trigger('click'); // Removed 2019-10-16
					});
					this.$original.on('change', function() {
						self.updateSelected();
						self.addFontLink($('li.active', self.$results).data('value'));
					});

					if (this.options.searchable) {
						this.$input.on('keyup', function() {
							var q = this.value.toLowerCase();
							// Hide options that don't match query:
							$('li', self.$results).each(function() {
								if ($(this).text().toLowerCase().indexOf(q) == -1) {
									$(this).hide();
								}
								else {
									$(this).show();
								}
							})
						})
					}

					$(document).on('click', function(e) {
						if ($(e.target).closest('.'+self.options.style).length === 0) {
							self.toggleDropdown('hide');
						}
					});
				},

				toggleDropdown: function(hideShow) {
					if (hideShow === 'hide') {
						// Make inactive
						this.$element.off('keydown keyup');
						this.query = '';
						this.keyActive = false;
						this.$element.removeClass('font-select-active');
						this.$drop.hide();
						clearInterval(this.visibleInterval);
					} else {
						// Make active
						this.$element.on('keydown', __bind(this.keyDown, this));
						this.$element.on('keyup', __bind(this.keyUp, this));
						this.$element.addClass('font-select-active');
						this.$drop.show();

						this.visibleInterval = setInterval(__bind(this.getVisibleFonts, this), 500);
						this.searchBoxHeight = this.$search.outerHeight();
						this.moveToSelected();

						/*
						if (this.options.searchable) {
							// Focus search box
							$this.$input.focus();
						}
						*/
					}
				},

				selectFont: function() {
					var font = $('li.active', this.$results).data('value');
					this.$original.val(font).change();
	 				this.updateSelected();
					this.toggleDropdown('hide'); // Hide dropdown
				},

				moveToSelected: function() {
					var font = this.$original.val().replace(/ /g, '+');
					var $li = font ? $("li[data-value='"+ font +"']", this.$results) : $li = $('li', this.$results).first();
					this.$results.scrollTop($li.addClass('active')[0].offsetTop - this.searchBoxHeight);
				},

				activateFont: function(e) {
					if (this.keyActive) { return; }
					$('li.active', this.$results).removeClass('active');
					$(e.target).addClass('active');
				},

				updateSelected: function() {
					var font = this.$original.val();
					$('span', this.$element).text(this.toReadable(font)).css(this.toStyle(font));
				},

				setupHtml: function() {
					this.$original.hide();
					this.$element = $('<div>', {'class': this.options.style});
					this.$select = $('<span tabindex="0">' + this.options.placeholder + '</span>');
					this.$search = $('<div>', {'class': 'fs-search'});
					this.$input = $('<input>', {type:'text'});
					if (this.options.placeholderSearch) {
						this.$input.attr('placeholder', this.options.placeholderSearch);
					}
					this.$search.append(this.$input);
					this.$drop = $('<div>', {'class': 'fs-drop'});
					this.$results = $('<ul>', {'class': 'fs-results'});
					this.$original.after(this.$element.append(this.$select, this.$drop));
					this.options.searchable && this.$drop.append(this.$search);
					this.$drop.append(this.$results.append(this.fontsAsHtml())).hide();
				},

				fontsAsHtml: function() {
					var i, r, s, style, h = '';
					var systemFonts = this.options.systemFonts;
					var localFonts = this.options.localFonts;
					var googleFonts = this.options.googleFonts;

					for (i = 0; i < systemFonts.length; i++){
						r = this.toReadable(systemFonts[i]);
						s = this.toStyle(systemFonts[i]);
						style = 'font-family:' + s['font-family'];
						if ((localFonts.length > 0 || googleFonts.length > 0) && i == systemFonts.length-1) {
							style += ';border-bottom:1px solid #444'; // Separator after last system font
						}
						h += '<li data-value="'+ systemFonts[i] +'" data-query="' + systemFonts[i].toLowerCase() + '" style="' + style + '">' + r + '</li>';
					}

					for (i = 0; i < localFonts.length; i++){
						r = this.toReadable(localFonts[i]);
						s = this.toStyle(localFonts[i]);
						style = 'font-family:' + s['font-family'];
						if (googleFonts.length > 0 && i == localFonts.length-1) {
							style += ';border-bottom:1px solid #444'; // Separator after last local font
						}
						h += '<li data-value="'+ localFonts[i] +'" data-query="' + localFonts[i].toLowerCase() + '" style="' + style + '">' + r + '</li>';
					}

					for (i = 0; i < googleFonts.length; i++){
						r = this.toReadable(googleFonts[i]);
						s = this.toStyle(googleFonts[i]);
						style = 'font-family:' + s['font-family'] + ';font-weight:' + s['font-weight'] + ';font-style:' + s['font-style'];
						h += '<li data-value="'+ googleFonts[i] +'" data-query="' + googleFonts[i].toLowerCase() + '" style="' + style + '">' + r + '</li>';
					}

					return h;
				},

				toReadable: function(font) {
					return font.replace(/[\+|:]/g, ' ').replace(/(\d+)italic/, '$1 italic');
				},

				toStyle: function(font) {
					var t = font.split(':'), italic = false;
					if (t[1] && /italic/.test(t[1])) {
						italic = true;
						t[1] = t[1].replace('italic','');
					}

					return {'font-family':"'"+this.toReadable(t[0])+"'", 'font-weight': (t[1] || 400), 'font-style': italic?'italic':'normal'};
				},

				getVisibleFonts: function() {
					if(this.$results.is(':hidden')) { return; }

					var fs = this;
					var top = this.$results.scrollTop();
					var bottom = top + this.$results.height();

					if (this.options.lookahead){
						var li = $('li', this.$results).first().height();
						bottom += li * this.options.lookahead;
					}

					$('li:visible', this.$results).each(function(){
						var ft = $(this).position().top+top;
						var fb = ft + $(this).height();

						if ((fb >= top) && (ft <= bottom)){
							fs.addFontLink($(this).data('value'));
						}
					});
				},

				addFontLink: function(font) {
					if (fontsLoaded[font]) { return; }
					fontsLoaded[font] = true;

					if (this.options.googleFonts.indexOf(font) > -1) {
						$('link:last').after('<link href="' + this.options.googleApi + font + '" rel="stylesheet" type="text/css">');
					}
					else if (this.options.localFonts.indexOf(font) > -1) {
						font = this.toReadable(font);
						$('head').append("<style> @font-face { font-family:'" + font + "'; font-style:normal; font-weight:400; src:local('" + font + "'), url('" + this.options.localFontsUrl + font + ".woff') format('woff'); } </style>");
					}
					// System fonts need not be loaded!
				}
			}; // End prototype

			return Fontselect;
		})();

		return this.each(function() {
			// If options exist, merge them
			if (options) { $.extend(settings, options); }

			return new Fontselect(this, settings);
		});
	};
})(jQuery);