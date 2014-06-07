<?php

/**
 * Provides functionality to export and import sidebar settings.
 *
 * @since 1.6
 */
class CustomSidebarsExport {

	// Main instance of the custom-sidebars class.
	private $csb = null;

	// Holds the contents of the import-file during preview/import.
	private $import_data = '';


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

		if ( isset( $_POST['upload-import-file'] ) ) {
			$this->read_import_file();
		}
	}

	/**
	 * Collects the plugin details for export.
	 *
	 * @since  1.6.0
	 */
	private function get_export_data() {
		global $wp_registered_widgets, $wp_version;

		$theme = wp_get_theme();

		$data = array();
		// Add some meta-details to the export file.
		$data['meta'] = array(
			'created' => time(),
			'wp_version' => $wp_version,
			'csb_version' => CSB_VERSION,
			'theme_name' => $theme->get( 'Name' ),
			'theme_version' => $theme->get( 'Version' ),
			'description' => htmlspecialchars( @$_POST['export-description'] ),
		);

		// Export the custom sidebars
		$data['sidebars'] = $this->csb->get_custom_sidebars();

		// Export the sidebar options (e.g. default replacement)
		$data['options'] = $this->csb->get_sidebar_options();

		/*
		 * Export all widget options.
		 *
		 * $wp_registered_widgets contains all widget-instances that were placed
		 * inside a sidebar. So we loop this array and fetch each widgets
		 * options individually:
		 *
		 * Widget options are saved inside options table with option_name
		 * "widget_<widget-slug>"; the options can be an array, e.g.
		 * "widget_search" contains options for all widget instances in any
		 * sidebar. When we place 2 search widgets in different sidebars there
		 * will be a list with two option-arrays.
		 *
		 */
		$data['widgets'] = array();
		foreach ( $this->csb->get_default_sidebars() as $sidebar => $widgets ) {
			if ( is_array( $widgets ) ) {
				$data['widgets'][ $sidebar ] = array();
				foreach ( $widgets as $widget_id ) {
					if ( isset( $wp_registered_widgets[$widget_id] ) ) {
						$item = array();
						$widget = reset( $wp_registered_widgets[$widget_id]['callback'] );
						$settings = $widget->get_settings();
						$data['widgets'][ $sidebar ][ $widget_id ] = $settings[ $widget->number ];
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
		$filename = 'sidebars.' . date( 'Y-m-d.H-i-s' ) . '.json';
		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo json_encode( $data );

		die();
	}

	/**
	 * Checks if a valid export-file was uploaded and stores the file contents
	 * inside $this->import_data. The data is de-serialized.
	 *
	 * @since  1.6.0
	 */
	private function read_import_file() {
		$error = false;

		if ( is_array( $_FILES['data'] ) ) {
			switch ( $_FILES['data']['error'] ) {
				case UPLOAD_ERR_OK:
					break;

				case UPLOAD_ERR_NO_FILE:
					$error = 'No file was uploaded';
					break;

				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$error = 'Import file is too big';
					break;

				default:
					$error = 'Something went wrong';
					break;
			}

			$finfo = new finfo( FILEINFO_MIME_TYPE );
			$infos = $finfo->file( $_FILES['data']['tmp_name'] );
			if (
				false === $error &&
				$finfo->file( $_FILES['data']['tmp_name'] ) !== 'text/plain'
			) {
				$error = 'This is no valid import file';
			}

			if ( false === $error ) {
				$content = file_get_contents( $_FILES['data']['tmp_name'] );
				$data = json_decode( $content, true );

				if (
					isset( $data['meta'] ) &&
					isset( $data['sidebars'] ) &&
					isset( $data['options'] ) &&
					isset( $data['widgets'] )
				) {
					$this->import_data = $data;
				} else {
					$error = 'The import file was not readable';
				}
			}

			if ( false !== $error ) {
				$this->csb->set_error( __( $error, CSB_LANG ) );
			}
		}
	}

	/**
	 * Returns the contents of the uploaded import file for preview or import.
	 *
	 * @since  1.6.0
	 */
	public function get_import_data() {
		return $this->import_data;
	}

};