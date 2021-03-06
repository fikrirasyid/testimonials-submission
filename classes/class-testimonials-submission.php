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
	var $messages;

	function __construct(){
		$this->messages = new Testimonials_Submission_Message;

		add_action( 'wp_ajax_testimonials_submission', array( $this, 'endpoint' ) );
		add_action( 'wp_ajax_nopriv_testimonials_submission', array( $this, 'endpoint' ) );

		add_filter( 'wp_mail_content_type', array( $this, 'modify_content_type') );

		add_action( 'init', array( $this, 'register_post_status' ) );
	}

	/**
	 * Register post status "submission"
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return void
	 */
	function register_post_status(){
		register_post_status( 'submission', array(
			'label'       => _x( 'Submission', 'post' ),
			'label_count' => _n_noop( 'Submission <span class="count">(%s)</span>', 'Submission <span class="count">(%s)</span>' ),
			'show_in_admin_status_list' => true,
		) );
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
	 * Modify the email format into HTML mail
	 * 
	 * @return string email content type
	 */
	function modify_content_type( $content_type ){
		if( isset( $_GET['action'] ) && $_GET['action'] == 'testimonials_submission' ){
			return 'text/html';
		} else {
			return $content_type;
		}
	}

	/**
	 * Send verification email
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return void
	 */
	function send_verification_email( $post_id ){
		$post = get_post( $post_id );
		if( !$post )
			return;

		$name 	= $post->post_title;
		$to 	= get_post_meta( $post_id, '_gravatar_email', true );
		$verification_id = get_post_meta( $post_id, '_verification_id', true );
		$link 	= admin_url() . 'admin-ajax.php?action=testimonials_submission_verification&id=' . $verification_id;
		$subject = get_bloginfo( 'name' ) . __( ' Testimonial Identity Verification', 'testimonials_submission' );
		$message = sprintf( __( apply_filters( 'testimonials_submission_verification_message', '<p>Hi %s,</p><p>Thank you for sending us your testimonial! To publish your testimonial, we have to verify your identity first. To do so, <a href="%s" title="Verify your identity" target="_blank">please click this link</a>.</p> <p>Thank you.</p>' ), 'testimonials_submission' ), $name, $link );

		// Send the mail
		$sending = wp_mail( $to, $subject, $message );

		return $sending;
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

		// Prepare Ajax Response
		if( $is_ajax )
			$ajax_response = array();

		// Verify bot
		if( !isset( $_POST['ts_lock'] ) || !wp_verify_nonce( $_POST['ts_lock'], 'testimonials_submission_invisible_value' ) ){
			$error_code = 'not-human';

			if( $is_ajax ){
				$ajax_response['error'][$error_code]['id'] 		= "ts-message";
				$ajax_response['error'][$error_code]['message'] = $this->messages->get_message( $error_code );
			} else {
				wp_redirect( "{$redirect}?response={$error_code}&{$filled_fields}" );
				die();
			}
		}

		// Verify nonce
		if( !isset( $_POST['_n'] ) || !wp_verify_nonce( $_POST['_n'], 'testimonials_submission_nonce' ) ){
			$error_code = 'unauthorized';

			if( $is_ajax ){
				$ajax_response['error'][$error_code]['id'] 		= "ts-message";
				$ajax_response['error'][$error_code]['message'] = $this->messages->get_message( $error_code );
			} else {
				wp_redirect( "{$redirect}?response={$error_code}&{$filled_fields}" );
				die();
			}
		}

		// Name cannot be empty
		if( !isset( $_POST['ts_name'] ) || empty( $_POST['ts_name'] ) ){
			$error_code = 'empty-name';

			if( $is_ajax ){
				$ajax_response['error'][$error_code]['id'] 		= "ts-name";
				$ajax_response['error'][$error_code]['message'] = $this->messages->get_message( $error_code );
			} else {
				wp_redirect( "{$redirect}?response={$error_code}&{$filled_fields}" );
				die();
			}
		}

		// Email cannot be empty
		if( !isset( $_POST['ts_email'] ) || empty( $_POST['ts_email'] ) ){
			$error_code = 'empty-email';

			if( $is_ajax ){
				$ajax_response['error'][$error_code]['id'] 		= "ts-email";
				$ajax_response['error'][$error_code]['message'] = $this->messages->get_message( $error_code );
			} else {
				wp_redirect( "{$redirect}?response={$error_code}&{$filled_fields}" );
				die();
			}
		}

		// Testimonial cannot be empty
		if( !isset( $_POST['ts_testimonial'] ) || empty( $_POST['ts_testimonial'] ) ){
			$error_code = 'empty-testimonial';

			if( $is_ajax ){
				$ajax_response['error'][$error_code]['id'] 		= "ts-testimonial";
				$ajax_response['error'][$error_code]['message'] = $this->messages->get_message( $error_code );
			} else {
				wp_redirect( "{$redirect}?response={$error_code}&{$filled_fields}" );
				die();
			}
		}

		// Email should be validated
		if( !isset( $_POST['ts_email'] ) || !filter_var( $_POST['ts_email'], FILTER_VALIDATE_EMAIL ) ){
			$error_code = 'invalid-email';

			if( $is_ajax ){
				$ajax_response['error'][$error_code]['id'] 		= "ts-email";
				$ajax_response['error'][$error_code]['message'] = $this->messages->get_message( $error_code );
			} else {
				wp_redirect( "{$redirect}?response={$error_code}&{$filled_fields}" );
				die();
			}
		}	

		// Check for repetition / if this has been entered before
		$repetition = $this->verify_repetition( $_POST['ts_name'] );
		if( $repetition ){
			$error_code = 'slow-down';

			if( $is_ajax ){
				$ajax_response['error'][$error_code]['id'] 		= "ts-message";
				$ajax_response['error'][$error_code]['message'] = $this->messages->get_message( $error_code );
			} else {
				wp_redirect( "{$redirect}?response={$error_code}&{$filled_fields}" );
				die();
			}
		}

		// Error response for ajax request
		if( $is_ajax && isset( $ajax_response['error'] ) ){
			echo json_encode( $ajax_response );
			die();
		}

		// If testimonial passes all validation, insert it into database
		$post_id = wp_insert_post( array(
			'post_status'	=> 'submission',
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

		// Saves verification ID
		$verification_id = md5( "verify_{$_POST['ts_name']}_{$_POST['ts_email']}_{$post_id}" );
			update_post_meta( $post_id, '_verification_id', $verification_id );

		// Sending verification email
		$this->send_verification_email( $post_id );

		// Succeeded!
		if( $is_ajax ){
			$ajax_response['success']['id'] 		= "ts-message";			
			$ajax_response['success']['message']	= $this->messages->get_message( 'success' );
			echo json_encode( $ajax_response );
		} else {
			wp_redirect( "{$redirect}?response=success" );
		}

		die();
	}
} // End Class

new Testimonials_Submission;