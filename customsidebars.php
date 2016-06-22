<?php
/* start:pro *//**
 * Plugin Name: Custom Sidebars Pro
 * Plugin URI:  http://premium.wpmudev.org/project/custom-sidebars/
 * Description: Allows you to create widgetized areas and custom sidebars. Replace whole sidebars or single widgets for specific posts and pages.
 * Version:     2.1.0.8
 * Author:      WPMU DEV
 * Author URI:  http://premium.wpmudev.org/
 * Textdomain:  custom-sidebars
 * WDP ID:      910520
 *//* end:pro *//* start:free *//**
 * Plugin Name: Custom Sidebars
 * Plugin URI:  https://wordpress.org/plugins/custom-sidebars/
 * Description: Allows you to create widgetized areas and custom sidebars. Replace whole sidebars or single widgets for specific posts and pages.
 * Version:     2.1.0.8
 * Author:      WPMU DEV
 * Author URI:  http://premium.wpmudev.org/
 * Textdomain:  custom-sidebars
 * WDP ID:      910520
 *//* end:free */

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

function inc_sidebars_init() {
	if ( class_exists( 'CustomSidebars' ) ) {
		return false;
	}

	$plugin_dir = dirname( __FILE__ );
	$plugin_dir_rel = dirname( plugin_basename( __FILE__ ) );
	$plugin_url = plugin_dir_url( __FILE__ );

	define( 'CSB_PLUGIN', __FILE__ );
	define( 'CSB_IS_PRO', /* start:pro */true,/* end:pro */false );
	define( 'CSB_LANG_DIR', $plugin_dir_rel . '/lang/' );
	define( 'CSB_VIEWS_DIR', $plugin_dir . '/views/' );
	define( 'CSB_INC_DIR', $plugin_dir . '/inc/' );
	define( 'CSB_JS_URL', $plugin_url . 'js/' );
	define( 'CSB_CSS_URL', $plugin_url . 'css/' );
	define( 'CSB_IMG_URL', $plugin_url . 'img/' );

	// Include function library.
	$modules[] = CSB_INC_DIR . 'external/wpmu-lib/core.php';
	$modules[] = CSB_INC_DIR . 'external/wdev-frash/module.php';
	$modules[] = CSB_INC_DIR . 'class-custom-sidebars.php';

	/* start:free */
	// Free-version configuration - no drip campaign yet...
	$cta_label = false;
	$drip_param = false;
	/* end:free */

	/* start:pro */
	// Pro-Only configuration.
	$cta_label = false;
	$drip_param = false;
	$modules[] = CSB_INC_DIR . 'external/wpmudev-dashboard/wpmudev-dash-notification.php';

	// WPMUDEV Dashboard.
	global $wpmudev_notices;
	$wpmudev_notices[] = array(
		'id' => 910520,
		'name' => 'Custom Sidebars Pro',
		'screens' => array(
			'widgets',
		),
	);
	/* end:pro */

	foreach ( $modules as $path ) {
		if ( file_exists( $path ) ) { require_once $path; }
	}

	// Register the current plugin, for pro and free plugins!
	do_action(
		'wdev-register-plugin',
		/*             Plugin ID */ plugin_basename( __FILE__ ),
		/*          Plugin Title */ 'CustomSidebars',
		/* https://wordpress.org */ '/plugins/custom-sidebars/',
		/*      Email Button CTA */ $cta_label,
		/*  getdrip Plugin param */ $drip_param
	);

	// Initialize the plugin
	CustomSidebars::instance();
}

inc_sidebars_init();


if ( ! class_exists( 'CustomSidebarsEmptyPlugin' ) ) {
	class CustomSidebarsEmptyPlugin extends WP_Widget {
		public function CustomSidebarsEmptyPlugin() {
			parent::__construct( false, $name = 'CustomSidebarsEmptyPlugin' );
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
} //end if class exists


// Translation.
function inc_sidebars_init_translation() {
	if ( defined( 'CSB_LANG_DIR' ) ) {
		load_plugin_textdomain(
			'custom-sidebars',
			false,
			CSB_LANG_DIR
		);
	}
}
add_action( 'plugins_loaded', 'inc_sidebars_init_translation' );
