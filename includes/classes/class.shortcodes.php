<?php
/*
  Shortcodes
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class TC_Shortcodes extends TC {

	function __construct() {
		//register shortcodes
		add_shortcode( 'tc_cart', array( &$this, 'tc_cart_page' ) );
		add_shortcode( 'tc_payment', array( &$this, 'tc_payment_page' ) );
		add_shortcode( 'tc_order_confirmation', array( &$this, 'tc_order_confirmation_page' ) );
		add_shortcode( 'tc_order_details', array( &$this, 'tc_order_details_page' ) );

		add_shortcode( 'ticket', array( &$this, 'ticket_cart_button' ) );
		add_shortcode( 'tc_ticket', array( &$this, 'ticket_cart_button' ) );

		add_shortcode( 'ticket_price', array( &$this, 'ticket_price' ) );
		add_shortcode( 'tc_ticket_price', array( &$this, 'ticket_price' ) );

		add_shortcode( 'event', array( &$this, 'event' ) );
		add_shortcode( 'tc_event', array( &$this, 'event' ) );
	}

	function event( $atts ) {
		ob_start();
		global $tc;
		extract( shortcode_atts( array(
			'id'				 => false,
			'event_table_class'	 => 'event_tickets tickera',
			'ticket_type_title'	 => __( 'Ticket Type', 'tc' ),
			'price_title'		 => __( 'Price', 'tc' ),
			'cart_title'		 => __( 'Cart', 'tc' ),
			'soldout_message'	 => __( 'Tickets are sold out.' ),
			'wrapper'			 => '' ), $atts ) );

		$event			 = new TC_Event( $id );
		$event_tickets	 = $event->get_event_ticket_types();
		?>

		<div class="tickera">
			<table class="<?php echo $event_table_class; ?>">
				<tr>
					<th><?php echo $ticket_type_title; ?></th>
					<th><?php echo $price_title; ?></th>
					<th><?php echo $cart_title; ?></th>
				</tr>
				<?php
				foreach ( $event_tickets as $event_ticket_id ) {
					$event_ticket = new TC_Ticket( $event_ticket_id );
					?>
					<tr>
						<td><?php echo $event_ticket->details->post_title; ?></td>
						<td><?php echo do_shortcode( '[ticket_price id="' . $event_ticket->details->ID . '"]' ); ?></td>
						<td><?php echo do_shortcode( '[ticket id="' . $event_ticket->details->ID . '" soldout_message="' . $soldout_message . '"]' ); ?></td>
					</tr>
				<?php } ?>
			</table>
		</div><!-- tickera -->

		<?php
		$content = ob_get_clean();
		return $content;
	}

	function ticket_cart_button( $atts ) {
		global $tc;

		$tc_general_settings = get_option( 'tc_general_setting', false );

		extract( shortcode_atts( array(
			'id'					 => false,
			'title'					 => __( 'Add to Cart', 'tc' ),
			'show_price'			 => false,
			'price_position'		 => 'after',
			'price_wrapper'			 => 'span',
			'price_wrapper_class'	 => 'price',
			'soldout_message'		 => __( 'Tickets are sold out.' ),
			'type'					 => 'cart',
			'wrapper'				 => '' ), $atts ) );

		$show_price = (bool) $show_price;

		if ( isset( $id ) ) {
			$ticket_type = new TC_Ticket( $id, 'publish' );
		}

		if ( isset( $ticket_type->details->ID ) ) {//check if ticket still exists
			if ( $show_price ) {
				$with_price_content = ' <span class="' . $price_wrapper_class . '">' . do_shortcode( '[ticket_price id="' . $id . '"]' ) . '</span> ';
			} else {
				$with_price_content = '';
			}

			if ( array_key_exists( $id, $tc->get_cart_cookie() ) ) {
				$button = sprintf( '<' . $price_wrapper . ' class="tc_in_cart">%s <a href="%s">%s</a></' . $price_wrapper . '>', __( 'Ticket added to', 'tc' ), $tc->get_cart_slug( true ), __( 'Cart', 'tc' ) );
			} else {
				if ( $ticket_type->is_ticket_exceeded_quantity_limit() === false ) {

					if ( isset( $tc_general_settings[ 'force_login' ] ) && $tc_general_settings[ 'force_login' ] == 'yes' && !is_user_logged_in() ) {
						$button = '<form class="cart_form">'
						. ($price_position == 'before' ? $with_price_content : '') . '<a href="' . wp_login_url( get_permalink() ) . '" class="add_to_cart_force_login" id="ticket_' . $id . '"><span class="title">' . $title . '</span></a>' . ($price_position == 'after' ? $with_price_content : '')
						. '<input type="hidden" name="ticket_id" class="ticket_id" value="' . $id . '"/>'
						. '</form>';
					} else {
						$button = '<form class="cart_form">'
						. ($price_position == 'before' ? $with_price_content : '') . '<a href="#" class="add_to_cart" data-button-type="' . $type . '" id="ticket_' . $id . '"><span class="title">' . $title . '</span></a>' . ($price_position == 'after' ? $with_price_content : '')
						. '<input type="hidden" name="ticket_id" class="ticket_id" value="' . $id . '"/>'
						. '</form>';
					}
				} else {
					$button = '<span class="tc_tickets_sold">' . $soldout_message . '</span>';
				}
			}

			if ( $id && get_post_type( $id ) == 'tc_tickets' ) {
				return $button;
			} else {
				return __( 'Unknown ticket ID', 'tc' );
			}
		} else {
			return '';
		}
	}

	function ticket_price( $atts ) {
		global $tc;
		extract( shortcode_atts( array(
			'id' => ''
		), $atts ) );

		$ticket_type = new TC_Ticket( $id, 'publish' );
		return $tc->get_cart_currency_and_format( $ticket_type->details->price_per_ticket );
	}

	function tc_cart_page( $atts ) {
		global $tc;
		ob_start();
		include_once( $tc->plugin_dir . 'includes/templates/shortcode-cart-contents.php' );
		$content = wpautop( ob_get_clean(), true );
		return $content;
	}

	function tc_payment_page( $atts ) {
		global $tc;
		ob_start();
		include_once( $tc->plugin_dir . 'includes/templates/page-payment.php' );
		$content = wpautop( ob_get_clean(), true );
		return $content;
	}

	function tc_order_confirmation_page( $atts ) {
		global $tc;
		ob_start();
		include_once( $tc->plugin_dir . 'includes/templates/page-confirmation.php' );
		$content = wpautop( ob_get_clean(), true );
		return $content;
	}

	function tc_order_details_page( $atts ) {
		global $tc, $wp;
		ob_start();
		include_once( $tc->plugin_dir . 'includes/templates/page-order.php' );
		$content = wpautop( ob_get_clean(), true );
		return $content;
	}

}

$tc_shortcodes = new TC_Shortcodes();
?>