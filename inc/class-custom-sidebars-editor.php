<?php

add_action( 'cs_init', array( 'CustomSidebarsEditor', 'instance' ) );

/**
 * Provides all the functionality for editing sidebars on the widgets page.
 */
class CustomSidebarsEditor extends CustomSidebars {

	private $modifiable = null;

	/**
	 * Capability required to use *any* of the plugin features. If user does not
	 * have this capability then he will not see any change on admin dashboard.
	 * @var  string
	 */
	static protected $cap_required = 'edit_theme_options';

	/**
	 * Metabox roles name
	 *
	 * @since 3.0.9
	 */
	private $metabox_roles_name = 'custom_sidebars_metabox_roles';

	/**
	 * Custom taxoniomies name
	 *
	 * @since 3.0.9
	 */
	private $custom_taxonomies_name = 'custom_sidebars_custom_taxonomies';

	/**
	 * Allow author to change sidebars
	 *
	 * @since 3.1.5
	 */
	private $allow_author_name = 'custom_sidebars_allow_author';

	/**
	 * Returns the singleton object.
	 *
	 * @since  2.0
	 */
	public static function instance() {
		static $Inst = null;
		if ( null === $Inst ) {
			$Inst = new CustomSidebarsEditor();
		}
		return $Inst;
	}

