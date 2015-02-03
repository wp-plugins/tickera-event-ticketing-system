<?php

global $tc, $tc_gateway_plugins;

$cart_total		 = 0;
$cart_contents	 = $tc->get_cart_cookie();

$payment_class_name	 = $tc_gateway_plugins[ $_POST[ 'tc_choose_gateway' ] ][ 0 ];
$payment_gateway	 = new $payment_class_name;

if ( !session_id() ) {
	session_start();
}

$cart_total = $_SESSION[ 'tc_cart_total' ];

if ( $tc->checkout_error == false ) {
	$payment_gateway->process_payment( $cart_contents );
	exit;
} else {
	wp_safe_redirect( $this->get_payment_slug( true ) );
	exit;
}
?>