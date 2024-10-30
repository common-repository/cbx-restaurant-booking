(function ($) {
	'use strict';

	jQuery(document).ready(function ($) {


		// booking entry form create/edit flatpickr js add
		$('.cbxrbookingadminform_wrapper').each(function (index, element) {

			var $form_wrapper = $(element);

			var $element    = $form_wrapper.find('.cbxrbooking-single');
			var $form_id    = parseInt($element.data('form-id'));
			var $forms_data = cbxrbookingadminlogformObj.forms_data[$form_id];


			var $schedule_weekdays       = $forms_data.schedule_weekdays;
			var $schedule_times          = $forms_data.schedule_times;
			var $exceptions_dates        = $forms_data.exceptions_dates;
			var $exceptions_times        = $forms_data.exceptions_times;
			var $early_bookings          = $forms_data.early_booking;
			var $late_bookings           = $forms_data.late_bookings;
			//var $date_pre_sel            = $forms_data.preselect_date;
			var $firstday                = $forms_data.first_day;
			var $time_interval           = $forms_data.time_interval;
			var $party_size_min          = $forms_data.party_size_min;
			var $party_size_max          = $forms_data.party_size_max;
			var $name_required           = $forms_data.name_required;
			var $email_required          = $forms_data.email_required;
			var $phone_required          = $forms_data.phone_required;
			var $success_message         = $forms_data.success_message;
			var $twenty_four_hour_format = $forms_data.twenty_four_hour_format;
			var $single_date_format      = $forms_data.single_date_format;

			$form_wrapper.find(".cbxrb_preferred_date").flatpickr({
				// dateFormat: 'M d, Y',
				//minDate: "today",

				"locale": {
					"firstDayOfWeek": $firstday // start week on Monday
				},
				altInput  : true,
				altFormat : $single_date_format,
				dateFormat: "Y-m-d",
				disableMobile: true
			});

			$form_wrapper.find(".cbxrb_preferred_time").flatpickr({
				enableTime   : true,
				noCalendar   : true,
				disableMobile: true,

				enableSeconds: false, // disabled by default

				time_24hr: $twenty_four_hour_format, // AM/PM time picker is used by default

				// default format
				dateFormat: "H:i",

				// initial values for time. don't use these to preload a date
				defaultHour  : 12,
				defaultMinute: 0,

				// Preload time with defaultDate instead:
				// defaultDate: "3:30"
			});


			var $formvalidator = $element.validate({
				errorPlacement: function (error, element) {
					error.appendTo(element.parents('.cbxrbooking-error-msg-show'));
				},
				errorElement  : 'p',
				rules         : {
					/*cbxrb_formid        : {
						required: true,
					},
					cbxrb_email         : {
						//required: true,
						email   : true
					},*/
					cbxrb_preferred_date: {
						required: true
					},
					cbxrb_preferred_time: {
						required: true
					}
				},
				messages      : {
					/*cbxrb_formid        : {
						required: cbxrbookingadminlogformObj.required
					},
					cbxrb_email         : {
						//required: cbxrbookingadminlogformObj.required,
						email   : cbxrbookingadminlogformObj.email
					},
					cbxrb_preferred_date: {
						required: cbxrbookingadminlogformObj.required
					},
					cbxrb_preferred_time: {
						required: cbxrbookingadminlogformObj.required
					}*/
				}
			});

			// prevent double click form submission


			$element.submit(function (e) {
				var $form = $(this);

				var error_msg   = '';
				var success_msg = '';

				if ($formvalidator.valid()) {
					e.preventDefault();
					$element.find('.cbxrb-actionbutton').prop("disabled", true);

					$form_wrapper.find('.cbxrbooking-success-messages').empty();
					$form_wrapper.find('.cbxrbooking-error-messages').empty();

					$element.find('.cbxrbooking_ajax_icon').show();
					var data = $form.serialize();

					// process the form
					var request = $.ajax({
						type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
						url: cbxrbookingadminlogformObj.ajaxurl, // the url where we want to POST
						data: data + '&action=rbooking_backend_entrysubmit_action', // our data object
						security: cbxrbookingadminlogformObj.nonce,
						dataType: 'json' // what type of data do we expect back from the server
					});
					request.done(function (data) {


						if ($.isEmptyObject(data.error)) {
							$element.find('.cbxrbooking_ajax_icon').hide();
							$element.find('.cbxrb-actionbutton').prop("disabled", false);

							var $messages = data.success.messages;
							//var $show_form = parseInt(data.success.show_form);


							$('#cbxrb_booking_id').val(data.success.booking_id);
							$element.find('.cbxrb-actionbutton').text(cbxrbookingadminlogformObj.update_booking_label);

							$.each($messages, function (key, $message) {
								$form_wrapper.find('.cbxrbooking-success-messages').append('<p class="alert alert-' + $message['type'] + '">' + $message['text'] + '</p>');
							});

							/*if ($show_form === 1) {
								//$formvalidator.resetForm();
								//$element[0].reset();
							} else {
								$element.remove();
							}*/

						} else {
							//validation errors
							$.each(data.error, function (key, valueObj) {
								$.each(valueObj, function (key2, valueObj2) {

									if (key === 'top_errors') {
										if (typeof valueObj2 === 'object') {
											$.each(valueObj2, function (key3, valueObj3) {
												//var error_msg_for_hidden_type = '<p class="alert alert-danger" id="' + key + "-error" + '">' + valueObj3 + '</p>';
												$form_wrapper.find('.cbxrbooking-error-messages').prepend('<p class="alert alert-danger" id="' + key + "-error" + '">' + valueObj3 + '</p>');
											});
										} else {
											//var error_msg_for_hidden_type = '<p class="alert alert-danger" id="' + key + "-error" + '">' + valueObj2 + '</p>';
											$form_wrapper.find('.cbxrbooking-error-messages').prepend('<p class="alert alert-danger" id="' + key + "-error" + '">' + valueObj2 + '</p>');
										}

									}


									if ($element.find("#" + key + '_' + $form_id).attr('type') == 'hidden') {
										//for hidden field show at top
										//var error_msg_for_hidden_type = '<p class="alert alert-danger" id="' + key + "-error" + '">' + valueObj2 + '</p>';
										$form_wrapper.find('.cbxrbooking-error-msg-show').prepend('<p class="alert alert-danger" id="' + key + "-error" + '">' + valueObj2 + '</p>');
									} else {
										//for regular field show after field
										//$element.find("#" + key + '_' + $form_id).after('<p class="error" id="' + key + "-error" + '">' + valueObj2 + '</p>');

										$element.find("#" + key + '_' + $form_id).addClass('error');
										$element.find("#" + key + '_' + $form_id).remove('valid');

										var $field_parent = $element.find("#" + key + '_' + $form_id).closest('.cbxrbooking-error-msg-show');
										if ($field_parent.find('p.error').length > 0) {
											$field_parent.find('p.error').html(valueObj2).show();

										} else {
											$('<p for="' + key + '_' + $form_id + '" class="error">' + valueObj2 + '</p>').appendTo($field_parent);

										}
									}

									/*if (Object.keys(valueObj).length > 1) {
                                        return false;
                                    }*/
								});
							});
						}

						$element.find('.cbxrbooking_ajax_icon').hide();
						$element.find('.cbxrb-actionbutton').prop("disabled", false);
					});

					request.fail(function (jqXHR, textStatus) {
						$element.find('.cbxrbooking_ajax_icon').hide();
					});
				}
			});
		}); //end each form

	});

})(jQuery);
