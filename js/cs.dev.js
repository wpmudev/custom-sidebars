/**
 * CsSidebar class
 *
 * This adds new functionality to each sidebar.
 */
function CsSidebar(id, type){
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
	this.extras = jQuery('#cs-widgets-extra');

	this.widgets = '';
	this.name = trim(this.sb.siblings('.sidebar-name').text());
	this.description = trim(this.sb.find('.sidebar-description').text());

	// Add one of two editbars to each sidebar.
	if ( type == 'custom' ) {
		var editbar = this.extras.find('.cs-custom-sidebar').clone();
	} else {
		var editbar = this.extras.find('.cs-theme-sidebar').clone();
	}

	this.sb.parent().append(editbar);

	// Customize the links and label-tags.
	editbar.find('a').each(function(){
		var me = jQuery( this );
		csSidebars.addIdToA( me, id );
	});
	editbar.find('label').each(function(){
		var me = jQuery( this );
		csSidebars.addIdToLabel( me, id );
	});
}

/**
 * Delete the sidebar.
 */
CsSidebar.prototype.remove = function($){
	var htmlid = this.id.split('\\').join(''),
		id = this.id,
		ajaxdata = {
			action:		'cs-ajax',
			cs_action:	'cs-delete-sidebar',
			'delete':	htmlid,
			nonce:		$('#_delete_nonce').val()
		};

	$.post(ajaxurl, ajaxdata, function(response){
		if(response.success){
			$('#' + id).parent().slideUp('fast', function(){
				$(this).remove();
			});
		}
		$('#_delete_nonce').val(response.nonce);
		csSidebars.showMessage(response.message, ! response.success);
	});
};

/**
 * Show popup with sidebar editor.
 */
CsSidebar.prototype.showEdit = function($) {
	var htmlid = this.id.split('\\').join('');
	editbar = this.sb.siblings('.cs-toolbar');
	this.editbar = editbar.html();
	editbar.html(this.extras.find('.cs-cancel-edit-bar').html());
	CsSidebar.addIdToA(editbar.find('.cs-advanced-edit'), htmlid);
	this.widgets = this.sb.detach();
	editbar.before('<div id="' + htmlid + '" class="widgets-sortables"></div>');
	form = this.extras.find('.sidebar-form').clone();
	form.find('form').addClass('cs-edit-form');
	form.find('.sidebar_name').val(this.name).attr('id', 'edit_sidebar_name');
	form.find('.sidebar_description').val(this.description).attr('id', 'edit_sidebar_description');
	thiscs = this;
	form.find('.cs-create-sidebar')
		.removeClass('cs-create-sidebar')
		.addClass('cs-edit-sidebar')
		.val($('#cs-save').text())
		.attr('id', 'edit_sidebar_submit')
		.on('click', function(){
			thiscs.edit($);
			return false;
		});
	editbar.siblings('#' + this.id).prepend(form);
	return false;
};

/**
 * Close popup with sidebar editor.
 */
CsSidebar.prototype.cancelEdit = function($){
	editbar = this.sb.siblings('.cs-toolbar');
	editbar.html(this.editbar);
	editbar.siblings('#' + this.id).remove();
	editbar.before(this.widgets);

}

/**
 * Save changes to sidebar.
 */
CsSidebar.prototype.edit = function($){
	var $id = '#' + this.id,
		el = jQuery( $id ),
		htmlid = this.id.split('\\').join(''),
		id = this.id,
		ajaxdata = {
			action:			'cs-ajax',
			cs_action:		'cs-edit-sidebar',
			'sidebar_name':	el.find('#edit_sidebar_name').val(),
			'sidebar_description':	el.find('#edit_sidebar_description').val(),
			'cs_id':		htmlid,
			nonce:			$('#_edit_nonce').val()
		}

   $.post(ajaxurl, ajaxdata, function(response){
		if(response.success) {
			sidebar = csSidebars.find(htmlid);
			editbar = $($id).siblings('.cs-toolbar');
			$($id).remove();
			editbar.before(sidebar.widgets);
			editbar.html(sidebar.editbar);
			$($id).find('.description').text(response.description)
			$($id).siblings('.sidebar-name').find('h3').html(getSidebarTitle(response.name));
		}
		$('#_edit_nonce').val(response.nonce);
		csSidebars.showMessage(response.message, ! response.success);
   });
};

/**
 * Show popup to assign sidebar to default categories.
 */
CsSidebar.prototype.showWhere = function(){

};

