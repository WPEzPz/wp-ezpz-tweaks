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


class Type_Select_Multiple extends Abstract_Custom_CMB2_Type {

    public $cutom_type = 'select_multiple';

    public function cmb2_render_custom_type_field_type( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
        $select_multiple = '<select class="widefat" multiple name="' . $field->args['_name'] . '[]" id="' . $field->args['_id'] . '"';
        foreach ( $field->args['attributes'] as $attribute => $value ) {
            $select_multiple .= " $attribute=\"$value\"";
        }
        $select_multiple .= ' />';
    
        foreach ( $field->options() as $value => $name ) {
            $selected = ( $escaped_value && in_array( $value, $escaped_value ) ) ? 'selected="selected"' : '';
            $select_multiple .= '<option class="cmb2-option" value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
        }
    
        $select_multiple .= '</select>';
        $select_multiple .= $field_type_object->_desc( true );
    
        echo $select_multiple; // WPCS: XSS ok.
    }

    public function cmb2_sanitize_custom_type_callback( $override_value, $value ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $saved_value ) {
                $value[$key] = sanitize_text_field( $saved_value );
            }

            return $value;
        }

        return;
    }

}