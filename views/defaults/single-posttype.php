<?php
$post_types = CustomSidebars::instance()->get_post_types();
?>

<div class="defaultsSelector">

<h3 class="csh3title" title="<?php _e( 'Click to toogle',  CSB_LANG ); ?>"><?php _e( 'By post type', CSB_LANG ); ?></h3>
<div id="posttypes-default" class="meta-box-holder defaultsContainer">

	<?php foreach ( $post_types as $pt ) : $post_type_object = get_post_type_object( $pt ); ?>
	<div id="pt-<?php echo esc_attr( $pt ); ?>" class="postbox closed" >
		<h3 class='hndle'><span><?php echo esc_html( $post_type_object->label ); ?></span></h3>

		<div class="inside">
		<?php if ( ! empty( $modifiable ) ) : foreach ( $modifiable as $m ) : $sb_name = $allsidebars[$m]['name']; ?>
			<p><?php echo esc_html( $sb_name ); ?>:
				<select name="type_posts_<?php echo esc_attr( $pt ); ?>_<?php echo esc_attr( $m );?>">
					<option value=""></option>
				<?php foreach ( $allsidebars as $key => $sb ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( @$defaults['post_type_posts'][$pt][$m], $key ); ?>>
						<?php echo esc_attr( $sb['name'] ); ?>
					</option>
				<?php endforeach; ?>
				</select>
                    <?php if ( ! isset( $cs_is_defaults ) ) : ?>
                        <a href="#" class="selectSidebar"><?php printf( __( '<- Set %s here.',  CSB_LANG ), $current_sidebar['name'] ); ?></a>
                    <?php endif; ?>
			</p>
		<?php endforeach; else : ?>
			<p><?php _e( 'There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.', CSB_LANG ); ?></p>
		<?php endif;?>
		</div>

	</div>

	<?php endforeach; ?>
</div>
</div>