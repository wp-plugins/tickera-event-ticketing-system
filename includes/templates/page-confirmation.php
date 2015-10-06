<?php

global $tc, $wp;

$tc_order_return = isset( $wp->query_vars[ 'tc_order_return' ] ) ? $wp->query_vars[ 'tc_order_return' ] : '';

if ( $tc_order_return !== '' ) {
	$order			 = tc_get_order_id_by_name( $tc_order_return );
	$order			 = new TC_Order( $order->ID );
	$gateway_class	 = $order->details->tc_cart_info[ 'gateway_class' ];
	$payment_info	 = $order->details->tc_payment_info;
	$cart_info		 = $order->details->tc_cart_info;
}

$cart_info_cookie	 = $tc->get_cart_info_cookie();
$order_cookie		 = $tc->get_order_cookie();

$payment_class_name = $gateway_class; //isset($gateway_class) ? $gateway_class : isset($_SESSION[ 'cart_info' ]) && isset( $_SESSION[ 'cart_info' ][ 'gateway_class' ] ) ? $_SESSION[ 'cart_info' ][ 'gateway_class' ] : (isset($cart_info_cookie[ 'gateway_class' ]) ? $cart_info_cookie[ 'gateway_class' ] : '');

$payment_gateway = new $payment_class_name;

$order_id = isset( $tc_order_return ) ? $tc_order_return : (isset( $_SESSION[ 'tc_order' ] ) ? $_SESSION[ 'tc_order' ] : (isset( $order_cookie ) && !empty( $order_cookie ) ? $order_cookie : ''));

do_action( 'tc_track_order_confirmation', $order_id, isset( $payment_info ) ? $payment_info : '', isset( $cart_info ) ? $cart_info : ''  );

$payment_gateway->order_confirmation( $order_id, isset( $payment_info ) ? $payment_info : '', isset( $cart_info ) ? $cart_info : ''  );

echo $payment_gateway->order_confirmation_message( $order_id, isset( $cart_info ) ? $cart_info : ''  );
