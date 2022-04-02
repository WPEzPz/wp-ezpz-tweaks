<?php
use EZPZ_TWEAKS\Engine\Settings\Render;
use EZPZ_TWEAKS\Engine\Settings\Register;
(new Register)->init();
$page = 'wpezpz-tweaks';
?>

<div class="wrap ezpz-tweaks-tabs">

	<h2>
		<img src="<?php echo EZPZ_TWEAKS_PLUGIN_ROOT_URL . 'assets/img/EzPzTweaks-logo.svg' ?>" style="width: 50px;vertical-align: middle;padding: 15px;">
		<?php echo EZPZ_TWEAKS_NAME ?>
	</h2>

	<!-- Start tabs -->
	<?php
	Render::navigation( $page );
	Render::tabs( $page );
	?>
	<!-- End tabs -->

</div>
