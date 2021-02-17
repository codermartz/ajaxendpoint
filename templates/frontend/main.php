<div id="ajaxendpoint_frontend_container">
	<div id="ajaxendpoint_fetched_data">
		<table id="ajaxendpoint_data_table">
			<tr id="ajaxendpoint_data_table_header"></tr>
		</table>
	</div>
	<div id="ajaxendpoint_fetch_error" class="ajaxendpoint_hidden">
		<p><?php _e('Unable to fetch data from the remote (dummy) endpoint at the moment. We received the following message from the server: ', 'ajaxendpoint'); ?><span class="ajaxendpoint_error_msg"></span></p>
	</div>
	<div id="ajaxendpoint_no_data" class="ajaxendpoint_hidden">
		<p><?php _e('No data has been fetch yet or the data has been prune recently. Login to WordPress admin panel and go to Tools->AJAXEndpoint and click the "Fetch fresh data" button to fetch a fresh set of data from the remote (dummy) endpoint.', 'ajaxendpoint'); ?></p>
	</div>
</div>