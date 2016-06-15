<?php
/**
 * Contents of the Import/Export popup in the widgets screen.
 *
 * This file is included in widgets.php.
 */
?>

<div class="wpmui-form module-export">
	<h2 class="no-pad-top"><?php _e( 'Export', 'custom-sidebars' ); ?></h2>
	<form class="frm-export">
		<input type="hidden" name="do" value="export" />
		<p>
			<i class="dashicons dashicons-info light"></i>
			<?php
			_e(
				'This will generate a complete export file containing all ' .
				'your sidebars and the current sidebar configuration.', 'custom-sidebars'
			);
			?>
		</p>
		<p>
			<label for="description"><?php _e( 'Optional description for the export file:', 'custom-sidebars' ); ?></label><br />
			<textarea id="description" name="export-description" placeholder="" cols="80" rows="3"></textarea>
		</p>
		<p>
			<button class="button-primary">
				<i class="dashicons dashicons-download"></i> <?php _e( 'Export', 'custom-sidebars' ); ?>
			</button>
		</p>
	</form>
	<hr />
	<h2><?php _e( 'Import', 'custom-sidebars' ); ?></h2>
	<form class="frm-preview-import">
		<input type="hidden" name="do" value="preview-import" />
		<p>
			<label for="import-file"><?php _e( 'Export file', 'custom-sidebars' ); ?></label>
			<input type="file" id="import-file" name="data" />
		</p>
		<p>
			<button class="button-primary">
				<i class="dashicons dashicons-upload"></i> <?php _e( 'Preview', 'custom-sidebars' ); ?>
			</button>
		</p>
	</form>
</div>
