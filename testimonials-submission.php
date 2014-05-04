<?php
/**
 * Plugin Name: Testimonials Submissions
 * Plugin URI: http://fikrirasyid.com/testimonials-submissions/
 * Description: Extension of Testimonials by WooThemes Plugin. Enabling user to submit testimonials.
 * Author: Fikri Rasyid
 * Version: 1.0.0
 * Author URI: http://fikrirasyid.com
 *
 * @package WordPress
 * @subpackage Testimonials_Submission
 * @author Fikri Rasyid
 * @since 1.0.0
 */
if (!defined('TESTIMONIALS_SUBMISSION_PLUGIN_URL'))
	define('TESTIMONIALS_SUBMISSION_PLUGIN_URL', plugin_dir_url( __FILE__ ));

require_once( 'classes/class-testimonials-submission-message.php' );
require_once( 'classes/class-testimonials-submission.php' );
require_once( 'classes/class-testimonials-submission-public.php' );;
require_once( 'testimonials-submission-template.php' );