<?php

class tc_ticket_owner_name_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_ticket_owner_name_element';
	var $element_title	 = 'Ticket Owner Name';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_ticket_owner_name_element_title', __( 'Ticket Owner Name', 'tc' ) );
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket_Instance( (int) $ticket_instance_id );
			$owner_name		 = $ticket_instance->details->first_name . ' ' . $ticket_instance->details->last_name;
			return apply_filters( 'tc_ticket_owner_name_element', $owner_name );
		} else {
			return apply_filters( 'tc_ticket_owner_name_element_default', __( 'John Smith', 'tc' ) );
		}
	}

}

tc_register_template_element( 'tc_ticket_owner_name_element', __( 'Ticket Owner Name', 'tc' ) );
