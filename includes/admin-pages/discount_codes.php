<?php
$discounts = new TC_Discounts();

$page = $_GET[ 'page' ];

if ( isset( $_POST[ 'add_new_discount' ] ) ) {
	if ( check_admin_referer( 'save_discount' ) ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'add_discount_cap' ) ) {
			$discounts->add_new_discount();
			$message = __( 'Discount Code data has been saved successfully.', 'tc' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tc' );
		}
	}
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) {
	$discount	 = new TC_Discount( $_GET[ 'ID' ] );
	$post_id	 = (int) $_GET[ 'ID' ];
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'delete' ) {
	if ( !isset( $_POST[ '_wpnonce' ] ) ) {
		check_admin_referer( 'delete_' . $_GET[ 'ID' ] );
		if ( current_user_can( 'manage_options' ) || current_user_can( 'delete_discount_cap' ) ) {
			$discount	 = new TC_Discount( (int) $_GET[ 'ID' ] );
			$discount->delete_discount();
			$message	 = __( 'Discount Code has been successfully deleted.', 'tc' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tc' );
		}
	}
}

if ( isset( $_GET[ 'page_num' ] ) ) {
	$page_num = (int) $_GET[ 'page_num' ];
} else {
	$page_num = 1;
}

if ( isset( $_GET[ 's' ] ) ) {
	$discountssearch = $_GET[ 's' ];
} else {
	$discountssearch = '';
}

$wp_discounts_search = new TC_Discounts_Search( $discountssearch, $page_num );
$fields				 = $discounts->get_discount_fields();
$columns			 = $discounts->get_columns();
?>
<div class="wrap tc_wrap">
    <h2><?php echo $discounts->form_title; ?><?php if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) { ?><a href="admin.php?page=<?php echo $_GET[ 'page' ]; ?>" class="add-new-h2"><?php _e( 'Add New', 'tc' ); ?></a><?php } ?></h2>

	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo esc_attr( $message ); ?></p></div>
		<?php
	}
	?>

    <form action="" method="post" enctype = "multipart/form-data">
		<?php wp_nonce_field( 'save_discount' ); ?>
		<?php
		if ( isset( $post_id ) ) {
			?>
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
			<?php
		}
		?>
        <table class="discount-table">
            <tbody>
				<?php foreach ( $fields as $field ) { ?>
					<?php if ( $discounts->is_valid_discount_field_type( $field[ 'field_type' ] ) ) { ?>    
						<tr valign="top">

							<th scope="row"><label for="<?php echo $field[ 'field_name' ]; ?>"><?php echo $field[ 'field_title' ]; ?></label></th>

							<td>
								<?php do_action( 'tc_before_discounts_field_type_check' ); ?>
								<?php
								if ( $field[ 'field_type' ] == 'function' ) {
									eval( $field[ 'function' ] . '("' . $field[ 'field_name' ] . '"' . (isset( $post_id ) ? ',' . $post_id : '') . ');' );
									?>
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'text' ) { ?>
									<input type="text" <?php if ( isset( $field[ 'placeholder' ] ) ) {
							echo 'placeholder="' . esc_attr( $field[ 'placeholder' ] ) . '"';
						} ?> class="regular-<?php echo $field[ 'field_type' ]; ?>" value="<?php
									if ( isset( $discount ) ) {
										if ( $field[ 'post_field_type' ] == 'post_meta' ) {
											echo esc_attr( isset( $discount->details->{$field[ 'field_name' ]} ) ? $discount->details->{$field[ 'field_name' ]} : ''  );
										} else {
											echo esc_attr( $discount->details->{$field[ 'post_field_type' ]} );
										}
									}
									?>" id="<?php echo $field[ 'field_name' ]; ?>" name="<?php echo $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>">
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'textarea' ) { ?>
									<textarea class="regular-<?php echo $field[ 'field_type' ]; ?>" id="<?php echo $field[ 'field_name' ]; ?>" name="<?php echo $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>"><?php
										if ( isset( $discount ) ) {
											if ( $field[ 'post_field_type' ] == 'post_meta' ) {
												echo esc_textarea( isset( $discount->details->{$field[ 'field_name' ]} ) ? $discount->details->{$field[ 'field_name' ]} : ''  );
											} else {
												echo esc_textarea( $discount->details->{$field[ 'post_field_type' ]} );
											}
										}
										?></textarea>
									<br /><?php echo $field[ 'field_description' ]; ?>
								<?php } ?>
								<?php
								if ( $field[ 'field_type' ] == 'image' ) {
									?>
									<div class="file_url_holder">
										<label>
											<input class="file_url" type="text" size="36" name="<?php echo $field[ 'field_name' ] . '_file_url_' . $field[ 'post_field_type' ]; ?>" value="<?php
											if ( isset( $discount ) ) {
												echo esc_attr( isset( $discount->details->{$field[ 'field_name' ] . '_file_url'} ) ? $discount->details->{$field[ 'field_name' ] . '_file_url'} : ''  );
											}
											?>" />
											<input class="file_url_button button-secondary" type="button" value="<?php _e( 'Browse', 'tc' ); ?>" />
											<?php echo $field[ 'field_description' ]; ?>
										</label>
									</div>
								<?php } ?>
								<?php do_action( 'tc_after_discounts_field_type_check' ); ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
            </tbody>
        </table>

		<?php submit_button( (isset( $_REQUEST[ 'action' ] ) && $_REQUEST[ 'action' ] == 'edit' ? __( 'Update', 'tc' ) : __( 'Add New', 'tc' ) ), 'primary', 'add_new_discount', true ); ?>

    </form>



    <div class="tablenav">
        <div class="alignright actions new-actions">
            <form method="get" action="?page=<?php echo esc_attr( $page ); ?>" class="search-form">
                <p class="search-box">
                    <input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>' />
                    <label class="screen-reader-text"><?php _e( 'Search Discounts', 'tc' ); ?>:</label>
                    <input type="text" value="<?php echo esc_attr( $discountssearch ); ?>" name="s">
                    <input type="submit" class="button" value="<?php _e( 'Search Discounts', 'tc' ); ?>">
                </p>
            </form>
        </div><!--/alignright-->

    </div><!--/tablenav-->

    <table cellspacing="0" class="widefat shadow-table">
        <thead>
            <tr>
                <!--<th style="" class="manage-column column-cb check-column" id="cb" scope="col" width="<?php echo (isset( $col_sizes[ 0 ] ) ? $col_sizes[ 0 ] . '%' : ''); ?>"><input type="checkbox"></th>-->
				<?php
				$n = 1;
				foreach ( $columns as $key => $col ) {
					?>
					<th style="" class="manage-column column-<?php echo $key; ?>" width="<?php echo (isset( $col_sizes[ $n ] ) ? $col_sizes[ $n ] . '%' : ''); ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
					<?php
					$n++;
				}
				?>
            </tr>
        </thead>

        <tbody>
			<?php
			$style = '';

			foreach ( $wp_discounts_search->get_results() as $discount ) {

				$discount_obj	 = new TC_Discount( $discount->ID );
				$discount_object = apply_filters( 'tc_discount_object_details', $discount_obj->details );

				$style	 = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
				?>
				<tr id='user-<?php echo $discount_object->ID; ?>' <?php echo $style; ?>>
					<!--<th scope='row' class='check-column'>
						<input type='checkbox' name='discounts[]' id='user_<?php echo $discount_object->$key; ?>' class='' value='<?php echo $discount_object->$key; ?>' />
					</th>-->
					<?php
					$n		 = 1;
					foreach ( $columns as $key => $col ) {
						if ( $key == 'edit' ) {
							?>
							<td>                    
								<a class="discounts_edit_link" href="<?php echo admin_url( 'admin.php?page=' . $page . '&action=' . $key . '&ID=' . $discount_object->ID ); ?>"><?php _e( 'Edit', 'tc' ); ?></a>
							</td>
						<?php } elseif ( $key == 'delete' ) {
							?>
							<td>
								<a class="discounts_edit_link tc_delete_link" href="<?php echo wp_nonce_url( 'admin.php?page=' . $page . '&action=' . $key . '&ID=' . $discount_object->ID, 'delete_' . $discount_object->ID ); ?>"><?php _e( 'Delete', 'tc' ); ?></a>
							</td>
							<?php
						} else {
							?>
							<td>
								<?php
								$post_field_type = $discounts->check_field_property( $key, 'post_field_type' );

								if ( isset( $post_field_type ) && $post_field_type == 'post_meta' ) {
									echo apply_filters( 'tc_discount_field_value', $discount_object->$key, $post_field_type, $key );
								} else {
									echo apply_filters( 'tc_discount_field_value', (isset( $discount_object->$post_field_type ) ? $discount_object->$post_field_type : $discount_object->$key ), $post_field_type, $key );
								}
								?>
							</td>
							<?php
						}
					}
					?>
				</tr>
				<?php
			}
			?>

			<?php
			if ( count( $wp_discounts_search->get_results() ) == 0 ) {
				?>
				<tr>
					<td colspan="6"><div class="zero-records"><?php _e( 'No discounts found.', 'tc' ) ?></div></td>
				</tr>
				<?php
			}
			?>
        </tbody>
    </table><!--/widefat shadow-table-->

    <div class="tablenav">
        <div class="tablenav-pages"><?php $wp_discounts_search->page_links(); ?></div>
    </div><!--/tablenav-->

</div>