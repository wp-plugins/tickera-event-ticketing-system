<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Order' ) ) {

	class TC_Order {

		var $id		 = '';
		var $output	 = 'OBJECT';
		var $ticket	 = array();
		var $details;

		function __construct( $id = '', $output = 'OBJECT' ) {

			$this->id		 = $id;
			$this->output	 = $output;
			$this->details	 = get_post( $this->id, $this->output );

			$tickets = new TC_Orders();
			$fields	 = $tickets->get_order_fields();

			foreach ( $fields as $field ) {

				if ( !isset( $this->details->{$field[ 'field_name' ]} ) ) {
					$this->details->{$field[ 'field_name' ]} = get_post_meta( $this->id, $field[ 'field_name' ], true );
				}
			}
		}

		function TC_Order( $id = '', $output = 'OBJECT' ) {
			$this->__construct( $id, $output );
		}

		function get_order() {
			$order = get_post_custom( $this->id, $this->output );
			return $order;
		}

		function delete_order( $force_delete = true ) {
			if ( $force_delete ) {
				wp_delete_post( $this->id );
			} else {
				wp_trash_post( $this->id );
			}

			//Delete associated ticket instances
			$args = array(
				'post_type'		 => 'tc_tickets_instances',
				'post_status'	 => 'any',
				'post_parent'	 => $this->id
			);

			$ticket_instances = get_posts( $args );

			foreach ( $ticket_instances as $ticket_instance ) {
				$ticket_instance_instance = new TC_Ticket_Instance( $ticket_instance->ID );
				$ticket_instance_instance->delete_ticket_instance();
			}
		}

	}

}
?>