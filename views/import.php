<?php
/**
 * PRO section: Show interface for import preview.
 */

global $wp_version;

$import = CustomSidebarsExport::instance()->get_import_data();

$date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
$theme = wp_get_theme();
$current_sidebars = CustomSidebars::instance()->get_custom_sidebars();
$theme_sidebars = CustomSidebars::instance()->get_theme_sidebars();
$current_keys = array();
foreach ( $current_sidebars as $c_sidebar ) {
	$current_keys[] = $c_sidebar['id'];
}

/**
 * Helper function used only in this view.
 * It renders a list with sidebar-replacement details
 */
function list_sidebar_replacement( $list ) {
	$import = CustomSidebarsExport::instance()->get_import_data();
	$theme_sidebars = CustomSidebars::instance()->get_theme_sidebars();
	$first = true;

	foreach ( $list as $from_id => $to_id ) {
		$from = $theme_sidebars[ $from_id ];
		$to = array();
		if ( isset( $theme_sidebars[ $to_id ] ) ) {
			$to = $theme_sidebars[ $to_id ];
		} else {
			$to = $import['sidebars'][ $to_id ];
		}
		?>
		<?php if ( ! $first ) : ?>
			</tr>
			<tr>
		<?php endif; ?>
		<td><?php echo esc_html( $from['name'] ); ?></td>
		<td><i class="dashicons dashicons-arrow-right-alt hint"></i></td>
		<td><?php echo esc_html( $to['name'] ); ?></td>
		<?php
		$first = false;
	}
}


?>

<div class="themes-php csb">
<div class="wrap">

	<div id="editsidebarpage">
		<div id="poststuff">


