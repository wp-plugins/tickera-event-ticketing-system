<?php
/*
  2Checkout - Payment Gateway
 */

class TC_Gateway_2Checkout extends TC_Gateway_API {

	var $plugin_name				 = 'checkout';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = false;
	var $ipn_url;
	var $API_Username, $API_Password, $SandboxFlag, $returnURL, $cancelURL, $API_Endpoint, $version, $currencyCode, $locale;
	var $currencies				 = array();
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = true;

	function on_creation() {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$this->admin_name	 = __( '2Checkout', 'tc' );
		$this->public_name	 = __( '2Checkout', 'tc' );

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/2checkout.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-2checkout.png';

		if ( isset( $settings[ 'gateways' ][ '2checkout' ] ) ) {
			$this->currencyCode	 = isset( $settings[ 'gateways' ][ '2checkout' ][ 'currency' ] ) ? $settings[ 'gateways' ][ '2checkout' ][ 'currency' ] : 'USD';
			$this->API_Username	 = $settings[ 'gateways' ][ '2checkout' ][ 'sid' ];
			$this->API_Password	 = $settings[ 'gateways' ][ '2checkout' ][ 'secret_word' ];
			$this->SandboxFlag	 = $settings[ 'gateways' ][ '2checkout' ][ 'mode' ];
		}

		$currencies = array(
			"AED"	 => __( 'AED - United Arab Emirates Dirham', 'tc' ),
			"ARS"	 => __( 'ARS - Argentina Peso', 'tc' ),
			"AUD"	 => __( 'AUD - Australian Dollar', 'tc' ),
			"BRL"	 => __( 'BRL - Brazilian Real', 'tc' ),
			"CAD"	 => __( 'CAD - Canadian Dollar', 'tc' ),
			"CHF"	 => __( 'CHF - Swiss Franc', 'tc' ),
			"DKK"	 => __( 'DKK - Danish Krone', 'tc' ),
			"EUR"	 => __( 'EUR - Euro', 'tc' ),
			"GBP"	 => __( 'GBP - British Pound', 'tc' ),
			"HKD"	 => __( 'HKD - Hong Kong Dollar', 'tc' ),
			"INR"	 => __( 'INR - Indian Rupee', 'tc' ),
			"ILS"	 => __( 'ILS - Israeli New Shekel', 'tc' ),
			"LTL"	 => __( 'LTL - Lithuanian Litas', 'tc' ),
			"JPY"	 => __( 'JPY - Japanese Yen', 'tc' ),
			"MYR"	 => __( 'MYR - Malaysian Ringgit', 'tc' ),
			"MXN"	 => __( 'MXN - Mexican Peso', 'tc' ),
			"NOK"	 => __( 'NOK - Norwegian Krone', 'tc' ),
			"NZD"	 => __( 'NZD - New Zealand Dollar', 'tc' ),
			"PHP"	 => __( 'PHP - Philippine Peso', 'tc' ),
			"RON"	 => __( 'RON - Romanian New Leu', 'tc' ),
			"RUB"	 => __( 'RUB - Russian Ruble', 'tc' ),
			"SEK"	 => __( 'SEK - Swedish Krona', 'tc' ),
			"SGD"	 => __( 'SGD - Singapore Dollar', 'tc' ),
			"TRY"	 => __( 'TRY - Turkish Lira', 'tc' ),
			"USD"	 => __( 'USD - U.S. Dollar', 'tc' ),
			"ZAR"	 => __( 'ZAR - South African Rand', 'tc' )
		);

		$this->currencies = $currencies;
	}

