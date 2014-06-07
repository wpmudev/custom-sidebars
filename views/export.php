<?php
/**
 * PRO section: Show interface for export/import.
 */
?>

<div class="themes-php csb">
<div class="wrap">

	<?php include 'part-tabs.php'; ?>

	<div id="editsidebarpage">
		<div id="poststuff">

			<h2 class="title"><?php _e( 'Export', CSB_LANG ); ?></h2>

			<form action="themes.php?page=customsidebars&p=export" method="post">
				<?php wp_nonce_field( 'custom-sidebars-export' ); ?>
				<input type="hidden" name="export-sidebars" value="1" />
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

			<h2 class="title"><?php _e( 'Import', CSB_LANG ); ?></h2>

			<form action="themes.php?page=customsidebars&p=import" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'custom-sidebars-import' ); ?>
				<input type="hidden" name="upload-import-file" value="1" />
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


	<?php include 'part-footer.php'; ?>

</div>
</div>