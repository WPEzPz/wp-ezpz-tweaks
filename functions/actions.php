<?php
	add_action( 'wp_footer','apply_front_colors',99999);
    
    function apply_front_colors(){
        if(is_admin())
        return;
        (new \EZPZ_TWEAKS\Engine\Features\Dashboard_Colors)->apply_admin_colors();
    }
    
    add_action('admin_footer',function(){
        if($widgets = get_option('wpezpz_dashboard_widgets'))
        return;
        include_once(ABSPATH.'/wp-admin/includes/dashboard.php');
        @wp_dashboard_setup();
        global $wp_meta_boxes;
        $widgets = array_merge($wp_meta_boxes['plugins']['normal']['core'],$wp_meta_boxes['plugins']['side']['core']);
        update_option('wpezpz_dashboard_widgets', $widgets);
    });
?>