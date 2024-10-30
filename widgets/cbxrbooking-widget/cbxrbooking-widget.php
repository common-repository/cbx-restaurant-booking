<?php

	// Prevent direct file access
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class CBXRBookingWidget extends WP_Widget {

		/**
		 *
		 * Unique identifier for your widget.
		 *
		 *
		 * The variable name is used as the text domain when internationalizing strings
		 * of text. Its value should match the Text Domain file header in the main
		 * widget file.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		protected $widget_slug = 'cbxrbooking'; //main parent plugin's language file

		/*--------------------------------------------------*/
		/* Constructor
		/*--------------------------------------------------*/

		/**
		 * Specifies the classname and description, instantiates the widget,
		 * loads localization files, and includes necessary stylesheets and JavaScript.
		 */
		public function __construct() {

			parent::__construct(
				$this->get_widget_slug(),
				esc_html__( 'CBX Booking Widget', 'cbxrbooking' ),
				array(
					'classname'   => 'widget-cbxrbooking',
					'description' => esc_html__( 'Displays CBX Booking Form in Widget position', 'cbxrbooking' )
				)
			);

		} // end constructor

		/**
		 * Return the widget slug.
		 *
		 * @since    1.0.0
		 *
		 * @return    Plugin slug variable.
		 */
		public function get_widget_slug() {
			return $this->widget_slug;
		}

		/*--------------------------------------------------*/
		/* Widget API Functions
		/*--------------------------------------------------*/

		/**
		 * Outputs the content of the widget.
		 *
		 * @param array args  The array of form elements
		 * @param array instance The current instance of the widget
		 */
		public function widget( $args, $instance ) {
			if ( ! isset ( $args['widget_id'] ) ) {
				$args['widget_id'] = $this->id;
			}


			// go on with your widget logic, put everything into a string and …

			extract( $args, EXTR_SKIP );

			$widget_string = $before_widget;

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? esc_html__( 'CBX Booking Widget', 'cbxrbooking' ) : $instance['title'], $instance, $this->id_base );
			// Defining the Widget Title
			if ( $title ) {
				$widget_string .= $args['before_title'] . $title . $args['after_title'];
			} else {
				$widget_string .= $args['before_title'] . $args['after_title'];
			}


			$instance = apply_filters( 'cbxrbookingsinglewidget_widget', $instance );

			$instance['id'] = isset( $instance['id'] ) ? esc_attr( $instance['id'] ) : '';

			extract( $instance );

			if ( intval( $id ) > 0 ) {
				$widget_string .= do_shortcode( '[cbxrbooking id="' . $id . '" scope="widget"]' );

			} else {
				$widget_string .= '<p>' . esc_html__( 'Sorry, No booking form found.', 'cbxrbooking' ) . '</p>';
			}

			$widget_string .= $after_widget;

			echo $widget_string;

		} // end widget


		/**
		 * Processes the widget's options to be saved.
		 *
		 * @param array new_instance The new instance of values to be generated via the update.
		 * @param array old_instance The previous instance of values before the update.
		 */
		public function update( $new_instance, $old_instance ) {

			$instance = $old_instance;

			$instance['title'] = sanitize_text_field( $new_instance['title'] );
			$instance['id']    = esc_attr( $new_instance['id'] );

			$instance = apply_filters( 'cbxrbookingsinglewidget_update', $instance, $new_instance );

			return $instance;

		} // end widget

		/**
		 * Generates the administration form for the widget.
		 *
		 * @param array instance The array of keys and values for the widget.
		 */
		public function form( $instance ) {

			$fields = array(
				'title' => esc_html__( 'CBX Booking Widget', 'cbxrbooking' ),
				'id'    => 0, //form id
			);

			$fields = apply_filters( 'cbxrbookingsinglewidget_widget_form_fields', $fields );

			$instance = wp_parse_args(
				(array) $instance,
				$fields
			);

			$instance = apply_filters( 'cbxrbookingsinglewidget_widget_form', $instance );

			extract( $instance, EXTR_SKIP );

			// Display the admin form

			include(cbxrbooking_locate_template( 'widgets/cbxrbooking-widget/admin.php' ));

		}//end form

	}//end class CBXRBookingWidget