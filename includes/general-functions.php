<?php

function tc_get_payment_page_slug() {
	$page_id = get_option( 'tc_payment_page_id', false );
	$page	 = get_post( $page_id, OBJECT );
	return $page->post_name;
}

function tc_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
	global $wpdb;

	$option_value = get_option( $option );

	if ( $option_value > 0 && get_post( $option_value ) )
		return -1;

	$page_found = null;

	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", $slug ) );
	}

	$page_found = apply_filters( 'woocommerce_create_page_id', $page_found, $slug, $page_content );

	if ( $page_found ) {
		if ( !$option_value ) {
			update_option( $option, $page_found );
		}

		return $page_found;
	}

	$page_data = array(
		'post_author'	 => get_current_user_id(),
		'post_status'	 => 'publish',
		'post_type'		 => 'page',
		'post_author'	 => 1,
		'post_name'		 => $slug,
		'post_title'	 => $page_title,
		'post_content'	 => $page_content,
		'post_parent'	 => $post_parent,
		'comment_status' => 'closed'
	);

	$page_id = wp_insert_post( $page_data );

	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;
}

function tc_get_events_and_tickets_shortcode_select_box() {
	?>
	<select name="tc_events_tickets_shortcode_select" class="tc_events_tickets_shortcode_select">
		<?php
		$wp_events_search = new TC_Events_Search( '', '', -1 );
		foreach ( $wp_events_search->get_results() as $event ) {
			$event_obj		 = new TC_Event( $event->ID );
			$ticket_types	 = $event_obj->get_event_ticket_types();
			?>
			<option class="option_event" value="<?php echo $event_obj->details->ID; ?>"><?php echo $event_obj->details->post_title; ?></option>
			<?php
			foreach ( $ticket_types as $ticket_type ) {
				$ticket_type_obj = new TC_Ticket( $ticket_type );
				?>
				<option class="option_ticket" value="<?php echo $ticket_type_obj->details->ID; ?>"><?php echo $event_obj->details->post_title; ?> > <?php echo $ticket_type_obj->details->post_title; ?></option>
				<?php
			}
		}
		?>
	</select>
	<?php
}

add_action( 'tc_order_created', 'tc_order_created_email', 10, 5 );

function client_email_from_name( $name ) {
	$tc_email_settings = get_option( 'tc_email_setting', false );
	return isset( $tc_email_settings[ 'client_order_from_name' ] ) ? $tc_email_settings[ 'client_order_from_name' ] : get_option( 'blogname' );
}

function client_email_from_email( $email ) {
	$tc_email_settings = get_option( 'tc_email_setting', false );
	return isset( $tc_email_settings[ 'client_order_from_email' ] ) ? $tc_email_settings[ 'client_order_from_email' ] : get_option( 'admin_email' );
}

function admin_email_from_name( $name ) {
	$tc_email_settings = get_option( 'tc_email_setting', false );
	return isset( $tc_email_settings[ 'admin_order_from_name' ] ) ? $tc_email_settings[ 'admin_order_from_name' ] : get_option( 'blogname' );
}

function admin_email_from_email( $email ) {
	$tc_email_settings = get_option( 'tc_email_setting', false );
	return isset( $tc_email_settings[ 'admin_order_from_email' ] ) ? $tc_email_settings[ 'admin_order_from_email' ] : get_option( 'admin_email' );
}

function tc_order_created_email( $order_id, $status, $cart_contents = false, $cart_info = false, $payment_info = false,
								 $send_email_to_admin = true ) {
	global $tc;

	$tc_email_settings = get_option( 'tc_email_setting', false );

	$order_id = strtoupper( $order_id );

	$order = tc_get_order_id_by_name( $order_id );

	if ( $cart_contents === false ) {
		$cart_contents = get_post_meta( $order->ID, 'tc_cart_contents', true );
	}

	if ( $cart_info === false ) {
		$cart_info = get_post_meta( $order->ID, 'tc_cart_info', true );
	}

	if ( $payment_info === false ) {
		$payment_info = get_post_meta( $order->ID, 'tc_payment_info', true );
	}

	add_filter( 'wp_mail_content_type', 'set_content_type' );

	function set_content_type( $content_type ) {
		return 'text/html';
	}

	do_action( 'tc_before_order_created_email' );

	if ( $status == 'order_paid' ) {
		//Send e-mail to the client

		if ( !isset( $tc_email_settings[ 'client_send_message' ] ) || (isset( $tc_email_settings[ 'client_send_message' ] ) && $tc_email_settings[ 'client_send_message' ] == 'yes') ) {
			add_filter( 'wp_mail_from', 'client_email_from_email', 999 );
			add_filter( 'wp_mail_from_name', 'client_email_from_name', 999 );

			$subject = isset( $tc_email_settings[ 'client_order_subject' ] ) ? $tc_email_settings[ 'client_order_subject' ] : __( 'Order Completed', 'tc' );

			$default_message = 'Hello, <br /><br />Your order (ORDER_ID) totalling <strong>ORDER_TOTAL</strong> is completed. <br /><br />You can download your tickets here: DOWNLOAD_URL';
			$message		 = isset( $tc_email_settings[ 'client_order_message' ] ) ? $tc_email_settings[ 'client_order_message' ] : $default_message;

			$order				 = new TC_Order( $order->ID );
			$order_status_url	 = $tc->tc_order_status_url( $order, $order->details->tc_order_date, '', false );

			$placeholders		 = array( 'ORDER_ID', 'ORDER_TOTAL', 'DOWNLOAD_URL' );
			$placeholder_values	 = array( $order_id, $tc->get_cart_currency_and_format( $payment_info[ 'total' ] ), $order_status_url );

			$to = $cart_info[ 'buyer_data' ][ 'email_post_meta' ];

			$message = str_replace( apply_filters( 'tc_order_completed_client_email_placeholders', $placeholders ), apply_filters( 'tc_order_completed_client_email_placeholder_values', $placeholder_values ), $message );

			$client_headers = ''; //'From: ' . client_email_from_name( '' ) . ' <' . client_email_from_email( '' ) . '>' . "\r\n";

			wp_mail( $to, $subject, html_entity_decode( stripcslashes( apply_filters( 'tc_order_completed_admin_email_message', $message ) ) ), apply_filters( 'tc_order_completed_client_email_headers', $client_headers ) );
		}

		/* --------------------------------------------------------------------- */

		//Send e-mail to the admin

		if ( (!isset( $tc_email_settings[ 'admin_send_message' ] ) || (isset( $tc_email_settings[ 'admin_send_message' ] ) && $tc_email_settings[ 'admin_send_message' ] == 'yes')) && $send_email_to_admin ) {

			add_filter( 'wp_mail_from', 'admin_email_from_email', 999 );
			add_filter( 'wp_mail_from_name', 'admin_email_from_name', 999 );

			$subject = isset( $tc_email_settings[ 'admin_order_subject' ] ) ? $tc_email_settings[ 'admin_order_subject' ] : __( 'New Order Completed', 'tc' );

			$default_message = 'Hello, <br /><br />a new order (ORDER_ID) totalling <strong>ORDER_TOTAL</strong> has been placed. <br /><br />You can check the order details here: ORDER_ADMIN_URL';
			$message		 = isset( $tc_email_settings[ 'admin_order_message' ] ) ? $tc_email_settings[ 'admin_order_message' ] : $default_message;

			$order	 = tc_get_order_id_by_name( $order_id );
			$order	 = new TC_Order( $order->ID );

			$order_admin_url = admin_url( 'admin.php?page=tc_orders&action=details&ID=' . $order->details->ID );

			$placeholders		 = array( 'ORDER_ID', 'ORDER_TOTAL', 'ORDER_ADMIN_URL' );
			$placeholder_values	 = array( $order_id, $tc->get_cart_currency_and_format( $payment_info[ 'total' ] ), $order_admin_url );

			$to = isset( $tc_email_settings[ 'admin_order_from_email' ] ) ? $tc_email_settings[ 'admin_order_from_email' ] : get_option( 'admin_email' );

			$message = str_replace( apply_filters( 'tc_order_completed_admin_email_placeholders', $placeholders ), apply_filters( 'tc_order_completed_admin_email_placeholder_values', $placeholder_values ), $message );

			$admin_headers = ''; //'From: ' . admin_email_from_name( '' ) . ' <' . admin_email_from_email( '' ) . '>' . "\r\n";

			wp_mail( $to, $subject, html_entity_decode( stripcslashes( apply_filters( 'tc_order_completed_admin_email_message', $message ) ) ), apply_filters( 'tc_order_completed_admin_email_headers', $admin_headers ) );
		}
	}

	do_action( 'tc_after_order_created_email' );
}

function tc_minimum_total( $total ) {
	if ( $total < 0 ) {
		return 0;
	} else {
		return $total;
	}
}

