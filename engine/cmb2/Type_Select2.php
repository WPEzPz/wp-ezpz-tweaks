<?php

/**
 * EZPZ_TWEAKS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpzdev.com>
 * @copyright 2022 WP EzPz
 * @license   GPL 3.0+
 * @link      https://wpezpzdev.com/
 */
namespace EZPZ_TWEAKS\Engine\cmb2;


class Type_Select2 extends Abstract_Custom_CMB2_Type {

    public $cutom_type = 'select2';

    public function cmb2_render_custom_type_field_type( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
        $this->setup_admin_scripts();
        if ($field->args('options_type') == 'grouped' ) {
            $options_html = '';
            
            foreach ($field->args('options') as $key => $all_option) {
                $options_html .= '<optgroup label="'. $key .'">';
                foreach ($all_option as $k => $v) {
                    $options_html .= '<option value="'.$k.'" ' . selected( $k, $escaped_value, false) . '>' . $v . '</option>';
                }
                $options_html .= '</optgroup>';
            };
        } else {
            $options_html = '<option></option>' . $field_type_object->concat_items();
        }
        
        echo @$field_type_object->select( array(
			'class'            => 'cmb2_type_select2_select',
			'desc'             => $field_type_object->_desc( true ),
			'options'          => $options_html,
			'data-placeholder' => $field->args( 'attributes', 'placeholder' ) ? $field->args( 'attributes', 'placeholder' ) : $field->args( 'description' ),
		) );

    }

    public function cmb2_sanitize_custom_type_callback( $override_value, $value, $object_id, $field_args ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $saved_value ) {
                $value[$key] = sanitize_text_field( $saved_value );
            }

            return $value;
        }
        
        return sanitize_text_field( $value );
    }

    /**
	 * Enqueue scripts and styles
	 */
	public function setup_admin_scripts() {
		wp_enqueue_style( 'select2', plugins_url( 'assets/css/select2.min.css', EZPZ_TWEAKS_PLUGIN_ABSOLUTE ), array( ), '4.0.13' );
		wp_enqueue_script( 'select2', plugins_url( 'assets/js/select2.min.js', EZPZ_TWEAKS_PLUGIN_ABSOLUTE ), array( 'jquery' ), '4.0.13', false );
	}

}