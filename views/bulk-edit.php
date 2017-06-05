<?php
/**
 * Form displayed in the Quick Edit box of the post list.
 *
 * Uses:
 *   $selected
 */

global $wp_registered_sidebars;
$available = CustomSidebars::sort_sidebars_by_name( $wp_registered_sidebars );

$sidebars = CustomSidebars::get_options( 'modifiable' );
static $print_nonce = true;

if ( $print_nonce ) {
	$print_nonce = false;
	wp_nonce_field( 'bulk-edit-cs', 'custom-sidebars-editor-bulk-edit' );
}

?>
<fieldset class="inline-edit-col-left cs-quickedit">
<div class="inline-edit-col">
<label><?php _e( 'Custom Sidebars', 'ub' ); ?></label>
<?php


foreach ( $sidebars as $s ) {
	$sb_name = $available[ $s ]['name'];
	?>
	<div class="inline-edit-group">
		<label>
			<span class="title"><?php echo esc_html( $sb_name ); ?></span>
			<select name="cs_replacement_<?php echo esc_attr( $s ); ?>"
				class="cs-replacement-field <?php echo esc_attr( $s ); ?>">
                    <option value="-"><?php _e( '&mdash; No Change &mdash;', 'ub' ); ?></option>
				<?php foreach ( $available as $a ) : ?>
				<option value="<?php echo esc_attr( $a['id'] ); ?>" <?php selected( $selected[ $s ], $a['id'] ); ?>>
					<?php echo esc_html( $a['name'] ); ?>
				</option>
				<?php endforeach; ?>
			</select>
		</label>
	</div>
	<?php
}

?>
</div>
</fieldset>