function tc_force_login( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'no';
		}
	}
	?>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="yes" <?php checked( $checked, 'yes', true ); ?>  /><?php _e( 'Yes', 'tc' ); ?>
	</label>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="no" <?php checked( $checked, 'no', true ); ?> /><?php _e( 'No', 'tc' ); ?>
	</label>
	<?php
}

function tc_show_discount_code_field( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'no';
		}
	}
	?>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="yes" <?php checked( $checked, 'yes', true ); ?>  /><?php _e( 'Yes', 'tc' ); ?>
	</label>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="no" <?php checked( $checked, 'no', true ); ?> /><?php _e( 'No', 'tc' ); ?>
	</label>
	<?php
}

function tc_get_client_order_message( $field_name, $default_value = '' ) {
	global $tc_email_settings;
	if ( isset( $tc_email_settings[ $field_name ] ) ) {
		$value = $tc_email_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$value = $default_value;
		} else {
			$value = '';
		}
	}
	wp_editor( html_entity_decode( stripcslashes( $value ) ), $field_name, array( 'textarea_name' => 'tc_email_setting[' . $field_name . ']', 'textarea_rows' => 2 ) );
}

function tc_get_admin_order_message( $field_name, $default_value = '' ) {
	global $tc_email_settings;
	if ( isset( $tc_email_settings[ $field_name ] ) ) {
		$value = $tc_email_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$value = $default_value;
		} else {
			$value = '';
		}
	}
	wp_editor( html_entity_decode( stripcslashes( $value ) ), $field_name, array( 'textarea_name' => 'tc_email_setting[' . $field_name . ']', 'textarea_rows' => 2 ) );
}

function tc_show_tax_rate( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'yes';
		}
	}
	?>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="yes" <?php checked( $checked, 'yes', true ); ?>  /><?php _e( 'Yes', 'tc' ); ?>
	</label>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="no" <?php checked( $checked, 'no', true ); ?> /><?php _e( 'No', 'tc' ); ?>
	</label>
	<?php
}

function tc_show_owner_fields( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'yes';
		}
	}
	?>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="yes" <?php checked( $checked, 'yes', true ); ?>  /><?php _e( 'Yes', 'tc' ); ?>
	</label>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="no" <?php checked( $checked, 'no', true ); ?> /><?php _e( 'No', 'tc' ); ?>
	</label>
	<?php
}

function tc_client_send_order_messages( $field_name, $default_value = '' ) {
	global $tc_email_settings;
	if ( isset( $tc_email_settings[ $field_name ] ) ) {
		$checked = $tc_email_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'yes';
		}
	}
	?>
	<label>
		<input type="radio" name="tc_email_setting[<?php echo esc_attr( $field_name ); ?>]" value="yes" <?php checked( $checked, 'yes', true ); ?>  /><?php _e( 'Yes', 'tc' ); ?>
	</label>
	<label>
		<input type="radio" name="tc_email_setting[<?php echo esc_attr( $field_name ); ?>]" value="no" <?php checked( $checked, 'no', true ); ?> /><?php _e( 'No', 'tc' ); ?>
	</label>
	<?php
}

function tc_admin_send_order_messages( $field_name, $default_value = '' ) {
	global $tc_email_settings;
	if ( isset( $tc_email_settings[ $field_name ] ) ) {
		$checked = $tc_email_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'yes';
		}
	}
	?>
	<label>
		<input type="radio" name="tc_email_setting[<?php echo esc_attr( $field_name ); ?>]" value="yes" <?php checked( $checked, 'yes', true ); ?>  /><?php _e( 'Yes', 'tc' ); ?>
	</label>
	<label>
		<input type="radio" name="tc_email_setting[<?php echo esc_attr( $field_name ); ?>]" value="no" <?php checked( $checked, 'no', true ); ?> /><?php _e( 'No', 'tc' ); ?>
	</label>
	<?php
}

function tc_show_fees( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'yes';
		}
	}
	?>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="yes" <?php checked( $checked, 'yes', true ); ?> /><?php _e( 'Yes', 'tc' ); ?>
	</label>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="no" <?php checked( $checked, 'no', true ); ?> /><?php _e( 'No', 'tc' ); ?>
	</label>
	<?php
}

function tc_show_cart( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'yes';
		}
	}
	?>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="yes" <?php checked( $checked, 'yes', true ); ?> /><?php _e( 'Yes', 'tc' ); ?>
	</label>
	<label>
		<input type="radio" name="tc_general_setting[<?php echo esc_attr( $field_name ); ?>]" value="no" <?php checked( $checked, 'no', true ); ?> /><?php _e( 'No', 'tc' ); ?>
	</label>
	<?php
}

function tc_get_price_formats( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'us';
		}
	}
	?>
	<select name="tc_general_setting[<?php echo $field_name; ?>]">
		<option value="us" <?php selected( $checked, 'us', true ); ?>><?php _e( '1,234.56', 'tc' ); ?></option>
		<option value="eu" <?php selected( $checked, 'eu', true ); ?>><?php _e( '1.234,56', 'tc' ); ?></option>
		<option value="french_comma" <?php selected( $checked, 'french_comma', true ); ?>><?php _e( '1 234,56', 'tc' ); ?></option>
		<option value="french_dot" <?php selected( $checked, 'french_dot', true ); ?>><?php _e( '1 234.56', 'tc' ); ?></option>
		<?php do_action( 'tc_price_formats' ); ?>
	</select>
	<?php
}

function tc_get_currency_positions( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'pre_nospace';
		}
	}

	$symbol = (isset( $tc_general_settings[ 'currency_symbol' ] ) && $tc_general_settings[ 'currency_symbol' ] != '' ? $tc_general_settings[ 'currency_symbol' ] : (isset( $tc_general_settings[ 'currencies' ] ) ? $tc_general_settings[ 'currencies' ] : '$'));
	?>
	<select name="tc_general_setting[<?php echo $field_name; ?>]">
		<option value="pre_space" <?php selected( $checked, 'pre_space', true ); ?>><?php echo $symbol . ' 10'; ?></option>
		<option value="pre_nospace" <?php selected( $checked, 'pre_nospace', true ); ?>><?php echo $symbol . '10'; ?></option>
		<option value="post_nospace" <?php selected( $checked, 'post_nospace', true ); ?>><?php echo '10' . $symbol; ?></option>
		<option value="post_space" <?php selected( $checked, 'post_space', true ); ?>><?php echo '10 ' . $symbol; ?></option>
		<?php do_action( 'tc_currencies_position' ); ?>
	</select>
	<?php
}

function tc_get_global_currencies( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	$settings	 = get_option( 'tc_settings' );
	$currencies	 = $settings[ 'gateways' ][ 'currencies' ];

	ksort( $currencies );

	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'USD';
		}
	}
	?>
	<select name="tc_general_setting[<?php echo $field_name; ?>]">
		<?php
		foreach ( $currencies as $symbol => $title ) {
			?>
			<option value="<?php echo $symbol; ?>" <?php selected( $checked, $symbol, true ); ?>><?php echo $title; ?></option>
			<?php
		}
		?>
	</select>
	<?php
}

function tc_add_additional_schedule_intervals( $schedules ) {
	$schedules[ 'half_hour' ] = array(
		'interval'	 => 30 * 60,
		'display'	 => __( 'Half Hour' )
	);
	return $schedules;
}

add_filter( 'cron_schedules', 'tc_add_additional_schedule_intervals' );

function tc_show_delete_pending_orders_times( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	$settings	 = get_option( 'tc_settings' );
	$schedules	 = array(
		__( 'Never', 'tc' )		 => '',
		__( '30 Minutes', 'tc' ) => 30 * 60,
		__( '45 Minutes', 'tc' ) => 45 * 60,
		__( '1 Hour', 'tc' )	 => 60 * 60,
		__( '2 Hours', 'tc' )	 => 2 * 60 * 60,
		__( '3 Hours', 'tc' )	 => 3 * 60 * 60,
		__( '4 Hours', 'tc' )	 => 4 * 60 * 60,
		__( '5 Hours', 'tc' )	 => 5 * 60 * 60,
		__( '6 Hours', 'tc' )	 => 6 * 60 * 60,
		__( '12 Hours', 'tc' )	 => 12 * 60 * 60,
		__( '1 Day', 'tc' )		 => 24 * 60 * 60,
		__( '2 Days', 'tc' )	 => 2 * 24 * 60 * 60,
		__( '3 Days', 'tc' )	 => 3 * 24 * 60 * 60,
		__( '7 Days', 'tc' )	 => 7 * 24 * 60 * 60,
		__( '14 Days', 'tc' )	 => 14 * 24 * 60 * 60,
		__( '30 Days', 'tc' )	 => 30 * 24 * 60 * 60
	);

	$schedules = apply_filters( 'tc_delete_pending_orders_schedule_times', $schedules );

	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = 'never';
		}
	}
	?>
	<select name="tc_general_setting[<?php echo $field_name; ?>]">
		<?php
		foreach ( $schedules as $display => $interval ) {
			?>
			<option value="<?php echo $interval; ?>" <?php selected( $checked, $interval, true ); ?>><?php echo $display; ?></option>
			<?php
		}
		?>
	</select>
	<?php
}

