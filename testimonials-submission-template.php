<?php
/**
 * Enqueueing scripts for js-based UX
 * 
 * @author Fikri Rasyid
 * 
 * @return void
 */
function testimonials_submission_scripts(){
	// Make the hook pluggable
	if( apply_filters( 'enqueue_testimonials_submission_scripts', true ) == false )
		return;

	wp_enqueue_script( 'testimonials_submission', TESTIMONIALS_SUBMISSION_PLUGIN_URL . 'assets/js/testimonials-submission.js', array('jquery'), '1.0', true );

	// Add params
	$testimonials_submission_params = array(
		'key' => wp_create_nonce( 'testimonials_submission_invisible_value' )
	);
	wp_localize_script( 'testimonials_submission', 'testimonials_submission_params', $testimonials_submission_params );
}
add_action( 'wp_enqueue_scripts', 'testimonials_submission_scripts' );	

/**
 * Get testimonials submission form
 * 
 * @author Fikri Rasyid
 * 
 * @return string
 */
function get_testimonials_submission( $args = array() ){
	$testimonials_submission = new Testimonials_Submission_Public();
	$form = $testimonials_submission->get_form( $args );

	return $form;	
}

/**
 * Print testimonials submission form
 * 
 * @author Fikri Rasyid
 * 
 * @return void
 */
function testimonials_submission( $args = array() ){
	echo get_testimonials_submission( $args );
}

/**
 * Adding shortcode for displaying testimonials submission form on the_content
 * 
 * @author Fikri Rasyid
 * 
 * @return string
 */
function testimonials_submission_shortcode( $atts ){
	$form = get_testimonials_submission( $atts );

	return $form;
}
add_shortcode( 'testimonials_submission', 'testimonials_submission_shortcode' );