<?php
/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the booking form
 *
 * @link       http://codeboxr.com
 * @since      1.0.7
 *
 * @package    cbxrbooking
 * @subpackage cbxrbooking/admin/templates
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<?php
global $wp;

$schedule_weekdays       = $schedule_times = array();
$schedule_event          = null;
$twenty_four_hour_format = true;

$single_date_format = CBXRBookingHelper::getSingleDateFormat( CBXRBookingHelper::storedFormDateFormatKey( $form_id ) );

if ( isset( $meta['settings']['cbxrbooking_booking_schedule'] ) ) {
	$cbxrbooking_booking_schedule_settings = $meta['settings']['cbxrbooking_booking_schedule'];


	// booking schedule settings
	$schedule_event = isset( $cbxrbooking_booking_schedule_settings['schedule_event'] ) ? $cbxrbooking_booking_schedule_settings['schedule_event'] : null;

	if ( isset( $cbxrbooking_booking_schedule_settings['time_format'] ) && intval( $cbxrbooking_booking_schedule_settings['time_format'] ) == 12 ) {
		$twenty_four_hour_format = false;
	}
}


if ( ! is_null( $schedule_event ) ) {
	foreach ( $schedule_event as $key => $arr_values ) {
		if ( isset( $arr_values['weekdays'] ) ) {
			$schedule_weekdays = array_merge( $schedule_weekdays, $arr_values['weekdays'] );
			foreach ( $arr_values['weekdays'] as $index => $week_day_number ) {
				if ( $arr_values['times']['start'] != '' || $arr_values['times']['end'] != '' ) {
					$schedule_times[ $week_day_number ] = $arr_values['times'];
				} else {
					$schedule_times[ $week_day_number ] = '';
				}
			}
		}
	}
	$schedule_weekdays = array_unique( $schedule_weekdays );
}

// booking schedule exceptions settings
$scheduler_exceptions = isset( $cbxrbooking_booking_schedule_settings['scheduler_exceptions'] ) ? $cbxrbooking_booking_schedule_settings['scheduler_exceptions'] : null;
$exceptions_dates     = $exceptions_times = array();
if ( ! is_null( $scheduler_exceptions ) ) {
	foreach ( $scheduler_exceptions as $index => $valArr ) {
		if ( isset( $valArr['date'] ) && $valArr['date'] != '' ) {

			if ( ( $key = array_search( $valArr['date'], $exceptions_dates ) ) !== false ) {
				unset( $exceptions_dates[ $key ] );
			}

			array_push( $exceptions_dates, $valArr['date'] );

			if ( $valArr['times']['start'] != '' || $valArr['times']['end'] != '' ) {
				$exceptions_times[ $valArr['date'] ] = $valArr['times'];
			} else {
				$exceptions_times[ $valArr['date'] ] = '';
			}
		}
	}
}

// Localize the script with new data
$translation_array = array(
	'schedule_weekdays'       => $schedule_weekdays,
	'schedule_times'          => $schedule_times,
	'exceptions_dates'        => $exceptions_dates,
	'exceptions_times'        => $exceptions_times,
	'early_booking'           => $meta['settings']['cbxrbooking_booking_schedule']['early_bookings'],
	'late_booking'            => $meta['settings']['cbxrbooking_booking_schedule']['late_bookings'],
	//'preselect_date'          => $meta['settings']['cbxrbooking_booking_schedule']['date_pre_selection'],
	'time_interval'           => $meta['settings']['cbxrbooking_booking_schedule']['time_interval'],
	'first_day'               => $meta['settings']['cbxrbooking_booking_schedule']['week_starts_on'],
	'party_size_min'          => $meta['settings']['cbxrbooking_style']['min_party_size'],
	'party_size_max'          => $meta['settings']['cbxrbooking_style']['max_party_size'],
	'name_required'           => $meta['settings']['cbxrbooking_style']['require_name'],
	'email_required'          => $meta['settings']['cbxrbooking_style']['require_email'],
	'phone_required'          => $meta['settings']['cbxrbooking_style']['require_phone'],
	'success_message'         => json_encode( $meta['settings']['cbxrbooking_style']['success_message'] ),
	'twenty_four_hour_format' => $twenty_four_hour_format,
	'single_date_format'      => $single_date_format
);


$inline_js = '
            cbxrbookingadminlogformObj.forms_data[' . $form_id . '] = ' . json_encode( $translation_array ) . ';             
        ';

wp_add_inline_script( 'cbxrbookingadminlogform', $inline_js, 'before' );


//$meta = get_post_meta( $form_id, '_cbxrbookingmeta', true );