add_action( 'tcmaybedeletependingorders', 'tc_check_maybe_delete_pending_orders' );

function tc_check_maybe_delete_pending_orders() {
	global $wpdb;

	$tc_general_settings = get_option( 'tc_general_setting', false );

	if ( isset( $tc_general_settings[ 'delete_pending_orders' ] ) && is_numeric( $tc_general_settings[ 'delete_pending_orders' ] ) ) {

		$max_unpaid_time = $tc_general_settings[ 'delete_pending_orders' ];

		$current_time	 = gmdate( 'Y-m-d H:i:s', time() );
		$pending_orders	 = $wpdb->get_results( "SELECT ID, post_date_gmt FROM " . $wpdb->prefix . "posts WHERE post_status = 'order_received'", OBJECT );

		foreach ( $pending_orders as $pending_order ) {
			$delete_time = strtotime( $pending_order->post_date_gmt ) + $max_unpaid_time;
			if ( strtotime( $current_time ) > $delete_time ) {
				$order = new TC_Order( $pending_order->ID );
				$order->delete_order( true );
			}
		}
	}
}

function tc_global_admin_per_page( $value ) {
	global $tc_general_settings;
	$settings	 = get_option( 'tc_settings' );
	$global_rows = isset( $tc_general_settings[ 'global_admin_per_page' ] ) ? $tc_general_settings[ 'global_admin_per_page' ] : $value;
	return $global_rows;
	//
}

function tc_get_global_admin_per_page( $field_name, $default_value = '' ) {
	global $tc_general_settings;
	$settings = get_option( 'tc_settings' );

	$rows = array( 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100 );

	if ( isset( $tc_general_settings[ $field_name ] ) ) {
		$checked = $tc_general_settings[ $field_name ];
	} else {
		if ( $default_value !== '' ) {
			$checked = $default_value;
		} else {
			$checked = '10';
		}
	}
	?>
	<select name="tc_general_setting[<?php echo $field_name; ?>]">
		<?php
		foreach ( $rows as $row ) {
			?>
			<option value="<?php echo $row; ?>" <?php selected( $checked, $row, true ); ?>><?php echo $row; ?></option>
			<?php
		}
		?>
	</select>
	<?php
}

function tc_save_page_ids() {
	if ( isset( $_POST[ 'tc_cart_page_id' ] ) ) {
		update_option( 'tc_cart_page_id', $_POST[ 'tc_cart_page_id' ] );
	}

	if ( isset( $_POST[ 'tc_payment_page_id' ] ) ) {
		update_option( 'tc_payment_page_id', $_POST[ 'tc_payment_page_id' ] );
	}

	if ( isset( $_POST[ 'tc_confirmation_page_id' ] ) ) {
		update_option( 'tc_confirmation_page_id', $_POST[ 'tc_confirmation_page_id' ] );
	}

	if ( isset( $_POST[ 'tc_order_page_id' ] ) ) {
		update_option( 'tc_order_page_id', $_POST[ 'tc_order_page_id' ] );
	}
}

function tc_get_cart_page_settings( $field_name, $default_value = '' ) {
	$args = array(
		'selected'	 => get_option( 'tc_cart_page_id', -1 ),
		'echo'		 => 1,
		'name'		 => 'tc_cart_page_id',
	);

	wp_dropdown_pages( $args );
}

function tc_get_payment_page_settings( $field_name, $default_value = '' ) {

	$args = array(
		'selected'	 => get_option( 'tc_payment_page_id', -1 ),
		'echo'		 => 1,
		'name'		 => 'tc_payment_page_id',
	);

	wp_dropdown_pages( $args );
}

function tc_get_confirmation_page_settings( $field_name, $default_value = '' ) {
	$args = array(
		'selected'	 => get_option( 'tc_confirmation_page_id', -1 ),
		'echo'		 => 1,
		'name'		 => 'tc_confirmation_page_id',
	);

	wp_dropdown_pages( $args );
}

function tc_get_order_page_settings( $field_name, $default_value = '' ) {

	$args = array(
		'selected'	 => get_option( 'tc_order_page_id', -1 ),
		'echo'		 => 1,
		'name'		 => 'tc_order_page_id',
	);

	wp_dropdown_pages( $args );
}

function tc_get_pages_settings( $field_name, $default_value = '' ) {
	global $tc;
	?>
	<p class="submit"><a href="<?php echo add_query_arg( 'install_tickera_pages', 'true', admin_url( 'admin.php?page=tc_settings' ) ); ?>" class="button-primary"><?php printf( __( 'Install %s Pages', 'tc' ), $tc->title ); ?></a></p>
	<?php
}

/**
 * Print years
 */
function tc_years_dropdown( $sel = '', $pfp = false ) {
	$localDate	 = getdate();
	$minYear	 = $localDate[ "year" ];
	$maxYear	 = $minYear + 15;

	$output = "<option value=''>--</option>";
	for ( $i = $minYear; $i < $maxYear; $i++ ) {
		if ( $pfp ) {
			$output .= "<option value='" . substr( $i, 0, 4 ) . "'" . ($sel == (substr( $i, 0, 4 )) ? ' selected' : '') .
			">" . $i . "</option>";
		} else {
			$output .= "<option value='" . substr( $i, 2, 2 ) . "'" . ($sel == (substr( $i, 2, 2 )) ? ' selected' : '') .
			">" . $i . "</option>";
		}
	}
	return($output);
}

