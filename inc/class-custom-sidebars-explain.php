<?php

add_action( 'cs_init', array( 'CustomSidebarsExplain', 'instance' ) );

/**
 * Adds some additional information to the page output which explain why which
 * Sidebar/widgets were added to the current page.
 *
 * =================================== USAGE ===================================
 *
 * Activate the explanation mode via URL parameter: "?cs-explain=on"
 * Deactiavte by setting the parameter to "off"
 *
 * The explanation is only displayed for the user that did activate it, other
 * users will not see anything.
 *
 * Explain-mode will possibly break the layout of the page, but it makes it
 * much easier to understand which sidebars and widgets are displayed and why.
 * It is meant for temporary debugging only and should be turned off when not
 * needed anymore.
 *
 * =============================================================================
 *
 */
class CustomSidebarsExplain extends CustomSidebars {

	/**
	 * Infos added via cs_explain.
	 * @var array
	 */
	private $infos = array();

	/**
	 * Explain debug status.
	 *
	 * @since  3.0.7
	 */
	private $debug = false;

	/**
	 * Current user id
	 *
	 * @since  3.0.7
	 */
	private $current_user_id = 0;

	/**
	 * Returns the singleton object.
	 *
	 * @since  2.0.9.1
	 */
	public static function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new CustomSidebarsExplain();
		}

		return $Inst;
	}

	/**
	 * Constructor is private -> singleton.
	 *
	 * @since  2.0.9.1
	 */
	private function __construct() {
		$this->debug = false;
		$this->current_user_id = get_current_user_id();
		if ( 0 == $this->current_user_id ) {
			$this->debug = apply_filters( 'custom_sidebars_explain', $this->debug );
		} else {
			$this->debug = (boolean) get_user_meta( $this->current_user_id, 'custom_sidebars_explain', true );
			$this->set_explain();
		}
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 );
		if ( false === $this->debug ) {
			return;
		}
		if ( is_admin() ) {
			return;
		}
		add_action( 'cs_explain', array( $this, 'add_info' ), 10, 2 );
		add_action( 'wp_footer', array( $this, 'show_infos' ) );
		add_action( 'dynamic_sidebar_before', array( $this, 'before_sidebar' ), 0, 2 );
		add_action( 'dynamic_sidebar_after', array( $this, 'after_sidebar' ), 0, 2 );
		add_action( 'wp_print_styles', array( $this, 'print_styles' ) );
	}

	/**
	 * Returns true if the "explain mode" is enabled.
	 * Explain mode will display additional information in the front-end of the
	 * website on why which sidebar/widget is displayed.
	 * This is a per-user option (stored in current session)
	 *
	 * @since  2.0.9.1
	 * @return boolean
	 */
	public function do_explain() {
		return $this->debug;
	}

	/**
	 * Sets the explain state
	 *
	 * @since 2.0.9.1
	 * @param string $state [on|off]
	 */
	public function set_explain() {
		if ( ! isset( $_GET['cs-explain'] ) ) {
			return;
		}
		if ( current_user_can( 'manage_options' ) && 'on' == $_GET['cs-explain'] ) {
			$this->debug = true;
			$result = add_user_meta( $this->current_user_id, 'custom_sidebars_explain', $this->debug, true );
			if ( ! $result ) {
				update_user_meta( $this->current_user_id, 'custom_sidebars_explain', $this->debug );
			}
			return;
		}
		$this->debug = false;
		delete_user_meta( $this->current_user_id, 'custom_sidebars_explain' );
	}

	/**
	 * Adds an info to the explanation output.
	 *
	 * @since 2.0.9.1
	 */
	public function add_info( $info, $new_item = false ) {
		if ( $new_item || 0 === count( $this->infos ) ) {
			$this->infos[] = $info;
		} else {
			$this->infos[ count( $this->infos ) - 1 ] .= '<br />' . $info;
		}
	}

	/**
	 * Outputs the collected information to the webpage.
	 *
	 * @since  2.0.9.1
	 */
	public function show_infos() {
		?>
		<div class="cs-infos" style="width:600px;margin:10px auto;padding:10px;color:#666;background:#FFF;">
			<style>
			.cs-infos > ul { list-style:none; padding: 0; margin: 0; }
			.cs-infos > ul > li { margin: 0; padding: 10px 0 10px 30px; border-bottom: 1px solid #eee; }
			.cs-infos h4 { color: #600; margin: 10px 0 0 -30px; }
			.cs-infos h5 { color: #006; margin: 10px 0 0 -15px; }
			</style>
			<h3>Sidebar Infos</h3>
			<ul>
				<?php foreach ( $this->infos as $info ) : ?>
					<li><?php echo $info; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Returns a random hex color.
	 *
	 * @since  2.0.9.1
	 * @return [type] [description]
	 */
	static public function get_color() {
		$r = rand( 40, 140 );
		$g = rand( 40, 140 );
		$b = rand( 40, 140 );
		return '#' . dechex( $r ) . dechex( $g ) . dechex( $b );
	}

	/**
	 * Adds a border/title to the sidebar to better illustrate the position/ID.
	 *
	 * @since  2.0.9.1
	 */
	public function before_sidebar( $index, $has_widgets ) {
		global $wp_registered_sidebars;
		$col = self::get_color();
		$w_col = self::get_color();

		$wp_registered_sidebars[ $index ]['before_widget'] =
			'<div style="border:2px solid ' . $w_col . ';margin:2px;width:auto;clear:both">' .
			'<div style="font-size:12px;padding:1px 4px 1px 6px;float:right;background-color:' . $w_col . ';color:#FFF">%1$s</div>' .
			@$wp_registered_sidebars[ $index ]['before_widget'];
		$wp_registered_sidebars[ $index ]['after_widget'] =
			@$wp_registered_sidebars[ $index ]['after_widget'] .
			'<div style="clear:both"> </div>' .
			'</div>';
		?>
		<div style="border:2px solid <?php echo esc_attr( $col ); ?>;position:relative;">
			<div style="font-size:12px;padding:1px 4px 1px 6px;float:right;background-color:<?php echo esc_attr( $col ); ?>;margin-bottom:2px;color:#FFF"><?php echo esc_html( $index ); ?></div>
		<?php
	}

	/**
	 * Closes the border around sidebar.
	 *
	 * @since  2.0.9.1
	 */
	public function after_sidebar( $index, $has_widgets ) {
		?>
		<div style="clear:both"> </div>
		</div>
		<?php
	}

	/**
	 * Added adminbar position for debug
	 *
	 * @since 3.0.7
	 */
	public function admin_bar_menu( $wp_admin_bar ) {
		if ( is_admin() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$args = array(
			'id'    => 'cs-explain',
			'title' => __( 'Sidebar Debug', 'custom-sidebars' ),
			'href'  => add_query_arg( 'cs-explain', 'on' ),
			'parent' => 'top-secondary',
			'meta' => array(
				'title' => __( 'Turn on Custom Sidebars explain mode.', 'custom-sidebars' ),
				'class' => 'debug-is-off',
			),
		);
		if ( $this->debug ) {
			$args['href'] = add_query_arg( 'cs-explain', 'off' );
			$args['meta'] = array(
				'title' => __( 'Turn off Custom Sidebars explain mode.', 'custom-sidebars' ),
				'class' => 'cs-explain-on',
			);
		}
		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Print style for debug
	 *
	 * @since 3.0.8
	 */
	public function print_styles() {
		echo '<style type="text/css" media="screen">';
		echo '#wpadminbar .cs-explain-on{ background-color:#050}';
		echo 'html body #wpadminbar .ab-top-menu .cs-explain-on:hover>.ab-item{background-color:#251}';
		echo '</style>';
	}
};