CsSidebar.prototype.where = function(){

};


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
		sidebars: [],

		/**
		 * This is the same prefix as defined in class-custom-sidebars.php
		 */
		sidebar_prefix: 'cs-',


		init: function(){
			csSidebars.scrollSetUp()
				.addCSControls()
				.showCreateSidebar()
				.createCsSidebars()
				.setEditbarsUp()
				.setupColumns();
		},

		scrollSetUp : function(){
			$('#widgets-right').addClass('overview').wrap('<div class="viewport" />');
			$('.viewport').height($(window).height() - 60);
			$('.widget-liquid-right').height($(window).height()).prepend('<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>').tinyscrollbar();

			$(window).resize(function() {
				$('.widget-liquid-right').height($(window).height());
				$('.viewport').height($(window).height() - 60);
				$('.widget-liquid-right').tinyscrollbar_update('relative');
			});
			$('#widgets-right').resize(function(){
				$('.widget-liquid-right').tinyscrollbar_update('relative');
			});

			$('.widget-liquid-right').click(function(){
				setTimeout("csSidebars.updateScroll()",400);
			});
			$('.widget-liquid-right').hover(function(){
				$('.scrollbar').fadeIn();
			}, function(){
				$('.scrollbar').fadeOut();
			});
			return csSidebars;
		},

		addCSControls: function(){
			$('#cs-title-options').detach().prependTo('#widgets-right').show();
			return csSidebars;
		},

		/**
		 * Arrange sidebars in left/right columns.
		 * Left column: Custom sidebars. Right column: Theme sidebars.
		 * @since  1.6
		 */
		setupColumns: function() {
			var extras = jQuery( '#cs-widgets-extra' ),
				container = jQuery( '#widgets-right'),
				col1 = container.find( '.sidebars-column-1' ),
				col2 = container.find( '.sidebars-column-2' ),
				title1 = extras.find( '.cs-title-col1' ),
				title2 = extras.find( '.cs-title-col2' ),
				sidebars = container.find( '.widgets-holder-wrap' );

			if ( ! col2.length ) {
				col2 = jQuery( '<div class="sidebars-column-2"></div>' );
				col2.appendTo( container );
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

		showCreateSidebar: function(){
			$('.btn-create-sidebar').click(function(){
				if($('#new-sidebar-holder').length == 0){ //If there is no form displayed

					var sbname,
						holder = $('#cs-new-sidebar').clone(true, true)
						.attr('id', 'new-sidebar-holder')
						.hide()
						.insertAfter('#cs-title-options');

					holder.find('._widgets-sortables')
						.addClass('widgets-sortables')
						.removeClass('_widgets-sortables')
						.attr('id', 'new-sidebar');
					holder.find('.sidebar-form')
						.attr('id', 'new-sidebar-form');
					holder.find('.sidebar_name')
						.attr('id', 'sidebar_name');
					holder.find('.sidebar_description')
						.attr('id', 'sidebar_description');
					holder.find('.cs-create-sidebar')
						.attr('id', 'cs-create-sidebar');

					holder.slideDown();
					sbname = holder.children(".sidebar-name");
					sbname.click(function(){
						var h = $(this).siblings(".widgets-sortables"),
							g = $(this).parent();

						if ( ! g.hasClass("closed") ) {
							h.sortable("disable");g.addClass("closed");
						} else {
							g.removeClass("closed");
							h.sortable("enable").sortable("refresh");
						}
					});


					csSidebars.setCreateSidebar();
				}
				else {
					$('#cs-options').find('.ajax-feedback').hide();
				}

				return false;
			});
			return csSidebars;
		},

		setCreateSidebar: function(){
			$('#cs-create-sidebar').click(function(){
				var ajaxdata = {
					action: 'cs-ajax',
					cs_action: 'cs-create-sidebar',
					nonce: $('#_create_nonce').val(),
					sidebar_name: $('#sidebar_name').val(),
					sidebar_description: $('#sidebar_description').val()
				};
				$('#new-sidebar-form').find('.ajax-feedback').show();
				$.post(ajaxurl, ajaxdata, function(response){
					if(response.success){
						var holder = $('#new-sidebar-holder'),
							column = $('#widgets-right').find('.sidebars-column-1'),
							content
						;
						holder.removeAttr('id')
							.find('.sidebar-name h3').html(getSidebarTitle(response.name));
						holder.find('#new-sidebar').attr('id', response.id) ;


						if(column.length) {
							holder.detach().prependTo(column);
						}

						content = $('#' + response.id).html('<p class="sidebar-description description">' + response.description + '</p>');

						csSidebars.add(content.attr('id'));
					}

					$('#_create_nonce').val(response.nonce);
					csSidebars.showMessage(response.message, ! response.success);
					$('#new-sidebar-form').find('.ajax-feedback').hide();

				}, 'json');

				return false;
			});
			return csSidebars;
		},

		updateScroll: function(){
			$('.widget-liquid-right').tinyscrollbar_update('relative');
		},

		createCsSidebars: function(){
			$('#widgets-right').find('.widgets-sortables').each(function() {
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
		 * Hook up all the functions in the sidebar toolbar.
		 * Toolbar is in the bottom of each sidebar.
		 */
		setEditbarsUp: function(){
			// DELETE sidebar
			$('#widgets-right').on('click', 'a.delete-sidebar', function(){
				var sbname = trim($(this).parent().siblings('.sidebar-name').text());
				if(confirm($('#cs-confirm-delete').text() + ' ' + sbname)){
					var sb = csSidebars.find($(this).parent().siblings('.widgets-sortables').attr('id')).remove($);
				}
				return false;
			});

			// EDIT dialog
			$('#widgets-right').on('click', 'a.edit-sidebar', function(){
				id = getIdFromEditbar($(this));
				csSidebars.find(id).showEdit($);
				return false;
			});

			// LOCATION popup
			$('#widgets-right').on('click', 'a.where-sidebar', function(){
				// Popup is opened by the "thickbox" plugin...
			});

			// CANCEL EDIT dialog
			$('#widgets-right').on('click', 'a.cs-cancel-edit', function(){
				id = getIdFromEditbar($(this));
				csSidebars.find(id).cancelEdit($);
				$(this).parent().html(this.editbar);
				this.editbar ='';
				return false;
			});

			return csSidebars;
		},

		/**
		 * Show a message to the user.
		 */
		showMessage: function(message, error){
			var html, btn_close,
				msgclass = 'cs-update',
				msgdiv = jQuery( '#cs-message' );

			if (error) {
				msgclass = 'cs-error';
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
				btn_close.appendTo(msgdiv).click( function() { csSidebars.hideMessage() } );
				msgdiv.insertAfter('#cs-title-options').fadeIn();
				btn_close.focus();
			}
			msgTimer = setTimeout('csSidebars.hideMessage()', 7000);
		},

		/**
		 * Hide the currently visible message box.
		 */
		hideMessage: function() {
			var msgdiv = jQuery('#cs-message');

			msgdiv.fadeOut(400)
			setTimeout( function() {
				msgdiv.remove();
			}, 400);
		},

		/**
		 * Find the specified CsSidebar object.
		 */
		find: function(id){
			return csSidebars.sidebars[id];
		},

		/**
		 * Create a new CsSidebar object.
		 */
		add: function(id, type){
			csSidebars.sidebars[id] = new CsSidebar(id, type);
			return csSidebars.sidebars[id];
		},

		/**
		 * Append the specified sidebar ID to the link URL.
		 */
		addIdToA: function( $obj, id ){
			if ( true != $obj.data( 'href-done' ) ) {
				var href = $obj.attr('href');
				$obj.attr( 'href', href + id );
				$obj.data( 'href-done', true );
			}
		},

		/**
		 * Append the specified sidebar ID to the label and input element.
		 */
		addIdToLabel: function( $obj, id ){
			if ( true != $obj.data( 'label-done' ) ) {
				var prefix = $obj.attr('for');
				$obj.attr( 'for', prefix + id );
				$obj.find( '.has-label' ).attr( 'id', prefix + id );
				$obj.data( 'label-done', true );
			}
		}
	};

	jQuery(function($){
		$('#csfooter').hide();
		if($('#widgets-right').length > 0) {
			csSidebars.init();
		}
		$('.defaultsContainer').hide();

		$('#widgets-right .widgets-sortables').on("sort", function(event, ui){
			var topx = $('#widgets-right').top;
			ui.position.top = - $('#widgets-right').css('top');
		});

		$('#widgets-right .widget').on("sortstart", function(event, ui){

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

function getIdFromEditbar( $ob ){
	return $ob.parent().siblings( '.widgets-sortables' ).attr( 'id' );
}

function getSidebarTitle( title ) {
	return title + '<img src="images/wpspin_light.gif" class="ajax-feedback" title="" alt="" />';
}
