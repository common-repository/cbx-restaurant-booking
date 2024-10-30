(function ($) {
    'use strict';

    //console.log(cbxrbookingformentry);

    jQuery(document).ready(function ($) {
        $.extend($.validator.messages, {
            required: cbxrbookingformentry.required,
            remote: cbxrbookingformentry.remote,
            email: cbxrbookingformentry.email,
            url: cbxrbookingformentry.url,
            date: cbxrbookingformentry.date,
            dateISO: cbxrbookingformentry.dateISO,
            number: cbxrbookingformentry.number,
            digits: cbxrbookingformentry.digits,
            creditcard: cbxrbookingformentry.creditcard,
            equalTo: cbxrbookingformentry.equalTo,
            extension: cbxrbookingformentry.extension,
            maxlength: $.validator.format(cbxrbookingformentry.maxlength),
            minlength: $.validator.format(cbxrbookingformentry.minlength),
            rangelength: $.validator.format(cbxrbookingformentry.rangelength),
            range: $.validator.format(cbxrbookingformentry.range),
            max: $.validator.format(cbxrbookingformentry.max),
            min: $.validator.format(cbxrbookingformentry.min)
        });

        // booking entry form create/edit js validation
        $(".cbxrbookingform_wrapper").each(function (index, elem) {

            var $form_wrapper = $(elem);


            var $element = $form_wrapper.find('.cbxrbooking-single');
            if ($element.length == 0) return;

            var $form_id = $element.data('form-id');
            var $forms_data = cbxrbookingformentry.forms_data[$form_id];

            //console.log($form_id);
            //console.log(cbxrbookingformentry.forms_data);


            var $schedule_weekdays = $forms_data.schedule_weekdays;
            var $schedule_times = $forms_data.schedule_times;
            var $exceptions_dates = $forms_data.exceptions_dates;
            var $exceptions_times = $forms_data.exceptions_times;

            var $early_bookings = $forms_data.early_booking;
            var $late_bookings = $forms_data.late_booking;

            //var $date_pre_sel            = $forms_data.preselect_date;
            var $date_pre_sel = 'today'; //for now hard coded

            var $firstday = $forms_data.first_day;
            var $time_interval = $forms_data.time_interval;
            var $party_size_min = $forms_data.party_size_min;
            var $party_size_max = $forms_data.party_size_max;
            var $name_required = $forms_data.name_required;
            var $email_required = $forms_data.email_required;
            var $phone_required = $forms_data.phone_required;
            var $success_message = $forms_data.success_message;
            var $twenty_four_hour_format = $forms_data.twenty_four_hour_format;
            var $single_date_format = $forms_data.single_date_format;

            var $cbxrb_preferred_date = $form_wrapper.find(".cbxrb_preferred_date");


            var current_date = new Date();
            var $day_number = current_date.getDay();
            var dd = current_date.getDate();
            var mm = current_date.getMonth() + 1; //January is 0!
            var yyyy = current_date.getFullYear();

            var hour = current_date.getHours();
            var min = current_date.getMinutes();

            if (dd < 10) {
                dd = '0' + dd
            }
            if (mm < 10) {
                mm = '0' + mm
            }

            var i = 0;

            var $cbxrb_preferred_time = $form_wrapper.find(".cbxrb_preferred_time").flatpickr({
                disableMobile: true,
                noCalendar: true,
                enableTime: true,
                enableSeconds: false, // disabled by default
                time_24hr: $twenty_four_hour_format, // AM/PM time picker is used by default
                // default format
                dateFormat: "H:i",
                defaultHour: (typeof ($schedule_times[$day_number]) != "undefined" && $schedule_times[$day_number] != '' && $schedule_times[$day_number] != null) ? $schedule_times[$day_number].start.split(":")[0] : 12,
                defaultMinute: (typeof ($schedule_times[$day_number]) != "undefined" && $schedule_times[$day_number] != '' && $schedule_times[$day_number] != null) ? $schedule_times[$day_number].start.split(":")[1] : 0,
                minuteIncrement: $time_interval

            });


            //console.log($cbxrb_preferred_time.config.time_24hr); //for any instance related https://flatpickr.js.org/instance-methods-properties-elements/
            //possible may need
            //set(option, value)#
            //Sets a config option optionto value, redrawing the calendar and updating the current view, if necessary.

            //some example here to use later
            //$cbxrb_preferred_time.set('time_24hr' , true);
            //console.log($cbxrb_preferred_time.config.time_24hr);


            $cbxrb_preferred_date.flatpickr({
                disableMobile: true,
                minDate: "today",
                defaultDate: $date_pre_sel,
                "locale": {
                    "firstDayOfWeek": $firstday // start week on Monday
                },

                altInput: true,
                altFormat: $single_date_format,
                dateFormat: "Y-m-d",

                enable: [
                    function (date) {

                        //we are trying to determine which day we will enable or disable
                        var $date_day = date.getDay(); //mon = 1 Fri = 6, Sun = 0

                        if ($exceptions_dates.length == 0 && $schedule_weekdays.length == 0) return true;

                        //handle exception list first, if exception block it's block, if exception allows then it's allow
                        if ($exceptions_dates.length !== 0) {

                            for (var index_val in $exceptions_dates) {
                                var $getDay = date.getDate();
                                var $getMonth = date.getMonth() + 1; //because month is zero based index , huh!
                                var $getYear = date.getFullYear();

                                if ($getMonth < 10) {
                                    $getMonth = '0' + $getMonth
                                }
                                if ($getDay < 10) {
                                    $getDay = '0' + $getDay
                                }
                                if ($getYear + '-' + $getMonth + '-' + $getDay === $exceptions_dates[index_val]) {
                                    var $exceptions_times_val = $exceptions_times[$exceptions_dates[index_val]];
                                    if ($exceptions_times_val == '') {
                                        //full closed
                                        return false;
                                    } else {
                                        //open for specific hour limits
                                        return true;
                                    }
                                }
                            }
                        }//end exception dates list

                        //now check with open week days
                        if ($schedule_weekdays.length !== 0) {
                            //week days are in string format (!)
                            if ($schedule_weekdays.includes($date_day.toString())) {
                                return true;
                            } else {
                                return false;
                            }
                        }

                        return true;

                    }//end function enable
                ],
                onChange: function (selectedDates, dateStr, instance) {
                    //on date select, we will set the time selector valid value so that user can not try to book for wrong time.


                    var $week_day_number = new Date(dateStr).getDay();


                    var $time_handled = false;

                    // at first check from exception times as it's quick
                    if (($exceptions_times.length !== 0) && (dateStr in $exceptions_times)) {
                        $time_handled = true;

                        if ($exceptions_times[dateStr] != '') {
                            if ($exceptions_times[dateStr].start != '') {
                                var $exception_time_h_m = $exceptions_times[dateStr].start.split(":");

                                //set default hours
                                $cbxrb_preferred_time.set('defaultHour', $exception_time_h_m[0]);
                                $cbxrb_preferred_time.set('defaultMinute', $exception_time_h_m[1]);

                                //set min date(here min hour and min)
                                $cbxrb_preferred_time.set('minDate', $exceptions_times[dateStr].start);
                            }

                            if ($exceptions_times[dateStr].end != '') {
                                //set max date, here max hour and min
                                $cbxrb_preferred_time.set('maxDate', $exceptions_times[dateStr].end);
                            }
                        } else {
                            //full closed day, though it's not possible click such day

                        }
                    }//exception handle done, now need to check the regular days

                    //check regular day based schedule.
                    if (($schedule_times.length !== 0) && ($time_handled == false)) {
                        //logically it's not possible to click a day that is not open by exception or regular day schedule.
                        if ($schedule_times[$week_day_number] != '') {
                            if ($schedule_times[$week_day_number].start != '') {

                                var $time_h_m = $schedule_times[$week_day_number].start.split(":");

                                //set default hours
                                $cbxrb_preferred_time.set('defaultHour', $time_h_m[0]);
                                $cbxrb_preferred_time.set('defaultMinute', $time_h_m[1]);

                                //set min date(here min hour and min)
                                $cbxrb_preferred_time.set('minDate', $schedule_times[$week_day_number].start);
                            }

                            if ($schedule_times[$week_day_number].end != '') {
                                //set max date, here max hour and min
                                $cbxrb_preferred_time.set('maxDate', $schedule_times[$week_day_number].end);
                            }
                        }
                    }
                }

            });


            var $formvalidator = $element.validate({
                errorPlacement: function (error, element) {
                    error.appendTo(element.parents('.cbxrbooking-error-msg-show'));
                },
                errorElement: 'p',
                rules: {
                    cbxrb_preferred_date: {
                        required: true
                    },
                    cbxrb_preferred_time: {
                        required: true
                    }
                },
                messages: {}
            });

            // prevent double click form submission

            $element.submit(function (e) {
                var $form = $(this);

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
                        url: cbxrbookingformentry.ajaxurl, // the url where we want to POST
                        data: data + '&action=rbooking_frontend_entrysubmit_action', // our data object
                        security: cbxrbookingformentry.nonce,
                        dataType: 'json' // what type of data do we expect back from the server
                    });
                    request.done(function (data) {
                        console.log(data);

                        if ($.isEmptyObject(data.error)) {
                            $element.find('.cbxrbooking_ajax_icon').hide();
                            $element.find('.cbxrb-actionbutton').prop("disabled", false);

                            var $messages = data.success.messages;
                            var $show_form = parseInt(data.success.show_form);


                            $.each($messages, function (key, $message) {
                                $form_wrapper.find('.cbxrbooking-success-messages').append('<p class="alert alert-' + $message['type'] + '">' + $message['text'] + '</p>');
                            });

                            if ($show_form === 1) {
                                $formvalidator.resetForm();
                                $element[0].reset();
                            } else {
                                $element.remove();
                            }

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