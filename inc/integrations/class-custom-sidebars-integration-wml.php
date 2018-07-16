<?php
require_once dirname( __FILE__ ).'/class-custom-sidebars-integration.php';
add_action( 'cs_integrations', array( 'CustomSidebarsIntegrationWML', 'instance' ) );
/**
 * Integrate sidebar locations with WML
 *
 * @since 3.2.0
 */
class CustomSidebarsIntegrationWML extends CustomSidebarsIntegration {

	/**
	 * Returns the singleton object.
	 *
	 * @since 3.2.0
	 */
	public static function instance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new CustomSidebarsIntegrationWML();
		}
		return $instance;
	}

	/**
	 * Constructor is private -> singleton.
	 *
	 * @since 3.2.0
	 */
	private function __construct() {
		if ( ! function_exists( 'wpm_get_languages' ) ) {
			return;
		}
		$languages = wpm_get_languages();
		if ( empty( $languages ) ) {
			return;
		}
		$this->key_name = 'wml';
		$this->languages = $languages;
		add_filter( 'custom_sidebars_integrations', array( $this, 'prepare' ) );
		add_filter( 'custom_sidebars_get_location', array( $this, 'get_location' ), 10, 2 );
		add_filter( 'custom_sidebars_set_location', array( $this, 'set_location' ), 10, 4 );
		add_filter( 'cs_replace_sidebars', array( $this, 'replace' ), 10, 2 );
	}

	/**
	 * Save dismiss decision, no more show it.
	 *
	 * @since 3.2.0
	 */
	public function prepare( $tabs ) {
		$tabs[ $this->key_name ] = array(
			'title' => __( 'WP Multilang', 'custom-sidebars' ),
			'cat_name' => __( 'Language', 'custom-sidebars' ),
		);
		return $tabs;
	}

	/**
	 * Add languages
	 *
	 * @since 3.2.0
	 */
	public function get_location( $req, $defaults ) {
		$req->wml = array();
		foreach ( $this->languages as $key => $lang ) {
			$req->wml[ $key ] = array(
				'name' => isset( $lang['name'] )? $lang['name'] : '',
				'native' => isset( $lang['name'] )? $lang['name'] : '',
				'archive' => array(),
			);
			if (
				isset( $defaults[ $this->key_name ] )
				&& isset( $defaults[ $this->key_name ][ $key ] )
			) {
				$req->wml[ $key ]['archive'] = $defaults[ $this->key_name ][ $key ];
			}
		}
		return $req;
	}

	/**
	 * Replace sidebar
	 *
	 * @since 3.2.0
	 */
	public function replace( $replacements, $options ) {
		if ( ! isset( $options[ $this->key_name ] ) ) {
			return $replacements;
		}
		if ( ! function_exists( 'wpm_get_language' ) ) {
			return;
		}
		$current_language = wpm_get_language();
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