if ( $log_id == 0 ) {

	$log_data               = new stdClass();
	$log_data->id           = 0;
	$log_data->name         = '';
	$log_data->email        = '';
	$log_data->phone        = '';
	$log_data->booking_date = '';
	$log_data->booking_time = '';
	$log_data->party_size   = '';
	$log_data->message      = '';
	$log_data->secret       = '';
	$log_data->status       = isset( $meta['settings']['cbxrbooking_style']['default_state'] ) ? $meta['settings']['cbxrbooking_style']['default_state'] : 'pending'; //default booking method
	$booking_id             = 0;
}


$booking_id = $log_data->id;


?>
<?php do_action( 'cbxrbooking_admin_logform_before', $form_id, $booking_id ); ?>

    <div class="wrap">
        <h2>
			<?php
			esc_html_e( 'Restaurant Booking: ', 'cbxrbooking' );
			if ( $log_id > 0 ) {
				esc_html_e( 'Edit Booking ', 'cbxrbooking' );
				echo $booking_id;
			} else {
				esc_html_e( 'Add Booking ', 'cbxrbooking' );
			}
			?>
        </h2>

        <p>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs' ) ); ?>"
               class="button button-primary" role="button"><?php esc_html_e( 'Go Back to List', 'cbxrbooking' ) ?></a>
			<?php if ( $booking_id > 0 ): ?>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs&log_id=0&form_id=' . $form_id ) ); ?>"
                   class="button button-default" role="button"><?php esc_html_e( 'Add New Booking', 'cbxrbooking' ) ?></a>
			<?php endif; ?>
        </p>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder">
                <div id="post-body-content">
                    <div class="meta-box-sortables ui-sortable">
                        <div class="postbox">
                            <div class="inside">
                                <div class="cbxrbookingcustombootstrap cbxrbooking_wrapper cbxrbookingadminform_wrapper" data-formid="<?php echo $form_id; ?>">
									<?php

									//$show_form = true;
									/*if ( isset( $backend_validation_success_trans[ $form_id ] ) ) {
										$backend_validation_success_trans_forms = $backend_validation_success_trans[ $form_id ];
										$show_form                            = ( isset( $backend_validation_success_trans_forms['show_form'] ) && $backend_validation_success_trans_forms['show_form'] === 1 ) ? true : false;

										if ( isset( $backend_validation_success_trans_forms['messages'] ) && sizeof( $backend_validation_success_trans_forms['messages'] ) > 0 ) {
											$messages = $backend_validation_success_trans_forms['messages'];
											foreach ( $messages as $message ) {
												echo '<div class="alert alert-' . $message['type'] . '" role="alert"><p>' . $message['text'] . '</p></div>';
											}
										}
									}*/

									/*if ( array_key_exists( 'cbxrbooking_backend_validation_success', $_SESSION ) && isset( $_SESSION['cbxrbooking_backend_validation_success'][ $form_id ] ) ) {

										if ( isset( $_SESSION['cbxrbooking_backend_validation_success'][ $form_id ]['show_form'] ) && ( $_SESSION['cbxrbooking_backend_validation_success'][ $form_id ]['show_form'] === 0 ) ) {
											//$hide_form = true;
											$show_form = false;
										}
										if ( isset( $_SESSION['cbxrbooking_backend_validation_success'][ $form_id ]['messages'] ) && sizeof( $_SESSION['cbxrbooking_backend_validation_success'][ $form_id ]['messages'] ) > 0 ) {
											$messages = $_SESSION['cbxrbooking_backend_validation_success'][ $form_id ]['messages'];
											foreach ( $messages as $message ) {
												echo '<div class="alert alert-' . $message['type'] . '" role="alert"><p>' . $message['text'] . '</p></div>';
											}
										}
									}*/
									?>

                                    <div class="cbxrbooking-success-messages">
                                    </div>
                                    <div class="cbxrbooking-error-messages">
                                    </div>

									<?php if ( $meta['settings']['cbxrbooking_style']['text_beforeform'] != '' ) : ?>
                                        <div class="rbooking-text-before-form rbooking--admin-text-before-form">
											<?php echo $meta['settings']['cbxrbooking_style']['text_beforeform']; ?>
                                        </div>
                                        <br/>
									<?php endif; ?>
                                    <form id="rbooking_entryform_<?php echo $form_id; ?>"
                                          class="form-horizontal cbxrbooking-single rbooking_entryform rbooking_entryform_<?php echo $form_id; ?>"
                                          method="post" data-form-id="<?php echo $form_id; ?>"
                                          action="<?php admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs&log_id=' . $booking_id ) ?>">

										<?php /*if ( $validation_errors_status ) { */?><!--
                                            <div class="cbxrbooking-error text-center">
												<?php
