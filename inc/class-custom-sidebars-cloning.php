<?php

add_action( 'cs_init', array( 'CustomSidebarsCloning', 'instance' ) );

/**
 * Adds visibility options to all widgets:
 * Clone a widget to quickly replicate its settings.
 *
 * @since  2.0
 */
class CustomSidebarsCloning {

	/**
	 * Buffer that holds data of a widget group.
	 * @see update_linked_widgets() // populate the property
	 * @see update_widget_group() // use the property
	 *
	 * @since  2.0
	 * @var    bool|array
	 */
	private $group_data = false;


	/**
	 * Returns the singleton object.
	 *
	 * @since  2.0
	 */
	public static function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new CustomSidebarsCloning();
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

			// in_widget_form: Update data of widget-group (see notes below).
			add_action(
				'in_widget_form',
				array( $this, 'update_widget_group' ),
				10, 3
			);

			// When the widget is saved (via Ajax) we save our options.
			add_filter(
				'widget_update_callback',
				array( $this, 'admin_widget_update' ),
				9999, 4
			);

			// Load the javascript support file for this module.
			$javascript_file = 'cs-cloning.min.js';
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$javascript_file = 'cs-cloning.js';
			}
			lib3()->ui->add( CSB_JS_URL . $javascript_file, 'widgets.php' );
			lib3()->ui->add( CSB_CSS_URL . 'cs-cloning.css', 'widgets.php' );
		}
	}

	/**
	 * Extracts and sanitizes the CSB cloning data from the widget instance.
	 * Cloning data contains the parent widget.
	 *
	 * @since  2.0
	 * @param  array $instance The widget instance data.
	 * @return array Sanitized CSB cloning data.
	 */
	protected function get_widget_data( $instance ) {
		$data = array();

		if ( isset( $instance['csb_clone'] ) ) {
			$data = $instance['csb_clone'];
		}

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		// group: ID of the group

		if ( isset( $data['group'] ) && is_numeric( $data['group'] ) && $data['group'] > 0 ) {
			$group = preg_replace( '/^.*-(\d+)$/', '$1', $data['group'] );
			$state = @$data['state'];
		} else {
			$group = $this->new_group_id();
			$state = 'new';
			$data['group'] = intval( $group );
		}

		// state: ok|empty|new
		if ( ! in_array( $state, array( 'ok', 'empty', 'new' ) ) ) {
			$state = 'new';
		}
		$data['state'] = $state;

		return $data;
	}

	/**
	 * Returns a new/unique group-ID.
	 *
	 * @since  2.0
	 */
	protected function new_group_id() {
		global $wp_registered_widgets;
		static $Used_ids = null;
		$group_id = 1;

		if ( null === $Used_ids ) {
			$Used_ids = array();

			// Loop though all widgets to fetch used IDs.
			foreach ( $wp_registered_widgets as $id => $data ) {
				if ( ! isset( $data['callback'] ) ) { continue; }
				if ( ! is_array( $data['callback'] ) ) { continue; }

				$widget = reset( $data['callback'] );
				$settings = false;
				if ( is_object( $widget ) && method_exists( $widget, 'get_settings' ) ) {
					$settings = $widget->get_settings();
				}

				// Check the database settings of the widget to find group IDs.
				if ( is_array( $settings ) ) {
					foreach ( $settings as $instance ) {
						if ( ! isset( $instance['csb_clone'] ) ) { continue; }
						if ( ! empty( $instance['csb_clone']['group'] ) ) {
							$group = $instance['csb_clone']['group'];
							if ( ! in_array( $group, $Used_ids ) ) {
								$Used_ids[] = $group;
							}
						} // endif: empty(group)
					} // endforeach
				} // endif: is_array()
			} // endforeach

		}

		// Find the first free group-ID.
		while ( in_array( $group_id, $Used_ids ) ) {
			$group_id += 1;
		}
		$Used_ids[] = $group_id;

		return $group_id;
	}

	/**
	 * Finds the settings for the specified group inside the settings array.
	 *
	 * @since  2.0
	 */
	protected function settings_for_group( $settings, $group ) {
		if ( is_numeric( $group ) && $group > 0 ) {
			foreach ( $settings as $data ) {
				$item_group = @$data['csb_clone']['group'];
				$item_status = @$data['csb_clone']['state'];

				if ( $group == $item_group && 'ok' == $item_status ) {
					return $data;
				}
			}
		}
		function_exists( 'wp_debug' ) &&  wp_debug( 'class-custom-sidebars-cloning.php:162', 'FAILED' );
		return false;
	}

	/**
	 * Action handler for 'in_widget_form'
	 *
	 * @since  2.0
	 */
	public function admin_widget_button( $widget, $return, $instance ) {
		$data = $this->get_widget_data( $instance );
		$is_linked = (0 != $data['group']);

		?>
		<div class="csb-clone csb-clone-<?php echo esc_attr( $widget->id ); ?>">
		<?php
		/*
		 * This input is only used to determine if the "visibility" button
		 * should be displayed in the widget form.
		 */
		?>
		<input type="hidden" name="csb-clone-button" value="0" />
		<input type="hidden" name="csb_clone[group]" class="csb-clone-group" value="<?php echo esc_attr( $data['group'] ); ?>" />
		<input type="hidden" name="csb_clone[state]" class="csb-clone-state" value="<?php echo esc_attr( $data['state'] ); ?>" />
		<?php if ( ! isset( $_POST['csb-clone-button'] ) && ( 'widgets' === get_current_screen()->id ) ) : ?>
			<a href="#" class="button csb-clone-button"><?php _e( 'Clone', 'custom-sidebars' ); ?></a>
		<?php else : ?>
			<script>jQuery(function() { jQuery('.csb-clone-<?php echo esc_js( $widget->id ); ?>').closest('.widget').trigger('csb:update'); }); </script>
		<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Action handler for 'in_widget_form'.
	 *
	 * The workflow for this function is not very obvious, it works like this:
	 * When a widget is saved it may update a number of other widgets that
	 * belong to the same widget-group (i.e. cloned widgets).
	 * However, the update function allows us to only change data of the updated
	 * widget - all changes made to other widgets will be overwritten by
	 * WordPress after the widget_update_callback filter is executed.
	 *
	 * So the widget_update_callback filter will save the modified widget data
	 * inside the property $this->group_data. {@see update_linked_widgets}
	 *
	 * After the widget_update_callback filter is called the widget is rendered
	 * again. Now the in_widget_form hook is called. It is not related to saving
	 * the widget but provides a way to update the settings in chronolically
	 * correct order, this is why we hijack it for saving settings...
	 *
	 * @since  2.0
	 */
	public function update_widget_group( $widget, $return, $instance ) {
		if ( ! empty( $this->group_data ) ) {
			$widget->save_settings( $this->group_data );
		}
	}

	/**
	 * Apply cloning logic when user saves the widget.
	 *
	 * @since  2.0
	 * @param  array $new_instance New settings for this instance as input by the user.
	 * @param  array $old_instance Old settings for this instance.
	 * @param  WP_Widget $widget   The current widget instance.
	 * @return array Modified settings.
	 */
	public function admin_widget_update( $instance, $new_instance, $old_instance, $widget ) {
		$data = $this->get_widget_data( $_POST );

		$instance['csb_clone'] = $data;
		$settings = $widget->get_settings();
		$my_id = $widget->number;

		switch ( @$instance['csb_clone']['state'] ) {
			case 'empty':
				return $this->populate_widget( $my_id, $settings, $instance, $widget );
				break;

			case 'ok':
				return $this->update_linked_widgets( $my_id, $settings, $instance, $widget );
				break;

			default:
				$instance['csb_clone']['state'] = 'ok';
				return $instance;
		}
	}

	/**
	 * This function returns the $instance data of a new clone. The data is
	 * populated with the values of the widget-group.
	 */
	protected function populate_widget( $id, $settings, $instance, $widget ) {
		$instance['csb_clone']['state'] = 'ok';

		if ( ! isset( $instance['csb_clone']['group'] ) ) {
			// Widget does not have any cloning information.
			return $instance;
		}

		$group = $instance['csb_clone']['group'];
		if ( empty( $group ) ) {
			// Widget does not have a group (anymore).
			return $instance;
		}

		$group_data = $this->settings_for_group( $settings, $group );
		if ( empty( $group_data ) ) {
			// The specified group does not exist (anymore).
			return $instance;
		}

		// Success, fetch the config from group!
		$instance = $group_data;
		return $instance;
	}

	/**
	 * Update ALL widgets in the same group as the specified widget.
	 */
	protected function update_linked_widgets( $id, $settings, $instance, $widget ) {
		$instance['csb_clone']['state'] = 'ok';
		$group_data = $instance;
		$my_group = @$group_data['csb_clone']['group'];

		foreach ( $settings as $key => $the_inst ) {
			if ( ! isset( $the_inst['csb_clone']['group'] ) ) {
				// Widget does not have any cloning information.
				continue;
			}

			$group = $the_inst['csb_clone']['group'];
			if ( empty( $group ) ) {
				// Widget does not have a group (anymore).
				continue;
			}

			if ( $group != $my_group ) {
				// This widget does not belong to the current group.
				continue;
			}

			// Success, this widget needs to be updated!
			$settings[ $key ] = $group_data;
		}

		$settings[ $id ] = $group_data;
		$this->group_data = $settings;

		return $instance;
	}
};
