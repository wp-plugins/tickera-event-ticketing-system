<?php

/*
  Addon Name: Delete Pending Orders
  Description: Delete pending orders (which are not paid for 12 hours or more). Note: all pending orders will be deleted made via all payment gateways except Free Orders and Offline Payments
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Delete_Pending_Orders' ) ) {

	class TC_Delete_Pending_Orders {

		var $version		 = '1.0';
		var $title		 = 'Delete Pending Orders';
		var $name		 = 'tc';
		var $dir_name	 = 'delete-pending-orders';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {
			$this->title = __( 'Delete Pending Orders', 'tc' );
			add_filter( 'tc_general_settings_miscellaneous_fields', array( &$this, 'delete_pending_orders_misc_settings_field' ) );
			add_action( 'save_tc_general_settings', array( &$this, 'schedule_delete_pending_orders_event' ) );
			add_action( 'tc_maybe_delete_pending_posts_hook', array( &$this, 'tc_maybe_delete_pending_posts' ) );
		}

		function delete_pending_orders_misc_settings_field( $settings_fields ) {

			$new_default_fields	 = array(
				array(
					'field_name'		 => 'delete_pending_orders',
					'field_title'		 => __( 'Delete Pending Orders', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_radio_checkbox',
					'default_value'		 => 'no',
					'field_description'	 => __( 'Delete pending orders (which are not paid for 12 hours or more). Note: all pending orders will be deleted made via all payment gateways except Free Orders and Offline Payments', 'tc' ),
					'section'			 => 'miscellaneous_settings'
				),
			);
			$default_fields		 = array_merge( $settings_fields, $new_default_fields );
			return $default_fields;
		}

		function schedule_delete_pending_orders_event() {
			if ( !wp_next_scheduled( 'tc_maybe_delete_pending_posts_hook' ) ) {
				wp_schedule_event( time(), 'hourly', 'tc_maybe_delete_pending_posts_hook' );
			}
			$this->tc_maybe_delete_pending_posts();
		}

		function tc_maybe_delete_pending_posts() {
			global $wpdb;
			$pending_orders = $wpdb->get_results( 'SELECT ID FROM ' . $wpdb->posts . '  WHERE post_date < (NOW() - INTERVAL 12 HOUR) AND post_type = "tc_orders" AND post_status = "order_received"', OBJECT );

			foreach ( $pending_orders as $pending_order ) {

				$order = new TC_Order( $pending_order->ID );
				if ( $order->details->tc_cart_info[ 'gateway_class' ] == 'TC_Gateway_Custom_Offline_Payments' || $order->details->tc_cart_info[ 'gateway_class' ] == 'TC_Gateway_Free_Orders' ) {
					//do not delete pending orders
				} else {
					//delete pending orders
					$order->delete_order( false );
				}
			}
		}

	}

}

$tc_delete_pending_orders = new TC_Delete_Pending_Orders();
?>