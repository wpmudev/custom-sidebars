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
		<h2><?php _e( 'Sidebars', CSB_LANG ); ?></h2>
		<div id="cs-options" class="csb cs-options">
			<button type="button" class="button button-primary cs-action btn-create-sidebar">
				<i class="dashicons dashicons-plus-alt"></i>
				<?php _e( 'Create a new sidebar', CSB_LANG ); ?>
			</button>
			<a href="#" class="cs-action btn-export"><?php _e( 'Import / Export Sidebars', CSB_LANG ); ?></a>
		</div>
	</div>


	<?php /*
	============================================================================
	===== LANGUAGE
	============================================================================
	*/ ?>
	<div class="cs-lang"
		data-title-edit="<?php _e( 'Edit', CSB_LANG ); ?>"
		data-title-new="<?php _e( 'New Custom Sidebar', CSB_LANG ); ?>"
		data-btn-edit="<?php _e( 'Save Changes', CSB_LANG ); ?>"
		data-btn-new="<?php _e( 'Create Sidebar', CSB_LANG ); ?>"
		data-title-delete="<?php _e( 'Delete Sidebar', CSB_LANG ); ?>"
		data-title-location="<?php _e( 'Define where you want this sidebar to appear.', CSB_LANG ); ?>"
		data-title-export="<?php _e( 'Import / Export Sidebars', CSB_LANG ); ?>"
	>
		<div class="cs-title-col1 cs-title"><h3><?php _e( 'Custom Sidebars', CSB_LANG ); ?></h3></div>
		<div class="cs-title-col2 cs-title"><h3><?php _e( 'Theme Sidebars', CSB_LANG ); ?></h3></div>
	</div>


	<?php /*
	============================================================================
	===== DELETE SIDEBAR confirmation
	============================================================================
	*/ ?>
	<div class="cs-delete">
		<div class="wpmui-form">
			<div>
			<?php _e(
				'Please confirm that you want to delete the sidebar <strong class="name"></strong>.', CSB_LANG
			); ?>
			</div>
			<div class="buttons">
				<button type="button" class="button-link btn-cancel"><?php _e( 'No, cancel', CSB_LANG ); ?></button>
				<button type="button" class="button-primary btn-delete"><?php _e( 'Yes, delete it', CSB_LANG ); ?></button>
			</div>
		</div>
	</div>


	<?php /*
	============================================================================
	===== ADD/EDIT SIDEBAR
	============================================================================
	*/ ?>
	<div class="cs-editor">
		<form class="wpmui-form">
			<input type="hidden" name="do" value="save" />
			<input type="hidden" name="sb" id="csb-id" value="" />

			<div class="wpmui-grid-8 no-pad-top">
				<div class="col-3">
					<label for="csb-name"><?php _e( 'Name', CSB_LANG ); ?></label>
					<input type="text" name="name" id="csb-name" placeholder="<?php _e( 'Sidebar name here...', CSB_LANG ); ?>" />
					<div class="hint"><?php _e( 'The name must be unique.', CSB_LANG ); ?></div>
				</div>
				<div class="col-5">
					<label for="csb-description"><?php _e( 'Description', CSB_LANG ); ?></label>
					<input type="text" name="description" id="csb-description" placeholder="<?php _e( 'Sidebar description here...', CSB_LANG ); ?>" />
				</div>
			</div>
			<hr />
			<div class="wpmui-grid-8">
				<div class="col-8">
					<label for="csb-more">
						<input type="checkbox" id="csb-more" />
						<?php _e( 'Add custom wrapper code', CSB_LANG ); ?>
					</label>
				</div>
			</div>
			<div class="wpmui-grid-8 csb-more-content">
				<div class="col-8 hint">
					<strong><?php _e( 'Caution:', CSB_LANG ); ?></strong>
					<?php _e(
						'Before-after title-widget properties define the html code that will wrap ' .
						'the widgets and their titles in the sidebars, more info about them on the '.
						'<a href="http://justintadlock.com/archives/2010/11/08/sidebars-in-wordpress" target="_blank">Justin ' .
						'Tadlock Blog</a>. Do not use these fields if you are not sure what you are doing, it can break ' .
						'the design of your site. Leave these fields blank to use the theme sidebars design.', CSB_LANG
					); ?>
				</div>
			</div>
			<div class="wpmui-grid-8 csb-more-content">
				<div class="col-4">
					<label for="csb-before-title"><?php _e( 'Before Title', CSB_LANG ); ?></label>
					<textarea rows="4" name="before_title" id="csb-before-title"></textarea>
				</div>
				<div class="col-4">
					<label for="csb-after-title"><?php _e( 'After Title', CSB_LANG ); ?></label>
					<textarea rows="4" name="after_title" id="csb-after-title"></textarea>
				</div>
			</div>
			<div class="wpmui-grid-8 csb-more-content">
				<div class="col-4">
					<label for="csb-before-widget"><?php _e( 'Before Widget', CSB_LANG ); ?></label>
					<textarea rows="4" name="before_widget" id="csb-before-widget"></textarea>
				</div>
				<div class="col-4">
					<label for="csb-after-widget"><?php _e( 'After Widget', CSB_LANG ); ?></label>
					<textarea rows="4" name="after_widget" id="csb-after-widget"></textarea>
				</div>
			</div>
			<div class="buttons">
				<button type="button" class="button-primary btn-save"><?php _e( 'Create Sidebar', CSB_LANG ); ?></button>
			</div>
		</form>
	</div>


	<?php /*
	============================================================================
	===== EXPORT
	============================================================================
	*/ ?>
	<div class="cs-export">
		<div class="wpmui-form">
			<h2 class="no-pad-top"><?php _e( 'Export', CSB_LANG ); ?></h2>
			<form class="frm-export">
				<input type="hidden" name="do" value="export" />
				<p>
					<?php _e( 'This will generate a complete export file containing all your sidebars and the current sidebar configuration.', CSB_LANG ); ?>
				</p>
				<p>
					<label for="description"><?php _e( 'Optional description for the export file:' ); ?></label><br />
					<textarea id="description" name="export-description" placeholder="" cols="80" rows="3"></textarea>
				</p>
				<p>
					<button class="button-primary"><i class="dashicons dashicons-download"></i> <?php _e( 'Export', CSB_LANG ); ?></button>
				</p>
			</form>
			<hr />
			<h2><?php _e( 'Import', CSB_LANG ); ?></h2>
			<form class="frm-preview-import">
				<input type="hidden" name="do" value="preview-import" />
				<p>
					<label for="import-file"><?php _e( 'Export file', CSB_LANG ); ?></label>
					<input type="file" id="import-file" name="data" />
				</p>
				<p>
					<button class="button-primary"><i class="dashicons dashicons-upload"></i> <?php _e( 'Preview', CSB_LANG ); ?></button>
				</p>
			</form>
		</div>
	</div>


	<?php /*
	============================================================================
	===== TOOLBAR for custom sidebars
	============================================================================
	*/ ?>
	<div class="cs-custom-sidebar cs-toolbar">
		<a class="cs-tool delete-sidebar" href="#" title="<?php _e( 'Delete', CSB_LANG ); ?>">
			<i class="dashicons dashicons-trash"></i>
		</a>
		<span class="cs-separator"> | </span>
		<a class="cs-tool edit-sidebar" href="#">
			<?php _e( 'Edit', CSB_LANG ); ?>
		</a>
		<span class="cs-separator"> | </span>
		<a class="cs-tool where-sidebar thickbox" href="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?action=cs-ajax&cs_action=where&id=" title="<?php _e( 'Where do you want the sidebar?', CSB_LANG ); ?>">
			<?php _e( 'Sidebar Location', CSB_LANG ); ?>
		</a>
		<span class="cs-separator"> | </span>
	</div>


	<?php /*
	============================================================================
	===== TOOLBAR for theme sidebars
	============================================================================
	*/ ?>
	<div class="cs-theme-sidebar cs-toolbar">
		<label for="cs-replaceable" class="cs-tool btn-replaceable">
			<input type="checkbox" id="" class="has-label" data-label="<?php echo esc_attr( __( 'This sidebar can be replaced', CSB_LANG ) ); ?>" />
			<?php _e( 'Make Replaceable', CSB_LANG ); ?>
		</label>
		<span class="cs-separator"> | </span>
	</div>

 </div>