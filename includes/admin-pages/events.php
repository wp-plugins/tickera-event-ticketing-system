<?php
global $tc;
$events = new TC_Events();

$page = $_GET[ 'page' ];

if ( isset( $_GET[ 'restore' ] ) ) {
	$event = new TC_Event();
	$event->restore_event( (int) $_GET[ 'restore' ] );
}

if ( isset( $_POST[ 'add_new_event' ] ) ) {
	if ( check_admin_referer( 'save_event' ) ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'add_event_cap' ) ) {
			$events->add_new_event();
			$message = __( 'Event data has been saved successfully.', 'tc' );
		} else {
			$message = __( 'You do not have required persmissions for this action.', 'tc' );
		}
	}
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) {
	$event	 = new TC_Event( $_GET[ 'ID' ] );
	$post_id = (int) $_GET[ 'ID' ];
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'delete' ) {
	if ( !isset( $_POST[ '_wpnonce' ] ) ) {
		check_admin_referer( 'delete_' . $_GET[ 'ID' ] );
		if ( current_user_can( 'manage_options' ) || current_user_can( 'delete_event_cap' ) ) {
			$event	 = new TC_Event( $_GET[ 'ID' ] );
			$event->delete_event();
			$message = __( 'Event has been successfully deleted.', 'tc' );
		} else {
			$message = __( 'You do not have required persmissions for this action.', 'tc' );
		}
	}
}

if ( isset( $_GET[ 'page_num' ] ) ) {
	$page_num = (int) $_GET[ 'page_num' ];
} else {
	$page_num = 1;
}

if ( isset( $_GET[ 's' ] ) ) {
	$eventssearch = $_GET[ 's' ];
} else {
	$eventssearch = '';
}

