<?php
global $action, $page, $tc;
wp_reset_vars( array( 'action', 'page' ) );

$page = $_GET[ 'page' ];

$tab = (isset( $_GET[ 'tab' ] )) ? $_GET[ 'tab' ] : '';
if ( empty( $tab ) ) {
	$tab = 'general';
}
?>

<div class="wrap tc_wrap nosubsub">
    <div class="icon32 icon32-posts-page" id="icon-options-general"><br></div>
    <h2><?php _e( 'Settings', 'tc' ); ?></h2>

	<?php
	if ( isset( $_POST[ 'submit' ] ) ) {
		?>
		<div id="message" class="updated fade"><p><?php _e( 'Settings saved successfully.', 'tc' ); ?></p></div>
		<?php
	}
	?>

	<?php
	$menus				 = array();
	$menus[ 'general' ]	 = __( 'General', 'tc' );
	$menus[ 'gateways' ] = __( 'Payment Gateways', 'tc' );
	$menus[ 'email' ]	 = __( 'E-mail', 'tc' );
	$menus[ 'api' ]		 = __( 'API Access', 'tc' );

	$menus = apply_filters( 'tc_settings_new_menus', $menus );
	?>

    <h3 class="nav-tab-wrapper">
		<?php
		foreach ( $menus as $key => $menu ) {
			?>
			<a class="nav-tab<?php
			   if ( $tab == $key )
				   echo ' nav-tab-active';
			   ?>" href="admin.php?page=<?php echo $page; ?>&amp;tab=<?php echo $key; ?>"><?php echo $menu; ?></a>
			   <?php
		   }
		   ?>
    </h3>

	<?php
	switch ( $tab ) {

		case 'general':
			$tc->show_page_tab( 'general' );
			break;


		case 'gateways':
			$tc->show_page_tab( 'gateways' );
			break;

		case 'email':
			$tc->show_page_tab( 'email' );
			break;

		case 'api':
			$tc->show_page_tab( 'api' );
			break;

		case 'permissions':
			$tc->show_page_tab( 'permissions' );
			break;

		case 'social':
			$tc->show_page_tab( 'social' );
			break;

		default: do_action( 'tc_settings_menu_' . $tab );
			break;
	}
	?>

</div>