<?php

/**
 * Adds visibility options to all widgets: Hide or show widgets only when
 * specific conditions are met.
 *
 * @since 1.6
 */
class CustomSidebarsVisibility {

	/**
	 * Returns the singleton object.
	 */
	public static function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new CustomSidebarsVisibility();
		}

		return $Inst;
	}

	/**
	 * Constructor is private -> singleton.
	 */
	private function __construct() {
		if ( is_admin() ) {
			// in_widget_form: Add our button inside each widget
			add_action(
				'in_widget_form',
				array( $this, 'admin_widget_button' ),
				10, 3
			);

			// Load the javascript support file for this module
			add_action(
				'admin_enqueue_scripts',
				array( $this, 'admin_scripts' )
			);
		}
	}

	/**
	 * Action handler for 'in_widget_form'
	 */
	public function admin_widget_button( $widget, $return, $instance ) {
		?>
		<a href="#" class="button csb-visibility"><span class="dashicons dashicons-visibility"></span> <?php _e( 'Visibility', CSB_LANG ); ?></a>
		<?php
	}

	/**
	 * Load the javascript support file for the visibility module.
	 */
	public function admin_scripts() {
		wp_enqueue_script(
			'csb-visibility',
			CSB_JS_URL . 'visibility.js',
			array( 'jquery' ),
			'1.6',
			true
		);

		wp_enqueue_style(
			'csb-visibility',
			CSB_CSS_URL . 'visibility.css',
			array(),
			'1.6'
		);
	}
};