<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wordpress.org/plugins/payo-cod
 * @since      1.0.0
 *
 * @package    Payo_Cod
 * @subpackage Payo_Cod/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Payo_Cod
 * @subpackage Payo_Cod/includes
 * @author     PAYO <https://wordpress.org/plugins/payo-cod>
 */
class Payo_Cod {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Payo_Cod_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $payo_cod    The string used to uniquely identify this plugin.
     */
    protected $payo_cod;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'PAYO_COD_VERSION' ) ) {
            $this->version = PAYO_COD_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'payo-cod';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Payo_Cod_Loader. Orchestrates the hooks of the plugin.
     * - Payo_Cod_i18n. Defines internationalization functionality.
     * - Payo_Cod_Admin. Defines all hooks for the admin area.
     * - Payo_Cod_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/payo-cod-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/payo-cod-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/payo-cod-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/payo-cod-public.php';

        $this->loader = new Payo_Cod_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Payo_Cod_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Payo_Cod_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Payo_Cod_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_payo_menu');

        // manual ship to payo
        $this->loader->add_filter( 'bulk_actions-edit-shop_order', $plugin_admin, 'ship_orders_bulk_actions', 20, 1 );
        $this->loader->add_filter( 'handle_bulk_actions-edit-shop_order', $plugin_admin, 'ship_handle_bulk_action_edit_shop_order', 10, 3 );
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'ship_bulk_action_admin_notice' );
        // cron job
        $this->loader->add_action( 'payo_push_all_orders', $plugin_admin, 'all_orders_run' );
        // add 3 mins time on cron
        $this->loader->add_filter( 'cron_schedules', $plugin_admin, 'payo_push_all_orders' );
        // resubmit orders
        $this->loader->add_filter( 'bulk_actions-edit-shop_order', $plugin_admin, 'resubmit_orders_bulk_actions', 20, 1 );
        $this->loader->add_filter( 'handle_bulk_actions-edit-shop_order', $plugin_admin, 'resubmit_handle_bulk_action_edit_shop_order', 10, 3 );
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'resubmit_bulk_action_admin_notice' );
        // salesforce endpoint
        $this->loader->add_action( 'rest_api_init', $plugin_admin, 'init_rest_api_endpoint' );
        // test credentials
        $this->loader->add_action( 'wp_ajax_test_credentials', $plugin_admin, 'test_credentials_callback' );
        // update configs
        $this->loader->add_action( 'wp_ajax_update_configs', $plugin_admin, 'update_configs_callback' );
        // update payments
        $this->loader->add_action( 'wp_ajax_update_payments', $plugin_admin, 'update_payments_callback' );
        // filter orders
        $this->loader->add_action( 'wp_ajax_filter_orders', $plugin_admin, 'filter_orders_callback' );
        $this->loader->add_filter( 'woocommerce_checkout_get_value' , $plugin_admin,  'clear_checkout_fields' , 10, 2 );
        //modify checkout
        $this->loader->add_filter( 'woocommerce_states' , $plugin_admin, 'custom_woocommerce_states' );
        $this->loader->add_filter( 'woocommerce_default_address_fields' , $plugin_admin, 'custom_override_default_address_fields' );
        $this->loader->add_action( 'wp_ajax_get_cities', $plugin_admin, 'get_cities_callback' );
        $this->loader->add_action( 'wp_ajax_nopriv_get_cities', $plugin_admin, 'get_cities_callback' );
        $this->loader->add_action( 'wp_ajax_get_barangays', $plugin_admin, 'get_barangays_callback' );
        $this->loader->add_action( 'wp_ajax_nopriv_get_barangays', $plugin_admin, 'get_barangays_callback' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Payo_Cod_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Payo_Cod_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