	/**
	 * Constructor is private -> singleton.
	 *
	 * @since  2.0
	 */
	private function __construct() {
		if ( ! is_admin() ) {
			return;
		}
		// Add the sidebar metabox to posts.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		// Save the options from the sidebars-metabox.
		add_action( 'save_post', array( $this, 'store_replacements' ) );
		// Handle ajax requests.
		add_action( 'cs_ajax_request', array( $this, 'handle_ajax' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
	}

	/**
	 * Settings function
	 *
	 * @since 3.1.0
	 */
	public function settings() {
		/**
		 * metabox role
		 */
		add_filter( 'screen_settings', array( $this, 'add_capabilities_select_box' ), 10, 2 );
		add_action( 'wp_ajax_custom_sidebars_metabox_roles', array( $this, 'update_custom_sidebars_metabox_roles' ) );
		add_action( 'wp_ajax_custom_sidebars_metabox_custom_taxonomies', array( $this, 'update_custom_sidebars_metabox_custom_taxonomies' ) );
		add_action( 'wp_ajax_custom_sidebars_allow_author', array( $this, 'update_custom_sidebars_allow_author' ) );
		/**
		 * Check user privileges
		 */
		$user_can_save = $this->current_user_can_update_custom_sidebars();
		if ( ! $user_can_save ) {
			return;
		}
		// Add a custom column to post list.
		$posttypes = self::get_post_types( 'objects' );
		foreach ( $posttypes as $pt ) {
			add_filter( 'manage_' . $pt->name . '_posts_columns', array( $this, 'post_columns' ) );
			add_action( 'manage_' . $pt->name . '_posts_custom_column', array( $this, 'post_column_content' ), 10, 2
			);
		}
		/** This action is documented in wp-admin/includes/screen.php */
		add_filter( 'default_hidden_columns', array( $this, 'default_hidden_columns' ), 10, 2 );
		add_action( 'quick_edit_custom_box', array( $this, 'post_quick_edit' ), 10, 2 );
		add_action( 'bulk_edit_custom_box', array( $this, 'post_bulk_edit' ), 10, 2 );
		add_action( 'admin_footer', array( $this, 'post_quick_edit_js' ) );
		/**
		 * Bulk Edit save
		 *
		 * @since 3.0.8
		 */
		add_action( 'save_post', array( $this, 'bulk_edit_save' ) );
	}

	/**
	 * Handles the ajax requests.
	 */
	public function handle_ajax( $action ) {
		$req = (object) array(
			'status' => 'ERR',
		);
		$is_json = true;
		$handle_it = false;
		$view_file = '';
		$sb_id = '';
		if ( isset( $_POST['sb'] ) ) {
			$sb_id = $_POST['sb'];
		}
		switch ( $action ) {
			case 'get':
			case 'save':
			case 'delete':
			case 'get-location':
			case 'set-location':
			case 'replaceable':
				$handle_it = true;
				$req->status = 'OK';
				$req->action = $action;
				$req->id = $sb_id;
				break;
		}
		// The ajax request was not meant for us...
		if ( ! $handle_it ) {
			return false;
		}
		$sb_data = self::get_sidebar( $sb_id );
		if ( ! current_user_can( self::$cap_required ) ) {
			$req = self::req_err(
				$req,
				__( 'You do not have permission for this', 'custom-sidebars' )
			);
		} else {
			switch ( $action ) {
				// Return details for the specified sidebar.
				case 'get':
					/**
				 * check nonce
				 */
					if (
					! isset( $_POST['_wpnonce'] )
					|| ! wp_verify_nonce( $_POST['_wpnonce'], 'custom-sidebars-get' )
					) {
						$req = self::req_err(
							$req,
							__( 'You do not have permission for this', 'custom-sidebars' )
						);
					} else {
						$sb_data['advance'] = false;
						$user_id = get_current_user_id();
						if ( $user_id ) {
							$advance = get_user_option( 'custom-sidebars-editor-advance', $user_id );
							if ( is_array( $advance ) && isset( $advance[ $sb_data['id'] ] ) ) {
								$sb_data['advance'] = $advance[ $sb_data['id'] ];
							}
						}
						$req->sidebar = $sb_data;
					}
				break;
				// Save or insert the specified sidebar.
				case 'save':
					$req = $this->save_item( $req, $_POST );
				break;
				// Delete the specified sidebar.
				case 'delete':
					$req->sidebar = $sb_data;
					$req = $this->delete_item( $req, $_POST );
				break;
				// Get the location data.
				case 'get-location':
					$req->sidebar = $sb_data;
					$req = $this->get_location_data( $req );
				break;
				// Update the location data.
				case 'set-location':
					$req->sidebar = $sb_data;
					$req = $this->set_location_data( $req );
				break;
				// Toggle theme sidebar replaceable-flag.
				case 'replaceable':
					$req = $this->set_replaceable( $req );
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

	/**
	 * Saves the item specified by $data array and populates the response
	 * object. When $req->id is empty a new sidebar will be created. Otherwise
	 * the existing sidebar is updated.
	 *
	 * @since  2.0
	 * @param  object $req Initial response object.
	 * @param  array $data Sidebar data to save (typically this is $_POST).
	 * @return object Updated response object.
	 */
	private function save_item( $req, $data ) {
		/**
		 * check nonce
		 */
		if (
			! isset( $data['_wpnonce'] )
			|| ! wp_verify_nonce( $data['_wpnonce'], 'custom-sidebars-edit-sidebar' )
		) {
			return self::req_err(
				$req,
				__( 'You have no permission to do this operation.', 'custom-sidebars' )
			);
		}
		$sidebars = self::get_custom_sidebars();
		$sb_id = $req->id;
		$sb_desc = '';
		if ( isset( $data['description'] ) ) {
			$sb_desc = stripslashes( trim( $data['description'] ) );
		}
		$sb_name = isset( $data['name'] )? $data['name']:'';
		if ( function_exists( 'mb_substr' ) ) {
			$sb_name = mb_substr( stripslashes( trim( $sb_name ) ), 0, 40 );
		} else {
			$sb_name = substr( stripslashes( trim( $sb_name ) ), 0, 40 );
		}
		if ( empty( $sb_name ) ) {
			return self::req_err(
				$req,
				__( 'Sidebar-name cannot be empty', 'custom-sidebars' )
			);
		}
		if ( empty( $sb_id ) ) {
			// Create a new sidebar.
			$action = 'insert';
			$num = count( $sidebars );
			do {
				$num += 1;
				$sb_id = self::$sidebar_prefix . $num;
			} while ( self::get_sidebar( $sb_id, 'cust' ) );
			$sidebar = array(
				'id' => $sb_id,
			);
		} else {
			// Update existing sidebar
			$action = 'update';
			$sidebar = self::get_sidebar( $sb_id, 'cust' );
			if ( ! $sidebar ) {
				return self::req_err(
					$req,
					__( 'The sidebar does not exist', 'custom-sidebars' )
				);
			}
		}
		if ( function_exists( 'mb_strlen' ) ) {
			if ( mb_strlen( $sb_desc ) > 200 ) {
				$sb_desc = mb_substr( $sb_desc, 0, 200 );
			}
		} else {
			if ( strlen( $sb_desc ) > 200 ) {
				$sb_desc = substr( $sb_desc, 0, 200 );
			}
		}
		// Populate the sidebar object.
		if ( 'insert' == $action || self::wpml_is_default_lang() ) {
			$sidebar['name'] = $sb_name;
			$sidebar['description'] = $sb_desc;
		} else {
			$sidebar['name_lang'] = $sb_name;
			$sidebar['description_lang'] = $sb_desc;
		}
		foreach ( array( 'before', 'after' ) as $prefix ) {
			foreach ( array( 'widget', 'title' ) as $sufix ) {
				$name = sprintf( '%s_%s', $prefix, $sufix );
				$sidebar[ $name ] = '';
				if ( isset( $_POST[ $name ] ) ) {
					$sidebar[ $name ] = stripslashes( trim( $_POST[ $name ] ) );
				}
			}
		}
		if ( 'insert' == $action ) {
			$sidebars[] = $sidebar;
			$req->message = sprintf(
				__( 'Created new sidebar <strong>%1$s</strong>', 'custom-sidebars' ),
				esc_html( $sidebar['name'] )
			);
		} else {
			$found = false;
			foreach ( $sidebars as $ind => $item ) {
				if ( $item['id'] == $sb_id ) {
					$req->message = sprintf(
						__( 'Updated sidebar <strong>%1$s</strong>', 'custom-sidebars' ),
						esc_html( $sidebar['name'] )
					);
					$sidebars[ $ind ] = $sidebar;
					$found = true;
					break;
				}
			}
			if ( ! $found ) {
				return self::req_err(
					$req,
					__( 'The sidebar was not found', 'custom-sidebars' )
				);
			}
		}
		// Save the changes.
		self::set_custom_sidebars( $sidebars );
		self::refresh_sidebar_widgets();
		$req->data = $sidebar;
		$req->action = $action;
		// Allow user to translate sidebar name/description via WPML.
		self::wpml_update( $sidebars );
		$req->data = self::wpml_translate( $sidebar );
		/**
		 * save user preferences (advance).
		 *
		 * @since 3.1.3
		 */
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$advance = get_user_option( 'custom-sidebars-editor-advance', $user_id );
			if ( ! is_array( $advance ) ) {
				$advance = array();
			}
			$advance[ $req->data['id'] ] = isset( $_POST['advance'] ) && 'show' === $_POST['advance'];
			update_user_option( $user_id, 'custom-sidebars-editor-advance', $advance );
		}
		return $req;
	}

	/**
	 * Delete the specified sidebar and update the response object.
	 *
	 * @since  2.0
	 * @since 3.0.8.1 Added the $data param.
	 *
	 * @param  object $req Initial response object.
	 * @param  array $data Sidebar data to save (typically this is $_POST).
	 * @return object Updated response object.
	 */
	private function delete_item( $req, $data ) {
		/**
		 * check nonce
		 */
		if (
			! isset( $data['_wpnonce'] )
			|| ! wp_verify_nonce( $data['_wpnonce'], 'custom-sidebars-delete-sidebar' )
		) {
			return self::req_err(
				$req,
				__( 'You have no permission to do this operation.', 'custom-sidebars' )
			);
		}
		$sidebars = self::get_custom_sidebars();
		$sidebar = self::get_sidebar( $req->id, 'cust' );
		if ( ! $sidebar ) {
			return self::req_err(
				$req,
				__( 'The sidebar does not exist', 'custom-sidebars' )
			);
		}
		$found = false;
		foreach ( $sidebars as $ind => $item ) {
			if ( $item['id'] == $req->id ) {
				$found = true;
				$req->message = sprintf(
					__( 'Deleted sidebar <strong>%1$s</strong>', 'custom-sidebars' ),
					esc_html( $req->sidebar['name'] )
				);
				unset( $sidebars[ $ind ] );
				break;
			}
		}
		if ( ! $found ) {
			return self::req_err(
				$req,
				__( 'The sidebar was not found', 'custom-sidebars' )
			);
		}
		// Save the changes.
		self::set_custom_sidebars( $sidebars );
		self::refresh_sidebar_widgets();
		return $req;
	}

	/**
	 * Save the repaceable flag of a theme sidebar.
	 *
	 * @since  2.0
	 * @param  object $req Initial response object.
	 * @return object Updated response object.
	 */
	private function set_replaceable( $req ) {
		$state = @$_POST['state'];
		$options = self::get_options();
		if ( 'true' === $state ) {
			$req->status = true;
			if ( ! in_array( $req->id, $options['modifiable'] ) ) {
				$options['modifiable'][] = $req->id;
			}
		} else {
			$req->status = false;
			foreach ( $options['modifiable'] as $i => $sb_id ) {
				if ( $sb_id == $req->id ) {
					unset( $options['modifiable'][ $i ] );
					break;
				}
			}
		}
		$options['modifiable'] = array_values( $options['modifiable'] );
		self::set_options( $options );
		$req->replaceable = (object) $options['modifiable'];
		return $req;
	}

	/**
	 * Populates the response object for the "get-location" ajax call.
	 * Location data defines where a custom sidebar is displayed, i.e. on which
	 * pages it is used and which theme-sidebars are replaced.
	 *
	 * @since  2.0
	 * @param  object $req Initial response object.
	 * @return object Updated response object.
	 */
	private function get_location_data( $req ) {
		$defaults = self::get_options();
		$raw_posttype = self::get_post_types( 'objects' );
		$raw_cat = self::get_all_categories();
		$raw_taxonomies = array(
			'_builtin' => self::get_taxonomies( 'objects', true ),
			'custom' => self::get_taxonomies( 'objects', false ),
		);
		$archive_type = array(
			'_blog' => __( 'Front Page', 'custom-sidebars' ),
			'_search' => __( 'Search Results', 'custom-sidebars' ),
			'_404' => __( 'Not Found (404)', 'custom-sidebars' ),
			'_authors' => __( 'Any Author Archive', 'custom-sidebars' ),
			'_date' => __( 'Date Archives', 'custom-sidebars' ),
		);
		/**
		 * taxonomies
		 *
		 * @since 3.0.7
		 */
		$default_taxonomies = array();
		foreach ( $raw_taxonomies['_builtin'] as $taxonomy ) {
			$default_taxonomies[] = $taxonomy->labels->singular_name;
			switch ( $taxonomy->name ) {
				case 'post_format':
				break;
				case 'post_tag':
					/**
				 * this a legacy and backward compatibility
				 */
					$archive_type['_tags'] = sprintf( __( '%s Archives', 'custom-sidebars' ), $taxonomy->labels->singular_name );
				break;
				case 'category':
					$archive_type[ '_'.$taxonomy->name ] = sprintf( __( '%s Archives', 'custom-sidebars' ), $taxonomy->labels->singular_name );
				break;
			}
		}
		foreach ( $raw_taxonomies['custom'] as $taxonomy ) {
			if ( in_array( $taxonomy->labels->singular_name, $default_taxonomies ) ) {
				$archive_type[ '_taxonomy_'.$taxonomy->name ] = sprintf( __( '%s Archives', 'custom-sidebars' ), ucfirst( $taxonomy->name ) );
			} else {
				$archive_type[ '_taxonomy_'.$taxonomy->name ] = sprintf( __( '%s Archives', 'custom-sidebars' ), $taxonomy->labels->singular_name );
			}
		}
		/**
		 * sort array by values
		 */
		asort( $archive_type );
		$raw_authors = array();
		$raw_authors = get_users(
			array(
				'order_by' => 'display_name',
				'fields' => array( 'ID', 'display_name' ),
				'who' => 'authors',
			)
		);
		// Collect required data for all posttypes.
		$posttypes = array();
		foreach ( $raw_posttype as $item ) {
			$sel_single = @$defaults['post_type_single'][ $item->name ];
			$posttypes[ $item->name ] = array(
				'name' => $item->labels->name,
				'single' => self::get_array( $sel_single ),
			);
		}
		// Extract the data from categories list that we need.
		$categories = array();
		foreach ( $raw_cat as $item ) {
			$sel_single = @$defaults['category_single'][ $item->term_id ];
			$sel_archive = @$defaults['category_archive'][ $item->term_id ];
			$categories[ $item->term_id ] = array(
				'name' => $item->name,
				'count' => $item->count,
				'single' => self::get_array( $sel_single ),
				'archive' => self::get_array( $sel_archive ),
			);
		}
		// Build a list of archive types.
		$archives = array(); // Start with a copy of the posttype list.
		foreach ( $raw_posttype as $item ) {
			if ( $item->name == 'post' ) {
				$label = __( 'Post Index', 'custom-sidebars' );
			} else {
				if ( ! $item->has_archive ) { continue; }
				$label = sprintf(
					__( '%1$s Archives', 'custom-sidebars' ),
					$item->labels->singular_name
				);
			}
			$sel_archive = @$defaults['post_type_archive'][ $item->name ];
			$archives[ $item->name ] = array(
				'name' => $label,
				'archive' => self::get_array( $sel_archive ),
			);
		}
		foreach ( $archive_type as $key => $name ) {
			$sel_archive = @$defaults[ substr( $key, 1 ) ];
			$archives[ $key ] = array(
				'name' => $name,
				'archive' => self::get_array( $sel_archive ),
			);
		}
		/**
		 * Custom taxonomies archive
		 *
		 * @since 3.0.7
		 */
		foreach ( $raw_taxonomies['custom'] as $t ) {
			$taxonomy = $t->name;
			if (
				isset( $defaults['taxonomies_archive'] )
				&& isset( $defaults['taxonomies_archive'][ $taxonomy ] )
			) {
				$name  = sprintf( __( '%s Archives', 'custom-sidebars' ), $t->labels->singular_name );
				if ( in_array( $t->labels->singular_name, $default_taxonomies ) ) {
					$name = sprintf( __( '%s Archives', 'custom-sidebars' ), ucfirst( $taxonomy ) );
				}
				$sel_archive = $defaults['taxonomies_archive'][ $taxonomy ];
				$key = '_taxonomy_'.$taxonomy;
				$archives[ $key ] = array(
					'name' => $name,
					'archive' => self::get_array( $sel_archive ),
				);
			}
		}
		/**
		 * Custom taxonomies taxes
		 *
		 * @since 3.1.4
		 */
		$allowed = get_option( $this->custom_taxonomies_name, array() );
		$args = array(
			'hide_empty' => true,
		);
		foreach ( $allowed as $key ) {
			$t = get_terms( $key, $args );
			$terms = array();
			foreach ( $t as $item ) {
				$sel_single = $sel_archive = array();
				if (
					isset( $defaults['taxonomies_single'] )
					&& isset( $defaults['taxonomies_single'][ $key ] )
					&& isset( $defaults['taxonomies_single'][ $key ][ $item->term_id ] )
				) {
					$sel_single = $defaults['taxonomies_single'][ $key ][ $item->term_id ];
				}
				if (
					isset( $defaults[ $key.'_archive' ] )
					&&  isset( $defaults[ $key.'_archive' ][ $item->term_id ] )
				) {
					$sel_archive = $defaults[ $key.'_archive' ][ $item->term_id ];
				}
				$terms[ $item->term_id ] = array(
					'name' => $item->name,
					'count' => $item->count,
					'single' => self::get_array( $sel_single ),
					'archive' => self::get_array( $sel_archive ),
				);
			}
			$req->$key = $terms;
		}
		/**
		 * Category archive.
		 */
		foreach ( $raw_taxonomies['_builtin'] as $t ) {
			if ( 'category' == $t->name ) {
				if ( isset( $defaults['category_archive'] ) ) {
					$sel_archive = $defaults['category_archive'];
					$archives['_category'] = array(
						'name' => sprintf( __( '%s Archives', 'custom-sidebars' ), $t->labels->singular_name ),
						'archive' => self::get_array( $sel_archive ),
					);
				}
			}
		}
		// Build a list of authors.
		$authors = array();
		foreach ( $raw_authors as $user ) {
			$sel_archive = @$defaults['author_archive'][ @$user->ID ];
			$authors[ @$user->ID ] = array(
				'name' => @$user->display_name,
				'archive' => self::get_array( $sel_archive ),
			);
		}
		$req->authors = $authors;
		$req->replaceable = $defaults['modifiable'];
		$req->posttypes = $posttypes;
		$req->categories = $categories;
		$req->archives = $archives;
		/**
		 * screen
		 */
		$screen = array();
		if (
			isset( $defaults['screen'] )
			&& isset( $defaults['screen'][ $req->id ] )
		) {
			$screen = $defaults['screen'][ $req->id ];
		}
		$req->screen = $screen;
		/**
		 * Allow to change data of locations.
		 *
		 * @since 3.1.2
		 *
		 * @param object $req Object of avaialble data.
		 * @pages $defaults Data from db.
		 */
		return apply_filters( 'custom_sidebars_get_location', $req, $defaults );
	}

	/**
	 * Save location data for a single sidebar and populate the response object.
	 * Location data defines where a custom sidebar is displayed, i.e. on which
	 * pages it is used and which theme-sidebars are replaced.
	 *
	 * @since  2.0
	 * @param  object $req Initial response object.
	 * @return object Updated response object.
	 */
	private function set_location_data( $req ) {
		/**
		 * check nonce
		 */
		if (
			! isset( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'custom-sidebars-set-location' )
		) {
			return self::req_err(
				$req,
				__( 'You have no permission to do this operation.', 'custom-sidebars' )
			);
		}
		$options = self::get_options();
		$sidebars = $options['modifiable'];
		$raw_posttype = self::get_post_types( 'objects' );
		$raw_cat = self::get_all_categories();
		$data = array();
		$raw_taxonomies = array(
			'custom' => self::get_taxonomies( 'names', false ),
		);
		foreach ( $_POST as $key => $value ) {
			if ( strlen( $key ) > 8 && '___cs___' == substr( $key, 0, 8 ) ) {
				list( $prefix, $id ) = explode( '___', substr( $key, 8 ) );
				if ( ! isset( $data[ $prefix ] ) ) {
					$data[ $prefix ] = array();
				}
				$data[ $prefix ][ $id ] = $value;
			}
		}
		$special_arc = array(
			'blog',
			'404',
			'tags',
			'authors',
			'search',
			'date',
		);
		$raw_authors = array();
		$raw_authors = get_users(
			array(
				'order_by' => 'display_name',
				'fields' => array( 'ID', 'display_name' ),
				'who' => 'authors',
			)
		);
		// == Update the options
		foreach ( $sidebars as $sb_id ) {
			// Post-type settings.
			foreach ( $raw_posttype as $item ) {
				$pt = $item->name;
				if (
					is_array( @$data['pt'][ $sb_id ] ) &&
					in_array( $pt, $data['pt'][ $sb_id ] )
				) {
					$options['post_type_single'][ $pt ][ $sb_id ] = $req->id;
				} elseif (
					isset( $options['post_type_single'][ $pt ][ $sb_id ] ) &&
					$options['post_type_single'][ $pt ][ $sb_id ] == $req->id
				) {
					unset( $options['post_type_single'][ $pt ][ $sb_id ] );
				}
				if (
					is_array( @$data['arc'][ $sb_id ] ) &&
					in_array( $pt, $data['arc'][ $sb_id ] )
				) {
					$options['post_type_archive'][ $pt ][ $sb_id ] = $req->id;
				} elseif (
					isset( $options['post_type_archive'][ $pt ][ $sb_id ] ) &&
					$options['post_type_archive'][ $pt ][ $sb_id ] == $req->id
				) {
					unset( $options['post_type_archive'][ $pt ][ $sb_id ] );
				}
			}
			// Category settings.
			foreach ( $raw_cat as $item ) {
				$cat = $item->term_id;
				if (
					is_array( @$data['cat'][ $sb_id ] ) &&
					in_array( $cat, $data['cat'][ $sb_id ] )
				) {
					$options['category_single'][ $cat ][ $sb_id ] = $req->id;
				} elseif (
					isset( $options['category_single'][ $cat ][ $sb_id ] ) &&
					$options['category_single'][ $cat ][ $sb_id ] == $req->id
				) {
					unset( $options['category_single'][ $cat ][ $sb_id ] );
				}
				if (
					is_array( @$data['arc-cat'][ $sb_id ] ) &&
					in_array( $cat, $data['arc-cat'][ $sb_id ] )
				) {
					$options['category_archive'][ $cat ][ $sb_id ] = $req->id;
				} elseif (
					isset( $options['category_archive'][ $cat ][ $sb_id ] ) &&
					$options['category_archive'][ $cat ][ $sb_id ] == $req->id
				) {
					unset( $options['category_archive'][ $cat ][ $sb_id ] );
				}
			}
			foreach ( $special_arc as $key ) {
				if (
					is_array( @$data['arc'][ $sb_id ] ) &&
					in_array( '_' . $key, $data['arc'][ $sb_id ] )
				) {
					$options[ $key ][ $sb_id ] = $req->id;
				} elseif (
					isset( $options[ $key ][ $sb_id ] ) &&
					$options[ $key ][ $sb_id ] == $req->id
				) {
					unset( $options[ $key ][ $sb_id ] );
				}
			}
			// Author settings.
			foreach ( $raw_authors as $user ) {
				$key = $user->ID;
				if (
					is_array( @$data['arc-aut'][ $sb_id ] ) &&
					in_array( $key, $data['arc-aut'][ $sb_id ] )
				) {
					$options['author_archive'][ $key ][ $sb_id ] = $req->id;
				} elseif (
					isset( $options['author_archive'][ $key ][ $sb_id ] ) &&
					$options['author_archive'][ $key ][ $sb_id ] == $req->id
				) {
					unset( $options['author_archive'][ $key ][ $sb_id ] );
				}
			}
			/**
			 * Custom taxonomies
			 *
			 * @since 3.0.7
			 */
			foreach ( $raw_taxonomies['custom'] as $taxonomy ) {
				$key = '_taxonomy_'.$taxonomy;
				if (
					isset( $data['arc'][ $sb_id ] )
					&& is_array( $data['arc'][ $sb_id ] )
					&& in_array( $key,  $data['arc'][ $sb_id ] )
				) {
					$options['taxonomies_archive'][ $taxonomy ][ $sb_id ] = $req->id;
				} elseif (
					isset( $options['taxonomies_archive'][ $taxonomy ][ $sb_id ] ) &&
					$options['taxonomies_archive'][ $taxonomy ][ $sb_id ] == $req->id
				) {
					unset( $options['taxonomies_archive'][ $taxonomy ][ $sb_id ] );
				}
				/**
				 * single custom taxonomy
				 *
				 * @since 3.1.4
				 */
				foreach ( $raw_taxonomies['custom'] as $taxonomy ) {
					$key = '_taxonomy_'.$taxonomy.'_single';
					$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
					foreach ( $terms as $term ) {
						$term_id = $term->term_id;
						if (
							isset( $data[ $taxonomy ][ $sb_id ] )
							&& is_array( $data[ $taxonomy ][ $sb_id ] )
							&& in_array( $term_id,  $data[ $taxonomy ][ $sb_id ] )
						) {
							$options['taxonomies_single'][ $taxonomy ][ $term_id ][ $sb_id ] = $req->id;
						} elseif (
							isset( $options['taxonomies_single'][ $taxonomy ][ $term_id ][ $sb_id ] ) &&
							$options['taxonomies_single'][ $taxonomy ][ $term_id ][ $sb_id ] == $req->id
						) {
							unset( $options['taxonomies_single'][ $taxonomy ][ $term_id ][ $sb_id ] );
						}
					}
				}
			}
			/**
			 * category Archive
			 *
			 * @since 3.0.7
			 */
			if (
				isset( $data['arc'][ $sb_id ] )
				&& is_array( $data['arc'][ $sb_id ] )
				&& in_array( '_category',  $data['arc'][ $sb_id ] )
			) {
				$options['category_archive'][ $sb_id ] = $req->id;
			} elseif (
				isset( $options['category_archive'][ $sb_id ] ) &&
				$options['category_archive'][ $sb_id ] == $req->id
			) {
				unset( $options['category_archive'][ $sb_id ] );
			}
		}

		/**
		 * screen size
		 */
			$size = array();
		if (
			isset( $_POST['cs-screen'] )
			&& isset( $_POST['cs-screen']['mode'] )
			&& isset( $_POST['cs-screen']['minmax'] )
			&& isset( $_POST['cs-screen']['size'] )
			&& is_array( $_POST['cs-screen']['mode'] )
			&& is_array( $_POST['cs-screen']['minmax'] )
			&& is_array( $_POST['cs-screen']['size'] )
		) {
			$screen_size = $_POST['cs-screen'];
			for ( $i = 0; $i < count( $screen_size['size'] ); $i++ ) {
				if ( ! empty( $screen_size['size'][ $i ] ) ) {
					$size[ $screen_size['size'][ $i ] ][ $screen_size['minmax'][ $i ] ] = $screen_size['mode'][ $i ];
				}
			}
			krsort( $size );
		}
		$options['screen'][ $req->id ] = $size;
		$req->message = sprintf(
			__( 'Updated sidebar <strong>%1$s</strong> settings.', 'custom-sidebars' ),
			esc_html( $req->sidebar['name'] )
		);
		/**
		 * Allow to change data of locations before save.
		 *
		 * @since 3.1.2
		 *
		 * @param array $options Current options to save.
		 * @param string $req->id Sidebar
		 * @param array $sidebars Allowed sidebars.
		 * @oaram array $data Data send by request.
		 */
		$options = apply_filters( 'custom_sidebars_set_location', $options, $req->id, $sidebars, $data );
		self::set_options( $options );
		return $req;
	}

	/**
	 * Registers the "Sidebars" meta box in the post-editor.
	 */
	public function add_meta_box() {
		global $post;
		/**
		 * check capabilities
		 */
		$user_can_change_sidebars = $this->check_author_ability_to_replace();
		if ( ! $user_can_change_sidebars ) {
			return;
		}
		$post_type = get_post_type( $post );
		if ( ! $post_type ) {
			return false;
		}
		if ( ! self::supported_post_type( $post_type ) ) {
			return false;
		}
		/**
		 * Option that can be set in wp-config.php to remove the custom sidebar
		 * meta box for certain post types.
		 *
		 * @since  2.0
		 *
		 * @option bool TRUE will hide all meta boxes.
		 */
		if (
			defined( 'CUSTOM_SIDEBAR_DISABLE_METABOXES' ) &&
			CUSTOM_SIDEBAR_DISABLE_METABOXES == true
		) {
			return false;
		}
		$pt_obj = get_post_type_object( $post_type );
		if ( $pt_obj->publicly_queryable || $pt_obj->public ) {
			add_meta_box(
				'customsidebars-mb',
				__( 'Sidebars', 'custom-sidebars' ),
				array( $this, 'print_metabox_editor' ),
				$post_type,
				'side'
			);
		}
	}

	/**
	 * Renders the Custom Sidebars meta box in the post-editor.
	 */
	public function print_metabox_editor() {
		global $post;
		$this->print_sidebars_form( $post->ID, 'metabox' );
	}

	/**
	 * Renders the sidebar-fields inside the quick-edit form.
	 */
	public function print_metabox_quick() {
		$this->print_sidebars_form( 0, 'quick-edit' );
	}

	/**
	 * Renders the Custom Sidebars form.
	 *
	 * @param  int $post_id The post-ID to display
	 * @param  string $type Which form to display. 'metabox/quick-edit/col-sidebars'.
	 */
	protected function print_sidebars_form( $post_id, $type = 'metabox' ) {
		/**
		 * check capabilities
		 */
		$user_can_change_sidebars = $this->check_author_ability_to_replace();
		if ( ! $user_can_change_sidebars ) {
			return;
		}
		global $wp_registered_sidebars;
		$available = CustomSidebars::sort_sidebars_by_name( $wp_registered_sidebars );
		$replacements = self::get_replacements( $post_id );
		$sidebars = self::get_options( 'modifiable' );
		$selected = array();
		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $s ) {
				if ( isset( $replacements[ $s ] ) ) {
					$selected[ $s ] = $replacements[ $s ];
				} else {
					$selected[ $s ] = '';
				}
			}
		}
		switch ( $type ) {
			case 'col-sidebars':
				include CSB_VIEWS_DIR . 'col-sidebars.php';
				break;
			case 'quick-edit':
				include CSB_VIEWS_DIR . 'quick-edit.php';
				break;
			case 'bulk-edit':
				/**
				 * @since 3.0.8
				 */
				include CSB_VIEWS_DIR . 'bulk-edit.php';
				break;
			default:
				include CSB_VIEWS_DIR . 'metabox.php';
				break;
		}
	}

	public function store_replacements( $post_id ) {
		global $action;
		/**
		 * check capabilities
		 */
		$user_can_change_sidebars = $this->check_author_ability_to_replace();
		if ( ! $user_can_change_sidebars ) {
			return;
		}
		/*
		 * Verify if this is an auto save routine. If it is our form has not
		 * been submitted, so we dont want to do anything
		 * (Copied and pasted from wordpress add_metabox_tutorial)
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		/*
		 * 'editpost' .. Saved from full Post-Editor screen.
		 * 'inline-save' .. Saved via the quick-edit form.
		 */
		if ( ( isset( $_REQUEST['action'] ) && 'inline-save' != $_REQUEST['action'] ) && 'editpost' != $action  ) {
			return $post_id;
		}
		// Make sure meta is added to the post, not a revision.
		if ( $the_post = wp_is_post_revision( $post_id ) ) {
			$post_id = $the_post;
		}
		$sidebars = self::get_options( 'modifiable' );
		$data = array();
		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $sb_id ) {
				if ( isset( $_POST[ 'cs_replacement_' . $sb_id ] ) ) {
					$replacement = $_POST[ 'cs_replacement_' . $sb_id ];
					if ( ! empty( $replacement ) ) {
						$data[ $sb_id ] = $replacement;
					}
				}
			}
		}
		self::set_post_meta( $post_id, $data );
	}

