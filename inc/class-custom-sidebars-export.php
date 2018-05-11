<?php


add_action( 'cs_init', array( 'CustomSidebarsExport', 'instance' ) );

/**
 * Provides functionality to export and import sidebar settings.
 *
 * @since  2.0
 */
class CustomSidebarsExport extends CustomSidebars {

	// Holds the contents of the import-file during preview/import.
	static private $import_data = null;

	// Used after preview. This holds only the items that were selected for import.
	private $selected_data = null;

	/**
	 * CSB version
	 */
	private $version = '';

	/**
	 * Returns the singleton object.
	 *
	 * @since  2.0
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
	 * @since  2.0
	 */
	private function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Admin Init
	 *
	 * @since 3.1.6
	 */
	public function admin_init() {
		add_action( 'cs_widget_header', array( $this, 'widget_header' ) );
		add_action( 'cs_ajax_request', array( $this, 'handle_ajax' ) );
	}

	/**
	 * Called by action 'cs_widget_header'. Output the export/import button in
	 * the widget header.
	 *
	 * @since  2.0
	 */
	public function widget_header() {
		?>
		<a href="#" class="cs-action btn-export"><?php _e( 'Import / Export Sidebars', 'custom-sidebars' ); ?></a>
		<?php
	}

	/**
	 * When the custom sidebars section is visible we see if export-action
	 * needs to be processed.
	 *
	 * @since  2.0
	 */
	public function handle_ajax( $ajax_action ) {
		$req = (object) array(
			'status' => 'ERR',
		);
		$is_json = true;
		$handle_it = false;
		$view_file = '';

		switch ( $ajax_action ) {
			case 'export':
			case 'import':
			case 'preview-import':
				$handle_it = true;
				$req->status = 'OK';
				$req->action = $ajax_action;
				break;
		}

		// The ajax request was not meant for us...
		if ( ! $handle_it ) {
			return false;
		}

		if ( ! current_user_can( self::$cap_required ) ) {
			$req = self::req_err(
				$req,
				__( 'You do not have permission for this', 'custom-sidebars' )
			);
		} else {
			switch ( $ajax_action ) {
				case 'export':
					$this->download_export_file();
					break;

				case 'preview-import':
					$req = $this->read_import_file( $req );
					if ( 'OK' == $req->status ) {
						ob_start();
						include CSB_VIEWS_DIR . 'import.php';
						$req->html = ob_get_clean();
					}
					break;

				case 'import':
					$req = $this->prepare_import_data( $req );
					break;
			}
		}

		// Make the ajax response either as JSON or plain text.
		if ( $is_json ) {
			self::json_response( $req );
		} else {
			ob_start();
			include CSB_VIEWS_DIR . $view_file;
			$resp = ob_get_clean();

			self::plain_response( $resp );
		}
	}


	/*============================*\
	================================
	==                            ==
	==           EXPORT           ==
	==                            ==
	================================
	\*============================*/


