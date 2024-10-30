<?php
/**
 * Provide a public view for the plugin
 *
 * This file is used to markup the public facing form
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    cbxrbooking
 * @subpackage cbxrbooking/public/templates
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<?php

global $wp;

$current_user_info = NULL;
if ( is_user_logged_in() ) {
	$current_user_info = wp_get_current_user()->data;
}


$current_url = home_url( add_query_arg( NULL, NULL ) );

$privacy_policy_page_link = function_exists( 'get_the_privacy_policy_link' ) ? get_privacy_policy_url() : '#';

$booking_id     = 0;
$booking_secret = 0;

$meta = get_post_meta( $form_id, '_cbxrbookingmeta', TRUE );

$schedule_weekdays       = $schedule_times = array();
$schedule_event          = NULL;
$twenty_four_hour_format = TRUE;

$single_date_format = CBXRBookingHelper::getSingleDateFormat( CBXRBookingHelper::storedFormDateFormatKey( $form_id ) );

if ( isset( $meta['settings']['cbxrbooking_booking_schedule'] ) ) {

	$cbxrbooking_booking_schedule_settings = $meta['settings']['cbxrbooking_booking_schedule'];

	// booking schedule settings
	$schedule_event = isset( $cbxrbooking_booking_schedule_settings['schedule_event'] ) ? $cbxrbooking_booking_schedule_settings['schedule_event'] : NULL;

	if ( isset( $cbxrbooking_booking_schedule_settings['time_format'] ) && intval( $cbxrbooking_booking_schedule_settings['time_format'] ) == 12 ) {
		$twenty_four_hour_format = FALSE;
	}
}


if ( ! is_null( $schedule_event ) && is_array( $schedule_event ) ) {
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
$scheduler_exceptions = isset( $cbxrbooking_booking_schedule_settings['scheduler_exceptions'] ) ? $cbxrbooking_booking_schedule_settings['scheduler_exceptions'] : NULL;
$exceptions_dates     = $exceptions_times = array();
if ( ! is_null( $scheduler_exceptions ) && is_array( $scheduler_exceptions ) ) {
	foreach ( $scheduler_exceptions as $index => $valArr ) {
		if ( isset( $valArr['date'] ) && $valArr['date'] != '' ) {
			if ( ( $key = array_search( $valArr['date'], $exceptions_dates ) ) !== FALSE ) {
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
	    cbxrbookingformentry.forms_data[' . $form_id . '] = ' . json_encode( $translation_array ) . ';	   
	';

wp_add_inline_script( 'cbxrbookingpublic', $inline_js, 'before' );


echo '<div class="cbxrbookingcustombootstrap cbxrbooking_wrapper cbxrbookingform_wrapper cbxrbooking_wrapper_' . $scope . '" data-form-id="' . $form_id . '">';
?>

<?php do_action( 'cbxrbooking_public_logform_before', $form_id, $booking_id ) ?>


<?php $show_form = true; ?>
    <div class="cbxrbooking-success-messages">
    </div>
    <div class="cbxrbooking-error-messages">
    </div>


<?php if ( $show_form ) : ?>
	<?php if ( $meta['settings']['cbxrbooking_style']['text_beforeform'] != '' ) : ?>
        <div class="rbooking-text-before-form"><?php echo $meta['settings']['cbxrbooking_style']['text_beforeform']; ?></div>
        <br/>
	<?php endif; ?>
    <form id="rbooking_entryform_<?php echo $form_id; ?>"
          class="form-horizontal cbxrbooking-single rbooking_entryform rbooking_entryform_<?php echo $form_id; ?>"
          method="post" data-form-id="<?php echo $form_id; ?>" action="<?php echo esc_url( $current_url ); ?>">


		<?php do_action( 'cbxrbooking_public_logform_start', $form_id, $booking_id ) ?>

        <div class="form-group">
            <label for="cbxrb_name_<?php echo $form_id; ?>"
                   class="cbxrb-label col-sm-2 control-label"><?php esc_html_e( 'Name', 'cbxrbooking' ) ?></label>
            <div class="cbxrb-input-wrapper col-sm-4 cbxrbooking-error-msg-show">
                <input type="text" class="form-control cbxrb_name cbxrb_name_<?php echo $form_id; ?>" name="cbxrb_name"
                       id="cbxrb_name_<?php echo $form_id; ?>"
                       value="<?php echo ! is_null( $current_user_info ) ? $current_user_info->display_name : ''; ?>"
                       placeholder="<?php esc_html_e( 'Name', 'cbxrbooking' ) ?>" <?php if ( $meta['settings']['cbxrbooking_style']['require_name'] === 'name-yes' ) {
					echo 'required';
				} ?> />

            </div>
            <label for="cbxrb_email_<?php echo $form_id; ?>"
                   class="cbxrb-label col-sm-2 control-label"><?php esc_html_e( 'Email', 'cbxrbooking' ) ?></label>
            <div class="cbxrb-input-wrapper col-sm-4 cbxrbooking-error-msg-show">
                <input type="email" class="form-control cbxrb_email cbxrb_email_<?php echo $form_id; ?>" name="cbxrb_email"
                       id="cbxrb_email_<?php echo $form_id; ?>"
                       value="<?php echo ! is_null( $current_user_info ) ? $current_user_info->user_email : ''; ?>"
                       placeholder="<?php esc_html_e( 'Email', 'cbxrbooking' ) ?>" <?php if ( $meta['settings']['cbxrbooking_style']['require_email'] === 'email-yes' ) {
					echo 'required';
				} ?> />

            </div>
        </div>
        <div class="form-group">
            <label for="cbxrb_preferred_date_<?php echo $form_id; ?>"
                   class="cbxrb-label col-sm-2 control-label"><?php esc_html_e( 'Date', 'cbxrbooking' ) ?></label>
            <div class="cbxrb-input-wrapper col-sm-4 cbxrbooking-error-msg-show">
                <input type="text" class="form-control cbxrb_preferred_date cbxrb_preferred_date_<?php echo $form_id; ?>" name="cbxrb_preferred_date"
                       id="cbxrb_preferred_date_<?php echo $form_id; ?>" value="<?php echo ''; ?>"
                       placeholder="<?php esc_html_e( 'Date', 'cbxrbooking' ) ?>" required/>


            </div>

            <label for="cbxrb_preferred_time_<?php echo $form_id; ?>"
                   class="cbxrb-label col-sm-2 control-label"><?php esc_html_e( 'Time', 'cbxrbooking' ) ?></label>
            <div class="col-sm-4 cbxrbooking-error-msg-show">
                <input type="text" class="cbxrb-input-wrapper form-control cbxrb_preferred_time cbxrb_preferred_time_<?php echo $form_id; ?>" name="cbxrb_preferred_time"
                       id="cbxrb_preferred_time_<?php echo $form_id; ?>" value="<?php echo ''; ?>"
                       placeholder="<?php esc_html_e( 'Time', 'cbxrbooking' ) ?>"
                       data-time-interval="<?php echo $meta['settings']['cbxrbooking_booking_schedule']['time_interval']; ?>" required/>

            </div>
        </div>

        <div class="form-group">
            <label for="cbxrb_party_<?php echo $form_id; ?>"
                   class="cbxrb-label col-sm-2 control-label"><?php esc_html_e( 'Party Size', 'cbxrbooking' ) ?></label>
            <div class="cbxrb-input-wrapper col-sm-4 cbxrbooking-error-msg-show">
                <select class="form-control cbxrb_party cbxrb_party_<?php echo $form_id; ?> " name="cbxrb_party" id="cbxrb_party_<?php echo $form_id; ?>" required>
					<?php
					$min_party_size = intval( $meta['settings']['cbxrbooking_style']['min_party_size'] );
					$max_party_size = $meta['settings']['cbxrbooking_style']['max_party_size'] != '' ? intval( $meta['settings']['cbxrbooking_style']['max_party_size'] ) : 100;
					// swap party size if min > max
					if ( $min_party_size > $max_party_size ) {
						$swap           = $max_party_size;
						$max_party_size = $min_party_size;
						$min_party_size = $swap;
					}

					for ( $party = $min_party_size; $party <= $max_party_size; $party ++ ) { ?>
                        <option <?php if ( isset( $_REQUEST['party'] ) && intval( $_REQUEST['party'] ) == $party )
							echo 'selected' ?>
                                value="<?php echo $party; ?>"><?php echo $party; ?></option>
						<?php
					}
					?>
                </select>

            </div>
            <label for="cbxrb_phone_<?php echo $form_id; ?>"
                   class="cbxrb-label col-sm-2 control-label"><?php esc_html_e( 'Phone', 'cbxrbooking' ) ?></label>
            <div class="cbxrb-input-wrapper col-sm-4 cbxrbooking-error-msg-show">
                <input type="text" class="form-control cbxrb_phone cbxrb_phone_<?php echo $form_id; ?>" name="cbxrb_phone"
                       id="cbxrb_phone_<?php echo $form_id; ?>" value="<?php echo ''; ?>"
                       placeholder="<?php esc_html_e( 'Phone', 'cbxrbooking' ) ?>" <?php if ( $meta['settings']['cbxrbooking_style']['require_phone'] === 'phone-yes' ) {
					echo 'required';
				} ?> />

            </div>
        </div>

        <div class="form-group">
            <label for="cbxrb_message_<?php echo $form_id; ?>"
                   class="cbxrb-label col-sm-2 control-label"><?php esc_html_e( 'Message', 'cbxrbooking' ) ?></label>
            <div class="cbxrb-input-wrapper col-sm-10">
               <textarea name="cbxrb_message" class="form-control cbxrb_message cbxrb_message_<?php echo $form_id; ?>" id="cbxrb_message_<?php echo $form_id; ?>" rows="4" cols="50"
                         placeholder="<?php esc_html_e( 'Message', 'cbxrbooking' ) ?>"></textarea>
            </div>
        </div>

		<?php do_action( 'cbxrbooking_public_logform_end', $form_id, $booking_id, array() ) ?>

		<?php if ( isset( $meta['settings']['cbxrbooking_style']['user_consent'] ) && $meta['settings']['cbxrbooking_style']['user_consent'] == 'on' ) : ?>
            <div class="form-group">
                <div class="cbxrb-input-wrapper cbxrb-input-wrapper-privacy col-sm-12 cbxrbooking-error-msg-show">
                    <label for="cbxrb_privacy_<?php echo $form_id; ?>" class="cbxrb-label col-sm-12 control-label">
                        <input required type="checkbox" name="cbxrb_privacy" id="cbxrb_privacy_<?php echo $form_id; ?>" class="cbxrb_privacy" value="on"/>
						<?php echo sprintf( __( 'YES, I agree with <a target="_blank" href="%s">Privacy policy</a>', 'cbxrbooking' ), apply_filters( 'cbxrbooking_privacy_policy_page_link', $privacy_policy_page_link ) ); ?>
                    </label>
                </div>

            </div>
		<?php endif; ?>


        <input type="hidden" name="cbxrb_formid" value="<?php echo $form_id; ?>"/>
        <input type="hidden" name="cbxrb_booking_id" value="<?php echo $booking_id; ?>"/>
        <input type="hidden" name="rbooking_frontend_entrysubmit" value="1"/>
        <input type="hidden" name="rbooking_frontend_url" value="<?php echo esc_url( $current_url ); ?>"/>
		<?php wp_nonce_field( 'rbooking_formentry', 'rbooking_token' ); ?>
        <div class="form-group text-center">
            <button type="submit"
                    class="btn btn-default btn-primary cbxrb-actionbutton"><?php esc_html_e( 'Submit', 'cbxrbooking' ) ?></button>

			<?php $cbx_ajax_icon = CBXRBOOKING_ROOT_URL . 'assets/images/busy.gif'; ?>
            <span data-busy="0" class="cbxrbooking_ajax_icon" style="display: none">
                <img src="<?php echo $cbx_ajax_icon; ?>"/>
            </span>
        </div>
    </form>
	<?php if ( $meta['settings']['cbxrbooking_style']['text_afterform'] != '' ) : ?>
        <div class="rbooking-text-after-form"><?php echo $meta['settings']['cbxrbooking_style']['text_afterform']; ?></div>
	<?php endif; ?>

<?php endif; ?>

<?php if ( ( $meta['settings']['cbxrbooking_misc']['show_credit'] === 'yes' ) ) : ?>
    <div class="pull-right">
		<?php esc_html_e( 'Powered by ', 'cbxrbooking' ) ?>
        <a href="https://codeboxr.com" target="_blank">
			<?php esc_html_e( 'Codeboxr', 'cbxrbooking' ) ?>
        </a>
    </div>
<?php endif; ?>


<?php

do_action( 'cbxrbooking_public_logform_after', $form_id, $booking_id );
echo '</div>';