	// ========== WPML support.
	/**
	 * Updates the WPML string register with the current sidebar string so the
	 * user can translate the sidebar details using the WPML string translation.
	 *
	 * @since  2.0.9.7
	 * @param  array $custom_sidebars List of the custom sidebars.
	 */
	static protected function wpml_update( $custom_sidebars ) {
		if ( ! function_exists( 'icl_register_string' ) ) { return false; }
		$theme_sidebars = self::get_sidebars();
		// This is used to identify the sidebar-translations by WPML.
		$context = 'Sidebar';
		// First do the theme sidebars, so they will be displayed in the
		// *bottom* of the translations list.
		foreach ( $theme_sidebars as $fields ) {
			self::wpml_update_field( $context, $fields['id'] . '-name', @$fields['name'], false );
			self::wpml_update_field( $context, $fields['id'] . '-description', @$fields['description'], false );
		}
		foreach ( $custom_sidebars as $fields ) {
			$name = isset( $fields['name_lang'] ) ? $fields['name_lang'] : $fields['name'];
			$description = isset( $fields['description_lang'] ) ? $fields['description_lang'] : $fields['description'];
			self::wpml_update_field( $context, $fields['id'] . '-name', $name, true );
			self::wpml_update_field( $context, $fields['id'] . '-description', $description, true );
		}
	}

