<?php
/*
  Free Orders
 */

class TC_Gateway_Free_Orders extends TC_Gateway_API {

	var $plugin_name				 = 'free_orders';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = false;
	var $ipn_url;
	var $automatically_activated	 = true;
	var $skip_payment_screen		 = false;

	function on_creation() {
		global $tc;

		$this->admin_name	 = $tc->get_setting( 'gateways->free_orders->admin_name' ) ? $tc->get_setting( 'gateways->free_orders->admin_name', __( 'Free Orders', 'tc' ) ) : __( 'Free Orders', 'tc' );
		$this->public_name	 = $tc->get_setting( 'gateways->free_orders->public_name' ) ? $tc->get_setting( 'gateways->free_orders->public_name', __( 'Free Orders', 'tc' ) ) : __( 'Free Orders', 'tc' );

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/free-orders.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-free-orders.png';
	}

	function payment_form( $cart ) {
		global $tc;
		return $tc->get_setting( 'gateways->free_orders->info' );
	}

	function process_payment( $cart ) {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$cart_contents = $tc->get_cart_cookie();

		if ( !session_id() ) {
			session_start();
		}

		$cart_total = $_SESSION[ 'tc_cart_total' ];

		if ( !isset( $_SESSION ) ) {
			session_start();
		}

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

		$order_id = $tc->generate_order_id();

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

		$_SESSION[ 'tc_payment_info' ] = $payment_info;

		$order = $tc->create_order( $order_id, $cart_contents, $cart_info, $payment_info, false );

		wp_redirect( $tc->get_confirmation_slug( true, $order_id ) );
		exit;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;

		$tc_payment_info = isset( $_SESSION[ 'tc_payment_info' ] ) ? $_SESSION[ 'tc_payment_info' ] : $payment_info;
		$total			 = $tc_payment_info[ 'total' ];

		$zero_total_status = $tc->get_setting( 'gateways->free_orders->zero_total_status' );

		$paid = false;

		if ( !isset( $zero_total_status ) || $zero_total_status == '' ) {
			$zero_total_status	 = 'order_paid';
			$paid				 = true;
		}

		if ( $total == 0 ) {//get default status for 100% discount and/or free orders
			if ( $zero_total_status == 'order_paid' ) {
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
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Review' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via %s for this order totaling <strong>%s</strong> is complete.', 'tc' ), $this->public_name, $tc->get_cart_currency_and_format( $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );
		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$content .= '<br /><br />' . $tc->get_setting( 'gateways->free_orders->instructions' );

		$tc->remove_order_session_data();

		return $content;
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='handle'><span><?php _e( 'Free Orders', 'tc' ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php _e( 'This method will be automatically activated if order total is 0 (zero). This is the only method which will be shown to buyers in this case - other payment options will be hidden.', 'tc' ) ?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="free_orders_public_name"><?php _e( 'Public Method Name', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Insert public name for this payment method which will be visible to buyers', 'tc' ) ?></span>
							<p>
								<input value="<?php echo esc_attr( $tc->get_setting( 'gateways->free_orders->public_name' ) ? $tc->get_setting( 'gateways->free_orders->public_name', __( 'Free Orders', 'tc' ) ) : __( 'Free Orders', 'tc' )  ); ?>" style="width: 100%;" name="tc[gateways][free_orders][public_name]" id="free_orders_public_name" type="text" />
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="free_orders_admin_name"><?php _e( 'Admin Method Name', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Insert admin name for this payment method which will be visible within the administration panel (Orders screen etc.)', 'tc' ) ?></span>
							<p>
								<input value="<?php echo esc_attr( $tc->get_setting( 'gateways->free_orders->admin_name' ) ? $tc->get_setting( 'gateways->free_orders->admin_name', __( 'Free Orders', 'tc' ) ) : __( 'Free Orders', 'tc' )  ); ?>" style="width: 100%;" name="tc[gateways][free_orders][admin_name]" id="free_orders_admin_name" type="text" />
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="free_orders_user_info"><?php _e( 'Payment Method Info', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Information about the payment method which will be visible to user.', 'tc' ) ?></span>
							<p>
								<?php wp_editor( $tc->get_setting( 'gateways->free_orders->info' ), 'free_orders_info', array( 'textarea_name' => 'tc[gateways][free_orders][info]', 'textarea_rows' => 2 ) ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="free_orders_instructions"><?php _e( 'Confirmation Message', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'An additional message shown on the payment confirmation page.', 'tc' ) ?></span>
							<p>
								<?php wp_editor( $tc->get_setting( 'gateways->free_orders->instructions' ), 'free_orders_instructions', array( 'textarea_name' => 'tc[gateways][free_orders][instructions]', 'textarea_rows' => 5 ) ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="free_orders_zero_total_status"><?php _e( 'Free Order Status', 'tc' ) ?></label></th>
						<td>
							<span class="description"><?php _e( 'Automatic payment status which will be set for each free order. For instance, you can automatically set status for all orders with 100% discount code or orders with free tickets to "Order Paid".', 'tc' ) ?></span>
							<p>
								<?php $zero_total_status = $tc->get_setting( 'gateways->free_orders->zero_total_status' ); ?>
								<select name="tc[gateways][free_orders][zero_total_status]">
									<option value="order_paid" <?php selected( $zero_total_status, 'order_paid', true ); ?>><?php _e( 'Order Paid', 'tc' ); ?></option>
									<option value="order_received" <?php selected( $zero_total_status, 'order_received', true ); ?>><?php _e( 'Order Received', 'tc' ); ?></option>
									<?php do_action( 'free_orders_zero_total_statuses' ); ?>
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

		if ( isset( $settings[ 'gateways' ][ 'free_orders' ] ) && !is_array( $settings[ 'gateways' ][ 'free_orders' ] ) )
			return $settings;

		$settings[ 'gateways' ][ 'free_orders' ] = array_map( 'stripslashes', (array) $settings[ 'gateways' ][ 'free_orders' ] );

		$settings[ 'gateways' ][ 'free_orders' ][ 'public_name' ] = stripslashes( wp_filter_nohtml_kses( $settings[ 'gateways' ][ 'free_orders' ][ 'public_name' ] ) );

		if ( !current_user_can( 'unfiltered_html' ) ) {
			$settings[ 'gateways' ][ 'free_orders' ][ 'instructions' ]	 = wp_filter_post_kses( $settings[ 'gateways' ][ 'free_orders' ][ 'instructions' ] );
			$settings[ 'gateways' ][ 'free_orders' ][ 'info' ]			 = wp_filter_post_kses( $settings[ 'gateways' ][ 'free_orders' ][ 'info' ] );
		}
		return $settings;
	}

	function ipn() {
		
	}

}

tc_register_gateway_plugin( 'TC_Gateway_Free_Orders', 'free_orders', __( 'Free Orders', 'tc' ) );
?>