function tc_countries( $class = '', $name = '' ) {
	ob_start();
	?>
	<select class="<?php echo $class; ?>" name="<?php echo $name; ?>">
		<option value="AF"><?php _e( 'Afghanistan', 'tc' ); ?></option>
		<option value="AX"><?php _e( 'Åland Islands', 'tc' ); ?></option>
		<option value="AL"><?php _e( 'Albania', 'tc' ); ?></option>
		<option value="DZ"><?php _e( 'Algeria', 'tc' ); ?></option>
		<option value="AS"><?php _e( 'American Samoa', 'tc' ); ?></option>
		<option value="AD"><?php _e( 'Andorra', 'tc' ); ?></option>
		<option value="AO"><?php _e( 'Angola', 'tc' ); ?></option>
		<option value="AI"><?php _e( 'Anguilla', 'tc' ); ?></option>
		<option value="AQ"><?php _e( 'Antarctica', 'tc' ); ?></option>
		<option value="AG"><?php _e( 'Antigua and Barbuda', 'tc' ); ?></option>
		<option value="AR"><?php _e( 'Argentina', 'tc' ); ?></option>
		<option value="AM"><?php _e( 'Armenia', 'tc' ); ?></option>
		<option value="AW"><?php _e( 'Aruba', 'tc' ); ?></option>
		<option value="AU"><?php _e( 'Australia', 'tc' ); ?></option>
		<option value="AT"><?php _e( 'Austria', 'tc' ); ?></option>
		<option value="AZ"><?php _e( 'Azerbaijan', 'tc' ); ?></option>
		<option value="BS"><?php _e( 'Bahamas', 'tc' ); ?></option>
		<option value="BH"><?php _e( 'Bahrain', 'tc' ); ?></option>
		<option value="BD"><?php _e( 'Bangladesh', 'tc' ); ?></option>
		<option value="BB"><?php _e( 'Barbados', 'tc' ); ?></option>
		<option value="BY"><?php _e( 'Belarus', 'tc' ); ?></option>
		<option value="BE"><?php _e( 'Belgium', 'tc' ); ?></option>
		<option value="BZ"><?php _e( 'Belize', 'tc' ); ?></option>
		<option value="BJ"><?php _e( 'Benin', 'tc' ); ?></option>
		<option value="BM"><?php _e( 'Bermuda', 'tc' ); ?></option>
		<option value="BT"><?php _e( 'Bhutan', 'tc' ); ?></option>
		<option value="BO"><?php _e( 'Bolivia, Plurinational State of', 'tc' ); ?></option>
		<option value="BQ"><?php _e( 'Bonaire, Sint Eustatius and Saba', 'tc' ); ?></option>
		<option value="BA"><?php _e( 'Bosnia and Herzegovina', 'tc' ); ?></option>
		<option value="BW"><?php _e( 'Botswana', 'tc' ); ?></option>
		<option value="BV"><?php _e( 'Bouvet Island', 'tc' ); ?></option>
		<option value="BR"><?php _e( 'Brazil', 'tc' ); ?></option>
		<option value="IO"><?php _e( 'British Indian Ocean Territory', 'tc' ); ?></option>
		<option value="BN"><?php _e( 'Brunei Darussalam', 'tc' ); ?></option>
		<option value="BG"><?php _e( 'Bulgaria', 'tc' ); ?></option>
		<option value="BF"><?php _e( 'Burkina Faso', 'tc' ); ?></option>
		<option value="BI"><?php _e( 'Burundi', 'tc' ); ?></option>
		<option value="KH"><?php _e( 'Cambodia', 'tc' ); ?></option>
		<option value="CM"><?php _e( 'Cameroon', 'tc' ); ?></option>
		<option value="CA"><?php _e( 'Canada', 'tc' ); ?></option>
		<option value="CV"><?php _e( 'Cape Verde', 'tc' ); ?></option>
		<option value="KY"><?php _e( 'Cayman Islands', 'tc' ); ?></option>
		<option value="CF"><?php _e( 'Central African Republic', 'tc' ); ?></option>
		<option value="TD"><?php _e( 'Chad', 'tc' ); ?></option>
		<option value="CL"><?php _e( 'Chile', 'tc' ); ?></option>
		<option value="CN"><?php _e( 'China', 'tc' ); ?></option>
		<option value="CX"><?php _e( 'Christmas Island', 'tc' ); ?></option>
		<option value="CC"><?php _e( 'Cocos (Keeling) Islands', 'tc' ); ?></option>
		<option value="CO"><?php _e( 'Colombia', 'tc' ); ?></option>
		<option value="KM"><?php _e( 'Comoros', 'tc' ); ?></option>
		<option value="CG"><?php _e( 'Congo', 'tc' ); ?></option>
		<option value="CD"><?php _e( 'Congo, the Democratic Republic of the', 'tc' ); ?></option>
		<option value="CK"><?php _e( 'Cook Islands', 'tc' ); ?></option>
		<option value="CR"><?php _e( 'Costa Rica', 'tc' ); ?></option>
		<option value="CI"><?php _e( "Côte d'Ivoire", 'tc' ); ?></option>
		<option value="HR"><?php _e( 'Croatia', 'tc' ); ?>', 'tc');?></option>
		<option value="CU"><?php _e( 'Cuba', 'tc' ); ?></option>
		<option value="CW"><?php _e( 'Curaçao', 'tc' ); ?></option>
		<option value="CY"><?php _e( 'Cyprus', 'tc' ); ?></option>
		<option value="CZ"><?php _e( 'Czech Republic', 'tc' ); ?></option>
		<option value="DK"><?php _e( 'Denmark', 'tc' ); ?></option>
		<option value="DJ"><?php _e( 'Djibouti', 'tc' ); ?></option>
		<option value="DM"><?php _e( 'Dominica', 'tc' ); ?></option>
		<option value="DO"><?php _e( 'Dominican Republic', 'tc' ); ?></option>
		<option value="EC"><?php _e( 'Ecuador', 'tc' ); ?></option>
		<option value="EG"><?php _e( 'Egypt', 'tc' ); ?></option>
		<option value="SV"><?php _e( 'El Salvador', 'tc' ); ?></option>
		<option value="GQ"><?php _e( 'Equatorial Guinea', 'tc' ); ?></option>
		<option value="ER"><?php _e( 'Eritrea', 'tc' ); ?></option>
		<option value="EE"><?php _e( 'Estonia', 'tc' ); ?></option>
		<option value="ET"><?php _e( 'Ethiopia', 'tc' ); ?></option>
		<option value="FK"><?php _e( 'Falkland Islands (Malvinas)', 'tc' ); ?></option>
		<option value="FO"><?php _e( 'Faroe Islands', 'tc' ); ?></option>
		<option value="FJ"><?php _e( 'Fiji', 'tc' ); ?></option>
		<option value="FI"><?php _e( 'Finland', 'tc' ); ?></option>
		<option value="FR"><?php _e( 'France', 'tc' ); ?></option>
		<option value="GF"><?php _e( 'French Guiana', 'tc' ); ?></option>
		<option value="PF"><?php _e( 'French Polynesia', 'tc' ); ?></option>
		<option value="TF"><?php _e( 'French Southern Territories', 'tc' ); ?></option>
		<option value="GA"><?php _e( 'Gabon', 'tc' ); ?></option>
		<option value="GM"><?php _e( 'Gambia', 'tc' ); ?></option>
		<option value="GE"><?php _e( 'Georgia', 'tc' ); ?></option>
		<option value="DE"><?php _e( 'Germany', 'tc' ); ?></option>
		<option value="GH"><?php _e( 'Ghana', 'tc' ); ?></option>
		<option value="GI"><?php _e( 'Gibraltar', 'tc' ); ?></option>
		<option value="GR"><?php _e( 'Greece', 'tc' ); ?></option>
		<option value="GL"><?php _e( 'Greenland', 'tc' ); ?></option>
		<option value="GD"><?php _e( 'Grenada', 'tc' ); ?></option>
		<option value="GP"><?php _e( 'Guadeloupe', 'tc' ); ?></option>
		<option value="GU"><?php _e( 'Guam', 'tc' ); ?></option>
		<option value="GT"><?php _e( 'Guatemala', 'tc' ); ?></option>
		<option value="GG"><?php _e( 'Guernsey', 'tc' ); ?></option>
		<option value="GN"><?php _e( 'Guinea', 'tc' ); ?></option>
		<option value="GW"><?php _e( 'Guinea-Bissau', 'tc' ); ?></option>
		<option value="GY"><?php _e( 'Guyana', 'tc' ); ?></option>
		<option value="HT"><?php _e( 'Haiti', 'tc' ); ?></option>
		<option value="HM"><?php _e( 'Heard Island and McDonald Islands', 'tc' ); ?></option>
		<option value="VA"><?php _e( 'Holy See (Vatican City State)', 'tc' ); ?></option>
		<option value="HN"><?php _e( 'Honduras', 'tc' ); ?></option>
		<option value="HK"><?php _e( 'Hong Kong', 'tc' ); ?></option>
		<option value="HU"><?php _e( 'Hungary', 'tc' ); ?></option>
		<option value="IS"><?php _e( 'Iceland', 'tc' ); ?></option>
		<option value="IN"><?php _e( 'India', 'tc' ); ?></option>
		<option value="ID"><?php _e( 'Indonesia', 'tc' ); ?></option>
		<option value="IR"><?php _e( 'Iran, Islamic Republic of', 'tc' ); ?></option>
		<option value="IQ"><?php _e( 'Iraq', 'tc' ); ?></option>
		<option value="IE"><?php _e( 'Ireland', 'tc' ); ?></option>
		<option value="IM"><?php _e( 'Isle of Man', 'tc' ); ?></option>
		<option value="IL"><?php _e( 'Israel', 'tc' ); ?></option>
		<option value="IT"><?php _e( 'Italy', 'tc' ); ?></option>
		<option value="JM"><?php _e( 'Jamaica', 'tc' ); ?></option>
		<option value="JP"><?php _e( 'Japan', 'tc' ); ?></option>
		<option value="JE"><?php _e( 'Jersey', 'tc' ); ?></option>
		<option value="JO"><?php _e( 'Jordan', 'tc' ); ?></option>
		<option value="KZ"><?php _e( 'Kazakhstan', 'tc' ); ?></option>
		<option value="KE"><?php _e( 'Kenya', 'tc' ); ?></option>
		<option value="KI"><?php _e( 'Kiribati', 'tc' ); ?></option>
		<option value="KP"><?php _e( "Korea, Democratic People's Republic of", 'tc' ); ?></option>
		<option value="KR"><?php _e( 'Korea, Republic of', 'tc' ); ?></option>
		<option value="KW"><?php _e( 'Kuwait', 'tc' ); ?></option>
		<option value="KG"><?php _e( 'Kyrgyzstan', 'tc' ); ?></option>
		<option value="LA"><?php _e( "Lao People's Democratic Republic", 'tc' ); ?></option>
		<option value="LV"><?php _e( 'Latvia', 'tc' ); ?></option>
		<option value="LB"><?php _e( 'Lebanon', 'tc' ); ?></option>
		<option value="LS"><?php _e( 'Lesotho', 'tc' ); ?></option>
		<option value="LR"><?php _e( 'Liberia', 'tc' ); ?></option>
		<option value="LY"><?php _e( 'Libya', 'tc' ); ?></option>
		<option value="LI"><?php _e( 'Liechtenstein', 'tc' ); ?></option>
		<option value="LT"><?php _e( 'Lithuania', 'tc' ); ?></option>
		<option value="LU"><?php _e( 'Luxembourg', 'tc' ); ?></option>
		<option value="MO"><?php _e( 'Macao', 'tc' ); ?></option>
		<option value="MK"><?php _e( 'Macedonia, the former Yugoslav Republic of', 'tc' ); ?></option>
		<option value="MG"><?php _e( 'Madagascar', 'tc' ); ?></option>
		<option value="MW"><?php _e( 'Malawi', 'tc' ); ?></option>
		<option value="MY"><?php _e( 'Malaysia', 'tc' ); ?></option>
		<option value="MV"><?php _e( 'Maldives', 'tc' ); ?></option>
		<option value="ML"><?php _e( 'Mali', 'tc' ); ?></option>
		<option value="MT"><?php _e( 'Malta', 'tc' ); ?></option>
		<option value="MH"><?php _e( 'Marshall Islands', 'tc' ); ?></option>
		<option value="MQ"><?php _e( 'Martinique', 'tc' ); ?></option>
		<option value="MR"><?php _e( 'Mauritania', 'tc' ); ?></option>
		<option value="MU"><?php _e( 'Mauritius', 'tc' ); ?></option>
		<option value="YT"><?php _e( 'Mayotte', 'tc' ); ?></option>
		<option value="MX"><?php _e( 'Mexico', 'tc' ); ?></option>
		<option value="FM"><?php _e( 'Micronesia, Federated States of', 'tc' ); ?></option>
		<option value="MD"><?php _e( 'Moldova, Republic of', 'tc' ); ?></option>
		<option value="MC"><?php _e( 'Monaco', 'tc' ); ?></option>
		<option value="MN"><?php _e( 'Mongolia', 'tc' ); ?></option>
		<option value="ME"><?php _e( 'Montenegro', 'tc' ); ?></option>
		<option value="MS"><?php _e( 'Montserrat', 'tc' ); ?></option>
		<option value="MA"><?php _e( 'Morocco', 'tc' ); ?></option>
		<option value="MZ"><?php _e( 'Mozambique', 'tc' ); ?></option>
		<option value="MM"><?php _e( 'Myanmar', 'tc' ); ?></option>
		<option value="NA"><?php _e( 'Namibia', 'tc' ); ?></option>
		<option value="NR"><?php _e( 'Nauru', 'tc' ); ?></option>
		<option value="NP"><?php _e( 'Nepal', 'tc' ); ?></option>
		<option value="NL"><?php _e( 'Netherlands', 'tc' ); ?></option>
		<option value="NC"><?php _e( 'New Caledonia', 'tc' ); ?></option>
		<option value="NZ"><?php _e( 'New Zealand', 'tc' ); ?></option>
		<option value="NI"><?php _e( 'Nicaragua', 'tc' ); ?></option>
		<option value="NE"><?php _e( 'Niger', 'tc' ); ?></option>
		<option value="NG"><?php _e( 'Nigeria', 'tc' ); ?></option>
		<option value="NU"><?php _e( 'Niue', 'tc' ); ?></option>
		<option value="NF"><?php _e( 'Norfolk Island', 'tc' ); ?></option>
		<option value="MP"><?php _e( 'Northern Mariana Islands', 'tc' ); ?></option>
		<option value="NO"><?php _e( 'Norway', 'tc' ); ?></option>
		<option value="OM"><?php _e( 'Oman', 'tc' ); ?></option>
		<option value="PK"><?php _e( 'Pakistan', 'tc' ); ?></option>
		<option value="PW"><?php _e( 'Palau', 'tc' ); ?></option>
		<option value="PS"><?php _e( 'Palestinian Territory, Occupied', 'tc' ); ?></option>
		<option value="PA"><?php _e( 'Panama', 'tc' ); ?></option>
		<option value="PG"><?php _e( 'Papua New Guinea', 'tc' ); ?></option>
		<option value="PY"><?php _e( 'Paraguay', 'tc' ); ?></option>
		<option value="PE"><?php _e( 'Peru', 'tc' ); ?></option>
		<option value="PH"><?php _e( 'Philippines', 'tc' ); ?></option>
		<option value="PN"><?php _e( 'Pitcairn', 'tc' ); ?></option>
		<option value="PL"><?php _e( 'Poland', 'tc' ); ?></option>
		<option value="PT"><?php _e( 'Portugal', 'tc' ); ?></option>
		<option value="PR"><?php _e( 'Puerto Rico', 'tc' ); ?></option>
		<option value="QA"><?php _e( 'Qatar', 'tc' ); ?></option>
		<option value="RE"><?php _e( 'Réunion', 'tc' ); ?></option>
		<option value="RO"><?php _e( 'Romania', 'tc' ); ?></option>
		<option value="RU"><?php _e( 'Russian Federation', 'tc' ); ?></option>
		<option value="RW"><?php _e( 'Rwanda', 'tc' ); ?></option>
		<option value="BL"><?php _e( 'Saint Barthélemy', 'tc' ); ?></option>
		<option value="SH"><?php _e( 'Saint Helena, Ascension and Tristan da Cunha', 'tc' ); ?></option>
		<option value="KN"><?php _e( 'Saint Kitts and Nevis', 'tc' ); ?></option>
		<option value="LC"><?php _e( 'Saint Lucia', 'tc' ); ?></option>
		<option value="MF"><?php _e( 'Saint Martin (French part)', 'tc' ); ?></option>
		<option value="PM"><?php _e( 'Saint Pierre and Miquelon', 'tc' ); ?></option>
		<option value="VC"><?php _e( 'Saint Vincent and the Grenadines', 'tc' ); ?></option>
		<option value="WS"><?php _e( 'Samoa', 'tc' ); ?></option>
		<option value="SM"><?php _e( 'San Marino', 'tc' ); ?></option>
		<option value="ST"><?php _e( 'Sao Tome and Principe', 'tc' ); ?></option>
		<option value="SA"><?php _e( 'Saudi Arabia', 'tc' ); ?></option>
		<option value="SN"><?php _e( 'Senegal', 'tc' ); ?></option>
		<option value="RS"><?php _e( 'Serbia', 'tc' ); ?></option>
		<option value="SC"><?php _e( 'Seychelles', 'tc' ); ?></option>
		<option value="SL"><?php _e( 'Sierra Leone', 'tc' ); ?></option>
		<option value="SG"><?php _e( 'Singapore', 'tc' ); ?></option>
		<option value="SX"><?php _e( 'Sint Maarten (Dutch part)', 'tc' ); ?></option>
		<option value="SK"><?php _e( 'Slovakia', 'tc' ); ?></option>
		<option value="SI"><?php _e( 'Slovenia', 'tc' ); ?></option>
		<option value="SB"><?php _e( 'Solomon Islands', 'tc' ); ?></option>
		<option value="SO"><?php _e( 'Somalia', 'tc' ); ?></option>
		<option value="ZA"><?php _e( 'South Africa', 'tc' ); ?></option>
		<option value="GS"><?php _e( 'South Georgia and the South Sandwich Islands', 'tc' ); ?></option>
		<option value="SS"><?php _e( 'South Sudan', 'tc' ); ?></option>
		<option value="ES"><?php _e( 'Spain', 'tc' ); ?></option>
		<option value="LK"><?php _e( 'Sri Lanka', 'tc' ); ?></option>
		<option value="SD"><?php _e( 'Sudan', 'tc' ); ?></option>
		<option value="SR"><?php _e( 'Suriname', 'tc' ); ?></option>
		<option value="SJ"><?php _e( 'Svalbard and Jan Mayen', 'tc' ); ?></option>
		<option value="SZ"><?php _e( 'Swaziland', 'tc' ); ?></option>
		<option value="SE"><?php _e( 'Sweden', 'tc' ); ?></option>
		<option value="CH"><?php _e( 'Switzerland', 'tc' ); ?></option>
		<option value="SY"><?php _e( 'Syrian Arab Republic', 'tc' ); ?></option>
		<option value="TW"><?php _e( 'Taiwan, Province of China', 'tc' ); ?></option>
		<option value="TJ"><?php _e( 'Tajikistan', 'tc' ); ?></option>
		<option value="TZ"><?php _e( 'Tanzania, United Republic of', 'tc' ); ?></option>
		<option value="TH"><?php _e( 'Thailand', 'tc' ); ?></option>
		<option value="TL"><?php _e( 'Timor-Leste', 'tc' ); ?></option>
		<option value="TG"><?php _e( 'Togo', 'tc' ); ?></option>
		<option value="TK"><?php _e( 'Tokelau', 'tc' ); ?></option>
		<option value="TO"><?php _e( 'Tonga', 'tc' ); ?></option>
		<option value="TT"><?php _e( 'Trinidad and Tobago', 'tc' ); ?></option>
		<option value="TN"><?php _e( 'Tunisia', 'tc' ); ?></option>
		<option value="TR"><?php _e( 'Turkey', 'tc' ); ?></option>
		<option value="TM"><?php _e( 'Turkmenistan', 'tc' ); ?></option>
		<option value="TC"><?php _e( 'Turks and Caicos Islands', 'tc' ); ?></option>
		<option value="TV"><?php _e( 'Tuvalu', 'tc' ); ?></option>
		<option value="UG"><?php _e( 'Uganda', 'tc' ); ?></option>
		<option value="UA"><?php _e( 'Ukraine', 'tc' ); ?></option>
		<option value="AE"><?php _e( 'United Arab Emirates', 'tc' ); ?></option>
		<option value="GB"><?php _e( 'United Kingdom', 'tc' ); ?></option>
		<option value="US"><?php _e( 'United States', 'tc' ); ?></option>
		<option value="UM"><?php _e( 'United States Minor Outlying Islands', 'tc' ); ?></option>
		<option value="UY"><?php _e( 'Uruguay', 'tc' ); ?></option>
		<option value="UZ"><?php _e( 'Uzbekistan', 'tc' ); ?></option>
		<option value="VU"><?php _e( 'Vanuatu', 'tc' ); ?></option>
		<option value="VE"><?php _e( 'Venezuela, Bolivarian Republic of', 'tc' ); ?></option>
		<option value="VN"><?php _e( 'Viet Nam', 'tc' ); ?></option>
		<option value="VG"><?php _e( 'Virgin Islands, British', 'tc' ); ?></option>
		<option value="VI"><?php _e( 'Virgin Islands, U.S.', 'tc' ); ?></option>
		<option value="WF"><?php _e( 'Wallis and Futuna', 'tc' ); ?></option>
		<option value="EH"><?php _e( 'Western Sahara', 'tc' ); ?></option>
		<option value="YE"><?php _e( 'Yemen', 'tc' ); ?></option>
		<option value="ZM"><?php _e( 'Zambia', 'tc' ); ?></option>
		<option value="ZW"><?php _e( 'Zimbabwe', 'tc' ); ?></option>
	</select>
	<?php
	$countries = ob_get_clean();
	return $countries;
}

