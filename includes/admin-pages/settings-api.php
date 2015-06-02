<?php
global $tc;

$api_keys = new TC_API_Keys();

$page	 = $_GET[ 'page' ];
$tab	 = $_GET[ 'tab' ];

if ( isset( $_POST[ 'add_new_api_key' ] ) ) {
	if ( check_admin_referer( 'save_api_key' ) ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'add_api_key_cap' ) ) {
			$api_keys->add_new_api_key();
			$message = __( 'API Key data has been successfully saved.', 'tc' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tc' );
		}
	}
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) {
	$api_key = new TC_API_Key( (int) $_GET[ 'ID' ] );
	$post_id = (int) $_GET[ 'ID' ];
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'delete' ) {
	if ( !isset( $_POST[ '_wpnonce' ] ) ) {
		check_admin_referer( 'delete_' . $_GET[ 'ID' ] );
		if ( current_user_can( 'manage_options' ) || current_user_can( 'delete_api_key_cap' ) ) {
			$api_key = new TC_API_Key( (int) $_GET[ 'ID' ] );
			$api_key->delete_api_key();
			$message = __( 'API Key has been successfully deleted.', 'tc' );
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
	$api_keys_search = $_GET[ 's' ];
} else {
	$api_keys_search = '';
}

$wp_api_keys_search	 = new TC_API_Keys_Search( $api_keys_search, $page_num );
$fields				 = $api_keys->get_api_keys_fields();
$columns			 = $api_keys->get_columns();
?>
<div class="wrap tc_wrap">
    <h2><?php if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' ) { ?><a href="admin.php?page=<?php echo $_GET[ 'page' ] . '&tab=' . $_GET[ 'tab' ]; ?>" class="add-new-h2"><?php _e( 'Add New', 'tc' ); ?></a><?php } ?></h2>

	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo esc_attr( $message ); ?></p></div>
		<?php
	}
	?>

    <form action="" method="post" enctype = "multipart/form-data">
		<?php wp_nonce_field( 'save_api_key' ); ?>
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
					<?php if ( $api_keys->is_valid_api_key_field_type( $field[ 'field_type' ] ) ) { ?>    
						<tr valign="top">

							<th scope="row"><label for="<?php echo $field[ 'field_name' ]; ?>"><?php echo $field[ 'field_title' ]; ?></label></th>

							<td>
								<?php do_action( 'tc_before_api_keys_field_type_check' ); ?>
								<?php
								if ( $field[ 'field_type' ] == 'function' ) {
									eval( $field[ 'function' ] . '("' . $field[ 'field_name' ] . '"' . (isset( $post_id ) ? ',' . $post_id : '') . ');' );
									?>
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'text' ) { ?>
									<input type="text" class="regular-<?php echo $field[ 'field_type' ]; ?>" value="<?php
									if ( isset( $api_key ) ) {
										if ( $field[ 'post_field_type' ] == 'post_meta' ) {
											echo esc_attr( isset( $api_key->details->{$field[ 'field_name' ]} ) ? $api_key->details->{$field[ 'field_name' ]} : ''  );
										} else {
											echo esc_attr( $api_key->details->{$field[ 'post_field_type' ]} );
										}
									} else {
										echo esc_attr( isset( $field[ 'default_value' ] ) ? $field[ 'default_value' ] : ''  );
									}
									?>" id="<?php echo $field[ 'field_name' ]; ?>" name="<?php echo $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>">
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'textarea' ) { ?>
									<textarea class="regular-<?php echo $field[ 'field_type' ]; ?>" id="<?php echo $field[ 'field_name' ]; ?>" name="<?php echo $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>"><?php
										if ( isset( $api_key ) ) {
											if ( $field[ 'post_field_type' ] == 'post_meta' ) {
												echo esc_textarea( isset( $api_key->details->{$field[ 'field_name' ]} ) ? $api_key->details->{$field[ 'field_name' ]} : ''  );
											} else {
												echo esc_textarea( $api_key->details->{$field[ 'post_field_type' ]} );
											}
										}
										?></textarea>
									<br /><?php echo $field[ 'field_description' ]; ?>
								<?php } ?>
								<?php do_action( 'tc_after_api_keys_field_type_check' ); ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
            </tbody>
        </table>

		<?php submit_button( (isset( $_REQUEST[ 'action' ] ) && $_REQUEST[ 'action' ] == 'edit' ? __( 'Update', 'tc' ) : __( 'Add New', 'tc' ) ), 'primary', 'add_new_api_key', true ); ?>

    </form>



    <div class="tablenav">
        <div class="alignright actions new-actions">
            <form method="get" action="?page=<?php echo esc_attr( $page ); ?>" class="search-form">
                <p class="search-box">
                    <input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>' />
                    <input type='hidden' name='tab' value='<?php echo esc_attr( $tab ); ?>' />
                    <label class="screen-reader-text"><?php _e( 'Search API Keys', 'tc' ); ?>:</label>
                    <input type="text" value="<?php echo esc_attr( $api_keys_search ); ?>" name="s">
                    <input type="submit" class="button" value="<?php _e( 'Search API Keys', 'tc' ); ?>">
                </p>
            </form>
        </div><!--/alignright-->

    </div><!--/tablenav-->

    <table cellspacing="0" class="widefat shadow-table">
        <thead>
            <tr>
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

			foreach ( $wp_api_keys_search->get_results() as $api_key ) {

				$api_key_obj	 = new TC_API_Key( $api_key->ID );
				$api_key_object	 = apply_filters( 'tc_api_key_object_details', $api_key_obj->details );

				$style	 = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
				?>
				<tr id='user-<?php echo $api_key_object->ID; ?>' <?php echo $style; ?>>
					<?php
					$n		 = 1;
					foreach ( $columns as $key => $col ) {
						if ( $key == 'edit' ) {
							?>
							<td>                    
								<a class="api_keys_edit_link" href="<?php echo admin_url( 'admin.php?page=' . $tc->name . '_settings&tab=api&action=' . $key . '&ID=' . $api_key_object->ID ); ?>"><?php _e( 'Edit', 'tc' ); ?></a>
							</td>
						<?php } elseif ( $key == 'delete' ) {
							?>
							<td>
								<a class="api_keys_edit_link tc_delete_link" href="<?php echo wp_nonce_url( 'admin.php?page=' . $tc->name . '_settings&tab=api&action=' . $key . '&ID=' . $api_key_object->ID, 'delete_' . $api_key_object->ID ); ?>"><?php _e( 'Delete', 'tc' ); ?></a>
							</td>
							<?php
						} else {
							?>
							<td>
								<?php
								$post_field_type = $api_keys->check_field_property( $key, 'post_field_type' );

								if ( isset( $post_field_type ) && $post_field_type == 'post_meta' ) {
									echo apply_filters( 'tc_api_key_field_value', $api_key_object->$key, $post_field_type, $key );
								} else {
									echo apply_filters( 'tc_api_key_field_value', (isset( $api_key_object->$post_field_type ) ? $api_key_object->$post_field_type : $api_key_object->$key ), $post_field_type, $key );
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
			if ( count( $wp_api_keys_search->get_results() ) == 0 ) {
				?>
				<tr>
					<td colspan="6"><div class="zero-records"><?php _e( 'No API Keys found.', 'tc' ) ?></div></td>
				</tr>
				<?php
			}
			?>
        </tbody>
    </table><!--/widefat shadow-table-->

    <div class="tablenav">
        <div class="tablenav-pages"><?php $wp_api_keys_search->page_links(); ?></div>
    </div><!--/tablenav-->

</div>