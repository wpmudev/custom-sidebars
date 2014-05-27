<?php
/*
Plugin Name: Custom Sidebars Pro
Plugin URI: http://premium.wpmudev.org/project/custom-sidebars/
Description: Allows to create your own widgetized areas and custom sidebars, and select what sidebars to use for each post or page.
Version: 1.5
Author: WPMU DEV
Author URI: http://premium.wpmudev.org/
Textdomain: csb
WDP ID: 132456
*/

/*
Copyright Incsub (http://incsub.com)
Author - Javier Marquez (http://arqex.com/)
Contributor - Philipp Stracker (Incsub)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
This plugin was originally developed by Javier Marquez.
http://arqex.com/
*/

if ( ! class_exists( 'CustomSidebars' ) ) {

	// used for more readable i18n functions: __( 'text', CSB_LANG );
	define( 'CSB_LANG', 'csb' );

	$views_dir = dirname( __FILE__ ) . '/views/';
	define( 'CSB_VIEWS_DIR', $views_dir );

	// Load the actual core.
	require_once 'inc/class-custom-sidebars.php';

} //exists class


if ( ! isset( $plugin_sidebars ) ){
	$plugin_sidebars = new CustomSidebars();
	add_action( 'widgets_init', array( $plugin_sidebars, 'registerCustomSidebars') );
	add_action( 'widgets_admin_page', array( $plugin_sidebars, 'widgetSidebarContent' ) );
	add_action( 'admin_menu', array( $plugin_sidebars, 'addSubMenus' ) );
	add_action( 'add_meta_boxes',  array( $plugin_sidebars, 'addMetaBox' ) );
	add_action( 'save_post', array( $plugin_sidebars, 'storeReplacements' ) );
	add_action( 'init', array( $plugin_sidebars, 'loadTextDomain' ) );
	add_action( 'admin_enqueue_scripts', array( $plugin_sidebars, 'addStyles' ) );

	//Frontend
	add_action( 'wp_head', array( $plugin_sidebars, 'replaceSidebars' ) );
	add_action( 'wp', array( $plugin_sidebars, 'storeOriginalPostId' ) );

    //AJAX actions
    add_action( 'wp_ajax_cs-ajax', array( $plugin_sidebars, 'ajaxHandler' ) );

    add_filter( 'admin_body_class', array( $plugin_sidebars, 'checkMP6' ) );
}

if ( ! class_exists( 'CustomSidebarsEmptyPlugin' ) ) :
	class CustomSidebarsEmptyPlugin extends WP_Widget {
		public function CustomSidebarsEmptyPlugin() {
			parent::WP_Widget( false, $name = 'CustomSidebarsEmptyPlugin' );
		}
		public function form( $instance ) {
			//Nothing, just a dummy plugin to display nothing
		}
		public function update( $new_instance, $old_instance ) {
			//Nothing, just a dummy plugin to display nothing
		}
		public function widget( $args, $instance ) {
			echo '';
		}
	} //end class
endif; //end if class exists