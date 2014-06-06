<?php
/**
 * Metabox inside posts/pages where user can define custom sidebars for an
 * individual post.
 */
?>

<p><?php _e( 'You can assign specific sidebars to this post, just select a sidebar and the default one will be replaced, if it is available on your template.', CSB_LANG )?></p>
<?php if ( ! empty( $sidebars ) ) : foreach ( $sidebars as $s ) : $sb_name = $available[ $s ]['name']; ?>
	<p><b><?php echo esc_html( $sb_name ); ?></b>:
	<select name="cs_replacement_<?php echo esc_attr( $s ); ?>">
		<option value=""></option>
		<?php foreach ( $available as $a ) : ?>
		<option value="<?php echo esc_attr( $a['id'] ); ?>" <?php selected( $selected[ $s ], $a['id'] ); ?>>
			<?php echo esc_html( $a['name'] ); ?>
		</option>
		<?php endforeach; ?>
	</select>
	</p>
<?php endforeach; else : ?>
	<p id="message" class="updated"><?php _e( 'There are not replaceable sidebars selected. You can define what sidebar will be able for replacement in the <a href="themes.php?page=customsidebars">Custom Sidebars config page</a>.', CSB_LANG ); ?></p>
<?php endif; ?>