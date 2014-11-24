<?php
global $tc_email_settings, $wp_rewrite;

if ( isset( $_POST[ 'save_tc_settings' ] ) ) {
	if ( check_admin_referer( 'save_settings' ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			update_option( 'tc_email_setting', $_POST[ 'tc_email_setting' ] );
			$wp_rewrite->flush_rules();
			$message = __( 'Settings data has been successfully saved.', 'tc' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tc' );
		}
	}
}

$tc_email_settings = get_option( 'tc_email_setting', false );
?>
<div class="wrap tc_wrap">
	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo esc_attr( $message ); ?></p></div>
		<?php
	}
	?>

    <div id="poststuff" class="metabox-holder tc-settings">

        <form id="tc-email-settings" method="post" action="admin.php?page=<?php echo $_GET[ 'page' ]; ?>&tab=<?php echo $_GET[ 'tab' ]; ?>">
			<?php wp_nonce_field( 'save_settings' ); ?>

			<?php
			$email_settings	 = new TC_Settings_Email();
			$sections		 = $email_settings->get_settings_email_sections();
			?>

			<?php foreach ( $sections as $section ) {
				?>
				<div id="<?php echo $section[ 'name' ]; ?>" class="postbox">
					<h3 class='hndle'><span><?php echo $section[ 'title' ]; ?></span></h3>
					<div class="inside">
						<span class="description"><?php echo $section[ 'description' ]; ?></span>
						<table class="form-table">
							<?php
							$fields = $email_settings->get_settings_email_fields();
							foreach ( $fields as $field ) {
								if ( $field[ 'section' ] == $section[ 'name' ] ) {
									?>    
									<tr valign="top">
										<th scope="row"><label for="<?php echo $field[ 'field_name' ]; ?>"><?php echo $field[ 'field_title' ]; ?></label></th>
										<td>
											<?php do_action( 'tc_before_settings_email_field_type_check' ); ?>
											<?php
											if ( $field[ 'field_type' ] == 'function' ) {
												if ( isset( $field[ 'default_value' ] ) ) {
													eval( $field[ 'function' ] . '("' . $field[ 'field_name' ] . '", "' . $field[ 'default_value' ] . '");' );
												} else {
													eval( $field[ 'function' ] . '("' . $field[ 'field_name' ] . '");' );
												}
												?>
												<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
											<?php } else { ?>
												<input type="text" name="tc_email_setting[<?php echo esc_attr( $field[ 'field_name' ] ); ?>]" value="<?php echo (isset( $tc_email_settings[ $field[ 'field_name' ] ] ) ? $tc_email_settings[ $field[ 'field_name' ] ] : (isset( $field[ 'default_value' ] ) ? $field[ 'default_value' ] : '') ) ?>">
												<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
											<?php } ?>
											<?php do_action( 'tc_after_settings_email_field_type_check' ); ?>
										</td>
									</tr>
									<?php
								}
							}
							?>
						</table>
					</div>
				</div>
			<?php } ?>
			<?php submit_button( __( 'Save Settings' ), 'primary', 'save_tc_settings' ); ?>
        </form>
    </div>
</div>