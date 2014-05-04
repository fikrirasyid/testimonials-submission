<?php
/**
 *
 */
function testimonials_submission_shortcode( $atts = array(), $content = null ){
	$testimonials_submission = new Testimonials_Submission_Public();
	$form = $testimonials_submission->get_form();

	return $form;
}
add_shortcode( 'testimonials_submission', 'testimonials_submission_shortcode' );