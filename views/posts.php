<?php
/**
 * PRO section: Allow user to assign custom sidebars to individual posts/pages.
 */

global $wp_registered_sidebars;
$csb = CustomSidebars::instance();
$post_list = $csb->get_all_posts();
$sidebars = $csb->get_modifiable_sidebars();
$sidebar_infos = $csb->get_theme_sidebars( true );

?>

<div class="themes-php csb">
<div class="wrap">

	<?php include 'part-tabs.php'; ?>

	<div id="editsidebarpage">
		<div id="poststuff">

			<h2 class="title"><?php _e( 'Select posts', CSB_LANG ); ?></h2>

			<form action="themes.php?page=customsidebars&p=posts" method="post" class="the-form">
				<input type="hidden" name="action" value="cs-assign-posts" />
				<?php wp_nonce_field( 'custom-sidebars-posts' ); ?>


				<?php
				/**
				 * Show a full list of all posts/pages/etc.
				 */
				?>
				<div class="stuffbox csb-postlist">
				<h3><?php _e( 'Posts and pages', CSB_LANG ); ?></h3>
				<div class="inside">
				<table class="csb-list" cellspacing="0" cellpadding="0">
				<?php foreach ( $post_list as $post_type => $posts ) : ?>
					<?php $post_type_object = get_post_type_object( $post_type ); ?>
					<tr>
						<th></th>
						<th><?php _e( $post_type_object->label ); ?></th>
						<?php foreach ( $sidebars as $ind => $s ) : ?>
							<?php $hint = sprintf( __( '"%s" replacement', CSB_LANG ), $sidebar_infos[ $s ]['name'] ); ?>
							<th class="sb-col sb-col-<?php echo esc_attr( $s ); ?>" data-hint="<?php echo esc_attr( $hint ); ?>"><?php echo esc_html( 1 + $ind ); ?></th>
						<?php endforeach; ?>
					</tr>
					<?php foreach ( $posts as $post_id => $title ) : ?>
						<?php $meta = $csb->get_post_meta( $post_id ); ?>
						<tr class="post-<?php echo esc_attr( $post_id ); ?>">
							<td><input type="checkbox" name="post-<?php echo esc_attr( $post_id ); ?>" id="post-<?php echo esc_attr( $post_id ); ?>" /></td>
							<td><label for="post-<?php echo esc_attr( $post_id ); ?>"><?php echo esc_attr( $title ); ?></label></td>
							<?php foreach ( $sidebars as $s ) : ?>
								<?php
								if ( ! empty( $meta[ $s ] ) && $s != $meta[ $s ] ) {
									$cls = 'is-cust';
								} else {
									$cls = 'is-def';
								}
								if ( ! empty( $meta[ $s ] ) && $s != $meta[ $s ] ) {
									$hint = $sidebar_infos[ $meta[ $s ] ]['name'];
								} else {
									$hint = __( 'Default', CSB_LANG );
								}
								?>
								<td class="sb-col sb-col-<?php echo esc_attr( $s ); ?> <?php echo esc_attr( $cls ); ?>" data-hint="<?php echo esc_attr( $hint ); ?>"></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
				</table>
				</div>
				</div>

				<?php
				/**
				 * Show the sidebar-replacement form.
				 */
				?>
				<div class="stuffbox csb-meta">
				<h3><?php _e( 'Sidebars', CSB_LANG ); ?></h3>
				<div class="inside">
				<?php foreach ( $sidebars as $ind => $s ) : ?>
					<?php $sb_name = $wp_registered_sidebars[ $s ]['name']; ?>
					<p>
						<label for="sb-<?php echo esc_attr( $s ); ?>">
							<strong><?php echo esc_html( 1 + $ind ); ?></strong>:
							<?php echo esc_html( $sb_name ); ?>
						</label>
						<select name="sb-<?php echo esc_attr( $s ); ?>" id="sb-<?php echo esc_attr( $s ); ?>">
							<option value="">(<?php echo esc_html( $sb_name ); ?>)</option>
							<option value=""></option>
							<?php foreach ( $wp_registered_sidebars as $a ) : ?>
								<?php if ( $a['id'] == $s ) { continue; } ?>
								<option value="<?php echo esc_attr( $a['id'] ); ?>">
									<?php echo esc_html( $a['name'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</p>
				<?php endforeach; ?>
				<p>
					<div class="info-assign" style="display:none">
					<?php _e( 'Assign these sidebars to <strong><span class="val">0</span> selected post(s)</strong>', CSB_LANG ); ?>:
					</div>
					<div class="info-empty">
					<?php _e( 'Select the posts on the left side to assign sidebars to them.', CSB_LANG ); ?>
					</div>
				</p>
				<p>
					<button class="button-primary btn-save disabled"><?php _e( 'Save Changes', CSB_LANG ); ?></button>
				</p>
				</div>
				</div>


				<p class="form-buttons">
				</p>
			</form>

		</div>
	</div>


	<?php include 'part-footer.php'; ?>

</div>
</div>
