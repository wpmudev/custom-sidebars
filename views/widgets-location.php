<?php
/**
 * Contents of the Location popup in the widgets screen.
 * User can define default locations where the custom sidebar will be used.
 *
 * This file is included in widgets.php.
 */

$sidebars = CustomSidebars::get_sidebars( 'theme' );

/**
 * Output the input fields to configure replacements for a single sidebar.
 *
 * @since  2.0
 * @param  array $sidebar Details provided by CustomSidebars::get_sidebar().
 * @param  string $prefix Category specific prefix used for input field ID/Name.
 * @param  string $cat_name Used in label: "Replace sidebar for <cat_name>".
 * @param  string $class Optinal classname added to the wrapper element.
 */
if ( ! function_exists( '_show_replaceable' ) ) {
	function _show_replaceable( $sidebar, $prefix, $cat_name, $class = '' ) {
		$base_id = 'cs-' . $prefix;
		$inp_id = $base_id . '-' . $sidebar['id'];
		$inp_name = '___cs___' . $prefix . '___' . $sidebar['id'];
		$sb_id = $sidebar['id'];
		$class = (empty( $class ) ? '' : ' ' . $class);
?>
    <div
        class="cs-replaceable <?php echo esc_attr( $sb_id . $class ); ?>"
        data-lbl-used="<?php _e( 'Replaced by another sidebar:', 'custom-sidebars' ); ?>"
        >
        <label for="<?php echo esc_attr( $inp_id ); ?>">
            <input type="checkbox"
                id="<?php echo esc_attr( $inp_id ); ?>"
                class="detail-toggle"
                />
<?php printf(
	__( 'As <strong>%1$s</strong> for selected %2$s', 'custom-sidebars' ),
	$sidebar['name'],
	$cat_name
); ?>
        </label>
        <div class="details">
            <select
                data-id="<?php echo esc_attr( $prefix ); ?>"
                class="cs-datalist <?php echo esc_attr( $base_id ); ?>"
                name="<?php echo esc_attr( $inp_name ); ?>[]"
                multiple="multiple"
                placeholder="<?php echo esc_attr(
					sprintf(
						__( 'Click here to pick available %1$s', 'custom-sidebars' ),
						$cat_name
					)
				); ?>"
            >
            </select>
        </div>
    </div>
<?php
	}
}
?>
<form class="frm-location wpmui-form">
	<input type="hidden" name="do" value="set-location" />
	<input type="hidden" name="sb" class="sb-id" value="" />

	<div class="cs-title">
		<h3 class="no-pad-top">
			<span class="sb-name">...</span>
		</h3>
	</div>
	<p class="message unique-post">
		<i class="dashicons dashicons-info light"></i>
		<?php
		printf(
			__(
				'To attach this sidebar to a unique Post or Page please visit ' .
				'that <a href="%1$s">Post</a> or <a href="%2$s">Page</a> & set it ' .
				'up via the sidebars metabox.', 'custom-sidebars'
			),
			admin_url( 'edit.php' ),
			admin_url( 'edit.php?post_type=page' )
		);
		?>
	</p>

<div class="hidden">
	<p class="message no-sidebars"><?php _e( 'There are no replaceable sidebars. Please allow at least one as replaceable.', 'custom-sidebars' ); ?></p>
</div>
	<?php
	/**
	 * =========================================================================
	 * Box 1: SINGLE entries (single pages, categories)
	 */
	?>
	<div class="wpmui-box">
		<h3>
			<a href="#" class="toggle" title="<?php _e( 'Click to toggle', 'custom-sidebars' ); ?>"><br></a>
			<span><?php _e( 'For all Single Entries matching selected criteria', 'custom-sidebars' ); ?></span>
		</h3>
		<div class="inside">
			<p><?php _e( 'These replacements will be applied to every single post that matches a certain post type or category.', 'custom-sidebars' ); ?>

			<div class="cs-half">
			<?php
			/**
			 * ========== SINGLE -- Categories ========== *
			 */
			foreach ( $sidebars as $sb_id => $details ) {
				$cat_name = __( 'categories', 'custom-sidebars' );
				_show_replaceable( $details, 'cat', $cat_name );
			}
			?>
			</div>

			<div class="cs-half">
			<?php
			/**
			 * ========== SINGLE -- Post-Type ========== *
			 */
			foreach ( $sidebars as $sb_id => $details ) {
				$cat_name = __( 'Post Types', 'custom-sidebars' );
				_show_replaceable( $details, 'pt', $cat_name );
			}
			?>
            </div>
<?php
			/**
			 * Custom Taxonomies
			 */
			$taxonomies = CustomSidebarsEditor::get_custom_taxonomies( 'allowed' );
foreach ( $taxonomies as $taxonomy_slug => $taxonomy ) {
	echo '<div class="cs-half cf-custom-taxonomies">';
	foreach ( $sidebars as $sb_id => $details ) {
		_show_replaceable( $details, $taxonomy_slug, $taxonomy->label );
	}
	echo '</div>';
}
?>
        </div>
    </div>
