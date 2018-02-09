<?php
require_once dirname( __FILE__ ).'/class-custom-sidebars-integration.php';
add_action( 'cs_integrations', array( 'CustomSidebarsIntegrationWPML', 'instance' ) );
/**
 * Integrate sidebar locations with WPML
 *
 * @since 3.1.2
 */
class CustomSidebarsIntegrationWPML extends CustomSidebarsIntegration {

	/**
	 * Returns the singleton object.
	 *
	 * @since 3.1.2
	 */
	public static function instance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new CustomSidebarsIntegrationWPML();
		}
		return $instance;
	}

	/**
	 * Constructor is private -> singleton.
	 *
	 * @since 3.1.2
	 */
	private function __construct() {
		$languages = apply_filters( 'wpml_active_languages', array() );
		if ( empty( $languages ) ) {
			return;
		}
		$this->key_name = 'wpml';
		$this->languages = $languages;
		add_filter( 'custom_sidebars_integrations', array( $this, 'prepare' ) );
		add_filter( 'custom_sidebars_get_location', array( $this, 'get_location' ), 10, 2 );
		add_filter( 'custom_sidebars_set_location', array( $this, 'set_location' ), 10, 4 );
		add_filter( 'cs_replace_sidebars', array( $this, 'replace' ), 10, 2 );
	}

	/**
	 * Save dismiss decision, no more show it.
	 *
	 * @since 3.1.2
	 */
	public function prepare( $tabs ) {
		$tabs[ $this->key_name ] = array(
			'title' => __( 'WPML', 'custom-sidebars' ),
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
		$req->wpml = array();
		foreach ( $this->languages as $key => $lang ) {
			$req->wpml[ $key ] = array(
				'name' => isset( $lang['translated_name'] )? $lang['translated_name']:$key,
				'native' => isset( $lang['native_name'] )? $lang['native_name'] : '',
				'archive' => array(),
			);
			if (
				isset( $defaults[ $this->key_name ] )
				&& isset( $defaults[ $this->key_name ][ $key ] )
			) {
				$req->wpml[ $key ]['archive'] = $defaults[ $this->key_name ][ $key ];
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
		if ( ! isset( $options[ $this->key_name ] ) ) {
			return $replacements;
		}
		$current_language = apply_filters( 'wpml_current_language', null );
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
