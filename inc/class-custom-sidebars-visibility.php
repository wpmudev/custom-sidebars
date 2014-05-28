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

		$data = $this->get_widget_data( $instance );
		$action_show = ($data['action'] == 'show');

		?>
		<div class="csb-visibility">
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
		<input type="hidden" name="csb_visible" class="csb-visible-flag" value="<?php esc_attr_e( $is_visible ); ?>" />

		<div class="csb-option-row csb-action">
			<label for="<?php esc_attr_e( $widget->id ); ?>-action" class="lbl-show-if toggle-action" <?php if ( ! $action_show ) : ?>style="display:none"<?php endif; ?>><?php _e( 'Show widget if:', CSB_LANG ); ?></label>
			<label for="<?php esc_attr_e( $widget->id ); ?>-action" class="lbl-hide-if toggle-action" <?php if ( $action_show ) : ?>style="display:none"<?php endif; ?>><?php _e( 'Hide widget if:', CSB_LANG ); ?></label>
			<input type="hidden" id="<?php esc_attr_e( $widget->id ); ?>-action" name="csb_visibility[action]" value="<?php esc_attr_e( $data['action'] ); ?>" />
		</div>

		<?php foreach ( $data['conditions'] as $index => $cond ) : ?>
			<div class="csb-option-row csb-date">
				<label for="<?php esc_attr_e( $widget->id ); ?>-date"><?php _e( 'Date', CSB_LANG ); ?></label>
				<input type="text" id="<?php esc_attr_e( $widget->id ); ?>-date" name="csb_visibility[conditions][date]" value="<?php esc_attr_e( @$cond['date'] ); ?>" />
			</div>
			<div class="csb-option-row csb-roles">
				<label for="<?php esc_attr_e( $widget->id ); ?>-roles"><?php _e( 'User roles', CSB_LANG ); ?></label>
				<input type="text" id="<?php esc_attr_e( $widget->id ); ?>-roles" name="csb_visibility[conditions][roles]" value="<?php esc_attr_e( @$cond['roles'] ); ?>" />
			</div>
			<div class="csb-option-row csb-membership">
				<label for="<?php esc_attr_e( $widget->id ); ?>-membership"><?php _e( 'Membership Level', CSB_LANG ); ?></label>
				<input type="text" id="<?php esc_attr_e( $widget->id ); ?>-membership" name="csb_visibility[conditions][membership]" value="<?php esc_attr_e( @$cond['membership'] ); ?>" />
			</div>
			<div class="csb-option-row csb-prosite">
				<label for="<?php esc_attr_e( $widget->id ); ?>-prosite"><?php _e( 'Pro Sites Level', CSB_LANG ); ?></label>
				<input type="text" id="<?php esc_attr_e( $widget->id ); ?>-prosite" name="csb_visibility[conditions][prosite]" value="<?php esc_attr_e( @$cond['prosite'] ); ?>" />
			</div>
			<div class="csb-option-row csb-posttypes">
				<label for="<?php esc_attr_e( $widget->id ); ?>-posttypes"><?php _e( 'Posttypes', CSB_LANG ); ?></label>
				<input type="text" id="<?php esc_attr_e( $widget->id ); ?>-posttypes" name="csb_visibility[conditions][posttypes]" value="<?php esc_attr_e( @$cond['posttypes'] ); ?>" />
			</div>
			<?php
			foreach ( $type_list as $type_item ) {
				$row_id = 'pt-' . $type_item->name;
				?>
				<div class="csb-option-row csb-<?php esc_attr_e( $row_id ); ?>">
					<label for="<?php esc_attr_e( $widget->id ); ?>-<?php esc_attr_e( $row_id ); ?>"><?php esc_html_e( $type_item->labels->name ); ?></label>
					<input type="text" id="<?php esc_attr_e( $widget->id ); ?>-<?php esc_attr_e( $row_id ); ?>" name="csb_visibility[conditions][<?php esc_attr_e( $row_id ); ?>]" value="<?php esc_attr_e( @$cond[ $row_id ] ); ?>" />
				</div>
				<?php
			}
			?>

			<?php
			foreach ( $tax_list as $tax_item ) {
				$row_id = 'tax-' . $tax_item->name;
				?>
				<div class="csb-option-row csb-<?php esc_attr_e( $row_id ); ?>">
					<label for="<?php esc_attr_e( $widget->id ); ?>-<?php esc_attr_e( $row_id ); ?>"><?php esc_html_e( $tax_item->labels->name ); ?></label>
					<input type="text" id="<?php esc_attr_e( $widget->id ); ?>-<?php esc_attr_e( $row_id ); ?>" name="csb_visibility[conditions][<?php esc_attr_e( $row_id ); ?>]" value="<?php esc_attr_e( @$cond[ $row_id ] ); ?>" />
				</div>
				<?php
			}
			?>

		<?php endforeach; ?>

		</div>
		</div>
		<?php
	}

	/**
	 * When user saves the widget we check for the
	 *
	 * @since  1.6
	 * @param array $new_instance New settings for this instance as input by the user.
	 * @param array $old_instance Old settings for this instance.
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

		wp_enqueue_style(
			'csb-visibility',
			CSB_CSS_URL . 'visibility.css',
			array(),
			'1.6'
		);
	}
};