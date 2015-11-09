<?php

class tc_ticket_id_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_ticket_id_element';
	var $element_title	 = 'Ticket ID';
        var $font_awesome_icon = '<i class="fa fa-slack"></i>';


	function on_creation() {
		$this->element_title = apply_filters( 'tc_ticket_id_element_title', __( 'Ticket ID', 'tc' ) );
	}
        
        function admin_content() {
		echo parent::get_cell_alignment();
		echo parent::get_element_margins();
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			return apply_filters( 'tc_ticket_ticket_id_element', $ticket_instance_id );
		} else {
			return apply_filters( 'tc_ticket_ticket_id_element_default', __( '123', 'tc' ) );
		}
                
                
	}

}

tc_register_template_element( 'tc_ticket_id_element', __( 'Ticket ID', 'tc' ) );