<?php
	/**
	 * =========================================================================
	 * Box 2: ARCHIVE pages
	 */
	?>
	<div class="wpmui-box closed">
		<h3>
			<a href="#" class="toggle" title="<?php _e( 'Click to toggle', 'custom-sidebars' );?>"><br></a>
			<span><?php _e( 'For Archives', 'custom-sidebars' ); ?></span>
		</h3>
		<div class="inside">
			<p><?php _e( 'These replacements will be applied to Archive Type posts and pages.', 'custom-sidebars' ); ?>
			<h3 class="wpmui-tabs">
				<a href="#tab-arch" class="tab active"><?php _e( 'Archive Types', 'custom-sidebars' ); ?></a>
				<a href="#tab-catg" class="tab"><?php _e( 'Category Archives', 'custom-sidebars' ); ?></a>
				<a href="#tab-aut" class="tab"><?php _e( 'Authors', 'custom-sidebars' ); ?></a>
			</h3>
			<div class="wpmui-tab-contents">
				<div id="tab-arch" class="tab active">
					<?php
					/**
					 * ========== ARCHIVE -- Special ========== *
					 */
					foreach ( $sidebars as $sb_id => $details ) {
						$cat_name = __( 'Archive Types', 'custom-sidebars' );
						_show_replaceable( $details, 'arc', $cat_name );
					}
					?>
				</div>
				<div id="tab-catg" class="tab">
					<?php
					/**
					 * ========== ARCHIVE -- Category ========== *
					 */
					foreach ( $sidebars as $sb_id => $details ) {
						$cat_name = __( 'Category Archives', 'custom-sidebars' );
						_show_replaceable( $details, 'arc-cat', $cat_name );
					}
					?>
				</div>
				<div id="tab-aut" class="tab">
					<?php
					/**
					 * ========== ARCHIVE -- Author ========== *
					 */
					foreach ( $sidebars as $sb_id => $details ) {
						$cat_name = __( 'Author Archives', 'custom-sidebars' );
						_show_replaceable( $details, 'arc-aut', $cat_name );
					}
					?>
				</div>
			</div>
		</div>
    </div>

<?php
	/**
	 * =========================================================================
	 * Box 3: SCREEN size
	 */
	?>
	<div class="wpmui-box closed csb-media-screen-width">
		<h3>
			<a href="#" class="toggle" title="<?php _e( 'Click to toggle', 'custom-sidebars' ); ?>"><br></a>
			<span><?php _e( 'For Screen Sizes', 'custom-sidebars' ); ?></span>
        </h3>
        <div class="inside">
            <p class="description"><?php _e( 'Those settings do not load unload sidebars, it only hide or show widgets, NOT SIDEBARS, depend on media screen width.', 'custom-sidebars' ); ?></p>
            <table class="form-table">
                <thead>
                    <tr>
                        <th><?php echo esc_attr_x( 'Screen', 'media screen width table', 'custom-sidebars' ); ?></th>
                        <th><?php echo esc_attr_x( 'Show',  'media screen width table', 'custom-sidebars' ); ?></th>
                        <th><?php echo esc_attr_x( 'Screen width', 'media screen width table',  'custom-sidebars' ); ?></th>
                        <th class="num"><span class="dashicons dashicons-trash"></span></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr><td colspan="3"><div class="notice notice-info inline"><p><?php esc_html_e( 'There is no defined rules.', 'custom-sidebars' ); ?></p></div></td></tr>
                </tfoot>
            </table>
            <button class="button btn-add-rule"><?php esc_html_e( 'Add new rule', 'custom-sidebars' ); ?></button>
        </div>
    </div>

	<?php
	/**
	 * =========================================================================
	 * Box 4: Plugin integration
	 */
					$integrations = apply_filters( 'custom_sidebars_integrations', array() );
	if ( ! empty( $integrations )  ) {
	?>
	<div class="wpmui-box closed cs-3rd-part">
<h3>
<a href="#" class="toggle" title="<?php _e( 'Click to toggle', 'custom-sidebars' ); ?>"><br></a>
<span><?php _e( '3rd party plugins', 'custom-sidebars' ); ?></span>
</h3>
<div class="inside">
<p><?php _e( 'These replacements will be applied to 3rd party plugins.', 'custom-sidebars' ); ?>

<h3 class="wpmui-tabs">
<?php
		$classes = array( 'tab', 'active' );
foreach ( $integrations as $id => $one ) {
	printf(
		'<a href="#tab-%s" class="%s">%s</a>',
		esc_attr( $id ),
		esc_attr( implode( ' ', $classes ) ),
		esc_html( $one['title'] )
	);
	$classes = array( 'tab' );
}
?>
</h3>
<div class="wpmui-tab-contents">
<?php
		$classes = array( 'tab', 'active' );
foreach ( $integrations as $id => $one ) {
	printf(
		'<div id="tab-%s" class="%s">',
		esc_attr( $id ),
		esc_attr( implode( ' ', $classes ) )
	);
	foreach ( $sidebars as $sb_id => $details ) {
		_show_replaceable( $details, $id, $one['cat_name'] );
	}
	echo '</div>';
	$classes = array( 'tab' );
}
?>
</div>
</div>
</div>
<?php
	}
?>

	<div class="buttons">
		<button type="button" class="button-link btn-cancel"><?php _e( 'Cancel', 'custom-sidebars' ); ?></button>
		<button type="button" class="button-primary btn-save"><?php _e( 'Save Changes', 'custom-sidebars' ); ?></button>
    </div>
    <?php wp_nonce_field( 'custom-sidebars-set-location' ); ?>
</form>
