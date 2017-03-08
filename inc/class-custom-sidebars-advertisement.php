<?php

add_action( 'cs_init', array( 'CustomSidebarsAdvertisement', 'instance' ) );

/**
 * Extends the widgets section to add the advertisements.
 *
 * @since 2.2.0
 */
class CustomSidebarsAdvertisement extends CustomSidebars {

	private $dismiss_name = 'custom_sidebars_advertisement_dismiss';
	private $nonce_name = 'custom_sidebars_advertisement_nonce';

	/**
	 * Returns the singleton object.
	 *
	 * @since  2.2.0
	 */
	public static function instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new CustomSidebarsAdvertisement();
		}

		return $instance;
	}

	/**
	 * Constructor is private -> singleton.
	 *
	 * @since  2.2.0
	 */
	private function __construct() {
		if ( ! is_admin() ) {
			return;
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_head-widgets.php', array( $this, 'init_admin_head' ) );
		add_action( 'wp_ajax_custom_sidebars_advertisement_dismiss', array( $this, 'dismiss' ) );
	}

	/**
	 * Enqueue admin scripts
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'wp-util' );
	}

	/**
	 * Save dismiss decision, no more show it.
	 *
	 * @since 2.2.0
	 */
	public function dismiss() {
		/**
		 * Check: is nonce send?
		 */
		if ( ! isset( $_GET['_wpnonce'] ) ) {
			die;
		}
		/**
		 * Check: is user id send?
		 */
		if ( ! isset( $_GET['user_id'] ) ) {
			die;
		}
		/**
		 * Check: nonce
		 */
		$nonce_name = $this->nonce_name . $_GET['user_id'];
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], $nonce_name ) ) {
			die;
		}
		/**
		 * save result
		 */
		$result = add_user_meta( $_GET['user_id'], $this->dismiss_name, true, true );
		if ( false == $result ) {
			update_user_meta( $_GET['user_id'], $this->dismiss_name, true );
		}
		die;
	}

	/**
	 * Admin header
	 *
	 * @since 2.2.0
	 */
	public function init_admin_head() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notice!
	 *
	 * @since 2.2.0
	 */
	public function admin_notices() {
		$user_id = get_current_user_id();
		$state = get_user_meta( $user_id, $this->dismiss_name, true );
		if ( ! $state ) {
?>
<script type="text/javascript">
    jQuery(document).on( 'click', '.custom-sidebars-wp-checkup .notice-dismiss', function() {
        jQuery.ajax({
            url: ajaxurl,
            data: {
            action: '<?php echo esc_attr( $this->dismiss_name ); ?>',
                _wpnonce: '<?php echo wp_create_nonce( $this->nonce_name . $user_id ) ?>',
                user_id: <?php echo $user_id ?>
            }
        })
    });
</script>
<div class="notice is-dismissible custom-sidebars-wp-checkup">
<p><?php _e( '<b>Warning:</b> Some of your plugins may be slowing down your site. Run a free security and performance scan with WP Checkup.', 'custom-sidebars' ); ?></p>
<form method="get" action="https://premium.wpmudev.org/wp-checkup/">
<input type="hidden" name="external-url" value="1" />
<input type="text" name="the-url" value="<?php echo esc_url( get_option( 'home' ) ); ?>" />
<input type="submit" value="<?php esc_attr_e( 'Scan', 'custom-sidebars' ); ?>" />
<input type="hidden" name="utm_source" value="custom_sidebar_ad" />
<input type="hidden" name="utm_campaign" value="custom_sidebar_plugin" />
<input type="hidden" name="utm_medium" value="Custom Sidebars Plugin" />
</form>
	<button type="button" class="notice-dismiss">
		<span class="screen-reader-text">Dismiss this notice.</span>
	</button>
</div>
<?php
		}
		$url = add_query_arg(
			array(
				'utm_source' => 'custom_sidebar_uf_ad',
				'utm_campaign' => 'custom_sidebar_plugin_uf_ad',
				'utm_medium' => 'Custom Sidebars Plugin',
			),
			'https://premium.wpmudev.org/projects/category/themes/'
		);
?>
<script type="text/javascript">
    jQuery(document).ready( function() {
        setTimeout( function() {
            var template = wp.template('custom-sidebars-upfront');
            jQuery(".sidebars-column-1 .inner").append( template() );
        }, 1000);
    });
</script>
<script type="text/html" id="tmpl-custom-sidebars-upfront">
<div class="custom-sidebars-upfront">
    <div class="devman">
        <p><?php esc_html_e( 'Don’t just replace sidebars. Add new sidebars and footers anywhere with Upfront.', 'custom-sidebars' ); ?></p>
        <p><a class="button" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'get Upfront free', 'custom-sidebars' ); ?></a></p>
    </div>
</div>
</script>
<?php
	}
};
