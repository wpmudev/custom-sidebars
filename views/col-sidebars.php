<?php
/**
 * Custom column inside the post-list.
 *
 * Uses:
 *   $selected
 */

global $wp_registered_sidebars;

$available = $wp_registered_sidebars;
$sidebars = CustomSidebars::get_options( 'modifiable' );

foreach ( $sidebars as $s ) {
	$sb_name = $available[ $s ]['name'];
	$replaced = ! empty( $available[ $selected[ $s ] ] );
	$class = $replaced ? 'cust' : 'def';

	?>
	<div class="<?php echo esc_attr( $class, CSB_LANG ); ?>"
		data-sidebar="<?php echo esc_attr( $s ); ?>"
		data-replaced="<?php echo esc_attr( @$selected[ $s ] ); ?>">
		<small class="cs-key">
			<?php echo esc_html( $sb_name ); ?>
		</small>
		<span class="cs-val">
		<?php if ( $replaced ) : ?>
			<?php echo esc_html( $available[ $selected[ $s ] ]['name'] ); ?>
		<?php else : ?>
			-
		<?php endif; ?>
		</span>
	</div>
	<?php
}
