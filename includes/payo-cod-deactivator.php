<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://wordpress.org/plugins/payo-cod
 * @since      1.0.0
 *
 * @package    Payo_Cod
 * @subpackage Payo_Cod/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Payo_Cod
 * @subpackage Payo_Cod/includes
 * @author     PAYO <https://wordpress.org/plugins/payo-cod>
 */
class Payo_Cod_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'payo_payment_gateways';

		$sql = "DROP TABLE IF EXISTS $table_name";

		$wpdb->query($sql);

		delete_option( 'payo_db_version' );
        delete_option( 'admin_panel_url' );
		wp_clear_scheduled_hook( 'add_every_three_minutes' );
		wp_clear_scheduled_hook( 'payo_push_all_orders' );
	}

}
