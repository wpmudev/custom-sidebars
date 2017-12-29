<?php
require_once dirname( __FILE__ ).'/class-custom-sidebars-integration.php';
add_action( 'cs_integrations', array( 'CustomSidebarsIntegrationPolylang', 'instance' ) );
/**
 * Integrate sidebar locations with Polylang
 *
 * @since 3.1.2
 */
class CustomSidebarsIntegrationPolylang extends CustomSidebarsIntegration {

	/**
	 * Returns the singleton object.
	 *
	 * @since 3.1.2
	 */
	public static function instance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new CustomSidebarsIntegrationPolylang();
		}
		return $instance;
	}

	/**
	 * Constructor is private -> singleton.
	 *
	 * @since 3.1.2
	 */
	private function __construct() {
		if ( ! defined( 'POLYLANG_VERSION' ) ) {
			return;
		}
		$this->key_name = 'polylang';
		add_filter( 'custom_sidebars_integrations', array( $this, 'prepare' ) );
		add_filter( 'custom_sidebars_get_location', array( $this, 'get_location' ), 10, 2 );
		add_filter( 'custom_sidebars_set_location', array( $this, 'set_location' ), 10, 4 );
		add_filter( 'cs_replace_sidebars', array( $this, 'replace' ), 10, 2 );
	}

	private function check() {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return false;
		}
		if ( ! empty( $this->languages ) ) {
			return true;
		}
		$args = array(
			'raw' => true,
			'hide_if_empty' => false,
		);
		$languages = pll_the_languages( $args );
		if ( empty( $languages ) ) {
			return false;
		}
		$this->languages = $languages;
		return true;
	}

	/**
	 * Save dismiss decision, no more show it.
	 *
	 * @since 3.1.2
	 */
	public function prepare( $tabs ) {
		$tabs[ $this->key_name ] = array(
			'title' => __( 'Polylang', 'custom-sidebars' ),
			'cat_name' => __( 'Language', 'custom-sidebars' ),
		);
		return $tabs;
	}

	/**
	 * Add languages
	 *
	 * @since 3.1.2
	 */
	public function get_location( $req, $defaults ) {
		$check = $this->check();
		if ( ! $check ) {
			return $req;
		}
		$req->polylang = array();
		foreach ( $this->languages as $key => $lang ) {
			$req->polylang[ $key ] = array(
				'name' => $lang['name'],
				'archive' => array(),
			);
			if (
				isset( $defaults[ $this->key_name ] )
				&& isset( $defaults[ $this->key_name ][ $key ] )
			) {
				$req->polylang[ $key ]['archive'] = $defaults[ $this->key_name ][ $key ];
			}
		}
		return $req;
	}

	/**
	 * Replace sidebar
	 *
	 * @since 3.1.2
	 */
	public function replace( $replacements, $options ) {
		$check = $this->check();
		if ( ! $check ) {
			return $replacements;
		}
		if ( ! isset( $options[ $this->key_name ] ) ) {
			return $replacements;
		}
		if ( ! function_exists( 'pll_current_language' ) ) {
			return $replacements;
		}
		$current_language = pll_current_language();
		if ( empty( $current_language ) ) {
			return $replacements;
		}
		foreach ( $replacements as $sb_id => $replacement ) {
			if ( ! empty( $replacement ) ) {
				continue;
			}
			if (
				isset( $options[ $this->key_name ][ $current_language ] )
				&& isset( $options[ $this->key_name ][ $current_language ][ $sb_id ] )
			) {
				$replacements[ $sb_id ] = array(
					$options[ $this->key_name ][ $current_language ][ $sb_id ],
					$this->key_name,
					$current_language,
				);
			}
		}
		return $replacements;
	}
};