$wp_events_search	 = new TC_Events_Search( $eventssearch, $page_num, '', isset( $_GET[ 'post_status' ] ) ? $_GET[ 'post_status' ] : 'any'  );
$fields				 = $events->get_event_fields();
$columns			 = $events->get_columns();
?>
<div class="wrap tc_wrap">
    <h2><?php echo $events->form_title; ?><?php if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) { ?><a href="admin.php?page=<?php echo $_GET[ 'page' ]; ?>" class="add-new-h2"><?php _e( 'Add New', 'tc' ); ?></a><?php } ?></h2>

	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo esc_attr( $message ); ?></p></div>
		<?php
	}
	?>

    <form action="" method="post" enctype = "multipart/form-data">
		<?php wp_nonce_field( 'save_event' ); ?>
		<?php
		if ( isset( $post_id ) ) {
			?>
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
			<?php
		}
		?>
        <table class="event-table">
            <tbody>
				<?php foreach ( $fields as $field ) { ?>
					<?php if ( $events->is_valid_event_field_type( $field[ 'field_type' ] ) && !isset( $field[ 'table_edit_invisible' ] ) ) { ?>    
						<tr valign="top">

							<th scope="row"><label for="<?php echo $field[ 'field_name' ]; ?>"><?php echo $field[ 'field_title' ]; ?></label></th>

							<td>
								<?php do_action( 'tc_before_events_field_type_check' ); ?>
								<?php
								if ( $field[ 'field_type' ] == 'function' ) {
									eval( $field[ 'function' ] . '("' . $field[ 'field_name' ] . '"' . (isset( $post_id ) ? ',' . $post_id : '') . ');' );
									?>
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'text' ) { ?>
									<input type="text" class="regular-<?php echo $field[ 'field_type' ]; ?>" value="<?php
									if ( isset( $event ) ) {
										if ( $field[ 'post_field_type' ] == 'post_meta' ) {
											echo esc_attr( isset( $event->details->{$field[ 'field_name' ]} ) ? $event->details->{$field[ 'field_name' ]} : ''  );
										} else {
											echo esc_attr( $event->details->{$field[ 'post_field_type' ]} );
										}
									}
									?>" id="<?php echo $field[ 'field_name' ]; ?>" name="<?php echo $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>">
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'textarea' ) { ?>
									<textarea class="regular-<?php echo $field[ 'field_type' ]; ?>" id="<?php echo $field[ 'field_name' ]; ?>" name="<?php echo $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>"><?php
										if ( isset( $event ) ) {
											if ( $field[ 'post_field_type' ] == 'post_meta' ) {
												echo esc_textarea( isset( $event->details->{$field[ 'field_name' ]} ) ? $event->details->{$field[ 'field_name' ]} : ''  );
											} else {
												echo esc_textarea( $event->details->{$field[ 'post_field_type' ]} );
											}
										}
										?></textarea>
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'textarea_editor' ) { ?>
									<?php
									if ( isset( $event ) ) {
										if ( $field[ 'post_field_type' ] == 'post_meta' ) {
											$editor_content = ( isset( $event->details->{$field[ 'field_name' ]} ) ? $event->details->{$field[ 'field_name' ]} : '' );
										} else {
											$editor_content = ( $event->details->{$field[ 'post_field_type' ]} );
										}
									} else {
										$editor_content = '';
									}
									wp_editor( html_entity_decode( stripcslashes( $editor_content ) ), $field[ 'field_name' ], array( 'textarea_name' => $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ], 'textarea_rows' => 5 ) );
									?>
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>


								<?php
								if ( $field[ 'field_type' ] == 'image' ) {
									?>
									<div class="file_url_holder">
										<label>
											<input class="file_url" type="text" size="36" name="<?php echo $field[ 'field_name' ] . '_file_url_' . $field[ 'post_field_type' ]; ?>" value="<?php
											if ( isset( $event ) ) {
												echo esc_attr( isset( $event->details->{$field[ 'field_name' ] . '_file_url'} ) ? $event->details->{$field[ 'field_name' ] . '_file_url'} : ''  );
											}
											?>" />
											<input class="file_url_button button-secondary" type="button" value="<?php _e( 'Browse', 'tc' ); ?>" />
											<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
										</label>
									</div>
								<?php } ?>
								<?php do_action( 'tc_after_events_field_type_check' ); ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
            </tbody>
        </table>

		<?php submit_button( (isset( $_REQUEST[ 'action' ] ) && $_REQUEST[ 'action' ] == 'edit' ? __( 'Update', 'tc' ) : __( 'Add New', 'tc' ) ), 'primary', 'add_new_event', true ); ?>

    </form>



    <div class="tablenav">
        <div class="alignright actions new-actions">
            <form method="get" action="?page=<?php echo esc_attr( $page ); ?>" class="search-form">
                <p class="search-box">
                    <input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>' />
                    <label class="screen-reader-text"><?php _e( 'Search Events', 'tc' ); ?>:</label>
                    <input type="text" value="<?php echo esc_attr( $eventssearch ); ?>" name="s">
                    <input type="submit" class="button" value="<?php _e( 'Search Events', 'tc' ); ?>">
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

			foreach ( $wp_events_search->get_results() as $event ) {

				$event_obj		 = new TC_Event( $event->ID );
				$event_object	 = apply_filters( 'tc_event_object_details', $event_obj->details );

				$style	 = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
				?>
				<tr id='user-<?php echo $event_object->ID; ?>' <?php echo $style; ?>>
					<!--<th scope='row' class='check-column'>
						<input type='checkbox' name='events[]' id='user_<?php echo $event_object->$key; ?>' class='' value='<?php echo $event_object->$key; ?>' />
					</th>-->
					<?php
					$n		 = 1;
					foreach ( $columns as $key => $col ) {
						if ( $key == 'edit' ) {
							?>
							<td>                    
								<a class="events_edit_link" href="<?php echo admin_url( 'admin.php?page=' . $page . '&action=' . $key . '&ID=' . $event_object->ID ); ?>"><?php _e( 'Edit', 'tc' ); ?></a>
							</td>
						<?php } elseif ( $key == 'delete' ) {
							?>
							<td>
								<a class="events_edit_link tc_delete_link" href="<?php echo wp_nonce_url( 'admin.php?page=' . $page . '&action=' . $key . '&ID=' . $event_object->ID, 'delete_' . $event_object->ID ); ?>"><?php _e( 'Delete', 'tc' ); ?></a>
							</td>
							<?php
						} else {
							?>
							<td>
								<?php echo apply_filters( 'tc_event_field_value', $event_object->$key ); ?>
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
			if ( count( $wp_events_search->get_results() ) == 0 ) {
				?>
				<tr>
					<td colspan="6"><div class="zero-records"><?php _e( 'No events found.', 'tc' ) ?></div></td>
				</tr>
				<?php
			}
			?>
        </tbody>
    </table><!--/widefat shadow-table-->

    <div class="tablenav">
        <div class="tablenav-pages"><?php $wp_events_search->page_links(); ?></div>
    </div><!--/tablenav-->

</div>