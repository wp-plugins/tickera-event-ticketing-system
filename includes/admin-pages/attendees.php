<?php
$tickets_instances = new TC_Tickets_Instances();

$page = $_GET[ 'page' ];

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'delete' ) {
	if ( !isset( $_POST[ '_wpnonce' ] ) ) {
		check_admin_referer( 'delete_' . $_GET[ 'ID' ] );
		if ( current_user_can( 'manage_options' ) || current_user_can( 'delete_tickets_cap' ) ) {
			$ticket_instance = new TC_Ticket_Instance( (int) $_GET[ 'ID' ] );
			$ticket_instance->delete_ticket_instance();
			$message		 = __( 'Attendee and Ticket data has been successfully deleted.', 'tc' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tc' );
		}
	}
}

if ( isset( $_GET[ 'page_num' ] ) ) {
	$page_num = (int) $_GET[ 'page_num' ];
} else {
	$page_num = 1;
}

if ( isset( $_GET[ 's' ] ) ) {
	$attendeesearch = $_GET[ 's' ];
} else {
	$attendeesearch = '';
}

$wp_tickets_instances_search = new TC_Tickets_Instances_Search( $attendeesearch, $page_num );

$fields	 = $tickets_instances->get_tickets_instances_fields();
$columns = $tickets_instances->get_columns();
?>
<div class="wrap tc_wrap">
    <h2><?php echo $tickets_instances->form_title; ?><?php if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'details' ) { ?><a href="admin.php?page=<?php echo $_GET[ 'page' ]; ?>" class="add-new-h2"><?php _e( 'Back', 'tc' ); ?></a><?php } ?></h2>

	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo esc_attr( $message ); ?></p></div>
		<?php
	}
	?>

	<?php
	if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'details' && isset( $_GET[ 'ID' ] ) ) {

		$ticket_instance = new TC_Ticket_Instance( (int) $_GET[ 'ID' ] );
		$ticket_type	 = new TC_Ticket( $ticket_instance->details->ticket_type_id );
		$ticket_event_id = $ticket_type->get_ticket_event( $ticket_instance->details->ticket_type_id );

		if ( isset( $_POST[ 'api_key' ] ) ) {
			$api_key		 = new TC_API_Key( $_POST[ 'api_key' ] );
			$checkin		 = new TC_Checkin_API( $api_key->details->api_key, apply_filters( 'tc_checkin_request_name', 'tickera_scan' ), 'return', $ticket_instance->details->ticket_code, false );
			$checkin_result	 = $checkin->ticket_checkin( false );
			if ( $checkin_result[ 'status' ] == 1 ) {
				$message_type	 = 'updated';
				$message		 = __( 'Ticket checked in successfully.', 'tc' );
			} else {
				$message_type	 = 'error';
				$message		 = __( 'Ticket expired.', 'tc' );
			}
		}

		$ticket_checkins = $ticket_instance->get_ticket_checkins();


		if ( isset( $_GET[ 'checkin_action' ] ) && $_GET[ 'checkin_action' ] == 'delete_checkin' && check_admin_referer( 'delete_checkin' ) && !isset( $_POST[ 'api_key' ] ) ) {
			$entry_to_delate = $_GET[ 'checkin_entry' ];

			$checkin_row = 0;

			if ( $ticket_checkins ) {
				foreach ( $ticket_checkins as $ticket_key => $ticket_checkin ) {
					if ( $ticket_checkin[ 'date_checked' ] == $entry_to_delate ) {
						unset( $ticket_checkins[ $ticket_key ] );
					}
					$checkin_row++;
				}
				update_post_meta( $ticket_instance->details->ID, 'tc_checkins', $ticket_checkins );
				$message_type	 = 'updated';
				$message		 = __( 'Check-in record deleted successfully.', 'tc' );
			}
		}

		$ticket_checkins = $ticket_instance->get_ticket_checkins();
		?>

		<?php
		if ( isset( $message ) ) {
			?>
			<div id="message" class="<?php echo $message_type; ?> fade"><p><?php echo $message; ?></p></div>
			<?php
		}
		?>

		<table class="checkins-table widefat shadow-table">
			<tbody>
				<tr valign="top">
					<th><?php _e( 'Check-in Date', 'tc' ); ?></th>
					<th><?php _e( 'Status', 'tc' ); ?></th>
					<th><?php _e( 'API Key', 'tc' ); ?></th>
					<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'delete_tickets_cap' ) ) { ?>
						<th><?php _e( 'Delete', 'tc' ); ?></th>
					<?php } ?>
				</tr>
				<?php
				$style = '';
				if ( $ticket_checkins ) {
					arsort( $ticket_checkins );
					foreach ( $ticket_checkins as $ticket_checkin ) {
						$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
						?>  
						<tr <?php echo $style; ?>>
							<td><?php echo tc_format_date($ticket_checkin[ 'date_checked' ]); ?></td>
							<td><?php echo apply_filters( 'tc_checkins_status', $ticket_checkin[ 'status' ] ); ?></td>
							<td><?php echo apply_filters( 'tc_checkins_api_key_id', $ticket_checkin[ 'api_key_id' ] ); ?></td>
							<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'delete_checkins_cap' ) ) { ?>
								<td><?php echo '<a class="tc_delete_link" href="' . wp_nonce_url( admin_url( 'admin.php?page=tc_attendees&action=details&ID=' . $_GET[ 'ID' ] . '&checkin_action=delete_checkin&checkin_entry=' . $ticket_checkin[ 'date_checked' ] ), 'delete_checkin' ) . '">' . __( 'Delete', 'tc' ) . '</a>'; ?></td>
							<?php } ?>
						</tr>
						<?php
					}
				} else {
					?>
					<tr>
						<td colspan="4"><?php _e( "There are no any check-ins for this ticket yet." ); ?></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>

		<?php
		global $current_user;
		get_currentuserinfo();
		$current_user_name	 = $current_user->user_login;
		$staff_api_keys_num	 = 0;

		$wp_api_keys_search = new TC_API_Keys_Search( '', '', $ticket_event_id );

		if ( !current_user_can( 'manage_options' ) ) {
			foreach ( $wp_api_keys_search->get_results() as $api_key ) {
				$api_key_obj = new TC_API_Key( $api_key->ID );
				if ( ($api_key_obj->details->api_username == $current_user_name ) ) {
					$staff_api_keys_num++;
				}
			}
		}


		if ( count( $wp_api_keys_search->get_results() ) > 0 && (current_user_can( 'manage_options' ) || (!current_user_can( 'manage_options' ) && $staff_api_keys_num > 0)) ) {
			?>
			<form action="" method="post" enctype="multipart/form-data">
				<table class="checkin-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="api_key"><?php _e( 'API Key' ) ?></label></th>
							<td>
								<select name="api_key">
									<?php
									foreach ( $wp_api_keys_search->get_results() as $api_key ) {
										$api_key_obj = new TC_API_Key( $api_key->ID );
										if ( current_user_can( 'manage_options' ) || ($api_key_obj->details->api_username == $current_user_name) ) {
											?>
											<option value="<?php echo $api_key->ID; ?>"><?php echo $api_key_obj->details->api_key_name; ?></option>
											<?php
										}
									}
									?>
								</select>
								<input type="submit" name="check_in_ticket" id="check_in_ticket" class="button button-primary" value="<?php _e( 'Check In', 'tc' ); ?>">
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		<?php } ?>
	<?php } else {
		?>

		<div class="tablenav">
			<div class="alignright actions new-actions">
				<form method="get" action="?page=<?php echo esc_attr( $page ); ?>" class="search-form">
					<p class="search-box">
						<input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>' />
						<label class="screen-reader-text"><?php _e( 'Search Attendees & Tickets', 'tc' ); ?>:</label>
						<input type="text" value="<?php echo esc_attr( $attendeesearch ); ?>" name="s">
						<input type="submit" class="button" value="<?php _e( 'Search Attendees & Tickets', 'tc' ); ?>">
					</p>
				</form>
			</div><!--/alignright-->

		</div><!--/tablenav-->

		<table cellspacing="0" class="widefat shadow-table">
			<thead>
				<tr>
					<!--<th style="" class="manage-column column-cb check-column" id="cb" scope="col" width="<?php //echo (isset($col_sizes[0]) ? $col_sizes[0] . '%' : '');                                    ?>"><input type="checkbox"></th>-->
					<?php
					$n = 1;
					foreach ( $columns as $col ) {
						?>
						<th style="" class="manage-column column-<?php echo $col[ 'id' ]; ?>" width="<?php echo (isset( $col_sizes[ $n ] ) ? $col_sizes[ $n ] . '%' : ''); ?>" id="<?php echo $col[ 'id' ]; ?>" scope="col"><?php echo $col[ 'field_title' ]; ?></th>
						<?php
						$n++;
					}
					?>
				</tr>
			</thead>

			<tbody>
				<?php
				$style = '';

				foreach ( $wp_tickets_instances_search->get_results() as $ticket_instance ) {

					$ticket_instance_obj = new TC_Ticket_Instance( $ticket_instance->ID );

					$ticket_instance_object = apply_filters( 'tc_ticket_instance_object_details', $ticket_instance_obj->details );

					$style	 = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
					?>
					<tr id='attendee-<?php echo $ticket_instance_object->ID; ?>' <?php echo $style; ?>>
						<!--<th scope='row' class='check-column'>
							<input type='checkbox' name='events[]' id='user_<?php echo $ticket_instance_object->$key; ?>' class='' value='<?php echo $ticket_instance_object->$key; ?>' />
						</th>-->
						<?php
						$n		 = 1;

						foreach ( $columns as $col ) {

							if ( $col[ 'id' ] == 'delete' ) {
								if ( current_user_can( 'manage_options' ) || current_user_can( 'delete_attendees_cap' ) ) {
									?>
									<td>
										<a class="order_delete_link tc_delete_link" href="<?php echo wp_nonce_url( 'admin.php?page=' . $page . '&action=' . $col[ 'id' ] . '&ID=' . $ticket_instance_object->ID, 'delete_' . $ticket_instance_object->ID ); ?>"><?php _e( 'Delete', 'tc' ); ?></a>
									</td>
									<?php
								}
							} else {
								?>
								<td>
									<?php
									$post_field_type = $tickets_instances->check_field_property( $col[ 'field_name' ], 'post_field_type' );
									$field_id		 = $col[ 'id' ]; //$tickets_instances->get_field_id($col['field_name'], 'post_field_type');

									if ( isset( $post_field_type ) && $post_field_type == 'post_meta' ) {
										if ( isset( $field_id ) ) {
											echo apply_filters( 'tc_ticket_instance_field_value', $ticket_instance_object->ID, $ticket_instance_object->$col[ 'field_name' ], $post_field_type, (isset( $col[ 'field_id' ] ) ? $col[ 'field_id' ] : '' ), $field_id );
										} else {
											echo apply_filters( 'tc_ticket_instance_field_value', $ticket_instance_object->ID, $ticket_instance_object->$col[ 'field_name' ], $post_field_type, (isset( $col[ 'field_id' ] ) ? $col[ 'field_id' ] : '' ) );
										}
									} else {
										if ( isset( $field_id ) ) {
											echo apply_filters( 'tc_ticket_instance_field_value', $ticket_instance_object->ID, (isset( $ticket_instance_object->$post_field_type ) ? $ticket_instance_object->$post_field_type : $ticket_instance_object->$col[ 'field_name' ] ), $post_field_type, $col[ 'field_name' ], $field_id );
										} else {
											echo apply_filters( 'tc_ticket_instance_field_value', $ticket_instance_object->ID, (isset( $ticket_instance_object->$post_field_type ) ? $ticket_instance_object->$post_field_type : $ticket_instance_object->$col[ 'field_name' ] ), $post_field_type, $col[ 'field_name' ] );
										}
									}
									?>
								</td>
								<?php
							}
						}
						?>
					</tr>
					<?php
				}
				?>

				<?php
				if ( count( $wp_tickets_instances_search->get_results() ) == 0 ) {
					?>
					<tr>
						<td colspan="6"><div class="zero-records"><?php _e( 'No attendees & tickets found.', 'tc' ) ?></div></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table><!--/widefat shadow-table-->

		<div class="tablenav">
			<div class="tablenav-pages"><?php $wp_tickets_instances_search->page_links(); ?></div>
		</div><!--/tablenav-->
	<?php } ?>
</div>
