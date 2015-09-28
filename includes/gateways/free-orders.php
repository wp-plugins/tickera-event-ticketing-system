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
	var $skip_payment_screen		 = true;

	//Support for older payment gateway API
	function on_creation() {
		$this->init();
	}

	function init() {
		global $tc;
		$this->skip_payment_screen	 = apply_filters( $this->plugin_name . '_skip_payment_screen', $this->skip_payment_screen );
		$this->admin_name			 = $this->get_option( 'admin_name', __( 'Free Orders', 'tc' ) );
		$this->public_name			 = $this->get_option( 'public_name', __( 'Free Orders', 'tc' ) );

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/free-orders.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-free-orders.png';

		add_filter( 'tc_redirect_gateway_message', array( &$this, 'custom_redirect_message' ), 10, 1 );
	}

	function custom_redirect_message( $message ) {
		return __( 'Redirecting to the confirmation page...', 'tc' );
	}

	function payment_form( $cart ) {
		return get_option( 'info' );
	}

	function process_payment( $cart ) {
		global $tc;

		$this->maybe_start_session();
		$this->save_cart_info();

		$order_id = $tc->generate_order_id();

		$$payment_info				 = array();
		$payment_info[ 'currency' ]	 = $tc->get_cart_currency();
		$payment_info				 = $this->save_payment_info( $payment_info );

		$order = $tc->create_order( $order_id, $this->cart_contents(), $this->cart_info(), $payment_info, false );

		wp_redirect( $tc->get_confirmation_slug( true, $order_id ) );
		exit;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;

		$tc_payment_info = isset( $_SESSION[ 'tc_payment_info' ] ) ? $_SESSION[ 'tc_payment_info' ] : $payment_info;
		$total			 = $tc_payment_info[ 'total' ];

		$zero_total_status = $this->get_option( 'zero_total_status' );

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

	function order_confirmation_message( $order, $cart_info = '' ) {
		global $tc;

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via %s for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), $this->public_name, apply_filters( 'tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Review' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via %s for this order totaling <strong>%s</strong> is complete.', 'tc' ), $this->public_name, apply_filters( 'tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );
		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$content .= '<br /><br />' . $tc->get_setting( 'gateways->free_orders->instructions' );

		$tc->remove_order_session_data();
		$tc->maybe_skip_confirmation_screen( $this, $order );
		return $content;
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='handle'><span><?php printf( __( '%s Settings', 'tc' ), $this->admin_name ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php _e( 'This method will be automatically activated if order total is 0 (zero). This is the only method which will be shown to buyers in this case - other payment options will be hidden.', 'tc' ) ?></span>
				<?php
				$fields = array(
					'public_name'		 => array(
						'title'			 => __( 'Public Method Name', 'tc' ),
						'type'			 => 'text',
						'description'	 => __( 'Insert public name for this payment method which will be visible to buyers', 'tc' ),
						'default'		 => $this->public_name
					),
					'admin_name'		 => array(
						'title'			 => __( 'Admin Method Name', 'tc' ),
						'type'			 => 'text',
						'description'	 => __( 'Insert admin name for this payment method which will be visible within the administration panel (Orders screen etc.)', 'tc' ),
						'default'		 => $this->admin_name
					),
					'info'				 => array(
						'title'			 => __( 'Payment Method Info', 'tc' ),
						'type'			 => 'wp_editor',
						'description'	 => __( 'Information about the payment method which will be visible to user.', 'tc' )
					),
					'instructions'		 => array(
						'title'			 => __( 'Payment Instructions', 'tc' ),
						'type'			 => 'wp_editor',
						'description'	 => __( 'An additional message shown on the payment confirmation page.', 'tc' )
					),
					'zero_total_status'	 => array(
						'title'			 => __( 'Automatic Payment Status', 'tc' ),
						'type'			 => 'select',
						'options'		 => array(
							'order_received' => __( 'Order Received', 'tc' ),
							'order_paid'	 => __( 'Order Paid', 'tc' )
						),
						'default'		 => 'order_received',
						'description'	 => __( 'Automatic payment status which will be set for all custom offline payment orders.', 'tc' )
					),
				);

				$form = new TC_Form_Fields_API( $fields, 'tc', 'gateways', $this->plugin_name );
				?>
				<table class="form-table">
					<?php $form->admin_options(); ?>
				</table>

			</div>
		</div>
		<?php
	}

}

tc_register_gateway_plugin( 'TC_Gateway_Free_Orders', 'free_orders', __( 'Free Orders', 'tc' ) );
?>