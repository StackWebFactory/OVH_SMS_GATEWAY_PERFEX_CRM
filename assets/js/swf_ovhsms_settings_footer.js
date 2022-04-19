$(function(){
	"use strict";
		$('#swf_ovhsms_validate').on('click',function(e){
			e.preventDefault();
			$('input[name="settings[swf_ovhsmshub_activation_code]"]').parents('.form-group').removeClass('has-error');
			var swf_ovhsms_purchase_key = $('input[name="settings[swf_ovhsmshub_activation_code]"]').val();
			var update_errors;
			if(swf_ovhsms_purchase_key != ''){
				var ubtn = $(this);
				ubtn.html($('#swf_ovhsms_validate_wrapper').data('wait-text'));
				ubtn.addClass('disabled');
				$.post(admin_url+'swf_ovhsmshub/validate',{
					purchase_key:swf_ovhsms_purchase_key,
				}).done(function(response){
					response=JSON.parse(response);
					if(response['status']){
						$('input[name="settings[swf_ovhsmshub_activated]"]').val(response['status']);
						$('#settings-form').submit();	
					}else{
						$('#swf_ovhsms_validate_messages').html('<div class="alert alert-danger"></div>');
						$('#swf_ovhsms_validate_messages .alert').append('<p>'+response['message']+'</p>');
						ubtn.removeClass('disabled');
						ubtn.html($('#swf_ovhsms_validate_wrapper').data('original-text'));
					}	
				}).fail(function(response){
					update_errors = JSON.parse(response.responseText);
					$('#swf_ovhsms_validate_messages').html('<div class="alert alert-danger"></div>');
					for (var i in update_errors){
						$('#swf_ovhsms_validate_messages .alert').append('<p>'+update_errors[i]+'</p>');
					}
					ubtn.removeClass('disabled');
					ubtn.html($('#swf_ovhsms_validate_wrapper').data('original-text'));
				});
			} 			
			else {
				$('input[name="settings[swf_ovhsmshub_activation_code]"]').parents('.form-group').addClass('has-error');
			}
		});
	});