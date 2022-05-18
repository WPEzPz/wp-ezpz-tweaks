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


class Type_Select2_Multiple extends Abstract_Custom_CMB2_Type {

    public $cutom_type = 'select2multiple';

    public function __construct() {
        parent::__construct();

        add_filter( 'cmb2_types_esc_' . $this->cutom_type, array( $this, 'custom_type_field_type_escaped_value' ), 10, 3 );
		add_filter( 'cmb2_repeat_table_row_types', array( $this, 'custom_type_field_type_table_row_class' ), 10, 1 );
    }


    public function cmb2_render_custom_type_field_type( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {
		$this->setup_admin_scripts();

		if ( version_compare( CMB2_VERSION, '2.2.2', '>=' ) ) {
			$field_type_object->type = new \CMB2_Type_Select( $field_type_object );
		}

		$a = $field_type_object->parse_args( $this->cutom_type, array(
			'multiple'         => 'multiple',
			'style'            => 'width: 99%',
			'class'            => 'js-example-basic-multiple js-states form-control cmb2_select2_multiselect',
			'name'             => $field_type_object->_name() . '[]',
			'id'               => $field_type_object->_id(),
			'desc'             => $field_type_object->_desc( true ),
			'options'          => $this->get_custom_type_field_type_options( $field_escaped_value, $field_type_object ),
			'data-placeholder' => $field->args( 'attributes', 'placeholder' ) ? $field->args( 'attributes', 'placeholder' ) : $field->args( 'description' ),
		) );

		$attrs = $field_type_object->concat_attrs( $a, array( 'desc', 'options' ) );
		echo sprintf( '<select%s>%s</select>%s', $attrs, $a['options'], $a['desc'] );
	}

    public function cmb2_sanitize_custom_type_callback( $check, $meta_value, $object_id, $field_args ) {
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}

		foreach ( $meta_value as $key => $val ) {
			$meta_value[$key] = array_map( 'sanitize_text_field', $val );
		}

		return $meta_value;
	}

    public function custom_type_field_type_escaped_value( $check, $meta_value, $field_args ) {
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}

		foreach ( $meta_value as $key => $val ) {
			$meta_value[$key] = array_map( 'esc_attr', $val );
		}

		return $meta_value;
	}
    

    public function custom_type_field_type_table_row_class( $check ) {
		$check[] = $this->custom_type;

		return $check;
	}

    public function sort_array_by_array( array $array, array $orderArray ) {
		$ordered = array();

		foreach ( $orderArray as $key ) {
			if ( array_key_exists( $key, $array ) ) {
				$ordered[ $key ] = $array[ $key ];
				unset( $array[ $key ] );
			}
		}

		return $ordered + $array;
	}

    public function get_custom_type_field_type_options( $field_escaped_value = array(), $field_type_object ) {
		$options = (array) $field_type_object->field->options();

		// If we have selected items, we need to preserve their order
		if ( ! empty( $field_escaped_value ) ) {
			$options = $this->sort_array_by_array( $options, $field_escaped_value );
		}

		$selected_items = '';
		$other_items = '';

		foreach ( $options as $option_value => $option_label ) {

			// Clone args & modify for just this item
			$option = array(
				'value' => $option_value,
				'label' => $option_label,
			);

			// Split options into those which are selected and the rest
			if ( in_array( $option_value, (array) $field_escaped_value ) ) {
				$option['checked'] = true;
				$selected_items .= $field_type_object->select_option( $option );
			} else {
				$other_items .= $field_type_object->select_option( $option );
			}
		}

		return $selected_items . $other_items;
	}


    /**
	 * Enqueue scripts and styles
	 */
	public function setup_admin_scripts() {
		wp_enqueue_style( 'select2', plugins_url( 'assets/css/select2.min.css', EZPZ_TWEAKS_PLUGIN_ABSOLUTE ), array( ), '4.0.13' );
		wp_enqueue_script( 'select2', plugins_url( 'assets/js/select2.min.js', EZPZ_TWEAKS_PLUGIN_ABSOLUTE ), array( 'jquery' ), '4.0.13', false );
	}

}