	function payment_form( $cart ) {
		global $tc;
		if ( isset( $_GET[ '2checkout_cancel' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Your transaction has been canceled.', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		}
	}

	function process_payment( $cart ) {
		global $tc;

		$settings		 = get_option( 'tc_settings' );
		$cart_contents	 = $tc->get_cart_cookie();

		if ( $this->SandboxFlag == 'sandbox' ) {
			//$url = 'https://sandbox.2checkout.com/checkout/purchase';
			$url = 'https://www.2checkout.com/checkout/purchase';
		} else {
			$url = 'https://www.2checkout.com/checkout/purchase';
		}

		$buyer_first_name	 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'first_name_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'first_name_post_meta' ] : '';
		$buyer_last_name	 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'last_name_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'last_name_post_meta' ] : '';
		$buyer_full_name	 = $buyer_first_name . ' ' . $buyer_last_name;
		$buyer_email		 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] : '';

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

		$order_id = $tc->generate_order_id();

		$params							 = array();
		$params[ 'total' ]				 = $total;
		$params[ 'sid' ]				 = $this->API_Username;
		$params[ 'cart_order_id' ]		 = $order_id;
		$params[ 'merchant_order_id' ]	 = $order_id;
		$params[ 'return_url' ]			 = $tc->get_confirmation_slug( true, $order_id );
		$params[ 'x_receipt_link_url' ]	 = $tc->get_confirmation_slug( true, $order_id ); //trailingslashit( $this->ipn_url ) . trailingslashit( $order_id );
		$params[ 'skip_landing' ]		 = '1';
		$params[ 'fixed' ]				 = 'Y';
		$params[ 'currency_code' ]		 = $this->currencyCode;
		$params[ 'mode' ]				 = '2CO';
		$params[ 'card_holder_name' ]	 = $buyer_full_name;
		$params[ 'email' ]				 = $buyer_email;

		if ( $this->SandboxFlag == 'sandbox' ) {
			$params[ 'demo' ] = 'Y';
		}

		//$counter	 = 0;
		//$cart_total	 = 0;

		/* foreach ( $cart_contents as $ticket_type => $ordered_count ) {
		  $ticket										 = new TC_Ticket( $ticket_type );
		  $cart_total									 = $cart_total + ($ticket->details->price_per_ticket * $ordered_count);
		  $sku										 = $ticket_type;
		  $params[ "li_" . $counter . "_type" ]		 = "product";
		  $params[ "li_" . $counter . "_name" ]		 = $ticket->details->post_title;
		  $params[ "li_" . $counter . "_price" ]		 = $ticket->details->price_per_ticket;
		  $params[ "li_" . $counter . "_tangible" ]	 = 'N';
		  $counter++;
		  } */

		$params[ "li_0_type" ]		 = "product";
		$params[ "li_0_name" ]		 = apply_filters( 'tc_item_name_2checkout', __( 'Order: #', 'tc' ) . $order_id );
		$params[ "li_0_price" ]		 = $total;
		$params[ "li_0_tangible" ]	 = 'N';

		if ( !isset( $_SESSION ) ) {
			session_start();
		}

		$payment_info[ 'subtotal' ]		 = $subtotal;
		$payment_info[ 'fees_total' ]	 = $fees_total;
		$payment_info[ 'tax_total' ]	 = $tax_total;

		$param_list = array();

		foreach ( $params as $k => $v ) {
			$param_list[] = "{$k}=" . rawurlencode( $v );
		}

		$param_str = implode( '&', $param_list );

		$paid = false;

		$payment_info							 = array();
		$payment_info[ 'gateway_public_name' ]	 = $this->public_name;
		$payment_info[ 'gateway_private_name' ]	 = $this->admin_name;
		$payment_info[ 'method' ]				 = $this->admin_name;
		//$payment_info[ 'transaction_id' ]		 = $charge[ 'id' ];
		$payment_info[ 'total' ]				 = $total;
		$payment_info[ 'subtotal' ]				 = $subtotal;
		$payment_info[ 'fees_total' ]			 = $fees_total;
		$payment_info[ 'tax_total' ]			 = $tax_total;
		$payment_info[ 'currency' ]				 = $this->currency;

		if ( !isset( $_SESSION ) ) {
			session_start();
		}

		$_SESSION[ 'tc_payment_info' ] = $payment_info;

		$tc->create_order( $order_id, $cart_contents, $cart_info, $payment_info, $paid );

		wp_redirect( "{$url}?{$param_str}" );
		exit( 0 );
	}

	function order_confirmation_message( $order, $cart_info = '' ) {
		global $tc;

		$cart_info = isset( $_SESSION[ 'cart_info' ] ) ? $_SESSION[ 'cart_info' ] : $cart_info;

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via 2Checkout for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via 2Checkout for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format',  $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );

		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$tc->remove_order_session_data();

		return $content;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;

		$settings = get_option( 'tc_settings' );

		$total = $_REQUEST[ 'total' ];

		$hashSecretWord	 = $settings[ 'gateways' ][ '2checkout' ][ 'secret_word' ]; //2Checkout Secret Word
		$hashSid		 = $settings[ 'gateways' ][ '2checkout' ][ 'sid' ]; //2Checkout account number
		$hashTotal		 = $total; //Sale total to validate against
		$hashOrder		 = $_REQUEST[ 'order_number' ]; //2Checkout Order Number

		if ( $this->SandboxFlag == 'sandbox' ) {
			$StringToHash = strtoupper( md5( $hashSecretWord . $hashSid . 1 . $hashTotal ) );
		} else {
			$StringToHash = strtoupper( md5( $hashSecretWord . $hashSid . $hashOrder . $hashTotal ) );
		}

		if ( $StringToHash != $_REQUEST[ 'key' ] ) {
			$tc->update_order_status( $order->ID, 'order_fraud' );
		} else {
			$paid	 = true;
			$order	 = tc_get_order_id_by_name( $order );
			$tc->update_order_payment_status( $order->ID, true );
		}

		$this->ipn();
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;

		$settings		 = get_option( 'tc_settings' );
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='handle'><span><?php _e( '2Checkout', 'tc' ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php
					_e( 'Sell your tickets via 2Checkout.com.', 'tc' );
					?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Mode', 'tc' ) ?></th>
						<td>
							<p>
								<select name="tc[gateways][2checkout][mode]">
									<option value="sandbox" <?php selected( isset( $settings[ 'gateways' ][ '2checkout' ][ 'mode' ] ) ? $settings[ 'gateways' ][ '2checkout' ][ 'mode' ] : '', 'sandbox' ) ?>><?php _e( 'Sandbox', 'tc' ) ?></option>
									<option value="live" <?php selected( isset( $settings[ 'gateways' ][ '2checkout' ][ 'mode' ] ) ? $settings[ 'gateways' ][ '2checkout' ][ 'mode' ] : '', 'live' ) ?>><?php _e( 'Live', 'tc' ) ?></option>
								</select>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( '2Checkout Credentials', 'tc' ) ?></th>
						<td>
							<span class="description"><?php print sprintf( __( 'Login to your 2Checkout dashboard to obtain the seller ID and secret word. <a target="_blank" href="%s">Instructions &raquo;</a>', 'tc' ), "http://help.2checkout.com/articles/FAQ/Where-do-I-set-up-the-Secret-Word/" ); ?></span>
							<p>
								<label><?php _e( 'Seller ID', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ '2checkout' ][ 'sid' ] ) ? $settings[ 'gateways' ][ '2checkout' ][ 'sid' ] : ''  ); ?>" name="tc[gateways][2checkout][sid]" type="text" />
								</label>
							</p>
							<p>
								<label><?php _e( 'Secret Word', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ '2checkout' ][ 'secret_word' ] ) ? $settings[ 'gateways' ][ '2checkout' ][ 'secret_word' ] : 'tango'  ); ?>"  name="tc[gateways][2checkout][secret_word]" type="text" />
								</label>
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( '2Checkout Currency', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Select 2Checkout currency. Make sure it matches global currency set in the General Settings.', 'tc' ); ?></span><br />

							<select name="tc[gateways][2checkout][currency]">
								<?php
								$sel_currency	 = (isset( $settings[ 'gateways' ][ '2checkout' ][ 'currency' ] )) ? $settings[ 'gateways' ][ '2checkout' ][ 'currency' ] : 'USD';

								$currencies = $this->currencies;

								foreach ( $currencies as $k => $v ) {

									echo '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html( $v, true ) . '</option>' . "\n";
								}
								?>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}

