<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('AJAX Endpoint Solution', 'ajaxendpoint'); ?></h1>
	<h3><?php _e('This solution demonstrates caching of data from a remote source with RESTful consumption (done locally) from an ajax request.', 'ajaxendpoint'); ?></h3>
	<div id="ajaxendpoint_admin_container">
		<?php if ( isset( $data['error'] ) ) { ?>
			<div id="ajaxendpoint_fetch_error">
				<p><?php _e('Unable to fetch data from the remote (dummy) endpoint at the moment. We received the following message from the server: ', 'ajaxendpoint'); ?><span class="ajaxendpoint_error_msg"><?php echo $data['message']; ?></span></p>
			</div>
		<?php } ?>
		<?php if ( ! empty( $data ) && ! isset( $data['error'] ) ) { ?>
			<table id="ajaxendpoint_data_table">
				<tr id="ajaxendpoint_data_table_header">
					<?php foreach ($data[0] as $key => $value) { ?>
						<th class="ajaxendpoint_header_field"><?php echo str_replace('_', ' ', $key); ?></th>
					<?php } ?>
				</tr>
				<?php foreach ($data as $item) { ?>
					<tr class="ajaxendpoint_data_row">
						<?php foreach ($item as $key => $value) { ?>
							<td class="ajaxendpoint_data_field"><?php echo $value; ?></td>
						<?php } ?>
					</tr>
				<?php } ?>
			</table>
		<?php } else { ?>
			<div id="ajaxendpoint_no_data">
				<p><?php _e('No data has been fetch yet or the data has been prune recently. Click the "Fetch fresh data" button to fetch and load a fresh copy of the remote data from the dummy endpoint.', 'ajaxendpoint'); ?></p>
				<p><i><?php _e('N.B. If for some unexpected reason the remote source is no longer available or keeps on responding with "Too Many Requests" you can always use a different source that returns a JSON formatted response having the same structure shown below. Just change the DUMMY_ENDPOINT constant from the ajaxendpoint.php file and this plugin will automatically render the received information. The data fields can be anything as long as they are key-value pairs.', 'ajaxendpoint'); ?></i></p>
				<p>
				<blockquote id="ajaxendpoint_sample_data_format">
					<pre>
					{
					"status": "success",
					"data": [
						{
						"id": "1",
						"employee_name": "John Doe",
						"employee_salary": "320800",
						"employee_age": "41",
						"profile_image": ""
						},
						....
						]
					}
					</pre>
				</blockquote>
				</p>
			</div>
		<?php } ?>
		<div id="ajaxendpoint_buttons">
			<form method="POST">
				<input type="hidden" name="submit" value="1" />
				<button name="fetch" class="button button-primary ajaxendpoint-fetch-data"><?php _e('Fetch fresh data', 'ajaxendpoint'); ?></button>
				<button name="prune" class="button ajaxendpoint-prune-cache" <?php if ( empty( $data ) || isset( $data['error'] ) ) echo 'disabled'; ?>><?php _e('Prune cache', 'ajaxendpoint'); ?></button>
			</form>
		</div>
	</div>
</div>';