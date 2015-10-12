<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Events' ) ) {

	class TC_Events {

		var $form_title				 = '';
		var $valid_admin_fields_type	 = array( 'text', 'textarea', 'textarea_editor', 'image', 'function' );

		function __construct() {
			$this->form_title				 = __( 'Events', 'tc' );
			$this->valid_admin_fields_type	 = apply_filters( 'tc_valid_admin_fields_type', $this->valid_admin_fields_type );
		}

		function TC_Events() {
			$this->__construct();
		}

		function get_event_fields() {

			$default_fields = array(
				array(
					'field_name'		 => 'post_title',
					'field_title'		 => __( 'Event Name', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => '',
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_title'
				),
				array(
					'field_name'		 => 'event_location',
					'field_title'		 => __( 'Event Location', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => __( 'Example: Grosvenor Square, Mayfair, London', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'event_date_time',
					'field_title'		 => __( 'Start Date & Time', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => __( 'Example: 2016-09-20 17:30 (it will be displayed in format as per WordPress settings)', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'event_end_date_time',
					'field_title'		 => __( 'End Date & Time', 'tc' ),
					'field_type'		 => 'text',
					'field_description'	 => __( 'Optional', 'tc' ),
					'table_visibility'	 => true,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'event_terms',
					'field_title'		 => __( 'Terms of Use', 'tc' ),
					'field_type'		 => 'textarea_editor',
					'field_description'	 => '',
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'event_logo',
					'field_title'		 => __( 'Event Logo', 'tc' ),
					'field_type'		 => 'image',
					'field_description'	 => __( '300 DPI recommended', 'tc' ),
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_meta'
				),
				array(
					'field_name'		 => 'sponsors_logo',
					'field_title'		 => __( 'Sponsors Logo', 'tc' ),
					'field_type'		 => 'image',
					'field_description'	 => __( '300 DPI recommended', 'tc' ),
					'table_visibility'	 => false,
					'post_field_type'	 => 'post_meta'
				),
			);

			return apply_filters( 'tc_event_fields', $default_fields );
		}

		function get_columns() {
			$fields	 = $this->get_event_fields();
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
			$fields	 = $this->get_event_fields();
			$result	 = search_array( $fields, 'field_name', $field_name );
			return $result[ 0 ][ 'post_field_type' ];
		}

		function is_valid_event_field_type( $field_type ) {
			if ( in_array( $field_type, $this->valid_admin_fields_type ) ) {
				return true;
			} else {
				return false;
			}
		}

		function add_new_event() {
			global $user_id, $post;

			if ( isset( $_POST[ 'add_new_event' ] ) ) {

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

					do_action( 'tc_after_event_post_field_type_check' );
				}

				$metas = apply_filters( 'events_metas', $metas );

				$arg = array(
					'post_author'	 => $user_id,
					'post_excerpt'	 => (isset( $excerpt ) ? $excerpt : ''),
					'post_content'	 => (isset( $content ) ? $content : ''),
					'post_status'	 => 'publish',
					'post_title'	 => (isset( $title ) ? $title : ''),
					'post_type'		 => 'tc_events',
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

//$events = new TC_Events();
?>
