<?php

add_action( 'cs_init', array( 'CustomSidebarsEditor', 'instance' ) );

/**
 * Provides all the functionality for editing sidebars on the widgets page.
 */
class CustomSidebarsEditor extends CustomSidebars {

	/**
	 * Returns the singleton object.
	 *
	 * @since  1.6
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
	 * @since  1.6
	 */
	private function __construct() {
		if ( is_admin() ) {
			// Add the sidebar metabox to posts.
			add_action(
				'add_meta_boxes',
				array( $this, 'add_meta_box' )
			);

			// Handle ajax requests.
			add_action(
				'cs_ajax_request',
				array( $this, 'handle_ajax' )
			);
		}
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

		$sb_id = @$_POST['sb'];
		$sb_data = self::get_sidebar( $sb_id );

		switch ( $action ) {
			case 'get':
				// Return details for the specified sidebar.
				$handle_it = true;
				$req->status = 'OK';
				$req->action = 'get';
				$req->id = $sb_id;
				$req->sidebar = $sb_data;
				break;

			case 'save':
				// Save or insert the specified sidebar.
				$handle_it = true;
				$req->status = 'OK';
				$req->action = 'save';
				$req->id = $sb_id;
				break;

			case 'delete':
				// Delete the specified sidebar.
				$handle_it = true;
				$req->status = 'OK';
				$req->action = 'delete';
				$req->id = $sb_id;
				break;

			case 'get-location':
				// Get the location data.
				$handle_it = true;
				$req->status = 'OK';
				$req->action = 'get-location';
				$req->id = $sb_id;
				$req->sidebar = $sb_data;
				$req = $this::get_location_data( $req );
				break;

			case 'set-location':
				// Update the location data.
				$handle_it = true;
				$req->status = 'OK';
				$req->action = 'set-location';
				$req->id = $sb_id;
				break;

			case 'replacable':
				// Toggle theme sidebar replacable-flag.
				$handle_it = true;
				$req->status = 'OK';
				$req->action = 'replacable';
				$req->id = $sb_id;
				break;
		}

		// The ajax request was not meant for us...
		if ( ! $handle_it ) {
			return false;
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
	 * Populates the response object for the "get-location" ajax call.
	 *
	 * @since  1.6.0
	 * @param  object $req Initial response object.
	 * @return object Updates response object.
	 */
	private function get_location_data( $req ) {
		$defaults = CustomSidebars::get_options();
		$raw_posttype = CustomSidebars::get_post_types( 'objects' );
		$raw_cat = CustomSidebars::get_all_categories();

		$archive_type = array(
			'_blog' => __( 'Front Page', CSB_LANG ),
			'_search' => __( 'Search Results', CSB_LANG ),
			'_authors' => __( 'Author Archives', CSB_LANG ),
			'_tags' => __( 'Tag Archives', CSB_LANG ),
			'_date' => __( 'Date Archives', CSB_LANG ),
		);

		// Collect required data for all posttypes.
		$posttypes = array();
		foreach ( $raw_posttype as $item ) {
			$sel_single = $defaults['post_type_single'][$item->name];

			$posttypes[ $item->name ] = array(
				'name' => $item->labels->name,
				'single' => $sel_single,
			);
		}

		// Extract the data from categories list that we need.
		$categories = array();
		foreach ( $raw_cat as $item ) {
			$sel_single = $defaults['category_single'][$item->name];
			$sel_archive = $defaults['category_archive'][$item->name];

			$categories[ $item->term_id ] = array(
				'name' => $item->name,
				'count' => $item->count,
				'single' => $sel_single,
				'archive' => $sel_archive,
			);
		}

		// Build a list of archive types.
		$archives = array(); // Start with a copy of the posttype list.
		foreach ( $raw_posttype as $item ) {
			$sel_archive = $defaults['post_type_archive'][$item->name];

			$label = sprintf(
				__( '%1$s Archives', CSB_LANG ),
				$item->labels->singular_name
			);

			$archives[ $item->name ] = array(
				'name' => $label,
				'archive' => $sel_archive,
			);
		}

		foreach ( $archive_type as $key => $name ) {
			$sel_archive = $defaults[substr( $key, 1 ) ];

			$archives[ $key ] = array(
				'name' => $name,
				'archive' => $sel_archive,
			);
		}

		$req->replaceable = $defaults['modifiable'];
		$req->posttypes = $posttypes;
		$req->categories = $categories;
		$req->archives = $archives;
		return $req;
	}

	/**
	 * Removes a single custom sidebar from the options.
	 */
	public function delete_sidebar() {
		if ( ! current_user_can( self::$cap_required ) ) {
			return new WP_Error( 'cscantdelete', __( 'You do not have permission to delete sidebars', CSB_LANG ) );
		}

		if ( ! DOING_AJAX && ! wp_verify_nonce( $_REQUEST['_n'], 'custom-sidebars-delete' ) ) {
			die( 'Security check stop your request.' );
		}

		$newsidebars = array();
		$deleted = FALSE;

		$custom = self::get_custom_sidebars();

		if ( ! empty( $custom ) ) {
			foreach ( $custom as $sb ) {
				if ( $sb['id'] != $_REQUEST['delete'] ) {
					$newsidebars[] = $sb;
				} else {
					$deleted = TRUE;
				}
			}
		}//endif custom

		//update option
		$this->set_custom_sidebars( $newsidebars );

		$this->refresh_sidebars_widgets();

		if ( $deleted ) {
			$this->set_message( sprintf( __( 'The sidebar "%s" has been deleted.', CSB_LANG ), $_REQUEST['delete'] ) );
		} else {
			$this->set_error( sprintf( __( 'There was not any sidebar called "%s" and it could not been deleted.', CSB_LANG ), $_GET['delete'] ) );
		}
	}

	/**
	 * Registers the "Sidebars" meta box in the post-editor.
	 */
	public function add_meta_box() {
		global $post;

		$post_type = get_post_type( $post );
		if ( ! $post_type ) { return false; }
		if ( ! self::supported_post_type( $post_type ) ) { return false; }

		/**
		 * Option that can be set in wp-config.php to remove the custom sidebar
		 * meta box for certain post types.
		 *
		 * @since  1.6
		 *
		 * @option bool TRUE will hide all meta boxes.
		 */
		if (
			defined( 'CUSTOM_SIDEBAR_DISABLE_METABOXES' ) &&
			CUSTOM_SIDEBAR_DISABLE_METABOXES == true
		) {
			return false;
		}

		$post_type_object = get_post_type_object( $post_type );
		if ( $post_type_object->publicly_queryable || $post_type_object->public ) {
			add_meta_box(
				'customsidebars-mb',
				__( 'Sidebars', CSB_LANG ),
				array( $this, 'print_metabox' ),
				$post_type,
				'side'
			);
		}
	}

	/**
	 * Renders the Custom Sidebars meta box in the post-editor.
	 */
	public function print_metabox() {
		global $post, $wp_registered_sidebars;

		$replacements = self::get_replacements( $post->ID );

		$available = $wp_registered_sidebars;
		ksort( $available );
		$sidebars = self::get_options( 'modifiable' );
		$selected = array();
		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $s ) {
				if ( isset( $replacements[$s] ) ) {
					$selected[$s] = $replacements[$s];
				} else {
					$selected[$s] = '';
				}
			}
		}

		include CSB_VIEWS_DIR . 'metabox.php';
	}