<?php if ( ! empty ( $import ) ) : ?>
	<?php
	/* *****************************************************************
	 *
	 * Show basic infos about the WordPress configuration at time of
	 * the export.
	 */
	?>

	<h2 class="title"><?php _e( 'Import', CSB_LANG ); ?>
	<div class="show-infos">
		<i class="dashicons dashicons-info"></i>
		<div class="export-infos" style="display:none">
			<table cellspacing="1" cellpadding="4" class="csb-export-head">
				<tbody>
					<tr>
						<th><?php _e( 'Filename', CSB_LANG ); ?></th>
						<td colspan="2"><?php echo esc_html( @$import['meta']['filename'] ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Exported on', CSB_LANG ); ?></th>
						<td colspan="2"><?php echo esc_html( ' ' . date( $date_format, $import['meta']['created'] ) ); ?></td>
					</tr>
				</tbody>
			</table>

			<div class="section"><?php _e( 'WordPress settings', CSB_LANG ); ?></div>
			<table cellspacing="1" cellpadding="4" class="csb-export-head">
				<thead>
					<tr>
						<th></th>
						<td>Export</td>
						<td>Current</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th><?php _e( 'WordPress version', CSB_LANG ); ?></th>
						<td><?php echo esc_html( $import['meta']['wp_version'] ); ?></td>
						<td><?php echo esc_html( $wp_version ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Plugin version', CSB_LANG ); ?></th>
						<td><?php echo esc_html( $import['meta']['csb_version'] ); ?></td>
						<td><?php echo esc_html( CSB_VERSION ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Theme', CSB_LANG ); ?></th>
						<td><?php echo esc_html( $import['meta']['theme_name'] . ' (' . $import['meta']['theme_version'] . ')' ); ?></td>
						<td><?php echo esc_html( $theme->get( 'Name' ) . ' (' . $theme->get( 'Version' ) . ')' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

	</div></h2>

	<?php if ( ! empty( $import['meta']['description'] ) ) : ?>
		<pre><?php echo esc_html( $import['meta']['description'] ); ?></pre>
	<?php endif; ?>


	<form method="post" class="csb-form">
	<input type="hidden" name="process-import-data" value="1" />


	<?php
	/* *****************************************************************
	 *
	 * List all sidebars in the import file
	 */
	$alternate = '';
	?>
	<h3 class="title"><?php _e( 'Custom Sidebars', CSB_LANG ); ?></h3>
	<p>
		<?php _e( 'Mark the sidebars that you want to import.', CSB_LANG ); ?>
	</p>
	<p>
		<label for="import-widgets">
			<input type="checkbox" id="import-widgets" name="import_widgets" />
			<?php _e( 'Also import all widgets of the selected sidebars.', CSB_LANG ); ?>
		</label>
	</p>
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
				<th scope="col" id="name" class="manage-column column-name"><?php _e( 'Name', CSB_LANG ); ?></th>
				<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Description', CSB_LANG ); ?></th>
				<th scope="col" id="note" class="manage-column column-note"><?php _e( 'Note', CSB_LANG ); ?></th>
				<th scope="col" id="widgets" class="manage-column column-widgets" style="display:none"><?php _e( 'Widgets', CSB_LANG ); ?></th>
			</tr>
		</thead>
		<tbody>
	<?php foreach ( $import['sidebars'] as $sidebar ) : ?>
		<?php
		$alternate = ('' == $alternate ? 'alternate' : '');
		if ( in_array( $sidebar['id'], $current_keys ) ) {
			$note = __( 'Existing sidebar will be replaced!', CSB_LANG );
		} else {
			$note = __( 'New sidebar will be created', CSB_LANG );
		}
		$import_sidebar = $import['widgets'][ $sidebar['id'] ];
		if ( ! is_array( $import_sidebar ) ) {
			$import_sidebar = array();
		}
		?>
		<tr class="<?php echo esc_attr( $alternate ); ?>">
			<th scope="row" class="check-column">
				<input type="checkbox" name="import_sb_<?php echo esc_attr( $sidebar['id'] ); ?>" />
			</th>
			<td class="name column-name"><?php echo esc_html( $sidebar['name'] ); ?></td>
			<td class="description column-description"><?php echo esc_html( $sidebar['description'] ); ?></td>
			<td class="note column-note"><?php echo esc_html( $note ); ?></td>
			<td class="widgets column-widgets" style="display:none">
		<?php if ( count( $import_sidebar ) ) : ?>
			<?php foreach ( $import_sidebar as $key => $data ) : ?>
				<?php echo esc_html( $data['name'] ); ?><br />
			<?php endforeach; ?>
		<?php else : ?>
			-
		<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>



	<?php
	/* *****************************************************************
	 *
	 * List all default theme sidebars that exist in the import file
	 */
	?>

	<?php foreach ( $theme_sidebars as $sidebar ) : ?>

		<?php if ( isset( $import['widgets'][ $sidebar['id'] ] ) ) : ?>
			<?php
			$alternate = ('' == $alternate ? 'alternate' : '');
			$import_sidebar = $import['widgets'][ $sidebar['id'] ];
			if ( ! is_array( $import_sidebar ) ) {
				$import_sidebar = array();
			}
			if ( ! count( $import_sidebar ) ) {
				continue;
			}
			?>
			<tr class="import-widgets <?php echo esc_attr( $alternate ); ?>" style="display: none">
				<th scope="row" class="check-column">
					<input type="checkbox" name="import_sb_<?php echo esc_attr( $sidebar['id'] ); ?>" />
				</th>
				<td class="name column-name"><?php echo esc_html( $sidebar['name'] ); ?></td>
				<td class="description column-description"><?php echo esc_html( $sidebar['description'] ); ?></td>
				<td><em><?php _e( '(Theme sidebar)', CSB_LANG ); ?></em></td>
				<td class="widgets column-widgets">
			<?php if ( count( $import_sidebar ) ) : ?>
				<?php foreach ( $import_sidebar as $key => $data ) : ?>
					<?php echo esc_html( $data['name'] ); ?><br />
				<?php endforeach; ?>
			<?php else : ?>
				-
			<?php endif; ?>
				</td>
			</tr>
		<?php endif; ?>

	<?php endforeach; ?>
		</tbody>
	</table>



	<?php
	/* *****************************************************************
	 *
	 * Show the custom sidebar configuration
	 */
	?>
	<p>&nbsp;</p>
	<h3 class="title"><?php _e( 'Configuration', CSB_LANG ); ?>
	<div class="show-infos">
		<i class="dashicons dashicons-info"></i>
		<div class="export-infos" style="display:none">
	<div class="section"><?php _e( 'Replaceable sidebars', CSB_LANG ); ?></div>
	<table cellspacing="1" cellpadding="4" class="csb-export-head">
		<tr>
			<th scope="row"><?php _e( 'Replaceable Sidebars', CSB_LANG ); ?></th>
			<td>
			<?php foreach ( $import['options']['modifiable'] as $sb_id ) : ?>
				<?php echo esc_html( $theme_sidebars[ $sb_id ]['name'] ); ?><br />
			<?php endforeach; ?>
			</td>
		</tr>
	</table>

	<?php /* single-posttype */ ?>
	<div class="section"><?php _e( 'By post type', CSB_LANG ); ?></div>
	<table cellspacing="1" cellpadding="4" class="csb-export-head">
	<?php
	$list = $import['options']['defaults'];
	foreach ( $list as $key => $values ) {
		$type = get_post_type_object( $key );
		$rows = count( $values );
		if ( $rows == 0 ) { continue; }
		?>
		<tr>
			<th scope="row" rowspan="<?php echo esc_attr( $rows ) ?>"><?php echo esc_html( $type->labels->name ); ?></th>
			<?php list_sidebar_replacement( $values ); ?>
		</tr>
		<?php
	}
	?>
	</table>

	<?php /* archive-posttype */ ?>
	<div class="section"><?php _e( 'Post-type archives', CSB_LANG ); ?></div>
	<table cellspacing="1" cellpadding="4" class="csb-export-head">
	<?php
	$list = $import['options']['post_type_pages'];
	foreach ( $list as $key => $values ) {
		$type = get_post_type_object( $key );
		$rows = count( $values );
		if ( $rows == 0 ) { continue; }
		?>
		<tr>
			<th scope="row" rowspan="<?php echo esc_attr( $rows ) ?>"><?php echo esc_html( $type->labels->name ); ?></th>
			<?php list_sidebar_replacement( $values ); ?>
		</tr>
		<?php
	}
	?>
	</table>

	<?php /* single-category */ ?>
	<div class="section"><?php _e( 'By category', CSB_LANG ); ?></div>
	<table cellspacing="1" cellpadding="4" class="csb-export-head">
	<?php
	$list = $import['options']['category_posts'];
	foreach ( $list as $key => $values ) {
		$cat = get_category( $key );
		$rows = count( $values );
		if ( $rows == 0 ) { continue; }
		?>
		<tr>
			<th scope="row" rowspan="<?php echo esc_attr( $rows ) ?>"><?php echo esc_html( $cat->name ); ?></th>
			<?php list_sidebar_replacement( $values ); ?>
		</tr>
		<?php
	}
	?>
	</table>

	<?php /* archive-category */ ?>
	<div class="section"><?php _e( 'Category archives', CSB_LANG ); ?></div>
	<table cellspacing="1" cellpadding="4" class="csb-export-head">
	<?php
	$list = $import['options']['category_pages'];
	foreach ( $list as $key => $values ) {
		$cat = get_category( $key );
		$rows = count( $values );
		if ( $rows == 0 ) { continue; }
		?>
		<tr>
			<th scope="row" rowspan="<?php echo esc_attr( $rows ) ?>"><?php echo esc_html( $cat->name ); ?></th>
			<?php list_sidebar_replacement( $values ); ?>
		</tr>
		<?php
	}
	?>
	</table>

	<?php /* special pages */ ?>
	<div class="section"><?php _e( 'Special pages', CSB_LANG ); ?></div>
	<table cellspacing="1" cellpadding="4" class="csb-export-head">
		<tr>
			<?php $rows = count( $import['options']['blog'] ); ?>
			<th scope="row" rowspan="<?php echo esc_attr( $rows ) ?>"><?php _e( 'Main blog page', CSB_LANG ); /* blog */ ?></th>
			<?php list_sidebar_replacement( $import['options']['blog'] ); ?>
		</tr>
		<tr>
			<?php $rows = count( $import['options']['date'] ); ?>
			<th scope="row" rowspan="<?php echo esc_attr( $rows ) ?>"><?php _e( 'Date archives', CSB_LANG ); /* date */ ?></th>
			<?php list_sidebar_replacement( $import['options']['date'] ); ?>
		</tr>
		<tr>
			<?php $rows = count( $import['options']['authors'] ); ?>
			<th scope="row" rowspan="<?php echo esc_attr( $rows ) ?>"><?php _e( 'Author archives', CSB_LANG ); /* authors */ ?></th>
			<?php list_sidebar_replacement( $import['options']['authors'] ); ?>
		</tr>
		<tr>
			<?php $rows = count( $import['options']['tags'] ); ?>
			<th scope="row" rowspan="<?php echo esc_attr( $rows ) ?>"><?php _e( 'Tag archives', CSB_LANG ); /* tags */ ?></th>
			<?php list_sidebar_replacement( $import['options']['tags'] ); ?>
		</tr>
		<tr>
			<?php $rows = count( $import['options']['search'] ); ?>
			<th scope="row" rowspan="<?php echo esc_attr( $rows ) ?>"><?php _e( 'Search results page', CSB_LANG ); /* search */ ?></th>
			<?php list_sidebar_replacement( $import['options']['search'] ); ?>
		</tr>
	</table>
	</div>
	</div>
	</h3>

	<p>
		<label for="import-config">
			<input type="checkbox" id="import-config" name="import_plugin_config" />
			<?php _e( 'Replace the current plugin configuration with the imported configuration.', CSB_LANG ); ?>
		</label>
	</p>
	<p>&nbsp;</p>


	<input type="hidden" name="import_data" value="<?php echo esc_attr( base64_encode( json_encode( $import ) ) ); ?>" />
	<p class="form-buttons">
		<button class="button-primary"><i class="dashicons dashicons-migrate"></i> Import selected items</button>
		<button class="button-secondary btn-cancel">Cancel</button>
	</p>
	</form>

	<?php
	/* *****************************************************************
	 *
	 * Overview of ignored/missing data
	 */
	?>
	<div class="ignored" style="display:none">
	<p>&nbsp;</p>
	<?php if ( ! empty( $import['ignore'] ) ) : ?>
	<h3 class="title"><?php _e( 'Ignored items', CSB_LANG ); ?></h3>
	<em><?php _e( 'These itmems do not exist in current theme or blog, settings for these items will not be imported:', CSB_LANG ); ?></em>
	<table cellspacing="1" cellpadding="4" class="csb-export-head">
		<?php
		$list = $import['ignore'];
		foreach ( $list as $type => $values ) : ?>
			<?php
			/* $type can be:
			 *  - sidebars
			 *  - categories
			 *  - widgets
			 */
			?>
			<tr>
				<th scope="row"><?php _e( $type ); ?></th>
				<td><ul>
				<?php foreach ( $values as $value ) : ?>
					<li><?php echo esc_html( $value ); ?></li>
				<?php endforeach; ?>
				</ul></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>
	</div>

	<?php endif; ?>

		</div>
	</div>

	<script>
	jQuery( function init_import() {
		var $chk_widget = jQuery( '#import-widgets'),
			$btn_cancel = jQuery( '.btn-cancel' );

		// Toggle the widget columns
		$chk_widget.change( function toggle_widget_cols() {
			var $context = jQuery( '.column-widgets, .import-widgets' );
			if ( $chk_widget.prop('checked') ) {
				$context.show();
			} else {
				$context.hide();
			}
		});

		// Cancel
		$btn_cancel.click( function do_cancel() {
			window.location.href = window.location.href.replace( 'p=import', 'p=export' );
		});
	});
	</script>
</div>
</div>