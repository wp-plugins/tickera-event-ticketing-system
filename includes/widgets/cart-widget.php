<?php

class TC_Cart_Widget extends WP_Widget {

	function TC_Cart_Widget() {
		$widget_ops = array( 'classname' => 'tc_cart_widget', 'description' => __( 'Displays tickets added to cart', 'tc' ) );
		parent::__construct( 'TC_Cart_Widget', __( 'Tickets Cart', 'tc' ), $widget_ops );
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );

		$title			 = $instance[ 'title' ];
		$button_title	 = $instance[ 'button_title' ];
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'tc' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo (!isset( $title ) ? __( 'Cart' ) : esc_attr( $title )); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'button_title' ); ?>"><?php _e( 'Cart Button Title', 'tc' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'button_title' ); ?>" name="<?php echo $this->get_field_name( 'button_title' ); ?>" type="text" value="<?php echo (!isset( $button_title ) ? __( 'Go to Cart' ) : esc_attr( $button_title )); ?>" /></label></p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance					 = $old_instance;
		$instance[ 'title' ]		 = $new_instance[ 'title' ];
		$instance[ 'button_title' ]	 = $new_instance[ 'button_title' ];
		return $instance;
	}

	function widget( $args, $instance ) {
		global $tc;

		$cart_url					 = trailingslashit( $tc->get_cart_slug( true ) );
		$show_widget_on_cart_page	 = apply_filters( 'tc_show_cart_widget_on_cart_page', false );

		if ( (current_url() !== $cart_url) || $show_widget_on_cart_page ) {

			extract( $args, EXTR_SKIP );

			echo $before_widget;

			$title			 = empty( $instance[ 'title' ] ) ? ' ' : apply_filters( 'tc_cart_widget_title', $instance[ 'title' ] );
			$button_title	 = empty( $instance[ 'button_title' ] ) ? '' : apply_filters( 'tc_cart_widget_button_title', $instance[ 'button_title' ] );

			if ( !empty( $title ) ) {
				echo $before_title . $title . $after_title;
			}
			?>

			<div class="tc_cart_contents" id='tc_cart_widget'>
				<?php
				// Cart Contents
				$cart_contents = $tc->get_cart_cookie();
				if ( !empty( $cart_contents ) ) {
					do_action( 'tc_cart_before_ul', $cart_contents );
					?>
					<ul class='tc_cart_ul'>
						<?php
						foreach ( $cart_contents as $ticket_type => $ordered_count ) {
							$ticket = new TC_Ticket( $ticket_type );
							?>
							<li id='tc_ticket_type_<?php echo $ticket_type; ?>'>
								<?php echo apply_filters( 'tc_cart_widget_item', ($ordered_count . ' x ' . $ticket->details->post_title . ' (' . apply_filters( 'tc_cart_currency_and_format', $ticket->details->price_per_ticket * $ordered_count ) . ')' ), $ordered_count, $ticket->details->post_title, $ticket->details->price_per_ticket ); ?>
							</li>
							<?php
						}
						?>
					</ul><!--tc_cart_ul-->

					<?php
					do_action( 'tc_cart_after_ul', $cart_contents );
				} else {
					do_action( 'tc_cart_before_empty' );
					?>
					<span class='tc_empty_cart'><?php _e( 'The cart is empty', 'tc' ); ?></span>
					<?php
					do_action( 'tc_cart_after_empty' );
				}
				?>
			</div>
			<button class='tc_widget_cart_button' data-url='<?php echo esc_attr( $cart_url ); ?>'><?php echo $button_title; ?></button>
			<div class='tc-clearfix'></div>

			<?php
			echo $after_widget;
		}
	}

}

add_action( 'widgets_init', create_function( '', 'return register_widget("TC_Cart_Widget");' ) );
?>