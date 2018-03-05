<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://developer.wordpress.org/
 * @since             1.0.0
 * @package           Static_Maker
 *
 * @wordpress-plugin
 * Plugin Name:       Static Maker
 * Plugin URI:        https://static-maker.example.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            ameyamashiro
 * Author URI:        https://developer.wordpress.org/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       static-maker
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-static-maker-activator.php
 */
function activate_static_maker() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-static-maker-activator.php';
	Static_Maker_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-static-maker-deactivator.php
 */
function deactivate_static_maker() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-static-maker-deactivator.php';
	Static_Maker_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_static_maker' );
register_deactivation_hook( __FILE__, 'deactivate_static_maker' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-static-maker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_static_maker() {

	$plugin = new Static_Maker();
	$plugin->run();

}
run_static_maker();