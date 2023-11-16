<?php
/**
 * EZPZ_TWEAKS
 * Related feature: Custom Admin CSS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpz.dev>
 * @license   GPL 2.0+
 * @link      https://wpezpzdev.com/
 */

namespace EZPZ_TWEAKS\Engine\Features;

class Dashboard_Widgets {
    /**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() {

        add_action( 'admin_init', array( $this, 'dashboard_widgets_options' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );

	}


	/**
	 * Related feature: Remove Dashboard Widgets
	 */
	public function get_dashboard_widgets() {
		global $wp_meta_boxes;

		$widgets = array();

		if ( isset( $wp_meta_boxes['dashboard'] ) ) {
			foreach( $wp_meta_boxes['dashboard'] as $context => $data ) {
				foreach( $data as $priority => $data ) {
					foreach( $data as $widget=>$data ) {
						$widgets[$widget] = [
							'id' 	   => $widget,
							'title'    => strip_tags( preg_replace( '/ <span.*span>/im', '', $data['title'] ) ),
							'context'  => $context,
							'priority' => $priority
						];
					}
				}
			}
		}

		return $widgets;
	}

	/**
	 * Related feature: Remove Dashboard Widgets
	 */
	public function dashboard_widgets_options() {
		$widgets = get_option('ezpz_tweaks_dashboard_widgets');
		$options = [];

		if( $widgets ) {
			foreach( $widgets as $widget ) {
				$options[$widget['id']] = $widget['title'];
			}
		}

		return $options;
	}

	/**
	 * Related feature: Remove Dashboard Widgets
	 */
	public function remove_dashboard_widgets() {
		$widgets = $this->get_dashboard_widgets();

		update_option('ezpz_tweaks_dashboard_widgets', $widgets);

		if ( isset( $this->customizing_option['remove_dashboard_widgets'] ) ) {
			global $wp_meta_boxes;

			$selected_widgets = $this->customizing_option['remove_dashboard_widgets'];

			foreach ( $widgets as $widget ) {
				if( in_array( $widget['id'], $selected_widgets ) ) {
					unset( $wp_meta_boxes['dashboard'][$widget['context']][$widget['priority']][$widget['id']] );
				}
			}
		}
	}

}