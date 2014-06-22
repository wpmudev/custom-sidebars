/**
 * CsSidebar class
 *
 * This adds new functionality to each sidebar.
 *
 * Note: Before the first CsSidebar object is created the class csSidebars below
 * must be initialized!
 */
function CsSidebar(id, type) {
	/**
	 * Replace % to fix bug http://wordpress.org/support/topic/in-wp-35-sidebars-are-not-collapsable-anymore?replies=16#post-3990447
	 * We'll use this.id to select and the original id for html
	 *
	 * @since 1.2
	 */
	this.id = id.split('%').join('\\%');

	/**
	 * Either 'custom' or 'theme'
	 *
	 * @since 1.6
	 */
	this.type = type;

	this.sb = jQuery('#' + this.id);

	this.widgets = '';
	this.name = trim(this.sb.find('.sidebar-name h3').text());
	this.description = trim(this.sb.find('.sidebar-description').text());

	// Add one of two editbars to each sidebar.
	if ( type == 'custom' ) {
		var editbar = csSidebars.extras.find('.cs-custom-sidebar').clone();
	} else {
		var editbar = csSidebars.extras.find('.cs-theme-sidebar').clone();
	}

	this.sb.parent().append(editbar);

	// Customize the links and label-tags.
	editbar.find('label').each(function(){
		var me = jQuery( this );
		csSidebars.addIdToLabel( me, id );
	});
}

/**
 * Returns the sidebar ID.
 *
 * @since  1.6.0
 */
CsSidebar.prototype.getID = function() {
	return this.id.split('\\').join('');
}


/**
 * =============================================================================
 *
 *
 * csSidebars class
 *
 * This is the collection of all CsSidebar objects.
 */
