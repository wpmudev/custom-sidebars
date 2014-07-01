/**
 * Javascript functions for the "Select posts" section in the sidebar options.
 */

jQuery(function init_posts() {
	var $doc = jQuery( document ),
		$list = jQuery( '.csb-postlist' ),
		$lbl_empty = jQuery( '.info-empty' ),
		$lbl_assign = jQuery( '.info-assign' ),
		$lbl_count = jQuery( '.val', $lbl_assign ),
		$btn_save = jQuery( '.btn-save' ),
		$form = jQuery( '.the-form' );

	// If ajaxurl is undefined then we are not in the admin page.
	if ( undefined === ajaxurl ) { return; }


	// Update the "Save changes" button when user selects a post/page.
	var update_button = function update_button( ev ) {
		var count = jQuery( 'input[type=checkbox]:checked', $list ).length;

		if ( 0 == count ) {
			$lbl_empty.show();
			$lbl_assign.hide();
			$btn_save.addClass('disabled');
		} else {
			$lbl_empty.hide();
			$lbl_assign.show();
			$lbl_count.text( count );
			$btn_save.removeClass('disabled');
		}
	};

	// When user clicks on the save button do an ajax request to save data.
	var ajax_submit = function ajax_submit( ev ) {
		ev.preventDefault();
		var data = $form.serialize();
		jQuery.post( ajaxurl, data, handle_response, 'json' );
		return false;
	};

	// Handles the ajax response after saving sidebar options.
	var handle_response = function handle_response( data ) {
		for ( idx in data.posts ) {
			var post_id = data.posts[idx],
				row_id = '.post-' + post_id,
				$row = jQuery( row_id, $list ),
				$checkbox = jQuery( 'input[type=checkbox]', $row );

			// Uncheck the checkbox.
			$checkbox.prop( 'checked', false );

			// Clear the sidebar-info columns.
			jQuery( '.sb-col', $row )
				.removeClass( 'is-def is-cust' )
				.attr( 'data-hint', '' );

			// Display the new status info in the sidebar-info columns.
			for ( sb_id in data.sidebars ) {
				var sb_info = data.sidebars[sb_id],
					col_id = '.sb-col-' + sb_id,
					$col = jQuery( col_id, $row );
				$col.addClass( sb_info['class'] )
					.attr( 'data-hint', sb_info['hint'] );
			}
		}
		console.log (data);
	};

	$list.on( 'change', 'input[type=checkbox]', update_button );
	$form.submit( ajax_submit );
});