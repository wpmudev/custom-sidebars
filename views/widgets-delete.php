<?php
/**
 * Contents of the Delete-sidebar popup in the widgets screen.
 *
 * This file is included in widgets.php.
 */
?>

<div class="wpmui-form">
	<div>
	<?php _e(
		'Please confirm that you want to delete the sidebar <strong class="name"></strong>.', 'custom-sidebars'
	); ?>
	</div>
	<div class="buttons">
		<button type="button" class="button-link btn-cancel"><?php _e( 'Cancel', 'custom-sidebars' ); ?></button>
		<button type="button" class="button-primary btn-delete"><?php _e( 'Yes, delete it', 'custom-sidebars' ); ?></button>
	</div>
</div>
