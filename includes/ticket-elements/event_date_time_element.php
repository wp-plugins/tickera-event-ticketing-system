<?php

class tc_event_date_time_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_event_date_time_element';
	var $element_title	 = 'Event Date & Time';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_event_date_time_element_title', __( 'Event Date & Time', 'tc' ) );
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket( (int) $ticket_instance_id );
			$ticket			 = new TC_Ticket();
			$event_id		 = $ticket->get_ticket_event( $ticket_instance->details->ticket_type_id );
			return apply_filters( 'tc_event_date_time_element_ticket_type', tc_format_date( strtotime( get_post_meta( $event_id, 'event_date_time', true ) ) ), $ticket_instance->details->ticket_type_id, $ticket_instance_id ); //date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( get_post_meta( $event_id, 'event_date_time', true ) ) )
		} else {
			if ( $ticket_type_id ) {
				$ticket_type = new TC_Ticket( (int) $ticket_type_id );
				$event_id	 = $ticket_type->get_ticket_event( $ticket_type_id );
				return tc_format_date( strtotime( get_post_meta( $event_id, 'event_date_time', true ) ) ); ////date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( get_post_meta( $event_id, 'event_date_time', true ) ) );
			} else {
				return apply_filters( 'tc_event_date_time_element_default', tc_format_date( time() ) ); //date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), time(), false )
			}
		}
	}

}

tc_register_template_element( 'tc_event_date_time_element', __( 'Event Date & Time', 'tc' ) );
