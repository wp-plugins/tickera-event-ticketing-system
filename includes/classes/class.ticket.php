<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Ticket' ) ) {

	class TC_Ticket {

		var $id		 = '';
		var $output	 = 'OBJECT';
		var $ticket	 = array();
		var $details;

		function __construct( $id = '', $status = 'any', $output = 'OBJECT' ) {
			$continue = true;

			if ( $status !== 'any' ) {
				if ( get_post_status( $id ) == $status ) {
					$continue = true;
				} else {
					$continue = false;
				}
			}

			if ( $continue ) {
				$this->id		 = $id;
				$this->output	 = $output;
				$this->details	 = get_post( $this->id, $this->output );

				$tickets = new TC_Tickets();
				$fields	 = $tickets->get_ticket_fields();

				if ( isset( $this->details ) ) {
					if ( !empty( $fields ) ) {
						foreach ( $fields as $field ) {
							if ( !isset( $this->details->{$field[ 'field_name' ]} ) ) {
								$this->details->{$field[ 'field_name' ]} = get_post_meta( $this->id, $field[ 'field_name' ], true );
							}
						}
					}
				}
			} else {
				$this->id = null;
			}
		}

		function TC_Ticket( $id = '', $output = 'OBJECT' ) {
			$this->__construct( $id, $output );
		}

		function get_ticket() {
			$ticket = get_post_custom( $this->id, $this->output );
			return $ticket;
		}

		function get_number_of_sold_tickets() {
			$ticket_search = new TC_Tickets_Instances_Search( '', '', -1, false, false, 'ticket_type_id', $this->id );
			if ( is_array( $ticket_search->get_results() ) ) {
				return count( $ticket_search->get_results() );
			} else {
				return 0;
			}
		}

		function get_tickets_quantity_left() {
			$max_quantity	 = $this->details->quantity_available;
			$sold_quantity	 = $this->get_number_of_sold_tickets();

			if ( $max_quantity == 0 || $max_quantity == '' ) {
				return 9999; //means no limit
			} else {
				return ($max_quantity - $sold_quantity);
			}
		}

		function is_ticket_exceeded_quantity_limit() {
			$max_quantity = $this->details->quantity_available;
			if ( $max_quantity == 0 || $max_quantity == '' ) {
				return false;
			} else {
				$sold_quantity = $this->get_number_of_sold_tickets();
				if ( $sold_quantity < $max_quantity ) {
					return false;
				} else {
					return true;
				}
			}
		}

		function delete_ticket( $force_delete = false ) {
			if ( $force_delete ) {
				wp_delete_post( $this->id );
			} else {
				wp_trash_post( $this->id );
			}
		}

		function get_ticket_event( $ticket_id = false ) {
			if ( $ticket_id == false ) {
				$ticket_id = $this->id;
			}
			return get_post_meta( $ticket_id, apply_filters( 'tc_event_name_field_name', 'event_name' ), true );
		}

		function get_ticket_id_by_name( $slug ) {

			$args = array(
				'name'			 => $slug,
				'post_type'		 => 'tc_tickets',
				'post_status'	 => 'any',
				'posts_per_page' => 1
			);

			$post = get_posts( $args );

			if ( $post ) {
				return $post[ 0 ]->ID;
			} else {
				return false;
			}
		}

	}

}
?>