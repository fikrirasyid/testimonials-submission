<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 *  Testimonials Submission Class
 *
 * All functionality related to the Testimonials Submission feature.
 *
 * @package WordPress
 * @subpackage Testimonials_Submission
 * @category Plugin
 * @author Fikri Rasyid
 * @since 1.0.0
 */
class Testimonials_Submission{
	function __construct(){
		add_action( 'wp_ajax_testimonials_submission', array( $this, 'endpoint' ) );
		add_action( 'wp_ajax_nopriv_testimonials_submission', array( $this, 'endpoint' ) );
	}

	/**
	 * Get post row based on title
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return obj|bool
	 */
	function get_testimonial( $column = 'post_title', $value = '' ){
		global $wpdb;

		$query = $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE $column = %s ORDER BY post_date DESC", $value );

		$result = $wpdb->get_row( $query );

		return $result;
	}

	/**
	 * Check if the testimonial submitted is an accidental repeated submission 
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return bool true if this has been submitted before, false if this should be submitted
	 */
	function verify_repetition( $post_title = '' ){
		$posted = $this->get_testimonial( 'post_title', $post_title );

		if( isset( $posted->post_date ) ){
			$timestamp 		= strtotime( $posted->post_date );
			$current_time 	= current_time( 'timestamp' );
			$min_distance 	= 60 * 60 * 3; // Wait for three hours at least

			if( ( $current_time - $timestamp ) > $min_distance ){
				return false;
			} else {
				return true;
			}
		} else {
			// If on post_date found, this isn't repeatition
			return false;
		}
	}

	/**
	 * Removing query string from URL given
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return string
	 */
	function query_string_less( $url = '' ){

		// Explode the url
		$exploded_url = explode( "?", $url );

		return $exploded_url[0];
	}

	/**
	 * Receive testimonial submission
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return mixed
	 */
	function endpoint(){
		// Define where should we redirect the result
		if( isset( $_POST['_wp_http_referer'] ) ){
			$redirect = $_POST['_wp_http_referer'];
		} else {
			$redirect = home_url();
		}		

		// Remove the query string from URL
		$redirect = $this->query_string_less( $redirect );

		// Send back $_POST as $_GET for better UX
		$qs = $_POST;
		if( isset( $qs['_n'] ) ) unset( $qs['_n'] );
		if( isset( $qs['_wp_http_referer'] ) ) unset( $qs['_wp_http_referer'] );
		$filled_fields = http_build_query( $qs );

		// Define is this an ajax or not?
		if( isset( $_POST['ts_is_ajax'] ) && $_POST['ts_is_ajax'] == true ){
			$is_ajax = true;
		} else {
			$is_ajax = false;
		}

		// Redirect if this is accessed directly
		if( !isset( $_POST ) || empty( $_POST ) )
			wp_redirect( $redirect );

		// Verify nonce
		if( !isset( $_POST['_n'] ) || !wp_verify_nonce( $_POST['_n'], 'testimonial_submission_nonce' ) ){
			$error_message = 'unauthorized';

			if( $is_ajax ){

			} else {
				wp_redirect( "{$redirect}?response={$error_message}&{$filled_fields}" );
				die();
			}
		}

		// Name cannot be empty
		if( !isset( $_POST['ts_name'] ) || empty( $_POST['ts_name'] ) ){
			$error_message = 'empty-name';

			if( $is_ajax ){

			} else {
				wp_redirect( "{$redirect}?response={$error_message}&{$filled_fields}" );
				die();
			}
		}

		// Email cannot be empty
		if( !isset( $_POST['ts_email'] ) || empty( $_POST['ts_email'] ) ){
			$error_message = 'empty-email';

			if( $is_ajax ){

			} else {
				wp_redirect( "{$redirect}?response={$error_message}&{$filled_fields}" );
				die();
			}
		}

		// Testimony cannot be empty
		if( !isset( $_POST['ts_testimonial'] ) || empty( $_POST['ts_testimonial'] ) ){
			$error_message = 'empty-testimonial';

			if( $is_ajax ){

			} else {
				wp_redirect( "{$redirect}?response={$error_message}&{$filled_fields}" );
				die();
			}
		}

		// Email should be validated
		if( !isset( $_POST['ts_email'] ) || !filter_var( $_POST['ts_email'], FILTER_VALIDATE_EMAIL ) ){
			$error_message = 'invalid-email';

			if( $is_ajax ){

			} else {
				wp_redirect( "{$redirect}?response={$error_message}&{$filled_fields}" );
				die();
			}
		}	

		// Check for repetition / if this has been entered before
		$repetition = $this->verify_repetition( $_POST['ts_name'] );
		if( $repetition ){
			$error_message = 'slow-down';

			if( $is_ajax ){

			} else {
				wp_redirect( "{$redirect}?response={$error_message}&{$filled_fields}" );
				die();
			}
		}

		// If testimonial passes all validation, insert it into database
		$post_id = wp_insert_post( array(
			'post_status'	=> 'trash',
			'post_type'		=> 'testimonial',
			'post_title'	=> sanitize_text_field( $_POST['ts_name'] ),
			'post_content'	=> sanitize_text_field( $_POST['ts_testimonial'] ),
		) );

		// Saves email
		if( $post_id && isset( $_POST['ts_email'] ) && !empty( $_POST['ts_email'] ) )
			update_post_meta( $post_id, '_gravatar_email', $_POST['ts_email'] );

		// Saves role
		if( $post_id && isset( $_POST['ts_role'] ) && !empty( $_POST['ts_role'] ) )
			update_post_meta( $post_id, '_byline', $_POST['ts_role'] );

		// Saves url
		if( $post_id && isset( $_POST['ts_url'] ) && !empty( $_POST['ts_url'] ) )
			update_post_meta( $post_id, '_url', $_POST['ts_url'] );

		// Succeeded!
		if( $is_ajax ){

		} else {
			wp_redirect( "{$redirect}?response=success&{$filled_fields}" );
		}

		die();
	}
} // End Class

new Testimonials_Submission;