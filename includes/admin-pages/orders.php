
<form>
    <input type="hidden" name="hiddenField" />
</form>

<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
		var replaceWith = $( '<input name="temp" class="tc_temp_value" type="text" />' ),
			connectWith = $( 'input[name="hiddenField"]' );

		$( 'td.first_name, td.last_name, td.owner_email' ).inlineEdit( replaceWith, connectWith );
	} );
</script>

<?php
$orders = new TC_Orders();

$page = $_GET[ 'page' ];

if ( isset( $_POST[ 'add_new_order' ] ) ) {
	if ( check_admin_referer( 'save_order' ) ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'save_order_cap' ) ) {
			$orders->add_new_order();
			$message = __( 'Order Data data has been saved successfully.', 'tc' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tc' );
		}
	}
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'details' ) {
	$order	 = new TC_Order( $_GET[ 'ID' ] );
	$post_id = (int) $_GET[ 'ID' ];
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'delete' ) {
	if ( !isset( $_POST[ '_wpnonce' ] ) ) {
		check_admin_referer( 'delete_' . $_GET[ 'ID' ] );
		if ( current_user_can( 'manage_options' ) || current_user_can( 'delete_order_cap' ) ) {
			$order	 = new TC_Order( (int) $_GET[ 'ID' ] );
			$order->delete_order();
			$message = __( 'Order has been successfully deleted.', 'tc' );
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
	$orderssearch = $_GET[ 's' ];
} else {
	$orderssearch = '';
}

$current_status = isset( $_GET[ 'post_status' ] ) ? $_GET[ 'post_status' ] : 'any';

$wp_orders_search = new TC_Orders_Search( $orderssearch, $page_num, '', $current_status );

$fields	 = $orders->get_order_fields();
$columns = $orders->get_columns();
?>
<div class="wrap tc_wrap">
    <h2><?php echo $orders->form_title; ?><?php if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'details' ) { ?><a href="edit.php?post_type=tc_events&page=<?php echo $_GET[ 'page' ]; ?>" class="add-new-h2"><?php _e( 'Back', 'tc' ); ?></a><?php } ?></h2>
	<?php if ( isset( $post_id ) ) { ?>
		<input type='hidden' id='order_id' value='<?php echo esc_attr( $post_id ); ?>' />
	<?php } ?>
	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo esc_attr( $message ); ?></p></div>
		<?php
	}
	?>
    <div class='message_placeholder'></div>

	<?php if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'details' ) { ?>
		<table class="order-table">
			<tbody>
				<?php foreach ( $fields as $field ) { ?>
					<?php if ( $orders->is_valid_order_field_type( $field[ 'field_type' ] ) ) { ?>    
						<tr valign="top">

							<?php if ( $field[ 'field_type' ] !== 'separator' ) { ?>
								<th scope="row"><label for="<?php echo $field[ 'field_name' ]; ?>"><?php echo $field[ 'field_title' ]; ?></label></th>
							<?php } ?>
							<td <?php echo ($field[ 'field_type' ] == 'separator') ? 'colspan="2"' : ''; ?>>
								<?php do_action( 'tc_before_orders_field_type_check' ); ?>
								<?php
								if ( $field[ 'field_type' ] == 'ID' ) {
									echo $order->details->{$field[ 'post_field_type' ] };
								}
								?>
								<?php
								if ( $field[ 'field_type' ] == 'function' ) {
									eval( $field[ 'function' ] . '("' . $field[ 'field_name' ] . '"' . (isset( $post_id ) ? ',' . $post_id : '') . (isset( $field[ 'id' ] ) ? ',"' . $field[ 'id' ] . '"' : '') . ');' );
									?>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'text' ) { ?>
									<input type="text" class="regular-<?php echo $field[ 'field_type' ]; ?>" value="<?php
									if ( isset( $order ) ) {
										if ( $field[ 'post_field_type' ] == 'post_meta' ) {
											echo esc_attr( isset( $order->details->{$field[ 'field_name' ]} ) ? $order->details->{$field[ 'field_name' ]} : ''  );
										} else {
											echo esc_attr( $order->details->{$field[ 'post_field_type' ]} );
										}
										?>" id="<?php
											   echo $field[ 'field_name' ];
										   }
										   ?>" name="<?php echo $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>">

								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'separator' ) { ?>
									<hr />
								<?php } ?>

								<?php do_action( 'tc_after_orders_field_type_check' ); ?>
							</td>
						</tr>
						<?php
					}
				}
				do_action( 'tc_after_order_details_fields' );
				?>
			</tbody>
		</table>
		<?php
	} else {
		$order_statuses = apply_filters( 'tc_order_admin_filter_statuses', array(
			'order_paid'	 => __( 'Paid', 'tc' ),
			'order_received' => __( 'Received', 'tc' ),
			'trash'			 => __( 'Trash', 'tc' ),
		) );

		$count_orders							 = wp_count_posts( 'tc_orders' );
		$count_orders_status[ 'trash' ]			 = (int) $count_orders->trash;
		$count_orders_status[ 'order_received' ] = (int) $count_orders->order_received;
		$count_orders_status[ 'order_paid' ]	 = (int) $count_orders->order_paid;
		$count_orders_status[ 'order_fraud' ]	 = (int) $count_orders->order_fraud;
		$count_orders_status[ 'all' ]			 = (int) $count_orders->order_received + (int) $count_orders->order_paid + (int) $count_orders->order_fraud;
		?>
		<ul class="subsubsub">
			<li class="all"><a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=tc_events&page=tc_orders&post_status=any' ) ); ?>" class="<?php echo $current_status == 'any' ? 'current' : ''; ?>"><?php _e( 'All', 'tc' ); ?> <span class="count">(<?php echo $count_orders_status[ 'all' ]; ?>)</span></a> |</li>
			<?php
			$order_statuses_count					 = count( $order_statuses );
			$i										 = 0;
			foreach ( $order_statuses as $order_status => $order_status_title ) {
				$i++;
				?>
				<li class="<?php echo esc_attr( $order_status ); ?>"><a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=tc_events&page=tc_orders&post_status=' . $order_status ) ); ?>" class="<?php echo $current_status == $order_status ? 'current' : ''; ?>"><?php echo esc_attr( $order_status_title ); ?> <span class="count">(<?php echo $count_orders_status[ $order_status ]; ?>)</span></a> <?php
					if ( $i == $order_statuses_count ) {
						
					} else {
						echo '|';
					}
					?></li>
				<?php
			}
			?>

		</ul>

		<form method="get" action="edit.php?post_type=tc_events&page=<?php echo esc_attr( $page ); ?>" class="search-form">
			<p class="search-box">
				<input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>' />
				<input type='hidden' name='post_type' value='tc_events' />
				<label class="screen-reader-text"><?php _e( 'Search Orders', 'tc' ); ?>:</label>
				<input type="text" value="<?php echo esc_attr( $orderssearch ); ?>" name="s">
				<input type="submit" class="button" value="<?php _e( 'Search Orders', 'tc' ); ?>">
			</p>
		</form>

		<table cellspacing="0" class="widefat shadow-table">
			<thead>
				<tr>
						<!--<th style="" class="manage-column column-cb check-column" id="cb" scope="col" width="<?php //echo (isset($col_sizes[0]) ? $col_sizes[0] . '%' : '');                                            ?>"><input type="checkbox"></th>-->
					<?php
					$n = 1;
					foreach ( $columns as $col ) {
						?>
						<th style="" class="manage-column column-<?php echo $col[ 'id' ]; ?>" width="<?php echo (isset( $col_sizes[ $n ] ) ? $col_sizes[ $n ] . '%' : ''); ?>" id="<?php echo $col[ 'id' ]; ?>" scope="col"><?php echo $col[ 'field_title' ]; ?></th>
						<?php
						$n++;
					}
					?>
				</tr>
			</thead>

			<tbody>
				<?php
				$style = '';

				foreach ( $wp_orders_search->get_results() as $order ) {

					$order_obj		 = new TC_Order( $order->ID );
					$order_object	 = apply_filters( 'tc_order_object_details', $order_obj->details );

					$style	 = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
					?>
					<tr id='order-<?php echo $order_object->ID; ?>' <?php echo $style; ?>>
							<!--<th scope='row' class='check-column'>
									<input type='checkbox' name='events[]' id='user_<?php echo $order_object->$key; ?>' class='' value='<?php echo $order_object->$key; ?>' />
							</th>-->
						<?php
						$n		 = 1;

						foreach ( $columns as $col ) {
							//echo $col['id'].'<br />';
							if ( $col[ 'id' ] == 'details' ) {
								?>
								<td>                    
									<a class="orders_details_link" href="<?php echo admin_url( 'edit.php?post_type=tc_events&page=' . $page . '&action=' . $col[ 'id' ] . '&ID=' . $order_object->ID ); ?>"><?php _e( 'View', 'tc' ); ?></a>
								</td>
							<?php } elseif ( $col[ 'id' ] == 'delete' ) {
								?>
								<td>
									<a class="order_delete_link tc_delete_link" href="<?php echo wp_nonce_url( 'edit.php?post_type=tc_events&page=' . $page . '&action=' . $col[ 'id' ] . '&ID=' . $order_object->ID, 'delete_' . $order_object->ID ); ?>"><?php _e( 'Delete', 'tc' ); ?></a>
								</td>
								<?php
							} else {
								?>
								<td>
									<?php
									$post_field_type = $orders->check_field_property( $col[ 'field_name' ], 'post_field_type' );
									$field_id		 = $col[ 'id' ]; //$orders->get_field_id($col['field_name'], 'post_field_type');

									if ( isset( $post_field_type ) && $post_field_type == 'post_meta' ) {
										if ( isset( $field_id ) ) {
											echo apply_filters( 'tc_order_field_value', $order_object->ID, $order_object->$col[ 'field_name' ], $post_field_type, isset( $col[ 'field_id' ] ) ? $col[ 'field_id' ] : '', $field_id );
										} else {
											echo apply_filters( 'tc_order_field_value', $order_object->ID, $order_object->$col[ 'field_name' ], $post_field_type, $col[ 'field_id' ] );
										}
									} else {
										if ( isset( $field_id ) ) {
											echo apply_filters( 'tc_order_field_value', $order_object->ID, (isset( $order_object->$post_field_type ) ? $order_object->$post_field_type : $order_object->$col[ 'field_name' ] ), $post_field_type, $col[ 'field_name' ], $field_id );
										} else {
											echo apply_filters( 'tc_order_field_value', $order_object->ID, (isset( $order_object->$post_field_type ) ? $order_object->$post_field_type : $order_object->$col[ 'field_name' ] ), $post_field_type, $col[ 'field_name' ] );
										}
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
				if ( count( $wp_orders_search->get_results() ) == 0 ) {
					?>
					<tr>
						<td colspan="6"><div class="zero-records"><?php _e( 'No orders found.', 'tc' ) ?></div></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table><!--/widefat shadow-table-->

		<div class="tablenav">
			<div class="tablenav-pages"><?php $wp_orders_search->page_links(); ?></div>
		</div><!--/tablenav-->
	<?php } ?>
</div>