/**
 * Print months
 */
function tc_months_dropdown( $sel = '' ) {
	$output = "<option value=''>--</option>";
	$output .= "<option " . ($sel == 1 ? ' selected' : '') . " value='01'>01 - " . __( 'Jan', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 2 ? ' selected' : '') . "  value='02'>02 - " . __( 'Feb', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 3 ? ' selected' : '') . "  value='03'>03 - " . __( 'Mar', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 4 ? ' selected' : '') . "  value='04'>04 - " . __( 'Apr', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 5 ? ' selected' : '') . "  value='05'>05 - " . __( 'May', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 6 ? ' selected' : '') . "  value='06'>06 - " . __( 'Jun', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 7 ? ' selected' : '') . "  value='07'>07 - " . __( 'Jul', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 8 ? ' selected' : '') . "  value='08'>08 - " . __( 'Aug', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 9 ? ' selected' : '') . "  value='09'>09 - " . __( 'Sep', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 10 ? ' selected' : '') . "  value='10'>10 - " . __( 'Oct', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 11 ? ' selected' : '') . "  value='11'>11 - " . __( 'Nov', 'tc' ) . "</option>";
	$output .= "<option " . ($sel == 12 ? ' selected' : '') . "  value='12'>12 - " . __( 'Dec', 'tc' ) . "</option>";

	return($output);
}