	/**
	 * Collects the plugin details for export.
	 *
	 * @since  2.0
	 */
	private function get_export_data() {
		global $wp_registered_widgets, $wp_version;
		$theme = wp_get_theme();
		$csb_info = get_plugin_data( CSB_PLUGIN );
		$this->version = $csb_info['Version'];
		$data = array();
		// Add some meta-details to the export file.
		$data['meta'] = array(
			'created' => time(),
			'wp_version' => $wp_version,
			'csb_version' => $csb_info['Version'],
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
			if ( 'wp_inactive_widgets' === $sidebar ) {
				continue;
			}
			if ( is_array( $widgets ) ) {
				$data['widgets'][ $sidebar ] = array();
				foreach ( $widgets as $widget_id ) {
					if ( isset( $wp_registered_widgets[ $widget_id ] ) ) {
						$item = $wp_registered_widgets[ $widget_id ];
						$cb = $item['callback'];
						$widget = is_array( $cb ) ? reset( $cb ) : false;
						$id = $widget_id;
						if ( ! isset( $data['widgets'][ $sidebar ][ $id ] ) ) {
							if ( preg_match( '/(\d+)$/', $widget_id, $matches ) ) {
								$id = $matches[1];
							}
						}
						if ( isset( $data['widgets'][ $sidebar ][ $id ] ) ) {
							continue;
						}
						if ( is_object( $widget ) && method_exists( $widget, 'get_settings' ) ) {
							/**
							 * set correct widget data
							 */
							$widget->id = $widget_id;
							$widget->number = $id;
							/**
							 * get settings
							 */
							$settings = $widget->get_settings();
							$data['widgets'][ $sidebar ][ $id ] = array(
								'name' => @$widget->name,
								'classname' => get_class( $widget ),
								'id_base' => @$widget->id_base,
								'description' => @$widget->description,
								'settings' => $settings[ @$widget->number ],
								'version' => 3,
							);
						} else {
							/**
							 * Widgets that are registered with the old widget API
							 * have a different structure:
							 *
							 * - Not an object but a callback function.
							 * - No standard options-form.
							 *   -> No widget settings to export.
							 *   -> No clone/visibility options to export.
							 * - Only one instance
							 *   -> "id_base" is same as $widget_id
							 */
							$data['widgets'][ $sidebar ][ $widget_id ] = array(
								'name' => @$item['name'],
								'classname' => @$item['classname'],
								'id_base' => @$item['id'],
								'description' => @$item['description'],
								'settings' => @$item['params'],
								'version' => 2,
							);
						}
						/**
						 * remove empty settings
						 */
						if ( isset( $data['widgets'][ $sidebar ][ $id ]['settings']['csb_visibility']['conditions'] ) ) {
							foreach ( $data['widgets'][ $sidebar ][ $id ]['settings']['csb_visibility']['conditions'] as $condition_id => $condition_value ) {
								if ( empty( $condition_value ) ) {

									unset( $data['widgets'][ $sidebar ][ $id ]['settings']['csb_visibility']['conditions'][ $condition_id ] );

								}
							}
						}
					}
				}
			} else {
				$data['widgets'][ $sidebar ] = $widgets;
			}
		}
		return $data;
	}

