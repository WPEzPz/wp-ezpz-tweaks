<?php
	add_action( 'wp_footer','apply_front_colors',99999);
    
    function apply_front_colors(){
        if(is_admin())
        return;
        (new \EZPZ_TWEAKS\Engine\Features\Dashboard_Colors)->apply_admin_colors();
    }
?>