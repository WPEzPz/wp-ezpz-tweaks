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
namespace EZPZ_TWEAKS\Engine\cmb2;


abstract class Abstract_Custom_CMB2_Type {

    public $cutom_type;

    public function __construct() {
        add_filter( 'cmb2_render_' . $this->cutom_type, array( $this, 'cmb2_render_custom_type_field_type' ), 10, 5 );
        add_filter( 'cmb2_sanitize_' . $this->cutom_type, array( $this, 'cmb2_sanitize_custom_type_callback' ), 10, 4 );
    }

    abstract public function cmb2_render_custom_type_field_type( $field, $escaped_value, $object_id, $object_type, $field_type_object );

    abstract public function cmb2_sanitize_custom_type_callback( $override_value, $value );

}