	public function update_modifiable() {
		check_admin_referer( 'custom-sidebars-options', 'options_wpnonce' );
		$options = self::get_options();

		//Modifiable bars
		if ( isset( $_POST['modifiable'] ) && is_array( $_POST['modifiable'] ) ) {
			$options['modifiable'] = $_POST['modifiable'];
		}

		self::set_options( $options );

		$this->set_message( __( 'The custom sidebars settings has been updated successfully.', CSB_LANG ) );
	}

	public function store_defaults() {
		$options = self::get_options();
		$modifiable = self::get_options( 'modifiable' );

		//Post-types posts and lists. Posts data are called default in order to keep backwards compatibility;

		$options['post_type_single'] = array();
		$options['post_type_archive'] = array();

		// "By post type"
		foreach ( self::get_post_types() as $pt ) {
			if ( ! empty( $modifiable ) ) {
				foreach ( $modifiable as $m ) {
					// single-posttype
					if ( isset( $_POST["type_posts_{$pt}_$m"] ) && $_POST["type_posts_{$pt}_$m"] != '' ) {
						if ( ! isset( $options['post_type_single'][$pt] ) ) {
							$options['post_type_single'][$pt] = array();
						}

						$options['post_type_single'][$pt][$m] = $_POST["type_posts_{$pt}_$m"];
					}

					// archive-posttype
					if ( isset( $_POST["type_page_{$pt}_$m"] ) && $_POST["type_page_{$pt}_$m"] != '' ) {
						if ( ! isset( $options['post_type_archive'][$pt] ) ) {
							$options['post_type_archive'][$pt] = array();
						}

						$options['post_type_archive'][$pt][$m] = $_POST["type_page_{$pt}_$m"];
					}
				}
			}
		}

		// Category posts and post lists.
		$options['category_posts'] = array();
		$options['category_pages'] = array();
		$categories = get_categories( array( 'hide_empty' => 0 ) );
		foreach ( $categories as $c ) {
			if ( ! empty( $modifiable ) ) {
				foreach ( $modifiable as $m ) {
					$catid = $c->cat_ID;
					// single-categories
					if ( isset( $_POST["category_posts_{$catid}_$m"] ) && $_POST["category_posts_{$catid}_$m"] != '' ) {
						if ( ! isset( $options['category_posts'][$catid] ) ) {
							$options['category_posts'][$catid] = array();
						}

						$options['category_posts'][$catid][$m] = $_POST["category_posts_{$catid}_$m"];
					}

					// archive-category
					if ( isset( $_POST["category_page_{$catid}_$m"] ) && $_POST["category_page_{$catid}_$m"] != '' ) {
						if ( ! isset( $options['category_pages'][$catid] ) ) {
							$options['category_pages'][$catid] = array();
						}

						$options['category_pages'][$catid][$m] = $_POST["category_page_{$catid}_$m"];
					}
				}
			}
		}

		// Blog page
		$options['blog'] = array();
		if ( ! empty( $modifiable ) ) {
			foreach ( $modifiable as $m ) {
				if ( isset( $_POST["blog_page_$m"] ) && $_POST["blog_page_$m"] != '' ) {
					if ( ! isset( $options['blog'] ) ) {
						$options['blog'] = array();
					}

					$options['blog'][$m] = $_POST["blog_page_$m"];
				}
			}
		}

		// Tag page
		$options['tags'] = array();
		if ( ! empty( $modifiable ) ) {
			foreach ( $modifiable as $m ) {
				if ( isset( $_POST["tag_page_$m"] ) && $_POST["tag_page_$m"] != '' ) {
					if ( ! isset( $options['tags'] ) ) {
						$options['tags'] = array();
					}

					$options['tags'][$m] = $_POST["tag_page_$m"];
				}
			}
		}

		// Author page
		$options['authors'] = array();
		if ( ! empty( $modifiable ) ) {
			foreach ( $modifiable as $m ) {
				if ( isset( $_POST["authors_page_$m"] ) && $_POST["authors_page_$m"] != '' ) {
					if ( ! isset( $options['authors'] ) ) {
						$options['authors'] = array();
					}

					$options['authors'][$m] = $_POST["authors_page_$m"];
				}
			}
		}

		// Search page
		$options['search'] = array();
		if ( ! empty( $modifiable ) ) {
			foreach ( $modifiable as $m ) {
				if ( isset( $_POST["search_page_$m"] ) && $_POST["search_page_$m"] != '' ) {
					if ( ! isset( $options['search'] ) ) {
						$options['search'] = array();
					}

					$options['search'][$m] = $_POST["search_page_$m"];
				}
			}
		}

		// Date archive
		$options['date'] = array();
		if ( ! empty( $modifiable ) ) {
			foreach ( $modifiable as $m ) {
				if ( isset( $_POST["date_page_$m"] ) && $_POST["date_page_$m"] != '' ) {
					if ( ! isset( $options['date'] ) ) {
						$options['date'] = array();
					}

					$options['date'][$m] = $_POST["date_page_$m"];
				}
			}
		}


		//Store defaults
		self::set_options( $options );

		$this->set_message( __( 'The default sidebars have been updated successfully.', CSB_LANG ) );

	}

