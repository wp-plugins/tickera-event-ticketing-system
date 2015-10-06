<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Discount' ) ) {

	class TC_Discount {

		var $id		 = '';
		var $output	 = 'OBJECT';
		var $discount = array();
		var $details;

		function __construct( $id = '', $output = 'OBJECT' ) {
			$this->id		 = $id;
			$this->output	 = $output;
			$this->details	 = get_post( $this->id, $this->output );

			$discounts	 = new TC_Discounts();
			$fields		 = $discounts->get_discount_fields();

			foreach ( $fields as $field ) {
				if ( !isset( $this->details->{$field[ 'field_name' ]} ) ) {
					$this->details->{$field[ 'field_name' ]} = get_post_meta( $this->id, $field[ 'field_name' ], true );
				}
			}
		}

		function TC_Discount( $id = '', $output = 'OBJECT' ) {
			$this->__construct( $id, $output );
		}

		function get_discount() {
			$event = get_post_custom( $this->id, $this->output );
			return $discount;
		}

		function delete_discount( $force_delete = false ) {
			if ( $force_delete ) {
				wp_delete_post( $this->id );
			} else {
				wp_trash_post( $this->id );
			}
		}

		function get_discount_by_code( $discount_code ) {

			$post = get_page_by_title( $discount_code, OBJECT, 'tc_discounts' );

			if ( $post != NULL ) {
				return $post;
			} else {
				return false;
			}
		}

		function get_discount_id_by_name( $slug ) {

			$args = array(
				'name'			 => $slug,
				'post_type'		 => 'tc_discounts',
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