	/**
	 * Generates the export file and sends it as a download to the browser.
	 *
	 * @since  2.0
	 */
	private function download_export_file() {
		/**
		 * check nonce
		 */
		if (
			! isset( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'custom-sidebars-export' )
		) {
			$req = (object) array(
				'status' => 'ERR',
			);
			$req = self::req_err(
				$req,
				__( 'You do not have permission for export sidebars.', 'custom-sidebars' )
			);
			self::json_response( $req );
		}
		/**
		 * build filename
		 */
		$filename = $this->get_file_name();
		$data = $this->get_export_data();
		$content = '';
		/**
		 * Check PHP version, for PHP < 5.3 do not add options
		 *
		 * @since 3.1.6
		 */
		$version = phpversion();
		$compare = version_compare( $version, '5.3', '<' );
		if ( $compare ) {
			$content = json_encode( $data );
		} else {
			$option = defined( 'JSON_PRETTY_PRINT' )? JSON_PRETTY_PRINT : null;
			$content = json_encode( $data, $option );
		}
		// Send the download headers.
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: private', false ); // required for certain browsers
		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . strlen( $content ) );
		/**
		 * Finally send the export-file content.
		 */
		echo $content;
		exit;
	}

	/**
	 * Generate export file name dynamically.
	 *
	 * Generate a unique file name to export in json.
	 *
	 * @since 3.1.6
	 *
	 * @return string File name.
	 */
	private function get_file_name() {
		/**
		 * get version if it is needded
		 */
		if ( empty( $this->version ) ) {
			$csb_info = get_plugin_data( CSB_PLUGIN );
			$this->version = $csb_info['Version'];
		}
		// Get site name.
		$site_name = sanitize_key( get_bloginfo( 'name' ) );
		$site_name = empty( $site_name ) ? '' : $site_name . '.';
		// Create export file name.
		$filename = sprintf(
			'%s.sidebars.%s.%s.json',
			$site_name,
			$this->version,
			date( 'Y-m-d.H-i-s' )
		);
		return $filename;
	}

	/*=============================*\
	=================================
	==                             ==
	==           PREVIEW           ==
	==                             ==
	=================================
	\*=============================*/


	/**
	 * Checks if a valid export-file was uploaded and stores the file contents
	 * inside self::$import_data. The data is de-serialized.
	 * In error case the response object will be set to error status.
	 *
	 * @since  2.0
	 * @param  object $req Initial response object for JSON response.
	 * @return object Updated response object.
	 */
	private function read_import_file( $req ) {
		/**
		 * check nonce
		 */
		if (
			! isset( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'custom-sidebars-import' )
		) {
			$req = (object) array(
				'status' => 'ERR',
			);
			$req = self::req_err(
				$req,
				__( 'You do not have permission for export sidebars.', 'custom-sidebars' )
			);
			self::json_response( $req );
		}

		if ( is_array( $_FILES['data'] ) ) {
			switch ( $_FILES['data']['error'] ) {
				case UPLOAD_ERR_OK:
					// This is the expeted status!
					break;

				case UPLOAD_ERR_NO_FILE:
					return self::req_err(
						$req,
						__( 'No file was uploaded', 'custom-sidebars' )
					);

				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					return self::req_err(
						$req,
						__( 'Import file is too big', 'custom-sidebars' )
					);

				default:
					return self::req_err(
						$req,
						__( 'Something went wrong', 'custom-sidebars' )
					);
			}

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
				self::$import_data = $data;

				// Remove details that does not exist on current blog.
				$this->prepare_data();
			} else {
				return self::req_err(
					$req,
					__( 'Unexpected import format', 'custom-sidebars' )
				);
			}
		} else {
			return self::req_err(
				$req,
				__( 'No file was uploaded', 'custom-sidebars' )
			);
		}

		return $req;
	}

	/**
	 * Loads the import-data into the self::$import_data property.
	 * The data was prepared by the import-preview screen.
	 * Populates the response object.
	 *
	 * @since  2.0
	 * @param  object $req Initial response object for JSON response.
	 * @return object Updated response object.
	 */
	private function prepare_import_data( $req ) {
		/**
		 * check nonce
		 */
		if (
			! isset( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'custom-sidebars-import' )
		) {
			$req = (object) array(
				'status' => 'ERR',
			);
			$req = self::req_err(
				$req,
				__( 'You do not have permission for import sidebars.', 'custom-sidebars' )
			);
			self::json_response( $req );
		}

		$data = json_decode( base64_decode( @$_POST['import_data'] ), true );

		if (
			is_array( $data['meta'] ) &&
			is_array( $data['sidebars'] ) &&
			is_array( $data['options'] ) &&
			is_array( $data['widgets'] ) &&
			is_array( $data['categories'] )
		) {
			$data['ignore'] = array();
			self::$import_data = $data;

			// Remove details that does not exist on current blog.
			$this->prepare_data();

			// "selected_data" only contains the items that were selected for import.
			$this->selected_data = self::$import_data;
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
			$req = $this->do_import( $req );
		} else {
			return self::req_err(
				$req,
				__(
					'Something unexpected happened and we could not finish ' .
					'the import. Please try again.', 'custom-sidebars'
				)
			);
		}

		return $req;
	}

	/**
	 * Loops through the import data array and removes configuration which is
	 * not relevant for the current blog. I.e. posttypes that are not registered
	 * or categories that do not match the current blog.
	 *
	 * @since  2.0
	 */
	private function prepare_data() {
		global $wp_registered_widgets;
		$theme_sidebars = self::get_sidebars();
		$valid_categories = array();
		$valid_sidebars = array();
		$valid_widgets = array();

		// =====
		// Normalize the sidebar list (change numeric index to sidebar-id).
		$sidebars_remapped = array();
		foreach ( self::$import_data['sidebars'] as $sidebar ) {
			$sidebars_remapped[ $sidebar['id'] ] = $sidebar;
		}
		self::$import_data['sidebars'] = $sidebars_remapped;

		// =====
		// Get a list of existing/valid sidebar-IDs.
		$valid_sidebars = array_merge(
			array_keys( $theme_sidebars ),
			array_keys( self::$import_data['sidebars'] )
		);

		// =====
		// Check for theme-sidebars that do not exist.
		foreach ( self::$import_data['options']['modifiable'] as $id => $sb_id ) {
			if ( ! isset( $theme_sidebars[ $sb_id ] ) ) {
				if ( ! isset( self::$import_data['ignore']['sidebars'] ) ) {
					self::$import_data['ignore']['sidebars'] = array();
				}
				self::$import_data['ignore']['sidebars'][] = $sb_id;
				unset( self::$import_data['options']['modifiable'][ $id ] );
			}
		}

		// =====
		// Remove invalid sidebars from the default replacement options.
		foreach ( array( 'post_type_single', 'post_type_archive', 'category_single', 'category_archive' ) as $key ) {
			foreach ( self::$import_data['options'][ $key ] as $id => $list ) {
				$list = $this->_remove_sidebar_from_list( $list, $valid_sidebars );
				self::$import_data['options'][ $key ][ $id ] = $list;
			}
		}
		foreach ( array( 'blog', 'tags', 'authors', 'search', 'date' ) as $key ) {
			$list = self::$import_data['options'][ $key ];
			$list = $this->_remove_sidebar_from_list( $list, $valid_sidebars );
			self::$import_data['options'][ $key ] = $list;
		}

		// =====
		// Check for missing/different categories.
		foreach ( get_categories( array( 'hide_empty' => 0 ) ) as $cat ) {
			$valid_categories[ $cat->term_id ] = $cat;
		}
		foreach ( self::$import_data['categories'] as $infos ) {
			$id = $infos['term_id'];
			if (
				empty( $valid_categories[ $id ] ) ||
				$valid_categories[ $id ]->slug != $infos['slug']
			) {
				if ( ! isset( self::$import_data['ignore']['categories'] ) ) {
					self::$import_data['ignore']['categories'] = array();
				}
				self::$import_data['ignore']['categories'][] = $infos['name'];
				unset( self::$import_data['categories'][ $id ] );

				// Remove the categories from the config array.
				unset( self::$import_data['options']['category_posts'][ $id ] );
				unset( self::$import_data['options']['category_pages'][ $id ] );
			}
		}

		// =====
		// Remove missing widgets from import data.
		foreach ( $wp_registered_widgets as $widget ) {
			if ( is_array( $widget['callback'] ) ) {
				$classname = get_class( $widget['callback'][0] );
			} else {
				$classname = $widget['classname'];
			}
			$valid_widgets[ $classname ] = true;
		}
		foreach ( self::$import_data['widgets'] as $sb_id => $sidebar ) {
			if ( ! is_array( $sidebar ) ) { continue; }
			foreach ( $sidebar as $id => $widget_instance ) {
				$version = $widget_instance['version'];
				$instance_class = $widget_instance['classname'];
				$exists = (true === @$valid_widgets[ $instance_class ]);
				if ( ! $exists ) {
					if ( ! isset( self::$import_data['ignore']['widgets'] ) ) {
						self::$import_data['ignore']['widgets'] = array();
					}
					self::$import_data['ignore']['widgets'][] = $widget_instance['name'];
					unset( $sidebar[ $id ] );
				}
			}
			self::$import_data['widgets'][ $sb_id ] = $sidebar;
		}
	}

	/**
	 * Helper function that is used by prepare_data.
	 *
	 * @since  2.0
	 */
	private function _remove_sidebar_from_list( $list, $valid_list ) {
		/**
		 * do not process if $list is not an array or is an empty array
		 */
		if ( ! is_array( $list ) || empty( $list ) ) {
			return $list;
		}
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
	 * @since  2.0
	 */
	static public function get_import_data() {
		return self::$import_data;
	}


	/*============================*\
	================================
	==                            ==
	==           IMPORT           ==
	==                            ==
	================================
	\*============================*/

	/**
	 * Process the import data provided in self::$import_data.
	 * Save the configuration to database.
	 * Populates the response object.
	 *
	 * @since  2.0
	 * @param  object $req Initial response object for JSON response.
	 * @return object Updated response object.
	 */
	private function do_import( $req ) {
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
			$msg[] = sprintf(
				_n(
					'Imported %d custom sidebar!',
					'Imported %d custom sidebars!',
					$sidebar_count,
					'custom-sidebars'
				),
				$sidebar_count
			);
		}

		// =====================================================================
		// Import plugin settings
		if ( ! empty( $data['options'] ) ) {
			self::set_options( $data['options'] );
			$msg[] = __( 'Plugin options were imported!', 'custom-sidebars' );
		}

		// =====================================================================
		// Import widgets
		$widget_count = 0;
		$def_sidebars = wp_get_sidebars_widgets();
		$widget_list = array();
		$orig_POST = $_POST;
		/**
		 * First replace existing sidebars.
		 */
		if ( isset( $data['widgets'] ) && is_array( $data['widgets'] ) ) {
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
					$_POST = array(
						'sidebar' => $sb_id,
						'widget-' . $id_base => array(),
						'the-widget-id' => $widget_id,
						'delete_widget' => '1',
					);
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
		}
		$_POST = $orig_POST;
		if ( $widget_count > 0 ) {
			wp_set_sidebars_widgets( $def_sidebars );
			$msg[] = sprintf(
				_n(
					'Imported %d widget!',
					'Imported %d widgets!',
					$widget_count,
					'custom-sidebars'
				),
				$widget_count
			);
		}

		$req->message = base64_encode( implode( '<br />', $msg ) );

		// We return a HTTP header to refresh the widgets page.
		header( 'HTTP/1.1 302 Found' );
		header( 'Location: ' . admin_url( 'widgets.php?cs-msg=' . $req->message ) );
		die();
	}

	/**
	 * Helper function used by the "do_import()" handler.
	 * Updates the widget-data in DB.
	 *
	 * @since  2.0
	 */
	private function _refresh_widget_settings( $id_base ) {
		global $wp_registered_widget_updates;

		foreach ( (array) $wp_registered_widget_updates as $name => $control ) {

			if ( $name == $id_base ) {
				if ( ! is_callable( $control['callback'] ) ) {
					continue;
				}

				ob_start();
				if ( is_object( $control['callback'] ) ) {
					$control['callback']->updated = false;
				}
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
	 * @since  2.0
	 */
	private function _add_new_widget( $id_base, $instance ) {
		global $wp_registered_widget_updates;
		$widget_name = false;

		foreach ( (array) $wp_registered_widget_updates as $name => $control ) {

			if ( $name == $id_base ) {
				if ( ! is_callable( $control['callback'] ) ) {
					continue;
				}

				if ( is_array( $control['callback'] ) ) {
					$obj = $control['callback'][0];
				} else {
					// We cannot import data from old widgets API.
					break;
				}
				$obj->updated = false;

				$all_instances = $obj->get_settings();

				// Find out what the next free number is.
				$new_number = 0;
				foreach ( $all_instances as $number => $data ) {
					$new_number = $number > $new_number ? $number : $new_number;
				}
				$new_number += 1;
				$widget_name = $id_base . '-' . $new_number;
				/**
				 * reset previous data
				 */
				$keys = array( 'title', 'text', 'filter', 'csb_visibility', 'csb_clone' );
				foreach ( $keys as $key ) {
					if ( isset( $_POST[ $key ] ) ) {
						unset( $_POST[ $key ] );
					}
				}
				/**
				 * set current values
				 */
				foreach ( $instance as $key => $value ) {
					$_POST[ $key ] = $value;
				}

				/**
				 * Filter a widget's settings before saving.
				 *
				 * Returning false will effectively short-circuit the widget's ability
				 * to update settings.
				 *
				 * @see    wp-includes/widgets.php : function "update_callback()"
				 * @since  WordPress 2.8.0
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
