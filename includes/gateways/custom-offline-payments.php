<?php
/*
  Custom Offline Payments Gateway
 */

class TC_Gateway_Custom_Offline_Payments extends TC_Gateway_API {

	var $plugin_name				 = 'custom_offline_payments';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = false;
	var $ipn_url;
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = false;

	function on_creation() {
		global $tc;

		$this->admin_name		 = $tc->get_setting( 'gateways->custom_offline_payments->admin_name' ) ? $tc->get_setting( 'gateways->custom_offline_payments->admin_name', __( 'Offline Payment', 'tc' ) ) : __( 'Offline Payment', 'tc' );
		$this->public_name		 = $tc->get_setting( 'gateways->custom_offline_payments->public_name' ) ? $tc->get_setting( 'gateways->custom_offline_payments->public_name', __( 'Cash on Delivery', 'tc' ) ) : __( 'Cash on Delivery', 'tc' );
		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/custom-offline-payments.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-custom-offline-payments.png';
		add_action( 'tc_order_created', array( &$this, 'send_payment_instructions' ), 10, 5 );
	}

	function payment_form( $cart ) {
		global $tc;
		return $tc->get_setting( 'gateways->custom_offline_payments->info' );
	}

	function send_payment_instructions( $order_id, $status, $cart_contents, $cart_info, $payment_info ) {
		global $tc, $order_instructions_sent;

		$send_instructions_value = $tc->get_setting( 'gateways->custom_offline_payments->instructions_email' );
		$send_instructions		 = isset( $send_instructions_value ) ? $send_instructions_value : 0;

		if ( $send_instructions == 1 && $status == 'order_received' ) {
			add_filter( 'wp_mail_content_type', 'set_content_type' );
			add_filter( 'wp_mail_from', 'client_email_from_email', 999 );
			add_filter( 'wp_mail_from_name', 'client_email_from_name', 999 );

			$client_headers	 = '';
			$to				 = $cart_info[ 'buyer_data' ][ 'email_post_meta' ];
			$message		 = $tc->get_setting( 'gateways->custom_offline_payments->instructions' );
			$subject		 = $tc->get_setting( 'gateways->custom_offline_payments->instructions_email_subject' );

			if ( $order_instructions_sent !== $cart_info[ 'buyer_data' ][ 'email_post_meta' ] ) {
				wp_mail( $to, $subject, apply_filters( 'tc_order_created_client_email_message', $message ), apply_filters( 'tc_order_created_client_email_headers', $client_headers ) );
				$order_instructions_sent = $cart_info[ 'buyer_data' ][ 'email_post_meta' ];
			}
		}
	}

	function process_payment( $cart ) {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$cart_contents = $tc->get_cart_cookie();

		if ( !session_id() ) {
			session_start();
		}

		$cart_total = $_SESSION[ 'tc_cart_total' ];

		$discounted_total								 = isset( $_SESSION[ 'discounted_total' ] ) ? $_SESSION[ 'discounted_total' ] : '';
		$_SESSION[ 'cart_info' ][ 'gateway' ]			 = $this->plugin_name;
		$_SESSION[ 'cart_info' ][ 'gateway_admin_name' ] = $this->admin_name;
		$_SESSION[ 'cart_info' ][ 'gateway_class' ]		 = get_class( $this );
		$subtotal										 = $_SESSION[ 'tc_cart_subtotal' ];
		$fees_total										 = $_SESSION[ 'tc_total_fees' ];
		$tax_total										 = $_SESSION[ 'tc_tax_value' ];

		$cart_info = $_SESSION[ 'cart_info' ];

		if ( isset( $discounted_total ) && is_numeric( $discounted_total ) ) {
			$total = round( $discounted_total, 2 );
		} else {
			$total = round( $cart_total, 2 );
		}

		$order_id	 = $tc->generate_order_id();
		$buyer_email = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] : '';

