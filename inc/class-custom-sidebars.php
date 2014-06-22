<?php

// Load additional Pro-modules.
require_once( 'class-custom-sidebars-widgets.php' );
require_once( 'class-custom-sidebars-editor.php' );
require_once( 'class-custom-sidebars-replacer.php' );
require_once( 'class-custom-sidebars-cloning.php' );
require_once( 'class-custom-sidebars-visibility.php' );
require_once( 'class-custom-sidebars-export.php' );


/**
 * Main plugin file.
 * The CustomSidebars class encapsulates all our plugin logic.
 */
class CustomSidebars {
	static protected $sidebar_prefix = 'cs-';

	static protected $cap_required = 'switch_themes';

	/**
	 * Returns the singleton instance of the custom sidebars class.
	 *
	 * @since 1.6
	 */
	static public function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new CustomSidebars();
		}

		return $Inst;
	}

	/**
	 * Private, since it is a singleton.
	 * We directly initialize sidebar options when class is created.
	 */
	private function __construct() {
		/**
		 * Hook up the plugin with WordPress.
		 */
		add_action( 'save_post', array( $this, 'store_replacements' ) );
		add_action( 'init', array( $this, 'load_text_domain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_styles' ) );

		//AJAX actions
		add_action( 'wp_ajax_cs-ajax', array( $this, 'ajax_handler' ) );

		// Extensions use this hook to initialize themselfs.
		do_action( 'cs_init' );
	}

	/**
	 * Load the .po language files.
	 */
	public function load_text_domain() {
		load_plugin_textdomain( CSB_LANG, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		self::get_options();
	}

	/**
	 * Load javascript and CSS files used by the plugin.
	 *
	 * @since 1.0.0
	 */
	public function add_styles( $hook ) {
		if ( 'widgets.php' == $hook ) {
			wp_enqueue_script( 'tiny-scrollbar', CSB_JS_URL . 'tiny-scrollbar.js', array( 'jquery' ) );
			wp_enqueue_script( 'cs_script', CSB_JS_URL . 'cs.dev.js', array( 'tiny-scrollbar') );
			wp_enqueue_script( 'wpmu-ui', CSB_JS_URL . 'wpmu-ui.js', array( 'jquery') );

			wp_enqueue_style( 'cs_style', CSB_CSS_URL . 'cs_style.css' );
			wp_enqueue_style( 'wpmu-ui', CSB_CSS_URL . 'wpmu-ui.css' );
		}
	}





	// =========================================================================
	// == DATA ACCESS
	// =========================================================================





	/**
	 * Returns a list with sidebars that were marked as "modifiable".
	 * Also contains information on the default replacements of these sidebars.
	 *
	 * Option-Key: 'cs_modifiable'
	 */
	static public function get_options( $key = null ) {
		static $Options = null;

		if ( null === $Options ) {
			$Options = get_option( 'cs_modifiable', array() );
			if ( ! is_array( $Options ) ) {
				$Options = array();
			}
			if ( ! is_array( @$Options['modifiable'] ) ) {
				$Options['modifiable'] = array();
			}
			if ( ! is_array( @$Options['defaults'] ) ) {
				$Options['defaults'] = array();
			}
		}
			// ----- DEBUG START
			function_exists( 'wp_describe' ) && wp_debug( 'class-custom-sidebars.php:109', $Options );
			//die();
			// ----- DEBUG END

		if ( ! empty( $key ) ) {
			return @$Options[ $key ];
		} else {
			return $Options;
		}
	}

	/**
	 * Saves the sidebar options to DB.
	 *
	 * Option-Key: 'cs_modifiable'
	 * @since 1.6.0
	 */
	static public function set_options( $value ) {
		update_option( 'cs_modifiable', $value );
	}

	/**
	 * Returns a list with all custom sidebars that were created by the user.
	 * Array of custom sidebars
	 * Each sidebar is an array with following fields
	 *   - name
	 *   - id
	 *   - description
	 *   - before_title
	 *   - after_title
	 *   - before_widget
	 *   - after_widget
	 *
	 * Option-Key: 'cs_sidebars'
	 */
	static public function get_custom_sidebars() {
		$sidebars = get_option( 'cs_sidebars', array() );
		if ( ! is_array( $sidebars ) ) {
			$sidebars = array();
		}
		return $sidebars;
	}

	/**
	 * Saves the custom sidebars to DB.
	 *
	 * Option-Key: 'cs_sidebars'
	 * @since 1.6.0
	 */
	static public function set_custom_sidebars( $value ) {
		update_option( 'cs_sidebars', $value );
	}

	/**
	 * Returns a list of all registered sidebars including a list of their
	 * widgets (this is stored inside a WordPress core option).
	 *
	 * Option-Key: 'sidebars_widgets'
	 * @since  1.6.0
	 */
	static public function get_sidebar_widgets() {
		return get_option( 'sidebars_widgets', array() );
	}

	/**
	 * Update the WordPress core settings for sidebar widgets:
	 * 1. Add empty widget information for new sidebars.
	 * 2. Remove widget information for sidebars that no longer exist.
	 *
	 * Option-Key: 'sidebars_widgets'
	 */
	static public function refresh_sidebar_widgets() {
		// Contains an array of all sidebars and widgets inside each sidebar.
		$widgetized_sidebars = self::get_sidebar_widgets();

		$cs_sidebars = self::get_custom_sidebars();
		$delete_widgetized_sidebars = array();


		foreach ( $widgetized_sidebars as $id => $bar ) {
			if ( substr( $id, 0, 3 ) == self::$sidebar_prefix ) {
				$found = FALSE;
				foreach ( $cs_sidebars as $csbar ) {
					if ( $csbar['id'] == $id ) {
						$found = TRUE;
					}
				}
				if ( ! $found ) {
					$delete_widgetized_sidebars[] = $id;
				}
			}
		}

		$all_ids = array_keys( $widgetized_sidebars );
		foreach ( $cs_sidebars as $cs ) {
			$sb_id = $cs['id'];
			if ( ! in_array( $sb_id, $all_ids ) ) {
				$widgetized_sidebars[$sb_id] = array();
			}
		}

		foreach ( $delete_widgetized_sidebars as $id ) {
			unset( $widgetized_sidebars[$id] );
		}

		update_option( 'sidebars_widgets', $widgetized_sidebars );
	}

	/**
	 * Returns a list of all sidebars available.
	 * Depending on the parameter this will be either all sidebars or only
	 * sidebars defined by the current theme.
	 */
	static public function get_sidebars( $include_custom_sidebars = FALSE ) {
		global $wp_registered_sidebars;
		$allsidebars = $wp_registered_sidebars;
		$result = array();

		ksort( $allsidebars );
		if ( $include_custom_sidebars ) {
			$result = $allsidebars;
		} else {
			$themesidebars = array();
			foreach ( $allsidebars as $key => $sb ) {
				// Remove sidebars that start with the custom-sidebar prefix.
				if ( substr( $key, 0, 3 ) != self::$sidebar_prefix ) {
					$themesidebars[$key] = $sb;
				}
			}
			$result = $themesidebars;
		}

		return $result;
	}

	/**
	 * Returns the custom sidebar metadata of a single post.
	 *
	 * Meta-Key: '_cs_replacements'
	 * @since  1.6
	 */
	static public function get_post_meta( $post_id ) {
		$data = get_post_meta( $post_id, '_cs_replacements', TRUE );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		return $data;
	}

	/**
	 * Saves custom sidebar metadata to a single post.
	 *
	 * Meta-Key: '_cs_replacements'
	 * @since 1.6
	 * @param int $post_id
	 * @param array $data When array is empty the meta data will be deleted.
	 */
	static public function set_post_meta( $post_id, $data ) {
		if ( ! empty( $data ) ) {
			update_post_meta( $post_id, '_cs_replacements', $data );
		} else {
			delete_post_meta( $post_id, '_cs_replacements' );
		}
	}

	/**
	 * Get sidebar replacement information for a single post.
	 */
	static public function get_replacements( $postid ) {
		$replacements = self::get_post_meta( $postid );
		if ( ! is_array( $replacements ) ) {
			$replacements = array();
		} else {
			$replacements = $replacements;
		}
		return $replacements;
	}

	/**
	 * Returns true, when the specified post type supports custom sidebars.
	 *
	 * @since  1.6.0
	 */
	static public function supported_post_type( $posttype ) {
		$Ignored_types = null;
		$Response = array();

		if ( null === $Ignored_types ) {
			$Ignored_types = get_post_types( array( 'public' => false ), 'names' );
			$Ignored_types[] = 'attachment';
		}

		if ( ! isset( $Response[ $posttype ] ) ) {
			$response = ! in_array( $posttype, $Ignored_types );

			/**
			 * Filters the support-flag. The flag defines if the posttype supports
			 * custom sidebars or not.
			 *
			 * @since 1.6
			 *
			 * @param  bool $response Flag if the posttype is supported.
			 * @param  string $posttype Name of the posttype that is checked.
			 */
			$response = apply_filters( 'cs_support_posttype', $response, $posttype );
			$Response[ $posttype ] = $response;
		}

		return $Response[ $posttype ];
	}

	/**
	 * Returns the sidebar with the specified ID from the sidebar-array.
	 */
	static public function get_sidebar( $id, $sidebars ) {
		$sidebar = false;
		$nsidebars = sizeof( $sidebars );
		$i = 0;
		while ( ! $sidebar && $i < $nsidebars ) {
			if ( $sidebars[$i]['id'] == $id ) {
				$sidebar = $sidebars[$i];
			}
			$i++;
		}
		return $sidebar;
	}

	/**
	 * Returns a list of all post types that support custom sidebars.
	 *
	 * @uses self::supported_post_type()
	 * @return array List of posttype names.
	 */
	static public function get_post_types() {
		$Valid = null;

		if ( null === $Valid ) {
			$all = get_post_types( array(), 'names' );
			$Valid = array();

			foreach ( $all as $post_type ) {
				if ( self::supported_post_type( $post_type ) ) {
					$Valid[] = $post_type;
				}
			}
		}

		return $Valid;
	}

	/**
	 * Returns a sorted list of all category terms of the current post.
	 * This information is used to find sidebar replacements.
	 *
	 * @uses  self::cmp_cat_level()
	 */
	static public function get_sorted_categories() {
		static $Sorted = null;

		if ( null === $Sorted ) {
			$Sorted = get_the_category();
			@usort( $Sorted, array( self, 'cmp_cat_level' ) );
		}
		return $Sorted;
	}

	/**
	 * Helper function used to sort categories.
	 *
	 * @uses  self::get_category_level()
	 */
	static public function cmp_cat_level( $cat1, $cat2 ) {
		$l1 = self::get_category_level( $cat1->cat_ID );
		$l2 = self::get_category_level( $cat2->cat_ID );
		if ( $l1 == $l2 ) {
			return strcasecmp( $cat1->name, $cat1->name );
		} else {
			return $l1 < $l2 ? 1 : -1;
		}
	}

	/**
	 * Helper function used to sort categories.
	 */
	static public function get_category_level( $catid ) {
		if ( $catid == 0 ) {
			return 0;
		}

		$cat = get_category( $catid );
		return 1 + self::get_category_level( $cat->category_parent );
	}





	// =========================================================================
	// == AJAX FUNCTIONS
	// =========================================================================





	/**
	 * Output JSON data and die()
	 *
	 * @since  1.0.0
	 */
	static public function json_response( $obj ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $obj );
		die();
	}

	/**
	 * Output HTML data and die()
	 *
	 * @since  1.6.0
	 */
	static public function plain_response( $data ) {
		header( 'Content-Type: text/plain' );
		echo $data;
		die();
	}

	/**
	 * All Ajax request are handled by this function.
	 * It analyzes the post-data and calls the required functions to execute
	 * the requested action.
	 *
	 * @since  1.0.0
	 */
	public function ajax_handler() {
		$action = @$_POST['do'];

		/**
		 * Notify all extensions about the ajax call.
		 *
		 * @since  1.6.0
		 * @param  string $action The specified ajax action.
		 */
		do_action( 'cs_ajax_request', $action );

		/*
		if ( $_REQUEST['cs_action'] == 'where' ) {
			$this->ajax_show_where();
			die();
		}

		$nonce = $_POST['nonce'];
		$action = $_POST['cs_action'];
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			$response = array(
				'success' => false,
				'message' => __( 'The operation is not secure and it cannot be completed.', CSB_LANG ),
				'nonce' => wp_create_nonce( $action ),
			);
			self::json_response( $response );
		}

		$response = array();
		if ( $action == 'cs-create-sidebar' ) {
			$response = $this->ajax_create_sidebar();
		} else if ( $action == 'cs-edit-sidebar' ) {
			$response = $this->ajax_edit_sidebar();
		} else if ( $action == 'cs-set-defaults' ) {
			$response = $this->ajax_set_defaults();
		} else if ( $action == 'cs-delete-sidebar' ) {
			$response = $this->ajax_delete_sidebar();
		}

		$response['nonce'] = wp_create_nonce( $action );
		self::json_response( $response );
		*/
	}

	/*
	public function ajax_set_defaults() {
		try {
			$this->store_defaults();
		} catch( Exception $e ) {
			return array(
				'success' => false,
				'message' => __( 'There has been an error storing the sidebars. Please, try again.', CSB_LANG ),
			);
		}
		return array(
			'success' => true,
			'message' => $this->message,
		);
	}

	public function ajax_create_sidebar() {
		$this->store_sidebar();

		if ( $this->message_class == 'error' ) {
			return array(
				'success' => false,
				'message' => $this->message,
			);
		}

		return array(
			'success' => true,
			'message' => __( 'The sidebar has been created successfully.', CSB_LANG ),
			'name' => stripslashes( trim( $_POST['sidebar_name'] ) ),
			'description' => stripslashes( trim( $_POST['sidebar_description'] ) ),
			'id' => self::$sidebar_prefix . sanitize_html_class( sanitize_title_with_dashes( $_POST['sidebar_name'] ) ),
		);
	}

	public function ajax_delete_sidebar() {
		$this->delete_sidebar();

		return array(
			'message' => $this->message,
			'success' => $this->message_class != 'error',
		);
	}

	public function ajax_edit_sidebar() {
		$id = trim( $_POST['cs_id'] );
		$sidebar = $this->get_sidebar( $id, self::get_custom_sidebars() );
		$_POST['cs_before_widget'] = $sidebar['cs_before_widget'];
		$_POST['cs_after_widget'] = $sidebar['cs_after_widget'];
		$_POST['cs_before_title'] = $sidebar['cs_before_title'];
		$_POST['cs_after_title'] = $sidebar['cs_after_title'];
		$this->update_sidebar();

		$sidebar = $this->get_sidebar( $id, self::get_custom_sidebars() );
		return array(
			'message' => $this->message,
			'success' => $this->message_class != 'error',
			'name' => $sidebar['name'],
			'description' => $sidebar['description'],
		);
	}

	public function ajax_show_where() {
		// FIXME: These are global variables. Move this to a (static) function instead
		$customsidebars = self::get_custom_sidebars();
		$themesidebars = self::get_sidebars();
		$allsidebars = self::get_sidebars( TRUE );
		$sidebarId = strtolower( urlencode( $_GET['id'] ) );

		if ( ! isset( $allsidebars[$sidebarId] ) ) {
			echo urlencode( $_GET['id'] );
			var_dump( $allsidebars );
			die( __( 'Unknown sidebar.', CSB_LANG ) );
		}
		foreach ( $allsidebars as $key => $sb ) {
			if ( strlen( $sb['name'] ) > 30 ) {
				$allsidebars[$key]['name'] = substr( $sb['name'], 0, 27 ) . '...';
			}
		}

		// FIXME: These are global variables. Move this to a (static) function instead
		$current_sidebar = $allsidebars[ $_GET['id'] ];
		$defaults = self::get_options( 'defaults' );
		$modifiable = self::get_options( 'modifiable' );
		$categories = get_categories( array( 'hide_empty' => 0 ) );
		if ( sizeof( $categories ) == 1 && $categories[0]->cat_ID == 1 ) {
			unset( $categories[0] );
		}

		include CSB_VIEWS_DIR . 'ajax.php';
	}
	*/
};