function tc_no_index_no_follow() {//prevent search engines to index a page
	?>
	<meta name='robots' content='noindex,nofollow' />
	<?php
}

function tc_get_order_id_by_name( $slug ) {
	global $wpdb;
	/* $args = array(
	  'post_name'		 => strtolower($slug),
	  'post_type'		 => 'tc_orders',
	  'posts_per_page' => 1,
	  'post_status'	 => 'any'
	  ); */

	$order_post_id	 = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = '%s'", strtolower( $slug ) ) );
	$post			 = get_post( $order_post_id );

	if ( isset( $post ) && !empty( $post ) ) {
		if ( $post->post_name == strtolower( $slug ) ) {
			return $post;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function tc_get_order_status( $field_name = '', $post_id = '' ) {
	$value		 = get_post_status( $post_id );
	$new_value	 = str_replace( '_', ' ', $value );
	if ( $value == 'order_fraud' ) {
		$color = "red";
	} else if ( $value == 'order_received' ) {
		$color = "#ff6600"; //yellow
	} else if ( $value == 'order_paid' ) {
		$color = "green";
	}

	echo sprintf( __( '%1$s %2$s %3$s', 'tc' ), '<font color="' . $color . '">', __( ucwords( $new_value ), 'tc' ), '</font>' );
}

function tc_get_order_front_link( $field_name = '', $post_id = '' ) {
	global $tc, $wp;
	$order = new TC_Order( $post_id );

	echo $tc->tc_order_status_url( $order, $order->details->tc_order_date, 'Order details page' );
}

function tc_get_order_status_select( $field_name = '', $post_id = '' ) {
	$value		 = get_post_status( $post_id );
	$new_value	 = str_replace( '_', ' ', $value );
	?>
	<select class="order_status_change">
		<option value='order_received' <?php selected( $value, 'order_received', true ); ?>><?php _e( 'Order Received', 'tc' ); ?></option>
		<option value='order_paid' <?php selected( $value, 'order_paid', true ); ?>><?php _e( 'Order Paid', 'tc' ); ?></option>
		<option value='order_fraud' <?php selected( $value, 'order_fraud', true ); ?>><?php _e( 'Order Fraud', 'tc' ); ?></option>
	</select>
	<?php
}

function tc_get_order_customer( $field_name = '', $post_id = '' ) {
	$value = get_post_meta( $post_id, $field_name, true );
	echo $value[ 'buyer_data' ][ 'first_name_post_meta' ] . ' ' . $value[ 'buyer_data' ][ 'last_name_post_meta' ];
}

function tc_get_order_customer_email( $field_name = '', $post_id = '' ) {
	$value = get_post_meta( $post_id, $field_name, true );
	echo $value[ 'buyer_data' ][ 'email_post_meta' ];
}

function tc_get_ticket_instance_event( $field_name = false, $field_id = false, $ticket_instance_id ) {
	$ticket_type_id	 = get_post_meta( $ticket_instance_id, 'ticket_type_id', true );
	$ticket_type	 = new TC_Ticket( $ticket_type_id );
	$event_id		 = $ticket_type->get_ticket_event( $ticket_type_id );
	$event			 = new TC_Event( $event_id );
	echo $event->details->post_title;
}

function tc_get_ticket_instance_type( $field_name, $field_id, $ticket_instance_id ) {
	$ticket_type_id	 = get_post_meta( $ticket_instance_id, 'ticket_type_id', true );
	$ticket_type	 = new TC_Ticket( $ticket_type_id );
	echo $ticket_type->details->post_title;
}

function tc_get_ticket_download_link( $field_name, $field_id, $ticket_id ) {
	global $tc, $wp;
	$ticket		 = new TC_Ticket( $ticket_id );
	$order		 = new TC_Order( $ticket->details->post_parent );
	$order_key	 = $wp->query_vars[ 'tc_order_key' ];
	echo '<a href="' . wp_nonce_url( trailingslashit( $tc->get_order_slug( true ) ) . $order->details->post_title . '/' . $order_key . '/?download_ticket=' . $ticket_id . '&order_key=' . $order_key, 'download_ticket_' . $ticket_id . '_' . $order_key, 'download_ticket_nonce' ) . '">' . __( 'Download', 'tc' ) . '</a>';
}

function tc_get_order_details_front( $order_id = '', $order_key = '' ) {
	global $tc;

	$tc_general_settings = get_option( 'tc_general_setting', false );

	$order = new TC_Order( $order_id );

	/* if ( isset( $_GET[ 'download_ticket' ] ) && $order->details->post_status == 'order_paid' ) {
	  if ( isset( $_GET[ 'download_ticket_nonce' ] ) && wp_verify_nonce( $_GET[ 'download_ticket_nonce' ], 'download_ticket_' . (int) $_GET[ 'download_ticket' ] . '_' . $order_key ) ) {
	  $templates = new TC_Ticket_Templates();
	  $templates->generate_preview( (int) $_GET[ 'download_ticket' ], true );
	  }
	  } */

	if ( $order->details->tc_order_date == $order_key ) {//key must match order creation date for security reasons
		if ( $order->details->post_status == 'order_received' ) {
			$order_status = __( 'Pending Payment', 'tc' );
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$order_status = __( 'Under Review', 'tc' );
		} else if ( $order->details->post_status == 'order_paid' ) {
			$order_status = __( 'Payment Completed', 'tc' );
		}

		$fees_total		 = $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'fees_total' ] );
		$tax_total		 = $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'tax_total' ] );
		$subtotal		 = $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'subtotal' ] );
		$total			 = $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'total' ] );
		$transaction_id	 = isset( $order->details->tc_payment_info[ 'transaction_id' ] ) ? $order->details->tc_payment_info[ 'transaction_id' ] : '';
		$order_id		 = strtoupper( $order->details->post_name );
		$order_date		 = $payment_date	 = apply_filters( 'tc_order_date', date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $order->details->tc_order_date, false ) );
		?>

		<label><span class="order_details_title"><?php _e( 'Order: ', 'tc' ); ?></span> <?php echo $order_id; ?></label>
		<label><span class="order_details_title"><?php _e( 'Order date: ', 'tc' ); ?></span> <?php echo $order_date; ?></label>
		<label><span class="order_details_title"><?php _e( 'Order status: ', 'tc' ); ?></span> <?php echo $order_status; ?></label>
		<?php if ( isset( $transaction_id ) && $transaction_id !== '' ) { ?>
			<label><span class="order_details_title"><?php _e( 'Transaction ID: ', 'tc' ); ?></span> <?php echo $transaction_id; ?></label>
		<?php } ?>
		<label><span class="order_details_title"><?php _e( 'Subtotal: ', 'tc' ); ?></span> <?php echo $subtotal; ?></label>
		<?php if ( !isset( $tc_general_settings[ 'show_fees' ] ) || isset( $tc_general_settings[ 'show_fees' ] ) && $tc_general_settings[ 'show_fees' ] == 'yes' ) { ?>
			<label><span class="order_details_title"><?php echo $tc_general_settings[ 'fees_label' ]; ?></span> <?php echo $fees_total; ?></label>
		<?php } ?>
		<?php if ( !isset( $tc_general_settings[ 'show_tax_rate' ] ) || isset( $tc_general_settings[ 'show_tax_rate' ] ) && $tc_general_settings[ 'show_tax_rate' ] == 'yes' ) { ?>
			<label><span class="order_details_title"><?php echo $tc_general_settings[ 'tax_label' ]; ?></span> <?php echo $tax_total; ?></label>
		<?php } ?>
		<hr />
		<label><span class="order_details_title"><?php _e( 'Total: ', 'tc' ); ?></span> <?php echo $total; ?></label>

		<?php
		if ( $order->details->post_status == 'order_paid' ) {
			$orders = new TC_Orders();

			$args = array(
				'posts_per_page' => -1,
				'orderby'		 => 'post_date',
				'order'			 => 'ASC',
				'post_type'		 => 'tc_tickets_instances',
				'post_parent'	 => $order->details->ID
			);

			$tickets = get_posts( $args );
			$columns = $orders->get_owner_info_fields_front();
			$style	 = '';
			?>

			<table class="order-details widefat shadow-table">
				<tr>
					<?php
					foreach ( $columns as $column ) {
						?>
						<th><?php echo $column[ 'field_title' ]; ?></th>
						<?php
					}
					?>
				</tr>

				<?php
				foreach ( $tickets as $ticket ) {
					$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
					?>
					<tr <?php echo $style; ?>>
						<?php
						foreach ( $columns as $column ) {
							?>
							<td>
								<?php
								if ( $column[ 'field_type' ] == 'function' ) {
									eval( $column[ 'function' ] . '("' . $column[ 'field_name' ] . '", "' . (isset( $column[ 'field_id' ] ) ? $column[ 'field_id' ] : '') . '", "' . $ticket->ID . '");' );
								} else {
									if ( $column[ 'post_field_type' ] == 'post_meta' ) {
										echo get_post_meta( $ticket->ID, $column[ 'field_name' ], true );
									}
									if ( $column[ 'post_field_type' ] == 'ID' ) {
										echo $ticket->ID;
									}
								}
								?>
							</td>
						<?php }
						?>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}
	} else {
		_e( "You don't have required permissions to access this page.", 'tc' );
	}
}

