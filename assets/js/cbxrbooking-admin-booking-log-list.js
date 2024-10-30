(function ($) {
	'use strict';

	jQuery(document).ready(function ($) {

		$('.addnewbooking_wrappanel_trig').on('click', function (e) {
			e.preventDefault();

			var $this = $(this);
			$('#addnewbooking_wrappanel').toggle();
		});

		// sandywalker/webui-popover popover to show full content
		$('.cbxrbooking-message-expand').webuiPopover({
			// title:'Title',
			// content:'Content',
			placement: 'bottom-right',
			trigger  : 'hover'
		});

		$('#cbx-between-dates-toggle').appendTo('#cbxrbooking_daterange_between');

		// booking log toggle filter by date
		$('.cbx-between-dates-link').on('click', function (event) {
			event.preventDefault();

			var $toggle_div = $('#cbx-between-dates-toggle');
			$toggle_div.toggle(); //toggle the data range div

			$('#cbxrbooking_logs').find('#date_range_input').val('between_dates');
		});

		// flatpickr date trigger in textbox
		$('.cbxrbookinglog_wrapper').each(function (index, element) {

			var $form_wrapper = $(element);

			var $global_twenty_four_hour_format = (cbxrbookingadminlogformObj.global_twenty_four_hour_format == 1);
			$form_wrapper.find(".cbxrblogfromDate, .cbxrblogtoDate").flatpickr({
				dateFormat: 'Y-m-d H:i:s',
				// minDate: "today",
				// altInput: true
				enableTime: true,
				time_24hr : $global_twenty_four_hour_format
			});
		});

		// branch change will change form
		$('#cbxrbooking_logs').on('change', '.branch', function (e) {
			var $this   = $(this);
			var $parent = $this.parents('#cbxrbooking_logs');

			$.ajax({
				type    : "post",
				dataType: "json",
				url     : cbxrbookingadminloglistObj.ajaxurl,
				data    : {
					action   : "cbxrbooking_branch_to_form_action",
					security : cbxrbookingadminloglistObj.nonce,
					branch_id: $this.val(),
				},
				success : function (data, textStatus, XMLHttpRequest) {

					var form_status = cbxrbookingadminloglistObj.no_booking_form_found;

					if (data !== '') {
						var form_status = cbxrbookingadminloglistObj.select_booking_form;

						var forms_dropdown = '<option value="">' + form_status + '</option>';

						$.each(data, function (key, item) {
							forms_dropdown += '<option value="' + item.id + '">' + item.form_name + '</option>';
						});
					}

					$parent.find('#form_id').empty();
					$parent.find('.form').append(forms_dropdown);
				}
			});
		});
	});

})(jQuery);
