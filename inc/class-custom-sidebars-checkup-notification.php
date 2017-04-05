<?php

add_action( 'cs_init', array( 'CustomSidebarsCheckupNotification', 'instance' ) );

/**
 * Extends the widgets section to add the advertisements.
 *
 * @since 3.0.0
 */
class CustomSidebarsCheckupNotification extends CustomSidebars {

	private $dismiss_name = 'custom_sidebars_checkup_notification_dismiss';
	private $nonce_name = 'custom_sidebars_checkup_notification_nonce';

	/**
	 * Returns the singleton object.
	 *
	 * @since 3.0.0
	 */
	public static function instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new CustomSidebarsCheckupNotification();
		}

		return $instance;
	}

	/**
	 * Constructor is private -> singleton.
	 *
	 * @since 3.0.0
	 */
	private function __construct() {
		if ( ! is_admin() ) {
			return;
		}
		//add_action( 'admin_head', array( $this, 'init_admin_head' ) );
		add_action( 'admin_head-widgets.php', array( $this, 'init_admin_head_in_widgets' ) );
		add_action( 'wp_ajax_custom_sidebars_checkup_notification_dismiss', array( $this, 'dismiss' ) );
	}

	/**
	 * Save dismiss decision, no more show it.
	 *
	 * @since 3.0.0
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
	 * @since 3.0.0
	 */
	public function init_admin_head() {
		add_action( 'admin_notices', array( $this, 'admin_notice_scan' ) );
	}

	/**
	 * Admin notice scan!
	 *
	 * @since 3.0.1
	 */
	public function admin_notice_scan() {
		$user_id = get_current_user_id();
		$state = get_user_meta( $user_id, $this->dismiss_name, true );
		if ( $state ) {
			return;
		}
		lib3()->ui->add( CSB_CSS_URL . 'cs-scan.css' );
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

	/**
	 * Admin header
	 *
	 * @since 3.0.1
	 */
	public function init_admin_head_in_widgets() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notice!
	 *
	 * @since 3.0.0
	 */
	public function admin_notices() {
		wp_enqueue_script( 'wp-util' );
		$this->show_box( 'checkup' );
	}

	/**
	 * Show box.
	 *
	 * @since 3.0.4
	 *
	 * @param string $template_name Template name.
	 */
	private function show_box( $template_name ) {
		$method = sprintf( 'show_box_%s', $template_name );
		if ( ! method_exists( $this, $method ) ) {
			return;
		}
?>
<script type="text/javascript">
	jQuery(document).ready( function() {
		setTimeout( function() {
			var template = wp.template('custom-sidebars-<?php echo $template_name; ?>');
			jQuery(".sidebars-column-1 .inner").append( template() );
		}, 1000);
	});
</script>
<script type="text/html" id="tmpl-custom-sidebars-<?php echo $template_name; ?>">
<?php
		$this->$method();
?>
</script>
<?php
	}

	/**
	 * Show *Run site health check* box.
	 *
	 * @since 3.0.4
	 */
	private function show_box_checkup() {
?>
<div class="custom-sidebars-box custom-sidebars-checkup">
	<div class="cs-inner">
		<h4><?php esc_html_e( 'Run site health check', 'custom-sidebars' ); ?></h4>
		<p><?php esc_html_e( 'Free performance, security and SEO report', 'custom-sidebars' ); ?></p>
		<form method="get" action="https://premium.wpmudev.org/wp-checkup/">
			<input type="hidden" name="external-url" value="1" />
			<input type="text" name="the-url" value="<?php echo esc_url( get_option( 'home' ) ); ?>" /><input type="submit" value="<?php esc_attr_e( 'Go', 'custom-sidebars' ); ?>" />
			<input type="hidden" name="utm_source" value="custom_sidebar_ad" />
			<input type="hidden" name="utm_campaign" value="custom_sidebar_plugin" />
			<input type="hidden" name="utm_medium" value="Custom Sidebars Plugin" />
		</form>
	</div>
</div>
<?php
	}

	private function show_box_upfront() {
		$url = add_query_arg(
			array(
				'utm_source' => 'custom_sidebar_uf_ad',
				'utm_campaign' => 'custom_sidebar_plugin_uf_ad',
				'utm_medium' => 'Custom Sidebars Plugin',
			),
			'https://premium.wpmudev.org/projects/category/themes/'
		);
?>
<div class="custom-sidebars-box custom-sidebars-upfront">
	<div class="cs-inner">
		<p><?php esc_html_e( 'Don’t just replace sidebars. Add new sidebars and footers anywhere with Upfront.', 'custom-sidebars' ); ?></p>
		<p><a class="button" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'get Upfront free', 'custom-sidebars' ); ?></a></p>
	</div>
</div>
<?php
	}
};