function tc_get_order_event( $field_name = '', $post_id = '' ) {

	$orders = new TC_Orders();

	$args	 = array(
		'posts_per_page' => -1,
		'orderby'		 => 'post_date',
		'order'			 => 'ASC',
		'post_type'		 => 'tc_tickets_instances',
		'post_parent'	 => $post_id
	);
	$tickets = get_posts( $args );
	$columns = $orders->get_owner_info_fields();
	$style	 = '';
	?>
	<table class="order-details widefat shadow-table">
		<tr>
			<?php
			foreach ( $columns as $column ) {
				?>
				<th><?php echo $column[ 'field_title' ]; ?></th>
				<?php
			}
			?>
		</tr>

		<?php
		foreach ( $tickets as $ticket ) {
			$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
			?>
			<tr <?php echo $style; ?>>
				<?php
				foreach ( $columns as $column ) {
					?>
					<td>
						<?php
						if ( $column[ 'field_type' ] == 'function' ) {
							eval( $column[ 'function' ] . '("' . $column[ 'field_name' ] . '", "' . (isset( $column[ 'field_id' ] ) ? $column[ 'field_id' ] : '') . '", "' . $ticket->ID . '");' );
						} else {
							if ( $column[ 'post_field_type' ] == 'post_meta' ) {
								$value = get_post_meta( $ticket->ID, $column[ 'field_name' ], true );
								if ( empty( $value ) ) {
									echo '-';
								} else {
									echo $value;
								}
							}
							if ( $column[ 'post_field_type' ] == 'ID' ) {
								echo $ticket->ID;
							}
						}
						?>
					</td>
				<?php }
				?>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
}

function tc_get_order_date( $field_name = '', $post_id = '' ) {
	$value = get_post_meta( $post_id, $field_name, true );
	echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $value, false );
}

function tc_get_order_tickets_info( $field_name = '', $post_id = '' ) {
	
}

function tc_get_order_gateway( $field_name = '', $post_id = '' ) {
	$order = new TC_Order( $post_id );
	echo $order->details->tc_cart_info[ 'gateway_admin_name' ];
}

function tc_get_order_transaction_id( $field_name = '', $post_id = '' ) {
	$order = new TC_Order( $post_id );
	echo $order->details->tc_payment_info[ 'transaction_id' ];
}

function tc_get_order_discount_info( $field_name = '', $post_id = '' ) {
	$discounts		 = new TC_Discounts();
	$discount_total	 = $discounts->get_discount_total_by_order( $post_id );

	if ( $discount_total > 0 ) {
		$discount_total = apply_filters( 'tc_cart_currency_and_format', $discount_total );
	} else {
		$discount_total = '-';
	}
	echo $discount_total;
}

function tc_get_order_total( $field_name = '', $post_id = '' ) {
	global $tc;
	$order = new TC_Order( $post_id );
	echo $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'total' ] );
}

function tc_get_order_subtotal( $field_name = '', $post_id = '' ) {
	global $tc;
	$order = new TC_Order( $post_id );
	echo $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'subtotal' ] );
}

function tc_get_order_fees_total( $field_name = '', $post_id = '' ) {
	global $tc;
	$order = new TC_Order( $post_id );
	echo $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'fees_total' ] );
}

function tc_get_order_tax_total( $field_name = '', $post_id = '' ) {
	global $tc;
	$order = new TC_Order( $post_id );
	echo $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'tax_total' ] );
}

function tc_get_order_download_tickets_link( $field_name = '', $post_id = '' ) {
	
}

function tc_get_ticket_type_form_field( $field_name = '', $field_type = '', $ticket_type_id = '', $ticket_type_count ) {
	?>
	<input type="hidden" name="owner_data_<?php echo $field_name . '_' . $field_type; ?>[]" value="<?php echo $ticket_type_id; ?>" />
	<?php
}

/* Get ticket fees type drop down */

function tc_get_ticket_fee_type( $field_name = '', $post_id = '' ) {
	if ( $post_id !== '' ) {
		$currently_selected = get_post_meta( $post_id, $field_name, true );
	} else {
		$currently_selected = '';
	}
	?>
	<select name="<?php echo $field_name; ?>_post_meta">
		<option value="fixed" <?php selected( $currently_selected, 'fixed', true ); ?>><?php _e( 'Fixed', 'tc' ); ?></option>
		<option value="percentage" <?php selected( $currently_selected, 'percentage', true ); ?>><?php _e( 'Percentage', 'tc' ); ?></option>
	</select>
	<?php
}

/* Get ticket templates drop down */

function tc_get_ticket_templates( $field_name = '', $post_id = '' ) {
	$wp_templates_search = new TC_Templates_Search( '', '', -1 );
	if ( $post_id !== '' ) {
		$currently_selected = get_post_meta( $post_id, $field_name, true );
	} else {
		$currently_selected = '';
	}
	?>
	<select name="<?php echo $field_name; ?>_post_meta">
		<?php
		foreach ( $wp_templates_search->get_results() as $template ) {

			$template_obj	 = new TC_Event( $template->ID );
			$template_object = $template_obj->details;
			?>
			<option value="<?php echo $template_object->ID; ?>" <?php selected( $currently_selected, $template_object->ID, true ); ?>><?php echo $template_object->post_title; ?></option>
			<?php
		}
		?>
	</select>
	<?php
	if ( isset( $_GET[ 'ID' ] ) ) {
		$ticket		 = new TC_Ticket( (int) $_GET[ 'ID' ] );
		$template_id = $ticket->details->ticket_template;
		?>
		<a class="ticket_preview_link" target="_blank" href="<?php echo apply_filters( 'ticket_preview_link', admin_url( 'admin.php?page=tc_ticket_templates&action=preview&ticket_type_id=' . (int) $_GET[ 'ID' ] ) . '&template_id=' . $template_id ); ?>"><?php _e( 'Preview', 'tc' ); ?></a>
		<?php
	}
}

