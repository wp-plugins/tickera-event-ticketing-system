<?php

class tc_event_terms_element extends TC_Ticket_Template_Elements {

	var $element_name		 = 'tc_event_terms_element';
	var $element_title		 = 'Terms & Conditions';
	var $font_awesome_icon	 = '<i class="fa fa-align-center"></i>';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_event_terms_element_title', __( 'Terms & Conditions', 'tc' ) );
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket( (int) $ticket_instance_id );
			$ticket			 = new TC_Ticket();
			$event_id		 = $ticket->get_ticket_event( apply_filters( 'tc_ticket_type_id', $ticket_instance->details->ticket_type_id ) );
			$event_terms	 = apply_filters( 'tc_the_content', get_post_meta( $event_id, 'event_terms', true ) );
			return apply_filters( 'tc_event_terms_element', $event_terms );
		} else {
			if ( $ticket_type_id ) {
				$ticket_type = new TC_Ticket( (int) $ticket_type_id );
				$event_id	 = $ticket_type->get_ticket_event( $ticket_type_id );
				$event		 = new TC_Event( $event_id );
				return apply_filters( 'tc_event_terms_element', apply_filters( 'tc_the_content', $event->details->event_terms ) );
			} else {
				return apply_filters( 'tc_event_terms_element_default', __( 'You must retain this Ticket on Your person at all times during the Event. In addition, for The Great Event, entrance to certain performances is subject to the purchase of an additional Ticket or “top-up” Ticket and for those performances only persons holding such a Ticket will be allowed access. Your Ticket may be invalidated if any part of it is removed, altered or defaced. Upon purchase, please check Tickets carefully as mistakes cannot always be rectified after purchase. Tickets are not issued on a sale or return basis and refunds will not be made on returned Tickets unless provided for under these Terms and Conditions. The Promoter will not be responsible for any Ticket that is lost, stolen or destroyed. You are solely responsible for the safe-keeping of Your Ticket. It is not always possible to issue duplicate Tickets. If duplicates are issued, a reasonable administration fee may be charged.', 'tc' ) );
			}
		}
	}

}

tc_register_template_element( 'tc_event_terms_element', __( 'Terms & Conditions', 'tc' ) );