	function process_gateway_settings( $settings ) {
		return $settings;
	}

	function ipn() {
		global $tc;

		$settings = get_option( 'tc_settings' );

		if ( isset( $_REQUEST[ 'message_type' ] ) && $_REQUEST[ 'message_type' ] == 'INVOICE_STATUS_CHANGED' ) {
			$sale_id			 = $_REQUEST[ 'sale_id' ]; //just for calculating hash
			$tco_vendor_order_id = $_REQUEST[ 'vendor_order_id' ]; //order "name"
			$total				 = $_REQUEST[ 'invoice_list_amount' ];

			$order_id	 = tc_get_order_id_by_name( $tco_vendor_order_id ); //get order id from order name
			$order_id	 = $order_id->ID;
			$order		 = new TC_Order( $order_id );

			if ( !$order ) {
				header( 'HTTP/1.0 404 Not Found' );
				header( 'Content-type: text/plain; charset=UTF-8' );
				echo 'Invoice not found';
				exit;
			}

			$hash = md5( $sale_id . $settings[ 'gateways' ][ '2checkout' ][ 'sid' ] . $_REQUEST[ 'invoice_id' ] . $settings[ 'gateways' ][ '2checkout' ][ 'secret_word' ] );

			if ( $_REQUEST[ 'md5_hash' ] != strtolower( $hash ) ) {
				header( 'HTTP/1.0 403 Forbidden' );
				header( 'Content-type: text/plain; charset=UTF-8' );
				echo "2Checkout hash key doesn't match";
				exit;
			}

			if ( strtolower( $_REQUEST[ 'invoice_status' ] ) != "deposited" ) {
				header( 'HTTP/1.0 200 OK' );
				header( 'Content-type: text/plain; charset=UTF-8' );
				echo 'Waiting for deposited invoice status.';
				exit;
			}

			if ( intval( round( $total, 2 ) ) >= round( $order->details->tc_payment_info[ 'total' ], 2 ) ) {
				$tc->update_order_payment_status( $order_id, true );
				header( 'HTTP/1.0 200 OK' );
				header( 'Content-type: text/plain; charset=UTF-8' );
				echo 'Order completed and verified.';
				exit;
			} else {
				$tc->update_order_status( $order_id, 'order_fraud' );
				header( 'HTTP/1.0 200 OK' );
				header( 'Content-type: text/plain; charset=UTF-8' );
				echo 'Fraudulent order detected and changed status.';
				exit;
			}
		}
	}

}

tc_register_gateway_plugin( 'TC_Gateway_2Checkout', 'checkout', __( '2Checkout', 'tc' ) );
?>