	public function store_replacements( $post_id ) {
		if ( ! current_user_can( self::$cap_required ) ) {
			return;
		}

		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
		// to do anything (Copied and pasted from wordpress add_metabox_tutorial)
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		global $action;

		//Get sure we are editing the post normaly, if we are bulk editing or quick editing,
		//no sidebars data is recieved and the sidebars would be deleted.
		if ( $action != 'editpost' ) {
			return $post_id;
		}

		// make sure meta is added to the post, not a revision
		if ( $the_post = wp_is_post_revision( $post_id ) ) {
			$post_id = $the_post;
		}

		$sidebars = self::get_options( 'modifiable' );
		$data = array();
		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $s ) {
				if ( isset( $_POST["cs_replacement_$s"] ) ) {
					$replacement = $_POST["cs_replacement_$s"];
					if ( ! empty( $replacement ) && $replacement != '' ) {
						$data[$s] = $replacement;
					}
				}
			}
		}//endif sidebars

		self::set_post_meta( $post_id, $data );
		$old_data = self::get_post_meta( $post_id );
	}

	public function store_sidebar() {
		$name = stripslashes( trim( $_POST['sidebar_name'] ) );
		$description = stripslashes( trim( $_POST['sidebar_description'] ) );
		if ( empty( $name ) OR empty( $description ) ) {
			$this->set_error( __( 'You have to fill all the fields to create a new sidebar.', CSB_LANG ) );
		} else {
			$id = self::$sidebar_prefix . sanitize_html_class( sanitize_title_with_dashes( $name ) );
			$sidebars = self::get_custom_sidebars();

			if ( ! self::get_sidebar( $id ) ) {
				//Create a new sidebar
				$sidebars[] = array(
					'name' => $name,
					'id' => $id,
					'description' => $description,
					'before_widget' => '', //all these fields are not needed, theme ones will be used
					'after_widget' => '',
					'before_title' => '',
					'after_title' => '',
				) ;

				//update option
				$this->set_custom_sidebars( $sidebars );
				$this->refresh_sidebars_widgets();
				$this->set_message( __( 'The sidebar has been created successfully.', CSB_LANG ) );

			} else {
				$this->set_error( __( 'There is already a sidebar registered with that name, please choose a different one.', CSB_LANG ) );
			}
		}
	}

	public function update_sidebar() {
		$id = stripslashes( trim( $_POST['cs_id'] ) );
		$name = stripslashes( trim( $_POST['sidebar_name'] ) );
		$description = stripslashes( trim( $_POST['sidebar_description'] ) );
		$before_widget = stripslashes( trim( $_POST['cs_before_widget'] ) );
		$after_widget = stripslashes( trim( $_POST['cs_after_widget'] ) );
		$before_title = stripslashes( trim( $_POST['cs_before_title'] ) );
		$after_title = stripslashes( trim( $_POST['cs_after_title'] ) );

		$sidebars = self::get_custom_sidebars();

		//Check the id
		$url = parse_url( $_POST['_wp_http_referer'] );
		if ( ! DOING_AJAX ) {
			if ( isset( $url['query'] ) ) {
				parse_str( $url['query'], $args );
				if ( $args['id'] != $id ) {
					return new WP_Error( __( 'The operation is not secure and it cannot be completed.', CSB_LANG ) );
				}
			} else {
				return new WP_Error( __( 'The operation is not secure and it cannot be completed.', CSB_LANG ) );
			}
		}

		$newsidebars = array();
		foreach ( $sidebars as $sb ) {
			if ( $sb['id'] != $id ) {
				$newsidebars[] = $sb;
			} else {
				$newsidebars[] = array(
					'name' => $name,
					'id' => $id,
					'description' => $description,
					'before_widget' => $before_widget,
					'after_widget' => $after_widget,
					'before_title' => $before_title,
					'after_title' => $after_title,
				);
			}
		}

		//update option
		$this->set_custom_sidebars( $newsidebars );
		$this->refresh_sidebars_widgets();
		$this->set_message( sprintf( __( 'The sidebar "%s" has been updated successfully.', CSB_LANG ), $id ) );
	}

	public function get_empty_widget() {
		$widget = new CustomSidebarsEmptyPlugin();
		return array(
			'name' => 'CS Empty Widget',
			'id' => 'csemptywidget',
			'callback' => array( $widget, 'display_callback' ),
			'params' => array( array( 'number' => 2) ),
			'classname' => 'CustomSidebarsEmptyPlugin',
			'description' => 'CS dummy widget',
		);
	}

};