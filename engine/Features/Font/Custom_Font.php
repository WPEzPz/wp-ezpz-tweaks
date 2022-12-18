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
namespace EZPZ_TWEAKS\Engine\Features\Font;


class Custom_Font {

    public static $customizing_option;

    public static function get_custom_font_css( $selected_font ) {

		$custom_fonts = self::$customizing_option['custom_fonts_repeat_group'];
		if ( empty( $custom_fonts ) ) {
            return;
        }
        foreach ( $custom_fonts as $custom_font ) {
            if ( $custom_font['custom_font_name'] !== $selected_font ) {
                return;
            }
            return '@font-face {
                font-family: "' . $custom_font['custom_font_name'] . '";
                src: url("' . $custom_font['custom_font_woff2'] . '") format("woff2"),
                    url("' . $custom_font['custom_font_woff'] . '") format("woff"),
                    url("' . $custom_font['custom_font_ttf'] . '") format("truetype");
                font-weight: normal;
                font-style: normal;
            }';
        }
    }

    public static function get_fonts() {
        if ( empty( self::$customizing_option ) ) {
            self::$customizing_option = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding' );
        }
		$custom_fonts_repeat_group = self::$customizing_option['custom_fonts_repeat_group'] ?? [];
		$fonts = [];

		foreach ( $custom_fonts_repeat_group as $font ) {
			if ( empty($font['custom_font_name']) ) {
				continue;
			}

			$fonts[$font['custom_font_name']] = $font['custom_font_name'];
		}

		return $fonts;
	}

    public static function custom_persian_fonts() {
		$fonts = array(
			'wp-default'  => 'پیشفرض وردپرس',
			'Vazir'       => 'وزیر',
			'Estedad'     => 'استعداد',
			'Shabnam'     => 'شبنم',
			'Samim'       => 'صمیم',
		);

		return $fonts;
	}
}