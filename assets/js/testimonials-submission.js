jQuery(document).ready(function($) { 
	/**
	 * Put the key into the lock
	 * This will do for bot which doesn't use js
	 */
	$('#ts-lock').val( testimonials_submission_params.key );

	/**
	* Testimonial submission handler
	*/
	$('#submit-testimonial').submit(function(e){
		e.preventDefault();

		// Remove previously added state
		$('#submit-testimonial input, #submit-testimonial textarea').removeAttr('style');
		$('#ts-message').remove();
		$('#submit-testimonial label span').empty();

		// Display loading state
		$('#submit-testimonial-block').show();
		$('#submit-testimonial-loading').slideDown();

		// Send data
		var inputs = $('#submit-testimonial').serializeArray();
		inputs.push( { 'name' : 'ts_is_ajax', 'value' : true } );
		var action = $('#submit-testimonial').attr('action');		

		$.post( action, inputs, function( response ){
			var data = $.parseJSON( response );

			// Hide loading state
			$('#submit-testimonial-block').hide();
			$('#submit-testimonial-loading').slideUp();

			// Error handling
			if( typeof data.error == 'undefined' ){
				// Empty the fields
				$('#submit-testimonial input[type="text"], #submit-testimonial input[type="email"], #submit-testimonial textarea').val('');

				// Display success message
				$('#submit-testimonial').prepend('<div id="ts-message" style="background: green; padding: 3px 15px; display: block; font-size: 14px; margin-bottom: 30px; color: white;">'+data.success.message+'</div>');				
			} else {
				// Display error message
				for( var error_key in data.error  ){
					var id 		= data.error[error_key].id;
					var message = data.error[error_key].message;

					if( id == 'ts-message' ){
						$('#submit-testimonial').prepend('<div id="ts-message" style="background: red; padding: 3px 15px; display: block; font-size: 14px; margin-bottom: 30px; color: white;">'+message+'</div>');
					} else {
						$('#'+id).css({ 'border' : '1px solid red' });
						$('#'+id+'-message').text( ' - ' + message );
					}
				}				
			}

			// Slide to message
			var form_offset = $('#submit-testimonial').offset();
			var slider_target = form_offset.top - 100;
			$('html, body').animate({
				scrollTop : slider_target
			});
		});
	});
});