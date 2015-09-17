<?php

	/**
	 * The plugin bootstrap file
	 *
	 * This file is read by WordPress to generate the plugin information in the plugin
	 * admin area. This file also includes all of the dependencies used by the plugin,
	 * registers the activation and deactivation functions, and defines a function
	 * that starts the plugin.
	 *
	 * @link              http://www.wildetech.com.au
	 * @since             0.1.0
	 * @package           Gmap_Zones
	 *
	 * @wordpress-plugin
	 * Plugin Name:       GMap Zones
	 * Plugin URI:        http://www.wildetech.com.au
	 * Description:       Creating Zones on Google Maps by Australian Post Code
	 * Version:           0.5.5
	 * Author:            Robert Wilde
	 * Author URI:        http://www.wildetech.com.au
	 * License:           GPL-2.0+
	 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
	 * Text Domain:       gmap-zones
	 * Domain Path:       /languages
	 */

// If this file is called directly, abort.
	if ( ! defined( 'WPINC' ) ) {
		die;
	}

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-gmap-zones-activator.php
	 */
	function activate_plugin_name() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-gmap-zones-activator.php';
		Gmap_Zones_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-gmap-zones-deactivator.php
	 */
	function deactivate_plugin_name() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-gmap-zones-deactivator.php';
		Gmap_Zones_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_plugin_name' );
	register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-gmap-zones.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    0.1.0
	 */
	function run_plugin_name() {

		$plugin = new Gmap_Zones();
		$plugin->run();

	}

	run_plugin_name();
