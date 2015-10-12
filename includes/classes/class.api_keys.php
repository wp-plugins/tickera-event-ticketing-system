<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_API_Keys' ) ) {

	class TC_API_Keys {

		var $form_title				 = '';
		var $valid_admin_fields_type	 = array( 'text', 'textarea', 'image', 'function' );

		function __construct() {
			$this->form_title				 = __( 'API Keys', 'tc' );
			$this->valid_admin_fields_type	 = apply_filters( 'tc_valid_admin_fields_type', $this->valid_admin_fields_type );
		}

		function TC_API_Keys() {
			$this->__construct();
		}

		function get_rand_api_key() {
			$tuid = '';

			$uid = uniqid( "", true );

			$data = '';
			$data .= isset( $_SERVER[ 'REQUEST_TIME' ] ) ? $_SERVER[ 'REQUEST_TIME' ] : '';
			$data .= isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ? $_SERVER[ 'HTTP_USER_AGENT' ] : '';
			$data .= isset( $_SERVER[ 'LOCAL_ADDR' ] ) ? $_SERVER[ 'LOCAL_ADDR' ] : '';
			$data .= isset( $_SERVER[ 'LOCAL_PORT' ] ) ? $_SERVER[ 'LOCAL_PORT' ] : '';
			$data .= isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? $_SERVER[ 'REMOTE_ADDR' ] : '';
			$data .= isset( $_SERVER[ 'REMOTE_PORT' ] ) ? $_SERVER[ 'REMOTE_PORT' ] : '';

			$tuid = substr( strtoupper( hash( 'ripemd128', $uid . md5( $data ) ) ), 0, apply_filters( 'tc_rand_api_key_length', 8 ) );

			return $tuid;
		}

		function get_api_keys_fields() {
			global $tc;
			$default_fields = array(
				array(
					'field_name'		 => 'event_name',
					'field_title'		 => __( 'Event', 'tc' ),
					'field_type'		 => 'function',
					'function'			 => 'tc_get_api_keys_events',
					'field_description'	 => '',
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'api_key_name',
					'field_title'		 => __( 'API Key Name', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => __( 'For example: iPhone 1, South Entrance, John Smith etc. This name will be linked with every check-in operation.', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta',
				),
				array(
					'field_name'		 => 'api_key',
					'field_title'		 => __( 'API Key', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => sprintf( __( 'This is the API Key you will have to enter in your %3$s %1$s or %2$s mobile application', 'tc' ), '<a href="https://itunes.apple.com/us/app/ticket-checkin/id958838933" target="_blank">' . __( 'iPhone', 'tc' ) . '</a>', '<a href="https://play.google.com/store/apps/details?id=com.tickera.tickeraapp" target="_blank">' . __( 'Android', 'tc' ) . '</a>', $tc->title ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta',
					'default_value'		 => $this->get_rand_api_key(),
				),
				array(
					'field_name'		 => 'api_username',
					'field_title'		 => __( 'Username', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => __( 'This is the WordPress user who will have access to the API key within the WP Admin. It is useful if you want to give API Key which will be available to a user with "Staff" role. If you leave it empty, API key will be available for administrators only.', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta',
					'default_value'		 => '',
				),
			);

			return apply_filters( 'tc_api_keys_fields', $default_fields );
		}

		function get_columns() {
			$fields	 = $this->get_api_keys_fields();
			$results = search_array( $fields, 'table_visibility', true );

			$columns = array();

			$columns[ 'ID' ] = __( 'ID', 'tc' );

			foreach ( $results as $result ) {
				$columns[ $result[ 'field_name' ] ] = $result[ 'field_title' ];
			}

			$columns[ 'edit' ]	 = __( 'Edit', 'tc' );
			$columns[ 'delete' ] = __( 'Delete', 'tc' );

			return $columns;
		}

		function check_field_property( $field_name, $property ) {
			$fields	 = $this->get_api_keys_fields();
			$result	 = search_array( $fields, 'field_name', $field_name );
			return isset( $result[ 0 ][ 'post_field_type' ] ) ? $result[ 0 ][ 'post_field_type' ] : '';
		}

		function is_valid_api_key_field_type( $field_type ) {
			if ( in_array( $field_type, $this->valid_admin_fields_type ) ) {
				return true;
			} else {
				return false;
			}
		}

		function get_api_keys() {
			
		}

		function add_new_api_key() {
			global $user_id, $post;

			if ( isset( $_POST[ 'add_new_api_key' ] ) ) {

				$metas				 = array();
				$post_field_types	 = tc_post_fields();

				foreach ( $_POST as $field_name => $field_value ) {

					if ( preg_match( '/_post_title/', $field_name ) ) {
						$title = $field_value;
					}

					if ( preg_match( '/_post_excerpt/', $field_name ) ) {
						$excerpt = $field_value;
					}

					if ( preg_match( '/_post_content/', $field_name ) ) {
						$content = $field_value;
					}

					if ( preg_match( '/_post_meta/', $field_name ) ) {
						$metas[ str_replace( '_post_meta', '', $field_name ) ] = $field_value;
					}

					do_action( 'tc_after_api_key_post_field_type_check' );
				}

				$metas = apply_filters( 'tc_api_keys_metas', $metas );

				$arg = array(
					'post_author'	 => $user_id,
					'post_excerpt'	 => (isset( $excerpt ) ? $excerpt : ''),
					'post_content'	 => (isset( $content ) ? $content : ''),
					'post_status'	 => 'publish',
					'post_title'	 => (isset( $title ) ? $title : ''),
					'post_type'		 => 'tc_api_keys',
				);

				if ( isset( $_POST[ 'post_id' ] ) ) {
					$arg[ 'ID' ] = $_POST[ 'post_id' ]; //for edit 
				}

				$post_id = @wp_insert_post( $arg, true );

				//Update post meta
				if ( $post_id !== 0 ) {
					if ( isset( $metas ) ) {
						foreach ( $metas as $key => $value ) {
							update_post_meta( $post_id, $key, $value );
						}
					}
				}

				return $post_id;
			}
		}

	}

}
?>
