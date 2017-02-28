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
		if ( is_admin() ) {
			add_action(
				'widgets_admin_page',
				array( $this, 'widget_sidebar_content' )
			);

			add_action(
				'admin_head-widgets.php',
				array( $this, 'init_admin_head' )
            );

            add_action( 'wp_ajax_custom_sidebars_advertisement_dismiss', array( $this, 'dismiss' ) );
		}
    }

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
	 * Adds the additional HTML code to the widgets section.
	 */
    public function widget_sidebar_content() {
        l(__FUNCTION__, __CLASS__ );
	}


    public function init_admin_head() {
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    public function admin_notices() {
        $user_id = get_current_user_id();
?>
<script type="text/javascript">
    jQuery(document).on( 'click', '.custom-sidebars-wp-checkup .notice-dismiss', function() {
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'custom_sidebars_advertisement_dismiss',
            _wpnonce: '<?php echo wp_create_nonce( $this->nonce_name . $user_id ) ?>',
            user_id: <?php echo $user_id ?>
        }
    })

})
</script>
<div class="notice is-dismissible custom-sidebars-wp-checkup">
<p><?php _e( '<b>Warning:</b> Some of your plugins may be slowing down your site. Run a free security and performance scan with WP Checkup.', 'custom-sidebars' ); ?></p>
<form method="post" action="https://premium.wpmudev.org/wp-checkup/">
<input type="text" name="the-url" value="<?php echo esc_url( get_option( 'home' ) ); ?>" />
<input type="submit" value="<?php esc_attr_e( 'Scan', 'custom-sidebars' ); ?>" />
</form>
	<button type="button" class="notice-dismiss">
		<span class="screen-reader-text">Dismiss this notice.</span>
	</button>
</div>
<?php
	}


};
