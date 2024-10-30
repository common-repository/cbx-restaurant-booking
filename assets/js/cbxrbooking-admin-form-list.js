(function ($) {
	'use strict';

	jQuery(document).ready(function ($) {
		//select all text on click of shortcode text
		$('.cbxrbookingshortcode').on("click", function () {

			var text = $(this).text();
			var $this = $(this);
			var $input = $('<input class="cbxrbookingshortcode-text" type="text">');
			$input.prop('value', text);
			$input.insertAfter($(this));
			$input.focus();
			$input.select();
			$this.hide();

			try {
				document.execCommand("copy");
			} catch (err) {

			}

			$input.focusout(function(){
				$this.show();
				$input.remove();
			});
		});

		//for form on/off in form listing
		var elem  = document.querySelector('.cbxrbookingjs-switch');
		var elems = Array.prototype.slice.call(document.querySelectorAll('.cbxrbookingjs-switch'));

		elems.forEach(function (changeCheckbox) {
			changeCheckbox.onchange = function () {

				var enable = (changeCheckbox.checked) ? 1 : 0;
				var postid = $(changeCheckbox).attr('data-postid');

				//ajax call for sending test notification
				$.ajax({
					type    : "post",
					dataType: "json",
					url     : cbxrbookingadminformlistObj.ajaxurl,
					data    : {
						action  : "cbxrbooking_form_enable_disable_action",
						security: cbxrbookingadminformlistObj.nonce,
						enable  : enable,
						postid  : postid
					},
					success : function (data, textStatus, XMLHttpRequest) {
						//
					}// end of success
				});// end of ajax
			};

			var switchery = new Switchery(changeCheckbox);
		});

		//form log countr reset
		$('.cbxrbooking_form_resetcounter_trig').on('click', function (e) {
			e.preventDefault();

			var $this      = $(this);
			var $form_id   = parseInt($this.data('formid'));
			var $target    = $this.data('countertarget');
			var $cur_count = parseInt($this.data('currentcount'));

			if ($form_id > 0 && $cur_count > 0) {
				//ajax call for sending test notification
				$.ajax({
					type    : "post",
					dataType: "json",
					url     : cbxrbookingadminformlistObj.ajaxurl,
					data    : {
						action  : "cbxrbooking_form_resetcounter_action",
						security: cbxrbookingadminformlistObj.nonce,
						postid  : $form_id
					},
					success : function (data, textStatus, XMLHttpRequest) {
						if (parseInt(data) == 1) {
							$('#' + $target).text('0');
							$this.hide();
						}
						else {
							$this.hide();
						}
					}// end of success
				});// end of ajax
			}
			else {
				$this.hide();
			}
		});

		//for form branch dropdown selection change
		$('.cbxrbookingjs-branch-select').on('change', function () {

			var $this    = $(this);
			var branchid = $this.val();
			var postid   = $this.attr('data-postid');

			//ajax call for sending test notification
			$.ajax({
				type    : "post",
				dataType: "json",
				url     : cbxrbookingadminformlistObj.ajaxurl,
				data    : {
					action  : "cbxrbooking_branch_selection_action",
					security: cbxrbookingadminformlistObj.nonce,
					branchid: branchid,
					postid  : postid
				},
				success : function (data, textStatus, XMLHttpRequest) {
					//
				}// end of success
			});// end of ajax
		});
	});

})(jQuery);
