<?php

/*
  Addon Name: Tickera Export
  Description: Export attendees data in PDF
 */


if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Export_Mix' ) ) {

	class TC_Export_Mix {

		var $version		 = '1.1';
		var $title		 = 'Tickera Export';
		var $name		 = 'tc';
		var $dir_name	 = 'tickera-export';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {
			$this->title = __( 'Tickera Export', 'tc' );
			add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
			add_action( 'tc_settings_menu_tickera_export_mixed_data', array( &$this, 'tc_settings_menu_tickera_export_mixed_data_show_page' ) );
			add_action( 'admin_init', array( &$this, 'tc_export_data' ), 0 );
		}

		function tc_settings_new_menus_additional( $settings_tabs ) {
			$settings_tabs[ 'tickera_export_mixed_data' ] = __( 'Export PDF', 'tc' );
			return $settings_tabs;
		}

		function tc_settings_menu_tickera_export_mixed_data_show_page() {
			require_once( $this->plugin_dir . 'includes/admin-pages/settings-tickera_export_mixed_data.php' );
		}

		function tc_export_data() {
			if ( isset( $_POST[ 'tc_export_event_data' ] ) ) {
				global $tc, $pdf;
				error_reporting( 0 );
				include_once( $tc->plugin_dir . 'includes/tcpdf/config/lang/eng.php' );
				require_once( $tc->plugin_dir . 'includes/tcpdf/tcpdf.php' );
				ob_end_clean();
				ob_start();

				$event_id		 = $_POST[ 'tc_export_event_data' ];
				$margin_left	 = 10;
				$margin_top		 = 10;
				$margin_right	 = 10;

				$pdf = new TCPDF( $_POST[ 'document_orientation' ], PDF_UNIT, $_POST[ 'document_size' ], true, get_bloginfo( 'charset' ), false );
				$pdf->setPrintHeader( false );
				$pdf->setPrintFooter( false );
				$pdf->SetFont( $_POST[ 'document_font' ], '', $_POST[ 'document_font_size' ] );
// set margins
				$pdf->SetMargins( $margin_left, $margin_top, $margin_right );
// set auto page breaks
				$pdf->SetAutoPageBreak( true, PDF_MARGIN_BOTTOM );
// set font
//$pdf->SetFont($metas->document_font_post_meta, '', 20);
				$pdf->AddPage();

				if ( $_POST[ 'document_title' ] !== '' ) {
					$rows = '<h1 style="text-align:center;">' . $_POST[ 'document_title' ] . '</h1>';
				}
				$rows .= '<table width="100%" border="1" cellpadding="2"><tr>';

				if ( isset( $_POST[ 'col_checkbox' ] ) ) {
					$rows .= '<th align="center">' . __( 'Check', 'tc' ) . '</th>';
				}
				if ( isset( $_POST[ 'col_owner_name' ] ) ) {
					$rows .= '<th align="center">' . __( 'Ticket Owner', 'tc' ) . '</th>';
				}
				if ( isset( $_POST[ 'col_payment_date' ] ) ) {
					$rows .= '<th align="center">' . __( 'Payment Date', 'tc' ) . '</th>';
				}
				if ( isset( $_POST[ 'col_ticket_id' ] ) ) {
					$rows .= '<th align="center">' . __( 'Ticket ID', 'tc' ) . '</th>';
				}
				if ( isset( $_POST[ 'col_ticket_type' ] ) ) {
					$rows .= '<th align="center">' . __( 'Ticket Type', 'tc' ) . '</th>';
				}
				if ( isset( $_POST[ 'col_buyer_name' ] ) ) {
					$rows .= '<th align="center">' . __( 'Buyer Name', 'tc' ) . '</th>';
				}
				if ( isset( $_POST[ 'col_buyer_email' ] ) ) {
					$rows .= '<th align="center">' . __( 'Buyer Email', 'tc' ) . '</th>';
				}
				if ( isset( $_POST[ 'col_barcode' ] ) ) {
					$rows .= '<th align="center">' . __( 'Barcode', 'tc' ) . '</th>';
				}
				if ( isset( $_POST[ 'col_qrcode' ] ) ) {
					$rows .= '<th align="center">' . __( 'QR Code', 'tc' ) . '</th>';
				}

				$rows = apply_filters( 'tc_pdf_additional_column_titles', $rows, $_POST );

				$rows .= '</tr>';

				$args = array(
					'posts_per_page' => -1,
					'orderby'		 => 'post_date',
					'order'			 => 'DESC',
					'post_type'		 => 'tc_tickets_instances',
					'post_status'	 => 'publish'
				);

				$ticket_instances = get_posts( $args );

				foreach ( $ticket_instances as $ticket_instance ) {
					$instance	 = new TC_Ticket_Instance( $ticket_instance->ID );
					$ticket_type = new TC_Ticket( apply_filters( 'tc_ticket_type_id', $instance->details->ticket_type_id ) );

					$event_name_meta = apply_filters( 'tc_event_name_field_name', 'event_name' );
					$event_name		 = $ticket_type->details->$event_name_meta;
					if ( $event_name == $event_id ) {
						$order = new TC_Order( $instance->details->post_parent );

						if ( $order->details->post_status == 'order_paid' ) {
							$order_is_paid = true;
						} else {
							$order_is_paid = false;
						}

						$order_is_paid = apply_filters( 'tc_order_is_paid', $order_is_paid, $order->details->ID );

						if ( $order_is_paid ) {
							$payment_date = date_i18n( get_option( 'date_format' ), apply_filters( 'tc_ticket_checkin_order_date', $order->details->tc_order_date, $order->details->ID ), false );
							$rows .= '<tr>';
							if ( isset( $_POST[ 'col_checkbox' ] ) ) {
								$rows .= '<td align="center"></td>';
							}
							if ( isset( $_POST[ 'col_owner_name' ] ) ) {
								$rows .= '<td>' . $instance->details->first_name . ' ' . $instance->details->last_name . '</td>';
							}
							if ( isset( $_POST[ 'col_payment_date' ] ) ) {
								$rows .= '<td>' . $payment_date . '</td>';
							}
							if ( isset( $_POST[ 'col_ticket_id' ] ) ) {
								$rows .= '<td>' . $instance->details->ticket_code . '</td>';
							}
							if ( isset( $_POST[ 'col_ticket_type' ] ) ) {
								$rows .= '<td>' . apply_filters( 'tc_checkout_owner_info_ticket_title', $ticket_type->details->post_title, $instance->details->ticket_type_id ) . '</td>';
							}
							if ( isset( $_POST[ 'col_buyer_name' ] ) ) {
								$buyer_full_name = $order->details->tc_cart_info[ 'buyer_data' ][ 'first_name_post_meta' ] . ' ' . $order->details->tc_cart_info[ 'buyer_data' ][ 'last_name_post_meta' ];
								$rows .= '<td>' . apply_filters( 'tc_ticket_checkin_buyer_full_name', $buyer_full_name, $order->details->ID ) . '</td>';
							}
							if ( isset( $_POST[ 'col_buyer_email' ] ) ) {
								$buyer_email = $order->details->tc_cart_info[ 'buyer_data' ][ 'email_post_meta' ];
								$rows .= '<td>' . apply_filters( 'tc_ticket_checkin_buyer_email', $buyer_email, $order->details->ID ) . '</td>';
							}
							if ( isset( $_POST[ 'col_barcode' ] ) ) {
								$rows .= '<td>BARCODE</td>';
							}
							if ( isset( $_POST[ 'col_qrcode' ] ) ) {
								$rows .= '<td>QRCODE</td>';
							}

							$rows = apply_filters( 'tc_pdf_additional_column_values', $rows, $order, $instance, $_POST );

							$rows .= '</tr>';
						}
					}
				}

				$rows .= '</table>';

				$page1 = preg_replace( "/\s\s+/", '', $rows ); //Strip excess whitespace 
				ob_get_clean();
				$pdf->writeHTML( $page1, true, 0, true, 0 ); //Write page 1 
//$pdf->lastPage();
				$pdf->Output( $_POST[ 'document_title' ] !== '' ? $_POST[ 'document_title' ] . '.pdf' : __( 'Attendee List', 'tc' ) . '.pdf', 'D' ); //D
				exit;
			}
		}

	}

}

$tc_export_mix = new TC_Export_Mix();
?>