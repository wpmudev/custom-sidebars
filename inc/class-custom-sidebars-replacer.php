<?php

add_action( 'cs_init', array( 'CustomSidebarsReplacer', 'instance' ) );

/**
 * This class actually replaces sidebars on the frontend.
 *
 * @since  1.6
 */
class CustomSidebarsReplacer extends CustomSidebars {

	/**
	 * Returns the singleton object.
	 *
	 * @since  1.6
	 */
	public static function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new CustomSidebarsReplacer();
		}

		return $Inst;
	}

	/**
	 * Constructor is private -> singleton.
	 *
	 * @since  1.6
	 */
	private function __construct() {
		add_action( 'widgets_init', array( $this, 'register_custom_sidebars') );

		if ( ! is_admin() ) {
			// Frontend hooks.
			add_action( 'wp_head', array( $this, 'replace_sidebars' ) );
			add_action( 'wp', array( $this, 'store_original_post_id' ) );
		}
	}

	/**
	 * Tell WordPress about the custom sidebars.
	 */
	public function register_custom_sidebars() {
		$sb = self::get_custom_sidebars();

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
		global $post,
			$_wp_sidebars_widgets,
			$wp_registered_sidebars,
			$wp_registered_widgets;

		/**
		 * Original sidebar configuration by WordPress:
		 * Lists sidebars and all widgets inside each sidebar.
		 */
		$original_widgets = $_wp_sidebars_widgets;

		$updated = FALSE;
		$defaults = self::get_options( 'defaults' );

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

	/**
	 * Find out if some sidebars should be replaced.
	 *
	 * @return  array List of the replaced sidebars.
	 */
	public function determine_replacements( $defaults ) {
		$replaceable_sidebars = self::get_options( 'modifiable' );
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

			if ( ! self::supported_post_type( $post_type ) ) {
				return $defaults;
			}

			$replacements = self::get_post_meta( $this->originalPostId );
			foreach ( $replaceable_sidebars as $sidebar ) {
				if ( is_array( $replacements ) && ! empty( $replacements[$sidebar] ) ) {
					$replacements[$sidebar] = array( $replacements[$sidebar], 'particular', -1 );
					$replacements_todo -= 1;
				}
			}

			//Parent sidebar
			if ( $post->post_parent != 0 && $replacements_todo > 0 ) {
				$replacements = self::get_post_meta( $post->post_parent );
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
				$categories = self::get_sorted_categories();
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
			if ( ! self::supported_post_type( $post_type ) ) {
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
			if ( ! self::supported_post_type( $post_type ) ) {
				return $defaults;
			}

			$replacements = self::get_post_meta( $this->originalPostId );
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
				$replacements = self::get_post_meta( $post->post_parent );
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
		$options = self::get_options();

		if ( isset( $wp_registered_sidebars[$replacement] ) ) {
			return true;
		}

		if ( $method == 'particular' ) {
			global $post;
			$sidebars = self::get_post_meta( $post->ID );
			if ( $sidebars && isset( $sidebars[$sidebar] ) ) {
				unset( $sidebars[$sidebar] );
				self::set_post_meta( $post->ID, $sidebars );
			}
		} else {
			if ( isset( $options[$method] ) ) {
				if ( $extra_index != -1 && isset( $options[$method][$extra_index] ) && isset( $options[$method][$extra_index][$sidebar] ) ) {
					unset( $options[$method][$extra_index][$sidebar] );
					self::set_options( $options );
				}

				if ( $extra_index == 1 && isset( $options[$method] ) && isset( $options[$method][$sidebar] ) ) {
					unset( $options[$method][$sidebar] );
					self::set_options( $options );
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
	 * Clean the slashes of before and after.

	 * @since  1.2
	 */
	public function clean_before_after_widget( $sidebar ) {
		$sidebar['before_widget'] = stripslashes( $sidebar['before_widget'] );
		$sidebar['after_widget'] = stripslashes( $sidebar['after_widget'] );
		$sidebar['before_title'] = stripslashes( $sidebar['before_title'] );
		$sidebar['after_title'] = stripslashes( $sidebar['after_title'] );
		return $sidebar;
	}

};