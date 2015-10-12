<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_API_Key' ) ) {

	class TC_API_Key {

		var $id		 = '';
		var $output	 = 'OBJECT';
		var $event	 = array();
		var $details;

		function __construct( $id = '', $output = 'OBJECT' ) {
			$this->id		 = $id;
			$this->output	 = $output;
			$this->details	 = get_post( $this->id, $this->output );

			$api_keys	 = new TC_API_Keys();
			$fields		 = $api_keys->get_api_keys_fields();

			foreach ( $fields as $field ) {
				if ( !isset( $this->details->{$field[ 'field_name' ]} ) ) {
					$this->details->{$field[ 'field_name' ]} = get_post_meta( $this->id, $field[ 'field_name' ], true );
				}
			}
		}

		function TC_API_Key( $id = '', $output = 'OBJECT' ) {
			$this->__construct( $id, $output );
		}

		function get_api_key() {
			$event = get_post_custom( $this->id, $this->output );
			return $event;
		}

		function delete_api_key( $force_delete = true ) {
			if ( $force_delete ) {
				wp_delete_post( $this->id );
			} else {
				wp_trash_post( $this->id );
			}
		}

		function get_api_key_id_by_name( $slug ) {

			$args = array(
				'name'			 => $slug,
				'post_type'		 => 'tc_api_keys',
				'post_status'	 => 'any',
				'posts_per_page' => 1
			);

			$post = get_posts( $args );

			if ( $post ) {
				return $post[ 0 ]->ID;
			} else {
				return false;
			}
		}

	}

}
?>