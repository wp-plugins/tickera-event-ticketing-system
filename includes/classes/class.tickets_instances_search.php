<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Tickets_Instances_Search' ) ) {

	class TC_Tickets_Instances_Search {

		var $per_page	 = 10;
		var $args		 = array();
		var $post_type	 = 'tc_tickets_instances';
		var $page_name	 = 'tc_attendees';
		var $items_title	 = 'Attendees';

		function __construct( $search_term = '', $page_num = '', $per_page = '', $post_parent = false, $offset = true,
						$meta_key = '', $meta_value = '', $post_status = 'publish', $filter_paid = false ) {
			global $tc;

			$this->filter_paid	 = $filter_paid;
			$this->per_page		 = $per_page == '' ? tc_global_admin_per_page( $this->per_page ) : $per_page;
			$this->page_name	 = $tc->name . '_attendees';
			$this->search_term	 = $search_term;
			$this->raw_page		 = ( '' == $page_num ) ? false : (int) $page_num;
			$this->page_num		 = (int) ( '' == $page_num ) ? 1 : $page_num;

			$args = array(
				's'				 => $this->search_term,
				'posts_per_page' => $this->per_page,
				'offset'		 => $offset ? (( $this->page_num - 1 ) * $this->per_page) : '',
				'category'		 => '',
				'orderby'		 => 'post_date',
				'order'			 => 'DESC',
				'include'		 => '',
				'exclude'		 => '',
				'meta_key'		 => $meta_key,
				'meta_value'	 => $meta_value,
				'post_type'		 => $this->post_type,
				'post_mime_type' => '',
				'post_parent'	 => ($post_parent ? $post_parent : ''),
				'post_status'	 => $post_status
			);

			if ( $filter_paid ) {
				//$args[ 'post_parent__in' ] = array();//array( 2298, 1482 )
			}

			$this->args = $args;
		}

		function TC_Tickets_Instances_Search( $search_term = '', $page_num = '' ) {
			$this->__construct( $search_term, $page_num );
		}

		function get_args() {
			return $this->args;
		}

		function get_results( $count = false ) {
			global $wpdb;
			$offset = ($this->page_num - 1 ) * $this->per_page;
			if ( $this->search_term !== '' ) {
				$results = $wpdb->get_results(
				$wpdb->prepare(
				"SELECT * FROM $wpdb->posts, $wpdb->postmeta "
				. "WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id "
				. "AND $wpdb->posts.post_type = %s "
				. "AND ($wpdb->posts.post_title LIKE %s "
				. "OR ($wpdb->postmeta.meta_value LIKE %s)) "
				. "ORDER BY $wpdb->posts.post_date "
				. "DESC LIMIT %d "
				. "OFFSET %d", $this->post_type, '%' . $this->search_term . '%', '%' . $this->search_term . '%', $this->per_page, $offset )
				, OBJECT );

				if ( $count ) {
					return count( $results );
				} else {
					return $results;
				}
			} else {
				return get_posts( $this->args );
			}
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
				$pagination->target( "edit.php?post_type=tc_events&page=" . $this->page_name . "&s=" . $this->search_term );
			} else {
				$pagination->target( "edit.php?post_type=tc_events&page=" . $this->page_name );
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