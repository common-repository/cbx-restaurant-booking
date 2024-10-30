<?php

	namespace CBXBookingElemWidget\Widgets;
	use Elementor\Widget_Base;
	use Elementor\Controls_Manager;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	/**
	 * Booking form Widget
	 */
	class CBXRBooking_ElemWidget extends \Elementor\Widget_Base {

		/**
		 * Retrieve google maps widget name.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return string Widget name.
		 */
		public function get_name() {
			return 'cbxrbooking_elemwidget';
		}

		/**
		 * Retrieve google maps widget title.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return string Widget title.
		 */
		public function get_title() {
			return esc_html__( 'CBX Booking', 'cbxrbooking' );
		}

		/**
		 * Get widget categories.
		 *
		 * Retrieve the widget categories.
		 *
		 * @since 1.0.10
		 * @access public
		 *
		 * @return array Widget categories.
		 */
		public function get_categories() {
			return array('cbxrbooking');
		}

		/**
		 * Retrieve google maps widget icon.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return string Widget icon.
		 */
		public function get_icon() {
			return 'cbxrbooking-icon';
		}

		/**
		 * Register google maps widget controls.
		 *
		 * Adds different input fields to allow the user to change and customize the widget settings.
		 *
		 * @since  1.0.0
		 * @access protected
		 */
		protected function _register_controls() {



			$this->start_controls_section(
				'section_cbxrbooking',
				array(
					'label' => esc_html__( 'Booking Form', 'cbxrbooking' ),
				)
			);

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
				$form_id    = get_the_ID();
				$form_title = get_the_title();
				$booking_forms[$form_id] = esc_attr($form_title);
			endforeach;
			\CBXRBookingHelper::wp_reset_postdata();


			$this->add_control(
				'cbxrbooking_post_id',
				array(
					'label'       => esc_html__( 'Select Booking Form', 'cbxrbooking' ),
					'type'        => Controls_Manager::SELECT,
					'placeholder' => esc_html__( 'Select Booking Form', 'cbxrbooking' ),
					'options'     => $booking_forms,
					'default'     => 0,
				)
			);

			$this->end_controls_section();
		}

		/**
		 * Render google maps widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @since  1.0.0
		 * @access protected
		 */
		protected function render() {
			if ( ! class_exists( 'CBXRBooking_Settings_API' ) ) {
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxrbooking-setting.php';
			}

			$settings_api        = new \CBXRBooking_Settings_API();


			$settings = $this->get_settings();
			$id = intval( $settings['cbxrbooking_post_id'] );
			if($id > 0 ) echo do_shortcode( '[cbxrbooking scope="shortcode" id="' . $id . '"]' );
			else echo esc_html__('Please booking form', 'cbxrbooking');
		}//end render

		/**
		 * Render google maps widget output in the editor.
		 *
		 * Written as a Backbone JavaScript template and used to generate the live preview.
		 *
		 * @since  1.0.0
		 * @access protected
		 */
		protected function _content_template() {
		}
	}//end CBXRBooking_ElemWidget
