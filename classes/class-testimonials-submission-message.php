<?php
class Testimonials_Submission_Message{
	/**
	 * List of message
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return array
	 */
	function messages(){
		$messages = array(
			'unauthorized' 	=> __( 'Your request cannot be authorized. Refresh your browser and try again.', 'testimonials_submission' ),
			'empty-message' => __( 'Name field cannot be empty.', 'testimonials_submission' ),
			'empty-email'	=> __( 'Email field cannot be empty.', 'testimonials_submission' ),
			'empty-testimonial' => __( 'Testimony field cannot be empty.', 'testimonials_submission' ),
			'invalid-email' => __( 'Please use your actual email.', 'testimonials_submission' ),
			'slow-down' 	=> __( 'Slow down, you can stop clicking submit testimonial button. We already have received your testimonial a moment ago.', 'testimonials_submission' ),
			'success' 		=> __( 'We have received your testimonial. Please check your email and follow further instruction to verify your identity.', 'testimonials_submission' ),
			'default' 		=> __( 'Something is wrong. Please try again later.', 'testimonials_submission' )
		);

		return apply_filters( 'testimonials_submission_message', $messages );
	}

	/**
	 * Get message
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return string
	 */
	function get_message( $code = '' ){
		$messages = $this->messages();

		if( isset( $messages[$code] ) ){
			return $messages[$code];
		} else {
			return $messages['default'];
		}
	}
}