<?php
/**
 * PRO section: Show interface for import preview.
 */

global $wp_version;

$import = CustomSidebarsExport::get_import_data();

$date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
$theme = wp_get_theme();
$current_sidebars = CustomSidebars::get_custom_sidebars();
$theme_sidebars = CustomSidebars::get_sidebars();
$current_keys = array();
foreach ( $current_sidebars as $c_sidebar ) {
	$current_keys[] = $c_sidebar['id'];
}

$csb_info = get_plugin_data( CSB_PLUGIN );

/**
 * Helper function used only in this view.
 * It renders a list with sidebar-replacement details
 */
function list_sidebar_replacement( $label, $list ) {
	$import = CustomSidebarsExport::get_import_data();
	$theme_sidebars = CustomSidebars::get_sidebars();

	if ( is_array( $list ) ) {
		foreach ( $list as $from_id => $to_id ) {
			$from = $theme_sidebars[ $from_id ];
			$to = array();
			if ( isset( $theme_sidebars[ $to_id ] ) ) {
				$to = $theme_sidebars[ $to_id ];
			} else {
				$to = $import['sidebars'][ $to_id ];
			}
?>
        <tr>
            <th scope="row"><?php echo esc_html( $label ); ?></th>
            <td><?php echo esc_html( $from['name'] ); ?></td>
            <td><i class="dashicons dashicons-arrow-right-alt hint"></i></td>
            <td><?php echo esc_html( $to['name'] ); ?></td>
        </tr>
<?php
		}
	}
}
?>
<div>
<div class="wpmui-form">
<?php if ( ! empty( $import ) ) : ?>
	<?php
	/* *****************************************************************
	 *
	 * Show basic infos about the WordPress configuration at time of
	 * the export.
	 */
	$filename = '';
	if (
	isset( $import['meta'] )
	&& isset( $import['meta']['filename'] )
	) {
		$filename = $import['meta']['filename'];
	}
	?>
	<h2 class="no-pad-top"><?php _e( 'Import', 'custom-sidebars' ); ?></h2>
	<div class="show-infos">
		<i class="dashicons dashicons-info"></i>
		<div class="export-infos" style="display:none">
			<table cellspacing="1" cellpadding="4" class="csb-export-head">
				<tbody>
					<tr>
						<th><?php _e( 'Filename', 'custom-sidebars' ); ?></th>
						<td colspan="2"><?php echo esc_html( $filename ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Exported on', 'custom-sidebars' ); ?></th>
						<td colspan="2"><?php echo esc_html( ' ' . date( $date_format, $import['meta']['created'] ) ); ?></td>
					</tr>
				</tbody>
			</table>

			<div class="section"><?php _e( 'WordPress Settings', 'custom-sidebars' ); ?></div>
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
						<th><?php _e( 'WordPress Version', 'custom-sidebars' ); ?></th>
						<td><?php echo esc_html( $import['meta']['wp_version'] ); ?></td>
						<td><?php echo esc_html( $wp_version ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Plugin Version', 'custom-sidebars' ); ?></th>
						<td><?php echo esc_html( $import['meta']['csb_version'] ); ?></td>
						<td><?php echo esc_html( isset( $csb_info['Version'] )? $csb_info['Version'] : __( 'Unknown', 'custom-sidebars' ) ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Theme', 'custom-sidebars' ); ?></th>
						<td><?php echo esc_html( $import['meta']['theme_name'] . ' (' . $import['meta']['theme_version'] . ')' ); ?></td>
						<td><?php echo esc_html( $theme->get( 'Name' ) . ' (' . $theme->get( 'Version' ) . ')' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<?php if ( ! empty( $import['meta']['description'] ) ) : ?>
		<pre><?php echo esc_html( stripslashes( $import['meta']['description'] ) ); ?></pre>
	<?php endif; ?>

	<form class="frm-import csb-form">
	<input type="hidden" name="do" value="import" />

	<?php
	/* *****************************************************************
	 *
	 * List all sidebars in the import file
	 */
	$alternate = '';
	?>
	<h3 class="title"><?php _e( 'Custom Sidebars', 'custom-sidebars' ); ?></h3>
	<p>
		<?php _e( 'Mark the sidebars that you want to import.', 'custom-sidebars' ); ?>
	</p>
	<p>
		<label for="import-widgets">
			<input type="checkbox" id="import-widgets" name="import_widgets" />
			<?php _e( 'Also import all widgets of the selected sidebars.', 'custom-sidebars' ); ?>
		</label>
	</p>
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
				<th scope="col" id="name" class="manage-column column-name"><?php _e( 'Name', 'custom-sidebars' ); ?></th>
				<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Description', 'custom-sidebars' ); ?></th>
				<th scope="col" id="note" class="manage-column column-note"><?php _e( 'Note', 'custom-sidebars' ); ?></th>
				<th scope="col" id="widgets" class="manage-column column-widgets" style="display:none"><?php _e( 'Widgets', 'custom-sidebars' ); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ( $import['sidebars'] as $sidebar ) {
	$alternate = ('' == $alternate ? 'alternate' : '');
	$note = __( 'New sidebar will be created', 'custom-sidebars' );
	if ( in_array( $sidebar['id'], $current_keys ) ) {
		$note = __( 'Existing sidebar will be replaced!', 'custom-sidebars' );
	}
	$import_sidebar = array();
	if (
		isset( $sidebar['id'] )
		&& isset( $import['widgets'] )
		&& isset( $import['widgets'][ $sidebar['id'] ] )
		&& is_array( $import['widgets'][ $sidebar['id'] ] )
	) {
		$import_sidebar = $import['widgets'][ $sidebar['id'] ];
	}
	$id = sprintf( 'import_sb_id_%s', $sidebar['id'] );
?>
		<tr class="<?php echo esc_attr( $alternate ); ?>">
			<th scope="row" class="check-column">
				<input type="checkbox" name="import_sb_<?php echo esc_attr( $sidebar['id'] ); ?>" id="<?php echo esc_attr( $id ); ?>"/>
			</th>
			<td class="name column-name"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $sidebar['name'] ); ?></label></td>
			<td class="description column-description"><?php echo esc_html( $sidebar['description'] ); ?></td>
			<td class="note column-note"><?php echo esc_html( $note ); ?></td>
			<td class="widgets column-widgets" style="display:none">
<?php
if ( count( $import_sidebar ) ) {
	echo '<ul>';
	foreach ( $import_sidebar as $key => $data ) {
		printf( '<li>%s</li>', esc_html( $data['name'] ) );
	}
	echo '</ul>';
} else {
	echo '-';
}
?>
			</td>
		</tr>
<?php
}


/* *****************************************************************
 *
 * List all default theme sidebars that exist in the import file
 */

foreach ( $theme_sidebars as $sidebar ) {
	if ( isset( $import['widgets'][ $sidebar['id'] ] ) ) {
		$alternate = ('' == $alternate ? 'alternate' : '');
		$import_sidebar = array();
		if (
			isset( $sidebar['id'] )
			&& isset( $import['widgets'] )
			&& isset( $import['widgets'][ $sidebar['id'] ] )
		) {
			$import_sidebar = $import['widgets'][ $sidebar['id'] ];
		}
		if ( ! is_array( $import_sidebar ) ) {
			$import_sidebar = array();
		}
		if ( ! count( $import_sidebar ) ) {
			continue;
		}
		$id = sprintf( 'import_sb_id_%s', $sidebar['id'] );
?>
            <tr class="import-widgets <?php echo esc_attr( $alternate ); ?>" style="display: none">
            <th scope="row" class="check-column">
                <input type="checkbox" name="import_sb_<?php echo esc_attr( $sidebar['id'] ); ?>" id="<?php echo esc_attr( $id ); ?>"/>
            </th>
            <td class="name column-name"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $sidebar['name'] ); ?></label></td>
                <td class="description column-description"><?php echo esc_html( $sidebar['description'] ); ?></td>
                <td><em><?php _e( '(Theme sidebar)', 'custom-sidebars' ); ?></em></td>
                <td class="widgets column-widgets">
<?php
if ( count( $import_sidebar ) ) {
	echo '<ul>';
	foreach ( $import_sidebar as $key => $data ) {
		printf( '<li>%s</li>', esc_html( $data['name'] ) );
	}
	echo '</ul>';
} else {
	echo '-';
}
?>
                </td>
            </tr>
<?php
	}
}
?>
        </tbody>
    </table>
<?php
/* *****************************************************************
 *
 * Show the custom sidebar configuration
 */
?>
    <p>&nbsp;</p>
    <h3 class="title"><?php _e( 'Configuration', 'custom-sidebars' ); ?>
    <div class="show-infos">
        <i class="dashicons dashicons-info"></i>
        <div class="export-infos" style="display:none">
    <div class="section"><?php _e( 'Replaceable Sidebars', 'custom-sidebars' ); ?></div>
    <table cellspacing="1" cellpadding="4" class="csb-export-head">
        <tr>
            <th scope="row"><?php _e( 'Replaceable Sidebars', 'custom-sidebars' ); ?></th>
            <td>
            <?php foreach ( $import['options']['modifiable'] as $sb_id ) : ?>
                <?php echo esc_html( $theme_sidebars[ $sb_id ]['name'] ); ?><br />
            <?php endforeach; ?>
            </td>
        </tr>
    </table>
<?php
/**
 * single-posttype
 */
if (
	isset( $import['options'] )
	&& isset( $import['options']['post_type_single'] )
	&& is_array( $import['options']['post_type_single'] )
) {
	printf(
		'<div class="section">%s/div>',
		esc_html__( 'By Post Type', 'custom-sidebars' )
	);
	echo '<table cellspacing="1" cellpadding="4" class="csb-export-head">';
	$list = $import['options']['post_type_single'];
	$list['foo'] = array();
	foreach ( $list as $key => $values ) {
		if ( ! count( $values ) ) {
			continue;
		}
		/**
		 * check post type exists
		 */
		$type = get_post_type_object( $key );
		if ( ! is_a( $type, 'WP_Post_Type' ) ) {
			continue;
		}
		list_sidebar_replacement( $type->labels->name, $values );
	}
	echo '</table>';
}
/**
 * archive-posttype
 */
if (
	isset( $import['options'] )
	&& isset( $import['options']['post_type_archive'] )
	&& is_array( $import['options']['post_type_archive'] )
) {
?>
	<div class="section"><?php _e( 'Post-Type Archives', 'custom-sidebars' ); ?></div>
	<table cellspacing="1" cellpadding="4" class="csb-export-head">
<?php
	$list = $import['options']['post_type_archive'];
foreach ( $list as $key => $values ) {
	$type = get_post_type_object( $key );
	if ( ! count( $values ) ) { continue; }
	list_sidebar_replacement( $type->labels->name, $values );
}
	echo '</table>';
}
/*
 * single-category
 */
if (
	isset( $import['options'] )
	&& isset( $import['options']['category_single'] )
	&& is_array( $import['options']['category_single'] )
) {

?>
	<div class="section"><?php _e( 'By Category', 'custom-sidebars' ); ?></div>
	<table cellspacing="1" cellpadding="4" class="csb-export-head">
<?php
$list = $import['options']['category_single'];
foreach ( $list as $key => $values ) {
	$cat = get_category( $key );
	if ( ! count( $values ) ) { continue; }
	list_sidebar_replacement( $cat->name, $values );
}
	echo '</table>';
}
/**
 * archive-category
 */
if (
	isset( $import['options'] )
	&& isset( $import['options']['category_archive'] )
	&& is_array( $import['options']['category_archive'] )
) {
?>
	<div class="section"><?php _e( 'Category Archives', 'custom-sidebars' ); ?></div>
	<table cellspacing="1" cellpadding="4" class="csb-export-head">
<?php
	$list = $import['options']['category_archive'];
foreach ( $list as $key => $values ) {
	$cat = get_category( $key );
	if ( ! is_a( $cat, 'WP_Term' ) ) {
		continue;
	}
	if ( ! count( $values ) ) {
		continue;
	}
	list_sidebar_replacement( $cat->name, $values );
}
	echo '</table>';
}
?>
    <?php /* special pages */ ?>
    <div class="section"><?php _e( 'Special Pages', 'custom-sidebars' ); ?></div>
    <table cellspacing="1" cellpadding="4" class="csb-export-head">
<?php
list_sidebar_replacement( __( 'Main Blog Page', 'custom-sidebars' ), $import['options']['blog'] );
list_sidebar_replacement( __( 'Date Archives', 'custom-sidebars' ), $import['options']['date'] );
list_sidebar_replacement( __( 'Author Archives', 'custom-sidebars' ), $import['options']['authors'] );
list_sidebar_replacement( __( 'Tag Archives', 'custom-sidebars' ), $import['options']['tags'] );
list_sidebar_replacement( __( 'Search Results Page', 'custom-sidebars' ), $import['options']['search'] );
?>
    </table>
    </div>
    </div>
    </h3>

    <p>
        <label for="import-config">
            <input type="checkbox" id="import-config" name="import_plugin_config" />
            <?php _e( 'Replace the current plugin configuration with the imported configuration.', 'custom-sidebars' ); ?>
        </label>
    </p>

    <input type="hidden" name="import_data" value="<?php echo esc_attr( base64_encode( json_encode( (object) $import ) ) ); ?>" />
    <p class="buttons">
        <button type="button" class="btn-cancel button-link">Cancel</button>
        <button class="button-primary btn-import"><i class="dashicons dashicons-migrate"></i> Import selected items</button>
    </p>
        <?php wp_nonce_field( 'custom-sidebars-import' ); ?>
    </form>

    <?php endif; ?>
</div>
</div>
