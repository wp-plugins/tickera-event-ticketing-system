<?php
global $tc;
$cart_contents = $tc->get_cart_cookie();

$tc_general_settings = get_option( 'tc_general_setting', false );

if ( isset( $tc_general_settings[ 'force_login' ] ) && $tc_general_settings[ 'force_login' ] == 'yes' && !is_user_logged_in() ) {
	?>
	<div class="force_login_message"><?php printf( __( 'Please %s to see this page', 'tc' ), '<a href="' . wp_login_url( $tc->get_payment_slug( true ) ) . '">' . __( 'Log In', 'tc' ) . '</a>' ); ?></div>
	<?php
} else {
	if ( empty( $cart_contents ) ) {
		wp_redirect( $tc->get_cart_slug( true ) );
		exit;
	}
	$tc->cart_payment( true );
}
?>
