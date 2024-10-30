(function ($) {
	'use strict';

	var serializeObject = function ($form, wp_action_name) {
		var o       = {};
		o['action'] = wp_action_name;
		var a       = $form.serializeArray();
		$.each(a, function () {
			if (o[this.name]) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};

	jQuery(document).ready(function ($) {

		//create new branch manager account
		$('#cbxrb-bm-account-form').submit(function (evnt) {
			evnt.preventDefault();

			var $form = $(this);
			$('#cbxrbloading').show();
			$form.find('#cbxrb-new-bm-acc').prop("disabled", true);
			$.ajax({
				type    : 'post',
				dataType: 'json',
				url     : ajaxurl,
				data    : serializeObject($form, 'add_new_branch_manager_acc'),
				success : function (response) {
					//clear all error and update field
					$('#cbxrbloading').hide();
					$form.find('.cbxrb-bm-acc').removeClass('cbxrb-bm-error');
					// $form.find('#cbxrb-edit-manage-acc-cancel').attr('disabled', 'disabled');
					$form.find('#cbxrb-new-bm-acc').prop("disabled", false);

					if (response.error) {
						$form.find('.cbxrb-msg-text').text(response.msg);
						$form.find('.cbxrb-msg-box').addClass('error').removeClass('updated hidden').show();
						$.each(response.field, function (index, value) {
							$form.find('label[for="' + value + '"]' + value).addClass('cbxrb-error');
						});
					} else {
						//$all_acc_list[response.form_value.id] = response.form_value;

						$form.find('.cbxrb-msg-text').html(response.msg);
						$form.find('.cbxrb-msg-box').addClass('updated').removeClass('error hidden').show();

						//reset form is new item inserted
						if (response.form_value.status = 'new') {
							$form[0].reset();
						}

						//var $accselection = response.form_value.type;

					}
				}
			});//end ajax calling for category
		});//end category form submission


	});

})(jQuery);
