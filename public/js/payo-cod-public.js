	/**
	 * All of the code for your public-facing JavaScript source
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

	jQuery( function($) {
		$('form.checkout').on('change', '#billing_state', function(){

			var data = {
				action: "get_cities",
                province: $(this).val().split(' ').join('_'),
			};

			$.post('/wp-admin/admin-ajax.php', data, function(response) {
				$('#billing_city').empty();
				$('#billing_address_1').empty();
				try {
					var results = JSON.parse(response);

					if(results != null) {
						$('#billing_city').append(
							$('<option></option>').val("").html("")
						);
		
						$.each(results, function(val) {
							$('#billing_city').append(
								$('<option></option>').val(results[val]["name"]).html(results[val]["name"])
							);
						});
					}
				} catch (error) {
					console.error('Error parsing JSON:', error);
				}
			});

		});
		$('form.checkout').on('change', '#billing_city', function(){

			var data = {
				action: "get_barangays",
                province: $("#billing_state").val().split(' ').join('_'),
                city: $(this).val().split(' ').join('_'),
			};
			$.post('/wp-admin/admin-ajax.php', data, function(response) {
				$('#billing_address_1').empty();
				try {
					var results = JSON.parse(response);

					if(results != null) {
						$('#billing_address_1').append(
							$('<option></option>').val("").html("")
						);
		
						$.each(results, function(val) {
							$('#billing_address_1').append(
								$('<option></option>').val(results[val]["name"]).html(results[val]["name"])
							);
						});
					}
				} catch (error) {
					console.error('Error parsing JSON:', error);
				}
			});

		});
	});

