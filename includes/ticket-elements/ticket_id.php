<?php

class tc_ticket_code_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_ticket_buyer_name_element';
	var $element_title	 = 'Ticket Buyer Name';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_ticket_code_element_title', __( 'Ticket Code', 'tc' ) );
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket_Instance( (int) $ticket_instance_id );
			$ticket_code		 = $ticket_instance->details->ticket_code;
			return apply_filters( 'tc_ticket_ticket_code_element', $ticket_code );
		} else {
			return apply_filters( 'tc_ticket_ticket_code_element_default', __( '123456-1', 'tc' ) );
		}
	}

}

tc_register_template_element( 'tc_ticket_code_element', __( 'Ticket Code', 'tc' ) );
