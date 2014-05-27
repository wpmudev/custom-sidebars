/**
 * Javascript support for the visibility module.
 */

jQuery(function init_visibility() {

	/**
	 * Moves the "Visibility" button next to the save button
	 */
	var move_button = function move_button() {
		var $widget = jQuery( this ),
			$btn = jQuery( '.csb-visibility', $widget ),
			$target = jQuery( '.widget-control-actions .alignright', $widget );

		$target.prepend( $btn );
	};

	/**
	 * When a widget is saved the ajax response contains a duplicate of the
	 * visibility-button. This function removes the duplicate button again.
	 */
	var ajax_remove_button = function ajax_remove_button(event, jqxhr, data) {
		var res, id, $widget,
			args = decodeURIComponent( data.data );

		// If the ajax action was not "save-widget" then ignore the ajax call.
		if ( ! args.match(/&?action=save-widget&?/) ) {
			return;
		}

		// Try to extract the widget-ID
		res = args.match(/&?widget-id=([^&]*)/);
		if ( res.length > 1 ) {
			id = res[1];

			// Find the widget-element:
			// The ID has to start with "widget" and end with "_" + ID
			$widget = jQuery( '.widget[id^=widget][id$=_' + id + ']', '.widgets-holder-wrap' );

			// Remove the visibility-button from the widget-form again
			jQuery( '.widget-content .csb-visibility', $widget ).remove();
		}
	};

	jQuery( '.widgets-holder-wrap .widget' ).each( move_button );

	/**
	 * This small trick allows us to observe all the ajax traffic of WordPress.
	 * We need to know when a widget was saved so we can update the "Visibility"
	 * button of the specific widget.
	 */
	jQuery.ajaxSetup({
		"global": true
	});

	jQuery( document ).ajaxSuccess( ajax_remove_button );

});