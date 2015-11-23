<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Event' ) ) {

	class TC_Event {

		var $id		 = '';
		var $output	 = 'OBJECT';
		var $event	 = array();
		var $details;

		function __construct( $id = '', $output = 'OBJECT' ) {
			$this->id		 = $id;
			$this->output	 = $output;
			$this->details	 = get_post( $this->id, $this->output );

			$events	 = new TC_Events();
			$fields	 = $events->get_event_fields();

			foreach ( $fields as $field ) {
				if ( !isset( $this->details->{$field[ 'field_name' ]} ) ) {
					$this->details->{$field[ 'field_name' ]} = get_post_meta( $this->id, $field[ 'field_name' ], true );
				}
			}
		}

		function TC_Event( $id = '', $output = 'OBJECT' ) {
			$this->__construct( $id, $output );
		}

		function get_event() {
			$event = get_post_custom( $this->id, $this->output );
			return $event;
		}

		function get_event_ticket_types( $post_status = 'any', $event_id = false ) {

			$event_id = $event_id ? $event_id : $this->id;

			$ticket_ids = array();

			$args = array(
				'post_type'		 => 'tc_tickets',
				'post_status'	 => $post_status,
				'posts_per_page' => -1,
				'meta_key'		 => 'event_name',
				'meta_value'	 => (string) $this->id,
				'orderby'		 => 'date',
				'order'			 => 'ASC',
			);

			$args = apply_filters( 'tc_get_event_ticket_types_args', $args );

			$ticket_types = apply_filters( 'tc_get_event_ticket_types', get_posts( $args ), $event_id );

			foreach ( $ticket_types as $ticket_type ) {
				$ticket_ids[] = (int) $ticket_type->ID;
			}

			return $ticket_ids;
		}

		function delete_event( $force_delete = false ) {
			if ( $force_delete ) {
				wp_delete_post( $this->id );
			} else {
				wp_trash_post( $this->id );
			}

			//delete event ticket types
			$args = array(
				'post_type'	 => 'tc_tickets',
				'meta_key'	 => 'event_name',
				'meta_value' => $this->id );

			$ticket_types = get_posts( $args );

			foreach ( $ticket_types as $ticket_type ) {
				$ticket_type_instance = new TC_Ticket( $ticket_type->ID );
				$ticket_type_instance->delete_ticket();
			}
		}

		function get_event_date( $event_id = false ) {
			if ( !$event_id ) {
				$event_id = $this->id;
			}
			$event_start_date	 = get_post_meta( $event_id, 'event_date_time', true );
			$event_end_date		 = get_post_meta( $event_id, 'event_end_date_time', true );

			$start_date	 = date_i18n( get_option( 'date_format' ), strtotime( $event_start_date ) );
			$start_time	 = date_i18n( get_option( 'time_format' ), strtotime( $event_start_date ) );

			$end_date	 = date_i18n( get_option( 'date_format' ), strtotime( $event_end_date ) );
			$end_time	 = date_i18n( get_option( 'time_format' ), strtotime( $event_end_date ) );

			if ( !empty( $event_end_date ) ) {
				if ( $start_date == $end_date ) {
					if ( $start_time == $end_time ) {
						$event_date = $start_date . ' ' . $start_time;
					} else {
						$event_date = $start_date . ' ' . $start_time . ' - ' . $end_time;
					}
				} else {
					if ( $start_time == $end_time ) {
						$event_date = $start_date . ' - ' . $end_date . ' ' . $start_time;
					} else {
						$event_date = $start_date . ' ' . $start_time . ' - ' . $end_date . ' ' . $end_time;
					}
				}
			} else {
				$event_date = $start_date . ' ' . $start_time;
			}

			return $event_date;
		}

		function restore_event( $event_id ) {
			wp_untrash_post( $event_id );

			//delete event ticket types
			$args = array(
				'post_type'	 => 'tc_tickets',
				'meta_key'	 => 'event_name',
				'meta_value' => $event_id );

			$ticket_types = get_posts( $args );

			foreach ( $ticket_types as $ticket_type ) {
				wp_untrash_post( $ticket_type->ID );
			}
		}

		function get_event_id_by_name( $slug ) {

			$args = array(
				'name'			 => $slug,
				'post_type'		 => 'tc_events',
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