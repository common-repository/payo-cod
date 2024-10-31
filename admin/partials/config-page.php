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

$current_options = get_option( 'payo_cod_config', array());
?>

<div class="container">
    <div class="image-container text-center mb-3 mt-5">
        <img alt="Payo logo" src="<?php echo esc_url(plugin_dir_url( __DIR__ ) . 'images/payo_logo.png'); ?>" class="payo-logo" />
    </div>
    <div class="text-center mb-3">
        <h1 class="title">Payo COD (Cash on Delivery) App</h1>
        <h2>App Settings</h2>
    </div>
    <div class="mb-5">
        <div class="form form-width m-auto mb-3">
            <div class="text-center text-md-start mb-3">
                <label>Please provide your Payo credentials:</label>
            </div>
            <div class="row">
                <div class="col-12 col-md-8">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="client-id"
                            >Client ID</span
                        >
                        <input type="text" class="form-control" id="client_id" name="client_id" value="<?php echo esc_attr(isset($current_options['client_id']) ? $current_options['client_id'] : ""); ?>" />
                    </div>
                    <div class="input-group">
                        <span class="input-group-text" id="api-key"
                            >API Key</span
                        >
                        <input type="text" class="form-control" id="api_key" name="api_key" value="<?php echo esc_attr(isset($current_options['api_key']) ? $current_options['api_key'] : ""); ?>" />
                    </div>
                </div>
                <div class="col-12 col-md-4 m-auto d-flex justify-content-center">
                    <button id="test-credentials" type="button" class="btn btn-secondary btn-lg mt-2 mt-md-0">
                        Test&nbsp;Credentials
                    </button>
                </div>
            </div>
            <div class="form">
                <div class="text-center text-md-start mt-3 mb-2">
                    <label>Ship with Payo:</label>
                </div>
                <div class="col-8 col-md-6">
                    <select class="form-select mb-3" name="app_mode" id="app_mode">
                        <option value="0" <?php selected($current_options['app_mode'], '0'); ?>>Manual</option>
                        <option value="1" <?php selected($current_options['app_mode'], '1'); ?>>All Orders</option>
                    </select>
                </div>
            </div>
            <div class="text-center button-row mt-4">
                <button class="btn btn-lg btn-secondary" id="config-submit">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalConfig" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered text-center">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fs-5">
                Success!
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn w-100 modal-button" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>