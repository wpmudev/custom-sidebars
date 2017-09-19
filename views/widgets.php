<?php
/**
 * Updates the default widgets page of the admin area.
 * There are some HTML to be added for having all the functionality, so we
 * include it at the begining of the page, and it's placed later via js.
 */
?>

<div id="cs-widgets-extra">

	<?php /*
	============================================================================
	===== WIDGET head
	============================================================================
	*/ ?>
	<div id="cs-title-options">
		<h2><?php _e( 'Sidebars', 'custom-sidebars' ); ?></h2>
		<div id="cs-options" class="csb cs-options">
			<button type="button" class="button button-primary cs-action btn-create-sidebar">
				<i class="dashicons dashicons-plus-alt"></i>
				<?php _e( 'Create a new sidebar', 'custom-sidebars' ); ?>
			</button>
			<?php
			/**
			 * Show additional functions in the widget header.
			 */
			do_action( 'cs_widget_header' );
			?>
		</div>
	</div>


	<?php /*
	============================================================================
	===== LANGUAGE
	============================================================================
	*/ ?>
	<script>
	csSidebarsData = {
		'title_edit': "<?php _e( 'Edit [Sidebar]', 'custom-sidebars' ); ?>",
		'title_new': "<?php _e( 'New Custom Sidebar', 'custom-sidebars' ); ?>",
		'btn_edit': "<?php _e( 'Save Changes', 'custom-sidebars' ); ?>",
		'btn_new': "<?php _e( 'Create Sidebar', 'custom-sidebars' ); ?>",
		'title_delete': "<?php _e( 'Delete Sidebar', 'custom-sidebars' ); ?>",
		'title_location': "<?php _e( 'Define where you want this sidebar to appear.', 'custom-sidebars' ); ?>",
		'title_export': "<?php _e( 'Import / Export Sidebars', 'custom-sidebars' ); ?>",
		'custom_sidebars': "<?php _e( 'Custom Sidebars', 'custom-sidebars' ); ?>",
		'theme_sidebars': "<?php _e( 'Theme Sidebars', 'custom-sidebars' ); ?>",
		'ajax_error': "<?php _e( 'Couldn\'t load data from WordPress...', 'custom-sidebars' ); ?>",
		'lbl_replaceable': "<?php _e( 'This sidebar can be replaced on certain pages', 'custom-sidebars' ); ?>",
		'replace_tip': "<?php _e( 'Activate this option to replace the sidebar with one of your custom sidebars.', 'custom-sidebars' ); ?>",
		'filter': "<?php _e( 'Filter...', 'custom-sidebars' ); ?>",
		'replaceable': <?php echo json_encode( (object) CustomSidebars::get_options( 'modifiable' ) ); ?>,
		'_wpnonce_get': "<?php echo esc_attr( wp_create_nonce( 'custom-sidebars-get' ) ); ?>"
	};
	</script>


	<?php /*
	============================================================================
	===== TOOLBAR for custom sidebars
	============================================================================
	*/ ?>
	<div class="cs-custom-sidebar cs-toolbar">
		<a
			class="cs-tool delete-sidebar"
			data-action="delete"
			href="#"
			title="<?php _e( 'Delete this sidebar.', 'custom-sidebars' ); ?>"
			>
			<i class="dashicons dashicons-trash"></i>
		</a>
		<span class="cs-separator">|</span>
		<a
			class="cs-tool"
			data-action="edit"
			href="#"
			title="<?php _e( 'Edit this sidebar.', 'custom-sidebars' ); ?>"
			>
			<?php _e( 'Edit', 'custom-sidebars' ); ?>
		</a>
		<span class="cs-separator">|</span>
		<a
			class="cs-tool"
			data-action="location"
			href="#"
			title="<?php _e( 'Where do you want to show the sidebar?', 'custom-sidebars' ); ?>"
			>
			<?php _e( 'Sidebar Location', 'custom-sidebars' ); ?>
		</a>
		<span class="cs-separator">|</span>
	</div>


	<?php /*
	============================================================================
	===== TOOLBAR for theme sidebars
	============================================================================
	*/ ?>
	<div class="cs-theme-sidebar cs-toolbar">
		<label
			for="cs-replaceable"
			class="cs-tool btn-replaceable"
			data-action="replaceable"
			data-on="<?php _e( 'This sidebar can be replaced on certain pages', 'custom-sidebars' ); ?>"
			data-off="<?php _e( 'This sidebar will always be same on all pages', 'custom-sidebars' ); ?>"
			>
			<span class="icon"></span>
			<input
				type="checkbox"
				id=""
				class="has-label chk-replaceable"
				/>
			<span class="is-label">
				<?php _e( 'Allow this sidebar to be replaced', 'custom-sidebars' ); ?>
			</span>
		</label>
		<span class="cs-separator">|</span>
		<span class="">
			<a
				class="cs-tool"
				data-action="location"
				href="#"
				title="<?php _e( 'Where do you want to show the sidebar?', 'custom-sidebars' ); ?>"
				>
				<?php _e( 'Sidebar Location', 'custom-sidebars' ); ?>
			</a>
			<span class="cs-separator">|</span>
		</span>
	</div>


	<?php /*
	============================================================================
	===== DELETE SIDEBAR confirmation
	============================================================================
	*/ ?>
	<div class="cs-delete">
	<?php include CSB_VIEWS_DIR . 'widgets-delete.php'; ?>
	</div>


	<?php /*
	============================================================================
	===== ADD/EDIT SIDEBAR
	============================================================================
	*/ ?>
	<div class="cs-editor">
	<?php include CSB_VIEWS_DIR . 'widgets-editor.php'; ?>
	</div>


	<?php /*
	============================================================================
	===== EXPORT
	============================================================================
	*/ ?>
	<div class="cs-export">
	<?php include CSB_VIEWS_DIR . 'widgets-export.php'; ?>
	</div>

	<?php /*
	============================================================================
	===== LOCATION popup.
	============================================================================
	*/ ?>
	<div class="cs-location">
	<?php include CSB_VIEWS_DIR . 'widgets-location.php'; ?>
	</div>

 </div>