		$payment_info							 = array();
		$payment_info[ 'gateway_public_name' ]	 = $this->public_name;
		$payment_info[ 'gateway_private_name' ]	 = $this->admin_name;
		$payment_info[ 'method' ]				 = $this->admin_name;
		$payment_info[ 'transaction_id' ]		 = $order_id;
		$payment_info[ 'subtotal' ]				 = $subtotal;
		$payment_info[ 'fees_total' ]			 = $fees_total;
		$payment_info[ 'tax_total' ]			 = $tax_total;
		$payment_info[ 'total' ]				 = $total;
		$payment_info[ 'currency' ]				 = $tc->get_cart_currency();

		if ( !isset( $_SESSION ) ) {
			session_start();
		}

		$_SESSION[ 'tc_payment_info' ]	 = $payment_info;
		$order							 = $tc->create_order( $order_id, $cart_contents, $cart_info, $payment_info, false );

		wp_redirect( $tc->get_confirmation_slug( true, $order_id ) );
		exit;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;

		$tc_payment_info = isset( $_SESSION[ 'tc_payment_info' ] ) ? $_SESSION[ 'tc_payment_info' ] : $payment_info;

		$total = $tc_payment_info[ 'total' ];

		$automatic_status = $tc->get_setting( 'gateways->custom_offline_payments->automatic_status' );

		$paid = false;

		if ( $total > 0 ) {//get default status for 100% discount and/or free orders
			if ( $automatic_status == 'order_paid' ) {
				$paid = true;
			} else {
				$paid = false;
			}
		}

