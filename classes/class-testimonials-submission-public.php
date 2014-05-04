<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 *  Testimonials Submission Public Class
 *
 * All functionality related to the Testimonials Submission Public-Facing feature.
 *
 * @package WordPress
 * @subpackage Testimonials_Submission
 * @category Plugin
 * @author Fikri Rasyid
 * @since 1.0.0
 */
class Testimonials_Submission_Public{

	/**
	 * Define form fields and its validation mechanism. Pluggable by filters
	 * @return arr
	 */
	function form_fields(){
		$fields = array(
			array(
				'id' 			=> 'name',
				'input_type'	=> 'text',
				'label'			=> 'Name',
				'required'		=> true,
				'description' 	=> __( 'Your Name', 'testimonials_submission' ),
				'placeholder'	=> __( 'John Awesome', 'testimonials_submission' )
			),
			array(
				'id' 			=> 'email',
				'input_type'	=> 'text_email',
				'label'			=> 'Email',
				'required'		=> true,
				'description' 	=> __( 'Your Email. We will send validation link to your email', 'testimonials_submission' ),
				'placeholder'	=> __( 'youremail@gmail.com', 'testimonials_submission' )
			),
			array(
				'id' 			=> 'role',
				'input_type'	=> 'text',
				'label'			=> 'Role',
				'required'		=> false,
				'description' 	=> __( 'Explain yourself briefly here', 'testimonials_submission' ),
				'placeholder'	=> __( 'Happy Customer', 'testimonials_submission' )
			),
			array(
				'id' 			=> 'url',
				'input_type'	=> 'text_url',
				'label'			=> 'URL',
				'required'		=> false,
				'description' 	=> __( 'Give us your URL so we can link back to you', 'testimonials_submission' ),
				'placeholder'	=> __( 'http://yoursite.com', 'testimonials_submission' )
			),
			array(
				'id' 			=> 'testimony',
				'input_type'	=> 'textarea',
				'label'			=> 'Testimony',
				'required'		=> true,
				'description' 	=> __( 'Tell us your experience', 'testimonials_submission' ),
				'placeholder'	=> __( 'I will definitely recommend you guys to my friends and family :D', 'testimonials_submission' )
			),
		);

		return apply_filters( 'testimonials_submission_fields', $fields );
	}

	/**
	 * Display message / response based on query strings
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return string
	 */
	function get_message(){
		if( isset( $_GET['response'] ) ){
			$code = $_GET['response'];
			$classes = 'ts-message error';
			$style = 'background: red; padding: 3px 15px; display: block; font-size: 14px; margin-bottom: 30px; color: white;';

			switch ( $code ) {
				case 'unauthorized':
					$message = __( 'Your request cannot be authorized. Refresh your browser and try again.', 'testimonials_submission' );
					break;

				case 'empty-name':
					$message = __( 'Name field cannot be empty.', 'testimonials_submission' );
					break;

				case 'empty-email':
					$message = __( 'Email field cannot be empty.', 'testimonials_submission' );
					break;

				case 'empty-testimony':
					$message = __( 'Testimony field cannot be empty.', 'testimonials_submission' );
					break;

				case 'invalid-email':
					$message = __( 'Please use your actual email.', 'testimonials_submission' );
					break;

				case 'slow-down':
					$message = __( 'Slow down, you can stop clicking submit testimony button. We already have received your testimony a moment ago.', 'testimonials_submission' );
					break;

				case 'success':
					$message = __( 'We have received your testimony. Please check your email and follow further instruction to verify your identity.', 'testimonials_submission' );
					$classes = 'ts-message success';
					$style = 'background: green; padding: 3px 15px; display: block; font-size: 14px; margin-bottom: 30px; color: white;';
					break;

				default:
					$message = __( 'Something is wrong. Please try again later.', 'testimonials_submission' );
					break;
			}

			return "<div class='$classes' style='$style'>$message</div>";
		} else {
			return '';
		}
	}

	/**
	 * Get filled fields
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return string
	 */
	function get_field_value( $name = '' ){
		if( isset( $_GET[$name] ) && isset( $_GET['response'] ) && $_GET[$name] != '' && $_GET['response'] != 'success' ){
			return $_GET[$name];
		} else {
			return '';
		}
	}

	/**
	 * Print form based on defined fields
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return string
	 */
	function get_form(){

		// Define form fields
		$fields = $this->form_fields();

		// Action URL
		$action = admin_url() . 'admin-ajax.php?action=testimonials_submission';

		// Form
		$form = "<form action='$action' id='submit-testimonials' method='POST'>";

		// Notification, if there's any
		$form .= $this->get_message();

		// Build the form
		foreach ($fields as $field) {

			// Continue the loop if value needed cannot be found
			if( !isset( $field['id'] ) || !isset( $field['input_type'] ) || !isset( $field['label'] ) || !isset( $field['required'] ) || !isset( $field['description'] ) )
				continue;

			// Extract the field variables
			extract( $field );

			// Build the form
			$form .= "<p>";
			$form .= "<label>$label";

			if( $required ){
				$form .= "<span class='required'>*</span>";
			}

			$form .= "</label>";

			// Get submitted value
			$value = $this->get_field_value('ts_' . $id );

			// Print input
			switch ( $input_type ) {
				case 'textarea': 
					$form .= "<textarea name='ts_$id' id='ts-$id' cols='30' rows='10' placeholder='$placeholder'>$value</textarea>";
					break;

				case 'text_url':
					$form .= "<input name='ts_$id' id='ts-$id' type='text' class='url' placeholder='$placeholder' value='$value'>";
					break;
				
				case 'text_email':
					$form .= "<input name='ts_$id' id='ts-$id' type='email' class='email' placeholder='$placeholder' value='$value'>";
					break;

				// text
				default:
					$form .= "<input name='ts_$id' id='ts-$id' type='text' placeholder='$placeholder' value='$value'>";
					break;
			}

			$form .= "<span class='field-description'>$description</span>";			
			
			$form .= "</p>";
		}

		// create nonce
		ob_start();

		wp_nonce_field( 'testimonial_submission_nonce', '_n' );

		$form .= ob_get_clean();

		// Submit Copy
		$submit_copy = __( 'Submit Testimony', 'testimonials_submission' );

		$form .= "<input type='submit' class='button' value='$submit_copy' />";

		$form .= '</form>';		

		return $form;
	}
} // End Class