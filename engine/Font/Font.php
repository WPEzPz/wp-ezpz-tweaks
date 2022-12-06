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
namespace EZPZ_TWEAKS\Engine\Font;

class Font {

    public static $customizing_option;

    public function __construct()
    {
        if ( empty( self::$customizing_option ) ) {
            self::$customizing_option = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding' );
        }   
    }

    public function wp_change_login_font() {
		if( !is_user_logged_in() ) {
			$this->render_fonts_css($is_login = true);
		}
	}

    public function enqueue_font( $handel, $css_selector ) {
        $font_key = $this->get_font_key();
		$font_name = $this->get_selected_font($font_key);
		if ( $font_name === 'wp_default_font' ) {
			return;
		}
        wp_add_inline_style( $handel, $css_selector . ' {font-family:"' . esc_html( $font_name ) . '" !important;}' );
    }

    public function change_admin_font() {
		$this->enqueue_font( EZPZ_TWEAKS_TEXTDOMAIN . '-admin-styles', 'body, h1, h2, h3, h4, h5, h6, label, input, textarea, .components-notice, #wpadminbar *:not([class="ab-icon"]), .wp-core-ui, .media-menu, .media-frame *, .media-modal *');
	}

    public function change_adminbar_font() {
		$this->enqueue_font( EZPZ_TWEAKS_TEXTDOMAIN . '-admin-styles', '#wpadminbar *:not([class="ab-icon"])');
	}

    public function change_editor_font() {
		$this->enqueue_font( EZPZ_TWEAKS_TEXTDOMAIN . '-admin-styles', 'body#tinymce.wp-editor, #editorcontainer #content, #wp_mce_fullscreen, .block-editor-writing-flow input, .block-editor-writing-flow textarea, .block-editor-writing-flow p');
	}

    public function change_login_font() {
        $this->enqueue_font( EZPZ_TWEAKS_TEXTDOMAIN . '-login-styles', '*');
	}

    public function remove_google_fonts() {
		// Unload Open Sans
		wp_deregister_style( 'open-sans' );
		wp_register_style( 'open-sans', false );
	}

    public static function get_fonts() {
		$custom_fonts = Custom_Font::get_fonts();
		$google_fonts = Google_Font::get_fonts();

		$s = esc_html__( 'Google Fonts', EZPZ_TWEAKS_TEXTDOMAIN );
		$cs = esc_html__( 'Custom Fonts', EZPZ_TWEAKS_TEXTDOMAIN );
		$csc = esc_html__( 'WordPress Default Font', EZPZ_TWEAKS_TEXTDOMAIN );

		$data[$csc] = ['wp_default_font' => $csc];

		if ( ! empty( $custom_fonts ) ) {
			$data[$cs] = $custom_fonts;
		}

		if ( ! empty( $google_fonts ) ) {
			foreach ( $google_fonts as $font ) {
				$data[$s][\ezpz_tweaks_get_google_font_name($font)] = \ezpz_tweaks_get_google_font_name($font);
			}
		}
		return $data;
	}

	public static function get_fa_fonts() {
		$custom_fonts = Custom_Font::get_fonts();
		$fonts = Custom_Font::custom_persian_fonts();
		$s = esc_html__( 'Persian Fonts', EZPZ_TWEAKS_TEXTDOMAIN );
		$cs = esc_html__( 'Custom Fonts', EZPZ_TWEAKS_TEXTDOMAIN );

		$data = [];

		if ( ! empty( $custom_fonts ) ) {
			$data[$cs] = $custom_fonts;
		}

		if ( ! empty( $fonts ) ) {
			$data[$s] = $fonts;
		}
		return $data;
	}

	public function get_font_key() {
		$font_key = is_admin() ? 'admin-font' : 'editor-font';
		$font_key = get_locale() === 'fa_IR' ? $font_key . 'fa' : $font_key;

		return $font_key;
	}

	public function get_selected_font($font_key = '') {

		if ( empty( $font_key ) ) {
			$font_key = $this->get_font_key();
		}

		if (!isset(self::$customizing_option[$font_key])) {
			return false;
		}

		return self::$customizing_option[$font_key];
	}

	public function render_fonts_css($is_login = false) {
		$font_key       = $this->get_font_key();
		$selected_font  = $this->get_selected_font($font_key);
		
		// nothing to do if selected font is default
		if ( $selected_font === 'wp_default_font' ) {
			return;
		}
		
		// if selected font is google font
        $google_fonts = Google_Font::get_fonts();
		foreach ( $google_fonts as $font ) {
			if ( $selected_font === \ezpz_tweaks_get_google_font_name($font) ) {
				$font_url = Google_Font::get_google_font_url( $selected_font );
				if ($font_key === 'editor-font') {
					wp_enqueue_style( EZPZ_TWEAKS_TEXTDOMAIN . '-editor-google-fonts', $font_url );
				} else if (true) {
					wp_enqueue_style( EZPZ_TWEAKS_TEXTDOMAIN . '-google-fonts', $font_url);
				}
				return;
			}
		}

		// if selected font is custom font
        $custom_fonts = Custom_Font::get_fonts();
		if ( in_array( $selected_font, $custom_fonts ) ) {
			$font_url = Custom_Font::get_custom_font_css( $selected_font );
			echo "<style> $font_url </style>";
			return;
		}

		// if selected font is persian font
        $fa_fonts = Custom_Font::custom_persian_fonts();
		if ( in_array( $selected_font, $fa_fonts ) ) {
			if ( get_locale() === 'fa_IR' ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'remove_google_fonts' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'remove_google_fonts' ) );
			}
		}

		if ($is_login) {
			$this->change_login_font();
		}
		if ($font_key === 'editor-font' && is_admin()) {
			$this->change_editor_font();
		} else if ($font_key === 'admin-font' && is_admin()) {
			$this->change_admin_font();
		} else if ($font_key === 'admin-font') {
			$this->change_adminbar_font();
		} else {
			$font = 'fa';
		}

		return false;
	}
}
