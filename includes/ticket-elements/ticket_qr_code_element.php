<?php

class tc_ticket_qr_code_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_ticket_qr_code_element';
	var $element_title	 = 'QR Code';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_ticket_qr_code_element_title', __( 'QR Code', 'tc' ) );
	}

	function admin_content() {
		echo $this->get_qr_code_size();
		echo parent::get_cell_alignment();
		echo parent::get_element_margins();
	}

	function get_qr_code_size() {
		?>
		<label><?php _e( 'QR Code Size', 'tc' ); ?>
			<input class="ticket_element_padding" type="text" name="<?php echo $this->element_name; ?>_qr_code_size_post_meta" value="<?php echo esc_attr( isset( $this->template_metas[ $this->element_name . '_qr_code_size' ] ) ? $this->template_metas[ $this->element_name . '_qr_code_size' ] : '50'  ); ?>" />
		</label>
		<?php
	}

	function ticket_content( $ticket_instance_id = false, $ticket_type_id = false ) {
		global $tc, $pdf;

		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket_Instance( (int) $ticket_instance_id );
			$ticket_code	 = ($ticket_instance) ? $ticket_instance->details->ticket_code : $tc->create_unique_id();
			$order			 = new TC_Order( $ticket_instance->details->post_parent );
			$qrstring		 = 'id|' . $ticket_instance_id . '|name|' . $ticket_instance->details->first_name . ' ' . $ticket_instance->details->last_name . '|city|' . ($ticket_instance->details->city ? $ticket_instance->details->city : '') . '|address|' . ($ticket_instance->details->address ? $ticket_instance->details->address : '') . '|country|' . ($ticket_instance->details->country ? $ticket_instance->details->country : '') . '|state|' . ($ticket_instance->details->state ? $ticket_instance->details->state : '') . '|payment_date|' . $order->details->post_date . '|checksum|' . $ticket_instance->details->ticket_code;
			if ( apply_filters( 'tc_qr_code_quick_scan_info', false ) ) {
				$qrstring = $ticket_instance->details->ticket_code;
			}
		}

		$cell_alignment	 = $this->template_metas[ $this->element_name . '_cell_alignment' ];
		$code_size		 = $this->template_metas[ $this->element_name . '_qr_code_size' ];
                
                if ( isset( $cell_alignment ) && $cell_alignment == 'right' ) {
			$cell_alignment = 'R';
		} elseif ( isset( $cell_alignment ) && $cell_alignment == 'left' ) {
			$cell_alignment = 'L';
		} elseif ( isset( $cell_alignment ) && $cell_alignment == 'center' ) {
			$cell_alignment = 'N';
		} else {
			$cell_alignment = 'N'; //default alignment
		}

		$style = array(
			'position'	 => apply_filters( 'qr_code_cell_alignment', $cell_alignment ),
			'border'	 => apply_filters( 'tc_show_qr_code_border', true ),
			'padding'	 => apply_filters( 'tc_qr_code_padding', 1 ),
			'fgcolor'	 => tc_hex2rgb( apply_filters( 'qr_code_fg_color', '#000000' ) ),
			'bgcolor'	 => tc_hex2rgb( apply_filters( 'qr_code_bg_color', '#FFFFFF' ) ),
		);

		$params_array = array(
			isset( $qrstring ) ? apply_filters( 'tc_qr_string', $qrstring ) : $tc->create_unique_id(),
			'QRCODE,H',
			'',
			'',
			$code_size,
			$code_size,
			$style,
			'N'
		);

		$params_array = apply_filters( 'tc_2d_code_params', $params_array, isset( $qrstring ) ? apply_filters( 'tc_qr_string', $qrstring ) : $tc->create_unique_id(), 'QRCODE,H', '', '', $code_size, $code_size, $style, 'N' );

		$pars = $pdf->serializeTCPDFtagParameters( $params_array );

		return '<tcpdf method="write2DBarcode" params="' . $pars . '" />';
	}

}

tc_register_template_element( 'tc_ticket_qr_code_element', __( 'QR Code', 'tc' ) );
