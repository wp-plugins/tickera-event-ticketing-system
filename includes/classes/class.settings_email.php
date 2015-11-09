<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Settings_Email' ) ) {

	class TC_Settings_Email {

		function __construct() {
			
		}

		function TC_Settings_Email() {
			$this->__construct();
		}

		function get_settings_email_sections() {
			$sections = array(
				array(
					'name'			 => 'client_order_completed_email',
					'title'			 => __( 'Client Order Completed E-Mail' ),
					'description'	 => '',
				),
				array(
					'name'			 => 'admin_order_completed_email',
					'title'			 => __( 'Admin Order Completed E-Mail' ),
					'description'	 => '',
				),
				array(
					'name'			 => 'misc_email',
					'title'			 => __( 'Miscellaneous' ),
					'description'	 => '',
				),
			);

			$sections = apply_filters( 'tc_settings_email_sections', $sections );

			return $sections;
		}

		function get_settings_email_fields() {

			$tc_email_settings = get_option( 'tc_email_setting', false );

			$client_order_completed_email_fields = array(
				array(
					'field_name'		 => 'client_order_subject',
					'field_title'		 => __( 'Subject', 'tc' ),
					'field_type'		 => 'option',
					'default_value'		 => __( 'Order Completed', 'tc' ),
					'field_description'	 => __( 'Subject of the e-mail', 'tc' ),
					'section'			 => 'client_order_completed_email'
				),
				array(
					'field_name'		 => 'client_order_from_name',
					'field_title'		 => __( 'From Name', 'tc' ),
					'field_type'		 => 'option',
					'default_value'		 => get_option( 'blogname' ),
					'field_description'	 => __( 'This name will appear as sent from name in the e-mail', 'tc' ),
					'section'			 => 'client_order_completed_email'
				),
				array(
					'field_name'		 => 'client_order_from_email',
					'field_title'		 => __( 'From E-mail Address', 'tc' ),
					'field_type'		 => 'option',
					'default_value'		 => get_option( 'admin_email' ),
					'field_description'	 => __( 'This e-mail will appear as sender address' ),
					'section'			 => 'client_order_completed_email'
				),
				array(
					'field_name'		 => 'client_order_message',
					'field_title'		 => __( 'Order Message', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_get_client_order_message',
					'default_value'		 => 'Hello, <br /><br />Your order (ORDER_ID) totalling <strong>ORDER_TOTAL</strong> is completed. <br /><br />You can download your tickets DOWNLOAD_URL',
					'field_description'	 => __( 'Body of the e-mail. You can use following placeholders (ORDER_ID, ORDER_TOTAL, DOWNLOAD_URL, BUYER_NAME, ORDER_DETAILS)', 'tc' ),
					'section'			 => 'client_order_completed_email'
				),
				array(
					'field_name'		 => 'client_send_message',
					'field_title'		 => __( 'Send E-Mails', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_client_send_order_messages',
					'default_value'		 => 'yes',
					'field_description'	 => __( 'Whether to send or not e-mail upon each completed order)', 'tc' ),
					'section'			 => 'client_order_completed_email'
				),
			);

			$admin_order_completed_email_fields = array(
				array(
					'field_name'		 => 'admin_order_subject',
					'field_title'		 => __( 'Subject', 'tc' ),
					'field_type'		 => 'option',
					'default_value'		 => __( 'New Order Completed', 'tc' ),
					'field_description'	 => __( 'Subject of the e-mail', 'tc' ),
					'section'			 => 'admin_order_completed_email'
				),
				array(
					'field_name'		 => 'admin_order_from_name',
					'field_title'		 => __( 'From Name', 'tc' ),
					'field_type'		 => 'option',
					'default_value'		 => get_option( 'blogname' ),
					'field_description'	 => __( 'This name will appear as sent from name in the e-mail', 'tc' ),
					'section'			 => 'admin_order_completed_email'
				),
				array(
					'field_name'		 => 'admin_order_from_email',
					'field_title'		 => __( 'From E-mail Address', 'tc' ),
					'field_type'		 => 'option',
					'default_value'		 => get_option( 'admin_email' ),
					'field_description'	 => __( 'This e-mail will appear as sender address' ),
					'section'			 => 'admin_order_completed_email'
				),
				array(
					'field_name'		 => 'admin_order_message',
					'field_title'		 => __( 'Order Message', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_get_admin_order_message',
					'default_value'		 => 'Hello, <br /><br />a new order (ORDER_ID) totalling <strong>ORDER_TOTAL</strong> has been placed. <br /><br />You can check the order details here ORDER_ADMIN_URL',
					'field_description'	 => __( 'Body of the e-mail. You can use following placeholders (ORDER_ID, ORDER_TOTAL, ORDER_ADMIN_URL, BUYER_NAME)', 'tc' ),
					'section'			 => 'admin_order_completed_email'
				),
				array(
					'field_name'		 => 'admin_send_message',
					'field_title'		 => __( 'Send E-Mails', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_admin_send_order_messages',
					'default_value'		 => 'yes',
					'field_description'	 => __( 'Whether to send or not e-mail upon each completed order)', 'tc' ),
					'section'			 => 'admin_order_completed_email'
				),
			);

			$misc_email_fields = array(
				array(
					'field_name'		 => 'email_send_type',
					'field_title'		 => __( 'E-mail Send Type', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_email_send_type',
					'default_value'		 => 'wp_mail',
					'field_description'	 => __( 'Whether to send e-mails via wp_mail or mail function. If wp_mail for some reason fails sending emails, try using standard php "mail" function.', 'tc' ),
					'section'			 => 'misc_email'
				)
			);

			$default_fields = array_merge( $client_order_completed_email_fields, $admin_order_completed_email_fields, $misc_email_fields );

			return apply_filters( 'tc_settings_email_fields', $default_fields );
		}

	}

}
?>
