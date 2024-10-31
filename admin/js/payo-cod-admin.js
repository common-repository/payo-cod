(function( $ ) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    $(function() {
        $('#test-credentials').click(function(){
            var data = {
                action: 'test_credentials',
                client_id: $('#client_id').val(),
                api_key: $('#api_key').val(),
            };
        
            $.post(ajax_object.ajax_url, data, function(response) {
                var result = JSON.parse(response.replace(/&quot;/g,'"'))

                if (result.success) {
                    show_notif_modal('modalConfig', "Success!");
                } else {
                    show_notif_modal('modalConfig', "Failed!");
                }
            });
        });

        $('#config-submit').click(function(){
            var data = {
                action: 'update_configs',
                client_id: $('#client_id').val(),
                api_key: $('#api_key').val(),
                app_mode: $('#app_mode').val(),
            };

            $.post(ajax_object.ajax_url, data, function(response) {
                if (response == "success") {
					show_notif_modal('modalConfig', "Successfully Updated!");
				}
            });
        });

        $('#payment-submit').click(function(){
			var data = {
				action: "update_payments"
			};
			$("input[type=checkbox]")
				.each(function() {
					data[this.id] = this.checked ? 1 : 0;
				})
        
            $.post(ajax_object.ajax_url, data, function(response) {
				if (response == "success") {
					show_notif_modal('modalPayment', "Successfully Updated!");
				}
            });
        });

        $('#filter-dropdown').change(function(){
            $("#pagination-nav-prev").prop("disabled", true);
            $('#current-page').val(0);
            filter_orders();
        });

        $('#order-id-input').keyup(function(){
            if ($('#order-id-input').val() != "") {
                $("#filter-dropdown").prop("disabled", true);
            } else {
                $("#filter-dropdown").prop("disabled", false);
                filter_orders();
            }
        });

        $('#search-button').click(function(){
            if ($('#order-id-input').val() != "") {
                $("#pagination-nav-prev").prop("disabled", true);
                $('#current-page').val(0);
                $('#filter-dropdown').val(3);
                filter_orders();
            }
        });

        $('#pagination-nav-prev').click(function(){
            var page = parseInt($('#current-page').val()) - 1;
            $('#current-page').val(page);
            filter_orders();

            if (page == 0) {
                $("#pagination-nav-prev").prop("disabled", true);
            }

            $("#pagination-nav-next").prop("disabled", false);
        });

        $('#pagination-nav-next').click(function(){
            var page = parseInt($('#current-page').val()) + 1;
            $('#current-page').val(page);
            filter_orders();

            if (page == $('#max-page').val()) {
                $("#pagination-nav-next").prop("disabled", true);
            }

            $("#pagination-nav-prev").prop("disabled", false);
        });

        function filter_orders() {
			var data = {
				action: "filter_orders",
                filter: $('#filter-dropdown').val(),
                search: $('#order-id-input').val(),
                page: $('#current-page').val(),
			};
            var adminUrl = $('#admin-url').val();

            $.post(ajax_object.ajax_url, data, function(response) {
                $('#orders-table').empty();

                if(response != 0) {
                    var results = JSON.parse(response);
                    $('#max-page').val(results.max);

                    if (results.max > 0) {
                        $('#pagination').show();

                        if (results.max != $('#current-page').val()) $("#pagination-nav-next").prop("disabled", false);

                    } else {
                        $('#pagination').hide();
                    }

                    results.data.forEach(function(result) {
                        let data = JSON.parse(result.data);
                        let adminPanelResponse = "New";
                        let shipbillStatus = "-";

                        if(result.admin_panel_response) adminPanelResponse = result.admin_panel_response;
                        if(result.shipbill_status) shipbillStatus = result.shipbill_status;

                        let dataRow = (`
                            <tr>
                                <td> ${result.order_id} </td>
                                <td> ${data.contact.firstname} ${data.contact.lastname} </td>
                                <td> ${shipbillStatus} </td>
                                <td> ${adminPanelResponse} </td>
                                <td>
                                    <a href="${adminUrl}?post=${result.order_id}&action=edit" target="_blank">
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
                        `);
                        $('#orders-table').append(dataRow);
                    });
                } else {
                    let dataRow = ('<td colspan="5">No Order(s)</td>');
                    $('#orders-table').append(dataRow);
                }
            });
        }

		function show_notif_modal($modalName, $result) {
			var currentModal = new bootstrap.Modal(document.getElementById($modalName));
            $('.modal-body').text($result);

			currentModal.show();

			setTimeout(function () {
				currentModal.hide();
			}, 3000);
		}
    });

})( jQuery );
