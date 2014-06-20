<?php

add_action( 'cs_init', array( 'CustomSidebarsExport', 'instance' ) );

/**
 * Provides functionality to export and import sidebar settings.
 *
 * @since 1.6
 */
class CustomSidebarsExport extends CustomSidebars {

	// Holds the contents of the import-file during preview/import.
	private $import_data = null;

	// Used after preview. This holds only the items that were selected for import.
	private $selected_data = null;


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
			add_action(
				'current_screen',
				array( $this, 'do_actions' )
			);

			// Show the "Export/Import" button in the widget area.
			add_action(
				'cs_widgets_additional_buttons',
				array( $this, 'render_button' )
			);

			// Add new "Export/Import" tabs.
			add_action(
				'cs_render_tab_content',
				array( $this, 'render_page' )
			);
		}
	}

	/**
	 * Output the Export/Import button on the widget screen.
	 *
	 * @since  1.6
	 */
	public function render_button() {
		?>
		<a href="#" class="cs-action btn-export"><?php _e( 'Import / Export Sidebars', CSB_LANG ); ?></a>
		<?php
	}

	/**
	 * Allows us to render the export/import option pages.
	 *
	 * @since  1.6.0
	 * @param  string $active The currently active tab.
	 */
	public function render_page( $active ) {
		switch ( $active ) {
			case 'export':
				include CSB_VIEWS_DIR . 'export.php';
				break;

			case 'import':
				include CSB_VIEWS_DIR . 'import.php';
				break;
		}
	}

	/**
	 * When the custom sidebars section is visible we see if export-action
	 * needs to be processed.
	 *
	 * @since  1.6.0
	 */
	public function do_actions( $current_screen ) {
		if ( isset( $_POST['export-sidebars'] ) ) {
			$this->download_export_file();
		}

		if ( isset( $_POST['upload-import-file'] ) ) {
			$this->read_import_file();
		}
		if ( isset( $_POST['process-import-data'] ) ) {
			$this->prepare_import_data();
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

		// Export the custom sidebars.
		$data['sidebars'] = self::get_custom_sidebars();

		// Export the sidebar options (e.g. default replacement).
		$data['options'] = self::get_options();

		// Export category-information.
		$data['categories'] = get_categories( array( 'hide_empty' => 0 ) );

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
		 */
		$data['widgets'] = array();
		foreach ( self::get_sidebar_widgets() as $sidebar => $widgets ) {
			if ( 'wp_inactive_widgets' === $sidebar ) { continue; }
			if ( is_array( $widgets ) ) {
				$data['widgets'][ $sidebar ] = array();
				foreach ( $widgets as $widget_id ) {
					if ( isset( $wp_registered_widgets[$widget_id] ) ) {
						$item = array();
						$widget = reset( $wp_registered_widgets[$widget_id]['callback'] );
						$settings = $widget->get_settings();
						$data['widgets'][ $sidebar ][ $widget_id ] = array(
							'name' => @$widget->name,
							'classname' => get_class( $widget ),
							'id_base' => @$widget->id_base,
							'description' => @$widget->description,
							'settings' => $settings[ @$widget->number ],
						);
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

			if ( false === $error ) {
				$content = file_get_contents( $_FILES['data']['tmp_name'] );
				$data = json_decode( $content, true );

				if (
					is_array( $data['meta'] ) &&
					is_array( $data['sidebars'] ) &&
					is_array( $data['options'] ) &&
					is_array( $data['widgets'] ) &&
					is_array( $data['categories'] )
				) {
					$data['meta']['filename'] = $_FILES['data']['name'];
					$data['ignore'] = array();
					$this->import_data = $data;

					// Remove details that does not exist on current blog.
					$this->prepare_data();
				} else {
					$error = 'The import file was not readable';
				}
			}

			if ( false !== $error ) {
				self::set_error( __( $error, CSB_LANG ) );

				// Redirect the user to the "Upload export file" section again.
				$_GET['p'] = 'export';
			}
		}
	}

	/**
	 * Loads the import-data into the $this->import_data property.
	 * The data was prepared by the import-preview screen.
	 *
	 * @since  1.6.0
	 */
	private function prepare_import_data() {
		$error = false;
		$data = json_decode( base64_decode( @$_POST['import_data'] ), true );

		if (
			is_array( $data['meta'] ) &&
			is_array( $data['sidebars'] ) &&
			is_array( $data['options'] ) &&
			is_array( $data['widgets'] ) &&
			is_array( $data['categories'] )
		) {
			$data['ignore'] = array();
			$this->import_data = $data;

			// Remove details that does not exist on current blog.
			$this->prepare_data();

			// "selected_data" only contains the items that were selected for import.
			$this->selected_data = $this->import_data;
			unset( $this->selected_data['meta'] );
			unset( $this->selected_data['categories'] );
			unset( $this->selected_data['ignore'] );

			if ( ! isset( $_POST['import_plugin_config'] ) ) {
				unset( $this->selected_data['options'] );
			}
			if ( ! isset( $_POST['import_widgets'] ) ) {
				unset( $this->selected_data['widgets'] );
			} else {
				foreach ( $this->selected_data['widgets'] as $id => $widgets ) {
					$key = 'import_sb_' . $id;
					if ( ! isset( $_POST[ $key ] ) ) {
						unset( $this->selected_data['widgets'][ $id ] );
					}
				}
			}
			foreach ( $this->selected_data['sidebars'] as $id => $sidebar ) {
				$key = 'import_sb_' . $sidebar['id'];
				if ( ! isset( $_POST[ $key ] ) ) {
					unset( $this->selected_data['sidebars'][ $id ] );
				}
			}

			// Finally: Import the config!
			$this->do_import();
		} else {
			self::set_error(
				__( 'Something unexpected happened and we could not finish the import. Please try again.', CSB_LANG )
			);
		}

		// Redirect the user to the "Upload export file" section again.
		//$_GET['p'] = 'export';
	}

	/**
	 * Loops through the import data array and removes configuration which is
	 * not relevant for the current blog. I.e. posttypes that are not registered
	 * or categories that do not match the current blog.
	 *
	 * @since  1.6.0
	 */
	private function prepare_data() {
		global $wp_registered_widgets;
		$theme_sidebars = self::get_theme_sidebars();
		$valid_categories = array();
		$valid_sidebars = array();
		$valid_widgets = array();

		// =====
		// Normalize the sidebar list (change numeric index to sidebar-id).
		$sidebars_remapped = array();
		foreach ( $this->import_data['sidebars'] as $sidebar ) {
			$sidebars_remapped[ $sidebar['id'] ] = $sidebar;
		}
		$this->import_data['sidebars'] = $sidebars_remapped;

		// =====
		// Get a list of existing/valid sidebar-IDs.
		$valid_sidebars = array_merge(
			array_keys( $theme_sidebars ),
			array_keys( $this->import_data['sidebars'] )
		);

		// =====
		// Check for theme-sidebars that do not exist.
		foreach ( $this->import_data['options']['modifiable'] as $id => $sb_id ) {
			if ( ! isset( $theme_sidebars[ $sb_id ] ) ) {
				if ( ! isset( $this->import_data['ignore']['sidebars'] ) ) {
					$this->import_data['ignore']['sidebars'] = array();
				}
				$this->import_data['ignore']['sidebars'][] = $sb_id;
				unset( $this->import_data['options']['modifiable'][ $id ] );
			}
		}

		// =====
		// Remove invalid sidebars from the default replacement options.
		foreach ( array( 'defaults', 'post_type_pages', 'category_posts', 'category_pages' ) as $key ) {
			foreach ( $this->import_data['options'][ $key ] as $id => $list ) {
				$list = $this->_remove_sidebar_from_list( $list, $valid_sidebars );
				$this->import_data['options'][ $key ][ $id ] = $list;
			}
		}
		foreach ( array( 'blog', 'tags', 'authors', 'search', 'date' ) as $key ) {
			$list = $this->import_data['options'][ $key ];
			$list = $this->_remove_sidebar_from_list( $list, $valid_sidebars );
			$this->import_data['options'][ $key ] = $list;
		}

		// =====
		// Check for missing/different categories.
		foreach ( get_categories( array( 'hide_empty' => 0 ) ) as $cat ) {
			$valid_categories[ $cat->term_id ] = $cat;
		}
		foreach ( $this->import_data['categories'] as $infos ) {
			$id = $infos['term_id'];
			if (
				empty( $valid_categories[ $id ] ) ||
				$valid_categories[ $id ]->slug != $infos['slug']
			) {
				if ( ! isset( $this->import_data['ignore']['categories'] ) ) {
					$this->import_data['ignore']['categories'] = array();
				}
				$this->import_data['ignore']['categories'][] = $infos['name'];
				unset( $this->import_data['categories'][ $id ] );

				// Remove the categories from the config array.
				unset( $this->import_data['options']['category_posts'][ $id ] );
				unset( $this->import_data['options']['category_pages'][ $id ] );
			}
		}

		// =====
		// Remove missing widgets from import data.
		foreach ( $wp_registered_widgets as $widget ) {
			$classname = get_class( $widget['callback'][0] );
			$valid_widgets[ $classname ] = true;
		}
		foreach ( $this->import_data['widgets'] as $sb_id => $sidebar ) {
			foreach ( $sidebar as $id => $widget_instance ) {
				$instance_class = $widget_instance['classname'];
				$exists = (true === @$valid_widgets[ $instance_class ]);
				if ( ! $exists ) {
					if ( ! isset( $this->import_data['ignore']['widgets'] ) ) {
						$this->import_data['ignore']['widgets'] = array();
					}
					$this->import_data['ignore']['widgets'][] = $widget_instance['name'];
					unset( $sidebar[ $id ] );
				}
			}
			$this->import_data['widgets'][ $sb_id ] = $sidebar;
		}
	}

	/**
	 * Helper function that is used by prepare_data.
	 *
	 * @since  1.6.0
	 */
	private function _remove_sidebar_from_list( $list, $valid_list ) {
		foreach ( $list as $id => $value ) {
			if ( ! in_array( $value, $valid_list ) ) {
				unset( $list[ $id ] );
			} else if ( ! in_array( $id, $valid_list ) ) {
				unset( $list[ $id ] );
			}
		}
		return $list;
	}

	/**
	 * Returns the contents of the uploaded import file for preview or import.
	 *
	 * @since  1.6.0
	 */
	public function get_import_data() {
		return $this->import_data;
	}

	/**
	 * Process the import data provided in $this->import_data.
	 * Save the configuration to database.
	 *
	 * @since  1.6.0
	 */
	private function do_import() {
		global $wp_registered_widgets;
		$data = $this->selected_data;
		$msg = array();

		// =====================================================================
		// Import custom sidebars

		$sidebars = self::get_custom_sidebars();
		$sidebar_count = 0;
		// First replace existing sidebars.
		foreach ( $sidebars as $idx => $sidebar ) {
			$sb_id = $sidebar['id'];
			if ( isset( $data['sidebars'][ $sb_id ] ) ) {
				$new_sidebar = $data['sidebars'][ $sb_id ];
				$sidebars[ $idx ] = array(
					'name' => @$new_sidebar['name'],
					'id' => $sb_id,
					'description' => @$new_sidebar['description'],
					'before_widget' => @$new_sidebar['before_widget'],
					'after_widget' => @$new_sidebar['after_widget'],
					'before_title' => @$new_sidebar['before_title'],
					'after_title' => @$new_sidebar['after_title'],
				);
				$sidebar_count += 1;
				unset( $data['sidebars'][ $sb_id ] );
			}
		}
		// Second add new sidebars.
		foreach ( $data['sidebars'] as $sb_id => $new_sidebar ) {
			$sidebars[] = array(
				'name' => @$new_sidebar['name'],
				'id' => $sb_id,
				'description' => @$new_sidebar['description'],
				'before_widget' => @$new_sidebar['before_widget'],
				'after_widget' => @$new_sidebar['after_widget'],
				'before_title' => @$new_sidebar['before_title'],
				'after_title' => @$new_sidebar['after_title'],
			);
			$sidebar_count += 1;
		}
		if ( $sidebar_count > 0 ) {
			self::set_custom_sidebars( $sidebars );
			$msg[] = sprintf( __( 'Imported %d custom sidebar(s)!', CSB_LANG ), $sidebar_count );
		}


		// =====================================================================
		// Import plugin settings
		if ( ! empty( $data['options'] ) ) {
			self::set_options( $data['options'] );
			$msg[] = __( 'Plugin options were imported!', CSB_LANG );
		}


		// =====================================================================
		// Import widgets
		$widget_count = 0;
		$def_sidebars = wp_get_sidebars_widgets();
		$widget_list = array();
		$orig_POST = $_POST;
		// First replace existing sidebars.
		foreach ( $data['widgets'] as $sb_id => $sidebar ) {
			// --- 1. Remove all widgets from the sidebar

			// @see wp-admin/includes/ajax-actions.php : function wp_ajax_save_widget()
			// Empty the sidebar, in case it contains widgets.
			$old_widgets = @$def_sidebars[ $sb_id ];
			$def_sidebars[ $sb_id ] = array();
			wp_set_sidebars_widgets( $def_sidebars );

			// Also remove the widget-instances from wp-option table.
			if ( ! is_array( $old_widgets ) ) {
				$old_widgets = array();
			}
			foreach ( $old_widgets as $widget_id ) {
				$id_base = preg_replace( '/-[0-9]+$/', '', $widget_id );
				$_POST = array('sidebar' => $sb_id, 'widget-' . $id_base => array(), 'the-widget-id' => $widget_id, 'delete_widget' => '1');
				$this->_refresh_widget_settings( $id_base );
			}

			// --- 2. Import the new widgets to the sidebar

			foreach ( $sidebar as $class => $widget ) {
				$widget_base = $widget['id_base'];
				$widget_name = $this->_add_new_widget( $widget_base, $widget['settings'] );

				if ( ! empty( $widget_name ) ) {
					$def_sidebars[ $sb_id ][] = $widget_name;
					$widget_count += 1;
				}
			}
		}
		$_POST = $orig_POST;
		if ( $widget_count > 0 ) {
			wp_set_sidebars_widgets( $def_sidebars );
			$msg[] = sprintf( __( 'Imported %d widget(s)!', CSB_LANG ), $widget_count );
		}

		self::set_message( implode( '<br />', $msg ) );
		// Redirect user to the "Select import file" screen.
		$_GET['p'] = 'export';
	}

	/**
	 * Helper function used by the "do_import()" handler.
	 * Updates the widget-data in DB.
	 *
	 * @since  1.6.0
	 */
	private function _refresh_widget_settings( $id_base ) {
		global $wp_registered_widget_updates;

		foreach ( (array) $wp_registered_widget_updates as $name => $control ) {

			if ( $name == $id_base ) {
				if ( ! is_callable( $control['callback'] ) ) {
					continue;
				}

				$control['callback']->updated = false;

				ob_start();
				call_user_func_array( $control['callback'], $control['params'] );
				ob_end_clean();
				break;
			}
		}
	}

	/**
	 * Helper function used by the "do_import()" handler.
	 * Updates the widget-data in DB.
	 *
	 * @since  1.6.0
	 */
	private function _add_new_widget( $id_base, $instance ) {
		global $wp_registered_widget_updates;
		$widget_name = false;

		foreach ( (array) $wp_registered_widget_updates as $name => $control ) {

			if ( $name == $id_base ) {
				if ( ! is_callable( $control['callback'] ) ) {
					continue;
				}

				$obj = $control['callback'][0];
				$obj->updated = false;

				$all_instances = $obj->get_settings();

				// Find out what the next free number is.
				$new_number = 0;
				foreach ( $all_instances as $number => $data ) {
					$new_number = $number > $new_number ? $number : $new_number;
				}
				$new_number += 1;
				$widget_name = $id_base . '-' . $new_number;

				foreach ( $instance as $key => $value ) {
					$_POST[ $key ] = $value;
				}

				/**
				 * Filter a widget's settings before saving.
				 *
				 * Returning false will effectively short-circuit the widget's ability
				 * to update settings.
				 *
				 * @since 2.8.0
				 * @see  wp-includes/widgets.php : function "update_callback()"
				 *
				 * @param array     $instance     The current widget instance's settings.
				 * @param array     $new_instance Array of new widget settings.
				 * @param array     $old_instance Array of old widget settings.
				 * @param WP_Widget $this         The current widget instance.
				 */
				$instance = apply_filters( 'widget_update_callback', $instance, $instance, array(), $obj );
				if ( false !== $instance ) {
					$all_instances[ $new_number ] = $instance;
				}

				$obj->save_settings( $all_instances );

				break;
			}
		}

		return $widget_name;
	}

};