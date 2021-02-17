jQuery(document).ready(function($) {
	init();

	/**
	 * Retrieves data from our WP ajax endpoint ('../wp-json/ajaxendpoint/v1/dummy-data/')
	 *
	 * @return {void}
	 */
	function init() {
		$.ajax({
			type: 'GET',
			url: ajaxendpoint.rest_url + 'ajaxendpoint/v1/dummy-data/',
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', ajaxendpoint.rest_nonce );
			},
			data: {},
			dataType: 'json',
			success: function(response) {
				if (response) {
					render_response(response);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				// We log everything into the browser console for easier debugging, in case
				// an error occurs while processing the request.
		        console.log(textStatus);
		        console.log(errorThrown);
		        console.log(jqXHR);
		    }
		});
	}

	/**
	 * Renders the fetched data in a table
	 *
	 * @param {object} response The response object we received from our AJAX endpoint
	 *
	 * @return {void}
	 */
	function render_response(response) {
		if (response.hasOwnProperty('error') && response.message.length) {
			$('#ajaxendpoint_fetch_error span.ajaxendpoint_error_msg').html(response.message);
			$('#ajaxendpoint_fetch_error').show();
		} else {
			if (response.hasOwnProperty('status') && 'success' == response.status) {
				var data = response.data;
				if ($.isArray(data)) {
					if (data.length) {
						for (var p in data[0]) {
							$('<th/>', {
								text: p.replace('_', ' '),
								class: 'ajaxendpoint_header_field'
							}).appendTo('tr#ajaxendpoint_data_table_header');
						}

						for (var i=0; i<data.length; i++) {
							var row = $('<tr/>', { class: 'ajaxendpoint_data_row' });
							for (var p in data[i]) {
								$('<td/>', {
									text: data[i][p],
									class: 'ajaxendpoint_data_field'
								}).appendTo(row);
							}
							row.appendTo('table#ajaxendpoint_data_table');
						}
					}
				}
			} else {
				$('#ajaxendpoint_no_data').show();
			}
		}
	}
});