var csSidebars, msgTimer;
(function($){
	csSidebars = {
		/**
		 * List of all CsSidebar objects.
		 * @type array
		 */
		sidebars: [],

		/**
		 * This is the same prefix as defined in class-custom-sidebars.php
		 * @type string
		 */
		sidebar_prefix: 'cs-',

		/**
		 * Form for the edit-sidebar popup.
		 * @type: jQuery object
		 */
		edit_form: null,

		/**
		 * Form for the delete-sidebar popup.
		 * @type: jQuery object
		 */
		delete_form: null,

		/**
		 * Form for the export/import popup.
		 * @type: jQuery object
		 */
		export_form: null,

		/**
		 * Shortcut to '#widgets-right'
		 * @type: jQuery object
		 */
		right: null,

		/**
		 * Shortcut to '#cs-widgets-extra'
		 * @type: jQuery object
		 */
		extras: null,

		/**
		 * Shortcut to '#cs-widgets-extra .cs-lang'
		 * @type: jQuery object
		 */
		lang: null,

		init: function(){
			csSidebars
				.initControls()
				.initScrollbar()
				.initTopTools()
				.initSidebars()
				.initToolbars()
				.initColumns();
		},

		/**
		 * =====================================================================
		 * Initialize the custom scrollbars on the right side.
		 *
		 * @since  1.0.0
		 */
		initScrollbar: function(){
			var viewport = jQuery( '.viewport' ),
				right_side = jQuery( '.widget-liquid-right' );

			csSidebars.right
				.addClass('overview')
				.wrap('<div class="viewport" />');

			viewport
				.height($(window).height() - 60);

			right_side
				.height($(window).height())
				.prepend('<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>')
				.tinyscrollbar();

			// Re-calculate the scrollbar size.
			var update_scrollbars = function update_scrollbars() {
				right_side.tinyscrollbar_update('relative');
			};

			$(window).resize(function() {
				right_side.height($(window).height());
				viewport.height($(window).height() - 60);
				right_side.tinyscrollbar_update('relative');
			});

			right_side.click(function(){
				setTimeout( update_scrollbars, 400 );
			});

			right_side.hover(
				function() {
					$('.scrollbar').fadeIn();
				}, function() {
					$('.scrollbar').fadeOut();
				}
			);
			return csSidebars;
		},

		/**
		 * =====================================================================
		 * Initialize DOM and find jQuery objects
		 *
		 * @since 1.0.0
		 */
		initControls: function(){
			csSidebars.right = jQuery( '#widgets-right' );
			csSidebars.extras = jQuery( '#cs-widgets-extra' );
			csSidebars.lang = csSidebars.extras.find( '.cs-lang' );

			if ( null == csSidebars.edit_form ) {
				csSidebars.edit_form = csSidebars.extras.find( '.cs-editor' ).clone();
				csSidebars.extras.find( '.cs-editor' ).remove();
			}

			if ( null == csSidebars.delete_form ) {
				csSidebars.delete_form = csSidebars.extras.find( '.cs-delete' ).clone();
				csSidebars.extras.find( '.cs-delete' ).remove();
			}

			if ( null == csSidebars.export_form ) {
				csSidebars.export_form = csSidebars.extras.find( '.cs-export' ).clone();
				csSidebars.extras.find( '.cs-export' ).remove();
			}

			jQuery('#cs-title-options')
				.detach()
				.prependTo( csSidebars.right );

			return csSidebars;
		},

		/**
		 * =====================================================================
		 * Arrange sidebars in left/right columns.
		 * Left column: Custom sidebars. Right column: Theme sidebars.
		 *
		 * @since  1.6
		 */
		initColumns: function() {
			var col1 = csSidebars.right.find( '.sidebars-column-1' ),
				col2 = csSidebars.right.find( '.sidebars-column-2' ),
				title1 = csSidebars.extras.find( '.cs-title-col1' ),
				title2 = csSidebars.extras.find( '.cs-title-col2' ),
				sidebars = csSidebars.right.find( '.widgets-holder-wrap' );

			if ( ! col2.length ) {
				col2 = jQuery( '<div class="sidebars-column-2"></div>' );
				col2.appendTo( csSidebars.right );
			}

			col1.prepend( title1 );
			col2.prepend( title2 );

			sidebars.each(function check_sidebar() {
				var me = jQuery( this ),
					sbar = me.find( '.widgets-sortables' );

				if ( csSidebars.isCustomSidebar( sbar) ) {
					me.appendTo( col1 );
				} else {
					me.appendTo( col2 );
				}
			});
		},

		/**
		 * =====================================================================
		 * Initialize the top toolbar, above the sidebar list.
		 *
		 * @since  1.0.0
		 */
		initTopTools: function() {
			var btn_create = jQuery( '.btn-create-sidebar' ),
				btn_export = jQuery( '.btn-export' ),
				data = {};

			// Button: Add new sidebar.
			btn_create.click(function() {
				data.id = '';
				data.title = csSidebars.lang.data( 'title-new' );
				data.button = csSidebars.lang.data( 'btn-new' );
				data.description = '';
				data.name = '';

				csSidebars.showEditor( data );
			});

			// Button: Export sidebars.
			btn_export.click( csSidebars.showExport );

			return csSidebars;
		},

		/**
		 * =====================================================================
		 * Show the editor for a custom sidebar as a popup window.
		 *
		 * @since  1.6.0
		 * @param  Object data Data describing the popup window.
		 *           - id .. ID of the sidebar (text).
		 *           - name .. Value of field "name".
		 *           - description .. Value of field "description".
		 *           - title .. Text for the window title.
		 *           - button .. Caption of the save button.
		 */
		showEditor: function( data ) {
			var popup = null,
				ajax = null;

			// Hide the "extra" fields
			var hide_extras = function hide_extras() {
				popup.$().removeClass( 'csb-has-more' );
				popup.size( null, 280 );
			};

			// Show the "extra" fields
			var show_extras = function show_extras() {
				popup.$().addClass( 'csb-has-more' );
				popup.size( null, 580 );
			};

			// Toggle the "extra" fields based on the checkbox state.
			var toggle_extras = function toggle_extras() {
				if ( jQuery( this ).prop( 'checked' ) ) {
					show_extras();
				} else {
					hide_extras();
				}
			}

			// Populates the input fields in the editor with given data.
			var set_values = function set_values( data, okay, xhr ) {
				popup.loading( false );

				// Ignore error responses from Ajax.
				if ( ! okay ) { return false; }

				// Populate known fields.
				if ( undefined !== data.id ) {
					popup.$().find( '#csb-id' ).val( data.id );
				}
				if ( undefined !== data.name ) {
					popup.$().find( '#csb-name' ).val( data.name );
				}
				if ( undefined !== data.description ) {
					popup.$().find( '#csb-description' ).val( data.description );
				}
				if ( undefined !== data.before_title ) {
					popup.$().find( '#csb-before-title' ).val( data.before_title );
				}
				if ( undefined !== data.after_title ) {
					popup.$().find( '#csb-after-title' ).val( data.after_title );
				}
				if ( undefined !== data.before_widget ) {
					popup.$().find( '#csb-before-widget' ).val( data.before_widget );
				}
				if ( undefined !== data.after_widget ) {
					popup.$().find( '#csb-after-widget' ).val( data.after_widget );
				}
				if ( undefined !== data.button ) {
					popup.$().find( '.btn-save' ).text( data.button );
				}
			};

			// Submit the data via ajax.
			var save_data = function save_data() {
				var form = popup.$().find( 'form' );

				// Close popup after ajax request
				var handle_done = function handle_done( resp, okay, xhr ) {
					// Remove animation.
					popup.loading( false );

					if ( okay ) {
						popup.close();
					}
				};

				// Start loading-animation.
				popup.loading( true );

				ajax.reset()
					.data( form )
					.ondone( handle_done )
					.load_json();

				return false;
			};

			// Show the popup.
			popup = wpmUi.popup()
				.modal( true )
				.title( data.title )
				.onshow( hide_extras )
				.content( csSidebars.edit_form );

			hide_extras();
			set_values( data, true, null );

			// Create new ajax object to get sidebar details.
			ajax = wpmUi.ajax( null, 'cs-ajax' );
			if ( data.id ) {
				popup.loading( true );
				ajax.reset()
					.data({
						'do': 'get',
						'sb': data.id
					})
					.ondone( set_values )
					.load_json();
			}

			popup.show();

			// Add event hooks to the editor.
			popup.$().on( 'click', '#csb-more', toggle_extras );
			popup.$().on( 'click', '.btn-save', save_data );

			return csSidebars;
		},

		/**
		 * =====================================================================
		 * Shows a popup window with the export/import form.
		 *
		 * @since  1.6.0
		 */
		showExport: function() {
			var popup = null;

			// Download export file.
			var do_export = function do_export( ev ) {
				var ajax = wpmUi.ajax(),
					form = jQuery( this ).closest( 'form' );

				ajax.data( form )
					.load_http( 'cs-ajax' );

				ev.preventDefault();
				return false;
			};

			// Ajax handler after import file was uploaded.
			var handle_upload_done = function handle_upload_done( resp, okay, xhr ) {
				popup.loading( false );

				if ( okay ) {
					popup
						.size( 900, 600 )
						.content( resp );
				}
			};

			// Upload the import file.
			var do_upload = function do_upload( ev ) {
				var ajax = wpmUi.ajax(),
					form = jQuery( this ).closest( 'form' );

				popup.loading( true );
				ajax.data( form )
					.ondone( handle_upload_done )
					.load_text( 'cs-ajax' );

				ev.preventDefault();
				return false;
			};

			// Import preview: Toggle widgets
			var toggle_widgets = function toggle_widgets() {
				var me = jQuery( this ),
					checked = me.prop( 'checked' ),
					items = popup.$().find( '.column-widgets, .import-widgets' );

				if ( checked ) {
					items.show();
				} else {
					items.hide();
				}
			};

			// Import preview: Cancel
			var show_overview = function show_overview() {
				popup
					.size( 740, 480 )
					.content( csSidebars.export_form );
			};

			// Show the popup.
			popup = wpmUi.popup()
				.modal( true )
				.size( 740, 480 )
				.title( csSidebars.lang.data( 'title-export' ) )
				.content( csSidebars.export_form )
				.show();

			// Events for the Import / Export view.
			popup.$().on( 'submit', '.frm-export', do_export );
			popup.$().on( 'submit', '.frm-preview-import', do_upload );

			// Events for the Import preview.
			popup.$().on( 'change', '#import-widgets', toggle_widgets );
			popup.$().on( 'click', '.btn-cancel', show_overview );

			return false;
		},

		/**
		 * =====================================================================
		 * Ask for confirmation before deleting a sidebar
		 */
		showRemove: function( sb ) {
			var popup = null,
				ajax = null,
				id = sb.getID(),
				name = sb.name;

			// Insert the sidebar name into the delete message.
			var insert_name = function insert_name( el ) {
				el.find('.name').text( name );
			};

			// Closes the delete confirmation.
			var close_popup = function close_popup() {
				popup.close();
			};

			// Handle response of the delete ajax-call.
			var handle_done = function handle_done( resp, okay, xhr ) {
				popup.loading( false );

				if ( okay ) {
					// Remove the Sidebar from the page.
					csSidebars.right
						.find('#' + id)
						.closest('.widgets-holder-wrap')
						.remove();

					// Remove object from internal collection.
					csSidebars.remove( id );

					popup.close();
				}
			}

			// Deletes the sidebar and closes the confirmation popup.
			var delete_sidebar = function delete_sidebar() {
				popup.loading( true );

				ajax.reset()
					.data({
						'do': 'delete',
						'sb': id
					})
					.ondone( handle_done )
					.load_json();
			};

			// Show the popup.
			popup = wpmUi.popup()
				.modal( true )
				.size( null, 160 )
				.title( csSidebars.lang.data( 'title-delete' ) )
				.content( csSidebars.delete_form )
				.onshow( insert_name )
				.show();

			// Create new ajax object.
			ajax = wpmUi.ajax( null, 'cs-ajax' );

			popup.$().on( 'click', '.btn-cancel', close_popup );
			popup.$().on( 'click', '.btn-delete', delete_sidebar );

			return false;
		},

		/**
		 * =====================================================================
		 * Show popup to assign sidebar to default categories.
		 *
		 * @since  1.6.0
		 */
		showLocations: function( sb ){
			var popup = null,
				ajax = null,
				id = sb.getID();

			// Display the location data after it was loaded by ajax.
			var show_data = function show_data( resp, okay, xhr ) {
				popup.loading( false );

				if ( okay ) {
					popup.content( resp );
				}
			};

			// Show the popup.
			popup = wpmUi.popup()
				.modal( true )
				.size( null, 500 )
				.title( csSidebars.lang.data( 'title-location' ) )
				.show();

			popup.loading( true );

			// Initialize ajax object.
			ajax = wpmUi.ajax( null, 'cs-ajax' );
			ajax.reset()
				.data({
					'do': 'get-location',
					'sb': id
				})
				.ondone( show_data )
				.load_text();

			return false;
		},

		/**
		 * =====================================================================
		 * Initialization function, creates a CsSidebar object for each sidebar.
		 *
		 * @since  1.0.0
		 */
		initSidebars: function(){
			csSidebars.right.find('.widgets-sortables').each(function() {
				var id = $(this).attr('id');

				if ( csSidebars.isCustomSidebar( this ) ) {
					csSidebars.add( id, 'custom' );
				} else {
					csSidebars.add( id, 'theme' );
				}
			});
			return csSidebars;
		},

		/**
		 * =====================================================================
		 * Hook up all the functions in the sidebar toolbar.
		 * Toolbar is in the bottom of each sidebar.
		 *
		 * @since  1.0.0
		 */
		initToolbars: function() {
			var tool_action = function( ev ) {
				var me = jQuery( ev.srcElement ).closest( '.cs-tool' ),
					id = csSidebars.getIdFromEditbar( me ),
					sb = csSidebars.find( id );

				// DELETE sidebar
				if ( me.hasClass( 'delete-sidebar' ) ) {
					csSidebars.showRemove( sb );

					return false;
				} else

				// EDIT dialog
				if ( me.hasClass( 'edit-sidebar' ) ) {
					var data = {
						id: sb.getID(),
						title: csSidebars.lang.data('title-edit') + ' ' + sb.name,
						button: csSidebars.lang.data('btn-edit')
					};
					csSidebars.showEditor( data );

					return false;
				} else

				// LOCATION popup
				if ( me.hasClass( 'where-sidebar' ) ) {
					csSidebars.showLocations( sb );
					return false;
				} else

				// TOGGLE REPLACEABLE flag
				if ( me.hasClass( 'btn-replaceable' ) ) {
					var the_bar = jQuery( sb.sb ).closest( '.widgets-holder-wrap' ),
						marker = the_bar.find( '.replace-marker' );

					if ( me.prop('checked') ) {
						if ( ! marker.length ) {
							jQuery( '<div></div>' )
								.appendTo( the_bar )
								.addClass( 'replace-marker' )
								.attr( 'data-label', me.attr( 'data-label' ) );
						}
						the_bar.addClass( 'replaceable' );
					} else {
						marker.remove();
						the_bar.removeClass( 'replaceable' );
					}
					// TODO: Make ajax call to save flag
					return true;
				}
			};

			csSidebars.right.on('click', '.cs-tool', tool_action);
			csSidebars.right.on('change', '.cs-tool', tool_action);

			return csSidebars;
		},

		/**
		 * =====================================================================
		 * Show a message to the user.
		 *
		 * TODO: Move this to the wpmUi object
		 *
		 * @since  1.0.0
		 */
		showMessage: function(message, error){
			var html, btn_close, msgclass,
				msgdiv = jQuery( '#cs-message' );

			var hide_message = function hide_message() {
				var msgdiv = jQuery('#cs-message');

				msgdiv.fadeOut(400)
				setTimeout( function() {
					msgdiv.remove();
				}, 400);
			};

			if (error) {
				msgclass = 'cs-error';
			} else {
				msgclass = 'cs-update';
			}

			if (msgdiv.length != 0) {
				clearTimeout(msgTimer);
				msgdiv.removeClass('cs-error cs-update').addClass(msgclass);
				msgdiv.text(message);
			}
			else {
				msgdiv = jQuery( '<div id="cs-message" class="cs-message"></div>' );
				btn_close = jQuery('<a href="#" class="close">&times;</a>');

				msgdiv.html(message).addClass(msgclass).hide();
				btn_close.appendTo(msgdiv).click( hide_message );
				msgdiv.insertAfter('#cs-title-options').fadeIn();
				btn_close.focus();
			}
			msgTimer = setTimeout( hide_message, 7000);
		},

		/**
		 * =====================================================================
		 * Find the specified CsSidebar object.
		 *
		 * @since  1.0.0
		 */
		find: function(id){
			return csSidebars.sidebars[id];
		},

		/**
		 * =====================================================================
		 * Create a new CsSidebar object.
		 *
		 * @since  1.0.0
		 */
		add: function(id, type){
			csSidebars.sidebars[id] = new CsSidebar(id, type);
			return csSidebars.sidebars[id];
		},

		/**
		 * =====================================================================
		 * Removes a new CsSidebar object.
		 *
		 * @since  1.6.0
		 */
		remove: function(id){
			delete csSidebars.sidebars[id];
		},

		/**
		 * =====================================================================
		 * Returns true when the specified ID is recognized as a sidebar
		 * that was created by the custom sidebars plugin.
		 *
		 * @since  1.6.0
		 */
		isCustomSidebar: function( el ) {
			var id = jQuery( el ).attr('id'),
				prefix = id.substr(0, csSidebars.sidebar_prefix.length);

			return prefix == csSidebars.sidebar_prefix;
		},

		/**
		 * =====================================================================
		 * Append the specified sidebar ID to the label and input element.
		 *
		 * @since  1.6.0
		 */
		addIdToLabel: function( $obj, id ){
			if ( true != $obj.data( 'label-done' ) ) {
				var prefix = $obj.attr('for');
				$obj.attr( 'for', prefix + id );
				$obj.find( '.has-label' ).attr( 'id', prefix + id );
				$obj.data( 'label-done', true );
			}
		},

		/**
		 * =====================================================================
		 * Returns the sidebar ID based on the sidebar DOM object.
		 *
		 * @since  1.6.0
		 * @param  jQuery $obj Any DOM object inside the Sidebar HTML structure.
		 * @return string The sidebar ID
		 */
		getIdFromEditbar: function( $obj ){
			var wrapper = $obj.closest( '.widgets-holder-wrap' ),
				sb = wrapper.find( '.widgets-sortables:first' ),
				id = sb.attr( 'id' );
			return id;
		}
	};

	jQuery(function($){
		$('#csfooter').hide();
		if ( $('#widgets-right').length > 0 ) {
			csSidebars.init();
		}
		$('.defaultsContainer').hide();

		$( '#widgets-right .widgets-sortables' ).on( "sort", function(event, ui) {
			var topx = $('#widgets-right').top;
			ui.position.top = - $('#widgets-right').css('top');
		});
	});
})(jQuery);




/*
 * http://blog.stevenlevithan.com/archives/faster-trim-javascript
 */
function trim( str ) {
	str = str.replace(/^\s\s*/, '');
	for (var i = str.length - 1; i >= 0; i--) {
		if (/\S/.test(str.charAt(i))) {
			str = str.substring(0, i + 1);
			break;
		}
	}
	return str;
}