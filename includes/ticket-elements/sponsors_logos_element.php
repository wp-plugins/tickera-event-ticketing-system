<?php

class tc_sponsors_logos_element extends TC_Ticket_Template_Elements {

	var $element_name		 = 'tc_sponsors_logos_element';
	var $element_title		 = 'Sponsors Logos';
	var $font_awesome_icon	 = '<i class="fa fa-money"></i>';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_sponsors_logos_element_title', __( 'Sponsors Logos', 'tc' ) );
	}

	function admin_content() {
		//parent::admin_content();
		echo parent::get_cell_alignment();
		echo parent::get_element_margins();
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket( (int) $ticket_instance_id );
			$ticket			 = new TC_Ticket();
			$event_id		 = $ticket->get_ticket_event( apply_filters( 'tc_ticket_type_id', $ticket_instance->details->ticket_type_id ) );
			$sponsors_logo	 = apply_filters( 'tc_sponsors_logos_element', get_post_meta( $event_id, 'sponsors_logo_file_url', true ) );

			if ( $sponsors_logo ) {
				return '<img src="' . $sponsors_logo . '" />';
			} else {
				return '';
			}
		} else {
			if ( $ticket_type_id ) {
				$ticket_type = new TC_Ticket( (int) $ticket_type_id );
				$event_id	 = $ticket_type->get_ticket_event( $ticket_type_id );
				$event		 = new TC_Event( $event_id );
				return apply_filters( 'tc_sponsors_logos_element', '<img src="' . $event->details->sponsors_logo_file_url . '" />' );
			} else {
				return apply_filters( 'tc_sponsors_logos_element_default', __( 'Sponsor Logos', 'tc' ) );
			}
		}
	}

}

tc_register_template_element( 'tc_sponsors_logos_element', __( 'Sponsor Logos', 'tc' ) );