/* Get events drop down */

function tc_get_api_keys_events( $field_name = '', $post_id = '' ) {
	$wp_events_search = new TC_Events_Search( '', '', -1 );
	if ( $post_id !== '' ) {
		$currently_selected = get_post_meta( $post_id, $field_name, true );
	} else {
		$currently_selected = '';
	}
	?>
	<select name="<?php echo $field_name; ?>_post_meta">
		<?php
		foreach ( $wp_events_search->get_results() as $event ) {

			$event_obj		 = new TC_Event( $event->ID );
			$event_object	 = $event_obj->details;
			?>
			<option value="<?php echo $event_object->ID; ?>" <?php selected( $currently_selected, $event_object->ID, true ); ?>><?php echo $event_object->post_title; ?></option>
			<?php
		}
		?>
	</select>
	<?php
}

function tc_ticket_limit_types( $field_name = '', $post_id = '' ) {
	if ( $post_id !== '' ) {
		$currently_selected = get_post_meta( $post_id, $field_name, true );
	} else {
		$currently_selected = '';
	}
	?>
	<select name="<?php echo $field_name; ?>_post_meta" id="tickets_limit_type">
		<?php ?>
		<option value="ticket_level" <?php selected( $currently_selected, 'ticket_level', true ); ?>><?php echo __( 'Ticket Type' ); ?></option>
		<option value="event_level" <?php selected( $currently_selected, 'event_level', true ); ?>><?php echo __( 'Event' ); ?></option>
		<?php ?>
	</select>
	<?php
}

/* Get events drop down */

function tc_get_quantity_sold( $field_name = '', $post_id = '' ) {
	return $post_id;
}

function tc_get_events( $field_name = '', $post_id = '' ) {
	$wp_events_search = new TC_Events_Search( '', '', '-1' );
	if ( $post_id !== '' ) {
		$currently_selected = get_post_meta( $post_id, $field_name, true );
	} else {
		$currently_selected = '';
	}
	?>
	<select name="<?php echo $field_name; ?>_post_meta">
		<?php
		foreach ( $wp_events_search->get_results() as $event ) {

			$event_obj		 = new TC_Event( $event->ID );
			$event_object	 = $event_obj->details;
			?>
			<option value="<?php echo $event_object->ID; ?>" <?php selected( $currently_selected, $event_object->ID, true ); ?>><?php echo $event_object->post_title; ?></option>
			<?php
		}
		?>
	</select>
	<?php
}

/* Get tickets drop down */

function tc_get_ticket_types( $field_name = '', $post_id = '' ) {
	$wp_tickets_search = new TC_Tickets_Search( '', '', -1 );
	if ( $post_id !== '' ) {
		$currently_selected = get_post_meta( $post_id, $field_name, true );
	} else {
		$currently_selected = '';
	}
	?>
	<select name="<?php echo $field_name; ?>_post_meta">
		<option value="" <?php selected( $currently_selected, '', true ); ?>><?php _e( 'All', 'tc' ); ?></option>
		<?php
		foreach ( $wp_tickets_search->get_results() as $ticket ) {

			$ticket_obj		 = new TC_Ticket( $ticket->ID );
			$ticket_object	 = $ticket_obj->details;

			$event_id	 = $ticket_object->event_name;
			$event_obj	 = new TC_Event( $event_id );
			?>
			<option value="<?php echo $ticket_object->ID; ?>" <?php selected( $currently_selected, $ticket_object->ID, true ); ?>><?php echo $ticket_object->post_title . ' (' . $event_obj->details->post_title . ')'; ?></option>
			<?php
		}
		?>
	</select>
	<?php
}

/* Get discount type */

function tc_get_discount_types( $field_name = '', $post_id = '' ) {
	if ( $post_id !== '' ) {
		$currently_selected = get_post_meta( $post_id, $field_name, true );
	} else {
		$currently_selected = '';
	}
	?>
	<select name="<?php echo $field_name; ?>_post_meta" class="postform" id="<?php echo $field_name; ?>">
		<option value="1" <?php selected( $currently_selected, '1', true ); ?>><?php _e( 'Fixed Amount', 'tc' ); ?></option>
		<option value="2" <?php selected( $currently_selected, '2', true ); ?>><?php _e( 'Percentage (%)', 'tc' ); ?></option>
	</select>
	<?php
}

function search_array( $array, $key, $value ) {
	$results = array();

	if ( is_array( $array ) ) {
		if ( isset( $array[ $key ] ) && $array[ $key ] == $value )
			$results[] = $array;

		foreach ( $array as $subarray )
			$results = array_merge( $results, search_array( $subarray, $key, $value ) );
	}

	return $results;
}

function tc_is_post_field( $post_field = '' ) {
	if ( in_array( $post_field, tc_post_fields() ) ) {
		return true;
	} else {
		return false;
	}
}

function tc_post_fields() {
	$post_fields = array(
		'ID',
		'post_author',
		'post_date',
		'post_date_gmt',
		'post_content',
		'post_title',
		'post_excerpt',
		'post_status',
		'comment_status',
		'ping_status',
		'post_password',
		'post_name',
		'to_ping',
		'pinged',
		'post_modified',
		'post_modified_gmt',
		'post_content_filtered',
		'post_parent',
		'guid',
		'menu_order',
		'post_type',
		'post_mime_type',
		'comment_count'
	);
	return $post_fields;
}

function tc_get_post_meta_all( $post_id ) {
	global $wpdb;
	$data = array();

	$wpdb->query( $wpdb->prepare( "
        SELECT `meta_key`, `meta_value`
        FROM " . $wpdb->postmeta . "
        WHERE `post_id` = %d", $post_id ) );

	foreach ( $wpdb->last_result as $k => $v ) {
		$data[ $v->meta_key ] = $v->meta_value;
	};

	return $data;
}

function tc_hex2rgb( $hex ) {
	$hex = str_replace( "#", "", $hex );

	if ( strlen( $hex ) == 3 ) {
		$r	 = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
		$g	 = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
		$b	 = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
	} else {
		$r	 = hexdec( substr( $hex, 0, 2 ) );
		$g	 = hexdec( substr( $hex, 2, 2 ) );
		$b	 = hexdec( substr( $hex, 4, 2 ) );
	}
	$rgb = array( $r, $g, $b );
	return $rgb; // returns an array with the rgb values
}

if ( !function_exists( 'json_encode' ) ) {

	function json_encode( $a = false ) {
		if ( is_null( $a ) )
			return 'null';
		if ( $a === false )
			return 'false';
		if ( $a === true )
			return 'true';
		if ( is_scalar( $a ) ) {
			if ( is_float( $a ) ) {
				return floatval( str_replace( ",", ".", strval( $a ) ) );
			}

			if ( is_string( $a ) ) {
				static $jsonReplaces = array( array( "\\", "/", "\n", "\t", "\r", "\b", "\f", '"' ), array( '\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"' ) );
				return '"' . str_replace( $jsonReplaces[ 0 ], $jsonReplaces[ 1 ], $a ) . '"';
			} else
				return $a;
		}
		$isList = true;
		for ( $i = 0, reset( $a ); $i < count( $a ); $i++, next( $a ) ) {
			if ( key( $a ) !== $i ) {
				$isList = false;
				break;
			}
		}
		$result = array();
		if ( $isList ) {
			foreach ( $a as $v )
				$result[] = json_encode( $v );
			return '[' . join( ',', $result ) . ']';
		} else {
			foreach ( $a as $k => $v )
				$result[] = json_encode( $k ) . ':' . json_encode( $v );
			return '{' . join( ',', $result ) . '}';
		}
	}

}

function ticket_code_to_id( $ticket_code ) {
	$args = array(
		'posts_per_page' => 1,
		'meta_key'		 => 'ticket_code',
		'meta_value'	 => $ticket_code,
		'post_type'		 => 'tc_tickets_instances'
	);

	$result = get_posts( $args );

	if ( $result[ 0 ] ) {
		return $result[ 0 ]->ID;
	} else {
		return false;
	}
}

function tc_checkout_step_url( $checkout_step ) {
	return apply_filters( 'tc_checkout_step_url', trailingslashit( home_url() ) . trailingslashit( $checkout_step ) );
}

function current_url() {
	$pageURL = 'http';
	if ( isset( $_SERVER[ "HTTPS" ] ) && $_SERVER[ "HTTPS" ] == "on" ) {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ( isset( $_SERVER[ "SERVER_PORT" ] ) && $_SERVER[ "SERVER_PORT" ] != "80" ) {
		$pageURL .= $_SERVER[ "SERVER_NAME" ] . ":" . $_SERVER[ "SERVER_PORT" ] . $_SERVER[ "REQUEST_URI" ];
	} else {
		$pageURL .= $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ];
	}
	return $pageURL;
}

if ( !function_exists( 'tc_write_log' ) ) {

	function tc_write_log( $log ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}

}


require_once("internal-hooks.php");
?>