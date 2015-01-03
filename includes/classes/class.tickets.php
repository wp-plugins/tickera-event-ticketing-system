<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Tickets' ) ) {

	class TC_Tickets {

		var $form_title				 = '';
		var $valid_admin_fields_type	 = array( 'text', 'textarea', 'image', 'function' );

		function __construct() {
			$this->form_title				 = __( 'Tickets', 'tc' );
			$this->valid_admin_fields_type	 = apply_filters( 'tc_valid_admin_fields_type', $this->valid_admin_fields_type );
		}

		function TC_Tickets() {
			$this->__construct();
		}

		function get_ticket_fields() {

			$default_fields = array(
				array(
					'field_name'		 => 'ID',
					'field_title'		 => __( 'ID', 'tc' ),
					'field_type'		 => 'ID',
					'field_description'	 => '',
					'table_visibility'	 => true,
					'post_field_type'	 => 'ID'
				),
				array(
					'field_name'		 => 'event_name',
					'field_title'		 => __( 'Event Name', 'tc' ),
					'placeholder'	     => '',
					'field_type'		 => 'function',
					'function'			 => 'tc_get_events',
					'field_description'	 => '',
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'ticket_type_name',
					'field_title'		 => __( 'Ticket Type / Name', 'tc' ),
					'placeholder'	     => '',
					'field_type'		 => 'text',
					'field_description'	 => __( 'Example: Standard ticket, VIP, Early Bird, Student, Regular Admission, etc.', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_title'
				),
				array(
					'field_name'		 => 'ticket_description',
					'field_title'		 => __( 'Ticket Description', 'tc' ),
					'placeholder'	     => '',
					'field_type'		 => 'textarea',
					'field_description'	 => __( 'Example: Access to the whole Congress, all business networking lounges excluding the Platinum Lounge and the Official Dinner.', 'tc' ),
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_content'
				),
				array(
					'field_name'		 => 'price_per_ticket',
					'field_title'		 => __( 'Price Per Ticket', 'tc' ),
					'placeholder'	     => '',
					'field_type'		 => 'text',
					'field_description'	 => __( 'Example: 29.90 (without currency symbol)', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'quantity_available',
					'field_title'		 => __( 'Quantity Available', 'tc' ),
					'placeholder'	     => __('Unlimited', 'tc'),
					'field_type'		 => 'text',
					'field_description'	 => __( 'Whole number like 100, empty field or 0 for unlimited', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'min_tickets_per_order',
					'field_title'		 => __( 'Min. tickets per order', 'tc' ),
					'placeholder'	     => __('No Minimum', 'tc'),
					'field_type'		 => 'text',
					'field_description'	 => __( 'Whole number e.g. 5, empty field or 0 for no minimum', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'max_tickets_per_order',
					'field_title'		 => __( 'Max. tickets per order', 'tc' ),
					'placeholder'	     => __('No Maximum', 'tc'),
					'field_type'		 => 'text',
					'field_description'	 => __( 'Whole number e.g. 5, empty field or 0 for no maximum', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'available_checkins_per_ticket',
					'field_title'		 => __( 'Available check-ins per ticket', 'tc' ),
					'placeholder'	     => __('Unlimited', 'tc'),
					'field_type'		 => 'text',
					'field_description'	 => __( 'It is useful if the event last more than one day. For instance, if duration of your event is 5 day, you should choose 5 or more for Available Check-ins', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'ticket_fee',
					'field_title'		 => __( 'Ticket Fee', 'tc' ),
					'placeholder'	     => __('No Fees', 'tc'),
					'field_type'		 => 'text',
					'field_description'	 => __( 'Ticket / Service Fee (you can add additional fee per ticket in order to cover payment gateway, service or any other type of cost) - 0 or empty for no service fee.', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'ticket_fee_type',
					'field_title'		 => __( 'Ticket Fee Type', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_get_ticket_fee_type',
					'field_description'	 => '',
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'ticket_template',
					'field_title'		 => __( 'Ticket Template', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_get_ticket_templates',
					'field_description'	 => '',
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_meta'
				),
			);

			return apply_filters( 'tc_ticket_fields', $default_fields );
		}

		function get_columns() {
			$fields	 = $this->get_ticket_fields();
			$results = search_array( $fields, 'table_visibility', true );

			$columns = array();

			foreach ( $results as $result ) {
				$columns[ $result[ 'field_name' ] ] = $result[ 'field_title' ];
			}

			$columns[ 'edit' ]	 = __( 'Edit', 'tc' );
			$columns[ 'delete' ] = __( 'Delete', 'tc' );

			return $columns;
		}

		function check_field_property( $field_name, $property ) {
			$fields	 = $this->get_ticket_fields();
			$result	 = search_array( $fields, 'field_name', $field_name );
			return $result[ 0 ][ 'post_field_type' ];
		}

		function is_valid_ticket_field_type( $field_type ) {
			if ( in_array( $field_type, $this->valid_admin_fields_type ) ) {
				return true;
			} else {
				return false;
			}
		}

		function restore_all_ticket_types() {
			$args = array(
				'post_type'		 => 'tc_tickets',
				'post_status'	 => 'trash'
			);

			$ticket_types = get_posts( $args );

			foreach ( $ticket_types as $ticket_type ) {
				wp_untrash_post($ticket_type->ID );
			}
		}

		function add_new_ticket() {
			global $user_id, $post;

			if ( isset( $_POST[ 'add_new_ticket' ] ) ) {

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

					do_action( 'tc_after_ticket_post_field_type_check' );
				}

				$metas = apply_filters( 'tickets_metas', $metas );

				$arg = array(
					'post_author'	 => $user_id,
					'post_excerpt'	 => (isset( $excerpt ) ? $excerpt : ''),
					'post_content'	 => (isset( $content ) ? $content : ''),
					'post_status'	 => 'publish',
					'post_title'	 => (isset( $title ) ? $title : ''),
					'post_type'		 => 'tc_tickets',
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
