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
/**
 * check is WooCommerce shop
 */
$is_woo_shop = intval( $post_id ) === ( function_exists( 'wc_get_page_id' )? intval( wc_get_page_id( 'shop' ) ) : 0 );
/**
 * helper function
 */
if ( ! function_exists( 'custom_sidebars_col_sideber_not_available' ) ) {
	/**
	 * local display helper
	 *
	 * @since 3.2.0
	 *
	 * @param string $page_name Page Name to display.
	 */
	function custom_sidebars_col_sideber_not_available( $page_name ) {
		$content = sprintf(
			esc_attr__( 'Not available for %s', 'custom-sidebars' ),
			$page_name
		);
		printf(
			'<small>%s</small>',
			esc_html( $content )
		);
	}
}
/**
 * prepare
 */
if ( $is_front ) {
	custom_sidebars_col_sideber_not_available( __( 'Home Page', 'custom-sidebars' ) );
} elseif ( $is_blog ) {
	custom_sidebars_col_sideber_not_available( __( 'Blog Page', 'custom-sidebars' ) );
} else if ( $is_woo_shop ) {
	custom_sidebars_col_sideber_not_available( __( 'WooCommerce Shop', 'custom-sidebars' ) );
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
