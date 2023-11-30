<?php
/**
 * EZPZ_TWEAKS
 * Related feature: Dashboard Colors
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpzdev.com>
 * @license   GPL 2.0+
 * @link      https://wpezpzdev.com/
 */

namespace EZPZ_TWEAKS\Engine\Features;

class Dashboard_Colors {

    protected $customizing_option;

    public const DEFAULT_COLORS = [
        'base'         => '#1d2327',
        'text'         => '#f0f0f1',
        'icon'         => '#f0f6fc99',
        'highlight'    => '#2271b1',
        'notification' => '#d63638',
    ];

    protected const COLORS_FIELDS = [
        'base'         => 'admin_colors__base',
        'text'         => 'admin_colors__text',
        'icon'         => 'admin_colors__icon',
        'highlight'    => 'admin_colors__highlight',
        'notification' => 'admin_colors__notification',
    ];

    public function __construct() {
        $this->customizing_option = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding' );
    }

    /**
     * Initialize the class.
     * 
     * @return void
     */
    public function initialize() {

        foreach( self::COLORS_FIELDS as $key => $value ) {
            add_action( 'cmb2_save_field_' . $value, [ $this, 'onsave_admin_color' ], 10, 3 );
        }

        add_action( 'admin_footer', [ $this, 'apply_admin_colors' ] );
    }

    public function onsave_admin_color( $updated, $action, $field ) {
        $this->update_admin_color_option(
            $field->args['id'],
            $field->value,
            $action
        );
    }

    private function update_admin_color_option( $key, $value, $action ) {
        if ( $action === 'removed' ) {
            // do nothing if nothing to remove
            if ( ! isset( $this->customizing_option[$key] ) ) {
                return;
            }
        }

        $key = str_replace( 'admin_colors__', '', $key );
        
        $colors = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-admin_colors', self::DEFAULT_COLORS );
        $colors[$key] = sanitize_hex_color( $value ) ?: self::DEFAULT_COLORS[$key];
        update_option( EZPZ_TWEAKS_TEXTDOMAIN . '-admin_colors', $colors );

        return true;
    }

    public function apply_admin_colors() {
        if ( isset( $_POST['submit-cmb'] ) ) {
            $colors = [];
            foreach ( self::COLORS_FIELDS as $key => $value ) {
                $colors[$key] =  isset( $_POST[$value] ) && !empty( sanitize_hex_color( $_POST[$value] ) ) && sanitize_hex_color( $_POST[$value] ) !== '#' ? sanitize_hex_color( $_POST[$value] ) : self::DEFAULT_COLORS[$key];
            }
        } else {
            $colors = get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-admin_colors', self::DEFAULT_COLORS );
        }

        $colors = array_map( 'sanitize_hex_color', $colors );

        $css_string = '';

        $css_string .= '<!--' . EZPZ_TWEAKS_TEXTDOMAIN . '-->';
        $css_string .= '<style type="text/css">';

        // base color
        $css_string .= "#wpadminbar { background-color: {$colors['base']}; }";
        $css_string .= "#adminmenu, #adminmenu .wp-submenu, #adminmenuback, #adminmenuwrap { background-color: {$colors['base']}; }";

        // text color
        $css_string .= "#adminmenu a { color: {$colors['text']}; }";
        $css_string .= "#wpadminbar .ab-empty-item, #wpadminbar a.ab-item, #wpadminbar>#wp-toolbar span.ab-label, #wpadminbar>#wp-toolbar span.noticon { color: {$colors['text']}; }";

        // highlight color
        $css_string .= "#adminmenu li.current a.menu-top, #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, #adminmenu li.wp-has-current-submenu .wp-submenu .wp-submenu-head, .folded #adminmenu li.current.menu-top {
            background-color: {$colors['highlight']};
        }";

        $css_string .=".wp-core-ui .button, .wp-core-ui .button-secondary {
            color: {$colors['highlight']};
            border-color: {$colors['highlight']};
        }";
        $css_string .= ".wp-core-ui .button-primary {
            color: #fff;
            background-color: {$colors['highlight']};
            border-color: {$colors['highlight']};
        }";
        $css_string .= ".wp-core-ui .button-primary:hover, .wp-core-ui .button-primary:focus {
            background-color: {$colors['highlight']};
            border-color: {$colors['highlight']};
        }";


        // icon color
        $css_string .= "#wpadminbar .ab-icon, #wpadminbar .ab-icon:before, #wpadminbar .ab-item:before, #wpadminbar .ab-item:after { color: {$colors['icon']}; }";
        $css_string .= "#adminmenu div.wp-menu-image:before { color: {$colors['icon']}; }";
        $css_string .= "#collapse-button { color: {$colors['icon']}; }";

        // notification color
        $css_string .= "#adminmenu .menu-counter, #adminmenu .awaiting-mod, #adminmenu .update-plugins { background-color: {$colors['notification']}; }";



        $css_string .= "";
        $css_string .= "";
        $css_string .= '</style>';

        echo $css_string;
    }


}