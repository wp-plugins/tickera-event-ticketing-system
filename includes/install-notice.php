<?php
/**
 * Install Notice
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div id="message" class="updated tickera-install-notice">
	<p><?php printf( __( '<strong>Welcome to %s</strong> &#8211; Install pages required by the plugin automatically.', 'tc' ), $tc->title ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'install_tickera_pages', 'true', admin_url( 'admin.php?page=tc_settings' ) ) ); ?>" class="button-primary"><?php printf( __( 'Install %s Pages', 'tc' ), $tc->title ); ?></a></p>
</div>