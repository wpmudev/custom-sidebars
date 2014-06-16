<?php

add_action( 'cs_init', array( 'CustomSidebarsPosts', 'instance' ) );

/**
 * Provides functionality for the "Assign pages" options section.
 *
 * @since 1.6
 */
class CustomSidebarsPosts {

	// Main instance of the custom-sidebars class.
	private $csb = null;

	/**
	 * Returns the singleton object.
	 *
	 * @since  1.6
	 */
	public static function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new CustomSidebarsPosts();
		}

		return $Inst;
	}

	/**
	 * Constructor is private -> singleton.
	 *
	 * @since  1.6
	 */
	private function __construct() {
		if ( is_admin() ) {
			$this->csb = CustomSidebars::instance();

			// Add new "Select posts" tab.
			add_action(
				'cs_additional_tabs',
				array( $this, 'show_tabs' )
			);

			// Add new "Select posts" tab.
			add_filter(
				'cs_register_tabs',
				array( $this, 'register_tab' )
			);

			// Add new "Select posts" tab.
			add_action(
				'cs_render_tab_content',
				array( $this, 'render_page' )
			);

			// Add ajax handler for the new tab.
			add_action(
				'wp_ajax_cs-assign-posts',
				array( $this, 'ajax_assign_posts' )
			);
		}
	}

	/**
	 * This is called when the tab list is displayed. We output the export-
	 * tabs at this point
	 *
	 * @since  1.6.0
	 * @param  string $active The currently active tab.
	 */
	public function show_tabs( $active ) {
		?>
		<a class="nav-tab <?php if ( 'posts' == $active ) : ?>nav-tab-active<?php endif; ?>" href="themes.php?page=customsidebars&p=posts"><?php _e( 'Assign Sidebars', CSB_LANG ); ?></a>
		<?php
	}

	/**
	 * Filter that adds the new tab-parameters to the list of recognized option
	 * pages. This filter is used in combination with the action
	 * "cs_additional_tabs" above.
	 *
	 * @since  1.6.0
	 */
	public function register_tab( $recognized_pages ) {
		$recognized_pages[] = 'posts';

		return $recognized_pages;
	}

	/**
	 * Allows us to render the export/import option pages.
	 *
	 * @since  1.6.0
	 * @param  string $active The currently active tab.
	 */
	public function render_page( $active ) {
		switch ( $active ) {
			case 'posts':
				include CSB_VIEWS_DIR . 'posts.php';
				break;
		}
	}

	/**
	 * Ajax handler: Assign sidebars to the specified posts.
	 *
	 * @since  1.6
	 */
	public function ajax_assign_posts() {
		$response = array(
			'sidebars' => array(),
			'posts' => array(),
		);
		$post_list = $this->csb->get_all_posts();
		$sidebars = $this->csb->get_modifiable_sidebars();
		$sidebar_infos = $this->csb->get_theme_sidebars( true );

		$data = array();
		foreach ( $sidebars as $sb_name ) {
			if ( isset( $_POST["sb-{$sb_name}"] ) ) {
				$replacement = $_POST["sb-{$sb_name}"];
				if ( ! empty( $replacement ) ) {
					$data[$sb_name] = $replacement;
					$response['sidebars'][$sb_name] = array(
						'class' => 'is-cust',
						'hint' => $sidebar_infos[ $replacement ]['name'],
					);
				} else {
					$response['sidebars'][$sb_name] = array(
						'class' => 'is-def',
						'hint' => __( 'Default', CSB_LANG ),
					);
				}
			}
		}

		foreach ( $post_list as $post_type => $posts ) {
			foreach ( $posts as $id => $title ) {
				if ( ! empty( $_POST["post-{$id}"] ) ) {
					$this->csb->set_post_meta( $id, $data );
					$response['posts'][] = $id;
				}
			}
		}

		echo json_encode( $response );
		die();
	}
};