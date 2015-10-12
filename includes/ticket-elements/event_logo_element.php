<?php

class tc_event_logo_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_event_logo_element';
	var $element_title	 = 'Event Logo';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_event_logo_element_title', __( 'Event Logo', 'tc' ) );
	}

	function admin_content() {
		echo parent::get_cell_alignment();
		echo parent::get_element_margins();
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		global $tc;
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket( (int) $ticket_instance_id );
			$ticket			 = new TC_Ticket();
			$event_id		 = $ticket->get_ticket_event( $ticket_instance->details->ticket_type_id );

			$event_logo = apply_filters( 'tc_event_logo_element', get_post_meta( $event_id, 'event_logo_file_url', true ) );

			if ( $event_logo ) {
				return '<img src="' . $event_logo . '" />';
			} else {
				return '';
			}
		} else {
			if ( $ticket_type_id ) {
				$ticket_type = new TC_Ticket( (int) $ticket_type_id );
				$event_id	 = $ticket_type->get_ticket_event( $ticket_type_id );
				$event		 = new TC_Event( $event_id );
				return apply_filters( 'tc_event_logo_element', '<img src="' . $event->details->event_logo_file_url . '" />');
			} else {
				return apply_filters( 'tc_event_logo_element_default', '<img src="' . $tc->plugin_dir . 'images/tickera_logo.png' . '" />' );
			}
		}
	}

}

tc_register_template_element( 'tc_event_logo_element', __( 'Event Logo', 'tc' ) );