	/**
	 * Updates the WPML string register for a single field.
	 *
	 * @since  2.0.9.7
	 * @param  string $context
	 * @param  string $field
	 * @param  string $value
	 * @param  bool $update_string If false then the translation will only be
	 *                registered but not updated.
	 */
	static protected function wpml_update_field( $context, $field, $value, $update_string = true ) {
		global $sitepress, $sitepress_settings;
		if ( empty( $sitepress ) || empty( $sitepress_settings ) ) { return false; }
		if ( ! function_exists( 'icl_t' ) ) { return false; }
		if ( ! icl_st_is_registered_string( $context, $field ) ) {
			// Register the field if it does not exist.
			icl_register_string( $context, $field, $value, false );
			$active_languages = $sitepress->get_active_languages();
			foreach ( $active_languages as $lang => $data ) {
				icl_update_string_translation( $field, $lang, $value, ICL_STRING_TRANSLATION_COMPLETE );
			}
			$default_language = ! empty( $sitepress_settings['st']['strings_language'] )
				? $sitepress_settings['st']['strings_language']
				: $sitepress->get_default_language();
			icl_update_string_translation( $field, $default_language, $value, ICL_STRING_TRANSLATION_COMPLETE );
		} else if ( $update_string ) {
			// Add translation.
			if ( defined( 'DOING_AJAX' ) ) {
				$current_language = $sitepress->get_language_cookie();
			} else {
				$current_language = $sitepress->get_current_language();
			}
			icl_update_string_translation( $field, $current_language, $value, ICL_STRING_TRANSLATION_COMPLETE );
		}
	}

