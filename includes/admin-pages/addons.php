<?php
if ( false === ( $addons = get_transient( 'tc_addons_data' ) ) ) {
	$addons_json = wp_safe_remote_get( 'https://tickera.com/addons.json', array( 'user-agent' => 'Tickera Addons Page' ) );

	if ( !is_wp_error( $addons_json ) ) {

		$addons = json_decode( wp_remote_retrieve_body( $addons_json ) );

		if ( $addons ) {
			set_transient( 'tc_addons_data', $addons, DAY_IN_SECONDS );
		}
	}
}
?>
<div class="wrap tc_wrap">
	<h2><?php _e( 'Add-ons', 'tc' ); ?></h2>
	<div class="updated"><p><?php _e( 'NOTE: All add-ons are included for free with the Developer License', 'tc' ); ?></p></div>
	<div class="tc_addons_wrap">
		<?php
		foreach ( $addons as $addon ) {
			echo '<div class="tc_addon"><a target="_blank" href="' . $addon->link . '">';
			if ( !empty( $addon->image ) ) {
				echo '<div class="tc-addons-image"><img src="' . $addon->image . '"/></div>';
			} else {
				echo '<h3>' . $addon->title . '</h3>';
			}
			echo '<div class="tc-addon-content"><p>' . $addon->excerpt . '</p>';
			echo '</div></a></div>';
		}
		?>
	</div>
</div>