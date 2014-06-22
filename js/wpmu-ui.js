/**
 * WPMU Dev UI library
 * (Philipp Stracker for WPMU Dev)
 *
 * This library provides a Javascript API via the global wpmUi object.
 *
 * @version  1.0.0
 * @author   Philipp Stracker for WPMU Dev
 * @link     http://appendto.com/2010/10/how-good-c-habits-can-encourage-bad-javascript-habits-part-1/
 * @requires jQuery
 */

(function( wpmUi ) {

	/**
	 * The body element.
	 *
	 * @type   jQuery object
	 * @since  1.0.0
	 * @private
	 */
	var _body = null;

	/**
	 * Modal overlay, created by this object.
	 *
	 * @type   jQuery object
	 * @since  1.0.0
	 * @private
	 */
	var _modal_overlay = null;


	// ==========
	// == Public UI functions ==================================================
	// ==========


	/**
	 * Opens a new popup layer.
	 *
	 * @since  1.0.0
	 * @return wpmUiWindow A new popup window.
	 */
	wpmUi.popup = function popup() {
		return new wpmUiWindow();
	};

	/**
	 * Creates a new formdata object.
	 * With this object we can load or submit data via ajax.
	 *
	 * @since  1.0.0
	 * @param  string ajaxurl URL to the ajax handler.
	 * @param  string default_action The action to use when an ajax function
	 *                does not specify an action.
	 * @return wpmUiAjaxData A new formdata object.
	 */
	wpmUi.ajax = function ajax( ajaxurl, default_action ) {
		return new wpmUiAjaxData( ajaxurl, default_action );
	}


	// ==========
	// == Private helper functions =============================================
	// ==========


	/**
	 * Initialize the object
	 *
	 * @since  1.0.0
	 * @private
	 */
	function _init() {
		_body = jQuery( 'body' );
	}

	/**
	 * Shows a modal background layer
	 *
	 * @since  1.0.0
	 * @private
	 */
	function _make_modal() {
		if ( null === _modal_overlay ) {
			_modal_overlay = jQuery( '<div></div>' )
				.addClass( 'wpmui-overlay' )
				.appendTo( _body );
		}
		_body.addClass( 'wpmui-has-overlay' );
	}

	/**
	 * Closes the modal background layer again.
	 *
	 * @since  1.0.0
	 * @private
	 */
	function _close_modal() {
		_body.removeClass( 'wpmui-has-overlay' );
	}

	// Initialize the object.
	jQuery(function() {
		_init();
	});










	// ==========
	// == UI Object: WINDOW ====================================================
	// ==========


	/**
	 * Popup window.
	 *
	 * @type   wpmUiWindow
	 * @since  1.0.0
	 */
	var wpmUiWindow = function() {

		/**
		 * Backreference to the wpmUiWindow object.
		 *
		 * @since 1.0.0
		 * @private
		 */
		var _me = this;


		/**
		 * Stores the state of the window.
		 *
		 * @since 1.0.0
		 * @private
		 */
		var _visible = false;

		/**
		 * Defines if a modal background should be visible.
		 *
		 * @since 1.0.0
		 * @private
		 */
		var _modal = false;

		/**
		 * Size of the window.
		 *
		 * @since 1.0.0
		 * @private
		 */
		var _width = 740;

		/**
		 * Size of the window.
		 *
		 * @since 1.0.0
		 * @private
		 */
		var _height = 400;

		/**
		 * Title of the window.
		 *
		 * @since 1.0.0
		 * @private
		 */
		var _title = 'Window';

		/**
		 * Content of the window. Either a jQuery selector/object or HTML code.
		 *
		 * @since 1.0.0
		 * @private
		 */
		var _content = '';

		/**
		 * Is set to true when new content is assigned to the window.
		 *
		 * @since 1.0.0
		 * @private
		 */
		var _content_changed = false;


		/**
		 * Called after the window is made visible.
		 *
		 * @type  Callback function.
		 * @since 1.0.0
		 * @private
		 */
		var _onshow = null;

		/**
		 * Called after the window was hidden.
		 *
		 * @type  Callback function.
		 * @since 1.0.0
		 * @private
		 */
		var _onhide = null;

		/**
		 * Called after the window was hidden + destroyed.
		 *
		 * @type  Callback function.
		 * @since 1.0.0
		 * @private
		 */
		var _onclose = null;


		/**
		 * The popup window element.
		 *
		 * @type  jQuery object.
		 * @since 1.0.0
		 * @private
		 */
		var _wnd = null;

		/**
		 * Title bar inside the window.
		 *
		 * @type  jQuery object.
		 * @since 1.0.0
		 * @private
		 */
		var _el_title = null;

		/**
		 * Close button inside the title bar.
		 *
		 * @type  jQuery object.
		 * @since 1.0.0
		 * @private
		 */
		var _btn_close = null;

		/**
		 * Content section of the window.
		 *
		 * @type  jQuery object.
		 * @since 1.0.0
		 * @private
		 */
		var _el_content = null;


		// ==============================
		// == Public functions ==========


		/**
		 * Sets the modal property.
		 *
		 * @since  1.0.0
		 */
		this.modal = function modal( state ) {
			_modal = ( state ? true : false );

			_update_window()
			return this;
		};

		/**
		 * Sets the window size.
		 *
		 * @since  1.0.0
		 */
		this.size = function size( width, height ) {
			var new_width = Math.abs( parseFloat( width ) ),
				new_height = Math.abs( parseFloat( height ) );

			if ( ! isNaN( new_width ) ) { _width = new_width; }
			if ( ! isNaN( new_height ) ) { _height = new_height; }

			_update_window();
			return this;
		};

		/**
		 * Sets the window title.
		 *
		 * @since  1.0.0
		 */
		this.title = function title( title ) {
			_title = title;

			_update_window();
			return this;
		};

		/**
		 * Sets the window content.
		 *
		 * @since  1.0.0
		 */
		this.content = function content( data ) {
			_content = data;
			_content_changed = true;

			_update_window();
			return this;
		};

		/**
		 * Define a callback that is executed after popup is made visible.
		 *
		 * @since  1.0.0
		 */
		this.onshow = function onshow( callback ) {
			_onshow = callback;
			return this;
		}

		/**
		 * Define a callback that is executed after popup is hidden.
		 *
		 * @since  1.0.0
		 */
		this.onhide = function onhide( callback ) {
			_onhide = callback;
			return this;
		}

		/**
		 * Define a callback that is executed after popup was destroyed.
		 *
		 * @since  1.0.0
		 */
		this.onclose = function onclose( callback ) {
			_onclose = callback;
			return this;
		}

		/**
		 * Add a loading-overlay to the popup or remove the overlay again.
		 *
		 * @since  1.0.0
		 * @param  bool state True will add the overlay, false removes it.
		 */
		this.loading = function loading( state ) {
			if ( state ) {
				_wnd.addClass( 'wpmui-loading' );
			} else {
				_wnd.removeClass( 'wpmui-loading' );
			}
			return this;
		}

		/**
		 * Show the popup window.
		 *
		 * @since  1.0.0
		 */
		this.show = function show() {
			_visible = true;

			_update_window();

			if ( typeof _onshow == 'function' ) {
				_onshow.apply( _me, [ _me.$() ] )
			}
			return this;
		};

		/**
		 * Hide the popup window.
		 *
		 * @since  1.0.0
		 */
		this.hide = function hide() {
			_visible = false;

			_update_window();

			if ( typeof _onhide == 'function' ) {
				_onhide.apply( _me, [ _me.$() ] )
			}
			return this;
		};

		/**
		 * Completely removes the popup window.
		 *
		 * @since  1.0.0
		 */
		this.close = function close() {
			this.hide();

			if ( typeof _onclose == 'function' ) {
				_onclose.apply( _me, [ _me.$() ] )
			}

			_wnd.remove();
			_wnd = null;
		}

		/**
		 * Returns the jQuery object of the window
		 *
		 * @since 1.0.0
		 */
		this.$ = function $() {
			return _wnd;
		}


		// ==============================
		// == Private functions =========


		/**
		 * Create the DOM elements for the window.
		 *
		 * @since  1.0.0
		 * @private
		 */
		function _init() {
			// Create the DOM elements.
			_wnd = jQuery( '<div class="wpmui-wnd"></div>' );
			_el_title = jQuery( '<div class="wpmui-wnd-title"><span class="the-title"></span></div>' );
			_btn_close = jQuery( '<a href="#" class="wpmui-wnd-close"><i class="dashicons dashicons-no-alt"></i></a>' );
			_el_content = jQuery( '<div class="wpmui-wnd-content"></div>' );

			// Attach the window to the current page.
			_el_title.appendTo( _wnd );
			_el_content.appendTo( _wnd );
			_btn_close.appendTo( _el_title );
			_wnd.appendTo( _body ).hide();

			// Add event handlers.
			_wnd.on( 'click', '.wpmui-wnd-close', function() { _me.close() } );
			jQuery( window ).resize( _check_size );

			// Refresh the window layout.
			_visible = false;
			_update_window();
		}

		/**
		 * Updates the size and position of the window.
		 *
		 * @since  1.0.0
		 * @private
		 */
		function _update_window( width, height ) {
			width = width || _width;
			height = height || _height;

			var styles = {
				'width': width,
				'height': height,
				'margin-left': -1 * (width / 2),
				'margin-top': -1 * (height / 2)
			};

			// Window title.
			_el_title.find( '.the-title' ).text( _title );

			// Display a copy of the specified content.
			if ( _content_changed ) {
				// Remove the current button bar.
				_wnd.find( '.buttons' ).remove();
				_wnd.addClass( 'no-buttons' );

				// Update the content.
				if ( _content instanceof jQuery ) {
					_el_content.html( _content.html() );
				} else {
					_el_content.html( jQuery( _content ).html() );
				}

				// Move the buttons out of the content area.
				var buttons = _el_content.find( '.buttons' );
				if ( buttons.length ) {
					buttons.appendTo( _wnd );
					_wnd.removeClass( 'no-buttons' );
				}

				_content_changed = false;
			}

			// Size and position.
			if ( _wnd.is( ':visible' ) ) {
				_wnd.animate(styles, 200);
			} else {
				_wnd.css(styles);
			}

			// Show or hide the window and modal background.
			if ( _visible ) {
				_wnd.show();
				_modal && _make_modal();
			} else {
				_wnd.hide();
				_close_modal();
			}
		}

		/**
		 * Makes sure that the popup window is not bigger than the viewport.
		 *
		 * @since  1.0.0
		 */
		function _check_size() {
			var me = jQuery( this ), // this is jQuery( window )
				window_width = me.innerWidth(),
				window_height = me.innerHeight(),
				real_width = _width,
				real_height = _height;

			if ( window_width < _width ) {
				real_width = window_width;
			}
			if ( window_height < _height ) {
				real_height = window_height;
			}
			_update_window( real_width, real_height );
		}

		// Initialize the popup window.
		_me = this;
		_init();

	}; /* ** End: wpmUiWindow ** */










	// ==
	// == UI Object: FORMDATA ==================================================
	// ==


	/**
	 * Form Data object that is used to load or submit data via ajax.
	 *
	 * @type   wpmUiAjaxData
	 * @since  1.0.0
	 */
	var wpmUiAjaxData = function( _ajaxurl, _default_action ) {

		/**
		 * Backreference to the wpmUiAjaxData object.
		 *
		 * @since 1.0.0
		 * @private
		 */
		var _me = this;

		/**
		 * An invisible iframe with name "wpmui_void", created by this object.
		 *
		 * @type   jQuery object
		 * @since  1.0.0
		 * @private
		 */
		var _void_frame = null;

		/**
		 * Data that is sent to the server.
		 *
		 * @type   Object
		 * @since  1.0.0
		 * @private
		 */
		var _data = {};

		/**
		 * Progress handler during upload/download.
		 * Signature function( progress )
		 *     - progress .. Percentage complete or "-1" for "unknown"
		 *
		 * @type  Callback function.
		 * @since 1.0.0
		 * @private
		 */
		var _onprogress = null;

		/**
		 * Receives the server response after ajax call is finished.
		 * Signature: function( response, okay, xhr )
		 *     - response .. Data received from the server.
		 *     - okay .. bool; false means an error occured.
		 *     - xhr .. XMLHttpRequest object.
		 *
		 * @type  Callback function.
		 * @since 1.0.0
		 * @private
		 */
		var _ondone = null;

		/**
		 * Feature detection: HTML5 upload/download progress events.
		 *
		 * @type  bool
		 * @since 1.0.0
		 * @private
		 */
		var _support_progress = false;

		/**
		 * Feature detection: HTML5 file API.
		 *
		 * @type  bool
		 * @since 1.0.0
		 * @private
		 */
		var _support_file_api = false;

		/**
		 * Feature detection: HTML5 FormData object.
		 *
		 * @type  bool
		 * @since 1.0.0
		 * @private
		 */
		var _support_form_data = false;


		// ==============================
		// == Public functions ==========


		/**
		 * Define the data that is sent to the server.
		 *
		 * @since  1.0.0
		 * @param  Object mixed Data that is sent to the server. Either:
		 *                - Normal javascript object interpreted as key/value pairs.
		 *                - A jQuery object of the whole form element
		 *                - An URL-encoded string ("key=val&key2=val2")
		 */
		this.data = function data( obj ) {
			_data = obj;
			return this;
		};

		/**
		 * Define the upload/download progress callback.
		 *
		 * @since  1.0.0
		 * @param  function callback Progress handler.
		 */
		this.onprogress = function onprogress( callback ) {
			_onprogress = callback;
			return this;
		};

		/**
		 * Callback that receives the server response of the ajax request.
		 *
		 * @since  1.0.0
		 * @param  function callback
		 */
		this.ondone = function ondone( callback ) {
			_ondone = callback;
			return this;
		};

		/**
		 * Reset all configurations.
		 *
		 * @since  1.0.0
		 */
		this.reset = function reset() {
			_data = {};
			_onprogress = null;
			_ondone = null;
			return this;
		};

		/**
		 * Submit the specified data to the ajaxurl and pass the response to a
		 * callback function. Server response can be any string.
		 *
		 * @since  1.0.0
		 * @param  action string The ajax action to execute.
		 */
		this.load_text = function load_text( action ) {
			action = action || _default_action;
			_load( action, 'text' );

			return this;
		};

		/**
		 * Submit the specified data to the ajaxurl and pass the response to a
		 * callback function. Server response must be a valid JSON string!
		 *
		 * @since  1.0.0
		 * @param  action string The ajax action to execute.
		 */
		this.load_json = function load_json( action ) {
			action = action || _default_action;
			_load( action, 'json' );

			return this;
		};

		/**
		 * Submit the specified data to the ajaxurl and let the browser process
		 * the response.
		 * Use this function for example when the server returns a file that
		 * should be downloaded.
		 *
		 * @since  1.0.0
		 * @param  action string The ajax action to execute.
		 */
		this.load_http = function load_http( action ) {
			action = action || _default_action;
			_form_submit( action );

			return this;
		};


		// ==============================
		// == Private functions =========


		/**
		 * Initialize the formdata object
		 *
		 * @since  1.0.0
		 * @private
		 */
		function _init() {
			// Initialize missing Ajax-URL: Use WordPress ajaxurl if possible.
			if ( ! _ajaxurl && typeof window.ajaxurl == 'string') {
				_ajaxurl = window.ajaxurl;
			}

			// Initialize an invisible iframe for file downloads.
			_void_frame = _body.find( '#wpmui_void' );

			if ( ! _void_frame.length ) {
				/**
				 * Create the invisible iframe.
				 * Usage: <form target="wpmui_void">...</form>
				 */
				_void_frame = jQuery('<iframe></iframe>')
					.attr( 'name', 'wpmui_void' )
					.attr( 'id', 'wpmui_void' )
					.css({
						'width': 1,
						'height': 1,
						'display': 'none',
						'visibility': 'hidden',
						'position': 'absolute',
						'left': -1000,
						'top': -1000
					})
					.hide()
					.appendTo( _body );
			}

			// Find out what HTML5 feature we can use.
			_what_is_supported();

			// Reset all configurations.
			_me.reset();
		}

		/**
		 * Feature detection
		 *
		 * @since  1.0.0
		 * @private
		 * @return bool
		 */
		function _what_is_supported() {
			var inp = document.createElement( 'INPUT' );
			var xhr = new XMLHttpRequest();

			// HTML 5 files API
			inp.type = 'file';
			_support_file_api = 'files' in inp;

			// HTML5 ajax upload "progress" events
			_support_progress = !! (xhr && ( 'upload' in xhr ) && ( 'onprogress' in xhr.upload ));

			// HTML5 FormData object
			_support_form_data = !! window.FormData;
		};

		/**
		 * Creates the XMLHttpReqest object used for the jQuery ajax calls.
		 *
		 * @since  1.0.0
		 * @private
		 * @return XMLHttpRequest
		 */
		function _create_xhr() {
			var xhr = new window.XMLHttpRequest();

			if ( _support_progress ) {
				// Upload progress
				xhr.upload.addEventListener( "progress", function( evt ) {
					if ( evt.lengthComputable ) {
						var percentComplete = evt.loaded / evt.total;
						_call_progress( percentComplete );
					} else {
						_call_progress( -1 );
					}
				}, false );

				// Download progress
				xhr.addEventListener( "progress", function( evt ) {
					if ( evt.lengthComputable ) {
						var percentComplete = evt.loaded / evt.total;
						_call_progress( percentComplete );
					} else {
						_call_progress( -1 );
					}
				}, false );
			};

			return xhr;
		}

		/**
		 * Calls the "onprogress" callback
		 *
		 * @since  1.0.0
		 * @private
		 * @param  float value Percentage complete / -1 for "unknown"
		 */
		function _call_progress( value ) {
			if ( _support_progress && typeof _onprogress == 'function' ) {
				_onprogress( value );
			}
		}

		/**
		 * Calls the "onprogress" callback
		 *
		 * @since  1.0.0
		 * @private
		 * @param  response mixed The parsed server response.
		 * @param  okay bool False means there was an error.
		 * @param  xhr XMLHttpRequest
		 */
		function _call_done( response, okay, xhr ) {
			_call_progress( 100 );
			if ( typeof _ondone == 'function' ) {
				_ondone( response, okay, xhr );
			}
		}

		/**
		 * Returns data object containing the data to submit.
		 * The data object is either a plain javascript object or a FormData
		 * object; this depends on the parameter "use_formdata" and browser-
		 * support for FormData.
		 *
		 * @since  1.0.0
		 * @param  string action
		 * @param  boolean use_formdata If set to true then we return FormData
		 *                when the browser supports it. If support is missing or
		 *                use_formdata is not true then the response is an object.
		 * @return Object or FormData
		 */
		function _get_data( action, use_formdata ) {
			var data = null;
			use_formdata = use_formdata && _support_form_data;

			if ( _data instanceof jQuery ) {

				// ===== CONVERT <form> to data object.

				if ( use_formdata ) {
					data = new FormData( _data[0] );
				} else {
					data = {};

					// Convert a jQuery object to data object.
					var temp = _data.serializeArray();
					for ( var ind in temp ) {
						var name = temp[ind].name,
							val = temp[ind].value;
						if ( undefined !== data[name]  ) {
							if ( 'object' != typeof data[name] ) {
								data[name] = [ data[name] ];
							}
							data[name].push( val );
						} else {
							data[name] = val;
						}
					}
					// Add file fields
					_data.find( 'input[type=file]' ).each( function() {
						var me = jQuery( this ),
							name = me.attr( 'name' ),
							inp = me.clone( true )[0];
						data[':files'] = data[':files'] || {};
						data[':files'][name] = inp;
					});
				}
			} else if ( typeof _data == 'string' ) {

				// ===== PARSE STRING to data object.

				temp = _data.split( '&' ).map( function (kv) {
					return kv.split( '=', 2 );
				});

				data = ( use_formdata ? new FormData() : {} );
				for ( var ind in temp ) {
					var name = decodeURI( temp[ind][0] ),
						val = decodeURI( temp[ind][1] );

					if ( use_formdata ) {
						data.append( name, val );
					} else {
						if ( undefined !== data[name]  ) {
							if ( 'object' != typeof data[name] ) {
								data[name] = [ data[name] ];
							}
							data[name].push( val );
						} else {
							data[name] = val;
						}
					}
				}
			} else if ( typeof _data == 'object' ) {

				// ===== USE OBJECT to populate data object.

				if ( use_formdata ) {
					data = new FormData();
					for ( var name in _data ) {
						if ( _data.hasOwnProperty( name ) ) {
							data.append( name, _data[name] );
						}
					}
				} else {
					data = jQuery.extend( {}, _data );
				}
			}

			if ( data instanceof FormData ) {
				data.append('action', action);
			} else {
				data.action = action;
			}

			return data;
		}

		/**
		 * Submit the data.
		 *
		 * @since  1.0.0
		 * @param  string action The ajax action to execute.
		 */
		function _load( action, type ) {
			var data = _get_data( action, true ),
				ajax_args = {},
				response = null,
				okay = false;

			if ( type != 'json' ) { type = 'text'; }

			_call_progress( -1 );

			ajax_args = {
				url: ajaxurl,
				type: 'POST',
				dataType: type,
				data: data,
				xhr: _create_xhr,
				success: function( resp, status, xhr ) {
					okay = true;
					response = resp;
				},
				error: function( xhr, status, error ) {
					okay = false;
					response = error;
				},
				complete: function( xhr, status ) {
					if ( typeof response == 'object' && 'ERR' == response.status ) {
						okay = false;
					}
					_call_done( response, okay, xhr );
				}
			};

			if ( data instanceof FormData ) {
				ajax_args.processData = false;  // tell jQuery not to process the data
				ajax_args.contentType = false;  // tell jQuery not to set contentType
			}

			jQuery.ajax(ajax_args);
		};

		/**
		 * Send data via a normal form submit targeted at the invisible iframe.
		 *
		 * @since  1.0.0
		 * @param  string action The ajax action to execute.
		 */
		function _form_submit( action ) {
			var data = _get_data( action, false ),
				form = jQuery( '<form></form>' );

			// Append all data fields to the form.
			for ( var name in data ) {
				if ( data.hasOwnProperty( name ) ) {
					if ( name == ':files' ) {
						for ( var file in data[name] ) {
							var inp = data[name][file];
							form.append( inp );
						}
					} else {
						jQuery('<input type="hidden" />')
							.attr( 'name', name )
							.attr( 'value', data[name] )
							.appendTo( form );
					}
				}
			}

			// Set correct form properties.
			form.attr( 'action', ajaxurl )
				.attr( 'method', 'POST' )
				.attr( 'enctype', 'multipart/form-data' )
				.attr( 'target', 'wpmui_void' );

			// Submit the form.
			form.submit();
		}


		// Initialize the formdata object
		_me = this;
		_init();

	}; /* ** End: wpmUiAjaxData ** */

}( window.wpmUi = window.wpmUi || {} ));

