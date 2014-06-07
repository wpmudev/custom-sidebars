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

?>

<div class="themes-php csb">
<div class="wrap">

	<?php include 'part-tabs.php'; ?>

	<div id="editsidebarpage">
		<div id="poststuff">

			<h2 class="title"><?php _e( 'Overview', CSB_LANG ); ?></h2>


			<?php
			/* *****************************************************************
			 *
			 * Show basic infos about the WordPress configuration at time of
			 * the export.
			 */
			?>

			<table cellspacing="1" cellpadding="4" class="csb-export-head">
				<tbody>
					<tr>
						<th><?php _e( 'Export date', CSB_LANG ); ?></th>
						<td><?php echo esc_html( date( $date_format, $import['meta']['created'] ) ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Export notes' ); ?></th>
						<td><?php echo nl2br( esc_html( $import['meta']['description'] ) ); ?></td>
					</tr>
				</tbody>
			</table>

			<h3><?php _e( 'Export details', CSB_LANG ); ?></h3>
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
						<th><?php _e( 'WordPress version' ); ?></th>
						<td><?php echo esc_html( $import['meta']['wp_version'] ); ?></td>
						<td><?php echo esc_html( $wp_version ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Plugin version' ); ?></th>
						<td><?php echo esc_html( $import['meta']['csb_version'] ); ?></td>
						<td><?php echo esc_html( CSB_VERSION ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Theme' ); ?></th>
						<td><?php echo esc_html( $import['meta']['theme_name'] . ' (' . $import['meta']['theme_version'] . ')' ); ?></td>
						<td><?php echo esc_html( $theme->get( 'Name' ) . ' (' . $theme->get( 'Version' ) . ')' ); ?></td>
					</tr>
				</tbody>
			</table>


			<?php
			/* *****************************************************************
			 *
			 * List all sidebars in the import file
			 */
			$alternate = '';
			?>
			<p>&nbsp;</p>
			<h2 class="title"><?php _e( 'Custom Sidebars', CSB_LANG ); ?></h2>
			<p>
				<?php _e( 'Mark the sidebars that you want to import.', CSB_LANG ); ?>
			</p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
						<th scope="col" id="name" class="manage-column column-name"><?php _e( 'Name', CSB_LANG ); ?></th>
						<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Description', CSB_LANG ); ?></th>
						<th scope="col" id="note" class="manage-column column-note"><?php _e( 'Note', CSB_LANG ); ?></th>
						<th scope="col" id="widgets" class="manage-column column-widgets"><?php _e( 'Widgets', CSB_LANG ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $import['sidebars'] as $sidebar ) : ?>
					<?php
					$alternate = ('' == $alternate ? 'alternate' : '');
					$note = in_array( $sidebar['id'], $current_keys ) ? __( '<strong>Existing sidebar will be replaced!</strong>', CSB_LANG ) : __( 'New sidebar will be created', CSB_LANG );
					$import_sidebar = $import['widgets'][ $sidebar['id'] ];
					$widget_count = count( $import_sidebar );
					?>
					<tr class="<?php echo esc_attr( $alternate ); ?>">
						<th scope="row" class="check-column"><input type="checkbox" /></td>
						<td class="name column-name"><?php echo esc_html( $sidebar['name'] ); ?></td>
						<td class="description column-description"><?php echo esc_html( $sidebar['description'] ); ?></td>
						<td class="note column-note"><?php echo $note; ?></td>
						<td class="widgets column-widgets"><?php echo esc_html( $widget_count ); ?> widgets</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>


			<?php
			/* *****************************************************************
			 *
			 * List all default theme sidebars that exist in the import file
			 */
			$alternate = '';
			?>
			<p>&nbsp;</p>
			<h2 class="title"><?php _e( 'Theme Sidebars', CSB_LANG ); ?></h2>
			<p>
				<?php _e( 'You can import widget-configuration for these theme sidebars.', CSB_LANG ); ?>
			</p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
						<th scope="col" id="name" class="manage-column column-name"><?php _e( 'Name', CSB_LANG ); ?></th>
						<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Description', CSB_LANG ); ?></th>
						<th scope="col" id="widgets" class="manage-column column-widgets"><?php _e( 'Widgets', CSB_LANG ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $theme_sidebars as $sidebar ) : ?>

					<?php if ( isset( $import['widgets'][ $sidebar['id'] ] ) ) : ?>
						<?php
						$alternate = ('' == $alternate ? 'alternate' : '');
						$import_sidebar = $import['widgets'][ $sidebar['id'] ];
						$widget_count = count( $import_sidebar );
						?>
						<tr class="<?php echo esc_attr( $alternate ); ?>">
							<th scope="row" class="check-column"><input type="checkbox" /></td>
							<td class="name column-name"><?php echo esc_html( $sidebar['name'] ); ?></td>
							<td class="description column-description"><?php echo esc_html( $sidebar['description'] ); ?></td>
							<td class="widgets column-widgets"><?php echo esc_html( $widget_count ); ?> widgets</td>
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
			$missing = array();
			?>
			<p>&nbsp;</p>
			<h2 class="title"><?php _e( 'Configuration', CSB_LANG ); ?></h2>
			<p>
				<?php _e( 'Choose if you want to replace the current plugin configuration with the following settings.', CSB_LANG ); ?>
			</p>
			<table cellspacing="1" cellpadding="4" class="csb-export-head">
				<tr>
					<th><?php _e( 'Replaceable Sidebars', CSB_LANG ); ?></th>
					<td>
					<?php foreach ( $import['options']['modifiable'] as $sb_id ) : ?>
						<?php if ( isset( $theme_sidebars[ $sb_id ] ) ) : ?>
							<?php echo esc_html( $theme_sidebars[ $sb_id ]['name'] ); ?><br />
						<?php else :
							$missing[] = $sb_id;
						endif; ?>
					<?php endforeach; ?>
					<br />
					<?php if ( ! empty( $missing ) ) : ?>
						<em><?php _e( 'These sidebars do not exist in current theme, settings for these sidebars will not be imported:', CSB_LANG ); ?></em>
						<br/>- <?php echo implode( '<br />- ', $missing ); ?>
					<?php endif; ?>
					</td>
				</tr>
			</table>


			<?php


			 // ----- DEBUG START
			 function_exists( 'wp_describe' ) && wp_describe( 'import.php:29', $import['options'] );
			 function_exists( 'wp_describe' ) && wp_describe( 'import.php:29', $import['widgets'] );
			 // ----- DEBUG END

			 ?>

		</div>
	</div>


	<?php include 'part-footer.php'; ?>

</div>
</div>