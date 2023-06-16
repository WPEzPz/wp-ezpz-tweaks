<?php

use EZPZ_TWEAKS\Engine\Settings\Settings;

?>
<div class="ezpz-full-w ezpz-header">

    <div class="ezpz-header-topbar">
        <div class="ezpz-brand-wrapper">
            <img src="<?php echo EZPZ_TWEAKS_PLUGIN_ROOT_URL . 'assets/img/EzPzTweaks-logo.svg' ?>">
            <h2><?php echo EZPZ_TWEAKS_NAME ?></h2>
        </div>
        <div class="ezpz-search-wrapper">
            <div class="ezpz-search">
                <script>
                    var searchData = <?php echo json_encode(Settings::get_search_data()) ?>
                </script>
                <input type="search" placeholder="<?php _e('Search Options') ?>">
                <div id="ezpz-search-res"></div>
            </div>
            <a href="#" class="ezpz-help"><img></a>
        </div>
    </div>
</div>