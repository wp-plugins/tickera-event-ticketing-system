<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Discounts_Search' ) ) {

	class TC_Discounts_Search {

		var $per_page	 = 10;
		var $args		 = array();
		var $post_type	 = 'tc_discounts';
		var $page_name	 = 'tc_discounts';
		var $items_title	 = 'Discounts';

		function __construct( $search_term = '', $page_num = '' ) {
			global $tc;

			$this->page_name	 = $tc->name . '_discounts';
			$this->search_term	 = $search_term;
			$this->raw_page		 = ( '' == $page_num ) ? false : (int) $page_num;
			$this->page_num		 = (int) ( '' == $page_num ) ? 1 : $page_num;

			$args = array(
				's'				 => $this->search_term,
				'posts_per_page' => $this->per_page,
				'offset'		 => ( $this->page_num - 1 ) * $this->per_page,
				'category'		 => '',
				'orderby'		 => 'post_date',
				'order'			 => 'DESC',
				'include'		 => '',
				'exclude'		 => '',
				'meta_key'		 => '',
				'meta_value'	 => '',
				'post_type'		 => $this->post_type,
				'post_mime_type' => '',
				'post_parent'	 => '',
				'post_status'	 => 'any'
			);

			$this->args = $args;
		}

		function TC_Events_Search( $search_term = '', $page_num = '' ) {
			$this->__construct( $search_term, $page_num );
		}

		function get_args() {
			return $this->args;
		}

		function get_results() {
			return get_posts( $this->args );
		}

		function get_count_of_all() {
			$args = array(
				's'				 => $this->search_term,
				'posts_per_page' => -1,
				'category'		 => '',
				'orderby'		 => 'post_date',
				'order'			 => 'DESC',
				'include'		 => '',
				'exclude'		 => '',
				'meta_key'		 => '',
				'meta_value'	 => '',
				'post_type'		 => $this->post_type,
				'post_mime_type' => '',
				'post_parent'	 => '',
				'post_status'	 => 'any'
			);
			return count( get_posts( $args ) );
		}

		function page_links() {
			$pagination					 = new TC_Pagination();
			$pagination->Items( $this->get_count_of_all() );
			$pagination->limit( $this->per_page );
			$pagination->parameterName	 = 'page_num';
			if ( $this->search_term != '' ) {
				$pagination->target( "admin.php?page=" . $this->page_name . "&s=" . $this->search_term );
			} else {
				$pagination->target( "admin.php?page=" . $this->page_name );
			}
			$pagination->currentPage( $this->page_num );
			$pagination->nextIcon( '&#9658;' );
			$pagination->prevIcon( '&#9668;' );
			$pagination->items_title = $this->items_title;
			$pagination->show();
		}

	}

}
?>