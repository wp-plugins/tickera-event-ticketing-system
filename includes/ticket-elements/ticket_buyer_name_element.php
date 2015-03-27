<?php

class tc_ticket_buyer_name_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_ticket_buyer_name_element';
	var $element_title	 = 'Ticket Buyer Name';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_ticket_buyer_name_element_title', __( 'Ticket Buyer Name', 'tc' ) );
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket_Instance( (int) $ticket_instance_id );
                        $order = new TC_Order($ticket_instance->details->post_parent);
			$buyer_name		 = $order->details->tc_cart_info[ 'buyer_data' ][ 'first_name_post_meta' ] . ' ' . $order->details->tc_cart_info[ 'buyer_data' ][ 'last_name_post_meta' ];
			return apply_filters( 'tc_ticket_buyer_name_element', $buyer_name );
		} else {
			return apply_filters( 'tc_ticket_buyer_name_element_default', __( 'John Smith', 'tc' ) );
		}
	}

}

tc_register_template_element( 'tc_ticket_buyer_name_element', __( 'Ticket Buyer Name', 'tc' ) );
