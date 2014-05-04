<?php
/**
 * Get testimonials submission form
 * 
 * @author Fikri Rasyid
 * 
 * @return string
 */
function get_testimonials_submission(){
	$testimonials_submission = new Testimonials_Submission_Public();
	$form = $testimonials_submission->get_form();

	return $form;	
}

/**
 * Print testimonials submission form
 * 
 * @author Fikri Rasyid
 * 
 * @return void
 */
function testimonials_submission(){
	echo get_testimonials_submission();
}

/**
 * Adding shortcode for displaying testimonials submission form on the_content
 * 
 * @author Fikri Rasyid
 * 
 * @return string
 */
function testimonials_submission_shortcode(){
	$form = get_testimonials_submission();

	return $form;
}
add_shortcode( 'testimonials_submission', 'testimonials_submission_shortcode' );