<?php

/*
  Payment Gateway API
 */
if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Gateway_API' ) ) {

	class TC_Gateway_API {

		var $plugin_name		 = '';
		var $admin_name		 = '';
		var $public_name		 = '';
		var $method_img_url	 = '';
		var $admin_img_url	 = '';
		var $force_ssl		 = false;
		var $ipn_url;

		function on_creation() {
			
		}

		function payment_form( $cart ) {
			
		}

		function process_payment( $cart ) {
			wp_die( __( "You must override the process_payment() method in your {$this->admin_name} payment gateway plugin!", 'tc' ) );
		}

		function order_confirmation( $order ) {
			wp_die( __( "You must override the order_confirmation() method in your {$this->admin_name} payment gateway plugin!", 'tc' ) );
		}

		function order_confirmation_email( $msg, $order ) {
			return $msg;
		}

		function order_confirmation_message( $order ) {
			wp_die( __( "You must override the order_confirmation_message() method in your {$this->admin_name} payment gateway plugin!", 'tc' ) );
		}

		function gateway_admin_settings( $settings, $visible ) {
			
		}

		function process_gateway_settings( $settings ) {

			return $settings;
		}

		function ipn() {
			
		}

		function _generate_ipn_url() {
			global $tc;
			$this->ipn_url = home_url( trailingslashit( $tc->get_payment_gateway_return_slug() ) . $this->plugin_name );
		}

		function _checkout_confirmation_hook() {
			global $wp_query, $tc;

			if ( $wp_query->query_vars[ 'pagename' ] == 'cart' ) {
				if ( isset( $wp_query->query_vars[ 'checkoutstep' ] ) && $wp_query->query_vars[ 'checkoutstep' ] == 'confirmation' )
					do_action( 'tc_checkout_payment_pre_confirmation_' . $_SESSION[ 'tc_payment_method' ], $tc->get_order( $_SESSION[ 'tc_order' ] ) );
			}
		}

		function __construct() {

			$this->_generate_ipn_url();

			$this->on_creation();

			if ( empty( $this->plugin_name ) || empty( $this->admin_name ) || empty( $this->public_name ) )
				wp_die( __( "Please override all required variables in your {$this->admin_name} payment gateway.", 'tc' ) );

			add_action( 'tc_gateway_settings', array( &$this, 'gateway_admin_settings' ) );
			add_action( 'tc_handle_payment_return_' . $this->plugin_name, array( &$this, 'ipn' ) );

			add_action( 'template_redirect', array( &$this, '_checkout_confirmation_hook' ) );
			add_filter( 'tc_checkout_confirm_payment_' . $this->plugin_name, array( &$this, 'confirm_payment_form' ), 10, 2 );
			add_action( 'tc_payment_confirm_' . $this->plugin_name, array( &$this, 'process_payment' ), 10, 2 );
			add_filter( 'tc_order_notification_' . $this->plugin_name, array( &$this, 'order_confirmation_email' ), 10, 2 );
			add_action( 'tc_checkout_payment_pre_confirmation_' . $this->plugin_name, array( &$this, 'order_confirmation' ) );
			add_filter( 'tc_checkout_payment_confirmation_' . $this->plugin_name, array( &$this, 'order_confirmation_message' ), 10, 2 );
			add_filter( 'tc_gateway_settings_filter', array( &$this, 'process_gateway_settings' ) );
		}

	}

}

/**
 * Use this function to register your gateway plugin class
 *
 * @param string $class_name - the case sensitive name of your plugin class
 * @param string $plugin_name - the sanitized private name for your plugin
 * @param string $admin_name - pretty name of your gateway, for the admin side.
 * @param bool $global optional - whether the gateway supports global checkouts
 */
function tc_register_gateway_plugin( $class_name, $plugin_name, $admin_name, $global = false, $demo = false ) {
	global $tc_gateway_plugins;

	if ( !is_array( $tc_gateway_plugins ) ) {
		$tc_gateway_plugins = array();
	}

	if ( class_exists( $class_name ) ) {
		$tc_gateway_plugins[ $plugin_name ] = array( $class_name, $admin_name, $global, $demo );
	} else {
		return false;
	}
}

?>