		$order = tc_get_order_id_by_name( $order );
		$tc->update_order_payment_status( $order->ID, $paid );
	}

	function order_confirmation_email( $msg, $order = null ) {
		global $tc;
	}

	function order_confirmation_message( $order, $cart_info = '' ) {
		global $tc;

		$cart_info = isset( $_SESSION[ 'cart_info' ] ) ? $_SESSION[ 'cart_info' ] : $cart_info;

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via %s for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), $this->public_name, $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via %s for this order totaling <strong>%s</strong> is complete.', 'tc' ), $this->public_name, $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );
		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$content .= '<br /><br />' . $tc->get_setting( 'gateways->custom_offline_payments->instructions' );

		$tc->remove_order_session_data();

		return $content;
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style = "display:none;"' : ''); ?>>
			<h3 class='handle'><span><?php _e( 'Offline Payments', 'tc' ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php _e( 'Track offline / custom payments (Cash on Delivery, Money Orders, Bank Deposits, Cheques etc.) manually.', 'tc' ) ?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="custom_offline_payments_public_name"><?php _e( 'Public Method Name', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Insert public name for this payment method which will be visible to buyers', 'tc' ) ?></span>
							<p>
								<input value="<?php echo esc_attr( $tc->get_setting( 'gateways->custom_offline_payments->public_name' ) ? $tc->get_setting( 'gateways->custom_offline_payments->public_name', __( 'Cash on Delivery', 'tc' ) ) : __( 'Cash on Delivery', 'tc' )  ); ?>" style="width: 100%;" name="tc[gateways][custom_offline_payments][public_name]" id="custom_offline_payments_public_name" type="text" />
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="custom_offline_payments_admin_name"><?php _e( 'Admin Method Name', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Insert admin name for this payment method which will be visible within the administration panel (Orders screen etc.)', 'tc' ) ?></span>
							<p>
								<input value="<?php echo esc_attr( $tc->get_setting( 'gateways->custom_offline_payments->admin_name' ) ? $tc->get_setting( 'gateways->custom_offline_payments->admin_name', __( 'Offline Payment', 'tc' ) ) : __( 'Offline Payment', 'tc' )  ); ?>" style="width: 100%;" name="tc[gateways][custom_offline_payments][admin_name]" id="custom_offline_payments_admin_name" type="text" />
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="custom_offline_payments_user_info"><?php _e( 'Payment Method Info', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Information about the payment method which will be visible to user upon choosing this payment method.', 'tc' ) ?></span>
							<p>
								<?php wp_editor( html_entity_decode(stripcslashes($tc->get_setting( 'gateways->custom_offline_payments->info' ))), 'custom_offline_payments_info', array( 'textarea_name' => 'tc[gateways][custom_offline_payments][info]', 'textarea_rows' => 2 ) ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="custom_offline_payments_instructions"><?php _e( 'Payment Instructions', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Your customers who checkout using the custom offline payment method will be given a set of instructions (set by you) to complete the purchase process immediately after checkout completion.', 'tc' ) ?></span>
							<p>
								<?php wp_editor( html_entity_decode(stripcslashes($tc->get_setting( 'gateways->custom_offline_payments->instructions' ))), 'custom_offline_payments_instructions', array( 'textarea_name' => 'tc[gateways][custom_offline_payments][instructions]', 'textarea_rows' => 5 ) ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="custom_offline_payments_instructions_email"><?php _e( 'E-mail Instructions', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Send an email with the payment instructions to a customer upon creating an order. The e-mail will be sent only if status of a order is "Order Received".', 'tc' ) ?></span>
							<p>
								<?php
								$send_instructions_value		 = $tc->get_setting( 'gateways->custom_offline_payments->instructions_email' );
								$send_instructions				 = isset( $send_instructions_value ) ? $send_instructions_value : 0;
								?>
								<select name="tc[gateways][custom_offline_payments][instructions_email]">
									<option value="0" <?php selected( $send_instructions, 0, true ); ?>><?php _e( 'No', 'tc' ); ?></option>
									<option value="1" <?php selected( $send_instructions, 1, true ); ?>><?php _e( 'Yes', 'tc' ); ?></option>
								</select>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="custom_offline_payments_instructions_email_subject"><?php _e( 'Instructions E-mail Subject', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Subject of the payment instructions e-mail', 'tc' ) ?></span>
							<p>
								<?php
								$send_instructions_subject_value = $tc->get_setting( 'gateways->custom_offline_payments->instructions_email_subject' );
								$send_instructions_subject		 = isset( $send_instructions_subject_value ) ? $send_instructions_subject_value : __( 'Payment Instructions', 'tc' );
								?>
								<input type="text" name="tc[gateways][custom_offline_payments][instructions_email_subject]" value="<?php echo esc_attr( $send_instructions_subject ); ?>" />
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="custom_offline_payments_automatic_payment_status"><?php _e( 'Automatic Payment Status', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Automatic payment status which will be set for all custom offline payment orders.', 'tc' ) ?></span>
							<p>
								<?php $automatic_status				 = $tc->get_setting( 'gateways->custom_offline_payments->automatic_status' ); ?>
								<select name="tc[gateways][custom_offline_payments][automatic_status]">
									<option value="order_received" <?php selected( $automatic_status, 'order_received', true ); ?>><?php _e( 'Order Received', 'tc' ); ?></option>
									<option value="order_paid" <?php selected( $automatic_status, 'order_paid', true ); ?>><?php _e( 'Order Paid', 'tc' ); ?></option>
									<?php do_action( 'custom_offline_payments_automatic_statuses' ); ?>
								</select>
							</p>
						</td>
					</tr>

				</table>

			</div>
		</div>
		<?php
	}

	function process_gateway_settings( $settings ) {

		if ( isset( $settings[ 'gateways' ][ 'custom_offline_payments' ] ) && !is_array( $settings[ 'gateways' ][ 'custom_offline_payments' ] ) )
			return $settings;

		$settings[ 'gateways' ][ 'custom_offline_payments' ] = array_map( 'stripslashes', (array) $settings[ 'gateways' ][ 'custom_offline_payments' ] );

		$settings[ 'gateways' ][ 'custom_offline_payments' ][ 'public_name' ] = stripslashes( wp_filter_nohtml_kses( $settings[ 'gateways' ][ 'custom_offline_payments' ][ 'public_name' ] ) );

		if ( !current_user_can( 'unfiltered_html' ) ) {
			$settings[ 'gateways' ][ 'custom_offline_payments' ][ 'instructions' ]	 = wp_filter_post_kses( $settings[ 'gateways' ][ 'custom_offline_payments' ][ 'instructions' ] );
			$settings[ 'gateways' ][ 'custom_offline_payments' ][ 'confirmation' ]	 = wp_filter_post_kses( $settings[ 'gateways' ][ 'custom_offline_payments' ][ 'info' ] );
		}

		return $settings;
	}

	function ipn() {
		
	}

}

tc_register_gateway_plugin( 'TC_Gateway_Custom_Offline_Payments', 'custom_offline_payments', __( 'Offline Payments', 'tc' ) );
?>