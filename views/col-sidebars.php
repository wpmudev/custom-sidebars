<?php
/**
 * Custom column inside the post-list.
 *
 * Uses:
 *   $selected
 *   $wp_registered_sidebars
 *   $post_id
 */

$sidebars = CustomSidebars::get_options( 'modifiable' );

$is_front = get_option( 'page_on_front' ) == $post_id;
$is_blog = get_option( 'page_for_posts' ) == $post_id;

if ( $is_front || $is_blog ) {
	if ( $is_front ) {
		_e( '(Not available for Home-Page)', 'custom-sidebars' );
	} else {
		_e( '(Not available for Blog-Page)', 'custom-sidebars' );
	}
	foreach ( $sidebars as $s ) : ?>
		<span data-sidebar="<?php echo esc_attr( $s ); ?>" data-replaced="<?php echo esc_attr( @$selected[ $s ] ); ?>" data-cshide="yes">
	<?php endforeach;
} else {
	global $wp_registered_sidebars;
	$available = CustomSidebars::sort_sidebars_by_name( $wp_registered_sidebars );
	$content = '';
	foreach ( $sidebars as $s ) {
		$sb_name = $available[ $s ]['name'];
		$replaced = ! empty( $available[ $selected[ $s ] ] );
		$class = $replaced ? 'cust' : 'def';

		if ( $replaced ) {
			$content .= sprintf(
				'<dt data-sidebar="%s" data-replaced="%s" class="cs-key %s">',
				esc_attr( $s ),
				isset( $selected[ $s ] )? esc_attr( $selected[ $s ] ):'',
				esc_attr( $class, 'custom-sidebars' )
			);
			$content .= esc_html( $sb_name );
			$content .= '</dt>';
			$content .= '<dd class="cs-val">';
			$content .= esc_html( $available[ $selected[ $s ] ]['name'] );
			$content .= '</dd>';
		}
	}
	if ( empty( $content ) ) {
		echo '-';
	} else {
		echo '<dl>';
		echo $content;
		echo '</dl>';
	}
}
