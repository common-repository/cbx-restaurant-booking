<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXRBooking
 * @subpackage CBXRBooking/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CBXRBooking
 * @subpackage CBXRBooking/public
 * @author     Codeboxr <info@codeboxr.com>
 */
class CBXRBooking_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	public $mail_format;
	//public $mail_from_address;
	//public $mail_from_name;
	public $settings_api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		//get instance of setting api
		$this->settings_api = new CBXRBooking_Settings_API();
	}

	public function init_session() {
		/**
		 * Start sessions if not exists
		 *
		 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
		 */
		if ( version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
			if ( function_exists( 'session_status' ) && session_status() == PHP_SESSION_NONE ) {
				session_start( array(
					'cache_limiter'  => 'private_no_expire',
					'read_and_close' => FALSE,
				) );
			}
		} else if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) && version_compare( PHP_VERSION, '7.0.0', '<' ) ) {
			if ( function_exists( 'session_status' ) && session_status() == PHP_SESSION_NONE ) {
				session_cache_limiter( 'private_no_expire' );
				session_start();
			}
		} else {
			if ( session_id() == '' ) {
				if ( version_compare( PHP_VERSION, '4.0.0', '>=' ) ) {
					session_cache_limiter( 'private_no_expire' );
				}
				session_start();
			}
		}
	}//end init_cookie

	public function init_shortcodes() {
		//add shortcode for frontend booking form, same booking form is available as widget
		add_shortcode( 'cbxrbooking', array( $this, 'cbxrbooking_shortcode' ) );
	}

	/**
	 * Shortcode callback
	 */
	public function cbxrbooking_shortcode( $atts ) {
		//global $post;

		$atts = shortcode_atts(
			array(
				'id'    => '', //form id
				'scope' => 'shortcode'
			),
			$atts, 'cbxrbooking' );


		$form_id = intval( $atts['id'] );
		$scope   = ( isset( $atts['scope'] ) && $atts['scope'] != '' ) ? esc_attr( $atts['scope'] ) : 'shortcode';

		//if no form id found return
		if ( $form_id == 0 ) {
			return '';
		}

		//if form id doesn't return proper post id then return
		if ( get_post_type( $form_id ) !== 'cbxrbooking' ) {
			return;
		}

		//get the meta status for the form
		$meta_status = get_post_meta( $form_id, '_cbxrbookingformmeta_status', TRUE );

		if ( $meta_status === FALSE || $meta_status != 1 ) {
			return '';
		} //nothing saved


		//get the meta values for the form
		$meta = get_post_meta( $atts['id'], '_cbxrbooking', TRUE );
		if ( $meta === FALSE ) {
			return '';
		} //nothing saved

		if ( ! is_array( $meta ) ) {
			$meta = array();
		} else {
			$meta = array_filter( $meta );
		}

		$booking_meta = get_post_meta( $form_id, '_cbxrbookingmeta', TRUE );

		$error_template = FALSE;
		$error_text     = '';

		if ( isset( $booking_meta['settings']['cbxrbooking_misc']['enable_form_submission_limit'] ) && $booking_meta['settings']['cbxrbooking_misc']['enable_form_submission_limit'] !== 'off' && $booking_meta['settings']['cbxrbooking_misc']['form_submission_limit_val'] !== '' && intval( $booking_meta['settings']['cbxrbooking_misc']['form_submission_limit_val'] ) > 0 ) {
			$submission_count = intval( get_post_meta( $form_id, '_cbxrbookingmeta_submission_count', TRUE ) );
			if ( $submission_count >= intval( $booking_meta['settings']['cbxrbooking_misc']['form_submission_limit_val'] ) ) {
				$error_template      = TRUE;
				$limit_error_message = ( isset( $booking_meta['settings']['cbxrbooking_misc']['limit_error_message'] ) && $booking_meta['settings']['cbxrbooking_misc']['limit_error_message'] != '' ) ? $booking_meta['settings']['cbxrbooking_misc']['limit_error_message'] : esc_html__( 'Sorry! Booking limit has crossed. We are not accepting any more request. Thank you.', 'cbxrbooking' );
				$error_text          = $limit_error_message;
			}
		}

		if ( isset( $booking_meta['settings']['cbxrbooking_style']['banned_ip'] ) ) {
			$banned_ips        = preg_split( '/\s+/', $booking_meta['settings']['cbxrbooking_style']['banned_ip'] );
			$current_ipaddress = CBXRBookingHelper::get_ipaddress();
			if ( in_array( $current_ipaddress, $banned_ips ) ) {
				$error_template = TRUE;
				$error_text     = sprintf( __( 'Sorry! We are not accepting any booking from your IP Address(%s). Thank you.', 'cbxrbooking' ), $current_ipaddress );
			}
		}

		//good to go

		if ( ! defined( 'CBXRBOOKING_FRONT_JSCSS' ) ) {

			//js and css are already registered but we are enqueuing when needed
			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_style( 'cbxrbookingcustombootstrap' );
			wp_enqueue_style( 'cbxrbookingpublic' );

			wp_enqueue_script( 'flatpickr' );
			wp_enqueue_script( 'jquery-validate' );
			wp_enqueue_script( 'cbxrbookingpublic' );

			define( 'CBXRBOOKING_FRONT_JSCSS', 1 );
		}

		ob_start();

		if ( $error_template ) {

			include( cbxrbooking_locate_template( 'public/cbxrbooking-public-form-error.php' ) );
		} else {

			include( cbxrbooking_locate_template( 'public/cbxrbooking-public-form.php' ) );
		}

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Frontend booking form submit handle
	 */
	public function frontend_entrysubmit() {

		//if frontend form submit and also nonce verified then go
		if ( ( isset( $_POST['rbooking_frontend_entrysubmit'] ) && intval( $_POST['rbooking_frontend_entrysubmit'] ) == 1 ) &&
		     ( isset( $_POST['rbooking_token'] ) &&
		       wp_verify_nonce( $_POST['rbooking_token'], 'rbooking_formentry' ) )
		) {

			//possible way to handle request
			$current_offset = get_option( 'gmt_offset' );
			$tzstring       = get_option( 'timezone_string' );

			$check_zone_info = TRUE;

			// Remove old Etc mappings. Fallback to gmt_offset.
			if ( FALSE !== strpos( $tzstring, 'Etc/GMT' ) ) {
				$tzstring = '';
			}

			if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists
				$check_zone_info = FALSE;
				if ( 0 == $current_offset ) {
					$tzstring = '+0';
				} elseif ( $current_offset < 0 ) {
					$tzstring = '' . $current_offset;
				} else {
					$tzstring = '+' . $current_offset;
				}
			}

			$date_time_zone       = new DateTimeZone( $tzstring );
			$today_date_requested = new DateTime( 'now', $date_time_zone );

			global $wpdb;
			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;

			$rbookinglog_table = $wpdb->prefix . "cbxrbooking_log_manager";
			$post_data         = $_POST;

			$page_url = isset( $post_data['rbooking_frontend_url'] ) ? esc_url( $post_data['rbooking_frontend_url'] ) : '';
			$form_id  = isset( $post_data['cbxrb_formid'] ) ? intval( $post_data['cbxrb_formid'] ) : 0;

			$booking_id = 0;

			//sanitization
			$preferred_date = isset( $post_data['cbxrb_preferred_date'] ) ? sanitize_text_field( $post_data['cbxrb_preferred_date'] ) : '';
			$preferred_time = isset( $post_data['cbxrb_preferred_time'] ) ? sanitize_text_field( $post_data['cbxrb_preferred_time'] ) : '';
			$party          = isset( $post_data['cbxrb_party'] ) ? intval( $post_data['cbxrb_party'] ) : 1;
			$name           = isset( $post_data['cbxrb_name'] ) ? sanitize_text_field( $post_data['cbxrb_name'] ) : '';
			$email          = isset( $post_data['cbxrb_email'] ) ? sanitize_email( $post_data['cbxrb_email'] ) : '';
			$phone          = isset( $post_data['cbxrb_phone'] ) ? sanitize_text_field( $post_data['cbxrb_phone'] ) : '';
			$message        = isset( $post_data['cbxrb_message'] ) ? sanitize_text_field( $post_data['cbxrb_message'] ) : '';
			$privacy        = isset( $post_data['cbxrb_privacy'] ) ? sanitize_text_field( $post_data['cbxrb_privacy'] ) : '';

			$date_requested      = new DateTime( $preferred_date, $date_time_zone );
			$time_requested      = new DateTime( $preferred_time, $date_time_zone );
			$date_time_requested = new DateTime( $date_requested->format( 'Y-m-d' ) . ' ' . $time_requested->format( 'H:i:s' ), $date_time_zone ); //will help for exception based checking

			$today_date_requested      = new DateTime( 'now', $date_time_zone );
			$today_date_time_requested = new DateTime( $today_date_requested->format( 'Y-m-d' ) . ' ' . $time_requested->format( 'H:i:s' ), $date_time_zone ); //will help for weekday based checking


			$secret = '';

			//validation
			$hasError          = FALSE;
			$validation_errors = [];

			if ( $form_id == 0 ) {
				$hasError                                                        = TRUE;
				$validation_errors['top_errors']['cbxrb_formid']['formid_empty'] = esc_html__( 'Invalid booking form or booking form doesn\'t exists in backend.', 'cbxrbooking' );
			}

			$meta = '';

			//form setting field validation
			if ( $form_id > 0 ) {

				// check post type is cbxrbooking
				$form_id_post_type = get_post_type( $form_id );
				if ( $form_id_post_type === FALSE || $form_id_post_type !== 'cbxrbooking' ) {
					$hasError                                                          = TRUE;
					$validation_errors['top_errors']['cbxrb_formid']['formid_invalid'] = esc_html__( 'Booking form doesn\'t exists.', 'cbxrbooking' );
				}

				// get form setting
				$meta = get_post_meta( $form_id, '_cbxrbookingmeta', TRUE );
				if ( $meta != '' ) {
					if ( $meta['settings']['cbxrbooking_style']['require_name'] === 'name-yes' && empty( $name ) ) {
						$hasError                                      = TRUE;
						$validation_errors['cbxrb_name']['name_empty'] = esc_html__( 'Sorry! Name is empty.', 'cbxrbooking' );
					}

					if ( $meta['settings']['cbxrbooking_style']['require_email'] === 'email-yes' && empty( $email ) ) {
						$hasError                                        = TRUE;
						$validation_errors['cbxrb_email']['email_empty'] = esc_html__( 'Sorry! Email address is empty.', 'cbxrbooking' );
					}

					if ( ! empty( $email ) && ! is_email( $email ) ) {
						$hasError                                          = TRUE;
						$validation_errors['cbxrb_email']['email_invalid'] = esc_html__( 'Sorry! Email address in invalid.', 'cbxrbooking' );
					}


					if ( $meta['settings']['cbxrbooking_style']['require_phone'] === 'phone-yes' && empty( $phone ) ) {
						$hasError                                        = TRUE;
						$validation_errors['cbxrb_phone']['phone_empty'] = esc_html__( 'Sorry! Phone number is empty.', 'cbxrbooking' );
					}

					if ( $meta['settings']['cbxrbooking_style']['user_consent'] === 'on' && $privacy == '' ) {
						$hasError                                              = TRUE;
						$validation_errors['cbxrb_privacy']['content_missing'] = esc_html__( 'Sorry! Privacy consent missing', 'cbxrbooking' );
					}


					//check if email banned
					if ( ! empty( $email ) && isset( $meta['settings']['cbxrbooking_style']['banned_email'] ) ) {
						$banned_emails = $meta['settings']['cbxrbooking_style']['banned_email'];
						if ( is_array( $banned_emails ) && in_array( $email, $banned_emails ) ) {
							$hasError                                                       = TRUE;
							$validation_errors['top_errors']['cbxrb_email']['email_banned'] = esc_html__( 'Sorry! Your email address is banned. Please contact site admin', 'cbxrbooking' );
						}
					}

					//check if ip banned
					if ( isset( $meta['settings']['cbxrbooking_style']['banned_ip'] ) ) {
						$banned_ips = $meta['settings']['cbxrbooking_style']['banned_ip'];
						if ( is_array( $banned_ips ) && in_array( $_SERVER['REMOTE_ADDR'], $banned_ips ) ) {
							$hasError                                                 = TRUE;
							$validation_errors['top_errors']['cbxrb_ip']['ip_banned'] = esc_html__( 'Sorry! Your ip address is banned. Please contact site admin.', 'cbxrbooking' );
						}
					}

					$min_party_size = intval( $meta['settings']['cbxrbooking_style']['min_party_size'] );
					$max_party_size = $meta['settings']['cbxrbooking_style']['max_party_size'] != '' ? intval( $meta['settings']['cbxrbooking_style']['max_party_size'] ) : 100;
					// swap party size if min > max
					if ( $min_party_size > $max_party_size ) {
						$swap           = $max_party_size;
						$max_party_size = $min_party_size;
						$min_party_size = $swap;
					}

					if ( ! ( $party >= $min_party_size && $party <= $max_party_size ) ) {
						$hasError                                               = TRUE;
						$validation_errors['cbxrb_party']['party_not_in_range'] = sprintf( esc_html__( 'Sorry! Party size should be between minimum %d and maximum %d', 'cbxrbooking' ), $min_party_size, $max_party_size );
					}

				}//end if meta exists

				//check if date empty or valid
				if ( empty( $preferred_date ) ) {
					$hasError                                                = TRUE;
					$validation_errors['cbxrb_preferred_date']['date_empty'] = esc_html__( 'Sorry, Date is empty', 'cbxrbooking' );
				} else if ( ! CBXRBookingHelper::validateDate( $preferred_date, 'Y-m-d' ) ) {
					$hasError                                                  = TRUE;
					$validation_errors['cbxrb_preferred_date']['date_invalid'] = esc_html__( 'Sorry! Date is invalid.', 'cbxrbooking' );
				}


				//check if time empty or valid
				if ( empty( $preferred_time ) ) {
					$hasError                                                = TRUE;
					$validation_errors['cbxrb_preferred_time']['time_empty'] = esc_html__( 'Sorry! Time is empty.', 'cbxrbooking' );
				} else if ( ! CBXRBookingHelper::validateDate( $preferred_time, 'H:i' ) ) {
					$hasError                                                  = TRUE;
					$validation_errors['cbxrb_preferred_time']['time_invalid'] = esc_html__( 'Sorry! Time is invalid.', 'cbxrbooking' );
				}

			}//end if form exists

			$current_time                          = date( "H:i" );
			$cbxrbooking_booking_schedule_settings = $meta['settings']['cbxrbooking_booking_schedule'];

			//if general validation passed then we can check the date time schedule setting
			if ( $hasError == FALSE ) {

				// early booking check
				$early_bookings = isset( $cbxrbooking_booking_schedule_settings['early_bookings'] ) ? $cbxrbooking_booking_schedule_settings['early_bookings'] : NULL;


				if ( ! is_null( $early_bookings ) && $early_bookings != '' ) {
					$early_bookings         = intval( $early_bookings );
					$early_bookings_seconds = $early_bookings * 24 * 60 * 60;
					if ( $date_time_requested->format( 'U' ) > ( current_time( 'timestamp' ) + $early_bookings_seconds ) ) {
						$hasError                                                  = TRUE;
						$early_booking_message                                     = CBXRBookingHelper::getEarlyBookingByValue( $early_bookings );
						$validation_errors['top_errors']['date_in_early_bookings'] = esc_html__( 'Sorry! Early booking rule is:', 'cbxrbooking' ) . '<strong>' . $early_booking_message . '</strong>';
					}
				}


				// late booking check
				$late_bookings = isset( $cbxrbooking_booking_schedule_settings['late_bookings'] ) ? $cbxrbooking_booking_schedule_settings['late_bookings'] : 30;
				if ( $hasError == FALSE ) {
					if ( empty( $late_bookings ) ) {
						if ( $date_time_requested->format( 'U' ) < current_time( 'timestamp' ) ) {
							$hasError = TRUE;
							//$validation_errors['cbxrb_preferred_date']['booking_in_the_past'] = esc_html__( 'Sorry! Booking can not be done in past date.', 'cbxrbooking' );
							$validation_errors['top_errors']['booking_in_the_past'] = esc_html__( 'Sorry! Booking can not be done in past date.', 'cbxrbooking' );
						}
					} else if ( $late_bookings === 'same_day' ) {
						if ( $date_time_requested->format( 'Y-m-d' ) == current_time( 'Y-m-d' ) ) {
							$hasError = TRUE;
							//$validation_errors['cbxrb_preferred_date']['date_in_late_bookings'] = esc_html__( 'Sorry! Same day booking is not allowed', 'cbxrbooking' );
							$validation_errors['top_errors']['date_in_late_bookings'] = esc_html__( 'Sorry! Same day booking is not allowed', 'cbxrbooking' );
						}
					} else {
						$late_bookings         = intval( $late_bookings );
						$late_bookings_seconds = $late_bookings * 60; // all values are in minutues

						if ( $date_time_requested->format( 'U' ) < ( current_time( 'timestamp' ) + $late_bookings_seconds ) ) {
							$hasError             = TRUE;
							$late_booking_message = CBXRBookingHelper::getLateBookingByValue( $late_bookings );

							$validation_errors['top_errors']['time_in_late_bookings'] = esc_html__( 'Sorry! Late booking rule is:', 'cbxrbooking' ) . '<strong>' . $late_booking_message . '</strong>';
						}

					}
				}
			}

			//regular and exception date schedule check
			if ( $hasError == FALSE ) {

				//requested weekday number
				$week_day_number_request = date( "w", strtotime( $preferred_date ) );


				// booking schedule settings: dates and times preparation
				$schedule_event = isset( $cbxrbooking_booking_schedule_settings['schedule_event'] ) ? $cbxrbooking_booking_schedule_settings['schedule_event'] : NULL;

				$schedule_weekdays = $schedule_times = array();
				//weekday schedule formatting
				if ( ! is_null( $schedule_event ) && is_array( $schedule_event ) && sizeof( $schedule_event ) > 0 ) {
					foreach ( $schedule_event as $key => $arr_values ) {
						if ( isset( $arr_values['weekdays'] ) ) {
							$schedule_weekdays = array_merge( $schedule_weekdays, $arr_values['weekdays'] );
							foreach ( $arr_values['weekdays'] as $index => $week_day_number ) {
								if ( $arr_values['times']['start'] != '' || $arr_values['times']['end'] != '' ) {

									if ( $arr_values['times']['start'] != '' ) {
										$schedule_time_start                    = new DateTime( $arr_values['times']['start'], $date_time_zone ); //only time
										$schedule_datetime_start                = new DateTime( $today_date_requested->format( 'Y-m-d' ) . ' ' . $schedule_time_start->format( 'H:i:s' ), $date_time_zone );
										$arr_values['times']['start_date_time'] = $schedule_datetime_start;
									} else {
										$arr_values['times']['start_date_time'] = '';
									}

									if ( $arr_values['times']['end'] != '' ) {
										$schedule_time_end                    = new DateTime( $arr_values['times']['end'], $date_time_zone );
										$schedule_datetime_end                = new DateTime( $today_date_requested->format( 'Y-m-d' ) . ' ' . $schedule_time_end->format( 'H:i:s' ), $date_time_zone );
										$arr_values['times']['end_date_time'] = $schedule_datetime_end;
									} else {
										$arr_values['times']['end_date_time'] = '';
									}

									$schedule_times[ $week_day_number ] = $arr_values['times'];

								} else {
									$schedule_times[ $week_day_number ] = '';
								}
							}
						}
					}
					$schedule_weekdays = array_unique( $schedule_weekdays );
				}//end of weekday schedule formatting


				// booking schedule exceptions: dates and times prepartion
				$scheduler_exceptions = isset( $cbxrbooking_booking_schedule_settings['scheduler_exceptions'] ) ? $cbxrbooking_booking_schedule_settings['scheduler_exceptions'] : NULL;
				$exceptions_dates     = $exceptions_times = array();

				//exception schedules formatting
				if ( ! is_null( $scheduler_exceptions ) && is_array( $scheduler_exceptions ) && sizeof( $scheduler_exceptions ) > 0 ) {
					foreach ( $scheduler_exceptions as $index => $valArr ) {
						if ( isset( $valArr['date'] ) && $valArr['date'] != '' ) {
							if ( ( $key = array_search( $valArr['date'], $exceptions_dates ) ) !== FALSE ) {
								unset( $exceptions_dates[ $key ] );
							}
							array_push( $exceptions_dates, $valArr['date'] );

							if ( $valArr['times']['start'] != '' || $valArr['times']['end'] != '' ) {
								//let's prepare pure date object from the exception date and their start and end time and store for future use
								$exception_date = new DateTime( $valArr['date'], $date_time_zone );

								if ( $valArr['times']['start'] != '' ) {
									$exception_time_start               = new DateTime( $valArr['times']['start'], $date_time_zone );
									$exception_datetime_start           = new DateTime( $exception_date->format( 'Y-m-d' ) . ' ' . $exception_time_start->format( 'H:i:s' ), $date_time_zone );
									$valArr['times']['start_date_time'] = $exception_datetime_start;
								} else {
									$valArr['times']['start_date_time'] = '';
								}

								if ( $valArr['times']['end'] != '' ) {
									$exception_time_end               = new DateTime( $valArr['times']['end'], $date_time_zone );
									$exception_datetime_end           = new DateTime( $exception_date->format( 'Y-m-d' ) . ' ' . $exception_time_end->format( 'H:i:s' ), $date_time_zone );
									$valArr['times']['end_date_time'] = $exception_datetime_end;
								} else {
									$valArr['times']['end_date_time'] = '';
								}

								$exceptions_times[ $valArr['date'] ] = $valArr['times'];

							} else {
								$exceptions_times[ $valArr['date'] ] = '';
							}
						}
					}
				}//end exception schedules formatting


				$exception_passed = FALSE;

				//check for exception list for any full day closed or special open
				if ( $hasError == FALSE && is_array( $exceptions_dates ) && sizeof( $exceptions_dates ) > 0 ) {
					//no regular schedule but still check exception list
					//here two things can happen, user may check in full closed day or may not check between the open time
					if ( in_array( $preferred_date, $exceptions_dates ) ) {
						//if requested dated exists in exception date list
						if ( $exceptions_times[ $preferred_date ] == '' ) {
							//sorry user requested in a closed day
							$hasError                                                             = TRUE;
							$validation_errors['cbxrb_preferred_date']['date_schedule_exception'] = esc_html__( 'Sorry! Date is not available for booking.', 'cbxrbooking' );
						} else if ( ( $exceptions_times[ $preferred_date ]['start_date_time'] != '' && $date_time_requested < $exceptions_times[ $preferred_date ]['start_date_time'] ) ||
						            ( $exceptions_times[ $preferred_date ]['end_date_time'] != '' && $date_time_requested > $exceptions_times[ $preferred_date ]['end_date_time'] ) ) {
							//let's check if user requested within proper time interval
							$hasError                                                      = TRUE;
							$validation_errors['cbxrb_preferred_time']['date_in_schedule'] = esc_html__( 'Sorry! This Time is not available.', 'cbxrbooking' );

						} else {
							$exception_passed = TRUE;
						}
					}
				}//end check for exception list for any full day closed or special open

				if ( $hasError == FALSE && $exception_passed == FALSE && is_array( $schedule_weekdays ) && sizeof( $schedule_weekdays ) > 0 ) {

					//at first check if the user selected date's weekday is in weekdays schedule
					if ( ! in_array( $week_day_number_request, $schedule_weekdays ) ) {
						$hasError                                                             = TRUE;
						$validation_errors['cbxrb_preferred_date']['date_schedule_exception'] = esc_html__( 'Sorry! Date is not available for booking.', 'cbxrbooking' );

					} else {
						//requested day is found in week days
						//found in weekday
						if ( $schedule_times[ $week_day_number_request ] != '' ) {
							//let's check if user requested within proper time interval
							if ( ( $schedule_times[ $week_day_number_request ]['start_date_time'] != '' && $today_date_time_requested < $schedule_times[ $week_day_number_request ]['start_date_time'] ) ||
							     ( $schedule_times[ $week_day_number_request ]['end_date_time'] != '' && $today_date_time_requested > $schedule_times[ $week_day_number_request ]['end_date_time'] ) ) {
								$hasError                                                      = TRUE;
								$validation_errors['cbxrb_preferred_time']['date_in_schedule'] = esc_html__( 'Sorry! This Time is not available.', 'cbxrbooking' );
							}
						}
					}
				}//end has $hasError

			}//end date time related all validation


			$validation_errors = apply_filters( 'cbxrbooking_form_validation_errors', $validation_errors, $post_data, $form_id, $booking_id, $secret );

			if ( sizeof( $validation_errors ) > 0 ) {
				$cbxrbooking_validation_errors['error'] = $validation_errors; //to send in ajax
				wp_send_json( $cbxrbooking_validation_errors );
			}

			//data validated and now good to add/update
			$data_safe['form_id']      = $form_id;
			$data_safe['booking_date'] = $preferred_date;
			$data_safe['booking_time'] = $preferred_time;
			$data_safe['name']         = $name;
			$data_safe['email']        = $email;
			$data_safe['party_size']   = $party;
			$data_safe['phone']        = $phone;
			$data_safe['message']      = $message;
			$data_safe['user_ip']      = CBXRBookingHelper::get_ipaddress();

			//insert
			$data_safe['secret']     = CBXRBookingHelper::generateBookingSecret(); //used for further edit using a private secret code
			$data_safe['activation'] = '';

			$meta = get_post_meta( $form_id, '_cbxrbookingmeta', TRUE );

			$data_safe['status'] = isset( $meta['settings']['cbxrbooking_style']['default_state'] ) ? $meta['settings']['cbxrbooking_style']['default_state'] : 'pending';

			if ( isset( $meta['settings']['cbxrbooking_style']['guest_activation'] ) && $meta['settings']['cbxrbooking_style']['guest_activation'] == 'on' ) {
				if ( intval( $user_id ) == 0 ) {
					$data_safe['activation'] = wp_generate_password( $length = 12, FALSE, FALSE );//used for email activation, if email activation enabled then we use it, after activation we delete this value like password activation
					$data_safe['status']     = 'unverified';
				}
			}


			$meta_data             = array();
			$meta_data             = apply_filters( 'cbxrbooking_form_meta_data_before_insert', $meta_data, $post_data, $form_id, $booking_id, $secret );
			$data_safe['metadata'] = maybe_serialize( $meta_data );

			$data_safe = apply_filters( 'cbxrbooking_form_data_before_insert', $data_safe, $form_id );

			$data_safe['add_by']   = $user_id;
			$data_safe['add_date'] = current_time( 'mysql' );

			$col_data_format = array(
				'%d', //form id
				'%s', //booking date
				'%s', ///booking time
				'%s', //name
				'%s', //email
				'%d', //party_size
				'%s', //phone
				'%s', //message
				'%s', //user_ip
				'%s', //secret
				'%s', //activation
				'%s', //status
				'%s', //metadata
				'%d', //add_by
				'%s', //add_date

			);
			$col_data_format = apply_filters( 'cbxrbooking_form_col_data_format_before_insert', $col_data_format, $data_safe, $form_id );

			$show_form   = 1;
			$messages    = array();
			$success_arr = array();

			do_action( 'cbxrbooking_form_before_insert', $form_id, $booking_id, $data_safe );

			if ( $wpdb->insert( $rbookinglog_table, $data_safe, $col_data_format ) !== FALSE ) {
				$booking_id      = $wpdb->insert_id;
				$data_safe['id'] = $booking_id;

				do_action( 'cbxrbooking_form_after_insert', $form_id, $booking_id, $data_safe );

				//update the form submission count
				$form_submission_count = intval( get_post_meta( $form_id, '_cbxrbookingmeta_submission_count', TRUE ) );
				update_post_meta( $form_id, '_cbxrbookingmeta_submission_count', intval( $form_submission_count ) + 1 );


				$message    = array(
					'text' => ( isset( $meta['settings']['cbxrbooking_style']['success_message'] ) && $meta['settings']['cbxrbooking_style']['success_message'] != '' ) ? str_replace( '{booking_code}', $data_safe['secret'], $meta['settings']['cbxrbooking_style']['success_message'] ) : sprintf( __( 'Booking request submitted successfully. Booking code: <code>%s</code>', 'cbxrbooking' ), $data_safe['secret'] ),
					'type' => 'success'
				);
				$messages[] = $message;

				//write_log($meta['settings']['cbxrbooking_misc']['showform_successful']);

				if ( isset( $meta['settings']['cbxrbooking_misc']['showform_successful'] ) && $meta['settings']['cbxrbooking_misc']['showform_successful'] == 'off' ) {
					$show_form = 0;
				}

				//$email_status = $this->action_form($form_id, $booking_id, $data_safe, $meta );
				$messages = $this->sendFrontendBookingAdminEmailAlert( $messages, $data_safe, $meta );
				$messages = $this->sendFrontendBookingUserEmailAlert( $messages, $data_safe, $meta );

				$messages = apply_filters( 'cbxrbooking_validation_success_messages', $messages, $form_id, $booking_id );
			} else {
				//failed to insert
				$message    = array(
					'text' => esc_html__( 'Sorry! Problem during booking request, please check again and try again.', 'cbxrbooking' ),
					'type' => 'danger'
				);
				$messages[] = $message;
				$show_form  = 0;
			}

			$success_arr['messages']  = $messages;
			$success_arr['show_form'] = $show_form;



			$cbxrbooking_insert['error']   = '';
			$cbxrbooking_insert['success'] = $success_arr;


			wp_send_json( $cbxrbooking_insert );
		}
	}//end frontend_entrysubmit

	/**
	 * Guest email verification: if guest email user redirect back to site by clicking activation link
	 */
	public function frontend_guest_activation() {
		if ( isset( $_GET['cbxrb_verification'] ) && intval( $_GET['cbxrb_verification'] ) == 1 && isset( $_GET['activation_code'] ) && $_GET['activation_code'] != '' ) {

			global $wpdb;
			$booking_table = $wpdb->prefix . "cbxrbooking_log_manager";

			$activation_code = sanitize_text_field( $_GET['activation_code'] );

			$booking_info = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $booking_table WHERE activation = %s", $activation_code )
			);

			//if booking log found
			if ( $booking_info !== NULL ) {

				$form_id = $booking_info->form_id;
				$log_id  = $booking_info->id;
				$secret  = $booking_info->secret;

				$meta           = get_post_meta( intval( $form_id ), '_cbxrbookingmeta', TRUE );
				$default_status = isset( $meta['settings']['cbxrbooking_style']['default_state'] ) ? $meta['settings']['cbxrbooking_style']['default_state'] : 'pending';

				$update_status = $wpdb->update(
					$booking_table,
					array(
						'activation' => '',
						'status'     => $default_status,
						'mod_date'   => current_time( 'mysql' )
					),
					array(
						'activation' => $activation_code,
						'form_id'    => $form_id,
						'id'         => $log_id

					),
					array(
						'%s',
						'%s',
						'%s'
					),
					array(
						'%s',
						'%d',
						'%d'
					)
				);

				//booking found and updated
				if ( $update_status !== FALSE && intval( $update_status ) > 0 ) {
					echo sprintf( __( 'Booking validated successfully. Booking code: <code>%s</code>. No email will be sent to inform this. Site admin will check your request and booking confirmation will be set as per system setting. <a href="%s">Click to go home</a>.', 'cbxrbooking' ), $secret, home_url() );
					exit();
				} else {
					//failed to update booking
					echo sprintf( __( 'Sorry, booking found but validation failed. Booking code: <code>%s</code>.<a href="%s">Click to go home</a>.', 'cbxrbooking' ), $secret, home_url() );
					exit();
				}

			} else {
				//booking not found or already activated
				echo sprintf( __( 'Sorry, booking not found or already validated. <a href="%s">Click to go home</a>.', 'cbxrbooking' ), home_url() );
				exit();
			}
		}
	}//end guest email verification

	/**
	 * Cancel request by guest if guest email user redirect back to site by clicking cancel link
	 */
	public function frontend_cancel_request() {
		if ( isset( $_GET['cbxrb_cancel_request'] ) && ( intval( $_GET['cbxrb_cancel_request'] ) == 1 ) && isset( $_GET['secret'] ) && $_GET['secret'] != '' ) {
			global $wpdb;
			$booking_table = $wpdb->prefix . "cbxrbooking_log_manager";

			$secret = sanitize_text_field( $_GET['secret'] );

			$booking_log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $booking_table WHERE secret = %s", $secret ) );

			$messages = array();
			$form_id  = 0;

			if ( $booking_log !== NULL ) {
				$form_id = intval( $booking_log->form_id );
				$meta    = get_post_meta( $form_id, '_cbxrbookingmeta', TRUE );

				//if cancel booking is not enabled then no email will be sent or no processing will be done.
				if ( isset( $meta['settings']['cbxrbooking_cancel_booking'] ) && $meta['settings']['cbxrbooking_cancel_booking']['booking_cancel'] !== 'on' ) {

					echo '<p>' . esc_html__( 'Booking cancel is not enabled and request can not be processed.', 'cbxrbooking' ) . '</p>';
					echo sprintf( __( '<p><a href="%s">Click to go home</a></p>', 'cbxrbooking' ), home_url() );
					exit();
				}

				//check current status of booking if we need to process next or ignore
				if ( ! in_array( $booking_log->status, array( 'canceled', 'cancel-request' ) ) ) {
					//possible way to handle request
					$current_offset = get_option( 'gmt_offset' );
					$tzstring       = get_option( 'timezone_string' );

					$check_zone_info = TRUE;

					// Remove old Etc mappings. Fallback to gmt_offset.
					if ( FALSE !== strpos( $tzstring, 'Etc/GMT' ) ) {
						$tzstring = '';
					}

					if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists
						$check_zone_info = FALSE;
						if ( 0 == $current_offset ) {
							$tzstring = '+0';
						} elseif ( $current_offset < 0 ) {
							$tzstring = '' . $current_offset;
						} else {
							$tzstring = '+' . $current_offset;
						}
					}

					$date_time_zone = new DateTimeZone( $tzstring );

					$cbxrbooking_cancel_booking_settings = $meta['settings']['cbxrbooking_cancel_booking'];
					$early_cancel                        = $cbxrbooking_cancel_booking_settings['early_cancel'];

					$ok_to_update     = FALSE;
					$today_datetime   = new DateTime( 'now', $date_time_zone );
					$booking_date     = $booking_log->booking_date;
					$booking_time     = $booking_log->booking_time;
					$booking_datetime = new DateTime( $booking_date . ' ' . $booking_time, $date_time_zone );

					$booking_datetime_interval = ( $booking_datetime->getTimestamp() - $today_datetime->getTimestamp() ) / 60; //value in min


					if ( $booking_datetime_interval < 0 ) {
						$message    = array(
							'text' => esc_html__( 'Sorry, You can not cancel, booking date has been passed.', 'cbxrbooking' ),
							'type' => 'warning',
						);
						$messages[] = $message;
					} else if ( $early_cancel != '' ) {
						if ( $early_cancel === 'same_day' ) {
							$current_date = date( 'Y-m-d' );


							if ( $current_date === $booking_log->booking_date ) {
								$message    = array(
									'text' => esc_html__( 'Sorry! You can not cancel booking in same day.', 'cbxrbooking' ),
									'type' => 'warning',
								);
								$messages[] = $message;
							} else {
								$ok_to_update = TRUE;
							}
						} else {


							$early_cancel = intval( $early_cancel ); // in minute


							if ( $early_cancel <= $booking_datetime_interval ) {
								$ok_to_update = TRUE;
							}

							if ( ! $ok_to_update ) {
								$message    = array(
									'text' => esc_html__( 'Sorry! You can\'t cancel booking because minimum time before cancel expired.', 'cbxrbooking' ),
									'type' => 'warning',
								);
								$messages[] = $message;
							}
						}
					} else {
						$ok_to_update = TRUE;
					}

					if ( $ok_to_update ) {
						$cancel_setting_status = $cbxrbooking_cancel_booking_settings['cancel_status'];

						do_action( 'cbxrbooking_cancel_request_before_update', $messages, $booking_log, $meta );

						$booking_update_status = $wpdb->update(
							$booking_table,
							array(
								'status'   => $cancel_setting_status,
								'mod_date' => current_time( 'mysql' )
							),
							array(
								'id'     => intval( $booking_log->id ),
								'secret' => $secret
							),
							array(
								'%s',
								'%s'
							),
							array(
								'%d',
								'%s'
							)
						);

						do_action( 'cbxrbooking_cancel_request_after_update', $messages, $booking_log, $meta );

						//if successfully update
						if ( $booking_update_status !== FALSE && intval( $booking_update_status ) > 0 ) {
							if ( $cancel_setting_status == 'canceled' ) {
								$message    = array(
									'text' => esc_html__( 'Your request for canceling booking is successfully granted.', 'cbxrbooking' ),
									'type' => 'success',
								);
								$messages[] = $message;
							} else {

								$message    = array(
									'text' => esc_html__( 'Your request for canceling booking is on processing. You will notified by email when it is canceled.', 'cbxrbooking' ),
									'type' => 'success',
								);
								$messages[] = $message;
							}


							//sending email to user
							if (
								( isset( $meta['settings']['cbxrbooking_cancel_booking']['cancel_user_status_progress'] ) && $meta['settings']['cbxrbooking_cancel_booking']['cancel_user_status_progress'] == 'on' ) ||
								( isset( $meta['settings']['cbxrbooking_cancel_booking']['cancel_user_status_cancelled'] ) && $meta['settings']['cbxrbooking_cancel_booking']['cancel_user_status_cancelled'] == 'on' )
							) {
								//if admin alert, user alert for request received, user alert for request auto approved any one is on, let's process email
								$messages = $this->sendBookingCancelUserEmailAlert( $messages, $booking_log, $meta, $cancel_setting_status );
							}

							//sending email to admin
							if ( isset( $meta['settings']['cbxrbooking_cancel_booking']['cancel_admin_status'] ) && $meta['settings']['cbxrbooking_cancel_booking']['cancel_admin_status'] == 'on' ) {
								//if admin alert, user alert for request received, user alert for request auto approved any one is on, let's process email

								$messages = $this->sendBookingCancelAdminEmailAlert( $messages, $booking_log, $meta, $cancel_setting_status );
							}

						} else {
							$message    = array(
								'text' => esc_html__( 'Some problem during canceling the request. Please try again.', 'cbxrbooking' ),
								'type' => 'warning',
							);
							$messages[] = $message;
						}
					}

				} else {
					if ( $booking_log->status === 'canceled' ) {
						$message    = array(
							'text' => esc_html__( 'Your requested booking cancel request is already canceled.', 'cbxrbooking' ),
							'type' => 'warning',
						);
						$messages[] = $message;
					} else if ( $booking_log->status === 'cancel-request' ) {
						$message    = array(
							'text' => esc_html__( 'Your request for canceling booking is already on processing. You will notified by email when it is canceled or request is approved.', 'cbxrbooking' ),
							'type' => 'warning',
						);
						$messages[] = $message;
					}
				}
			} else {
				$message    = array(
					'text' => esc_html__( 'Sorry! No booking found.', 'cbxrbooking' ),
					'type' => 'danger',
				);
				$messages[] = $message;
			}

			//failed to update booking
			$output = '';
			if ( sizeof( $messages ) > 0 ) {
				foreach ( $messages as $message ) {
					$output .= '<div class="alert alert-' . $message['type'] . '" ><p>' . $message['text'] . '</p></div>';;
				}
			}
			echo $output;
			echo sprintf( __( '<p><a href="%s">Click to go home</a></p>', 'cbxrbooking' ), home_url() );
			exit();
		}//end no booking found

	}//end cancel request by guest

	/**
	 * Send email to admin after form submit based on form setting
	 *
	 * @param $messages array
	 * @param $data     array
	 * @param $meta     array
	 *
	 * @return array
	 */
	public function sendFrontendBookingAdminEmailAlert( $messages, $data, $meta ) {

		$settings = $meta['settings'];
		$html     = $admin_email_body = $user_email_body = $message = '';
		$return   = array();

		//send email to admin
		if ( isset( $settings['cbxrbooking_email_admin']['status'] ) && $settings['cbxrbooking_email_admin']['status'] == 'on' ) {
			$this->mail_format = $settings['cbxrbooking_email_admin']['format'];
			//$this->mail_from_address = ( $settings['cbxrbooking_email_admin']['from'] != '' ) ? $settings['cbxrbooking_email_admin']['from'] : get_bloginfo( 'admin_email' );
			//$this->mail_from_name    = $settings['cbxrbooking_email_admin']['name'];

			$to       = $settings['cbxrbooking_email_admin']['to'];
			$cc       = $settings['cbxrbooking_email_admin']['cc'];
			$bcc      = $settings['cbxrbooking_email_admin']['bcc'];
			$reply_to = $settings['cbxrbooking_email_admin']['reply_to'];

			$reply_to = str_replace( '{user_email}', $data['email'], $reply_to ); //replace email


			$subject             = $settings['cbxrbooking_email_admin']['subject'];
			$admin_email_heading = $settings['cbxrbooking_email_admin']['heading'];
			$admin_email_body    = $settings['cbxrbooking_email_admin']['body'];

			//email body syntax parsing
			$admin_email_body = str_replace( '{user_name}', $data['name'], $admin_email_body ); //replace name

			$admin_email_body = str_replace( '{user_email}', $data['email'], $admin_email_body ); //replace email

			$booking_date     = CBXRBookingHelper::viewDateFormat( $data['booking_date'], CBXRBookingHelper::storedFormDateFormatKey( $data['form_id'] ) );
			$admin_email_body = str_replace( '{booking_date}', $booking_date, $admin_email_body ); //replace booking date

			$booking_time             = $data['booking_time'];
			$twelve_hour_booking_time = CBXRBookingHelper::twelveHourBookingTimeFormat( $data['form_id'], $booking_time );
			if ( $twelve_hour_booking_time != '' ) {
				$booking_time = $twelve_hour_booking_time;
			}
			$admin_email_body = str_replace( '{booking_time}', $booking_time, $admin_email_body ); //replace booking time

			$admin_email_body = str_replace( '{party_size}', $data['party_size'], $admin_email_body ); //relace party size
			$admin_email_body = str_replace( '{booking_phone}', $data['phone'], $admin_email_body ); //replace phone number
			$admin_email_body = str_replace( '{booking_message}', $data['message'], $admin_email_body ); //replace message
			$admin_email_body = str_replace( '{booking_ip}', $data['user_ip'], $admin_email_body ); //replace booking ip
			$admin_email_body = str_replace( '{booking_code}', $data['secret'], $admin_email_body ); //replace booking code
			$admin_email_body = str_replace( '{booking_status}', CBXRBookingHelper::getBookingStatus( $data['status'] ), $admin_email_body ); //replace booking code

			$booking_log_url           = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs&log_id=' . $data['id'] );
			$booking_log_url_formatted = sprintf( __( '<p>To check or take action about this booking request <a href="%s">click this url.</a></p>', 'cbxrbooking' ), $booking_log_url );
			$admin_email_body          = str_replace( '{booking_log_url}', $booking_log_url_formatted, $admin_email_body ); //replace booking log url for admin

			//esp = email syntax parse
			$admin_email_body = apply_filters( 'cbxrbooking_esp_admin_alert', $admin_email_body, $messages, $data, $meta );

			$admin_email_body = wpautop( $admin_email_body );

			//end email body syntax parsing

			$emailTemplate = new CBXRbookingMailTemplate();
			$message       = $emailTemplate->getHtmlTemplate();
			$message       = str_replace( '{mainbody}', $admin_email_body, $message ); //replace mainbody
			$message       = str_replace( '{emailheading}', $admin_email_heading, $message ); //replace emailbody

			if ( $this->mail_format == 'html' ) {
				$message = $emailTemplate->htmlEmeilify( $message );
			} elseif ( $this->mail_format == 'plain' ) {
				$message = $emailTemplate->htmlEmeilify( $message );
				$message = Html2TextBooking\Html2Text::convert( $message );
				$message = Html2TextBooking\Html2Text::fixNewlines( $message );
			}

			$mail_helper = new CBXRBookingMail( $this->mail_format );
			$header      = $mail_helper->email_header( $cc, $bcc, $reply_to );

			//add_filter( 'wp_mail_from', array( $mail_class_obj, 'filter_wp_mail_from' ) );
			//add_filter( 'wp_mail_from_name', array( $mail_class_obj, 'filter_wp_mail_from_name' ) );
			//add_filter( 'wp_mail_content_type', array( $mail_class_obj, 'filter_mail_content_type' ) );

			$admin_email_status = $mail_helper->wp_mail( $to, $subject, $message, $header );

			//remove_filter( 'wp_mail_from', array( $mail_class_obj, 'filter_wp_mail_from' ) );
			//remove_filter( 'wp_mail_from_name', array( $mail_class_obj, 'filter_wp_mail_from_name' ) );
			//remove_filter( 'wp_mail_content_type', array( $mail_class_obj, 'filter_mail_content_type' ) );

			//we don't want to show admin related email message
			/*if ( $admin_email_status )
			{
				$return['admin'] = array( 'msg' => esc_html__( 'Admin Mail Send Successfully', 'cbxrbooking' ) );
			}*/

		}

		return $messages;
	}

	/**
	 * Send email to user after form submit based on form setting
	 *
	 * @param $messages array
	 * @param $data     array
	 * @param $meta     array
	 *
	 * @return array
	 */
	public function sendFrontendBookingUserEmailAlert( $messages, $data, $meta, $action_status = 'pending' ) {

		$settings = $meta['settings'];
		$html     = $user_email_body = $message = '';
		$return   = array();

		//send email to user
		if ( isset( $settings['cbxrbooking_email_user']['status'] ) && $settings['cbxrbooking_email_user']['status'] == 'on' ) {
			$this->mail_format = $settings['cbxrbooking_email_user']['format'];
			//$this->mail_from_address = ( $settings['cbxrbooking_email_user']['from'] != '' ) ? $settings['cbxrbooking_email_user']['from'] : get_bloginfo( 'admin_email' );
			//$this->mail_from_name    = $settings['cbxrbooking_email_user']['name'];

			$to       = str_replace( '{user_email}', $data['email'], $settings['cbxrbooking_email_user']['to'] );
			$reply_to = isset( $settings['cbxrbooking_email_user']['reply_to'] ) ? $settings['cbxrbooking_email_user']['reply_to'] : '';


			if ( $action_status == 'confirmed' ) {
				$subject            = $settings['cbxrbooking_email_user']['confirmed_subject'];
				$user_email_heading = $settings['cbxrbooking_email_user']['confirmed_heading'];
				$user_email_body    = $settings['cbxrbooking_email_user']['confirmed_body'];

				//email body syntax parsing
				$user_email_body = str_replace( '{booking_status}', CBXRBookingHelper::getBookingStatus( $data['status'] ), $user_email_body ); //replace booking status
				$user_email_body = str_replace( '{booking_code}', $data['secret'], $user_email_body ); //replace booking code
				$user_email_body = str_replace( '{user_name}', $data['name'], $user_email_body ); //replace name
				$user_email_body = str_replace( '{user_email}', $data['email'], $user_email_body ); //replace email
				$user_email_body = str_replace( '{booking_phone}', $data['phone'], $user_email_body ); //replace phone number

				$booking_date    = CBXRBookingHelper::viewDateFormat( $data['booking_date'], CBXRBookingHelper::storedFormDateFormatKey( $data['form_id'] ) );
				$user_email_body = str_replace( '{booking_date}', $booking_date, $user_email_body ); //replace date

				$booking_time             = $data['booking_time'];
				$twelve_hour_booking_time = CBXRBookingHelper::twelveHourBookingTimeFormat( $data['form_id'], $booking_time );
				if ( $twelve_hour_booking_time != '' ) {
					$booking_time = $twelve_hour_booking_time;
				}
				$user_email_body = str_replace( '{booking_time}', $booking_time, $user_email_body ); //replace time
				$user_email_body = str_replace( '{party_size}', $data['party_size'], $user_email_body ); //relace party size
				$user_email_body = str_replace( '{booking_message}', $data['message'], $user_email_body ); //replace message
			} else {
				$subject            = $settings['cbxrbooking_email_user']['new_subject'];
				$user_email_heading = $settings['cbxrbooking_email_user']['new_heading'];
				$user_email_body    = $settings['cbxrbooking_email_user']['new_body'];

				//email body syntax parsing
				$user_email_body = str_replace( '{user_name}', $data['name'], $user_email_body ); //replace name
				$user_email_body = str_replace( '{user_email}', $data['email'], $user_email_body ); //replace email

				$booking_date    = CBXRBookingHelper::viewDateFormat( $data['booking_date'], CBXRBookingHelper::storedFormDateFormatKey( $data['form_id'] ) );
				$user_email_body = str_replace( '{booking_date}', $booking_date, $user_email_body ); //replace date

				$booking_time             = $data['booking_time'];
				$twelve_hour_booking_time = CBXRBookingHelper::twelveHourBookingTimeFormat( $data['form_id'], $booking_time );
				if ( $twelve_hour_booking_time != '' ) {
					$booking_time = $twelve_hour_booking_time;
				}
				$user_email_body = str_replace( '{booking_time}', $booking_time, $user_email_body ); //replace time
				$user_email_body = str_replace( '{party_size}', $data['party_size'], $user_email_body ); //relace party size
				$user_email_body = str_replace( '{booking_phone}', $data['phone'], $user_email_body ); //replace phone number
				$user_email_body = str_replace( '{booking_message}', $data['message'], $user_email_body ); //replace message
				$user_email_body = str_replace( '{booking_ip}', $data['user_ip'], $user_email_body ); //replace booking ip
				$user_email_body = str_replace( '{booking_code}', $data['secret'], $user_email_body ); //replace booking code
				$user_email_body = str_replace( '{booking_status}', CBXRBookingHelper::getBookingStatus( $data['status'] ), $user_email_body ); //replace booking status

				$activation_link = '';
				if ( $data['activation'] != '' ) {
					//$activation_link = home_url( '/?cbxrb_verification=1&activation_code=' . $data['activation'] );
					$activation_link = add_query_arg(
						array(
							'cbxrb_verification' => 1,
							'activation_code'    => $data['activation']
						),
						home_url( '/' )
					);

					$activation_link_formatted = sprintf( __( '<p>To confirm your booking request, please verify your email address by <a href="%s">clicking this url.</a></p>', 'cbxrbooking' ), $activation_link );

					$user_email_body = str_replace( '{activation_link}', $activation_link_formatted, $user_email_body );
				} else {
					$user_email_body = str_replace( '{activation_link}', $activation_link, $user_email_body );
				}

				$cancel_link = '';
				if ( isset( $settings['cbxrbooking_cancel_booking'] ) && $settings['cbxrbooking_cancel_booking']['booking_cancel'] === 'on' ) {
					$cancel_link = add_query_arg( array(
						'cbxrb_cancel_request' => 1,
						'secret'               => $data['secret'],
					), home_url( '/' ) );

					$cancel_link_formatted = sprintf( __( '<p>To cancel booking please <a href="%s">click this url</a></p>', 'cbxrbooking' ), $cancel_link );


					$user_email_body = str_replace( '{cancel_link}', $cancel_link_formatted, $user_email_body );
				} else {
					$user_email_body = str_replace( '{cancel_link}', $cancel_link, $user_email_body );
				}
			}


			//esp = email syntax parse
			$user_email_body = apply_filters( 'cbxrbooking_esp_user_alert', $user_email_body, $messages, $data, $meta );
			//email body syntax parsing

			$user_email_body = wpautop( $user_email_body );

			$emailTemplate = new CBXRbookingMailTemplate();
			$message       = $emailTemplate->getHtmlTemplate();
			$message       = str_replace( '{mainbody}', $user_email_body, $message ); //replace mainbody
			$message       = str_replace( '{emailheading}', $user_email_heading, $message ); //replace emailbody

			if ( $this->mail_format == 'html' ) {
				$message = $emailTemplate->htmlEmeilify( $message );
			} elseif ( $this->mail_format == 'plain' ) {
				$message = $emailTemplate->htmlEmeilify( $message );
				$message = Html2TextBooking\Html2Text::convert( $message );
				$message = Html2TextBooking\Html2Text::fixNewlines( $message );
			}


			$mail_helper = new CBXRBookingMail( $this->mail_format );
			$header      = $mail_helper->email_header( '', '', $reply_to );

			$user_email_status = $mail_helper->wp_mail( $to, $subject, $message, $header );


			if ( $user_email_status === TRUE ) {

				$message    = array(
					'text' => esc_html__( 'Email sent Successfully', 'cbxrbooking' ),
					'type' => 'success'
				);
				$messages[] = $message;
			} else {
				$message    = array(
					'text' => esc_html__( 'Email sent failed. ', 'cbxrbooking' ),
					'type' => 'danger'
				);
				$messages[] = $message;
			}
		}

		return $messages;
	}

	/**
	 * Send email to admin after booking cancel request from user as per form setting
	 *
	 * @param $message     array
	 * @param $data        array
	 * @param $meta        array
	 * @param $new_status  string
	 *
	 * @return array
	 * @throws \Html2TextBooking\Html2TextException
	 */
	public function sendBookingCancelAdminEmailAlert( $messages, $data, $meta, $new_status ) {

		$settings = $meta ['settings'];

		$html = $admin_email_body = $user_email_body = $message = '';

		$data = (array) $data;


		// admin cancel alert email
		if ( isset( $settings['cbxrbooking_cancel_booking']['cancel_admin_status'] ) && $settings['cbxrbooking_cancel_booking']['cancel_admin_status'] == 'on' ) {
			$this->mail_format = $settings['cbxrbooking_cancel_booking']['format'];
			//$this->mail_from_address = ( $settings['cbxrbooking_cancel_booking']['from'] != '' ) ? $settings['cbxrbooking_cancel_booking']['from'] : get_bloginfo( 'admin_email' );
			//$this->mail_from_name    = $settings['cbxrbooking_cancel_booking']['name'];

			$to       = $settings['cbxrbooking_cancel_booking']['to'];
			$reply_to = $settings['cbxrbooking_cancel_booking']['reply_to'];
			$reply_to = str_replace( '{user_email}', $data['email'], $reply_to ); //replace email


			$subject             = $settings['cbxrbooking_cancel_booking']['subject'];
			$admin_email_heading = $settings['cbxrbooking_cancel_booking']['heading'];
			$admin_email_body    = $settings['cbxrbooking_cancel_booking']['body'];


			$admin_email_body = str_replace( '{user_name}', $data['name'], $admin_email_body ); //replace name
			$admin_email_body = str_replace( '{user_email}', $data['email'], $admin_email_body ); //replace email

			$booking_date     = CBXRBookingHelper::viewDateFormat( $data['booking_date'], CBXRBookingHelper::storedFormDateFormatKey( $data['form_id'] ) );
			$admin_email_body = str_replace( '{booking_date}', $booking_date, $admin_email_body ); //replace booking date

			$booking_time             = $data['booking_time'];
			$twelve_hour_booking_time = CBXRBookingHelper::twelveHourBookingTimeFormat( $data['form_id'], $booking_time );
			if ( $twelve_hour_booking_time != '' ) {
				$booking_time = $twelve_hour_booking_time;
			}
			$admin_email_body = str_replace( '{booking_time}', $booking_time, $admin_email_body ); //replace booking time
			$admin_email_body = str_replace( '{party_size}', $data['party_size'], $admin_email_body ); //relace party size
			$admin_email_body = str_replace( '{booking_phone}', $data['phone'], $admin_email_body ); //replace phone number
			$admin_email_body = str_replace( '{booking_message}', $data['message'], $admin_email_body ); //replace message
			$admin_email_body = str_replace( '{booking_ip}', $data['user_ip'], $admin_email_body ); //replace booking ip
			$admin_email_body = str_replace( '{booking_code}', $data['secret'], $admin_email_body ); //replace booking code
			$admin_email_body = str_replace( '{booking_status}', CBXRBookingHelper::getBookingStatus( $new_status ), $admin_email_body ); //replace booking code

			$booking_log_url           = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs&log_id=' . $data['id'] );
			$booking_log_url_formatted = sprintf( __( '<p>To check or take action about this booking request <a href="%s">click this url.</a></p>', 'cbxrbooking' ), $booking_log_url );
			$admin_email_body          = str_replace( '{booking_log_url}', $booking_log_url_formatted, $admin_email_body ); //replace booking log url for admin

			//esp = email syntax parse
			$admin_email_body = apply_filters( 'cbxrbooking_esp_cancel_admin_alert', $admin_email_body, $messages, $data, $meta, $new_status );

			$admin_email_body = wpautop( $admin_email_body );

			$emailTemplate = new CBXRbookingMailTemplate();
			$message       = $emailTemplate->getHtmlTemplate();
			$message       = str_replace( '{mainbody}', $admin_email_body, $message ); //replace mainbody
			$message       = str_replace( '{emailheading}', $admin_email_heading, $message ); //replace emailbody

			if ( $this->mail_format == 'html' ) {
				$message = $emailTemplate->htmlEmeilify( $message );
			} elseif ( $this->mail_format == 'plain' ) {
				$message = $emailTemplate->htmlEmeilify( $message );
				$message = Html2TextBooking\Html2Text::convert( $message );
				$message = Html2TextBooking\Html2Text::fixNewlines( $message );
			}

			$mail_helper = new CBXRBookingMail( $this->mail_format );
			$header      = $mail_helper->email_header( $settings['cbxrbooking_cancel_booking']['cc'], $settings['cbxrbooking_cancel_booking']['bcc'], $reply_to );

			$admin_email_status = $mail_helper->wp_mail( $to, $subject, $message, $header );


			if ( $admin_email_status ) {
				$message    = array(
					'text' => esc_html__( 'Your request to cancel booking is notified to admin.', 'cbxrbooking' ),
					'type' => 'success'
				);
				$messages[] = $message;
			}
		}//end admin email alert for cancel request

		return $messages;
	}

	/**
	 * Send email to user after booking cancel request from user as per form setting
	 *
	 * @param $message     array
	 * @param $data        array
	 * @param $meta        array
	 * @param $new_status  string
	 *
	 * @return array
	 * @throws \Html2TextBooking\Html2TextException
	 */
	public function sendBookingCancelUserEmailAlert( $messages, $data, $meta, $new_status ) {

		$settings = $meta ['settings'];

		$html = $user_email_body = $message = '';

		$data = (array) $data;

		//send email to user
		if (
			( isset( $settings['cbxrbooking_cancel_booking']['cancel_user_status_progress'] ) && $settings['cbxrbooking_cancel_booking']['cancel_user_status_progress'] == 'on' ) ||
			( isset( $settings['cbxrbooking_cancel_booking']['cancel_user_status_cancelled'] ) && $settings['cbxrbooking_cancel_booking']['cancel_user_status_cancelled'] == 'on' )
		) {
			$this->mail_format = $settings['cbxrbooking_cancel_booking']['format_user_mail'];
			//$this->mail_from_address = ( $settings['cbxrbooking_cancel_booking']['from_user_mail'] != '' ) ? $settings['cbxrbooking_cancel_booking']['from_user_mail'] : get_bloginfo( 'admin_email' );
			//$this->mail_from_name    = $settings['cbxrbooking_cancel_booking']['name_user_mail'];

			$to = str_replace( '{user_email}', $data['email'], $settings['cbxrbooking_cancel_booking']['to_user_mail'] );


			//check default cancel request status
			if ( $new_status == 'canceled' ) {
				$subject            = $settings['cbxrbooking_cancel_booking']['canceled_subject'];
				$user_email_heading = $settings['cbxrbooking_cancel_booking']['canceled_heading'];
				$user_email_body    = $settings['cbxrbooking_cancel_booking']['canceled_body'];
			} else if ( $new_status == 'cancel-request' ) {
				$subject            = $settings['cbxrbooking_cancel_booking']['cancel_request_subject'];
				$user_email_heading = $settings['cbxrbooking_cancel_booking']['cancel_request_heading'];
				$user_email_body    = $settings['cbxrbooking_cancel_booking']['cancel_request_body'];
			}


			if ( $new_status == 'canceled' || $new_status == 'cancel-request' ) {
				$user_email_body = str_replace( '{user_name}', $data['name'], $user_email_body ); //replace name
				$user_email_body = str_replace( '{user_email}', $data['email'], $user_email_body ); //replace email

				$booking_date    = CBXRBookingHelper::viewDateFormat( $data['booking_date'], CBXRBookingHelper::storedFormDateFormatKey( $data['form_id'] ) );
				$user_email_body = str_replace( '{booking_date}', $booking_date, $user_email_body ); //replace booking date

				$booking_time             = $data['booking_time'];
				$twelve_hour_booking_time = CBXRBookingHelper::twelveHourBookingTimeFormat( $data['form_id'], $booking_time );
				if ( $twelve_hour_booking_time != '' ) {
					$booking_time = $twelve_hour_booking_time;
				}
				$user_email_body = str_replace( '{booking_time}', $booking_time, $user_email_body ); //replace booking time
				$user_email_body = str_replace( '{party_size}', $data['party_size'], $user_email_body ); //relace party size
				$user_email_body = str_replace( '{booking_phone}', $data['phone'], $user_email_body ); //replace phone number
				$user_email_body = str_replace( '{booking_message}', $data['message'], $user_email_body ); //replace message
				$user_email_body = str_replace( '{booking_ip}', $data['user_ip'], $user_email_body ); //replace booking ip
				$user_email_body = str_replace( '{booking_code}', $data['secret'], $user_email_body ); //replace booking code
				$user_email_body = str_replace( '{booking_status}', CBXRBookingHelper::getBookingStatus( $new_status ), $user_email_body ); //replace booking code

				//esp = email syntax parse
				$user_email_body = apply_filters( 'cbxrbooking_esp_cancel_user_alert', $user_email_body, $messages, $data, $meta, $new_status );
			}

			$user_email_body = wpautop( $user_email_body );


			if ( ( $new_status == 'canceled' && $settings['cbxrbooking_cancel_booking']['cancel_user_status_cancelled'] == 'on' ) || ( $new_status == 'cancel-request' && $settings['cbxrbooking_cancel_booking']['cancel_user_status_progress'] == 'on' ) ) {
				$emailTemplate = new CBXRbookingMailTemplate();
				$message       = $emailTemplate->getHtmlTemplate();
				$message       = str_replace( '{mainbody}', $user_email_body, $message ); //replace mainbody
				$message       = str_replace( '{emailheading}', $user_email_heading, $message ); //replace emailbody

				if ( $this->mail_format == 'html' ) {
					$message = $emailTemplate->htmlEmeilify( $message );
				} elseif ( $this->mail_format == 'plain' ) {
					$message = $emailTemplate->htmlEmeilify( $message );
					$message = Html2TextBooking\Html2Text::convert( $message );
					$message = Html2TextBooking\Html2Text::fixNewlines( $message );
				}

				$mail_helper       = new CBXRBookingMail( $this->mail_format );
				$header            = $mail_helper->email_header( '', '', '' );
				$user_email_status = $mail_helper->wp_mail( $to, $subject, $message, $header );


				if ( $user_email_status ) {
					if ( $new_status == 'canceled' ) {
						$message    = array(
							'text' => esc_html__( 'Booking request cancellation email is sent.', 'cbxrbooking' ),
							'type' => 'success'
						);
						$messages[] = $message;
					} else if ( $new_status == 'cancel-request' ) {
						$message    = array(
							'text' => esc_html__( 'A booking cancel request pending email is sent.', 'cbxrbooking' ),
							'type' => 'success'
						);
						$messages[] = $message;
					}
				}

			}

		}//end user email alert for cancel request


		return $messages;
	}

	/**
	 * Register Widget
	 */
	public function register_widget() {
		register_widget( "CBXRBookingWidget" ); //form widget
	}

	/**
	 * Formats the header of the mail
	 *
	 * @param $cc
	 * @param $bcc
	 * @param $reply_to
	 *
	 * @return array
	 */
	/*public function email_header( $cc, $bcc, $reply_to = '' ) {
		$cc_array  = explode( ',', $cc );
		$bcc_array = explode( ',', $bcc );

		foreach ( $cc_array as $key => $cc ) {
			$headers[] = 'CC: ' . $cc;
		}

		foreach ( $bcc_array as $key => $bcc ) {
			$headers[] = 'BCC: ' . $bcc;
		}

		if ( $reply_to != '' ) {
			$headers[] = 'Reply-To: ' . $reply_to;
		}

		return $headers;
	}*/

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_register_style( 'cbxrbookingcustombootstrap', plugin_dir_url( __FILE__ ) . '../assets/css/cbxrbookingcustombootstrap.css', array(), $this->version, 'all' );
		wp_register_style( 'flatpickr', plugin_dir_url( __FILE__ ) . '../assets/vendors/flatpickr/flatpickr.min.css', array(), $this->version, 'all' );
		wp_register_style( 'cbxrbookingpublic', plugin_dir_url( __FILE__ ) . '../assets/css/cbxrbooking-public.css', array(
			'cbxrbookingcustombootstrap',
			'flatpickr'
		), $this->version, 'all' );

		do_action( 'cbxrbooking_early_enqueue_style' );
		//wp_enqueue_style('flatpickr.min');
		//wp_enqueue_style('cbxrbookingcustombootstrap');
		//wp_enqueue_style('cbxrbookingpublic');
	}//end enqueue_styles

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $wp_locale;

		wp_enqueue_script( 'jquery' );
		wp_register_script( 'jquery-validate', plugin_dir_url( __FILE__ ) . '../assets/vendors/jquery.validate.min.js', array( 'jquery' ), $this->version, TRUE );
		wp_register_script( 'flatpickr', plugin_dir_url( __FILE__ ) . '../assets/vendors/flatpickr/flatpickr.min.js', array( 'jquery' ), $this->version, TRUE );
		wp_register_script( 'cbxrbookingpublic', plugin_dir_url( __FILE__ ) . '../assets/js/cbxrbooking-public.js', array(
			'jquery',
			'jquery-validate',
			'flatpickr'
		), $this->version, TRUE );


		$flatpickr_inline_weekdays_shorthand = array_values( $wp_locale->weekday_abbrev );


		$flatpickr_inline_weekdays_longhand = array_values( $wp_locale->weekday );


		$flatpickr_inline_months_longhand = array_values( $wp_locale->month );


		$flatpickr_inline_months_shorthand = array_values( $wp_locale->month_abbrev );

		$flatpickr_inline_js = '
	        flatpickr.l10ns.en.weekdays.shorthand = ' . json_encode( $flatpickr_inline_weekdays_shorthand ) . ';	   
	        flatpickr.l10ns.en.weekdays.longhand = ' . json_encode( $flatpickr_inline_weekdays_longhand ) . ';	   
	        flatpickr.l10ns.en.months.longhand = ' . json_encode( $flatpickr_inline_months_shorthand ) . ';	   
	        flatpickr.l10ns.en.months.longhand = ' . json_encode( $flatpickr_inline_months_longhand ) . ';	   
	        flatpickr.l10ns.en.rangeSeparator = "' . esc_html__( ' to ', 'cbxrbooking' ) . '";            
            flatpickr.l10ns.en.scrollTitle = "' . esc_html__( 'Scroll to increment', 'cbxrbooking' ) . '";
            flatpickr.l10ns.en.toggleTitle = "' . esc_html__( 'Click to toggle', 'cbxrbooking' ) . '";
			';

		wp_add_inline_script( 'flatpickr', $flatpickr_inline_js, 'after' );


		// Localize the script with new data
		$translation_array = array(
			'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
			'nonce'                   => wp_create_nonce( 'rbooking_formentry' ),
			'required'                => esc_html__( 'This field is required.', 'cbxrbooking' ),
			'remote'                  => esc_html__( 'Please fix this field.', 'cbxrbooking' ),
			'email'                   => esc_html__( 'Please enter a valid email address.', 'cbxrbooking' ),
			'url'                     => esc_html__( 'Please enter a valid URL.', 'cbxrbooking' ),
			'date'                    => esc_html__( 'Please enter a valid date.', 'cbxrbooking' ),
			'dateISO'                 => esc_html__( 'Please enter a valid date ( ISO ).', 'cbxrbooking' ),
			'number'                  => esc_html__( 'Please enter a valid number.', 'cbxrbooking' ),
			'digits'                  => esc_html__( 'Please enter only digits.', 'cbxrbooking' ),
			'equalTo'                 => esc_html__( 'Please enter the same value again.', 'cbxrbooking' ),
			'maxlength'               => esc_html__( 'Please enter no more than {0} characters.', 'cbxrbooking' ),
			'minlength'               => esc_html__( 'Please enter at least {0} characters.', 'cbxrbooking' ),
			'rangelength'             => esc_html__( 'Please enter a value between {0} and {1} characters long.', 'cbxrbooking' ),
			'range'                   => esc_html__( 'Please enter a value between {0} and {1}.', 'cbxrbooking' ),
			'max'                     => esc_html__( 'Please enter a value less than or equal to {0}.', 'cbxrbooking' ),
			'min'                     => esc_html__( 'Please enter a value greater than or equal to {0}.', 'cbxrbooking' ),
			'recaptcha'               => esc_html__( 'Please check the captcha.', 'cbxrbooking' ),
			'validation_msg_required' => esc_html__( 'This field is required.', 'cbxrbooking' ),
			'validation_msg_email'    => esc_html__( 'Please enter a valid email address.', 'cbxrbooking' ),
			'forms_data'              => array()
		);
		wp_localize_script( 'cbxrbookingpublic', 'cbxrbookingformentry', $translation_array );

		do_action( 'cbxrbooking_early_enqueue_script' );
	}//end enqueue_scripts


	/**
	 * Init elementor widget
	 *
	 * @throws Exception
	 */
	public function init_elementor_widgets() {
		//include the file
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/elementor-elements/class-cbxrbooking-elemwidget.php';

		//register the widget
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new CBXBookingElemWidget\Widgets\CBXRBooking_ElemWidget() );
	}//end widgets_registered

	/**
	 * Add new category to elementor
	 *
	 * @param $elements_manager
	 */
	public function add_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'cbxrbooking',
			array(
				'title' => esc_html__( 'CBX Restaurant Booking Widgets', 'cbxrbooking' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}//end add_elementor_widget_categories

	/**
	 * Load Elementor Custom Icon
	 */
	function elementor_icon_loader() {
		wp_register_style( 'cbxrbooking_elementor_icon', CBXRBOOKING_ROOT_URL . 'widgets/elementor-elements/elementor-icon/icon.css', FALSE, CBXRBOOKING_PLUGIN_VERSION );
		wp_enqueue_style( 'cbxrbooking_elementor_icon' );
	}//end elementor_icon_loader

	/**
	 * Enqueue css early
	 */
	public function elementor_early_enqueue_style() {
		$elementor_preview = isset( $_REQUEST['elementor-preview'] ) ? intval( $_REQUEST['elementor-preview'] ) : 0;
		if ( $elementor_preview > 0 || ( is_admin() && CBXRBookingHelper::is_gutenberg_page() ) ) {
			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_style( 'cbxrbookingcustombootstrap' );
			wp_enqueue_style( 'cbxrbookingpublic' );
		}
	}//end elementor_early_enqueue_style

	/**
	 *  Enqueue js early
	 */
	public function elementor_early_enqueue_script() {
		$elementor_preview = isset( $_REQUEST['elementor-preview'] ) ? intval( $_REQUEST['elementor-preview'] ) : 0;
		if ( $elementor_preview > 0 || ( is_admin() && CBXRBookingHelper::is_gutenberg_page() ) ) {
			/*wp_enqueue_script( 'flatpickr.min' );
			wp_enqueue_script( 'jquery.validate.min' );
			wp_enqueue_script( 'cbxrbookingpublic' );*/
		}
	}//end elementor_early_enqueue_script

	/**
	 * // Before VC Init
	 */
	public function vc_before_init_actions() {
		if ( ! class_exists( 'CBXRbooking_WPBWidget' ) ) {
			require_once CBXRBOOKING_ROOT_PATH . 'widgets/vc-element/class-cbxrbooking-wpbwidget.php';
		}

		new CBXRbooking_WPBWidget();

	}//end vc_before_init_actions
}//end class CBXRBooking_Public
