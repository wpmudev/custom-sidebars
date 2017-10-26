<?php

add_action( 'cs_init', array( 'CustomSidebarsVisibility', 'instance' ) );

/**
 * Adds visibility options to all widgets: Hide or show widgets only when
 * specific conditions are met.
 *
 * @since  2.0
 */
class CustomSidebarsVisibility extends CustomSidebars {

	public static $filtered_tax_list = false;
	/**
	 * Returns the singleton object.
	 *
	 * @since  2.0
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
	 *
	 * @since  2.0
	 */
	private function __construct() {
		if ( is_admin() ) {
			// in_widget_form: Add our button inside each widget.
			add_action(
				'in_widget_form',
				array( $this, 'admin_widget_button' ),
				10, 3
			);

			// When the widget is saved (via Ajax) we save our options.
			add_filter(
				'widget_update_callback',
				array( $this, 'admin_widget_update' ),
				10, 3
			);

			$url = 'widgets.php';
			if ( isset( $_SERVER['SCRIPT_NAME'] ) ) {
				$url = explode( '/', $_SERVER['SCRIPT_NAME'] );
				$url = array_pop( $url );
			}
			$javascript_file = 'cs-visibility.min.js';
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$javascript_file = 'cs-visibility.js';
			}
			lib3()->ui->add( CSB_JS_URL . $javascript_file, $url );
			lib3()->ui->add( CSB_CSS_URL . 'cs-visibility.css', $url );

			// Custom Sidebars Ajax request.
			add_action(
				'cs_ajax_request_get',
				array( $this, 'handle_ajax' )
			);
		} else {
			// Filters the list of widget-areas and their widgets
			add_filter(
				'sidebars_widgets',
				array( $this, 'sidebars_widgets' )
			);
		}
	}

	/**
	 * Extracts and sanitizes the CSB visibility data from the widget instance.
	 *
	 * @since  2.0
	 * @param  array $instance The widget instance data.
	 * @return array Sanitized CSB visibility data.
	 */
	protected function get_widget_data( $instance ) {
		static $Condition_keys = null;
		$data = array();

		if ( null === $Condition_keys ) {
			$tax_list = get_taxonomies( array( 'public' => true ), 'objects' );
			$type_list = CustomSidebars::get_post_types( 'objects' );
			$Condition_keys = array(
				'guest' => array(),
				'date' => array(),
				'roles' => array(),
				'pagetypes' => array(),
				'posttypes' => array(),
				'membership' => array(),
				'membership2' => array(),
				'prosite' => array(),
			);
			foreach ( $type_list as $type_item ) {
				$Condition_keys[ 'pt-' . $type_item->name ] = array();
			}
			foreach ( $tax_list as $tax_item ) {
				$Condition_keys[ 'tax-' . $tax_item->name ] = array();
			}
		}

		if ( isset( $instance['csb_visibility'] ) ) {
			$data = $instance['csb_visibility'];
		}

		$valid_action = array( 'show', 'hide' );
		if ( ! isset( $data['action'] ) || ! in_array( $data['action'], $valid_action ) ) {
			$data['action'] = reset( $valid_action );
		}

		$conditions = isset( $data['conditions'] )? $data['conditions'] : array();
		if ( ! is_array( $conditions ) ) {
			$conditions = array();
		}
		$data['conditions'] = array();

		$data['always'] = true;
		foreach ( $Condition_keys as $key => $def_value ) {
			$val = $def_value;
			if ( isset( $conditions[ $key ] ) && ! empty( $conditions[ $key ] ) ) {
				$data['always'] = false;
				$val = $conditions[ $key ];
			}
			$data['conditions'][ $key ] = $val;
		}

		return $data;
	}

	/**
	 * Action handler for 'in_widget_form'
	 *
	 * @since  2.0
	 */
	public function admin_widget_button( $widget, $return, $instance ) {
		static $Loaded = false;
		static $tax_list = array();
		static $type_list = array();
		static $role_list = array();
		static $membership_levels = array();
		static $pagetype_list = array();

		if ( false === $Loaded ) {
			$tax_list = get_taxonomies( array( 'public' => true ), 'objects' );
			$type_list = CustomSidebars::get_post_types( 'objects' );
			$role_list = array_reverse( get_editable_roles() );
			$membership_levels = $this->get_membership_levels();
			$membership2_items = $this->get_membership2_items();
			$pagetype_list = array(
				'frontpage' => __( 'Front Page', 'custom-sidebars' ),
				'home' => __( 'Post Index', 'custom-sidebars' ),
				'single' => __( 'Single page', 'custom-sidebars' ),
				//'posts' => __( 'Posts page', 'custom-sidebars' ),  "Posts page" is same as "Post Index"...
				'archive' => __( 'Archives', 'custom-sidebars' ),
				'search' => __( 'Search results', 'custom-sidebars' ),
				'e404' => __( 'Not found (404)', 'custom-sidebars' ),
				'preview' => __( 'Preview', 'custom-sidebars' ),
				'day' => __( 'Archive: Day', 'custom-sidebars' ),
				'month' => __( 'Archive: Month', 'custom-sidebars' ),
				'year' => __( 'Archive: Year', 'custom-sidebars' ),
			);

			// Remove taxonomies without values.
			if ( ! self::$filtered_tax_list ) {
				foreach ( $tax_list as $index => $tax_item ) {
					$tags = get_terms( $tax_item->name, array( 'hide_empty' => false ) );
					if ( empty( $tags ) ) {
						unset( $tax_list[ $index ] );
					}
				}
				self::$filtered_tax_list = $tax_list;
			} else {
				$tax_list = self::$filtered_tax_list;
			}
		}

		$is_visible = ( isset( $_POST['csb_visible'] ) && '1' == $_POST['csb_visible'] ? 1 : 0);
		$data = $this->get_widget_data( $instance );
		$action_show = ('show' == $data['action']);
		$cond = $data['conditions'];

		?>
		<div class="csb-visibility csb-visibility-<?php echo esc_attr( $widget->id ); ?>"
			data-widget="<?php echo esc_attr( $widget->option_name ); ?>"
			data-number="<?php echo esc_attr( absint( @$widget->number ) ); ?>">
		<?php
		/*
		 * This input is only used to determine if the "visibility" button
		 * should be displayed in the widget form.
		 */
		?>
		<input type="hidden" name="csb-visibility-button" value="0" />
		<?php if ( ! isset( $_POST['csb-visibility-button'] ) ) : ?>
			<a href="#" class="button csb-visibility-button"><span class="dashicons dashicons-visibility"></span> <?php _e( 'Visibility', 'custom-sidebars' ); ?></a>
		<?php else : ?>
			<script>jQuery(function() { jQuery('.csb-visibility-<?php echo esc_js( $widget->id ); ?>').closest('.widget').trigger('csb:update'); }); </script>
		<?php endif; ?>

		<div class="csb-visibility-inner" <?php if ( ! $is_visible ) : ?>style="display:none"<?php endif; ?>>
		<input type="hidden" name="csb_visible" class="csb-visible-flag" value="<?php echo esc_attr( $is_visible ); ?>" />

		<div class="csb-option-row csb-action">
			<label for="<?php echo esc_attr( $widget->id ); ?>-action" class="lbl-show-if toggle-action" <?php if ( ! $action_show ) : ?>style="display:none"<?php endif; ?>><?php _e( '<b>Show</b> widget if:', 'custom-sidebars' ); ?></label>
			<label for="<?php echo esc_attr( $widget->id ); ?>-action" class="lbl-hide-if toggle-action" <?php if ( $action_show ) : ?>style="display:none"<?php endif; ?>><?php _e( '<b>Hide</b> widget if:', 'custom-sidebars' ); ?></label>
			<input type="hidden" id="<?php echo esc_attr( $widget->id ); ?>-action" name="csb_visibility[action]" value="<?php echo esc_attr( $data['action'] ); ?>" />
			<i class="dashicons dashicons-plus choose-filters show-on-hover action"></i>
			<ul class="dropdown" style="display:none">
				<li class="csb-group"><?php _e( 'Filters', 'custom-sidebars' ); ?></li>
				<li class="add-filter"
					data-for=".csb-date"
					style="display:none">
					<?php _e( 'Date', 'custom-sidebars' ); ?>
				</li>
				<li class="add-filter"
					data-for=".csb-guest"
					<?php if ( ! empty( $cond['guest'] ) ) : ?>style="display:none"<?php endif; ?>>
					<?php _e( 'Guests', 'custom-sidebars' ); ?>
				</li>
				<li class="add-filter"
					data-for=".csb-roles"
					<?php if ( ! empty( $cond['roles'] ) ) : ?>style="display:none"<?php endif; ?>>
					<?php _e( 'Roles', 'custom-sidebars' ); ?>
				</li>
				<?php if ( false != $membership_levels ) : ?>
					<li class="add-filter"
						data-for=".csb-membership">
						<?php _e( 'Membership', 'custom-sidebars' ); ?>
					</li>
				<?php endif; ?>
				<?php if ( false != $membership2_items ) : ?>
					<li class="add-filter"
						data-for=".csb-membership2">
						<?php _e( 'Membership2', 'custom-sidebars' ); ?>
					</li>
				<?php endif; ?>
				<li class="add-filter"
					data-for=".csb-pagetypes"
					<?php if ( ! empty( $cond['pagetypes'] ) ) : ?>style="display:none"<?php endif; ?>>
					<?php _e( 'Special pages', 'custom-sidebars' ); ?>
				</li>
				<li class="add-filter"
					data-for=".csb-posttypes"
					<?php if ( ! empty( $cond['posttypes'] ) ) : ?>style="display:none"<?php endif; ?>>
					<?php _e( 'For posttype', 'custom-sidebars' ); ?>
				</li>
				<li class="csb-group"><?php _e( 'Taxonomy', 'custom-sidebars' ); ?></li>
				<?php foreach ( $tax_list as $tax_item ) :
					$row_id = 'tax-' . $tax_item->name;
					?>
					<li class="add-filter"
						data-for=".csb-<?php echo esc_attr( $row_id ); ?>"
						<?php if ( ! empty( $cond[ $row_id ] ) ) : ?>style="display:none"<?php endif; ?>>
						<?php echo esc_html( $tax_item->labels->name ); ?>
					</li>
					<?php
				endforeach; ?>
			</ul>
		</div>

		<?php $block_name = 'csb_visibility[conditions]'; ?>

		<div class="csb-option-row csb-always" <?php if ( ! $data['always'] ) : ?>style="display:none"<?php endif; ?>>
			<label><?php _e( 'Always', 'custom-sidebars' ); ?></label>
		</div>

		<?php /* DATE */ /* ?>
		<div class="csb-option-row csb-date" style="display:none">
			<label for="<?php echo esc_attr( $widget->id ); ?>-date">
				<span class="csb-and" style="display:none"><?php _e( 'AND', 'custom-sidebars' ); ?></span>
				<?php _e( 'On these dates', 'custom-sidebars' ); ?>
			</label>
			<i class="dashicons dashicons-trash clear-filter show-on-hover action"></i>
			<input type="text"
				id="<?php echo esc_attr( $widget->id ); ?>-date"
				name="<?php echo esc_attr( $block_name ); ?>[date][from]"
				value="<?php echo esc_attr( @$cond['date']['from'] ); ?>" />
			<input type="text"
				id="<?php echo esc_attr( $widget->id ); ?>-date-to"
				name="<?php echo esc_attr( $block_name ); ?>[date][to]"
				value="<?php echo esc_attr( @$cond['date']['to'] ); ?>" />
		</div>
		<?php */ ?>

		<?php /* GUEST */ ?>
		<div class="csb-option-row csb-guest" <?php if ( empty( $cond['guest'] ) ) : ?>style="display:none"<?php endif; ?>>
			<label for="<?php echo esc_attr( $widget->id ); ?>-guest1" style="padding-top:10px;margin-bottom:0">
				<input id="<?php echo esc_attr( $widget->id ); ?>-guest1" type="radio" name="<?php echo esc_attr( $block_name ); ?>[guest][]" value="guest" <?php checked( in_array( 'guest', $cond['guest'] ) ); ?> />
				<span class="csb-and" style="display:none"><?php _e( 'AND', 'custom-sidebars' ); ?></span>
				<?php _e( 'User is not logged-in (Guest)', 'custom-sidebars' ); ?><br />
			</label>
			<label for="<?php echo esc_attr( $widget->id ); ?>-guest2" style="border:0;margin-bottom:0">
				<input id="<?php echo esc_attr( $widget->id ); ?>-guest2" type="radio" name="<?php echo esc_attr( $block_name ); ?>[guest][]" value="member" <?php checked( in_array( 'member', $cond['guest'] ) ); ?>  />
				<span class="csb-and" style="display:none"><?php _e( 'AND', 'custom-sidebars' ); ?></span>
				<?php _e( 'User is logged-in (Member)', 'custom-sidebars' ); ?>
			</label>
			<i class="dashicons dashicons-trash clear-filter show-on-hover action"></i>
		</div>

		<?php /* ROLES */ ?>
		<div class="csb-option-row csb-roles" <?php if ( empty( $cond['roles'] ) ) : ?>style="display:none"<?php endif; ?>>
			<label for="<?php echo esc_attr( $widget->id ); ?>-roles">
				<span class="csb-and" style="display:none"><?php _e( 'AND', 'custom-sidebars' ); ?></span>
				<?php _e( 'User has role', 'custom-sidebars' ); ?>
			</label>
			<i class="dashicons dashicons-trash clear-filter show-on-hover action"></i>
			<select id="<?php echo esc_attr( $widget->id ); ?>-roles" name="<?php echo esc_attr( $block_name ); ?>[roles][]" multiple="multiple">
			<?php foreach ( $role_list as $role => $details ) : ?>
				<?php $name = translate_user_role( $details['name'] ); ?>
				<?php $is_selected = in_array( $role, $cond['roles'] ); ?>
				<option <?php selected( $is_selected, true ); ?> value="<?php echo esc_attr( $role ); ?>">
					<?php echo esc_html( $name ); ?>
				</option>
			<?php endforeach; ?>
			</select>
		</div>

		<?php /* MEMBERSHIP */ ?>
		<?php if ( is_array( $membership_levels ) ) : ?>
		<div class="csb-option-row csb-membership" <?php if ( empty( $cond['membership'] ) ) : ?>style="display:none"<?php endif; ?>>
			<label for="<?php echo esc_attr( $widget->id ); ?>-membership">
				<span class="csb-and" style="display:none"><?php _e( 'AND', 'custom-sidebars' ); ?></span>
				<?php _e( 'User has Membership Level', 'custom-sidebars' ); ?>
			</label>
			<i class="dashicons dashicons-trash clear-filter show-on-hover action"></i>
			<select id="<?php echo esc_attr( $widget->id ); ?>-membership" name="<?php echo esc_attr( $block_name ); ?>[membership][]" multiple="multiple">
			<?php foreach ( $membership_levels as $level ) : ?>
				<?php $is_selected = in_array( $level['id'], $cond['membership'] ); ?>
				<option <?php selected( $is_selected ); ?> value="<?php echo esc_attr( $level['id'] ); ?>">
					<?php echo esc_html( $level['level_title'] ); ?>
					<?php if ( ! $level['level_active'] ) { _e( '(inactive)', 'custom-sidebars' ); } ?>
				</option>
			<?php endforeach; ?>
			</select>
		</div>
		<?php endif; ?>

		<?php /* MEMBERSHIP2 (PROTECTED CONTENT) */ ?>
		<?php if ( is_array( $membership2_items ) ) : ?>
		<div class="csb-option-row csb-membership2" <?php if ( empty( $cond['membership2'] ) ) : ?>style="display:none"<?php endif; ?>>
			<label for="<?php echo esc_attr( $widget->id ); ?>-membership2">
				<span class="csb-and" style="display:none"><?php _e( 'AND', 'custom-sidebars' ); ?></span>
				<?php _e( 'User has Membership', 'custom-sidebars' ); ?>
			</label>
			<i class="dashicons dashicons-trash clear-filter show-on-hover action"></i>
			<select id="<?php echo esc_attr( $widget->id ); ?>-membership2" name="<?php echo esc_attr( $block_name ); ?>[membership2][]" multiple="multiple">
			<?php foreach ( $membership2_items as $item ) : ?>
				<?php $is_selected = in_array( $item->id, $cond['membership2'] ); ?>
				<option <?php selected( $is_selected ); ?> value="<?php echo esc_attr( $item->id ); ?>">
					<?php echo esc_html( $item->name ); ?>
					<?php if ( ! $item->active ) { _e( '(inactive)', 'custom-sidebars' ); } ?>
				</option>
			<?php endforeach; ?>
			</select>
		</div>
		<?php endif; ?>

		<?php /* PAGE TYPES */ ?>
		<div class="csb-option-row csb-pagetypes" <?php if ( empty( $cond['pagetypes'] ) ) : ?>style="display:none"<?php endif; ?>>
			<label for="<?php echo esc_attr( $widget->id ); ?>-pagetypes">
				<span class="csb-and" style="display:none"><?php _e( 'AND', 'custom-sidebars' ); ?></span>
				<?php _e( 'On these special pages', 'custom-sidebars' ); ?>
			</label>
			<i class="dashicons dashicons-trash clear-filter show-on-hover action"></i>
			<select id="<?php echo esc_attr( $widget->id ); ?>-pagetypes" name="<?php echo esc_attr( $block_name ); ?>[pagetypes][]" multiple="multiple">
			<?php foreach ( $pagetype_list as $type => $name ) : ?>
				<?php $is_selected = in_array( $type, $cond['pagetypes'] ); ?>
				<option <?php selected( $is_selected ); ?> value="<?php echo esc_attr( $type ); ?>">
					<?php echo esc_html( $name ); ?>
				</option>
			<?php endforeach; ?>
			</select>
		</div>

		<?php /* POSTTYPES */ ?>
		<div class="csb-option-row csb-posttypes"
			<?php if ( empty( $cond['posttypes'] ) ) : ?>style="display:none"<?php endif; ?>>

			<label for="<?php echo esc_attr( $widget->id ); ?>-posttypes">
				<span class="csb-and" style="display:none"><?php _e( 'AND', 'custom-sidebars' ); ?></span>
				<?php _e( 'On any page of these types', 'custom-sidebars' ); ?>
			</label>
			<i class="dashicons dashicons-trash clear-filter show-on-hover action"></i>
			<select class="posttype"
				id="<?php echo esc_attr( $widget->id ); ?>-posttypes"
				name="<?php echo esc_attr( $block_name ); ?>[posttypes][]"
				multiple="multiple">
			<?php foreach ( $type_list as $type_item ) : ?>
				<?php $is_selected = in_array( $type_item->name, $cond['posttypes'] ); ?>
				<option <?php selected( $is_selected ); ?> value="<?php echo esc_attr( $type_item->name ); ?>">
					<?php echo esc_html( $type_item->labels->name ); ?>
				</option>
			<?php endforeach; ?>
			</select>

			<?php /* SPECIFIC POSTS */ ?>
			<?php foreach ( $type_list as $type_item ) :
				$row_id = 'pt-' . $type_item->name;
				$lbl_all = sprintf( __( 'Only for specific %s', 'custom-sidebars' ), $type_item->labels->name );
				$lbl_single = sprintf( __( 'Only these %s:', 'custom-sidebars' ), $type_item->labels->name );
				$is_selected = in_array( $type_item->name, $cond['posttypes'] );
				$ajax_url = admin_url( 'admin-ajax.php?action=cs-ajax&do=visibility&posttype=' . $type_item->name );
				$posts = array();

				if ( ! empty( $cond[ $row_id ] ) ) {
					$posts = get_posts(
						array(
							'post_type' => $type_item->name,
							'order_by' => 'title',
							'order' => 'ASC',
							'numberposts' => '0',
							'include' => implode( ',', $cond[ $row_id ] ),
						)
					);
				}

				?>
				<div class="csb-detail-row csb-<?php echo esc_attr( $row_id ); ?>"
					<?php if ( ! $is_selected ) : ?>style="display:none"<?php endif; ?>>

					<label for="<?php echo esc_attr( $widget->id ); ?>-<?php echo esc_attr( $row_id ); ?>">
						<input type="checkbox"
							id="<?php echo esc_attr( $widget->id ); ?>-<?php echo esc_attr( $row_id ); ?>"
							<?php checked( ! empty( $cond[ $row_id ] ) ); ?>
							data-lbl-all="<?php echo esc_attr( $lbl_all ); ?>"
							data-lbl-single="<?php echo esc_attr( $lbl_single ); ?>" />
						<span class="lbl">
							<?php echo esc_html( empty( $cond[ $row_id ] ) ? $lbl_all : $lbl_single ); ?>
						</span>
					</label>
					<div class="detail" <?php if ( empty( $cond[ $row_id ] ) ) : ?>style="display:none"<?php endif; ?>>

						<select name="<?php echo esc_attr( $block_name ); ?>[<?php echo esc_attr( $row_id ); ?>][]" data-select-ajax="<?php echo esc_url( $ajax_url ); ?>" multiple="multiple">
							<?php if ( ! empty( $posts ) ) : ?>
								<?php foreach ( $posts as $post ) : ?>
							<option value="<?php echo esc_attr( $post->ID ); ?>" selected="selected"><?php echo esc_html( $post->post_title ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php /* SPECIFIC TAXONOMY */ ?>
		<?php
		foreach ( $tax_list as $tax_item ) {
			$row_id = 'tax-' . $tax_item->name;
			$ajax_url = admin_url( 'admin-ajax.php?action=cs-ajax&do=visibility&tag=' . $tax_item->name );
			$tags = array();
			if ( ! empty( $cond[ $row_id ] ) ) {
				$tags = get_terms(
					$tax_item->name,
					array(
						'include' => implode( ',', $cond[ $row_id ] ),
						'hide_empty' => false,
					)
				);
			}
?>
			<div class="csb-option-row csb-<?php echo esc_attr( $row_id ); ?>"
		<?php if ( empty( $cond[ $row_id ] ) ) : ?>style="display:none"<?php endif; ?>>
				<label for="<?php echo esc_attr( $widget->id ); ?>-<?php echo esc_attr( $row_id ); ?>">
					<span class="csb-and" style="display:none"><?php _e( 'AND', 'custom-sidebars' ); ?></span>
					<?php echo esc_html( $tax_item->labels->name ); ?>
					</label>
				<i class="dashicons dashicons-trash clear-filter show-on-hover action"></i>
				<select name="<?php echo esc_attr( $block_name ); ?>[<?php echo esc_attr( $row_id ); ?>][]" data-select-ajax="<?php echo esc_url( $ajax_url ); ?>" multiple="multiple">
<?php
foreach ( $tags as $one ) {
	printf(
		'<option value="%d" selected="selected">%s</option>',
		esc_attr( $one->term_id ),
		esc_html( $one->name )
	);
}
?>
</select>
			</div>
			<?php
		}
		?>

		</div>
		</div>
		<?php
	}

	/**
	 * Integration with the WPMU Dev Membership plugin:
	 * If the plugin is installed and active this function returns a list of
	 * all membership levels.
	 *
	 * If the plugin is not active the return value is boolean false.
	 *
	 * @since  2.0
	 * @return bool|array
	 */
	public function get_membership_levels() {
		$Result = null;

		if ( null === $Result ) {
			if (
				function_exists( 'M_get_membership_active' ) &&
				'no' != M_get_membership_active() &&
				defined( 'MEMBERSHIP_TABLE_LEVELS' )
			) {
				global $wpdb;
				$Result = $wpdb->get_results(
					sprintf(
						'SELECT
							id, level_title, level_active
						FROM %s
						ORDER BY id',
						MEMBERSHIP_TABLE_LEVELS
					), ARRAY_A
				);
			} else {
				$Result = false;
			}
		}

		return $Result;
	}

	/**
	 * Integration with the WPMU Dev Membership2 plugin:
	 * If the plugin is installed and active this function returns a list of
	 * all membership levels.
	 *
	 * If the plugin is not active the return value is boolean false.
	 *
	 * @since  2.0
	 * @return bool|array
	 */
	public function get_membership2_items() {
		$Result = null;

		if ( null === $Result ) {
			$is_active_membership = apply_filters( 'ms_active', false );
			if ( $is_active_membership ) {
				$Result = MS_Plugin::$api->list_memberships( true );
			}
		}

		return $Result;
	}

	/**
	 * When user saves the widget we check for the
	 *
	 * @since  2.0
	 * @param  array $new_instance New settings for this instance as input by the user.
	 * @param  array $old_instance Old settings for this instance.
	 * @return array Modified settings.
	 */
	public function admin_widget_update( $instance, $new_instance, $old_instance ) {
		$data = $this->get_widget_data( $_POST );
		foreach ( $data['conditions'] as $key => $list ) {
			if ( ! is_array( $list ) ) {
				$list = explode( ',', $list );
				$data['conditions'][ $key ] = $list;
			}
		}
		$instance['csb_visibility'] = $data;
		return $instance;
	}

	// == Front-end functions

	/**
	 * Filter the list of widgets for a sidebar so that active sidebars work as expected.
	 *
	 * @since  2.0
	 * @param  array $widget_areas An array of widget areas and their widgets.
	 * @return array The modified $widget_area array.
	 */
	public function sidebars_widgets( $widget_areas ) {
		static $Settings = array();
		static $Result = array();

		$custom_sidebars_explain = CustomSidebarsExplain::instance();
		$expl = $custom_sidebars_explain->do_explain();

		if ( ! did_action( 'cs_before_replace_sidebars' ) ) {
			return $widget_areas;
		}

		$key = serialize( $widget_areas );

		if ( ! isset( $Result[ $key ] ) ) {
			$expl && do_action( 'cs_explain', '<h4>Filter widgets</h4>', true );
			foreach ( $widget_areas as $widget_area => $widgets ) {
				if ( empty( $widgets ) ) {
					continue;
				}

				if ( 'wp_inactive_widgets' == $widget_area ) {
					continue;
				}

				$expl && do_action( 'cs_explain', '<h5>Sidebar "' . $widget_area . '"</h5>', true );

				foreach ( $widgets as $position => $widget_id ) {
					// Find the conditions for this widget.
					if ( preg_match( '/^(.+?)-(\d+)$/', $widget_id, $matches ) ) {
						$id_base = $matches[1];
						$widget_number = intval( $matches[2] );
					} else {
						$id_base = $widget_id;
						$widget_number = null;
					}

					if ( ! isset( $Settings[ $id_base ] ) ) {
						$Settings[ $id_base ] = get_option( 'widget_' . $id_base );
					}

					$expl && do_action( 'cs_explain', 'Widget "' . $widget_id . '"', true );

					// New multi widget (WP_Widget)
					if ( ! is_null( $widget_number ) ) {
						if ( isset( $Settings[ $id_base ][ $widget_number ] ) && false === $this->maybe_display_widget( $Settings[ $id_base ][ $widget_number ] ) ) {
							unset( $widget_areas[ $widget_area ][ $position ] );
						}
					} elseif ( ! empty( $Settings[ $id_base ] ) && false === $this->maybe_display_widget( $Settings[ $id_base ] ) ) {
						// Old single widget.
						unset( $widget_areas[ $widget_area ][ $position ] );
					}
				}
			}

			$Result[ $key ] = $widget_areas;
		}

		return $Result[ $key ];
	}

	public function maybe_display_widget( $instance ) {
		global $post, $wp_query;
		static $Type_list = null;
		static $Tax_list = null;

		$show_widget = true;
		$condition_true = true;
		$action = 'show';
		$explain = ''; // This is used to explain why a widget is not displayed.

		$custom_sidebars_explain = CustomSidebarsExplain::instance();
		$expl = $custom_sidebars_explain->do_explain();

		if ( empty( $instance['csb_visibility'] ) || empty( $instance['csb_visibility']['conditions'] ) ) {
			return $show_widget;
		}

		$cond = $instance['csb_visibility']['conditions'];
		$action = 'hide' != $instance['csb_visibility']['action'] ? 'show' : 'hide';

		if ( $instance['csb_visibility']['always'] ) {
			$expl && do_action( 'cs_explain', '<span style="color:#090">Always</span> <b>' . $action . '</b>' );
			return ( 'hide' == $action ? false : true );
		}

		if ( null === $Type_list ) {
			$Tax_list = get_taxonomies( array( 'public' => true ), 'objects' );
			$Type_list = get_post_types( array( 'public' => true ), 'objects' );
		}

		// Filter for DATE-RANGE.
		if ( $condition_true && ! empty( $cond['date'] ) ) {
			// not implemented yet...
		}

		// Filter for GUEST STATUS.
		if ( $condition_true && ! empty( $cond['guest'] ) && is_array( $cond['guest'] ) ) {
			$expl && $explain .= '<br />GUEST [';
			if ( is_user_logged_in() ) {
				if ( 'member' != $cond['guest'][0] ) {
					$expl && $explain .= 'user is logged in';
					$condition_true = false;
				}
			} else {
				if ( 'guest' != $cond['guest'][0] ) {
					$expl && $explain .= 'user not logged in';
					$condition_true = false;
				}
			}
			$expl && $explain .= '] ';
		}

		// Filter for USER ROLES.
		if ( $condition_true && ! empty( $cond['roles'] ) && is_array( $cond['roles'] ) ) {
			$expl && $explain .= '<br />ROLE [';
			if ( ! is_user_logged_in() ) {
				$expl && $explain .= 'user not logged in';
				$condition_true = false;
			} else {
				global $current_user;
				$has_role = false;
				foreach ( $current_user->roles as $user_role ) {
					if ( in_array( $user_role, $cond['roles'] ) ) {
						$expl && $explain .= 'ok:' . $user_role;
						$has_role = true;
						break;
					}
				}
				if ( ! $has_role ) {
					$expl && $explain .= 'invalid role';
					$condition_true = false;
				}
			}
			$expl && $explain .= '] ';
		}

		// Filter for MEMBERSHIP Level.
		if ( $condition_true && ! empty( $cond['membership'] ) ) {
			$expl && $explain .= '<br />MEMBERSHIP [';
			if ( class_exists( 'Membership_Factory' ) ) {
				$has_level = false;
				$wpuser = get_userdata( get_current_user_id() );

				$is_admin = $wpuser && (
					$wpuser->has_cap( 'membershipadmin' ) ||
					$wpuser->has_cap( 'manage_options' ) ||
					is_super_admin()
					);

				if ( $is_admin ) {
					$expl && $explain .= 'is admin';
					$has_level = true;
				} else {
					$factory = new Membership_Factory();
					$user = $factory->get_member( get_current_user_id() );
					$levels = $user->get_level_ids();

					if ( ! is_array( $levels ) ) { $levels = array( $levels ); }

					foreach ( $cond['membership'] as $need_level_id ) {
						if ( empty( $need_level_id ) ) { continue; }
						foreach ( $levels as $the_level ) {
							if ( $the_level->level_id == $need_level_id ) {
								$expl && $explain .= 'ok';
								$has_level = true;
								break;
							}
						}
						if ( $has_level ) { break; }
					}
				}
				if ( ! $has_level ) {
					$expl && $explain .= 'invalid user level';
					$condition_true = false;
				}
			}
			$expl && $explain .= '] ';
		}

		// Filter for MEMBERSHIP2 Level.
		if ( $condition_true && ! empty( $cond['membership2'] ) ) {
			$expl && $explain .= '<br />MEMBERSHIP2 [';
			if ( apply_filters( 'ms_active', false ) ) {
				$is_member = false;
				$member = MS_Plugin::$api->get_current_member();

				if ( $member->is_admin_user() ) {
					$expl && $explain .= 'is admin';
					$is_member = true;
				} else {
					foreach ( $cond['membership2'] as $membership_id ) {
						if ( $member->has_membership( $membership_id ) ) {
							$is_member = true;
							break;
						}
					}
				}
				if ( ! $is_member ) {
					$expl && $explain .= 'is no member';
					$condition_true = false;
				}
			}
			$expl && $explain .= '] ';
		}

		// Filter for PRO-SITE Level.
		if ( $condition_true && ! empty( $cond['prosite'] ) ) {
			$expl && $explain .= '<br />PROSITE [';
			// not implemented yet...
			$expl && $explain .= '] ';
		}

		// Filter for SPECIAL PAGES.
		if ( $condition_true && ! empty( $cond['pagetypes'] ) && is_array( $cond['pagetypes'] ) ) {
			$expl && $explain .= '<br />PAGETYPE [';
			$is_type = false;
			foreach ( $cond['pagetypes'] as $type ) {
				if ( $is_type ) {
					break;
				}

				switch ( $type ) {
					case 'e404':
						$is_type = $is_type || is_404();
						break;
					case 'single':
						$is_type = $is_type || is_singular();
						break;
					case 'search':
						$is_type = $is_type || is_search();
						break;
					case 'archive':
						$is_type = $is_type || is_archive();
						break;
					case 'preview':
						$is_type = $is_type || is_preview();
						break;
					case 'day':
						$is_type = $is_type || is_day();
						break;
					case 'month':
						$is_type = $is_type || is_month();
						break;
					case 'year':
						$is_type = $is_type || is_year();
						break;
					case 'frontpage':
						if ( current_theme_supports( 'infinite-scroll' ) ) {
							$is_type = $is_type || is_front_page();
						} else {
							$is_type = $is_type ||  ( is_front_page() && ! is_paged() );
						}
						break;
					case 'posts':
					case 'home':
						$is_type = $is_type || is_home();
						break;
				}
				$expl && $explain .= $type . ':' . ($is_type ? 'ok' : 'invalid');
			}
			if ( ! $is_type ) {
				$condition_true = false;
			}
			$expl && $explain .= '] ';
		}

		// Filter for POST-TYPE.
		if ( $condition_true && ! empty( $cond['posttypes'] ) ) {
			$expl && $explain .= '<br />POSTTYPE-';
			/**
			 * Check for is singular or post type archive
			 */
			if ( is_singular( $cond['posttypes'] ) || is_post_type_archive( $cond['posttypes'] ) ) {
				$posttype = get_post_type();
				$expl && $explain .= strtoupper( $posttype ) . ' [';
				if ( ! in_array( $posttype, $cond['posttypes'] ) ) {
					$expl && $explain .= 'invalid posttype';
					$condition_true = false;
				} else {
					// Filter for SPECIFIC POSTS.
					if ( ! empty( $cond[ 'pt-' . $posttype ] ) ) {
						if ( ! in_array( get_the_ID(), $cond[ 'pt-' . $posttype ] ) ) {
							$expl && $explain .= 'invalid post_id';
							$condition_true = false;
						}
					}
				}
				if ( $condition_true ) {
					$expl && $explain .= 'ok';
				}
			} else {
				$expl && $explain .= ' it is not singular or post type archive';
				$condition_true = false;
			}
			$expl && $explain .= '] ';
		}

		if ( $condition_true ) {
			// TAXONOMY condition.
			$tax_query = null;
			if ( isset( $wp_query->tax_query ) ) {
				$tax_query = $wp_query->tax_query->queries;
			}
			$tax_type = $tax_terms = false;
			if ( ! empty( $tax_query ) && is_array( $tax_query ) ) {
				$tax_type = $tax_query[0]['taxonomy'];
				$tax_terms = $tax_query[0]['terms'];
			}

			foreach ( $Tax_list as $tax_item ) {
				if ( ! $condition_true ) {
					break;
				}

				$tax_key = 'tax-' . $tax_item->name;
				if ( isset( $cond[ $tax_key ] ) && ! empty( $cond[ $tax_key ] ) ) {
					$expl && $explain .= '<br />TAX-' . strtoupper( $tax_item->name ) . ' [';
					$has_term = false;

					if ( $tax_type && $tax_type == $tax_item->name ) {
						// Check if we did filter for the specific taxonomy.
						foreach ( $tax_terms as $slug ) {
							$term_data = get_term_by( 'slug', $slug, $tax_type );
							if ( in_array( $term_data->term_id, $cond[ $tax_key ] ) ) {
								$expl && $explain .= 'ok:' . $term_data->term_id;
								$has_term = true;
							}
						}
					} else {
						// Check if current post has the specific taxonomy.
						foreach ( $cond[ $tax_key ] as $term ) {
							if ( has_term( $term, $tax_item->name ) ) {
								$expl && $explain .= 'ok:' . $term;
								$has_term = true;
								break;
							}
						}
					}
					if ( ! $has_term ) {
						$expl && $explain .= 'no match';
						$condition_true = false;
					}
					$expl && $explain .= '] ';
				}
			}
		}

		if ( ( 'show' == $action && ! $condition_true ) || ( 'hide' == $action && $condition_true ) ) {
			$show_widget = false;
		}

		$expl && do_action(
			'cs_explain',
			($condition_true ? '<span style="color:#090">Do</span>' : '<span style="color:#900">Dont</span>') .
			' <b>' . $action . '</b> - ' .
			$explain
		);

		return $show_widget;
	}

	//
	// ========== AJAX Handler
	//

	/**
	 * Ajax handler. If the action is processed the request is closed via die()
	 *
	 * @since  2.0.9.7
	 * @param  string $action
	 */
	public function handle_ajax( $action ) {
		// The ajax request was not meant for us...
		if ( 'visibility' != $action ) {
			return false;
		}

		$data = array();
		if ( isset( $_GET['tag'] ) ) {
			$data = $this->ajax_data_terms( @$_GET['tag'], @$_REQUEST['q'] );
		} elseif ( isset( $_GET['posttype'] ) ) {
			$data = $this->ajax_data_posts( @$_GET['posttype'], @$_REQUEST['q'] );
		}

		self::json_response( array( 'items' => $data ) );
	}

	/**
	 * Returns an array with tags that contain the specified search term.
	 *
	 * @since  2.0.9.7
	 * @param  string $term_name Taxonomy type.
	 * @param  string $search Search term.
	 * @return array
	 */
	protected function ajax_data_terms( $term_name, $search ) {
		$data = array();
		$tags = get_terms(
			$term_name,
			array(
				'hide_empty' => false,
				'search' => $search,
			)
		);

		foreach ( $tags as $tag ) {
			$key = $tag->term_id;
			$name = $tag->name;
			$data[] = array(
				'id' => $key,
				'text' => esc_html( $name ),
			);
		}

		return $data;
	}

	/**
	 * Returns an array with post-titles that contain the specified search term.
	 *
	 * @since  2.0.9.7
	 * @param  string $posttype Post-type to search.
	 * @param  string $search Search term.
	 * @return array
	 */
	protected function ajax_data_posts( $posttype, $search ) {
		$data = array();
		$posts = get_posts(
			array(
				'post_type' => $posttype,
				'order_by' => 'title',
				'order' => 'ASC',
				'numberposts' => '0',
				's' => $search,
			)
		);

		foreach ( $posts as $post ) {
			$id = $post->ID;
			$text = $post->post_title;
			$data[] = array(
				'id' => $post->ID,
				'text' => esc_html( $text ),
			);
		}

		return $data;
	}
};
