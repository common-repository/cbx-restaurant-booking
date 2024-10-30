<?php
	// Prevent direct file access
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	
	
	class CBXRbooking_WPBWidget extends WPBakeryShortCode {
		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'bakery_shortcode_mapping'),12 );
		}// /end of constructor
		
		
		/**
		 * Element Mapping
		 */
		public function bakery_shortcode_mapping() {
			$booking_forms = array();
			
			$booking_forms[0] = esc_html__( 'Select Booking Form', 'cbxrbooking' );
			
			global $post;
			$args = array(
				'post_type'      => 'cbxrbooking',
				'orderby'        => 'ID',
				'order'          => 'DESC',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => '_cbxrbookingformmeta_status',
						'value'   => 1,
						'compare' => '=',
					),
				),
			);
			
			$all_posts = get_posts( $args );
			foreach ( $all_posts as $post ):
				\CBXRBookingHelper::setup_postdata( $post );
				$form_id                   = get_the_ID();
				$form_title                = get_the_title();
				//$booking_forms[ $form_id ] = esc_attr( $form_title );
				$booking_forms[ esc_attr( $form_title ) ] = $form_id;
			endforeach;
			\CBXRBookingHelper::wp_reset_postdata();
			//wp_reset_postdata();
			
			// Map the block with vc_map()
			vc_map( array(
				"name"        => esc_html__( "CBX Restaurant Booking Form", 'cbxrbooking' ),
				"description" => esc_html__( "Online restaurant bookings and reservations with branch manager",
					'cbxrbooking' ),
				"base"        => "cbxrbooking",
				"icon"        => CBXRBOOKING_ROOT_URL . 'assets/images/booking.png',
				"category"    => esc_html__( 'CBX Widgets', 'cbxrbooking' ),
				"params"      => array(
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Tour', 'cbxrbooking' ),
						'param_name'  => 'id',
						'admin_label' => true,
						'value'       => $booking_forms,
						'std'         => 0,
					),
				)
			) );
		}//end bakery_shortcode_mapping
	}// end class cbxrbooking_WPBWidget
	
	new cbxrbooking_WPBWidget();