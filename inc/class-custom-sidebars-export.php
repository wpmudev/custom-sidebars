<?php

/**
 * Provides functionality to export and import sidebar settings.
 *
 * @since 1.6
 */
class CustomSidebarsExport {

	private $csb = null;

	/**
	 * Returns the singleton object.
	 *
	 * @since  1.6
	 */
	public static function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new CustomSidebarsExport();
		}

		return $Inst;
	}

	/**
	 * Constructor is private -> singleton.
	 *
	 * @since  1.6
	 */
	private function __construct() {
		if ( is_admin() ) {
			$this->csb = CustomSidebars::instance();

			add_action(
				'current_screen',
				array( $this, 'do_actions' )
			);
		}
	}

	/**
	 * When the custom sidebars section is visible we see if export-action
	 * needs to be processed.
	 *
	 * @since  1.6.0
	 */
	public function do_actions( $current_screen ) {
		if ( $this->csb->get_screen_id() != $current_screen->id ) {
			return;
		}

		if ( isset( $_POST['export-sidebars'] ) ) {
			$this->download_export_file();
		}
	}

	/**
	 * Collects the plugin details for export.
	 *
	 * @since  1.6.0
	 */
	private function get_export_data() {
		global $wp_registered_widget_controls, $wp_registered_widgets;

		$data = array();
		$data['options'] = $this->csb->get_sidebar_options();
		$data['sidebars'] = $this->csb->get_custom_sidebars();
		$data['widgets'] = array();
		foreach ( $this->csb->get_default_sidebars() as $sidebar => $widgets ) {
			if ( is_array( $widgets ) ) {
				$data['widgets'][ $sidebar ] = array();
				foreach ( $widgets as $widget_id ) {
					if ( isset( $wp_registered_widgets[$widget_id] ) ) {
						$item = array();
						//$widget = $wp_registered_widget_controls[$widget_id];
						$widget = $wp_registered_widgets[$widget_id];
						/*
						todo: Get the instance data of the widget! Export the instance data...
						$all_instances = $widget->get_settings();

						if ( -1 == $widget_args['number'] ) {
							// We echo out a form where 'number' can be set later
							$this->_set('__i__');
							$instance = array();
						} else {
							$this->_set($widget_args['number']);
							$instance = $all_instances[ $widget_args['number'] ];
						}
						*/
						$data['widgets'][ $sidebar ][ $widget_id . '---' ] = $widget;
					}
				}
			} else {
				$data['widgets'][ $sidebar ] = $widgets;
			}
		}
		return $data;
	}

	/**
	 * Generates the export file.
	 *
	 * @since  1.6.0
	 */
	private function download_export_file() {
		$data = $this->get_export_data();
		//header( 'Content-type: application/json' );
		//header( 'Content-Disposition: attachment; filename="test.json"' );
		//echo json_encode( $data );

		global $wp_registered_widget_controls;

		// ----- DEBUG START
		function_exists( 'wp_describe' ) && wp_describe( 'class-custom-sidebars-export.php:86', $data );
		// ----- DEBUG END


		die();
	}

};