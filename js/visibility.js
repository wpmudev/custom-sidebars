/**
 * Javascript support for the visibility module.
 */

jQuery(function init_visibility() {

	/**
	 * Moves the "Visibility" button next to the save button.
	 */
	var init_widget = function init_widget() {
		var $widget = jQuery( this ),
			$btn = jQuery( '.csb-visibility-button', $widget ),
			$target = jQuery( '.widget-control-actions .widget-control-save', $widget ),
			$spinner = jQuery( '.widget-control-actions .spinner', $widget );

		$spinner.insertBefore( $target ).css({ 'float': 'left' });
		$btn.insertBefore( $target ).click( toggle_section );
		$widget.on( 'click', '.toggle-action', toggle_action );
		$widget.on( 'csb:ui', init_ui ).trigger( 'csb:ui' );
	};

	/**
	 * Initialize the UI components (i.e. chosen select-list, etc)
	 */
	var init_ui = function init_ui( ev ) {
		var $widget = jQuery( this );
		console.log('init ui', $widget);
		jQuery( '.csb-visibility select[multiple]', $widget ).chosen();
	};

	/**
	 * Shows or hides the visibility-options for the current widget.
	 */
	var toggle_section = function toggle_section( ev ) {
		var $me = jQuery( this ),
			$widget = $me.closest( '.widget' ),
			$section = $widget.find( '.csb-visibility-inner' ),
			$flag = $section.find( '.csb-visible-flag' );

		ev.preventDefault();
		if ( $flag.val() == '0' ) {
			$flag.val(1);
			$section.show();
		} else {
			$flag.val(0);
			$section.hide();
		}

		return false;
	};

	/**
	 * Toggles the widget state between "show if" / "hide if"
	 */
	var toggle_action = function toggle_action( ev ) {
		var $me = jQuery( this ),
			$widget = $me.closest( '.widget' ),
			sel = '#' + $me.attr( 'for' ),
			$action = $widget.find( sel ),
			state = $action.val(),
			$lbl_show = $widget.find( '.lbl-show-if' ),
			$lbl_hide = $widget.find( '.lbl-hide-if' );

		ev.preventDefault();
		if ( 'show' != state ) {
			$lbl_show.show();
			$lbl_hide.hide();
			$action.val( 'show' );
		} else {
			$lbl_show.hide();
			$lbl_hide.show();
			$action.val( 'hide' );
		}
		return false;
	}

	jQuery( '.widgets-holder-wrap .widget' ).each( init_widget );
});