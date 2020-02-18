<div id="learndash-settings-support" class="learndash-settings">
	<h1><?php _e( 'Status', 'learndash-notifications' ); ?></h1>

	<table cellspacing="0" class="learndash-support-settings">
		<thead>
			<tr>
				<th scope="col" class="learndash-support-settings-left"><?php _e( 'Key', 'learndash-notifications' ) ?></th>
				<th scope="col" class="learndash-support-settings-right"><?php _e( 'Value', 'learndash-notifications' ) ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th scope="row"><?php _e( 'Server Cron Setup', 'learndash-notifications' )  ?></th>
				<td>
					<?php echo isset( $values['cron_setup'] ) && $values['cron_setup'] == 'true' ? __( 'Yes', 'learndash-notifications' ) : '<span style="color:red;">' . __( 'Not detected', 'learndash-notifications' ) . '</span>' ; ?>
					<?php echo ! isset( $values['cron_setup'] ) || $values['cron_setup'] == 'false' ? sprintf( __( ', <a href="%s" target="_blank">click here</a> for cron setup instruction (it may take some times for this value to be updated)', 'learndash-notifications' ), 'https://support.learndash.com/articles/how-can-i-make-my-notifications-send-on-time/' ) : ''; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Queued Emails in DB', 'learndash-notifications' ) ?></th>
				<td>
					<?php $emails_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ld_notifications_delayed_emails" ); ?>
					<?php $emails_count = $emails_count > 0 ? $emails_count : 0; ?>
					<?php echo $emails_count; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Last Run', 'learndash-notifications' ) ?></th>
				<td>
					<?php $last_run = ! empty( $values['last_run'] ) ? date( 'Y-m-d H:i:s', $values['last_run'] ) : ''; ?>
					<?php echo $last_run; ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>