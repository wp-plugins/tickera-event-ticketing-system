<div class="tc_cart_errors">
	<?php echo apply_filters( 'tc_cart_errors', '' ); ?>
</div>
<?php
global $tc;
$cart_contents		 = $tc->get_cart_cookie();
$tc_general_settings = get_option( 'tc_general_setting', false );
if ( isset( $tc_general_settings[ 'force_login' ] ) && $tc_general_settings[ 'force_login' ] == 'yes' && !is_user_logged_in() ) {
	?>
	<div class="force_login_message"><?php printf( __( 'Please %s to see this page', 'tc' ), '<a href="' . wp_login_url( $tc->get_cart_slug( true ) ) . '">' . __( 'Log In', 'tc' ) . '</a>' ); ?></div>
	<?php
} else {
	if ( !empty( $cart_contents ) ) {
		?>
		<form id="tickera_cart" method="post" class="tickera" name="tickera_cart">
			<input type='hidden' name='cart_action' id='cart_action' value="update_cart" />
			<div class="tc-container">
				<div class="tickera-checkout">
					<table cellspacing="0" class="tickera_table" cellpadding="10">
						<thead>
							<tr>
								<?php do_action( 'tc_cart_col_title_before_ticket_type' ); ?>
								<th><?php _e( 'Ticket Type', 'tc' ); ?></th>
								<?php do_action( 'tc_cart_col_title_before_ticket_price' ); ?>
								<th class="ticket-price-header"><?php _e( 'Ticket Price', 'tc' ); ?></th>
								<?php do_action( 'tc_cart_col_title_before_quantity' ); ?>
								<th><?php _e( 'Quantity', 'tc' ); ?></th>
								<?php do_action( 'tc_cart_col_title_before_total_price' ); ?>
								<th><?php _e( 'Subtotal', 'tc' ); ?></th>
								<?php do_action( 'tc_cart_col_title_after_total_price' ); ?>
							</tr>
						</thead>
						<tbody>
							<?php
							$cart_subtotal = 0;
							foreach ( $cart_contents as $ticket_type => $ordered_count ) {
								$ticket			 = new TC_Ticket( $ticket_type );
								$cart_subtotal	 = $cart_subtotal + ($ticket->details->price_per_ticket * $ordered_count);

								if ( !isset( $_SESSION ) ) {
									session_start();
								}
								$_SESSION[ 'cart_subtotal_pre' ] = $cart_subtotal;
								?>
								<tr>
									<?php do_action( 'tc_cart_col_value_before_ticket_type', $ticket_type, $ordered_count, $ticket->details->price_per_ticket ); ?>
									<td class="ticket-type"><?php echo $ticket->details->post_title; ?><input type="hidden" name="ticket_cart_id[]" value="<?php echo $ticket_type; ?>"></td>
									<?php do_action( 'tc_cart_col_value_before_ticket_price', $ticket_type, $ordered_count, $ticket->details->price_per_ticket ); ?>
									<td class="ticket-price"><span class="ticket_price"><?php echo apply_filters( 'tc_cart_currency_and_format', apply_filters( 'tc_cart_price_per_ticket', $ticket->details->price_per_ticket, $ticket_type ) ); ?></span></td>
									<?php do_action( 'tc_cart_col_value_before_quantity', $ticket_type, $ordered_count, $ticket->details->price_per_ticket ); ?>
									<td class="ticket-quantity" class="ticket_quantity"><input class="tickera_button minus" type="button" value="-"><input type="text" name="ticket_quantity[]" value="<?php echo $ordered_count; ?>" class="quantity">  <input class="tickera_button plus" type="button" value="+" /></td>
									<?php do_action( 'tc_cart_col_value_before_total_price', $ticket_type, $ordered_count, $ticket->details->price_per_ticket ); ?>
									<td class="ticket-total"><span class="ticket_total"><?php echo apply_filters( 'tc_cart_currency_and_format', apply_filters( 'tc_cart_price_per_ticket_and_quantity', ($ticket->details->price_per_ticket * $ordered_count ), $ticket_type, $ordered_count ) ); ?></span></td>
									<?php do_action( 'tc_cart_col_value_after_total_price', $ticket_type, $ordered_count, $ticket->details->price_per_ticket ); ?>
								</tr>
							<?php } ?>
							<tr class="last-table-row">
								<td class="ticket-total-all" colspan="<?php echo apply_filters( 'tc_cart_table_colspan', '5' ); ?>">
									<?php do_action( 'tc_cart_col_value_before_total_price_subtotal', apply_filters( 'tc_cart_subtotal', $cart_subtotal ) ); ?>
									<span class="total_item_title"><?php _e( 'SUBTOTAL: ', 'tc' ); ?></span><span class="total_item_amount"><?php echo apply_filters( 'tc_cart_currency_and_format', apply_filters( 'tc_cart_subtotal', $cart_subtotal ) ); ?></span>
									<?php do_action( 'tc_cart_col_value_before_total_price_discount', apply_filters( 'tc_cart_discount', 0 ) ); ?>
									<span class="total_item_title"><?php _e( 'DISCOUNT: ', 'tc' ); ?></span><span class="total_item_amount"><?php echo apply_filters( 'tc_cart_currency_and_format', apply_filters( 'tc_cart_discount', 0 ) ); ?></span>
									<?php do_action( 'tc_cart_col_value_before_total_price_total', apply_filters( 'tc_cart_total', $cart_subtotal ) ); ?>
									<span class="total_item_title cart_total_price_title"><?php _e( 'TOTAL: ', 'tc' ); ?></span><span class="total_item_amount cart_total_price"><?php echo apply_filters( 'tc_cart_currency_and_format', apply_filters( 'tc_cart_total', $cart_subtotal ) ); ?></span>
									<?php do_action( 'tc_cart_col_value_after_total_price_total' ); ?>
								</td>
								<?php do_action( 'tc_cart_col_value_after_total_price_total' ); ?>
							</tr>
							<tr>
								<td class="actions" colspan="<?php echo apply_filters( 'tc_cart_table_colspan', '5' ); ?>">
									<?php do_action( 'tc_cart_before_discount_field' ); ?>
									<?php
									if ( !isset( $tc_general_settings[ 'show_discount_field' ] ) || (isset( $tc_general_settings[ 'show_discount_field' ] ) && $tc_general_settings[ 'show_discount_field' ] == 'yes') ) {
										?>
										<span class="coupon-code"><input type="text" name="coupon_code" id="coupon_code" placeholder="<?php _e( "Discount Code", "tc" ); ?>" class="coupon_code tickera-input-field" value="<?php echo esc_attr( (isset( $_POST[ 'coupon_code' ] ) ? $_POST[ 'coupon_code' ] : (isset( $_COOKIE[ 'tc_discount_code_' . COOKIEHASH ] ) ? $_COOKIE[ 'tc_discount_code_' . COOKIEHASH ] : '') ) ); ?>" /></span> <input type="submit" id="apply_coupon" value="<?php _e( "APPLY", "tc" ); ?>" class="apply_coupon tickera-button"><span class="coupon-code-message"><?php echo apply_filters( 'tc_discount_code_message', '' ); ?></span><?php do_action( 'tc_cart_after_discount_field' ); ?>
									<?php } ?>
									<input type="submit" id="update_cart" value="<?php _e( "Update Cart", "tc" ); ?>" class="tickera_update tickera-button">
									<?php do_action( 'tc_cart_after_update_cart' ); ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div><!-- tickera-checkout -->
			</div><!-- tc-container -->

			<div class="tickera_additional_info">

				<div class="tickera_buyer_info info_section">
					<h3><?php _e( 'Buyer Info', 'tc' ); ?></h3>
					<?php
					$buyer_form = new TC_Cart_Form();

					$buyer_form_fields = $buyer_form->get_buyer_info_fields();

					foreach ( $buyer_form_fields as $field ) {
						if ( $field[ 'field_type' ] == 'function' ) {
							eval( $field[ 'function' ] . '();' );
						}
						?><?php if ( $field[ 'field_type' ] == 'text' ) { ?><div class="fields-wrap <?php
							if ( isset( $field[ 'field_class' ] ) ) {
								echo $field[ 'field_class' ];
							}
							?>"><label><span><?php echo ($field[ 'required' ] ? '*' : ''); ?><?php echo $field[ 'field_title' ]; ?></span><input type="<?php echo $field[ 'field_type' ]; ?>" <?php
										 if ( isset( $field[ 'field_placeholder' ] ) ) {
											 echo 'placeholder="' . esc_attr( $field[ 'field_placeholder' ] ) . '"';
										 }
										 ?> class="buyer-field-<?php echo $field[ 'field_type' ]; ?> tickera-input-field" value="<?php echo (isset( $_POST[ 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ] ] ) ? $_POST[ 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ] ] : ''); ?>" name="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>"></label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>
						<?php if ( $field[ 'field_type' ] == 'textarea' ) { ?><div class="fields-wrap <?php
							if ( isset( $field[ 'field_class' ] ) ) {
								echo $field[ 'field_class' ];
							}
							?>"><label><span><?php echo ($field[ 'required' ] ? '*' : ''); ?><?php echo $field[ 'field_title' ]; ?></span><textarea class="buyer-field-<?php echo $field[ 'field_type' ]; ?> tickera-input-field" <?php
								 if ( isset( $field[ 'field_placeholder' ] ) ) {
									 echo 'placeholder="' . esc_attr( $field[ 'field_placeholder' ] ) . '"';
								 }
								 ?> name="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>"><?php echo (isset( $_POST[ 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ] ] ) ? $_POST[ 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ] ] : ''); ?></textarea></label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

						<?php if ( $field[ 'field_type' ] == 'radio' ) { ?><div class="fields-wrap <?php
							if ( isset( $field[ 'field_class' ] ) ) {
								echo $field[ 'field_class' ];
							}
							?>"><label><span><?php echo ($field[ 'required' ] ? '*' : ''); ?><?php echo $field[ 'field_title' ]; ?></span>
										 <?php
										 if ( isset( $field[ 'field_values' ] ) ) {
											 $field_values = explode( ',', $field[ 'field_values' ] );
											 foreach ( $field_values as $field_value ) {
												 ?>
											<input type="<?php echo $field[ 'field_type' ]; ?>" class="buyer-field-<?php echo $field[ 'field_type' ]; ?> tickera-input-field" value="<?php echo trim( $field_value ); ?>" name="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>" <?php
											if ( isset( $field[ 'field_default_value' ] ) && $field[ 'field_default_value' ] == trim( $field_value ) ) {
												echo 'checked';
											}
											?>><?php echo trim( $field_value ); ?>
												   <?php
											   }
										   }
										   ?>
								</label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

						<?php if ( $field[ 'field_type' ] == 'checkbox' ) { ?><div class="fields-wrap <?php
							if ( isset( $field[ 'field_class' ] ) ) {
								echo $field[ 'field_class' ];
							}
							?>"><label><span><?php echo ($field[ 'required' ] ? '*' : ''); ?><?php echo $field[ 'field_title' ]; ?></span>
										 <?php
										 if ( isset( $field[ 'field_values' ] ) ) {
											 $field_values = explode( ',', $field[ 'field_values' ] );
											 foreach ( $field_values as $field_value ) {
												 ?>
											<input type="<?php echo $field[ 'field_type' ]; ?>" class="buyer-field-<?php echo $field[ 'field_type' ]; ?> tickera-input-field" value="<?php echo trim( $field_value ); ?>" <?php
											if ( isset( $field[ 'field_default_value' ] ) && $field[ 'field_default_value' ] == trim( $field_value ) ) {
												echo 'checked';
											}
											?>><?php echo trim( $field_value ); ?>
												   <?php
											   }
											   ?>
										<input type="hidden" class="checkbox_values" name="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>" value="" />
										<?php
									}
									?>
								</label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

						<?php if ( $field[ 'field_type' ] == 'select' ) { ?><div class="fields-wrap <?php
							if ( isset( $field[ 'field_class' ] ) ) {
								echo $field[ 'field_class' ];
							}
							?>"><label><span><?php echo ($field[ 'required' ] ? '*' : ''); ?><?php echo $field[ 'field_title' ]; ?></span>
									<select class="buyer-field-<?php echo $field[ 'field_type' ]; ?> tickera-input-field" name="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>">	 
										<?php
										if ( isset( $field[ 'field_values' ] ) ) {
											$field_values = explode( ',', $field[ 'field_values' ] );
											foreach ( $field_values as $field_value ) {
												?>
												<option value="<?php echo trim( $field_value ); ?>" <?php
												if ( isset( $field[ 'field_default_value' ] ) && $field[ 'field_default_value' ] == trim( $field_value ) ) {
													echo 'selected';
												}
												?>><?php echo trim( $field_value ); ?>
												</option>
												<?php
											}
										}
										?>
									</select>	
								</label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

						<?php if ( $field[ 'required' ] ) { ?><input type="hidden" name="tc_cart_required[]" value="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>" /><?php } ?>
					<?php }//buyer fields        ?>


				</div><!-- tickera_buyer_info -->  


				<?php
				if ( !isset( $tc_general_settings[ 'show_owner_fields' ] ) || (isset( $tc_general_settings[ 'show_owner_fields' ] ) && $tc_general_settings[ 'show_owner_fields' ] == 'yes') ) {
					$show_owner_fields = true;
				} else {
					$show_owner_fields = false;
				}
				?>   
				<div class="tickera_owner_info info_section" <?php
				if ( !$show_owner_fields ) {
					echo 'style="display: none"';
				}
				?>>
						 <?php
						 $ticket_type_order = 1;
						 foreach ( $cart_contents as $ticket_type => $ordered_count ) {
							 $owner_form			 = new TC_Cart_Form( $ticket_type );
							 $owner_form_fields	 = $owner_form->get_owner_info_fields( $ticket_type );

							 $ticket = new TC_Ticket( $ticket_type );
							 ?>
						<h2><?php echo apply_filters( 'tc_checkout_owner_info_ticket_title', $ticket->details->post_title, $ticket_type, $cart_contents ); ?></h2>
						<?php
						for ( $i = 1; $i <= $ordered_count; $i++ ) {
							?>																																																																											
							<h5><?php
								echo $i . '. ';
								_e( 'Owner Info', 'tc' );
								?></h5>

							<div class="owner-info-wrap">
								<?php foreach ( $owner_form_fields as $field ) { ?>

									<?php
									if ( $field[ 'field_type' ] == 'function' ) {
										eval( $field[ 'function' ] . '("' . $field[ 'field_name' ] . '"' . (isset( $field[ 'post_field_type' ] ) ? ', "' . $field[ 'post_field_type' ] . '"' : '') . (isset( $ticket_type ) ? ',' . $ticket_type : '') . (isset( $ordered_count ) ? ',' . $ordered_count : '') . ');' );
									}
									if ( $show_owner_fields ) {
										?>
										<?php if ( $field[ 'field_type' ] == 'text' ) { ?><div class="fields-wrap <?php
											if ( isset( $field[ 'field_class' ] ) ) {
												echo $field[ 'field_class' ];
											}
											?>"><label><span><?php echo ($field[ 'required' ] ? '*' : ''); ?><?php echo $field[ 'field_title' ]; ?></span><input type="<?php echo $field[ 'field_type' ]; ?>" <?php
														 if ( isset( $field[ 'field_placeholder' ] ) ) {
															 echo 'placeholder="' . esc_attr( $field[ 'field_placeholder' ] ) . '"';
														 }
														 ?> class="owner-field-<?php echo $field[ 'field_type' ]; ?> tickera-input-field" value="" name="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>[<?php echo $ticket_type; ?>][]"></label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

										<?php if ( $field[ 'field_type' ] == 'textarea' ) { ?><div class="fields-wrap <?php
											if ( isset( $field[ 'field_class' ] ) ) {
												echo $field[ 'field_class' ];
											}
											?>"><label><span><?php echo ($field[ 'required' ] ? '*' : ''); ?><?php echo $field[ 'field_title' ]; ?></span><textarea class="owner-field-<?php echo $field[ 'field_type' ]; ?> tickera-input-field" <?php
												 if ( isset( $field[ 'field_placeholder' ] ) ) {
													 echo 'placeholder="' . esc_attr( $field[ 'field_placeholder' ] ) . '"';
												 }
												 ?> name="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>[<?php echo $ticket_type; ?>][]"></textarea></label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

										<?php if ( $field[ 'field_type' ] == 'radio' ) { ?><div class="fields-wrap <?php
											if ( isset( $field[ 'field_class' ] ) ) {
												echo $field[ 'field_class' ];
											}
											?>"><label><span><?php echo ($field[ 'required' ] ? '*' : ''); ?><?php echo $field[ 'field_title' ]; ?></span>
														 <?php
														 if ( isset( $field[ 'field_values' ] ) ) {
															 $field_values = explode( ',', $field[ 'field_values' ] );
															 foreach ( $field_values as $field_value ) {
																 ?>
															<input type="<?php echo $field[ 'field_type' ]; ?>" class="owner-field-<?php echo $field[ 'field_type' ]; ?> tickera-input-field" value="<?php echo esc_attr( trim( $field_value ) ); ?>" name="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>[<?php echo $ticket_type; ?>][]" <?php
															if ( isset( $field[ 'field_default_value' ] ) && $field[ 'field_default_value' ] == trim( $field_value ) ) {
																echo 'checked';
															}
															?>><?php echo trim( $field_value ); ?>
																   <?php
															   }
														   }
														   ?>
												</label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

										<?php if ( $field[ 'field_type' ] == 'checkbox' ) { ?><div class="fields-wrap <?php
											if ( isset( $field[ 'field_class' ] ) ) {
												echo $field[ 'field_class' ];
											}
											?>"><label><span><?php echo ($field[ 'required' ] ? '*' : ''); ?><?php echo $field[ 'field_title' ]; ?></span>
														 <?php
														 if ( isset( $field[ 'field_values' ] ) ) {
															 $field_values = explode( ',', $field[ 'field_values' ] );
															 foreach ( $field_values as $field_value ) {
																 ?>
															<input type="<?php echo $field[ 'field_type' ]; ?>" class="owner-field-<?php echo $field[ 'field_type' ]; ?> tickera-input-field" value="<?php echo esc_attr( trim( $field_value ) ); ?>" <?php
															if ( isset( $field[ 'field_default_value' ] ) && $field[ 'field_default_value' ] == trim( $field_value ) ) {
																echo 'checked';
															}
															?>><?php echo trim( $field_value ); ?>
																   <?php
															   }
															   ?>
														<input type="hidden" class="checkbox_values" name="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>[<?php echo $ticket_type; ?>][]" value="" />
														<?php
													}
													?>
												</label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

										<?php if ( $field[ 'field_type' ] == 'select' ) { ?><div class="fields-wrap <?php
											if ( isset( $field[ 'field_class' ] ) ) {
												echo $field[ 'field_class' ];
											}
											?>"><label><span><?php echo ($field[ 'required' ] ? '*' : ''); ?><?php echo $field[ 'field_title' ]; ?></span>
													<select class="owner-field-<?php echo $field[ 'field_type' ]; ?> tickera-input-field" name="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>[<?php echo $ticket_type; ?>][]">	 
														<?php
														if ( isset( $field[ 'field_values' ] ) ) {
															$field_values = explode( ',', $field[ 'field_values' ] );
															foreach ( $field_values as $field_value ) {
																?>
																<option value="<?php echo trim( $field_value ); ?>" <?php
																if ( isset( $field[ 'field_default_value' ] ) && $field[ 'field_default_value' ] == trim( $field_value ) ) {
																	echo 'selected';
																}
																?>><?php echo trim( $field_value ); ?>
																</option>
																<?php
															}
														}
														?>
													</select>	
												</label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

										<?php
										if ( $field[ 'required' ] && $show_owner_fields ) {
											if ( $show_owner_fields ) {
												?>
												<input type="hidden" name="tc_cart_required[]" value="" />
												<?php
											}
										}
										?>                      																																																																																																																																																													                                                                
										<!--<div class="tc-clearfix"></div>-->
										<?php
									}
								}
								?>		
							</div><!-- owner-info-wrap -->																																																															                                                                                
						<?php } $i++; ?>
						<div class="tc-clearfix"></div>     



					<?php } //foreach ( $cart_contents as $ticket_type => $ordered_count ) ?>

				</div><!-- tickera_owner_info -->
				<?php do_action( 'before_cart_submit' ); ?>
				<p><input type="submit" id="proceed_to_checkout" name='proceed_to_checkout' value="<?php _e( "Proceed to Checkout", "tc" ); ?>" class="tickera_checkout tickera-button"></p>
			</div><!-- tickera_additional_info -->
			<?php
		} else {
			?><div class="cart_empty_message"><?php _e( "The cart is empty.", "tc" ); ?></div>
			<?php
		}
	}
	?>
	<?php wp_nonce_field( 'page_cart' ); ?>
</form>