<?php

/**
 * Adds visibility options to all widgets: Hide or show widgets only when
 * specific conditions are met.
 *
 * @since 1.6
 */
class CustomSidebarsVisibility {

	/**
	 * Returns the singleton object.
	 *
	 * @since  1.6
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
	 * @since  1.6
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

			// Load the javascript support file for this module.
			add_action(
				'admin_enqueue_scripts',
				array( $this, 'admin_scripts' )
			);
		}
	}

	/**
	 * Extracts and sanitizes the CSB visibility data from the widget instance.
	 *
	 * @since  1.6
	 * @param  array $instance The widget instance data.
	 * @return array Sanitized CSB visibility data.
	 */
	protected function get_widget_data( $instance ) {
		$data = array();

		if ( isset( $instance['csb_visibility'] ) ) {
			$data = $instance['csb_visibility'];
		}

		$valid_action = array( 'hide', 'show' );
		if ( ! array_search( @$data['action'], $valid_action ) ) {
			$data['action'] = reset( $valid_action );
		}

		if ( ! is_array( @$data['conditions'] ) ) {
			$data['conditions'] = array(
				array(),
			);
		}

		foreach ( $data['conditions'] as $index => $cond ) {

		}

		return $data;
	}

	/**
	 * Action handler for 'in_widget_form'
	 *
	 * @since  1.6
	 */
	public function admin_widget_button( $widget, $return, $instance ) {
		$is_visible = ('1' == @$_POST['csb_visible'] ? 1 : 0);
		$tax_list = get_taxonomies( array( 'public' => true ), 'objects' );
		$type_list = get_post_types( array( 'public' => true ), 'objects' );
		$role_list = array_reverse( get_editable_roles() );
		$pagetype_list = array(
			'frontpage' => 'Frontpage',
			'single' => 'Single page',
			'archives' => 'Archives',
			'search' => 'Search results',
			'e404' => 'Not found',
		);

		$data = $this->get_widget_data( $instance );
		$action_show = ($data['action'] == 'show');

		?>
		<div class="csb-visibility csb-visibility-<?php echo esc_attr( $widget->id ); ?>">
		<?php
		/*
		 * This input is only used to determine if the "visibility" button
		 * should be displayed in the widget form.
		 */
		?>
		<input type="hidden" name="csb-visibility-button" value="0" />
		<?php if ( ! isset( $_POST[ 'csb-visibility-button' ] ) ) : ?>
		<a href="#" class="button csb-visibility-button"><span class="dashicons dashicons-visibility"></span> <?php _e( 'Visibility', CSB_LANG ); ?></a>
		<?php endif; ?>

		<div class="csb-visibility-inner" <?php if ( ! $is_visible ) : ?>style="display:none"<?php endif; ?>>
		<input type="hidden" name="csb_visible" class="csb-visible-flag" value="<?php echo esc_attr( $is_visible ); ?>" />

		<div class="csb-option-row csb-action">
			<label for="<?php echo esc_attr( $widget->id ); ?>-action" class="lbl-show-if toggle-action" <?php if ( ! $action_show ) : ?>style="display:none"<?php endif; ?>><?php _e( '<b>Show</b> widget if:', CSB_LANG ); ?></label>
			<label for="<?php echo esc_attr( $widget->id ); ?>-action" class="lbl-hide-if toggle-action" <?php if ( $action_show ) : ?>style="display:none"<?php endif; ?>><?php _e( '<b>Hide</b> widget if:', CSB_LANG ); ?></label>
			<input type="hidden" id="<?php echo esc_attr( $widget->id ); ?>-action" name="csb_visibility[action]" value="<?php echo esc_attr( $data['action'] ); ?>" />
		</div>

		<?php foreach ( $data['conditions'] as $index => $cond ) : ?>
			<?php $block_name = 'csb_visibility[conditions][' . $index . ']'; ?>

			<div class="csb-option-row csb-date" <?php if ( empty( $cond['date'] ) ) : ?>style="display:none"<?php endif; ?>>
				<label for="<?php echo esc_attr( $widget->id ); ?>-date"><?php _e( 'Date', CSB_LANG ); ?></label>
				<input type="text" id="<?php echo esc_attr( $widget->id ); ?>-date" name="<?php echo esc_attr( $block_name ); ?>[date]" value="<?php echo esc_attr( @$cond['date'] ); ?>" />
			</div>
			<div class="csb-option-row csb-roles" <?php if ( empty( $cond['roles'] ) ) : ?>style="display:none"<?php endif; ?>>
				<label for="<?php echo esc_attr( $widget->id ); ?>-roles"><?php _e( 'Current user has roles', CSB_LANG ); ?></label>
				<select id="<?php echo esc_attr( $widget->id ); ?>-roles" name="<?php echo esc_attr( $block_name ); ?>[roles][]" multiple="multiple">
				<?php foreach ( $role_list as $role => $details ) : ?>
					<?php $name = translate_user_role( $details['name'] ); ?>
					<?php $is_selected = false !== array_search( $role, $cond['roles'] ); ?>
					<option <?php selected( $is_selected, true ); ?> value="<?php echo esc_attr( $role ); ?>"><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
				</select>
			</div>
			<div class="csb-option-row csb-membership" <?php if ( empty( $cond['membership'] ) ) : ?>style="display:none"<?php endif; ?>>
				<label for="<?php echo esc_attr( $widget->id ); ?>-membership"><?php _e( 'Membership Level', CSB_LANG ); ?></label>
				<input type="text" id="<?php echo esc_attr( $widget->id ); ?>-membership" name="<?php echo esc_attr( $block_name ); ?>[membership]" value="<?php echo esc_attr( @$cond['membership'] ); ?>" />
			</div>
			<div class="csb-option-row csb-prosite" <?php if ( empty( $cond['prosite'] ) ) : ?>style="display:none"<?php endif; ?>>
				<label for="<?php echo esc_attr( $widget->id ); ?>-prosite"><?php _e( 'Pro Sites Level', CSB_LANG ); ?></label>
				<input type="text" id="<?php echo esc_attr( $widget->id ); ?>-prosite" name="<?php echo esc_attr( $block_name ); ?>[prosite]" value="<?php echo esc_attr( @$cond['prosite'] ); ?>" />
			</div>
			<div class="csb-option-row csb-posttypes" <?php if ( empty( $cond['posttypes'] ) ) : ?>style="display:none"<?php endif; ?>>
				<label for="<?php echo esc_attr( $widget->id ); ?>-posttypes"><?php _e( 'Posttypes', CSB_LANG ); ?></label>
				<input type="text" id="<?php echo esc_attr( $widget->id ); ?>-posttypes" name="<?php echo esc_attr( $block_name ); ?>[posttypes]" value="<?php echo esc_attr( @$cond['posttypes'] ); ?>" />
			</div>
			<?php
			foreach ( $type_list as $type_item ) {
				$row_id = 'pt-' . $type_item->name;
				?>
				<div class="csb-option-row csb-<?php echo esc_attr( $row_id ); ?>" <?php if ( empty( $cond[ $row_id ] ) ) : ?>style="display:none"<?php endif; ?>>
					<label for="<?php echo esc_attr( $widget->id ); ?>-<?php echo esc_attr( $row_id ); ?>"><?php esc_html_e( $type_item->labels->name ); ?></label>
					<input type="text" id="<?php echo esc_attr( $widget->id ); ?>-<?php echo esc_attr( $row_id ); ?>" name="<?php echo esc_attr( $block_name ); ?>[<?php echo esc_attr( $row_id ); ?>]" value="<?php echo esc_attr( @$cond[ $row_id ] ); ?>" />
				</div>
				<?php
			}
			?>

			<?php
			foreach ( $tax_list as $tax_item ) {
				$row_id = 'tax-' . $tax_item->name;
				?>
				<div class="csb-option-row csb-<?php echo esc_attr( $row_id ); ?>" <?php if ( empty( $cond[ $row_id ] ) ) : ?>style="display:none"<?php endif; ?>>
					<label for="<?php echo esc_attr( $widget->id ); ?>-<?php echo esc_attr( $row_id ); ?>"><?php esc_html_e( $tax_item->labels->name ); ?></label>
					<input type="text" id="<?php echo esc_attr( $widget->id ); ?>-<?php echo esc_attr( $row_id ); ?>" name="<?php echo esc_attr( $block_name ); ?>[<?php echo esc_attr( $row_id ); ?>]" value="<?php echo esc_attr( @$cond[ $row_id ] ); ?>" />
				</div>
				<?php
			}
			?>

			<div class="csb-option-row csb-pagetypes" <?php if ( empty( $cond['pagetypes'] ) ) : ?>style="display:none"<?php endif; ?>>
				<label for="<?php echo esc_attr( $widget->id ); ?>-pagetypes"><?php _e( 'On these page types', CSB_LANG ); ?></label>
				<select id="<?php echo esc_attr( $widget->id ); ?>-pagetypes" name="<?php echo esc_attr( $block_name ); ?>[pagetypes][]" multiple="multiple">
				<?php foreach ( $pagetype_list as $type => $name ) : ?>
					<?php $is_selected = false !== array_search( $type, $cond['pagetypes'] ); ?>
					<option <?php selected( $is_selected, true ); ?> value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
				</select>
			</div>

		<?php endforeach; ?>

		<?php if ( isset( $_POST[ 'csb-visibility-button' ] ) ) : ?>
			<script>jQuery(function() { jQuery('.csb-visibility-<?php echo esc_js( $widget->id ); ?>').closest('.widget').trigger('csb:ui'); }); </script>
		<?php endif; ?>

		</div>
		</div>
		<?php
	}

