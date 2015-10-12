<?php

class TC_Shortcode_Builder {

	function __construct() {
		global $tc;

		if ( isset( $_GET[ 'page' ] ) && ($_GET[ 'page' ] == 'tc_events' || $_GET[ 'page' ] == 'tc_ticket_types' || $_GET[ 'page' ] == 'tc_settings') ) {
			return;
		}

		add_action( 'media_buttons', array( &$this, 'media_buttons' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_styles_scripts' ) );
		add_action( 'in_admin_footer', array( &$this, 'show_shortcodes' ) );
	}

	public function show_shortcodes() {
		if ( did_action( 'media_buttons' ) == 0 ) {
			return;
		}

		$shortcodes = array(
			'tc_ticket'			 => __( 'Display a ticket / add to cart button', 'tc' ),
			'tc_event'			 => __( 'Display a list of tickets for an event', 'tc' ),
			'event_tickets_sold' => __( 'Display a number of tickets sold for an event', 'tc' ),
			'event_tickets_left' => __( 'Display a number of tickets left for an event', 'tc' ),
			'tickets_sold'		 => __( 'Display a number of sold tickets', 'tc' ),
			'tickets_left'		 => __( 'Display a number of available tickets', 'tc' ),
			'tc_order_history'	 => __( 'Display order history for a user', 'tc' ),
		);

		$shortcodes = apply_filters( 'tc_shortcodes', $shortcodes );
		?>
		<div id="tc-shortcode-builder-wrap" style="display:none">
			<form id="tc-shortcode-builder">
				<div class="tc-title-wrap">
					<h3><?php _e( 'Add Short Code', 'tc' ); ?></h3>
				</div><!-- .tc-title-wrap -->

				<div class="tc-shortcode-wrap">
					<select name="shortcode-select" id="tc-shortcode-select">					
						<?php foreach ( $shortcodes as $shortcode => $label ) : ?>
							<option value="<?php echo esc_attr( $shortcode ); ?>"><?php echo $label; ?></option>
						<?php endforeach; ?>
					</select>

					<div class="tc-shortcode-atts">
						<h3><?php _e( 'Shortcode Attributes', 'tc' ); ?></h3>
						<?php
						foreach ( $shortcodes as $shortcode => $label ) {
							$func = 'show_' . $shortcode . '_attributes';

							if ( method_exists( $this, $func ) ) {
								call_user_func( array( &$this, $func ) );
							}
							if ( function_exists( $func ) ) {
								call_user_func( $func );
							}
						}
						?>
					</div>
					<p class="submit">
						<input class="button-primary" type="submit" value="<?php _e( 'Insert Short Code', 'tc' ); ?>" />
					</p>                                
				</div><!-- .tc-shortcode-wrap -->

			</form>
		</div>
		<?php
	}

	public function show_tc_order_history_attributes() {
		?>
		<table id="tc-order-history-shortcode" class="shortcode-table" style="display:none">
			<tr>
				<th scope="row"><?php _e( 'Without extra attributes', 'tc' ); ?></th>
				<td>
					<?php _e( 'Just insert a shortcode in the post / page and it will show order history of the current logged in user.', 'tc' ); ?>
				</td>
			</tr>
		</table>	
		<?php
	}

	public function show_tc_ticket_attributes() {
		?>
		<table id="tc-ticket-shortcode" class="shortcode-table" style="display:none">
			<tr>
				<th scope="row"><?php _e( 'Ticket Type', 'tc' ); ?></th>
				<td>
					<select name="id">
						<?php
						$wp_tickets_search = new TC_Tickets_Search( '', '', -1 );
						foreach ( $wp_tickets_search->get_results() as $ticket_type ) {
							$ticket = new TC_Ticket( $ticket_type->ID );
							?>
							<option value="<?php echo esc_attr( $ticket->details->ID ); ?>"><?php echo $ticket->details->post_title; ?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Link Title', 'tc' ); ?></th>
				<td>
					<input type="text" name="title" value="" placeholder="<?php echo esc_attr( __( 'Add to Cart', 'tc' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Soldout Message', 'tc' ); ?></th>
				<td>
					<input type="text" name="soldout_message" value="" placeholder="<?php echo esc_attr( __( 'Tickets are sold out.', 'tc' ) ); ?>" /><br />
					<span class="description"><?php _e( 'The message which will be shown when all tickets are sold.', 'tc' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Show Price', 'tc' ); ?></th>
				<td>
					<select name="show_price" data-default-value="false">
						<option value="false"><?php _e( 'No', 'tc' ); ?></option>
						<option value="true"><?php _e( 'Yes', 'tc' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Price Position', 'tc' ); ?></th>
				<td>
					<select name="price_position" data-default-value="after">
						<option value="after"><?php _e( 'After', 'tc' ); ?></option>
						<option value="before"><?php _e( 'Before', 'tc' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Link Type', 'tc' ); ?></th>
				<td>
					<select name="type" data-default-value="cart">
						<option value="cart"><?php _e( 'Cart', 'tc' ); ?></option>
						<option value="buynow"><?php _e( 'Buy Now', 'tc' ); ?></option>
					</select>
					<span class="description"><?php _e( 'If Buy Now is selected, after clicking on the link, user will be redirected automatically to the cart page.', 'tc' ); ?></span>
				</td>
			</tr>
		</table>	
		<?php
	}

	public function show_tc_event_attributes() {
		?>
		<table id="tc-event-shortcode" class="shortcode-table" style="display:none">
			<tr>
				<th scope="row"><?php _e( 'Event', 'tc' ); ?></th>
				<td>
					<select name="id">
						<?php
						$wp_events_search = new TC_Events_Search( '', '', -1 );
						foreach ( $wp_events_search->get_results() as $event ) {
							$event = new TC_Event( $event->ID );
							?>
							<option value="<?php echo esc_attr( $event->details->ID ); ?>"><?php echo $event->details->post_title; ?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Ticket Type Column Title', 'tc' ); ?></th>
				<td>
					<input type="text" name="ticket_type_title" value="" placeholder="<?php echo esc_attr( __( 'Ticket Type', 'tc' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Price Column Title', 'tc' ); ?></th>
				<td>
					<input type="text" name="price_title" value="" placeholder="<?php echo esc_attr( __( 'Price', 'tc' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Cart Column Title', 'tc' ); ?></th>
				<td>
					<input type="text" name="cart_title" value="" placeholder="<?php echo esc_attr( __( 'Cart', 'tc' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Quantity Column Title', 'tc' ); ?></th>
				<td>
					<input type="text" name="quantity_title" value="" placeholder="<?php echo esc_attr( __( 'Qty.', 'tc' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Soldout Message', 'tc' ); ?></th>
				<td>
					<input type="text" name="soldout_message" value="" placeholder="<?php echo esc_attr( __( 'Tickets are sold out.', 'tc' ) ); ?>" />
				</td>
			</tr>

			<tr>
				<th scope="row"><?php _e( 'Show Quantity Selector', 'tc' ); ?></th>
				<td>
					<select name="quantity" data-default-value="">
						<option value=""><?php _e( 'No', 'tc' ); ?></option>
						<option value="true"><?php _e( 'Yes', 'tc' ); ?></option>
					</select>
				</td>
			</tr>

		</table>	
		<?php
	}

	public function show_event_tickets_sold_attributes() {
		?>
		<table id="event-tickets-sold-shortcode" class="shortcode-table" style="display:none">
			<tr>
				<th scope="row"><?php _e( 'Event', 'tc' ); ?></th>
				<td>
					<select name="event_id">
						<?php
						$wp_events_search = new TC_Events_Search( '', '', -1 );
						foreach ( $wp_events_search->get_results() as $event ) {
							$event = new TC_Event( $event->ID );
							?>
							<option value="<?php echo esc_attr( $event->details->ID ); ?>"><?php echo $event->details->post_title; ?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>
		</table>	
		<?php
	}

	public function show_event_tickets_left_attributes() {
		?>
		<table id="event-tickets-left-shortcode" class="shortcode-table" style="display:none">
			<tr>
				<th scope="row"><?php _e( 'Event', 'tc' ); ?></th>
				<td>
					<select name="event_id">
						<?php
						$wp_events_search = new TC_Events_Search( '', '', -1 );
						foreach ( $wp_events_search->get_results() as $event ) {
							$event = new TC_Event( $event->ID );
							?>
							<option value="<?php echo esc_attr( $event->details->ID ); ?>"><?php echo $event->details->post_title; ?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>
		</table>	
		<?php
	}

	public function show_tickets_sold_attributes() {
		?>
		<table id="tickets-sold-shortcode" class="shortcode-table" style="display:none">
			<tr>
				<th scope="row"><?php _e( 'Ticket Type', 'tc' ); ?></th>
				<td>
					<select name="ticket_type_id">
						<?php
						$wp_tickets_search = new TC_Tickets_Search( '', '', -1 );
						foreach ( $wp_tickets_search->get_results() as $event ) {
							$ticket = new TC_Ticket( $event->ID );
							?>
							<option value="<?php echo esc_attr( $ticket->details->ID ); ?>"><?php echo $ticket->details->post_title; ?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>
		</table>	
		<?php
	}

	public function show_tickets_left_attributes() {
		?>
		<table id="tickets-left-shortcode" class="shortcode-table" style="display:none">
			<tr>
				<th scope="row"><?php _e( 'Ticket Type', 'tc' ); ?></th>
				<td>
					<select name="ticket_type_id">
						<?php
						$wp_tickets_search = new TC_Tickets_Search( '', '', -1 );
						foreach ( $wp_tickets_search->get_results() as $event ) {
							$ticket = new TC_Ticket( $event->ID );
							?>
							<option value="<?php echo esc_attr( $ticket->details->ID ); ?>"><?php echo $ticket->details->post_title; ?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>
		</table>	
		<?php
	}

	public function enqueue_styles_scripts() {
		global $tc;
		wp_enqueue_style( $tc->name . '-colorbox', $tc->plugin_url . 'css/colorbox/colorbox.css', false, $tc->version );
		wp_enqueue_script( $tc->name . '-colorbox', $tc->plugin_url . 'js/jquery.colorbox-min.js', false, $tc->version );
		wp_enqueue_script( $tc->name . '-shortcode-builder', $tc->plugin_url . 'js/shortcode-builder.js', array( $tc->name . '-colorbox' ), $tc->version );
	}

	public function media_buttons() {
		global $tc;
		?>
		<a href="javascript:;" class="button tc-shortcode-builder-button" title="<?php echo $tc->title . ' ' . __( 'Shortcodes', 'tc' ); ?>"><span class="wp-media-buttons-icon dashicons dashicons-tickets-alt"></span> <?php echo $tc->title; ?></a>
		<?php
	}

}

$shortcode_builder = new TC_Shortcode_Builder();
