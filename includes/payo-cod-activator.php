<?php

global $db_version;
global $admin_panel_url;

$db_version = '1.0.0';
$admin_panel_url = 'http://cod.payo.asia';

/**
 * Fired during plugin activation
 *
 * @link       https://wordpress.org/plugins/payo-cod
 * @since      1.0.0
 *
 * @package    Payo_Cod
 * @subpackage Payo_Cod/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Payo_Cod
 * @subpackage Payo_Cod/includes
 * @author     PAYO <https://wordpress.org/plugins/payo-cod>
 */
class Payo_Cod_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;

        $plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

        if (
            in_array( $plugin_path, wp_get_active_and_valid_plugins() )
        ) {
            global $db_version;
            global $admin_panel_url;

            // Insert payo_orders table
            $table_name = $wpdb->prefix . 'payo_orders';

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                order_id varchar(255) NOT NULL,
                data longtext NOT NULL,
                admin_panel_status int,
                admin_panel_response text,
                shipbill_status text,
                external_id text,
                created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            add_option( 'payo_db_version', $db_version );
            add_option( 'admin_panel_url', $admin_panel_url );

            //Insert payo payment gateways table
            $payment_gateway_table = $wpdb->prefix . 'payo_payment_gateways';

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $payment_gateway_table (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                gateway_code varchar(255) NOT NULL,
                name varchar(255) NOT NULL,
                is_enabled tinyint(1) DEFAULT '1' NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            $gateways = WC()->payment_gateways->payment_gateways();

            foreach ( $gateways as $id => $gateway ) {
                if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled ) {
                    $wpdb->insert(
                        $wpdb->prefix . 'payo_payment_gateways',
                        array(
                            'gateway_code' => $gateway->id,
                            'name' => $gateway->title,
                        )
                    );
                }
            }

            // Add cron job for all orders
            if ( ! wp_next_scheduled ( 'payo_push_all_orders' ) ) {
                wp_schedule_event( time(), 'every_three_minutes', 'payo_push_all_orders' );
            }

        } else {
            echo esc_html('<h3>'.__('Please install and activate WooCommerce before activation.', 'ap').'</h3>');

            //Adding @ before will prevent XDebug output
            @trigger_error(__('Please install and activate WooCommerce before activation.', 'ap'), E_USER_ERROR);
        }
    }
}
