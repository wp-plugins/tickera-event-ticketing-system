<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Discounts' ) ) {

	class TC_Discounts {

		var $form_title				 = '';
		var $discount_message		 = '';
		var $valid_admin_fields_type	 = array( 'text', 'textarea', 'image', 'function' );

		function __construct() {
			$this->form_title				 = __( 'Discount Codes', 'tc' );
			$this->valid_admin_fields_type	 = apply_filters( 'tc_valid_admin_fields_type', $this->valid_admin_fields_type );
		}

		function TC_Discounts() {
			$this->__construct();
		}

		function get_discount_total_by_order( $order_id ) {
			global $tc;

			$discount_value = 0;

			$cart_info			 = get_post_meta( $order_id, 'tc_cart_info', false );
			$cart_info_single	 = get_post_meta( $order_id, 'tc_cart_info', true );
			$cart_contents		 = get_post_meta( $order_id, 'tc_cart_contents', true );

			$current_date	 = date( "Y-m-d H:i:s", get_post_meta( $order_id, 'tc_order_date', true ) );
			$total			 = $cart_info_single[ 'total' ];

			$total_cart = 0;

			$discount_object = get_page_by_title( $cart_info_single[ 'coupon_code' ], OBJECT, 'tc_discounts' );

			if ( !empty( $discount_object ) ) {
				$discount_object = new TC_Discount( $discount_object->ID );

				/*
				 * $discount_object->details->usage_limit
				 */

				if ( is_numeric( trim( $discount_object->id ) ) ) {//discount object is not empty means discount code is entered
					if ( $discount_object->details->expiry_date >= $current_date ) {

						$usage_limit = ($discount_object->details->usage_limit !== '' ? $discount_object->details->usage_limit : 9999999); //set "unlimited" if empty

						$number_of_discount_uses	 = 0; //get real number of discount code uses
						$discount_codes_available	 = $usage_limit - $number_of_discount_uses;

						if ( $discount_object->details->discount_availability == '' ) {//unlimited
							foreach ( $cart_contents as $ticket_type_id => $ordered_count ) {

								$ticket			 = new TC_Ticket( $ticket_type_id );
								$ticket_price	 = $ticket->details->price_per_ticket;
								$total_cart		 = $total_cart + ($ticket_price * $ordered_count);

								$discount_value_per_each = ($discount_object->details->discount_type == 1 ? $discount_object->details->discount_value : (($ticket_price / 100) * $discount_object->details->discount_value));

								$max_discount = ($ordered_count >= $discount_codes_available ? $discount_codes_available : $ordered_count);

								for ( $i = 1; $i <= (int) $max_discount; $i++ ) {
									$discount_value				 = $discount_value + $discount_value_per_each;
									$number_of_discount_uses++;
									$discount_codes_available	 = $discount_object->details->usage_limit - $number_of_discount_uses;
									//$max_discount = ($ordered_count >= $discount_codes_available ? $discount_codes_available : $ordered_count);
								}


								$i = 1;
							}
						} else {
							//check ticket marked in availability is in the cart first
							$is_in_cart = false;

							foreach ( $cart_contents as $ticket_type_id => $ordered_count ) {

								$ticket			 = new TC_Ticket( $ticket_type_id );
								$ticket_price	 = $ticket->details->price_per_ticket;
								$total_cart		 = $total_cart + ($ticket_price * $ordered_count);

								if ( $ticket_type_id == $discount_object->details->discount_availability ) {
									$is_in_cart = true;
									break;
								}
							}

							if ( $is_in_cart ) {

								$ticket			 = new TC_Ticket( $discount_object->details->discount_availability );
								$ticket_price	 = $ticket->details->price_per_ticket;

								$discount_value_per_each = ($discount_object->details->discount_type == 1 ? $discount_object->details->discount_value : (($ticket_price / 100) * $discount_object->details->discount_value));

								$max_discount = ($ordered_count >= $discount_codes_available ? $discount_codes_available : $ordered_count);

								for ( $i = 1; $i <= $max_discount; $i++ ) {
									$discount_value				 = $discount_value + $discount_value_per_each;
									$number_of_discount_uses++;
									$discount_codes_available	 = $discount_object->details->usage_limit - $number_of_discount_uses;
									$max_discount				 = ($ordered_count >= $discount_codes_available ? $discount_codes_available : $ordered_count);
								}
							}
						}
					}
				}
			}

			$new_total = $total_cart - $discount_value;

			return $discount_value;
		}

		function discounted_cart_total( $total = false, $discount_code = '' ) {
			global $tc, $discount, $discount_value_total, $init_total, $new_total;

			$cart_subtotal = 0;

			if ( empty( $discount ) ) {
				$discount = new TC_Discounts();
			}

			if ( !$total ) {

				$cart_contents = $tc->get_cart_cookie();
				foreach ( $cart_contents as $ticket_type => $ordered_count ) {
					$ticket			 = new TC_Ticket( $ticket_type );
					$cart_subtotal	 = $cart_subtotal + ($ticket->details->price_per_ticket * $ordered_count);
				}

				if ( !isset( $_SESSION ) ) {
					session_start();
				}

				$_SESSION[ 'tc_cart_subtotal' ] = $cart_subtotal;
			}

			$cart_contents = $tc->get_cart_cookie();

			$discount_value	 = 0;
			$current_date	 = date( "Y-m-d H:i:s" );

			if ( $discount_code == '' ) {
				$discount_code = (isset( $_POST[ 'coupon_code' ] ) ? $_POST[ 'coupon_code' ] : '');
			}

			$discount_object = get_page_by_title( $discount_code, OBJECT, 'tc_discounts' );

			if ( !empty( $discount_object ) && $discount_object->post_status == 'publish') {
				$discount_object = new TC_Discount( $discount_object->ID );

				/*
				 * $discount_object->details->usage_limit
				 */

				if ( is_numeric( trim( $discount_object->id ) ) ) {//discount object is not empty means discount code is entered
					if ( $discount_object->details->expiry_date >= $current_date ) {

						$usage_limit = ($discount_object->details->usage_limit !== '' ? $discount_object->details->usage_limit : 9999999); //set "unlimited" if empty

						$number_of_discount_uses	 = 0; //get real number of discount code uses
						$discount_codes_available	 = $usage_limit - $number_of_discount_uses;

						if ( $discount_object->details->discount_availability == '' ) {//unlimited
							foreach ( $cart_contents as $ticket_type_id => $ordered_count ) {

								$ticket			 = new TC_Ticket( $ticket_type_id );
								$ticket_price	 = $ticket->details->price_per_ticket;

								$discount_value_per_each = ($discount_object->details->discount_type == 1 ? $discount_object->details->discount_value : (($ticket_price / 100) * $discount_object->details->discount_value));

								$max_discount = ($ordered_count >= $discount_codes_available ? $discount_codes_available : $ordered_count);

								for ( $i = 1; $i <= (int) $max_discount; $i++ ) {
									$discount_value				 = $discount_value + $discount_value_per_each;
									$number_of_discount_uses++;
									$discount_codes_available	 = $usage_limit - $number_of_discount_uses;
									//$max_discount = ($ordered_count >= $discount_codes_available ? $discount_codes_available : $ordered_count);
									//echo $i.'<br />';
								}


								$i = 1;
							}
							//exit;
						} else {

							//check ticket marked in availability is in the cart first
							$is_in_cart = false;

							foreach ( $cart_contents as $ticket_type_id => $ordered_count ) {
								if ( $ticket_type_id == $discount_object->details->discount_availability ) {
									$is_in_cart = true;
									break;
								}
							}

							if ( $is_in_cart ) {

								$ticket			 = new TC_Ticket( $discount_object->details->discount_availability );
								$ticket_price	 = $ticket->details->price_per_ticket;

								$discount_value_per_each = ($discount_object->details->discount_type == 1 ? $discount_object->details->discount_value : (($ticket_price / 100) * $discount_object->details->discount_value));

								$max_discount = ($ordered_count >= $discount_codes_available ? $discount_codes_available : $ordered_count);

								for ( $i = 1; $i <= $max_discount; $i++ ) {
									$discount_value				 = $discount_value + $discount_value_per_each;
									$number_of_discount_uses++;
									$discount_codes_available	 = $discount_object->details->usage_limit - $number_of_discount_uses;
									$max_discount				 = ($ordered_count >= $discount_codes_available ? $discount_codes_available : $ordered_count);
								}
							} else {
								$discount->discount_message = __( "Discount code is not valid for the ticket type(s) in the cart.", 'tc' );
							}
						}
					} else {
						$discount->discount_message = __( 'Discount code expired', 'tc' );
					}
				}
			} else {
				$discount->discount_message = __( 'Discount code cannot be found', 'tc' );
			}

			$discount_value_total = $discount_value;

			add_filter( 'tc_cart_discount', 'tc_cart_discount_value_total', 10, 0 );

			if ( !function_exists( 'tc_cart_discount_value_total' ) ) {

				function tc_cart_discount_value_total() {
					global $discount_value_total;
					if ( !isset( $_SESSION ) ) {
						session_start();
					}

					$_SESSION[ 'discount_value_total' ] = tc_minimum_total( $discount_value_total );

					return $discount_value_total;
				}

			}

			$init_total = $total;

			add_filter( 'tc_cart_subtotal', 'tc_cart_subtotal_minimum' );

			if ( !function_exists( 'tc_cart_subtotal_minimum' ) ) {

				function tc_cart_subtotal_minimum() {
					global $init_total;
					if ( !isset( $_SESSION ) ) {
						session_start();
					}
					return tc_minimum_total( $_SESSION[ 'tc_cart_subtotal' ] );
				}

			}

			$new_total = (isset($_SESSION[ 'tc_cart_subtotal' ]) ? $_SESSION[ 'tc_cart_subtotal' ] : 0) - $discount_value;

			add_filter( 'tc_cart_total', 'tc_cart_total_minimum_total' );

			if ( !function_exists( 'tc_cart_total_minimum_total' ) ) {

				function tc_cart_total_minimum_total() {
					global $new_total;
					if ( !isset( $_SESSION ) ) {
						session_start();
					}
					$_SESSION[ 'tc_cart_total' ] = tc_minimum_total( $new_total );

					return tc_minimum_total( $new_total );
				}

			}

			if ( $new_total != $total ) {
				$discount->discount_message		 = __( 'Discount code applied.', 'tc' );
				$_SESSION[ 'tc_discount_code' ]	 = $discount_code;
			}

			if ( !isset( $_SESSION ) ) {
				session_start();
			}

			$_SESSION[ 'discounted_total' ]	 = tc_minimum_total( apply_filters( 'tc_discounted_total', $new_total ) );
			$discounted_total				 = $new_total;

			//return $discounted_total;
		}

		function discount_code_message( $message ) {
			global $discount;
			$message = $discount->discount_message;
			return $message;
		}

		function get_discount_fields() {

			$default_fields = array(
				array(
					'field_name'		 => 'post_title',
					'field_title'		 => __( 'Discount Code', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => __( 'Discount Code, e.g. ABC123', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_title'
				),
				array(
					'field_name'		 => 'discount_type',
					'field_title'		 => __( 'Discount Type', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_get_discount_types',
					'field_description'	 => '',
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'discount_value',
					'field_title'		 => __( 'Discount Value', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => __( 'For example: 9.99', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'discount_availability',
					'field_title'		 => __( 'Discount Available for', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_get_ticket_types',
					'field_description'	 => 'Select ticket type(s)',
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'usage_limit',
					'field_title'		 => __( 'Usage Limit', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => __( '(optional) How many times this discount code can be used before it is void, e.g. 100', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'expiry_date',
					'field_title'		 => __( 'Expiry Date', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => __( 'The date this discount will expire (24 hour format)', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				), /*
			  array(
			  'field_name' => 'expiry_date',
			  'field_title' => __('Expiry Date', 'tc'),
			  'field_type' => 'function',
			  'function' => 'tc_get_discount_expiry_date',
			  'field_description' => __('The date this discount will expire (24 hour format)', 'tc'),
			  'table_visibility' => true,
			  'post_field_type' => 'post_meta'
			  ), */
			);

			return apply_filters( 'tc_discount_fields', $default_fields );
		}

		function get_columns() {
			$fields	 = $this->get_discount_fields();
			$results = search_array( $fields, 'table_visibility', true );

			$columns = array();

			$columns[ 'ID' ] = __( 'ID', 'tc' );

			foreach ( $results as $result ) {
				$columns[ $result[ 'field_name' ] ] = $result[ 'field_title' ];
			}

			$columns[ 'edit' ]	 = __( 'Edit', 'tc' );
			$columns[ 'delete' ] = __( 'Delete', 'tc' );

			return $columns;
		}

		function check_field_property( $field_name, $property ) {
			$fields	 = $this->get_discount_fields();
			$result	 = search_array( $fields, 'field_name', $field_name );
			return isset( $result[ 0 ][ 'post_field_type' ] ) ? $result[ 0 ][ 'post_field_type' ] : '';
		}

		function is_valid_discount_field_type( $field_type ) {
			if ( in_array( $field_type, $this->valid_admin_fields_type ) ) {
				return true;
			} else {
				return false;
			}
		}

		function add_new_discount() {
			global $user_id, $post;

			if ( isset( $_POST[ 'add_new_discount' ] ) ) {

				$metas = array();

				foreach ( $_POST as $field_name => $field_value ) {
					if ( preg_match( '/_post_title/', $field_name ) ) {
						$title = $field_value;
					}

					if ( preg_match( '/_post_excerpt/', $field_name ) ) {
						$excerpt = $field_value;
					}

					if ( preg_match( '/_post_content/', $field_name ) ) {
						$content = $field_value;
					}

					if ( preg_match( '/_post_meta/', $field_name ) ) {
						$metas[ str_replace( '_post_meta', '', $field_name ) ] = $field_value;
					}

					do_action( 'tc_after_discount_post_field_type_check' );
				}

				$metas = apply_filters( 'discount_code_metas', $metas );

				$arg = array(
					'post_author'	 => $user_id,
					'post_excerpt'	 => (isset( $excerpt ) ? $excerpt : ''),
					'post_content'	 => (isset( $content ) ? $content : ''),
					'post_status'	 => 'publish',
					'post_title'	 => (isset( $title ) ? $title : ''),
					'post_type'		 => 'tc_discounts',
				);

				if ( isset( $_POST[ 'post_id' ] ) ) {
					$arg[ 'ID' ] = $_POST[ 'post_id' ]; //for edit 
				}

				$post_id = @wp_insert_post( $arg, true );

				//Update post meta
				if ( $post_id !== 0 ) {
					if ( isset( $metas ) ) {
						foreach ( $metas as $key => $value ) {
							update_post_meta( $post_id, $key, $value );
						}
					}
				}

				return $post_id;
			}
		}

	}

}
?>
