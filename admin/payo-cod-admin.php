<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/payo-cod
 * @since      1.0.0
 *
 * @package    Payo_Cod
 * @subpackage Payo_Cod/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Payo_Cod
 * @subpackage Payo_Cod/admin
 * @author     PAYO <https://wordpress.org/plugins/payo-cod>
 */
class Payo_Cod_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $payo_cod    The ID of this plugin.
     */
    private $payo_cod;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $payo_cod       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $payo_cod, $version ) {

        $this->payo_cod = $payo_cod;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Payo_Cod_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Payo_Cod_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->payo_cod, plugin_dir_url( __FILE__ ) . 'css/payo-cod-admin.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'payo-bootstrap-css', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Payo_Cod_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Payo_Cod_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->payo_cod, plugin_dir_url( __FILE__ ) . 'js/payo-cod-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script( $this->payo_cod, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));

        wp_enqueue_script( 'payo-bootstrap-js', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->version, false );

    }

    public function add_payo_menu() {
        add_menu_page('Payo COD', 'Payo COD', 'manage_options', 'payo-cod', '', 'dashicons-cart', 30);
        add_submenu_page('payo-cod', 'Payo COD', 'Settings', 'manage_options', 'payo-cod',  __CLASS__ .'::payo_cod_config');
        add_submenu_page('payo-cod', 'Payo COD', 'Payments', 'manage_options', 'payo-cod-payments',  __CLASS__ .'::payo_cod_payments');
        add_submenu_page('payo-cod', 'Payo COD', 'Orders', 'manage_options', 'payo-cod-orders',  __CLASS__ .'::payo_cod_orders');
        register_setting( 'payo_cod_config', 'payo_cod_config' );
    }

    public static function payo_cod_config() {
        include_once plugin_dir_path( __FILE__ ) . 'partials/config-page.php';
    }

    public static function payo_cod_payments() {
        include_once plugin_dir_path( __FILE__ ) . 'partials/payments-page.php';
    }

    public static function payo_cod_orders() {
        include_once plugin_dir_path( __FILE__ ) . 'partials/orders-page.php';
    }

    public function all_orders_run() {
        $current_options = get_option( 'payo_cod_config', array());
        if ($current_options['app_mode'] != 1) {
            return;
        }

        global $wpdb;

        $query_posts = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s AND post_status != %s and post_status = %s ORDER BY ID desc LIMIT %d",
            'shop_order',
            'trash',
            'wc-processing',
            10
        );

        $results = $wpdb->get_results($query_posts);

        foreach( $results as $result ){
            $order_id = $result->ID;

            $this->shipOrderToPayo($order_id);
        }

        $query_orders = $wpdb->prepare(
            "SELECT order_id, data FROM {$wpdb->prefix}payo_orders where admin_panel_status = %d LIMIT %d",
            0,
            10
        );

        $results = $wpdb->get_results($query_orders, ARRAY_A);

        foreach ($results as $result) {
            $response = $this->decodeResponse($this->sendOrderToPayo($result['data']));
            $this->saveAdminPanelResponse($result['order_id'], $response);
        }
    }

    function payo_push_all_orders( $schedules ) {
        $schedules['every_three_minutes'] = array(
                'interval'  => 180,
                'display'   => __( 'Every 3 Minutes', 'textdomain' )
        );
        return $schedules;
    }

    public function ship_orders_bulk_actions( $bulk_actions ) {
        $bulk_actions['ship_order'] = 'Ship Order To Payo';
        return $bulk_actions;
    }

    public function ship_handle_bulk_action_edit_shop_order( $redirect_to, $action, $post_ids ) {
        if ( $action !== 'ship_order' )
            return $redirect_to;

        $processed_ids = array();

        foreach ( $post_ids as $post_id ) {
            if ($this->shipOrderToPayo($post_id, 'manual')) {
                $processed_ids[] = $post_id;
            }
        }

        return $redirect_to = add_query_arg( array(
            'ship_order' => '1',
            'processed_count' => count( $processed_ids ),
            'processed_ids' => implode( ',', $processed_ids ),
        ), $redirect_to );
    }

    public function ship_bulk_action_admin_notice() {
        if ( empty( $_REQUEST['ship_order'] ) ) return;

        $count = intval( $_REQUEST['processed_count'] );

        printf( '<div id="message" class="notice notice-success is-dismissible"><p>' .
            _n( 'Processed %s Order.',
            'Processed %s Orders',
            $count,
            'ship_order'
        ) . '</p></div>', $count );
    }

    public function resubmit_orders_bulk_actions( $bulk_actions ) {
        $bulk_actions['resubmit_order'] = 'Resubmit Order To Payo';
        return $bulk_actions;
    }

    public function resubmit_handle_bulk_action_edit_shop_order( $redirect_to, $action, $post_ids ) {
        if ( $action !== 'resubmit_order' )
            return $redirect_to;

        $processed_ids = array();

        foreach ( $post_ids as $post_id ) {
            if ($this->resubmitOrderToPayo($post_id)) {
                $processed_ids[] = $post_id;
            }
        }

        return $redirect_to = add_query_arg( array(
            'ship_order' => '1',
            'processed_count' => count( $processed_ids ),
            'processed_ids' => implode( ',', $processed_ids ),
        ), $redirect_to );
    }

    public function resubmit_bulk_action_admin_notice() {
        if ( empty( $_REQUEST['resubmit_order'] ) ) return;

        $count = intval( $_REQUEST['processed_count'] );

        printf( '<div id="message" class="notice notice-success is-dismissible"><p>' .
            _n( 'Processed %s Order.',
            'Processed %s Orders',
            $count,
            'ship_order'
        ) . '</p></div>', $count );
    }

    function init_rest_api_endpoint() {
        $this->add_salesforce_endpoint();
        $this->add_wms_endpoint();
    }

    public function add_salesforce_endpoint() {
        register_rest_route( 'salesforce', '/order/status', array(
            'methods' => 'POST',
            'callback' => [ $this, 'update_delivery_status' ],
            'permission_callback' => '__return_true'
        ));
    }

    public function update_delivery_status(WP_REST_Request $request) {
        global $wpdb;

        $externalId = $request['external_id'];

        if($this->shipbillNotExisting($externalId)) {
            $response = $this->formSFResponse(false, $externalId);

            return $response;
        } else {
            $wpdb->update( 
                $wpdb->prefix . 'payo_orders',
                array( 
                    'shipbill_status' => $request['shipbill_status'],
                ),
                [ 'external_id' => $request['external_id'] ],
            );

            $response = $this->formSFResponse(true, $externalId);
            
            return $response;
        }
    }

    public function add_wms_endpoint() {
        register_rest_route( 'wms', '/inventory/update', array(
            'methods' => 'POST',
            'callback' => [ $this, 'update_inventory_stock' ],
            'permission_callback' => '__return_true'
        ));
    }

    public function update_inventory_stock(WP_REST_Request $request) {
        global $wpdb;

        $result['shop'] = home_url();
        $data['id'] = $request['id'];
        $data['sku'] = $request['sku'];
        $data['qty'] = $request['qty'];

        $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $data['sku'] ) );

        if($product_id) {
            $updated = wc_update_product_stock($product_id, $data['qty'], 'set', false);

            if($updated) {
                $result['status'] = 200;
                $result['message'] = 'success'; 
            } else {
                $result['status'] = 500;
                $result['message'] = 'Update quantity failed.'; 
            }
        } else {
            $result['status'] = 500;
            $result['message'] = 'No product id found using SKU: '. $data['sku']; 
        }

        return array($result);
    }

    private function mapOrderData($order) {
        $current_options = get_option('payo_cod_config', array());
        $orderData = $order->get_data();
        $paymentMethod = $order->get_payment_method();
        $deliveryType = $order->get_shipping_method();
        $note = $order->get_customer_note();
        $states = WC()->countries->get_states( $order->get_shipping_country() );
        $data = [];

        $data['contact']['email'] = $orderData['billing']['email'];

        if (isset($orderData['shipping'])) {
            $data['contact']['firstname'] = $orderData['shipping']['first_name'];
            $data['contact']['lastname'] = $orderData['shipping']['last_name'];
            $data['contact']['phone']   =  $orderData['billing']['phone'];

            $data['shipping']['country'] = "Philippines";
            $data['shipping']['state'] = $orderData['shipping']['state'];
            $data['shipping']['city'] = $orderData['shipping']['city'];
            $data['shipping']['barangay'] = $orderData['shipping']['address_1'];
            $data['shipping']['street'] = $orderData['shipping']['address_2'];
            $data['shipping']['zip'] = $orderData['shipping']['postcode'];

        } else {
            $data['contact']['firstname'] = $orderData['billing']['first_name'];
            $data['contact']['lastname'] = $orderData['billing']['last_name'];
            $data['contact']['phone']   =  $orderData['billing']['phone'];

            $data['shipping']['country'] = "Philippines";
            $data['shipping']['state'] = $orderData['billing']['state'];
            $data['shipping']['city'] = $orderData['billing']['city'];
            $data['shipping']['barangay'] = $orderData['billing']['address_1'];
            $data['shipping']['street'] = $orderData['billing']['address_2'];
            $data['shipping']['zip'] = $orderData['billing']['postcode'];

        }

        $description = "";
        $items = [];

        foreach ($order->get_items() as $item_key => $item ):
            $product = $item->get_product();
            $item_quantity = $item->get_quantity();
            $sku = empty($product->get_sku()) ? "-" : $product->get_sku();
            if ($item_quantity > 0) {
                $items[] = [
                    'id' => '6x1309936',
                    'price' => $product->get_price(),
                    'quantity' => $item_quantity,
                    'sku' => $sku,
                ];

                $description .= $item_quantity." x ". $item->get_name();
                $description .= "; \n";
            }
        endforeach;

        $data['items'] = $items;
        $data['charges'] = $order->get_total_shipping();

        if (stripos($paymentMethod, "xendit") !== false) {

            $data['shipping_method'] = "xendit";

            if(stripos($paymentMethod, "gcash") !== false) {
                $data['xendit_payment_type'] = "PH_GCASH";
            } else if(stripos($paymentMethod, "maya") !== false) {
                $data['xendit_payment_type'] = "PH_PAYMAYA";
            } else if(stripos($paymentMethod, "grab") !== false) {
                $data['xendit_payment_type'] = "PH_GRABPAY";
            } else if(stripos($paymentMethod, "shopee") !== false) {
                $data['xendit_payment_type'] = "PH_SHOPEE";
            } else if(stripos($paymentMethod, "bpi") !== false) {
                $data['xendit_payment_type'] = "PH_BPI";
            } else if(stripos($paymentMethod, "ubp") !== false) {
                $data['xendit_payment_type'] = "PH_UBP";
            }
            else {
                $data['xendit_payment_type'] = "";
            }

        } else {
            $data['shipping_method'] = $paymentMethod;
        }
        $data['delivery_type'] = $this->checkDeliveryType($deliveryType);
        $data['order_id'] = (string) $orderData['id'];
        $data['description'] = $description;
        $data['package_notes_1'] = $note;

        $data['client_id'] = $current_options['client_id'];
        $data['signature'] = $this->getSignature($data, $current_options['api_key']);
        $data['source'] = 3;
        $data['woocommerce_url'] = get_site_url();

        return $data;
    }

    private function checkDeliveryType($data) {
        if(stripos($data, "Same-Day") !== false) {
            return "Same Day - On Demand";
        } else if (stripos($data, "Next-Day") !== false) {
            return "Same Day - Pre Booked";
        }
        return "Standard";
    }

    private function getSignature($data, $apiKey) {
        $signStr = $data['contact']['email'];
        $signStr .= $data['contact']['firstname'];
        $signStr .= $data['contact']['lastname'];
        $signStr .= count($data['items']);
        $signStr .= $data['client_id'];
        $signStr .= $apiKey;
        $signInfo = hash('sha256', $signStr);
        return $signInfo;
    }

    private function shipOrderToPayo($post_id, $mode = NULL) {
        global $wpdb;

        $order = wc_get_order( $post_id );
        $order_data = $order->get_data();
        $id = $order_data['id'];
        $gateway_code = $order->get_payment_method();

        $query_gateways = $wpdb->prepare(
            "SELECT is_enabled FROM {$wpdb->prefix}payo_payment_gateways WHERE gateway_code = %s",
            $gateway_code
        );

        $is_enabled = $wpdb->get_var($query_gateways);

        if ($is_enabled == null) {
            $is_enabled = $this->checkPaymentIfEnabled($gateway_code);
        }

        if ($this->orderNotExisting($id) && ($is_enabled != null && $is_enabled === "1")) {
            $data = json_encode($this->mapOrderData($order));
        
            $wpdb->insert(
                $wpdb->prefix . 'payo_orders', 
                array( 
                    'order_id' => $id, 
                    'data' => $data,
                    'admin_panel_status' => 0,
                    'created_at' => $order_data['date_created']->date('Y-m-d H:i:s'),
                    'updated_at' => $order_data['date_modified']->date('Y-m-d H:i:s'), 
                ) 
            );

            if ($mode == 'manual') {
                $response = $this->decodeResponse($this->sendOrderToPayo($data));
                $this->saveAdminPanelResponse($id, $response);
            }

            return true;
        } else {
            return false;
        }
    }

    private function checkPaymentIfEnabled($gateway_code) {
        global $wpdb;

        $gateways = WC()->payment_gateways->payment_gateways();

        foreach ( $gateways as $id => $gateway ) {
            if ($id == $gateway_code) {
                if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled ) {
                    $wpdb->insert(
                        $wpdb->prefix . 'payo_payment_gateways',
                        array(
                            'gateway_code' => $gateway->id,
                            'name' => $gateway->title,
                        )
                    );

                    return "1";
                } else {
                    return null;
                }
            }
        }

        return null;
    }

    private function orderNotExisting($id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'payo_orders';

        $query_orders = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}payo_orders WHERE order_id = %d",
            $id
        );
        $result = $wpdb->get_var($query_orders);

        return $result == NULL;
    }

    private function shipbillNotExisting($id) {
        global $wpdb;

        $query_orders = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}payo_orders WHERE external_id = %d",
            $id
        );
        $result = $wpdb->get_var($query_orders);

        return $result == NULL;
    }

    private function formSFResponse($result, $id) {
        $data = array(
            'success' => $result,
            'external_id' => $id,
        );
        $response = new WP_REST_Response( $data );

        return $response;
    }

    private function resubmitOrderToPayo($post_id) {
        global $wpdb;

        $order = wc_get_order( $post_id );
        $order_data = $order->get_data();
        $id = $order_data['id'];

        if ($this->orderIsRejected($id)) {
            $data = json_encode($this->mapOrderData($order));

            $wpdb->update( 
                $wpdb->prefix . 'payo_orders', 
                array(
                    'admin_panel_status' => 0,
                    'admin_panel_response' => '',
                    'updated_at' => $order_data['date_modified']->date('Y-m-d H:i:s'), 
                ),
                [ 'order_id' => $id ],
            );

            $response = $this->decodeResponse($this->sendOrderToPayo($data, 'update'));
            $this->saveAdminPanelResponse($id, $response);

            return true;
        } else {
            return false;
        }
    }

    private function orderIsRejected($id) {
        global $wpdb;

        $query_orders = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}payo_orders WHERE order_id = %d AND admin_panel_status = %d",
            $id,
            2
        );
        $result = $wpdb->get_var($query_orders);

        return $result;
    }

    private function decodeResponse($data) {
        return json_decode(wp_remote_retrieve_body($data));
    }

    private function sendOrderToPayo($data, $mode = NULL) {
        $url = '';
        if ($mode == 'update') {
            $url = get_option('admin_panel_url') . '/order/update';
        } else {
            $url = get_option('admin_panel_url') . '/order/create';
        }

        $options = [
            'body'        => $data,
            'headers'     => [
                'Content-Type' => 'application/json',
            ],
        ];

        return wp_remote_post($url, $options);
    }

    private function saveAdminPanelResponse($id, $response) {
        global $wpdb;

        if($response->success) {
            $wpdb->update( 
                $wpdb->prefix . 'payo_orders',
                array( 
                    'admin_panel_status' => 1, 
                    'admin_panel_response' => 'Invoice #' . $response->invoice_no,
                    'external_id' => $response->invoice_no,
                ),
                [ 'order_id' => $id ],
            );

            $order = new WC_Order($id);
            $order->update_status('completed', 'Shipped to Payo: ');
        } else {
            $wpdb->update( 
                $wpdb->prefix . 'payo_orders',
                array( 
                    'admin_panel_status' => 2, 
                    'admin_panel_response' => $response->error,
                ),
                [ 'order_id' => $id ],
            );
        }
    }

    public function test_credentials_callback() {
        $clientId = sanitize_text_field( $_POST['client_id'] );
        $apiKey = sanitize_text_field( $_POST['api_key'] );
        $salt = md5(rand(1000000000, 9999999999));
        $token = $salt . hash("SHA256", $salt . $apiKey);

        $params = [
            "client_id" => $clientId,
            "token" => $token
        ];

        $url = get_option('admin_panel_url') . '/api/check';

        $options = [
            'body'        => $params,
            'headers'     => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];

        echo esc_html(wp_remote_retrieve_body(wp_remote_post($url, $options)));

        wp_die();
    }

    public function update_payments_callback() {
        global $wpdb;
        $success = 0;
        $query_gateways = $wpdb->prepare("SELECT id, name, is_enabled FROM {$wpdb->prefix}payo_payment_gateways");
        $gateways = $wpdb->get_results($query_gateways, ARRAY_A);

        foreach ($gateways as $gateway) {
            $id = trim(json_encode($gateway['id']), '"');
            $value = sanitize_text_field( $_POST[$id] );
    
            if ($value != trim(json_encode($gateway['is_enabled']), '"')) {
                $wpdb->update( 
                    $wpdb->prefix . 'payo_payment_gateways', 
                    array(
                        'is_enabled' => $value,
                        'updated_at' => date('Y-m-d H:i:s'), 
                    ),
                    [ 'id' => $id ],
                );
                $success = 1;
            }
        }

        if ($success === 1) {
            echo esc_html("success");
        }
        wp_die();
    }

    public function update_configs_callback() {
        $success = 0;
        $current_options = get_option('payo_cod_config', array());

        $new_client_id = sanitize_text_field( $_POST['client_id'] );
        $new_api_key = sanitize_text_field( $_POST['api_key'] );
        $new_app_mode = sanitize_text_field( $_POST['app_mode'] );

        $newValues = array(
            "client_id" => $new_client_id,
            "api_key" => $new_api_key,
            "app_mode" => $new_app_mode,
        );

        if ($current_options['client_id'] != $new_client_id || $current_options['api_key'] != $new_api_key || $current_options['app_mode'] != $new_app_mode) {
            $success = 1;
        }

        $merged_options = wp_parse_args($newValues, $current_options);
        update_option('payo_cod_config', $merged_options);

        if ($success === 1) {
            echo esc_html("success");
        }

        wp_die();
    }

    public function filter_orders_callback() {
        global $wpdb;

        $data = 0;
        $filter = sanitize_text_field( $_POST['filter'] );
        $search = sanitize_text_field( $_POST['search'] );
        $page = sanitize_text_field( $_POST['page'] * 10 );
        $search_clause = "WHERE";
        $filter_query = "";
        $search_query = "";
        $query_orders = "";
        $query_orders_count = "";
        
        $sql = "SELECT order_id, data, admin_panel_response, shipbill_status FROM {$wpdb->prefix}payo_orders";
        $sql_count = "SELECT count(*) FROM {$wpdb->prefix}payo_orders";

        if($filter != 3) {
            if($search) {
                $query_orders = $wpdb->prepare(
                    $sql . " WHERE admin_panel_status = %d AND order_id = %s order by id desc LIMIT %d OFFSET %d",
                    $filter,
                    $search,
                    10,
                    $page
                );
                $query_orders_count = $wpdb->prepare(
                    $sql_count . " WHERE admin_panel_status = %d AND order_id = %s",
                    $filter,
                    $search
                );
            } else {
                $query_orders = $wpdb->prepare(
                    $sql . " WHERE admin_panel_status = %d order by id desc LIMIT %d OFFSET %d",
                    $filter,
                    10,
                    $page
                );
                $query_orders_count = $wpdb->prepare(
                    $sql_count . " WHERE admin_panel_status = %d",
                    $filter
                );
            }
        } else {
            if($search) {
                $query_orders = $wpdb->prepare(
                    $sql . " WHERE order_id = %s order by id desc LIMIT %d OFFSET %d",
                    $search,
                    10,
                    $page
                );
                $query_orders_count = $wpdb->prepare(
                    $sql_count . " WHERE order_id = %s",
                    $search
                );
            } else {
                $query_orders = $wpdb->prepare(
                    $sql . " order by id desc LIMIT %d OFFSET %d",
                    10,
                    $page
                );
                $query_orders_count = $wpdb->prepare(
                    $sql_count
                );
            }
        }

        $count = $wpdb->get_var($query_orders_count);
        $max = ceil($count / 10) - 1;

        $result['data'] = $wpdb->get_results($query_orders, ARRAY_A);
        $result['max'] = $max;

        if($result['data']) $data = json_encode($result);

        print_r($data);

        wp_die();
    }

    function custom_woocommerce_states( $states ) {

        $url = get_option('admin_panel_url') . '/api/locations/provinces';

        $options = [
            'headers'     => [
                'Content-Type' => 'application/json',
            ],
        ];
        $response = wp_remote_get($url, $options);

        $results = json_decode($response['body'], true);

        $states['PH'] = array();

        foreach($results as $result) {
            $states['PH'][$result['name']] = $result['name'];
        }
        

      
        return $states;
      }

    // Our hooked in function - $fields is passed via the filter!
    function custom_override_default_address_fields($address_fields) {

        $address_fields['state']['label'] = 'Province';
        $address_fields['city']['label'] = 'City';
        $address_fields['address_1']['label'] = 'Barangay';
        $address_fields['address_2']['label'] = 'Street';
        $address_fields['address_1']['required'] = true;
        $address_fields['address_2']['required'] = true;
        unset($address_fields['country']);

        $address_fields['city']['priority'] = '81';
        $address_fields['address_1']['priority'] = '82';
        $address_fields['city']['type'] = 'select';
        $address_fields['address_1']['type'] = 'select';
        $address_fields['state']['placeholder'] = 'Select a Province';
        $address_fields['city']['placeholder'] = 'Select a City';
        $address_fields['address_1']['placeholder'] = 'Select a Barangay';
        $address_fields['city']['input_class'] = array('state_select');
        $address_fields['address_1']['input_class'] = array('state_select');

        $address_fields['city']['options'] = array(
            '' => ''
          );

        $address_fields['address_1']['options'] = array(
            '' => ''
        );

        return $address_fields;
    }

    public function get_cities_callback() {
        global $wpdb;

        // $data = 0;
        $province = sanitize_text_field(str_replace('_', ' ',$_POST['province']));

        $url = get_option('admin_panel_url') . '/api/locations';

        $province_url = $url . '/provinces/name/'. $province;
        $response = wp_remote_get($province_url, $options);
        $results = json_decode($response['body'], true);
        $code = $results[0]['code'];

        $cities_url = $url . '/provinces/'. $code . '/cities';
        $response = wp_remote_get($cities_url, $options);

        print_r($response['body']);

        wp_die();
    }    
    
    public function get_barangays_callback() {
        global $wpdb;

        // $data = 0;
        $province = sanitize_text_field(str_replace('_', ' ',$_POST['province']));
        $city = sanitize_text_field(str_replace('_', ' ',$_POST['city']));

        $url = get_option('admin_panel_url') . '/api/locations';

        $cities_url = $url . '/provinces/'. $province . '/cities/' . $city;
        $response = wp_remote_get($cities_url, $options);
        $results = json_decode($response['body'], true);
        $code = $results[0]['code'];

        $barangays_url = $url . '/cities/'. $code . '/brgy';
        $response = wp_remote_get($barangays_url, $options);

        print_r($response['body']);

        wp_die();
    }

    function clear_checkout_fields( $value, $input ){
        if( $input == 'select2-billing_state-container' )
            $value = '';

        return $value;
    }
}