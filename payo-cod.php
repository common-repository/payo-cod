<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wordpress.org/plugins
 * @since             2.4.0
 * @package           Payo_Cod
 *
 * @wordpress-plugin
 * Plugin Name:       Payo COD
 * Plugin URI:        https://wordpress.org/plugins/payo-cod/
 * Description:       Making E-commerce simple for online merchants
 * Version:           2.4.0
 * Author:            Payo Asia
 * Author URI:        https://payo.asia/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       payo-cod
 * Domain Path:       /languages
 * 
 * WC requires at least: 6.0
 * WC tested up to: 7.0.0
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
define( 'PAYO_COD_VERSION', '2.4.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/payo-cod-activator.php
 */
function activate_payo_cod() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/payo-cod-activator.php';
	Payo_Cod_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/payo-cod-deactivator.php
 */
function deactivate_payo_cod() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/payo-cod-deactivator.php';
	Payo_Cod_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_payo_cod' );
register_deactivation_hook( __FILE__, 'deactivate_payo_cod' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/payo-cod.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.1.0
 */
function run_payo_cod() {

	$plugin = new Payo_Cod();
	$plugin->run();

}
run_payo_cod();
