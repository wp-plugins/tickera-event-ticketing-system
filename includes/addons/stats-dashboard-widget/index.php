<?php
/*
  Addon Name: Delete Pending Orders
  Description: Delete pending orders (which are not paid for 12 hours or more). Note: all pending orders will be deleted made via all payment gateways except Free Orders and Offline Payments
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Stats_Dashboard_Widget' ) ) {

	class TC_Stats_Dashboard_Widget {

		var $version		 = '1.0';
		var $title		 = 'TC_Stats_Dashboard_Widget';
		var $name		 = 'tc';
		var $dir_name	 = 'stats-dashboard-widget';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {
			$this->title = __( 'Ticketing Store at a Glance', 'tc' );
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_styles_scripts' ) );
			add_action( 'wp_dashboard_setup', array( &$this, 'add_tc_dashboard_widgets' ) );
		}

		function add_tc_dashboard_widgets() {
			if ( !current_user_can( apply_filters( 'tc_can_view_dashboard_widgets_capability', 'manage_options' ) ) ) {
				return;
			}
			wp_add_dashboard_widget( 'tc_store_report', $this->title, array( &$this, 'tc_store_report_display' ) );
		}

		function enqueue_styles_scripts() {
			global $pagenow, $tc;

			if ( !empty( $pagenow ) && ('index.php' === $pagenow) ) {
				wp_enqueue_style( 'tc-dashboard-widgets', $tc->plugin_url . 'includes/addons/' . $this->dir_name . '/css/dashboard-widgets.css', false, $tc->version );
				wp_enqueue_style( 'tc-dashboard-widgets-font-awesome', $tc->plugin_url . '/css/font-awesome.min.css', array(), $tc->version );
				wp_enqueue_script( 'tc-dashboard-widgets-peity', $tc->plugin_url . '/includes/addons/' . $this->dir_name . '/js/jquery.peity.min.js', array( 'jquery' ), $tc->version );
				wp_enqueue_script( 'tc-dashboard-widgets', $tc->plugin_url . '/includes/addons/' . $this->dir_name . '/js/dashboard-widgets.js', array( 'jquery' ), $tc->version );
			}
		}

		function create_date_range_array( $strDateFrom, $strDateTo ) {
			// takes two dates formatted as YYYY-MM-DD and creates an
			// inclusive array of the dates between the from and to dates.
			// could test validity of dates here but I'm already doing
			// that in the main script

			$aryRange = array();

			$iDateFrom	 = mktime( 1, 0, 0, substr( $strDateFrom, 5, 2 ), substr( $strDateFrom, 8, 2 ), substr( $strDateFrom, 0, 4 ) );
			$iDateTo	 = mktime( 1, 0, 0, substr( $strDateTo, 5, 2 ), substr( $strDateTo, 8, 2 ), substr( $strDateTo, 0, 4 ) );

			if ( $iDateTo >= $iDateFrom ) {
				//array_push( $aryRange, date( 'Y-m-d', $iDateFrom ) ); // first entry
				$aryRange[ date( 'Y-m-d', $iDateFrom ) ] = 0;
				while ( $iDateFrom < $iDateTo ) {
					$iDateFrom+=86400; // add 24 hours
					//array_push( $aryRange, date( 'Y-m-d', $iDateFrom ) );
					$aryRange[ date( 'Y-m-d', $iDateFrom ) ] = 0;
				}
			}
			return $aryRange;
		}

		function tc_store_report_display() {
			global $tc;

			$days_range			 = 30;
			$days				 = $days_range * -1;
			$paid_orders_count	 = 0;
			$total_revenue		 = 0;
			$todays_revenue		 = 0;
			$paid_orders_search	 = new TC_Orders_Search( '', '', '', 'order_paid', $days, '>' );
			$paid_orders		 = array();

			$range_dates_earnings	 = $this->create_date_range_array( date( "Y-m-d" ), date( 'Y-m-d', strtotime( '+' . ($days_range - 1) . ' days' ) ) );
			$count_of_tickets		 = 0;

			foreach ( $paid_orders_search->get_results() as $order ) {
				$order_object = new TC_Order( $order->ID );

				$args = array(
					'posts_per_page' => -1,
					'post_parent'	 => $order->ID,
					'post_type'		 => 'tc_tickets_instances',
					'post_status'	 => 'publish',
				);

				$tickets = get_posts( $args );

				$count_of_tickets = $count_of_tickets + count( $tickets );

				$total_revenue	 = $total_revenue + $order_object->details->tc_payment_info[ 'total' ];
				$paid_orders[]	 = $order_object->details->tc_payment_info[ 'total' ];

				$createDate	 = new DateTime( $order_object->details->post_date );
				$strip_date	 = $createDate->format( 'Y-m-d' );

				$range_dates_earnings[ $strip_date ] = $range_dates_earnings[ $strip_date ] + $order_object->details->tc_payment_info[ 'total' ];

				$paid_orders_count++;
			}

			$todays_revenue	 = $range_dates_earnings[ date( "Y-m-d" ) ];
			$total_revenue	 = round( $total_revenue, 2 );

			$pending_orders_count	 = 0;
			$pending_orders_search	 = new TC_Orders_Search( '', '', '', 'order_received', $days, '>' );

			foreach ( $pending_orders_search->get_results() as $order ) {
				$order_object = new TC_Order( $order->ID );
				$pending_orders_count++;
			}
			?>
			<ul class="tc-status-list">
				<li class="sales-this-month">
					<a>
						<i class="fa fa-money tc-icon tc-icon-dashboard-sales"></i> 
						<strong><span class="amount"><?php echo $tc->get_cart_currency_and_format( $total_revenue ); ?></span></strong>
						<span class="tc-dashboard-widget-subtitle"><?php printf( _n( 'last %d day earnings', 'last %d days earnings', $days_range, 'tc' ), $days_range ); ?></span>
						<span class = "tc-bar"><?php
							$vals = '';
							foreach ( $range_dates_earnings as $key => $val ) {
								$vals = $vals . $val . '|';
							} echo rtrim( $vals, "|" );
							?>
						</span>
					</a>

				</li>


				<li class="todays-earnings">
					<a>
						<i class="fa fa-money tc-icon tc-icon-dashboard-todays-earnings"></i> 
						<strong><?php echo $tc->get_cart_currency_and_format( $todays_revenue ); ?></strong>
						<span class="tc-dashboard-widget-subtitle"><?php _e( 'today\'s earnings', 'tc' ); ?></span>
					</a>
				</li>
				<li class="sold-tickets">
					<a>
						<i class="fa fa-ticket tc-icon tc-icon-dashboard-sold"></i> 
						<strong><?php printf( _n( '%d ticket sold', '%d tickets sold', $count_of_tickets, 'tc' ), $count_of_tickets ); ?></strong>
						<span class="tc-dashboard-widget-subtitle"><?php printf( _n( 'in the last %d day', 'in the last %d days', $days_range, 'tc' ), $days_range ); ?></span>
					</a>
				</li>
				<li class="completed-orders">
					<a>
						<i class="fa fa-shopping-cart tc-icon tc-icon-dashboard-completed"></i> 
						<strong><?php printf( _n( '%d order completed', '%d orders completed', $paid_orders_count, 'tc' ), $paid_orders_count ); ?></strong>
						<span class="tc-dashboard-widget-subtitle"><?php printf( _n( 'in the last %d day', 'in the last %d days', $days_range, 'tc' ), $days_range ); ?></span>
					</a>
				</li>
				<li class="pending-orders">
					<a>
						<i class="fa fa-shopping-cart tc-icon tc-icon-dashboard-pending"></i> 
						<strong><?php printf( _n( '%d pending order', '%d pending orders', $pending_orders_count, 'tc' ), $pending_orders_count ); ?></strong>
						<span class="tc-dashboard-widget-subtitle"><?php printf( _n( 'in the last %d day', 'in the last %d days', $days_range, 'tc' ), $days_range ); ?></span>
					</a>
				</li>

			</ul>
			<?php
		}

	}

}

if ( is_admin() ) {
	$tc_stats_dashboard_widget = new TC_Stats_Dashboard_Widget();
}
?>