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

function get_payment_gateways() {
    global $wpdb;
    $query_gateways = esc_sql("SELECT id, name, is_enabled FROM {$wpdb->prefix}payo_payment_gateways");
    return $wpdb->get_results($query_gateways, ARRAY_A);
}

?>
<div class="container">
    <div class="image-container text-center mb-3 mt-5">
        <img alt="Payo logo" src="<?php echo esc_url(plugin_dir_url( __DIR__ ) . 'images/payo_logo.png'); ?>" class="payo-logo" />
    </div>
    <div class="text-center mb-3">
        <h1 class="title">Payo COD (Cash on Delivery) App</h1>
        <h2>Payments Settings</h2>
    </div>
    <div class="form form-width mx-auto" method="post" action="">
        <div class="text-center text-md-start mb-1">
            <label>Enable the following payment type with Payo:</label>
        </div>
        <div class="row mb-4">
            <table class="table table-striped text-center">
                <thead>
                    <tr>
                        <th>Payment Type</th>
                        <th>Enable With Payo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $gateways = get_payment_gateways();
                    foreach ($gateways as $gateway) { ?>
                    <tr>
                        <td><?php echo esc_html(trim(json_encode($gateway['name']), '"')); ?></td>
                        <td>
                            <input
                                class="payment-checkbox me-1"
                                name=<?php echo esc_attr(trim(json_encode($gateway['id']), '"')); ?>
                                id=<?php echo esc_attr(trim(json_encode($gateway['id']), '"')); ?>
                                type="checkbox"
                                <?php checked(trim(json_encode($gateway['is_enabled']), '"'), 1); ?>
                            />
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="text-center button-row mt-4">
                <button name="payment-submit" class="btn btn-lg btn-secondary" id="payment-submit">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalPayment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered text-center">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fs-5">
                Successfully Updated!
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn w-100 modal-button" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>