/*												if ( array_key_exists( 'top_errors', $validation_errors ) ) {
													$top_errors = $validation_errors['top_errors'];
													if ( is_array( $top_errors ) && sizeof( $top_errors ) ) {
														foreach ( $top_errors as $key => $val ) {
															$val = array_values( $val );
															foreach ( $val as $error_text ) {
																echo '<div class="alert alert-danger" role="alert"><p>' . $error_text . '</p></div>';
															}
														}
													}
												}
												*/?>
                                            </div>
										--><?php /*} */?>



										<?php do_action( 'cbxrbooking_admin_logform_start', $form_id, $booking_id ) ?>

                                        <div class="form-group">
                                            <label for="cbxrb_name_<?php echo $form_id; ?>"
                                                   class="col-sm-2 control-label"><?php esc_html_e( 'Name', 'cbxrbooking' ) ?></label>
                                            <div class="col-sm-4 cbxrbooking-error-msg-show">
                                                <input type="text" class="form-control cbxrb_name" name="cbxrb_name"
                                                       id="cbxrb_name_<?php echo $form_id; ?>"
                                                       value="<?php echo $log_data->name; ?>"
                                                       placeholder="<?php esc_html_e( 'Name', 'cbxrbooking' ) ?>" <?php if ( $meta['settings']['cbxrbooking_style']['require_name'] === 'name-yes' ) {
													echo 'required';
												} ?> />

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="cbxrb_email_<?php echo $form_id; ?>"
                                                   class="col-sm-2 control-label"><?php esc_html_e( 'Email', 'cbxrbooking' ) ?></label>
                                            <div class="col-sm-4 cbxrbooking-error-msg-show">
                                                <input type="text" class="form-control cbxrb_email" name="cbxrb_email"
                                                       id="cbxrb_email_<?php echo $form_id; ?>"
                                                       value="<?php echo $log_data->email; ?>"
                                                       placeholder="<?php esc_html_e( 'Email', 'cbxrbooking' ) ?>" <?php if ( $meta['settings']['cbxrbooking_style']['require_email'] === 'email-yes' ) {
													echo 'required';
												} ?> />

                                            </div>
                                        </div>
                                        <div class="form-group">

                                            <label for="cbxrb_preferred_date_<?php echo $form_id; ?>"
                                                   class="col-sm-2 control-label"><?php esc_html_e( 'Date', 'cbxrbooking' ) ?></label>
                                            <div class="col-sm-4 cbxrbooking-error-msg-show">
                                                <input type="text" class="form-control cbxrb_preferred_date"
                                                       name="cbxrb_preferred_date"
                                                       id="cbxrb_preferred_date_<?php echo $form_id; ?>"
                                                       value="<?php echo $log_data->booking_date; ?>"
                                                       placeholder="<?php esc_html_e( 'Date', 'cbxrbooking' ) ?>" required/>


                                            </div>

                                        </div>
                                        <div class="form-group">

                                            <label for="cbxrb_preferred_time_<?php echo $form_id; ?>"
                                                   class="col-sm-2 control-label"><?php esc_html_e( 'Time', 'cbxrbooking' ) ?></label>
                                            <div class="col-sm-4 cbxrbooking-error-msg-show">
                                                <input type="text" class="form-control cbxrb_preferred_time"
                                                       name="cbxrb_preferred_time"
                                                       id="cbxrb_preferred_time_<?php echo $form_id; ?>"
                                                       value="<?php echo $log_data->booking_time; ?>"
                                                       placeholder="<?php esc_html_e( 'Time', 'cbxrbooking' ) ?>" required/>

                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label for="cbxrb_party_<?php echo $form_id; ?>"
                                                   class="col-sm-2 control-label"><?php esc_html_e( 'Party Size', 'cbxrbooking' ) ?></label>
                                            <div class="col-sm-4 cbxrbooking-error-msg-show">
                                                <select class="form-control " name="cbxrb_party" id="cbxrb_party_<?php echo $form_id; ?>" required>
													<?php
													for ( $party = 1; $party <= 100; $party ++ ) { ?>
                                                        <option <?php if ( $log_data->party_size == $party ) {
															echo 'selected';
														} ?> value="<?php echo $party; ?>">
															<?php echo $party; ?>
                                                        </option>
														<?php
													}
													?>
                                                </select>

                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="cbxrb_phone_<?php echo $form_id; ?>"
                                                   class="col-sm-2 control-label"><?php esc_html_e( 'Phone', 'cbxrbooking' ) ?></label>
                                            <div class="col-sm-4 cbxrbooking-error-msg-show">
                                                <input type="text" class="form-control cbxrb_phone" name="cbxrb_phone"
                                                       id="cbxrb_phone_<?php echo $form_id; ?>"
                                                       value="<?php echo $log_data->phone; ?>"
                                                       placeholder="<?php esc_html_e( 'Phone', 'cbxrbooking' ) ?>" <?php if ( $meta['settings']['cbxrbooking_style']['require_phone'] === 'phone-yes' ) {
													echo 'required';
												} ?> />

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="cbxrb_message_<?php echo $form_id; ?>"
                                                   class="col-sm-2 control-label"><?php esc_html_e( 'Message', 'cbxrbooking' ) ?></label>
                                            <div class="col-sm-4">
                                               <textarea class="form-control cbxrb_message" name="cbxrb_message"
                                                         id="cbxrb_message_<?php echo $form_id; ?>" rows="4"
                                                         placeholder="<?php esc_html_e( 'Message', 'cbxrbooking' ) ?>"><?php echo stripslashes( $log_data->message ); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="cbxrb_status"
                                                   class="col-sm-2 control-label"><?php esc_html_e( 'Status', 'cbxrbooking' ) ?></label>
                                            <div class="col-sm-4 cbxrbooking-error-msg-show">
                                                <select class="form-control" name="cbxrb_status" id="cbxrb_status">
													<?php
													$all_booking_status                   = CBXRBookingHelper::getAllBookingStatus();
													$all_booking_status['cancel-request'] = esc_html__( 'Cancel Request', 'cbxrbooking' );
													foreach ( $all_booking_status as $key => $value ) { ?>
                                                        <option <?php if ( $log_data->status === $key )
															echo 'selected' ?>
                                                                value="<?php echo $key; ?>"><?php echo stripslashes( $value ); ?></option>
													<?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-4 cbxrbooking-error-msg-show">
                                                <div class="checkbox">
                                                    <label for="cbxrb_email_notify_<?php echo $form_id; ?>"
                                                           class="control-label">
                                                        <input type="checkbox" class="cbxrb_email_notify"
                                                               name="cbxrb_email_notify"
                                                               id="cbxrb_email_notify_<?php echo $form_id; ?>"
                                                               value="1"/>
                                                        <span class="notif_alert_text"><?php esc_html_e( 'Notify User by Email', 'cbxrbooking' ); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

										<?php do_action( 'cbxrbooking_admin_logform_end', $form_id, $booking_id, $log_data ) ?>

                                        <input type="hidden" name="cbxrb_formid" value="<?php echo $form_id; ?>"/>
                                        <input type="hidden" name="cbxrb_booking_id" id="cbxrb_booking_id" value="<?php echo $booking_id; ?>"/>
                                        <input type="hidden" name="rbooking_backend_entrysubmit" value="1"/>
										<?php wp_nonce_field( 'rbooking_formentry', 'rbooking_token' ); ?>

                                        <div class="form-group text-center">
                                            <div class="col-sm-6">

                                                <button type="submit"
                                                        class="btn btn-default btn-primary cbxrb-actionbutton"><?php echo ( $booking_id == 0 ) ? esc_html__( 'Add Booking', 'cbxrbooking' ) : esc_html__( 'Update Booking', 'cbxrbooking' ); ?></button>

												<?php $cbx_ajax_icon = CBXRBOOKING_ROOT_URL . 'assets/images/busy.gif'; ?>
                                                <span data-busy="0" class="cbxrbooking_ajax_icon" style="display: none">
                                                <img src="<?php echo $cbx_ajax_icon; ?>"/>
                                            </span>
                                            </div>

                                        </div>
                                        <div class="alert alert-info">
                                            <p>
												<?php
												if ( $booking_id > 0 ) {
													echo __( 'Email notification is sent to user if status is changed to <code>Confirmed</code> and <code>Cancelled</code> from any previous different status. Email is sent as per form setting.', 'cbxrbooking' );
												} else {
													echo __( 'Email notification is sent to user if status is set to <code>Confirmed</code> and <code>Pending</code>. Email is sent as per form setting.', 'cbxrbooking' );
												}
												?>
                                            </p>
                                        </div>
                                    </form>
									<?php if ( $meta['settings']['cbxrbooking_style']['text_afterform'] != '' ) : ?>
                                        <div class="rbooking-text-after-form rbooking-admin-text-after-form"><?php echo $meta['settings']['cbxrbooking_style']['text_afterform']; ?></div>
									<?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clear clearfix"></div>
        </div>
    </div>

<?php do_action( 'cbxrbooking_admin_logform_after', $form_id, $booking_id );