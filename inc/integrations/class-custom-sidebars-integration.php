<?php
/**
 * Integrate sidebar locations
 *
 * @since 3.1.2
 */
abstract class CustomSidebarsIntegration {

	protected $languages = array();
	protected $key_name = '';

	abstract public function prepare( $tabs );
	abstract public function replace( $replacements, $options );
	abstract public function get_location( $req, $defaults );

	/**
	 * It should be abstract static function, but ...
	 * https://bugs.php.net/bug.php?id=53081
	 */
	public static function instance() {}

	/**
	 * Set languages
	 *
	 * @since 3.1.2
	 *
	 * @param array $options Current save option.
	 * @param string $id Sidebar
	 */
	public function set_location( $options, $id, $sidebars, $data ) {
		$options[ $this->key_name ] = array();
		foreach ( $sidebars as $sb_id ) {
			if ( isset( $data[ $this->key_name ] ) ) {
				foreach ( $data[ $this->key_name ] as $sb_id => $value ) {
					if ( ! isset( $options[ $this->key_name ] ) ) {
						$options[ $this->key_name ] = array();
					}
					foreach ( $value as $lang ) {
						$options[ $this->key_name ][ $lang ][ $sb_id ] = $id;
					}
				}
			}
		}
		return $options;
	}
};