	/**
	 * When user saves the widget we check for the
	 *
	 * @since  1.6
	 * @param  array $new_instance New settings for this instance as input by the user.
	 * @param  array $old_instance Old settings for this instance.
	 * @return array Modified settings.
	 */
	public function admin_widget_update( $instance, $new_instance, $old_instance ) {
		if ( isset( $_POST['csb_visibility'] ) ) {
			$instance['csb_visibility'] = $_POST['csb_visibility'];
		}

		if ( ! isset( $instance['csb_visibility'] ) ) {
			$instance['csb_visibility'] = array();
		}

		return $instance;
	}

	/**
	 * Load the javascript support file for the visibility module.
	 *
	 * @since  1.6
	 */
	public function admin_scripts() {
		wp_enqueue_script(
			'csb-visibility',
			CSB_JS_URL . 'visibility.js',
			array( 'jquery' ),
			'1.6',
			true
		);

		wp_enqueue_script(
			'chosen',
			CSB_JS_URL . 'chosen.jquery.min.js',
			array( 'jquery' ),
			'1.6',
			true
		);

		// -- CSS --

		wp_enqueue_style(
			'csb-visibility',
			CSB_CSS_URL . 'visibility.css',
			array(),
			'1.6'
		);

		wp_enqueue_style(
			'chosen',
			CSB_CSS_URL . 'chosen.min.css',
			array(),
			'1.6'
		);
	}
};