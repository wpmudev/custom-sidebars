/*! Custom Sidebars - v3.2.3
 * https://premium.wpmudev.org/project/custom-sidebars-pro/
 * Copyright (c) 2019; * Licensed GPLv2+ */
/*global jQuery:false */
/*global window:false */
/*global document:false */
/*global wp:false */
/*global wpmUi:false */

jQuery(function init_cloning() {
	var $doc = jQuery( document ),
		$all = jQuery( '#widgets-right' ),
		is_cloning = false;

	/**
	 * Updates all group_id values for the widget-templates to the next free id.
	 */
	var update_template_groups = function update_template_groups() {
		var $groups = jQuery( '#widgets-left input.csb-clone-group' ),
			next_id = parseInt( $groups.first().val() );
		while ( $all.find( 'input.csb-clone-group[value="' + next_id + '"]' ).length ) {
			next_id += 1;
		}
		$groups.val( next_id );
	};

	/**
	 * Clones the widget:
	 * Add a new widget using default WordPress JS API and then update all the
	 * input values of the new widget to match the original widget.
	 */
	var clone_widget = function clone_widget( ev ) {
		var $widget = jQuery( this ).closest( '.widget' ),
			$available = jQuery( '#widgets-left' ),
			$chooser = jQuery( '.widgets-chooser' ),
			$content = jQuery( '#wpbody-content' );

		ev.preventDefault();
		is_cloning = true;

		// 1. If the current widget is new then first save the current widget
		var state = $widget.find( 'input.csb-clone-state' ).val();
		if ( 'new' === state ) {
			window.wpWidgets.save( $widget, 0, 0, 0 );
		}

		// 2. Close any open chooser
		window.wpWidgets.clearWidgetSelection();
		$chooser.slideUp( 200, function() {
			$chooser.hide();
			$content.append( this );
		});

		// 3. Find the "widget-in-question".
		var class_name = $widget.find('input.id_base').val(),
			$base = $available.find('input.id_base[value="' + class_name + '"]'),
			$in_question = $base.closest( '.widget' );
		$in_question.addClass( 'widget-in-question' );

		// 4. Provide data about the origin widget.
		var group_id = $widget.find( 'input.csb-clone-group' ).val(),
			$contr = $in_question.find( '.widget-control-actions' ),
			$group = $in_question.find( 'input.csb-clone-group' ),
			$state = $in_question.find( 'input.csb-clone-state' );
		$group.val( group_id );
		$state.val( 'empty' );

		// 5. Select the current sidebar in the chooser.
		var $sidebar = $widget.closest( '.widgets-sortables' ),
			sb_id = $sidebar.attr( 'id' );
		$chooser.find ( '.widgets-chooser-selected' ).removeClass( 'widgets-chooser-selected' );
		$chooser.find( 'li' ).each( function() {
			var $li = jQuery( this );
			if ( sb_id === $li.data('sidebarId') ) {
				$li.addClass( 'widgets-chooser-selected' ).focus();
			}
		});

		// 6. Add the new widget to the sidebar.
		//    This will directly trigger the ajax command to save the widget.
		window.wpWidgets.addWidget( $chooser );

		// 7. Remove the custom elements and information again.
		window.wpWidgets.clearWidgetSelection();
		update_template_groups();

		is_cloning = false;

		return false;
	};

	/**
	 * Update all widgets belonging to the same group.
	 */
	var prepare_update_group = function prepare_update_group( ev ) {
		var $widget = jQuery( this ).closest( '.widget' ),
			group_id = $widget.find( 'input.csb-clone-group' ).val(),
			$members = $all.find( 'input.csb-clone-group[value="' + group_id + '"]' ).closest( '.widget' ).not( $widget );
		$members.each(function() {
			var $item = jQuery( this ),
				$state = $item.find( 'input.csb-clone-state' );
			$item.addClass('wpmui-loading').attr( 'data-reload', true );
		});
	};

	/**
	 * Moves the "Clone" button next to the save button.
	 */
	var init_widget = function init_widget( ev, el ) {
		var $widget = jQuery( el ).closest( '.widget' ),
			$btn = $widget.find( '.csb-clone-button' ),
			$target = $widget.find( '.widget-control-actions .widget-control-save' ),
			$spinner = $widget.find( '.widget-control-actions .spinner' ),
			$btn_save = $widget.find( '.widget-control-save' );

		if ( $widget.data( '_csb_cloning' ) ) {
			return;
		}

		$spinner.insertBefore( $target ).css({ 'float': 'left' });
		$btn.insertBefore( $target ).click( clone_widget );
		$btn_save.click( prepare_update_group );

		$widget.data( '_csb_cloning', true );
	};

	/**
	 * Updates the group-counter when a widget is added.
	 */
	var update_group_counter = function update_group_counter( ev, el ) {
		// We do NOT want to change the group-id when we clone a widget...
		if ( is_cloning ) {
			return false;
		}
		var $widget = jQuery( el ).closest( '.widget' ),
			$widget_group = $widget.find( 'input.csb-clone-group' ),
			group_id = parseInt( $widget_group.val() ),
			check = null;
		do {
			check = $all.find( 'input.csb-clone-group[value="' + group_id + '"]' );
			if ( ! check.length || ( 1 === check.length && check[0] === $widget_group[0] ) ) {
				break;
			} else {
				group_id += 1;
			}
		}
		while ( true );

		$widget_group.val( group_id );
		update_template_groups();
	};


	/**
	 * Viually highlights all widgets of the same group.
	 */
	var mark_group = function mark_group( ev ) {
		var $widget = jQuery( this ).closest( '.widget' ),
			group_id = $widget.find( 'input.csb-clone-group' ).val(),
			$members = $all.find( 'input.csb-clone-group[value="' + group_id + '"]' ).closest( '.widget' );

		if ( isNaN( group_id ) || group_id < 1 ) {
			return;
		}

		$members.addClass('csb-marker');
		$widget.removeClass('csb-marker');
	};

	/**
	 * Removes the visual highlighting of group widgets.
	 */
	var unmark_group = function unmark_group( ev ) {
		var $marked = jQuery( '.widget.csb-marker' );
		$marked.removeClass('csb-marker');
	};

	/**
	 * Remove widget from group/assign to group again (only works until widget
	 * was saved.)
	 */
	var toggle_group = function toggle_group( ev ) {
		var $widget = jQuery( this ).closest( '.widget' ),
			$title = $widget.find( '.widget-title h4' ),
			$icon = $title.find( '.btn-clone-group' ),
			$group = $widget.find( 'input.csb-clone-group' );
		ev.preventDefault();
		ev.stopPropagation();
		if ( $title.hasClass( 'group-active' ) ) {
			$title.removeClass( 'group-active' );
			$icon.removeClass('dashicons-admin-links').addClass('dashicons-editor-unlink');
			$group.data( 'group', $group.val() );
			$group.val( 0 );
			unmark_group();
		} else {
			$title.addClass( 'group-active' );
			$icon.addClass('dashicons-admin-links').removeClass('dashicons-editor-unlink');
			$group.val( $group.data( 'group' ) );
			mark_group.call( this, [ev] );
		}
		return false;
	};

	/**
	 * Adds icons to all widgets that are inside a group.
	 */
	var init_group_icons = function init_group_icons() {
		var $groups = $all.find( 'input.csb-clone-group' );
		$groups.each(function() {
			var group_id = jQuery( this ).val(),
				$members = $all.find( 'input.csb-clone-group[value="' + group_id + '"]' ).closest( '.widget' ),
				$titles = $members.find( '.widget-title h4, .widget-title h3' ),
				action = 'add';
			if ( isNaN( group_id ) || group_id < 1 ) {
				action = 'remove';
			}
			if ( $members.length < 2 ) {
				action = 'remove';
			}
			// Always remove the icons from the group.
			$titles.removeClass( 'csb-group group-active' )
				.find( '.btn-clone-group' ).remove();
			$members.removeAttr( 'data-csb-icon' );
			// If action is "add" then we add the icons again.
			if ( action === 'add' ) {
				$titles.addClass( 'csb-group group-active' )
					.prepend( '<i class="dashicons dashicons-admin-links btn-clone-group"></i> ' );
				$titles.find( '.btn-clone-group' )
					.hover( mark_group, unmark_group )
					.click( toggle_group );
			}
		});
	};

	/**
	 * Saves the specified widget if the clone-state is "empty".
	 */
	var populate_widget = function populate_widget( $widget ) {
		var $state = $widget.find( 'input.csb-clone-state' );

		if ( $state.val() === 'empty' ) {
			$widget.addClass( 'wpmui-loading' );
			window.wpWidgets.save( $widget, 0, 1, 0 );
		}
	};

	/**
	 * Update all widgets belonging to the same group.
	 */
	var update_group_widgets = function update_group_widgets( el ) {
		var $widgets = $all.find( '.widget[data-reload]' );

		$widgets.each(function() {
			var $item = jQuery( this ),
				$state = $item.find( 'input.csb-clone-state' );

			$state.val( 'empty' );
			$item.removeAttr( 'data-reload' );
			window.wpWidgets.save( $item, 0, 0, 0 );
		});
	};

	/**
	 * Global ajax observer reacts to all ajax responses. We need this to find
	 * out when a new widget is saved for the first time.
	 */
	var ajax_observer = function ajax_observer( ev, xhr, opt, resp ) {
		var data = ( 'string' === typeof opt.data ? opt.data : '' ),
			find_action = data.match( /^.*&action=([^&]+).*$/ ),
			find_widget = data.match( /^.*&widget-id=([^&]+).*$/ ),
			action = (find_action && find_action.length === 2 ? find_action[1] : ''),
			widget = (find_widget && find_widget.length === 2 ? find_widget[1] : '');

		if ( ! widget.length ) {
			return;
		}

		var $base = jQuery( '.widget input.widget-id[value="' + widget + '"]' ),
			$widget = $base.closest( '.widget' );

		switch ( action ) {
			case 'save-widget':
				$widget.removeClass( 'wpmui-loading' );
				if ( ! resp.length ) {
					// Populate widget with data from group, if required.
					populate_widget( $widget );
				} else if ( resp.match( /^deleted:/ ) ) {
					// Widget was deleted and is removed with animation.
					window.setTimeout( init_group_icons, 400 );
				} else {
					// Existing widget was updated.
					init_group_icons();
					update_group_widgets( $widget );
				}
				break;

			default:
				// Unrelated ajax event.
		}
	};


	$all.find( '.widget' ).each( init_widget );
	$doc.on( 'widget-added', init_widget );
	$doc.on( 'widget-added', update_group_counter );
	$doc.ajaxSuccess( ajax_observer );

	init_group_icons();
	update_template_groups();
});