	/**
	 * Returns boolean true, when site is currently using the default language.
	 *
	 * @since  2.0.9.7
	 * @return bool
	 */
	static protected function wpml_is_default_lang() {
		global $sitepress, $sitepress_settings;
		if ( empty( $sitepress ) || empty( $sitepress_settings ) ) { return true; }
		if ( ! function_exists( 'icl_t' ) ) { return true; }
		if ( defined( 'DOING_AJAX' ) ) {
			$current_language = $sitepress->get_language_cookie();
		} else {
			$current_language = $sitepress->get_current_language();
		}
		$default_language = ! empty( $sitepress_settings['st']['strings_language'] )
			? $sitepress_settings['st']['strings_language']
			: $sitepress->get_default_language();
		return $default_language == $current_language;
	}

	/**
	 * Translates the text inside the specified sidebar object.
	 *
	 * @since  2.0.9.7
	 * @param  array $sidebar Sidebar object.
	 * @return array Translated sidebar object.
	 */
	static protected function wpml_translate( $sidebar ) {
		if ( ! function_exists( 'icl_t' ) ) { return $sidebar; }
		$context = 'Sidebar';
		// Translate the name and description.
		// Note: When changing a translation the icl_t() function will not
		// return the updated value due to caching.
		if ( isset( $sidebar['name_lang'] ) ) {
			$sidebar['name'] = $sidebar['name_lang'];
		} else {
			$sidebar['name'] = icl_t( $context, $sidebar['id'] . '-name', $sidebar['name'] );
		}
		if ( isset( $sidebar['description_lang'] ) ) {
			$sidebar['description'] = $sidebar['description_lang'];
		} else {
			$sidebar['description'] = icl_t( $context, $sidebar['id'] . '-description', $sidebar['description'] );
		}
		return $sidebar;
	}

