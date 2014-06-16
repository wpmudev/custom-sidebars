<?php

// Load additional Pro-modules.
require_once( 'class-custom-sidebars-posts.php' );
require_once( 'class-custom-sidebars-cloning.php' );
require_once( 'class-custom-sidebars-visibility.php' );
require_once( 'class-custom-sidebars-export.php' );


/**
 * Main plugin file.
 * The CustomSidebars class encapsulates all our plugin logic.
 */
class CustomSidebars {
	private $message = '';
	private $message_class = '';

	private $sidebar_prefix = 'cs-';
	private $cap_required = 'switch_themes';
	private $options = array();
	private $current_page = '';

	// @since 1.6
	private $screen_id = '';

	/**
	 * Returns the singleton instance of the custom sidebars class.
	 *
	 * @since 1.6
	 */
	public static function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new CustomSidebars();

			// Extensions use this hook to initialize themselfs.
			do_action( 'cs_init' );
		}

		return $Inst;
	}

	/**
	 * Private, since it is a singleton.
	 * We directly initialize sidebar options when class is created.
	 */
	private function __construct() {
		$this->get_sidebar_options();
	}

	/**
	 * Returns a list with sidebars that were marked as "modifiable".
	 * Also contains information on the default replacements of these sidebars.
	 */
	public function get_sidebar_options() {
		$this->options = get_option( 'cs_modifiable', array() );
		return $this->options;
	}

	/**
	 * Saves the sidebar options to DB.
	 *
	 * @since 1.6.0
	 */
	public function set_sidebar_options( $value ) {
		update_option( 'cs_modifiable', $value );
	}

	/**
	 * Returns a list with all custom sidebars that were created by the user.
	 */
	public function get_custom_sidebars() {
		$sidebars = get_option( 'cs_sidebars', array() );
		if ( ! is_array( $sidebars ) ) {
			$sidebars = array();
		}
		return $sidebars;
	}

	/**
	 * Saves the custom sidebars to DB.
	 *
	 * @since 1.6.0
	 */
	public function set_custom_sidebars( $value ) {
		update_option( 'cs_sidebars', $value );
	}

	/**
	 * Returns a list of all registered sidebars including a list of their
	 * widgets (this is stored inside a WordPress core option).
	 *
	 * @since  1.6.0
	 */
	public function get_sidebar_widgets() {
		return get_option( 'sidebars_widgets', array() );
	}

	public function get_theme_sidebars( $include_custom_sidebars = FALSE ) {
		global $wp_registered_sidebars;
		$allsidebars = $wp_registered_sidebars;
		ksort( $allsidebars );
		if ( $include_custom_sidebars ) {
			return $allsidebars;
		}

		$themesidebars = array();
		foreach ( $allsidebars as $key => $sb ) {
			if ( substr( $key, 0, 3 ) != $this->sidebar_prefix ) {
				$themesidebars[$key] = $sb;
			}
		}

		return $themesidebars;
	}

	/**
	 * Returns the custom sidebar metadata of a single post.
	 *
	 * @since  1.6
	 */
	public function get_post_meta( $post_id ) {
		$data = get_post_meta( $post_id, '_cs_replacements', TRUE );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		return $data;
	}

	/**
	 * Saves custom sidebar metadata to a single post.
	 *
	 * @since 1.6
	 * @param int $post_id
	 * @param array $data When array is empty the meta data will be deleted.
	 */
	public function set_post_meta( $post_id, $data ) {
		if ( ! empty( $data ) ) {
			update_post_meta( $post_id, '_cs_replacements', $data );
		} else {
			delete_post_meta( $post_id, '_cs_replacements' );
		}
	}

	/**
	 * Tell WordPress about the custom sidebars.
	 */
	public function register_custom_sidebars() {
		$sb = $this->get_custom_sidebars();
		if ( ! empty( $sb ) ) {
			foreach ( $sb as $sidebar ) {
				/**
				 * i18n support for custom sidebars.
				 */
				$sidebar['name'] = __( $sidebar['name'], CSB_LANG );
				$sidebar['description'] = __( $sidebar['description'], CSB_LANG );
				$sidebar['before_widget'] = __( $sidebar['before_widget'], CSB_LANG );
				$sidebar['after_widget'] = __( $sidebar['after_widget'], CSB_LANG );
				$sidebar['before_title'] = __( $sidebar['before_title'], CSB_LANG );
				$sidebar['after_title'] = __( $sidebar['after_title'], CSB_LANG );

				/**
				 * Filter sidebar options for custom sidebars.
				 *
				 * @since  1.6
				 *
				 * @param  array $sidebar Options used by WordPress to display
				 *           the sidebar.
				 */
				$sidebar = apply_filters( 'cs_sidebar_params', $sidebar );

				register_sidebar( $sidebar );
			}
		}
	}

	/**
	 * Replace the sidebars on current page with some custom sidebars.
	 * Sidebars are replaced by directly modifying the WordPress globals
	 * `$_wp_sidebars_widgets` and `$wp_registered_sidebars`
	 *
	 * What it really does it not replacing a specific *sidebar* but simply
	 * replacing all widgets inside the theme sidebars with the widgets of the
	 * custom defined sidebars.
	 */
	public function replace_sidebars() {
		global $post, $_wp_sidebars_widgets, $wp_registered_sidebars,
		$wp_registered_widgets;

		/**
		 * Original sidebar configuration by WordPress:
		 * Lists sidebars and all widgets inside each sidebar.
		 */
		$original_widgets = $_wp_sidebars_widgets;

		$updated = FALSE;

		$replaceables = $replaceable_sidebars;
		$defaults = $this->get_default_replacements();

		/**
		 * Fires before determining sidebar replacements.
		 *
		 * @param  array $defaults Array of the default sidebars for the page.
		 */
		do_action( 'cs_predetermine_replacements', $defaults );
		// Legacy handler with camelCase
		do_action( 'cs_predetermineReplacements', $defaults );

		$replacements = $this->determine_replacements( $defaults );

		foreach ( $replacements as $sb_name => $replacement_info ) {
			if ( $replacement_info ) {
				list( $replacement, $replacement_type, $extra_index ) = $replacement_info;

				if ( $this->check_and_fix_sidebar( $sb_name, $replacement, $replacement_type, $extra_index ) ) {
					if ( sizeof( $original_widgets[$replacement] ) == 0 ) {
						// No widgets on custom bar, show nothing.
						$wp_registered_widgets['csemptywidget'] = $this->get_empty_widget();
						$_wp_sidebars_widgets[$sb_name] = array( 'csemptywidget' );
					} else {
						$_wp_sidebars_widgets[$sb_name] = $original_widgets[$replacement];
						//replace before/after widget/title?
						$sidebar_for_replacing = $wp_registered_sidebars[$replacement];
						if ( $this->replace_before_after_widget( $sidebar_for_replacing ) ) {
							$sidebar_for_replacing = $this->clean_before_after_widget( $sidebar_for_replacing );
							$wp_registered_sidebars[$sb_name] = $sidebar_for_replacing;
						}
					}
					$wp_registered_sidebars[$sb_name]['class'] = $replacement;
				}  // endif: check_and_fix_sidebar

			} // endif: replacement_info
		} // endforeach
	}

	/* v1.2 clean the slashes of before and after */
	public function clean_before_after_widget( $sidebar ) {
		$sidebar['before_widget'] = stripslashes( $sidebar['before_widget'] );
		$sidebar['after_widget'] = stripslashes( $sidebar['after_widget'] );
		$sidebar['before_title'] = stripslashes( $sidebar['before_title'] );
		$sidebar['after_title'] = stripslashes( $sidebar['after_title'] );
		return $sidebar;
	}

	/**
	 * Find out if some sidebars should be replaced.
	 *
	 * @return  array List of the replaced sidebars.
	 */
	public function determine_replacements( $defaults ) {
		$replaceable_sidebars = $this->get_modifiable_sidebars();
		$replacements_todo = sizeof( $replaceable_sidebars );
		$replacements = array();

		foreach ( $replaceable_sidebars as $sb ) {
			$replacements[ $sb ] = FALSE;
		}


		//posts
		if ( is_single() ) {
			//Post sidebar
			global $post;
			$post_type = get_post_type( $post );

			if ( ! $this->supported_post_type( $post_type ) ) {
				return $defaults;
			}

			$replacements = $this->get_post_meta( $this->originalPostId );
			foreach ( $replaceable_sidebars as $sidebar ) {
				if ( is_array( $replacements ) && ! empty( $replacements[$sidebar] ) ) {
					$replacements[$sidebar] = array( $replacements[$sidebar], 'particular', -1 );
					$replacements_todo -= 1;
				}
			}

			//Parent sidebar
			if ( $post->post_parent != 0 && $replacements_todo > 0 ) {
				$replacements = $this->get_post_meta( $post->post_parent );
				foreach ( $replaceable_sidebars as $sidebar ) {
					if ( ! $replacements[$sidebar]
						&& is_array( $replacements )
						&& ! empty( $replacements[$sidebar] )
					) {
						$replacements[$sidebar] = array( $replacements[$sidebar], 'particular', -1 );
						$replacements_todo -= 1;
					}
				}
			}

			//Category sidebar
			global $sidebar_category;
			if ( $replacements_todo > 0 ) {
				$categories = $this->get_sorted_categories();
				$i = 0;
				while ( $replacements_todo > 0 && $i < sizeof( $categories ) ) {
					foreach ( $replaceable_sidebars as $sidebar ) {
						if ( ! $replacements[$sidebar]
							&& ! empty( $defaults['category_posts'][$categories[$i]->cat_ID][$sidebar] )
						) {
							$replacements[$sidebar] = array(
								$defaults['category_posts'][$categories[$i]->cat_ID][$sidebar],
								'category_posts',
								$sidebar_category,
							);
							$replacements_todo -= 1;
						}
					}
					$i += 1;
				}
			}

			//Post-type sidebar
			if ( $replacements_todo > 0 ) {
				foreach ( $replaceable_sidebars as $sidebar ) {
					if ( ! $replacements[$sidebar]
						&& isset( $defaults['post_type_posts'][$post_type] )
						&& isset( $defaults['post_type_posts'][$post_type][$sidebar] )
					) {
						$replacements[$sidebar] = array(
							$defaults['post_type_posts'][$post_type][$sidebar],
							'defaults',
							$post_type,
						);
						$replacements_todo -= 1;
					}
				}
			}
		} else
		//Category archive
		if ( is_category() ) {
			global $sidebar_category;

			$category_object = get_queried_object();
			$current_category = $category_object->term_id;
			while ( $current_category != 0 && $replacements_todo > 0 ) {
				foreach ( $replaceable_sidebars as $sidebar ) {
					if ( ! $replacements[$sidebar]
						&& ! empty( $defaults['category_pages'][$current_category][$sidebar] )
					) {
						$replacements[$sidebar] = array(
							$defaults['category_pages'][$current_category][$sidebar],
							'category_pages',
							$current_category,
						);
						$replacements_todo -= 1;
					}
				}
				$current_category = $category_object->category_parent;
				if ( $current_category != 0 ) {
					$category_object = get_category( $current_category );
				}
			}
		} else
		//Search comes before because searches with no results are recognized as post types archives
		if ( is_search() ) {
			foreach ( $replaceable_sidebars as $sidebar ) {
				if ( ! empty( $defaults['search'][$sidebar] ) ) {
					$replacements[$sidebar] = array( $defaults['search'][$sidebar], 'search', -1 );
				}
			}
		} else
		//post type archive
		if ( ! is_category() && ! is_singular() && get_post_type() != 'post' ) {
			$post_type = get_post_type();
			if ( ! $this->supported_post_type( $post_type ) ) {
				return $defaults;
			}

			foreach ( $replaceable_sidebars as $sidebar ) {
				if ( isset( $defaults['post_type_pages'][$post_type] )
					&& isset( $defaults['post_type_pages'][$post_type][$sidebar] )
				) {
					$replacements[$sidebar] = array(
						$defaults['post_type_pages'][$post_type][$sidebar],
						'post_type_pages',
						$post_type,
					);
					$replacements_todo -= 1;
				}
			}
		} else
		//Page sidebar
		if ( is_page() ) {
			global $post;

			$post_type = get_post_type( $post );
			if ( ! $this->supported_post_type( $post_type ) ) {
				return $defaults;
			}

			$replacements = $this->get_post_meta( $this->originalPostId );
			foreach ( $replaceable_sidebars as $sidebar ) {
				if ( is_array( $replacements )
					&& ! empty( $replacements[$sidebar] )
				) {
					$replacements[$sidebar] = array( $replacements[$sidebar], 'particular', -1 );
					$replacements_todo -= 1;
				}
			}

			//Parent sidebar
			if ( $post->post_parent != 0 && $replacements_todo > 0 ) {
				$replacements = $this->get_post_meta( $post->post_parent );
				foreach ( $replaceable_sidebars as $sidebar ) {
					if ( ! $replacements[$sidebar]
						&& is_array( $replacements )
						&& ! empty( $replacements[$sidebar] )
					) {
						$replacements[$sidebar] = array( $replacements[$sidebar], 'particular', -1 );
						$replacements_todo -= 1;
					}
				}
			}

			//Page Post-type sidebar
			if ( $replacements_todo > 0 ) {
				foreach ( $replaceable_sidebars as $sidebar ) {
					if ( ! $replacements[$sidebar]
						&& isset( $defaults['post_type_posts'][$post_type] )
						&& isset( $defaults['post_type_posts'][$post_type][$sidebar] )
					) {
						$replacements[$sidebar] = array(
							$defaults['post_type_posts'][$post_type][$sidebar],
							'defaults',
							$post_type,
						);
						$replacements_todo -= 1;
					}
				}
			}
		} else
		if ( is_home() ) {
			foreach ( $replaceable_sidebars as $sidebar ) {
				if ( ! empty( $defaults['blog'][$sidebar] ) ) {
					$replacements[$sidebar] = array( $defaults['blog'][$sidebar], 'blog', -1 );
				}
			}
		} else
		if ( is_tag() ) {
			foreach ( $replaceable_sidebars as $sidebar ) {
				if ( ! empty( $defaults['tags'][$sidebar] ) ) {
					$replacements[$sidebar] = array( $defaults['tags'][$sidebar], 'tags', -1 );
				}
			}
		} else
		if ( is_author() ) {
			foreach ( $replaceable_sidebars as $sidebar ) {
				if ( ! empty( $defaults['authors'][$sidebar] ) ) {
					$replacements[$sidebar] = array( $defaults['authors'][$sidebar], 'authors', -1 );
				}
			}
		} else
		if ( is_date() ) {
			foreach ( $replaceable_sidebars as $sidebar ) {
				if ( ! empty( $defaults['date'][$sidebar] ) ) {
					$replacements[$sidebar] = array( $defaults['date'][$sidebar], 'date', -1);
				}
			}
		}

		/**
		 * Filter the replaced sidebars before they are processed by the plugin.
		 *
		 * @since  1.6
		 *
		 * @param  array $replacements List of the final/replaced sidebars.
		 */
		$replacements = apply_filters( 'cs_replace_sidebars', $replacements );

		return $replacements;
	}

	/**
	 * Stores the original post id before any plugin (buddypress) can modify this data, to show the proper sidebar.
	 * @return null
	 */
	public function store_original_post_id() {
		global $post;
		if ( isset( $post->ID ) ) {
			$this->originalPostId = $post->ID;
		}
	}

	public function check_and_fix_sidebar( $sidebar, $replacement, $method, $extra_index ) {
		global $wp_registered_sidebars;

		if ( isset( $wp_registered_sidebars[$replacement] ) ) {
			return true;
		}

		if ( $method == 'particular' ) {
			global $post;
			$sidebars = $this->get_post_meta( $post->ID );
			if ( $sidebars && isset( $sidebars[$sidebar] ) ) {
				unset( $sidebars[$sidebar] );
				$this->set_post_meta( $post->ID, $sidebars );
			}
		} else {
			if ( isset( $this->options[$method] ) ) {
				if ( $extra_index != -1 && isset( $this->options[$method][$extra_index] ) && isset( $this->options[$method][$extra_index][$sidebar] ) ) {
					unset( $this->options[$method][$extra_index][$sidebar] );
					$this->set_sidebar_options( $this->options );
				}

				if ( $extra_index == 1 && isset( $this->options[$method] ) && isset( $this->options[$method][$sidebar] ) ) {
					unset( $this->options[$method][$sidebar] );
					$this->set_sidebar_options( $this->options );
				}
			}
		}

		return false;
	}

	public function replace_before_after_widget( $sidebar ) {
		return (
			trim( $sidebar['before_widget'] ) != ''
			OR trim( $sidebar['after_widget'] ) != ''
			OR trim( $sidebar['before_title'] ) != ''
			OR trim( $sidebar['after_title'] ) != ''
		);
	}

	/**
	 * Removes a single custom sidebar from the options.
	 */
	public function delete_sidebar() {
		if ( ! current_user_can( $this->cap_required ) ) {
			return new WP_Error( 'cscantdelete', __( 'You do not have permission to delete sidebars', CSB_LANG ) );
		}

		if ( ! DOING_AJAX && ! wp_verify_nonce( $_REQUEST['_n'], 'custom-sidebars-delete' ) ) {
			die( 'Security check stop your request.' );
		}

		$newsidebars = array();
		$deleted = FALSE;

		$custom = $this->get_custom_sidebars();

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

	public function current_page() {
		if ( @$_GET['page'] != 'customsidebars' ) {
			return '';
		}

		// These are the default option pages by the custom sidebars plugin
		$option_pages = array(
			'defaults',
			'edit',
			'conf',
		);

		/**
		 * Filters the pages that are recognized by the custom sidebar options
		 * page. This filter is used in combination with the hook
		 * 'cs_additional_tabs' to recognize the new tabs.
		 *
		 * @var array $option_pages A list of all pages (URL param 'p').
		 */
		$option_pages = apply_filters( 'cs_register_tabs', $option_pages );

		if ( in_array( @$_GET['p'], $option_pages ) ) {
			return $_GET['p'];
		} else {
			return 'conf';
		}
	}

	/**
	 * Renders the admin page for custom sidebars.
	 * Also eventual actions such as delete or update are executed if required.
	 */
	public function create_page() {
		//$this->refresh_sidebars_widgets();
		if ( ! empty( $_POST) ) {
			if ( isset( $_POST['create-sidebars'] ) ) {
				check_admin_referer( 'custom-sidebars-new' );
				$this->store_sidebar();
			} else if ( isset( $_POST['update-sidebar'] ) ) {
				check_admin_referer( 'custom-sidebars-update' );
				$this->update_sidebar();
			} else if ( isset( $_POST['update-modifiable'] ) ) {
				$this->update_modifiable();
				$this->get_sidebar_options();
			} else if ( isset( $_POST['update-defaults-posts'] ) OR isset( $_POST['update-defaults-pages'] ) ) {
				$this->store_defaults();
			} else if ( isset( $_POST['reset-sidebars'] ) ) {
				$this->reset_sidebars();
			}

			$this->get_sidebar_options();
		} else if ( ! empty( $_GET['delete'] ) ) {
			$this->delete_sidebar();
			$this->get_sidebar_options();
		} else if (
			'edit' == $this->current_page()
			&& ! empty( $_GET['id'] )
		) {
			$customsidebars = $this->get_custom_sidebars();
			$sb = $this->get_sidebar( $_GET['id'], $customsidebars );
			if ( ! $sb ) {
				return new WP_Error( 'cscantdelete', __( 'You do not have permission to delete sidebars', CSB_LANG ) );
			}
			include CSB_VIEWS_DIR . 'edit.php';
			return;
		}

		// FIXME: These are global variables. Move this to a (static) function instead
		$customsidebars = $this->get_custom_sidebars();
		$themesidebars = $this->get_theme_sidebars();
		$allsidebars = $this->get_theme_sidebars( TRUE );
		$defaults = $this->get_default_replacements();
		$modifiable = $this->get_modifiable_sidebars();

		$deletenonce = wp_create_nonce( 'custom-sidebars-delete' );

		$tab = $this->current_page();
		switch ( $tab ) {
			case 'defaults':
				$categories = get_categories( array( 'hide_empty' => 0 ) );
				if ( sizeof( $categories ) == 1 && $categories[0]->cat_ID == 1 ) {
					unset( $categories[0] );
				}
				include CSB_VIEWS_DIR . 'defaults.php';
				break;

			case 'edit':
				include CSB_VIEWS_DIR . 'edit.php';
				break;

			case 'conf':
				include CSB_VIEWS_DIR . 'settings.php';
				break;

			default:
				/**
				 * Allow other extensions to render their own settings page.
				 *
				 * @see    filter 'cs_additional_tabs' and hook 'cs_additional_tabs'.
				 * @since  1.6
				 * @param  string $tab slug of the current settings tab.
				 */
				do_action( 'cs_render_tab_content', $tab );
				break;
		}
	}

	/**
	 * Adds the "Custom Sidebars Pro" menu item to the "Appearance" menu.
	 */
	public function add_sub_menus() {
		$page = add_submenu_page(
			'themes.php',
			__( 'Custom Sidebars Pro', CSB_LANG ),
			__( 'Custom Sidebars Pro', CSB_LANG ),
			$this->cap_required,
			'customsidebars',
			array( $this, 'create_page' )
		);

		$this->screen_id = $page;

		add_action( 'admin_print_scripts-' . $page, array( $this, 'add_scripts' ) );
	}

	/**
	 * Returns the screen_id of the custom sidebars section.
	 *
	 * @since  1.6.0
	 */
	public function get_screen_id() {
		return $this->screen_id;
	}

	public function add_scripts() {
		if ( 'posts' === $this->current_page() ) {
			/**
			 * PRO feature: Directly assign sidebars to multiple pages/posts.
			 *
			 * @since  1.6
			 */
			wp_enqueue_script(
				'csb-posts',
				CSB_JS_URL . 'posts.js',
				array( 'jquery' ),
				'1.6',
				true
			);
		}

		wp_enqueue_script( 'post' );
	}

	public function add_styles( $hook ) {
		if ( 'widgets.php' == $hook || 'appearance_page_customsidebars' == $hook ) {
			wp_enqueue_script( 'cs_script', CSB_JS_URL . 'cs.js' );
			wp_enqueue_script( 'thickbox', null, array( 'jquery' ) );
			wp_enqueue_style( 'thickbox.css', includes_url() . 'js/thickbox/thickbox.css', null, '1.6' );
		}
		wp_enqueue_style( 'cs_style', CSB_CSS_URL . 'cs_style.css' );
	}

	/**
	 * Registers the "Sidebars" meta box in the post-editor.
	 */
	public function add_meta_box() {
		global $post;

		$post_type = get_post_type( $post );
		if ( ! $post_type ) { return false; }
		if ( ! $this->supported_post_type( $post_type ) ) { return false; }

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
	 * Returns true, when the specified post type supports custom sidebars.
	 *
	 * @since  1.6.0
	 */
	public function supported_post_type( $posttype ) {
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
	 * Renders the Custom Sidebars meta box in the post-editor.
	 */
	public function print_metabox() {
		global $post, $wp_registered_sidebars;

		$replacements = $this->get_replacements( $post->ID );

		//$available = array_merge(array( '' ), $this->get_theme_sidebars( TRUE) );
		$available = $wp_registered_sidebars;
		ksort( $available );
		$sidebars = $this->get_modifiable_sidebars();
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

	public function load_text_domain() {
		load_plugin_textdomain( CSB_LANG, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	public function get_replacements( $postid ) {
		$replacements = $this->get_post_meta( $postid );
		if ( $replacements == '' ) {
			$replacements = array();
		} else {
			$replacements = $replacements;
		}
		return $replacements;
	}

	public function get_modifiable_sidebars() {
		if ( $modifiable = $this->options ) {
			return $modifiable['modifiable'];
		}
		return array();
	}

	public function get_default_replacements() {
		if ( $defaults = $this->options ) {
			$defaults['post_type_posts'] = isset( $defaults['defaults'] ) ? $defaults['defaults'] : array();
			unset( $defaults['modifiable'] );
			unset( $defaults['defaults'] );
			return $defaults;
		}
		return array();
	}

	public function update_modifiable() {
		check_admin_referer( 'custom-sidebars-options', 'options_wpnonce' );
		$options = $this->options ? $this->options : array();

		//Modifiable bars
		if ( isset( $_POST['modifiable'] ) && is_array( $_POST['modifiable'] ) ) {
			$options['modifiable'] = $_POST['modifiable'];
		}

		$this->set_sidebar_options( $options );

		$this->set_message( __( 'The custom sidebars settings has been updated successfully.', CSB_LANG ) );
	}

	public function store_defaults() {
		$options = $this->options;
		$modifiable = $this->get_modifiable_sidebars();

		//Post-types posts and lists. Posts data are called default in order to keep backwards compatibility;

		$options['defaults'] = array();
		$options['post_type_pages'] = array();

		// "By post type"
		foreach ( $this->get_post_types() as $pt ) {
			if ( ! empty( $modifiable ) ) {
				foreach ( $modifiable as $m ) {
					// single-posttype
					if ( isset( $_POST["type_posts_{$pt}_$m"] ) && $_POST["type_posts_{$pt}_$m"] != '' ) {
						if ( ! isset( $options['defaults'][$pt] ) ) {
							$options['defaults'][$pt] = array();
						}

						$options['defaults'][$pt][$m] = $_POST["type_posts_{$pt}_$m"];
					}

					// archive-posttype
					if ( isset( $_POST["type_page_{$pt}_$m"] ) && $_POST["type_page_{$pt}_$m"] != '' ) {
						if ( ! isset( $options['post_type_pages'][$pt] ) ) {
							$options['post_type_pages'][$pt] = array();
						}

						$options['post_type_pages'][$pt][$m] = $_POST["type_page_{$pt}_$m"];
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
		if ( $this->options === FALSE ) {
			$options['modifiable'] = array();
		}
		$this->set_sidebar_options( $options );

		$this->set_message( __( 'The default sidebars have been updated successfully.', CSB_LANG ) );

	}

	public function store_replacements( $post_id ) {
		if ( ! current_user_can( $this->cap_required ) ) {
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

		$sidebars = $this->get_modifiable_sidebars();
		$data = array();
		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $s ) {
				if ( isset( $_POST["cs_replacement_$s"] ) ) {
					$it = $_POST["cs_replacement_$s"];
					if ( ! empty( $it ) && $it != '' ) {
						$data[$s] = $it;
					}
				}
			}
		}//endif sidebars

		$this->set_post_meta( $post_id, $data );
		$old_data = $this->get_post_meta( $post_id );
	}

	public function store_sidebar() {
		$name = stripslashes( trim( $_POST['sidebar_name'] ) );
		$description = stripslashes( trim( $_POST['sidebar_description'] ) );
		if ( empty( $name ) OR empty( $description ) ) {
			$this->set_error( __( 'You have to fill all the fields to create a new sidebar.', CSB_LANG ) );
		} else {
			$id = $this->sidebar_prefix . sanitize_html_class( sanitize_title_with_dashes( $name ) );
			$sidebars = $this->get_custom_sidebars();

			if ( ! $this->get_sidebar( $id, $sidebars ) ) {
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

		$sidebars = $this->get_custom_sidebars();

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

	public function widget_sidebar_content() {
		include CSB_VIEWS_DIR . 'widgets.php';
	}

	public function get_sidebar( $id, $sidebars ) {
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

	public function message( $echo = TRUE ) {
		$message = '';
		if ( ! empty( $this->message ) ) {
			$message = '<div id="message" class="' . $this->message_class . '"><p><strong>' . $this->message . '</strong></p></div>';
		}

		if ( $echo ) {
			echo $message;
		}
		else {
			return $message;
		}
	}

	public function set_message( $text ) {
		$this->message = $text;
		$this->message_class = 'updated';
	}

	public function set_error( $text ) {
		$this->message = $text;
		$this->message_class = 'error';
	}

	/**
	 * Returns a list of all post types that support custom sidebars.
	 *
	 * @return array List of posttype names.
	 */
	public function get_post_types() {
		$Valid = null;

		if ( null === $Valid ) {
			$all = get_post_types( array(), 'names' );
			$Valid = array();

			foreach ( $all as $post_type ) {
				if ( $this->supported_post_type( $post_type ) ) {
					$Valid[] = $post_type;
				}
			}
		}

		return $Valid;
	}

	/**
	 * Returns a full list with all published posts that support custom sidebars.
	 *
	 * @since  1.6.0
	 * @return array List of published posts, categorized by post-type.
	 */
	public function get_all_posts() {
		$Post_list = null;

		if ( null === $Post_list ) {
			$Post_list = array();
			$post_types = $this->get_post_types();

			foreach ( $post_types as $ind => $post_type ) {
				$Post_list[ $post_type ] = array();
				$posts = get_posts(
					array(
						'post_type' => $post_type,
						'orderby' => 'title',
						'order' => 'ASC',
						'posts_per_page' => -1,
					)
				);
				foreach ( $posts as $post ) {
					$Post_list[ $post_type ][ $post->ID ] = $post->post_title;
				}
			}
		}

		return $Post_list;
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

	public function refresh_sidebars_widgets() {
		$widgetized_sidebars = $this->get_sidebar_widgets();
		$delete_widgetized_sidebars = array();
		$cs_sidebars = $this->get_custom_sidebars();

		foreach ( $widgetized_sidebars as $id => $bar ) {
			if ( substr( $id, 0, 3 ) == $this->sidebar_prefix ) {
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


		foreach ( $cs_sidebars as $cs ) {
			if ( array_search( $cs['id'], array_keys( $widgetized_sidebars ) ) === FALSE ) {
				$widgetized_sidebars[$cs['id']] = array();
			}
		}

		foreach ( $delete_widgetized_sidebars as $id ) {
			unset( $widgetized_sidebars[$id] );
		}

		update_option( 'sidebars_widgets', $widgetized_sidebars );
	}

	public function reset_sidebars() {
		if ( ! current_user_can( $this->cap_required ) ) {
			return new WP_Error( 'cscantdelete', __( 'You do not have permission to delete sidebars', CSB_LANG ) );
		}

		if ( ! wp_verify_nonce( $_REQUEST['reset-n'], 'custom-sidebars-delete' ) ) {
			die( 'Security check stopped your request.' );
		}

		$this->set_sidebar_options( array() );
		$this->set_custom_sidebars( array() );

		$widgetized_sidebars = $this->get_sidebar_widgets();
		$delete_widgetized_sidebars = array();
		foreach ( $widgetized_sidebars as $id => $bar ) {
			if ( substr( $id, 0, 3 ) == $this->sidebar_prefix ) {
				$found = FALSE;
				if ( empty( $cs_sidebars ) ) {
					$found = TRUE;
				} else {
					foreach ( $cs_sidebars as $csbar ) {
						if ( $csbar['id'] == $id ) {
							$found = TRUE;
						}
					}
				}
				if ( ! $found ) {
					$delete_widgetized_sidebars[] = $id;
				}
			}
		}

		foreach ( $delete_widgetized_sidebars as $id ) {
			unset( $widgetized_sidebars[$id] );
		}

		update_option( 'sidebars_widgets', $widgetized_sidebars );

		$this->set_message( __( 'The Custom Sidebars data has been removed successfully,', CSB_LANG ) );
	}

	public function get_sorted_categories() {
		$unorderedcats = get_the_category();
		@usort( $unorderedcats, array( $this, 'cmp_cat_level' ) );
		return $unorderedcats;
	}

	public function cmp_cat_level( $cat1, $cat2 ) {
		$l1 = $this->get_category_level( $cat1->cat_ID );
		$l2 = $this->get_category_level( $cat2->cat_ID );
		if ( $l1 == $l2 ) {
			return strcasecmp( $cat1->name, $cat1->name );
		} else {
			return $l1 < $l2 ? 1 : -1;
		}
	}

	public function get_category_level( $catid ) {
		if ( $catid == 0 ) {
			return 0;
		}

		$cat = &get_category( $catid );
		return 1 + $this->get_category_level( $cat->category_parent );
	}

	public function json_response( $obj ) {
			header( 'Content-Type: application/json' );
			echo json_encode( $obj );
			die();
		}

	public function ajax_handler() {
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
			$this->json_response( $response );
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
		$this->json_response( $response );
	}


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
			'id' => $this->sidebar_prefix . sanitize_html_class( sanitize_title_with_dashes( $_POST['sidebar_name'] ) ),
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
		$sidebar = $this->get_sidebar( $id, $this->get_custom_sidebars() );
		$_POST['cs_before_widget'] = $sidebar['cs_before_widget'];
		$_POST['cs_after_widget'] = $sidebar['cs_after_widget'];
		$_POST['cs_before_title'] = $sidebar['cs_before_title'];
		$_POST['cs_after_title'] = $sidebar['cs_after_title'];
		$this->update_sidebar();

		$sidebar = $this->get_sidebar( $id, $this->get_custom_sidebars() );
		return array(
			'message' => $this->message,
			'success' => $this->message_class != 'error',
			'name' => $sidebar['name'],
			'description' => $sidebar['description'],
		);
	}

	public function ajax_show_where() {
		// FIXME: These are global variables. Move this to a (static) function instead
		$customsidebars = $this->get_custom_sidebars();
		$themesidebars = $this->get_theme_sidebars();
		$allsidebars = $this->get_theme_sidebars( TRUE );
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
		$defaults = $this->get_default_replacements();
		$modifiable = $this->get_modifiable_sidebars();
		$categories = get_categories( array( 'hide_empty' => 0 ) );
		if ( sizeof( $categories ) == 1 && $categories[0]->cat_ID == 1 ) {
			unset( $categories[0] );
		}

		include CSB_VIEWS_DIR . 'ajax.php';
	}
};
