<?php
$tc_general_settings = get_option( 'tc_general_setting', false );
$cart_contents		 = apply_filters( 'tc_cart_contents', array() );
?>
<div class="tickera_additional_info">
	<div class="tickera_buyer_info info_section">
		<?php
		$buyer_form			 = new TC_Cart_Form();

		$buyer_form_fields = $buyer_form->get_buyer_info_fields();

		foreach ( $buyer_form_fields as $field ) {
			if ( $field[ 'field_type' ] == 'function' ) {
				eval( $field[ 'function' ] . '();' );
			}
			?><?php if ( $field[ 'field_type' ] == 'text' ) { ?><div class="fields-wrap <?php
				if ( isset( $field[ 'field_class' ] ) ) {
					echo $field[ 'field_class' ];
				}
				if ( isset( $field[ 'validation_type' ] ) ) {
					$validation_class = 'tc_validate_field_type_' . $field[ 'validation_type' ];
				} else {
					$validation_class = '';
				}
				?>"><label><span><?php echo $field[ 'field_title' ]; ?><?php echo ($field[ 'required' ] ? '<abbr class="required" title="required">*</abbr>' : ''); ?></span><input type="<?php echo $field[ 'field_type' ]; ?>" <?php
							 if ( isset( $field[ 'field_placeholder' ] ) ) {
								 echo 'placeholder="' . esc_attr( $field[ 'field_placeholder' ] ) . '"';
							 }
							 ?> class="buyer-field-<?php echo $field[ 'field_type' ] . ' ' . $validation_class; ?> tickera-input-field" value="<?php echo (isset( $_POST[ 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ] ] ) ? $_POST[ 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ] ] : $buyer_form->get_default_value( $field )); ?>" name="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>"></label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>
			<?php if ( $field[ 'field_type' ] == 'textarea' ) { ?><div class="fields-wrap <?php
				if ( isset( $field[ 'field_class' ] ) ) {
					echo $field[ 'field_class' ];
				}
				if ( isset( $field[ 'validation_type' ] ) ) {
					$validation_class = 'tc_validate_field_type_' . $field[ 'validation_type' ];
				} else {
					$validation_class = '';
				}
				?>"><label><span><?php echo $field[ 'field_title' ]; ?><?php echo ($field[ 'required' ] ? '<abbr class="required" title="required">*</abbr>' : ''); ?></span><textarea class="buyer-field-<?php echo $field[ 'field_type' ] . ' ' . $validation_class; ?> tickera-input-field" <?php
					 if ( isset( $field[ 'field_placeholder' ] ) ) {
						 echo 'placeholder="' . esc_attr( $field[ 'field_placeholder' ] ) . '"';
					 }
					 ?> name="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>"><?php echo (isset( $_POST[ 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ] ] ) ? $_POST[ 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ] ] : $buyer_form->get_default_value( $field )); ?></textarea></label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

			<?php if ( $field[ 'field_type' ] == 'radio' ) { ?><div class="fields-wrap <?php
				if ( isset( $field[ 'field_class' ] ) ) {
					echo $field[ 'field_class' ];
				}
				if ( isset( $field[ 'validation_type' ] ) ) {
					$validation_class = 'tc_validate_field_type_' . $field[ 'validation_type' ];
				} else {
					$validation_class = '';
				}
				?>"><span><?php echo $field[ 'field_title' ]; ?><?php echo ($field[ 'required' ] ? '<abbr class="required" title="required">*</abbr>' : ''); ?></span>
						 <?php
						 if ( isset( $field[ 'field_values' ] ) ) {
							 $field_values = explode( ',', $field[ 'field_values' ] );
							 foreach ( $field_values as $field_value ) {
								 ?>
							<label><input type="<?php echo $field[ 'field_type' ]; ?>" class="buyer-field-<?php echo $field[ 'field_type' ] . ' ' . $validation_class; ?> tickera-input-field" value="<?php echo trim( $field_value ); ?>" name="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>" <?php
								if ( isset( $field[ 'field_default_value' ] ) && $field[ 'field_default_value' ] == trim( $field_value ) || (empty( $field[ 'field_default_value' ] ) && isset( $field_values[ 0 ] ) && $field_values[ 0 ] == trim( $field_value ) ) ) {
									echo 'checked';
								}
								?>></label><?php echo trim( $field_value ); ?>
								<?php
							}
						}
						?>
					<span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

			<?php if ( $field[ 'field_type' ] == 'checkbox' ) { ?><div class="fields-wrap <?php
				if ( isset( $field[ 'field_class' ] ) ) {
					echo $field[ 'field_class' ];
				}
				if ( isset( $field[ 'validation_type' ] ) ) {
					$validation_class = 'tc_validate_field_type_' . $field[ 'validation_type' ];
				} else {
					$validation_class = '';
				}
				?>"><span><?php echo $field[ 'field_title' ]; ?><?php echo ($field[ 'required' ] ? '<abbr class="required" title="required">*</abbr>' : ''); ?></span>
						 <?php
						 if ( isset( $field[ 'field_values' ] ) ) {
							 $field_values = explode( ',', $field[ 'field_values' ] );
							 foreach ( $field_values as $field_value ) {
								 ?><label><input type="<?php echo $field[ 'field_type' ]; ?>" class="buyer-field-<?php echo $field[ 'field_type' ] . ' ' . $validation_class; ?> tickera-input-field" value="<?php echo trim( $field_value ); ?>" <?php
								if ( isset( $field[ 'field_default_value' ] ) && $field[ 'field_default_value' ] == trim( $field_value ) ) {
									echo 'checked';
								}
								?>></label><?php echo trim( $field_value ); ?>
								<?php
							}
							?>
						<input type="hidden" class="checkbox_values" name="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>" value="" />
						<?php
					}
					?>
					<span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

			<?php if ( $field[ 'field_type' ] == 'select' ) { ?><div class="fields-wrap <?php
				if ( isset( $field[ 'field_class' ] ) ) {
					echo $field[ 'field_class' ];
				}
				if ( isset( $field[ 'validation_type' ] ) ) {
					$validation_class = 'tc_validate_field_type_' . $field[ 'validation_type' ];
				} else {
					$validation_class = '';
				}
				?>"><label><span><?php echo $field[ 'field_title' ]; ?><?php echo ($field[ 'required' ] ? '<abbr class="required" title="required">*</abbr>' : ''); ?></span>
						<select class="buyer-field-<?php echo $field[ 'field_type' ] . ' ' . $validation_class; ?> tickera-input-field" name="<?php echo 'buyer_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>">	 
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
			<?php
		}//buyer fields         
		?>


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

				 $owner_form			 = new TC_Cart_Form( apply_filters( 'tc_ticket_type_id', $ticket_type ) );
				 $owner_form_fields	 = $owner_form->get_owner_info_fields( apply_filters( 'tc_ticket_type_id', $ticket_type ) );

				 $ticket = new TC_Ticket( $ticket_type );
				 ?>
			<h2><?php echo apply_filters( 'tc_checkout_owner_info_ticket_title', $ticket->details->post_title, $ticket_type, $cart_contents ); ?></h2>
			<?php
			for ( $i = 1; $i <= $ordered_count; $i++ ) {
				$owner_index = $i - 1;
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
							<?php if ( $field[ 'field_type' ] == 'text' ) { ?>
								<?php if ( (isset( $tc_general_settings[ 'show_owner_email_field' ] ) && $tc_general_settings[ 'show_owner_email_field' ] == 'yes' && $field[ 'field_name' ] == 'owner_email' ) || $field[ 'field_name' ] !== 'owner_email' ) { ?><div class="fields-wrap <?php
									if ( isset( $field[ 'field_class' ] ) ) {
										echo $field[ 'field_class' ];
									}
									if ( isset( $field[ 'validation_type' ] ) ) {
										$validation_class = 'tc_validate_field_type_' . $field[ 'validation_type' ];
									} else {
										$validation_class = '';
									}
									?>"><label><span><?php echo $field[ 'field_title' ]; ?><?php echo ($field[ 'required' ] ? '<abbr class="required" title="required">*</abbr>' : ''); ?></span><input type="<?php echo $field[ 'field_type' ]; ?>" <?php
												 if ( isset( $field[ 'field_placeholder' ] ) ) {
													 echo 'placeholder="' . esc_attr( $field[ 'field_placeholder' ] ) . '"';
												 }
												 ?> class="owner-field-<?php echo $field[ 'field_type' ] . ' ' . $validation_class; ?> tickera-input-field tc-owner-field <?php if ( $field[ 'field_name' ] == 'owner_email' ) { ?>tc_owner_email<?php } ?>" value="" name="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>[<?php echo $ticket_type; ?>][<?php echo $owner_index; ?>]"></label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?><?php } ?>

							<?php if ( $field[ 'field_type' ] == 'textarea' ) { ?><div class="fields-wrap <?php
								if ( isset( $field[ 'field_class' ] ) ) {
									echo $field[ 'field_class' ];
								}
								if ( isset( $field[ 'validation_type' ] ) ) {
									$validation_class = 'tc_validate_field_type_' . $field[ 'validation_type' ];
								} else {
									$validation_class = '';
								}
								?>"><label><span><?php echo $field[ 'field_title' ]; ?><?php echo ($field[ 'required' ] ? '<abbr class="required" title="required">*</abbr>' : ''); ?></span><textarea class="owner-field-<?php echo $field[ 'field_type' ] . ' ' . $validation_class; ?> tickera-input-field" <?php
									 if ( isset( $field[ 'field_placeholder' ] ) ) {
										 echo 'placeholder="' . esc_attr( $field[ 'field_placeholder' ] ) . '"';
									 }
									 ?> name="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>[<?php echo $ticket_type; ?>][<?php echo $owner_index; ?>]"></textarea></label><span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

							<?php if ( $field[ 'field_type' ] == 'radio' ) { ?><div class="fields-wrap <?php
								if ( isset( $field[ 'field_class' ] ) ) {
									echo $field[ 'field_class' ];
								}
								if ( isset( $field[ 'validation_type' ] ) ) {
									$validation_class = 'tc_validate_field_type_' . $field[ 'validation_type' ];
								} else {
									$validation_class = '';
								}
								?>"><span><?php echo $field[ 'field_title' ]; ?><?php echo ($field[ 'required' ] ? '<abbr class="required" title="required">*</abbr>' : ''); ?></span>
										 <?php
										 if ( isset( $field[ 'field_values' ] ) ) {
											 $field_values = explode( ',', $field[ 'field_values' ] );
											 foreach ( $field_values as $field_value ) {
												 ?>
											<label><input type="<?php echo $field[ 'field_type' ]; ?>" class="owner-field-<?php echo $field[ 'field_type' ] . ' ' . $validation_class; ?> tickera-input-field" value="<?php echo esc_attr( trim( $field_value ) ); ?>" name="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>[<?php echo $ticket_type; ?>][<?php echo $owner_index; ?>]" <?php
												if ( isset( $field[ 'field_default_value' ] ) && $field[ 'field_default_value' ] == trim( $field_value ) || (empty( $field[ 'field_default_value' ] ) && isset( $field_values[ 0 ] ) && $field_values[ 0 ] == trim( $field_value ) ) ) {
													echo 'checked';
												}
												?>></label><?php echo trim( $field_value ); ?>
												<?php
											}
										}
										?>
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

							<?php if ( $field[ 'field_type' ] == 'checkbox' ) { ?><div class="fields-wrap <?php
								if ( isset( $field[ 'field_class' ] ) ) {
									echo $field[ 'field_class' ];
								}
								if ( isset( $field[ 'validation_type' ] ) ) {
									$validation_class = 'tc_validate_field_type_' . $field[ 'validation_type' ];
								} else {
									$validation_class = '';
								}
								?>"><span><?php echo $field[ 'field_title' ]; ?><?php echo ($field[ 'required' ] ? '<abbr class="required" title="required">*</abbr>' : ''); ?></span>
										 <?php
										 if ( isset( $field[ 'field_values' ] ) ) {
											 $field_values = explode( ',', $field[ 'field_values' ] );
											 foreach ( $field_values as $field_value ) {
												 ?><label><input type="<?php echo $field[ 'field_type' ]; ?>" class="owner-field-<?php echo $field[ 'field_type' ] . ' ' . $validation_class; ?> tickera-input-field" value="<?php echo esc_attr( trim( $field_value ) ); ?>" <?php
												if ( isset( $field[ 'field_default_value' ] ) && $field[ 'field_default_value' ] == trim( $field_value ) ) {
													echo 'checked';
												}
												?>></label><?php echo trim( $field_value ); ?>
												<?php
											}
											?>
										<input type="hidden" class="checkbox_values" name="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>[<?php echo $ticket_type; ?>][<?php echo $owner_index; ?>]" value="" />
										<?php
									}
									?>
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span></div><!-- fields-wrap --><?php } ?>

							<?php if ( $field[ 'field_type' ] == 'select' ) { ?><div class="fields-wrap <?php
								if ( isset( $field[ 'field_class' ] ) ) {
									echo $field[ 'field_class' ];
								}
								if ( isset( $field[ 'validation_type' ] ) ) {
									$validation_class = 'tc_validate_field_type_' . $field[ 'validation_type' ];
								} else {
									$validation_class = '';
								}
								?>"><label><span><?php echo $field[ 'field_title' ]; ?><?php echo ($field[ 'required' ] ? '<abbr class="required" title="required">*</abbr>' : ''); ?></span>
										<select class="owner-field-<?php echo $field[ 'field_type' ] . ' ' . $validation_class; ?> tickera-input-field" name="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>[<?php echo $ticket_type; ?>][<?php echo $owner_index; ?>]">	 
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
									<input type="hidden" name="tc_cart_required[]" value="<?php echo 'owner_data_' . $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>" />
									<?php
								}
							}
							?>                      																																																																																																																																																													                                                                
							<!--<div class="tc-clearfix"></div>-->
							<?php
						} //if ( $show_owner_fields )
					}
					?>		
				</div><!-- owner-info-wrap -->																																																															                                                                                
			<?php } $i++; ?>
			<div class="tc-clearfix"></div>     



		<?php } //foreach ( $cart_contents as $ticket_type => $ordered_count )   ?>

	</div><!-- tickera_owner_info -->
	<?php
	do_action( 'before_cart_submit' );
	do_action( 'tc_before_cart_submit' );
	?>
</div><!-- tickera_additional_info -->