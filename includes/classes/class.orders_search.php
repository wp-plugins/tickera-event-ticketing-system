<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Orders_Search' ) ) {

	class TC_Orders_Search {

		var $per_page	 = 10;
		var $args		 = array();
		var $post_type	 = 'tc_orders';
		var $page_name	 = 'tc_orders';
		var $items_title	 = 'Orders';

		function __construct( $search_term = '', $page_num = '', $per_page = '', $post_status = array( 'any' ), $period = '',
						$period_compare = '=' ) {
			global $tc;

			$this->per_page			 = $per_page == '' ? tc_global_admin_per_page( $this->per_page ) : $per_page;
			$this->page_name		 = $tc->name . '_orders';
			$this->search_term		 = $search_term;
			$this->raw_page			 = ( '' == $page_num ) ? false : (int) $page_num;
			$this->page_num			 = (int) ( '' == $page_num ) ? 1 : $page_num;
			//$this->per_page = (int) ( '' == $per_page ) ? 10 : $per_page;
			$this->post_status		 = $post_status;
			$this->period			 = ( '' == $period ) ? '' : $period;
			$this->period_compare	 = $period_compare;

			$args = array(
				's'				 => $this->search_term,
				'posts_per_page' => $this->per_page,
				'offset'		 => ( $this->page_num - 1 ) * $this->per_page,
				'orderby'		 => 'post_date',
				'order'			 => 'DESC',
				'post_type'		 => $this->post_type,
				'post_status'	 => $this->post_status
			);

			if ( $per_page > 0 ) {
				$args[ 'posts_per_page' ]	 = $this->per_page;
				$args[ 'offset' ]			 = ( $this->page_num - 1 ) * $this->per_page;
			}

			$this->args = $args;
		}

		function filter_where( $where = '' ) {
			if ( is_array( $this->post_status ) ) {
				$post_status = ''; //
			} else {
				$post_status = $this->post_status;
			}
			$where .= " AND post_date " . $this->period_compare . " '" . date( 'Y-m-d', strtotime( $this->period . ' days' ) ) . "' AND post_status = '" . $post_status . "'";
			return $where;
		}

		function TC_Orders_Search( $search_term = '', $page_num = '', $per_page = '', $post_status = array( 'any' ),
							 $period = '', $period_compare = '=' ) {
			$this->__construct( $search_term	 = '', $page_num		 = '', $per_page		 = '', $post_status	 = array( 'any' ), $period			 = '', $period_compare	 = '=' );
		}

		function get_args() {
			return $this->args;
		}

		function get_results( $count = false ) {
			global $wpdb;

			$offset = ($this->page_num - 1 ) * $this->per_page;

			if ( $this->search_term !== '' ) {

				if ( is_array( $this->post_status ) ) {
					$this->post_status = $this->post_status[ 0 ];
				}

				if ( $this->post_status !== 'any' ) {
					$post_status_query = "AND $wpdb->posts.post_status = '" . $this->post_status . "' ";
				} else {
					$post_status_query = '';
				}

				$results = $wpdb->get_results(
				$wpdb->prepare(
				"SELECT * FROM $wpdb->posts, $wpdb->postmeta "
				. "WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id "
				. $post_status_query
				. "AND $wpdb->posts.post_type = %s "
				. "AND ($wpdb->posts.post_title LIKE %s "
				. "OR ($wpdb->postmeta.meta_value LIKE %s)) "
				. "GROUP BY $wpdb->posts.post_title "
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

				if ( $this->period !== '' ) {
					add_filter( 'posts_where', array( &$this, 'filter_where' ) );
				}

				return query_posts( $this->args );
				remove_filter( 'posts_where', array( &$this, 'filter_where' ) );
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
				'post_status'	 => isset( $_GET[ 'post_status' ] ) ? $_GET[ 'post_status' ] : 'any'
			);
			return count( get_posts( $args ) );
		}

		function page_links() {
			$current_post_status = isset( $_GET[ 'post_status' ] ) ? $_GET[ 'post_status' ] : 'any';

			$pagination					 = new TC_Pagination();
			$pagination->Items( $this->get_count_of_all() );
			$pagination->limit( $this->per_page );
			$pagination->parameterName	 = 'page_num';
			if ( $this->search_term != '' ) {
				$pagination->target( "admin.php?page=" . $this->page_name . "&s=" . $this->search_term );
			} else {
				$pagination->target( "admin.php?page=" . $this->page_name . "&post_status=" . $current_post_status );
			}
			$pagination->currentPage( $this->page_num );
			$pagination->nextIcon( ' & #9658;' );
			$pagination->prevIcon( '&#9668;' );
			$pagination->items_title = $this->items_title;
			$pagination->show();
		}

	}

}
?>