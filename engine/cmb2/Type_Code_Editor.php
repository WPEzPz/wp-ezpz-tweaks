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

use \EZPZ_TWEAKS\Lib\CSSTidy\csstidy as CSSTidy;
class Type_Code_Editor extends Abstract_Custom_CMB2_Type {

    public $cutom_type = 'code-editor';

    public function cmb2_render_custom_type_field_type( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
        //options used for code mirror
        $cm_options = $field->args['options'];

        
        
        //enqueue scripts and styles used for codeeditor field
        $this->load_scripts();

        $value = wp_parse_args($escaped_value, array(
            'cm_code' => ''
        ));

        $this->render($cm_options, $field_type_object, $value);
    }
    
    public function cmb2_sanitize_custom_type_callback( $override_value, $value, $object_id, $field_args  ) {
        require_once( EZPZ_TWEAKS_PLUGIN_ROOT . 'inc/csstidy/csstidy.php' );

        
        $csstidy = new CSSTidy();

        $csstidy->set_cfg('optimise_shorthands', 2);
        $csstidy->set_cfg('template', 'high');

        // Parse the CSS
        $csstidy->parse($value['cm_code']);

        $css_code_opt = $csstidy->print->plain();
        $value['cm_code'] = $css_code_opt;

        return $value;
    }

    private function render($cm_options, $field_type, $value) {
        ?>

        <textarea class="codemirror_txtarea <?php echo $cm_options['classes']; ?>"
            role="wp_codemirror"
            name="<?php echo $field_type->_name('[cm_code]') ?>"
            id="<?php echo $field_type->_id('_cm_code') ?>" 
            value=""><?php echo sanitize_textarea_field($value['cm_code'])  ?>
        </textarea>
        <?php
    }

    private function load_scripts() {
        wp_enqueue_style('wp-codemirror');
        wp_enqueue_script('wp-codemirror');
    }
}