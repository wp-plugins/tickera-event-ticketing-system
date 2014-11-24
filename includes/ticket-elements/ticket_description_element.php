<?php

class tc_ticket_description_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_ticket_description_element';
	var $element_title	 = 'Ticket Description';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_ticket_description_element_title', __( 'Ticket Description', 'tc' ) );
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket( (int) $ticket_instance_id );
			$ticket			 = new TC_Ticket( $ticket_instance->details->ticket_type_id );
			return apply_filters( 'tc_ticket_description_element', $ticket->details->post_content );
		} else {
			if ( $ticket_type_id ) {
				$ticket_type = new TC_Ticket( (int) $ticket_type_id );
				return apply_filters( 'tc_ticket_description_element', $ticket_type->details->post_content );
			} else {
				return apply_filters( 'tc_ticket_description_element_default', __(
				'<ul>
				<li>AGES 21+ (with valid state-issued photo ID)</li>
				<li>Includes transportation via Ferry or Shuttle Bus (you choose during purchase process)</li>
				<li>Express Festival Entry</li>
				<li>VIP Lounge Access with plush furniture, premium food and cash bar</li>
</ul>', 'tc' ) );
			}
		}
	}

}

tc_register_template_element( 'tc_ticket_description_element', __( 'Ticket Description', 'tc' ) );
