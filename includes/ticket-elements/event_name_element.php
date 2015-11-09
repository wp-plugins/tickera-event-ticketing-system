<?php

class tc_event_name_element extends TC_Ticket_Template_Elements {

	var $element_name		 = 'tc_event_name_element';
	var $element_title		 = 'Event Name';
	var $font_awesome_icon	 = '<i class="fa fa-font"></i>';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_event_name_element_title', __( 'Event Name', 'tc' ) );
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket( (int) $ticket_instance_id );
			$ticket			 = new TC_Ticket();
			$event_id		 = $ticket->get_ticket_event( apply_filters( 'tc_ticket_type_id', $ticket_instance->details->ticket_type_id ) );
			$event_name		 = get_the_title( $event_id ); //, 'event_name', true);
			return apply_filters( 'tc_event_name_element', $event_name );
		} else {
			if ( $ticket_type_id ) {
				$ticket_type = new TC_Ticket( (int) $ticket_type_id );
				$event_id	 = $ticket_type->get_ticket_event( $ticket_type_id );
				$event		 = new TC_Event( $event_id );
				return apply_filters( 'tc_event_name', $event->details->post_title );
			} else {
				return apply_filters( 'tc_event_name_element', __( 'Great Event', 'tc' ) );
			}
		}
	}

}

tc_register_template_element( 'tc_event_name_element', __( 'Event Name', 'tc' ) );
