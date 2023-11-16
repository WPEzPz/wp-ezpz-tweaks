<?php
/**
 * EZPZ_TWEAKS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpzdev.com>
 * @license   GPL 2.0+
 * @link      https://wpezpzdev.com/
 */

/**
 * Get the settings of the plugin in a filterable way
 *
 * @since 1.0.0
 * @return array
 */
function ezpz_tweaks_get_settings(): array {
	return apply_filters( 'ezpz_tweaks_get_settings', get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-settings' ) );
}

/**
 * @return array
 */
function ezpz_tweaks_wp_roles_array(): array {
	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}

	$editable_roles = get_editable_roles();

	foreach ( $editable_roles as $role => $details ) {
		$roles[ esc_attr( $role ) ] = translate_user_role( $details['name'] );
	}

	return $roles;
}

function ezpz_tweaks_get_google_font_name( $font ) {
	$font 		 = str_replace( '+', ' ', $font );
	$font 		 = explode( ':', $font );
	
	return $font[0];
}

/**
 * Recursive sanitation for an array
 * 
 * @param $array
 *
 * @return mixed
 */
function ezpz_tweaks_recursive_sanitize( $array ) {
    foreach ( $array as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = ezpz_tweaks_recursive_sanitize( $value );
        } else {
            $value = sanitize_text_field( $value );
        }
    }

    return $array;
}