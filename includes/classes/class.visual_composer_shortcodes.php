<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_VC_Shortcodes' ) ) {

	class TC_VC_Shortcodes {

		function __construct() {
			add_action( 'vc_before_init', array( &$this, 'add_tickera_shortcodes' ) );
		}

		function add_tickera_shortcodes() {
			vc_map( array(
				'name'						 => __( 'Cart', 'tc' ),
				'description'				 => __( 'Display the cart contents', 'tc' ),
				'base'						 => 'tc_cart',
				'class'						 => 'tc_vc_icon',
				'category'					 => __( 'Tickera', 'tc' ),
				'show_settings_on_create'	 => false
			/* 'params'	 => array(
			  array(
			  'type'			 => 'textfield',
			  'holder'		 => 'div',
			  'class'			 => '',
			  'heading'		 => __( 'Text', 'my-text-domain' ),
			  'param_name'	 => 'foo',
			  'value'			 => __( 'Default param value', 'my-text-domain' ),
			  'description'	 => __( 'Description for foo param.', 'my-text-domain' )
			  )
			  ) */
			) );
		}

	}

	$TC_VC_Shortcodes = new TC_VC_Shortcodes();
}