	//
	// ========== Custom column an Quick-Edit fields for post list.
	// http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu
	//
	/**
	 * Adds a custom column to post-types that support custom sidebars.
	 *
	 * @since  2.0.9.7
	 * @param  array $columns Column list.
	 * @return array Modified column list.
	 */
	public function post_columns( $columns ) {
		// This column is added.
		$insert = array(
			'cs_replacement' => _x( 'Custom Sidebars', 'Column name on entries list.', 'custom-sidebars' ),
		);
		// Column is added after column 'title'.
		$insert_after = 'title';
		$pos = array_search( $insert_after, array_keys( $columns ) ) + 1;
		$columns = array_merge(
			array_slice( $columns, 0, $pos ),
			$insert,
			array_slice( $columns, $pos )
		);
		return $columns;
	}

	/**
	 * Display values in the custom column.
	 *
	 * @since  2.0.9.7
	 * @param  string $column_name Column-Key defined in post_columns above.
	 * @param  int $post_id Post-ID
	 */
	public function post_column_content( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'cs_replacement':
				$this->print_sidebars_form( $post_id, 'col-sidebars' );
				break;
		}
	}

	/**
	 * Adds a custom field to the quick-edit box to select custom columns.
	 *
	 * @since  2.0.9.7
	 * @param  string $column_name Column-Key defined in post_columns above.
	 * @param  string $post_type Post-type that is currently edited.
	 */
	public function post_quick_edit( $column_name, $post_type ) {
		if ( ! self::supported_post_type( $post_type ) ) { return false; }
		switch ( $column_name ) {
			case 'cs_replacement':
				$this->print_metabox_quick();
				break;
		}
	}

	/**
	 * Javascript to set the values of the quick-edit form.
	 *
	 * Note: There is only 1 quick-edit form on the page. The form is displayed
	 * when the user clicks the quick edit action; all fields are then populated
	 * with values of the corresponding post.
	 *
	 * @since  2.0.9.7
	 */
	public function post_quick_edit_js() {
		global $current_screen;
		if ( ( $current_screen->base != 'edit' ) ) { return false; }
		if ( ! self::supported_post_type( $current_screen->post_type ) ) { return false; }
		?>
		<script type="text/javascript">
		<!--
		jQuery(function() {
			// we create a copy of the WP inline edit post function
			var wp_inline_edit = inlineEditPost.edit;
			// and then we overwrite the function with our own code
			inlineEditPost.edit = function( id ) {
				// "call" the original WP edit function
				// we don't want to leave WordPress hanging
				wp_inline_edit.apply( this, arguments );
				// now we take care of our business.
				// get the post ID
				var post_id = 0;
				if ( typeof( id ) == 'object' ) {
					post_id = parseInt( this.getId( id ) );
				}
				if ( post_id > 0 ) {
					// define the edit row
					var edit_row = jQuery( '#edit-' + post_id );
					var post_row = jQuery( '#post-' + post_id );
					// Our custom column
					var sidebar_col = post_row.find( '.cs_replacement' );
					sidebar_col.find( '[data-sidebar]' ).each(function() {
						var key = jQuery( this ).attr( 'data-sidebar' ),
							val = jQuery( this ).attr( 'data-replaced' ),
							hide = 'yes' === jQuery( this ).attr( 'data-cshide' );
						if ( hide ) {
							edit_row.find( '.cs-replacement-field.' + key ).val( val ).parent().hide();
						} else {
							edit_row.find( '.cs-replacement-field.' + key ).val( val ).parent().show();
						}
					});
				}
			};
		});
		//-->
		</script>
		<?php
	}

	/**
	 * Hide column "Custom Sidebars" by default.
	 *
	 * @since 3.0.5
	 *
	 * @param array $hidden Array of hidden columns.
	 * @param WP_Screen $screen Current WP screen.
	 * @return array $hidden
	 */
	public function default_hidden_columns( $hidden, $screen ) {
		if ( is_object( $screen ) && isset( $screen->post_type ) && 'post' == $screen->post_type ) {
			$hidden[] = 'cs_replacement';
		}
		return $hidden;
	}

	/**
	 * Adds a custom field to the bulk-edit box to select custom columns.
	 *
	 * @since  3.0.8
	 * @param  string $column_name Column-Key defined in post_columns above.
	 * @param  string $post_type Post-type that is currently edited.
	 */
	public function post_bulk_edit( $column_name, $post_type ) {
		if ( ! self::supported_post_type( $post_type ) ) { return false; }
		switch ( $column_name ) {
			case 'cs_replacement':
				$this->print_metabox_bulk();
				break;
		}
	}

	/**
	 * Renders the sidebar-fields inside the bulk-edit form.
	 *
	 * @since 3.0.8
	 */
	public function print_metabox_bulk() {
		$this->print_sidebars_form( 0, 'bulk-edit' );
	}

	/**
	 * Bulk Edit save
	 *
	 * @since 3.0.8
	 */
	public function bulk_edit_save( $post_id ) {
		if ( ! isset( $_REQUEST['custom-sidebars-editor-bulk-edit'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_REQUEST['custom-sidebars-editor-bulk-edit'], 'bulk-edit-cs' ) ) {
			return;
		}
		if ( null == $this->modifiable ) {
			$this->modifiable = CustomSidebars::get_options( 'modifiable' );
		}
		if ( empty( $this->modifiable ) ) {
			return;
		}
		$update = false;
		$data = CustomSidebars::get_post_meta( $post_id );
		foreach ( $this->modifiable as $key ) {
			$k = sprintf( 'cs_replacement_%s', $key );
			$value = isset( $_REQUEST[ $k ] )? $_REQUEST[ $k ]:'-';
			if ( '-' != $value ) {
				$update = true;
				$data[ $key ] = $value;
			}
		}
		if ( ! $update ) {
			return;
		}
		self::set_post_meta( $post_id, $data );
	}

	/**
	 * Add capabilities for options on widgets.php
	 *
	 * @param string    $screen_settings Screen settings.
	 * @param WP_Screen $screen          WP_Screen object.
	 */
	public function add_capabilities_select_box( $screen_settings, $screen ) {
		if ( 'widgets' == $screen->base && current_user_can( 'manage_options' ) ) {
			$allowed = get_option( $this->metabox_roles_name, 'any' );
			$roles = get_editable_roles();
			$screen_settings .= '<fieldset class="metabox-prefs cs-roles">';
			$screen_settings .= wp_nonce_field( $this->metabox_roles_name, $this->metabox_roles_name, false, false );
			$screen_settings .= sprintf( '<legend>%s</legend>', __( 'Custom sidebars configuration is allowed for:', 'custom-sidebars' ) );
			foreach ( $roles as $role => $data ) {
				if ( isset( $data['capabilities'][ self::$cap_required ] ) && $data['capabilities'][ self::$cap_required ] ) {
					$checked = false;
					if ( is_string( $allowed ) && 'any' == $allowed ) {
						$checked = true;
					} else if ( is_array( $allowed ) && in_array( $role, $allowed ) ) {
						$checked = true;
					}
					$screen_settings .= sprintf(
						'<label><input type="checkbox" name="cs-roles[]" value="%s" %s /> %s</label>',
						esc_attr( $role ),
						checked( $checked, true, false ),
						esc_html( $data['name'] )
					);
				}
			}
			$screen_settings .= '</fieldset>';
			/**
			 * allow author to edit custom sidebars
			 */
			$screen_settings .= '<fieldset class="metabox-prefs cs-allow-author">';
			$screen_settings .= wp_nonce_field( $this->allow_author_name, $this->allow_author_name, false, false );
			$screen_settings .= sprintf( '<legend>%s</legend>', __( 'Allowed to replace sidebars:', 'custom-sidebars' ) );
			$checked = get_option( $this->allow_author_name, false );
			$screen_settings .= sprintf(
				'<label><input type="checkbox" name="cs-allow-author" value="%s" %s /> %s</label>',
				esc_attr( $role ),
				checked( $checked, true, false ),
				esc_html__( 'Allow entry author to change sidebars', 'custom-sidebars' )
			);
			$screen_settings .= '</fieldset>';
			/**
			 * Custom taxonomies
			 */
			$taxonomies = CustomSidebars::get_custom_taxonomies();
			if ( ! empty( $taxonomies ) ) {
				$allowed = get_option( $this->custom_taxonomies_name, array() );
				$screen_settings .= '<fieldset class="metabox-prefs cs-custom-taxonomies">';
				$screen_settings .= wp_nonce_field( $this->custom_taxonomies_name, $this->custom_taxonomies_name, false, false );
				$screen_settings .= sprintf( '<legend>%s</legend>', __( 'Allow Custom Taxonomies in Sidebar Location:', 'custom-sidebars' ) );
				foreach ( $taxonomies as $taxonomy_slug => $taxonomy ) {
					$checked = in_array( $taxonomy_slug, $allowed );
					$screen_settings .= sprintf(
						'<label><input type="checkbox" name="cs-custom-taxonomy[]" value="%s" %s /> %s</label>',
						esc_attr( $taxonomy_slug ),
						checked( $checked, true, false ),
						esc_html( $taxonomy->label )
					);
				}
				$screen_settings .= '</fieldset>';
				$screen_settings .= sprintf( '<p class="description">%s</p>', __( 'After turn on any Custom Taxonomy you need to reload this screen to be able choose it in Sidebar Location.', 'custom-sidebars' ) );
			}
		}
		return $screen_settings;
	}

	/**
	 * Update capabilities select box
	 *
	 * @since 3.0.9
	 */
	public function update_custom_sidebars_metabox_roles() {
		$this->update_option_field( $this->metabox_roles_name );
	}

	/**
	 * Update custom taxoniomies select box
	 *
	 * @since 3.1.4
	 */
	public function update_custom_sidebars_metabox_custom_taxonomies() {
		$this->update_option_field( $this->custom_taxonomies_name );
	}

	/**
	 * Helper for update metabox functions.
	 *
	 * @since 3.1.4
	 */
	private function update_option_field( $name ) {
		/**
		 * check required data
		 */
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_REQUEST['fields'] ) ) {
			wp_send_json_error();
		}
		/**
		 * check nonce value
		 */
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $name ) ) {
			wp_send_json_error();
		}
		$value = array();
		foreach ( $_REQUEST['fields'] as $role => $status ) {
			if ( 'true' == $status ) {
				$value[] = $role;
			}
		}
		$status = add_option( $name, $value, '', 'no' );
		if ( ! $status ) {
			update_option( $name, $value );
		}
		wp_send_json_success();
	}

	/**
	 * Update entry author is allowed to change custom sidebars.
	 *
	 * @since 3.1.5
	 */
	public function update_custom_sidebars_allow_author() {
		/**
		 * check required data
		 */
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			wp_send_json_error();
		}
		/**
		 * check nonce value
		 */
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $this->allow_author_name ) ) {
			wp_send_json_error();
		}
		$value = isset( $_REQUEST['value'] )? 'true' === $_REQUEST['value'] : false;
		$status = add_option( $this->allow_author_name, $value, '', 'no' );
		if ( ! $status ) {
			update_option( $this->allow_author_name, $value );
		}
		wp_send_json_success();
	}
	/**
	 * Check ability to save sidebars
	 *
	 * @since 3.0.9
	 */
	public function current_user_can_update_custom_sidebars() {
		$allowed = get_option( $this->metabox_roles_name, 'any' );
		if ( is_string( $allowed ) && 'any' == $allowed ) {
					return true;
		} else {
			$current_user = wp_get_current_user();
			$current_user_roles = (array) $current_user->roles;
			foreach ( $allowed as $role ) {
				if ( in_array( $role, $current_user_roles ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Get allowed taxoniomies.
	 *
	 * @since 3.1.4
	 */
	public function get_allowed_custom_taxonmies() {
		$value = get_option( $this->custom_taxonomies_name );
		if ( empty( $value ) || ! is_array( $value ) ) {
			return array();
		}
		return $value;
	}

	/**
	 * Check author ability to change sidebars during entry edit
	 *
	 * @since 3.1.5
	 */
	private function check_author_ability_to_replace() {
		global $post;
		if ( current_user_can( self::$cap_required ) ) {
			return true;
		}
		$checked = get_option( $this->allow_author_name, false );
		if ( $checked ) {
			if ( current_user_can( 'edit_posts', $post->ID ) ) {
				return true;
			}
		}
		return false;
	}
};
