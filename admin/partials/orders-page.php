<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wordpress.org/plugins/payo-cod
 * @since      1.0.0
 *
 * @package    Payo_Cod
 * @subpackage Payo_Cod/admin/partials
 */
global $wpdb;

$query_orders = $wpdb->prepare(
    "SELECT order_id, data, admin_panel_response, shipbill_status FROM {$wpdb->prefix}payo_orders order by id desc LIMIT %d",
    10
);

$results = $wpdb->get_results($query_orders, ARRAY_A);
$count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}payo_orders");
$max = ceil($count / 10) - 1;
?>
<div class="container">
    <div class="image-container text-center mb-3 mt-5">
        <img alt="Payo logo" src="<?php echo esc_url(plugin_dir_url( __DIR__ ) . 'images/payo_logo.png'); ?>" class="payo-logo" />
    </div>
    <div class="button-div">
        <div class="button-div-1 mb-2">
            <button type="button" class="btn btn-secondary" onClick="window.location.reload()">
                Refresh&nbsp;Data
            </button>
        </div>
        <div class="button-div-2 mb-2">
            <select class="filter-table" id="filter-dropdown" aria-label="Status Filter">
                <option value="3" selected>All Status</option>
                <option value="0">New</option>
                <option value="1">Successful</option>
                <option value="2">Rejected</option>
            </select>
            <div class="input-group form">
                <input type="text" class="form-control" id="order-id-input" placeholder="Search Order ID" aria-label="Enter Order ID" aria-describedby="button-search">
                <button class="btn btn-outline-secondary" type="button" id="search-button">Search</button>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-striped text-center align-middle">
            <thead>
                <tr>
                    <th scope="col">Order&nbsp;ID</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Delivery&nbsp;Status</th>
                    <th scope="col">Payo&nbsp;Status</th>
                    <th colspan="2" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody class="order-body lh-sm" id="orders-table">
                <?php
                    if ($results) {
                        foreach($results as $result) {
                        $data = json_decode($result['data']);
                ?>
                <tr>
                    <td><?php echo esc_html($result['order_id']); ?></td>
                    <td><?php echo esc_html($data->contact->firstname." ".$data->contact->lastname); ?></td>
                    <td><?php echo esc_html(($result['shipbill_status']) ? $result['shipbill_status'] : "-" ); ?></td>
                    <td><?php echo esc_html(($result['admin_panel_response']) ? $result['admin_panel_response'] : "New"); ?></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $result['order_id'] . '&action=edit')); ?>" target="_blank">
                            <button
                                type="button"
                                class="viewButton btn rounded-pill"
                            >
                                <i class="fas fa-eye order-icons"></i>
                                View
                            </button>
                        </a>
                    </td>
                </tr>
                <?php }} else {
                    ?> <td colspan="5">No Order(s)</td> <?php
                } ?>
            </tbody>
        </table>
    </div>
    <?php if ($max > 0) { ?>
        <div class="pagination" id="pagination">
            <button class="pagination-nav pagination-nav-prev" id="pagination-nav-prev" disabled>
                Prev
            </button>
            <button class="pagination-nav pagination-nav-next" id="pagination-nav-next">
                Next
            </button>
        </div>
    <?php } ?>
    <input type="hidden" id="admin-url" name="admin-url" value="<?php echo esc_attr(admin_url('post.php')); ?>">
    <input type="hidden" id="max-page" name="max-page" value="<?php echo esc_attr($max); ?>">
    <input type="hidden" id="current-page" name="current-page" value="0">
</div>