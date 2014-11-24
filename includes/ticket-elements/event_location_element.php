<?php

class tc_event_location_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_event_location_element';
	var $element_title	 = 'Event Location';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_event_location_element_title', __( 'Event Location', 'tc' ) );
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket( (int) $ticket_instance_id );
			$ticket			 = new TC_Ticket();
			$event_id		 = $ticket->get_ticket_event( $ticket_instance->details->ticket_type_id );
			return apply_filters( 'tc_event_location_element', get_post_meta( $event_id, 'event_location', true ) );
		} else {
			if ( $ticket_type_id ) {
				$ticket_type = new TC_Ticket( (int) $ticket_type_id );
				$event_id	 = $ticket_type->get_ticket_event( $ticket_type_id );
				$event = new TC_Event($event_id);
				return apply_filters( 'tc_event_location_element', $event->details->event_location );
			} else {
				return apply_filters( 'tc_event_location_element_default', __( 'Grosvenor Square, Mayfair, London', 'tc' ) );
			}
		}
	}

}

tc_register_template_element( 'tc_event_location_element', __( 'Event Location', 'tc' ) );
