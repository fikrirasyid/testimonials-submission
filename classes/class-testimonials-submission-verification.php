<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

class Testimonials_Submission_Verification{
	function __construct(){
		add_action( 'wp_ajax_testimonials_submission_verification', array( $this, 'render_page' ) );
		add_action( 'wp_ajax_nopriv_testimonials_submission_verification', array( $this, 'render_page' ) );
	}

	/**
	 * Verify verification id given
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return int|bool return post_id if given ID is correct, return false if no record found
	 */
	function verify_id( $id = '' ){
		global $wpdb;

		$query = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_value = %s", $id );

		$result = $wpdb->get_row( $query );

		if( isset( $result->post_id ) ){
			return intval( $result->post_id );
		} else {
			return false;
		}
	}

	/**
	 * Get testimonial from post_id given
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return arr|bool
	 */
	function get_testimonial( $post_id ){
		$post = get_post( $post_id );

		if( $post ){
			$testimonial['status']	= $post->post_status;

			$testimonial['name'] 	= $post->post_title;

			$testimonial['content'] = $post->post_content;

			$testimonial['email'] 	= get_post_meta( $post_id, '_gravatar_email', true );

			$byline = get_post_meta( $post_id, '_byline', true );
			if( $byline )
				$testimonial['byline'] = $byline;

			$url = get_post_meta( $post_id, '_url', true );
			if( $url )
				$testimonial['url'] = $url;

			return $testimonial;
		} else {
			return false;
		}
	}

	/**
	 * Display message based on verification id given, whether it is right or not
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return void
	 */
	function message( $verification_id = '' ){
		// Verify the ID given
		$verification = $this->verify_id( $verification_id );

		if( !$verification ){
			?>
				<p><?php _e( 'We cannot find any testimonial which matches with your verification id. <br> You are probably clicking the wrong link.', 'testimonial_submission' ); ?></p>
			<?php
		} else {
			// display message
			$testimonial = $this->get_testimonial( $verification );

			// Check status
			if( $testimonial['status'] == 'submission' ) :
			
				// Update testimonial status to draft
				$update = wp_update_post(array(
					'ID' 			=> $verification,
					'post_status'	=> 'draft'
				));

				?>
					<p style="margin-bottom: 20px;"><?php printf( __( 'Hi %s,', 'testimonial_submission' ), $testimonial['name']); ?></p>
					<p><?php _e( 'Your testimonial below has been verified:', 'testimonial_submission' ); ?></p>

					<blockquote style="margin:60px 0; padding: 30px 20px; font-weight: lighter; font-size: 1.4em; border-top: 1px solid #cfcfcf; border-bottom: 1px solid #cfcfcf;">
						<?php echo wpautop( $testimonial['content'] ); ?>
						
						<br>
						
						<?php echo get_avatar( $testimonial['email'] ); ?>

						<p style="font-size: .8em; font-weight: bold;"><cite><?php echo $testimonial['name']; ?></cite></p>

						<?php if( isset( $testimonial['byline'] ) ) : ?>
						<p style="font-size: .8em;"><?php echo $testimonial['byline']; ?></p>
						<?php endif; ?>

						<?php if( isset( $testimonial['url'] ) ) : ?>
						<p style="font-size: .8em;"><a href="<?php echo $testimonial['url']; ?>" target="_blank"><?php echo $testimonial['url']; ?></a></p>
						<?php endif; ?>

					</blockquote>	

					<p style="margin-bottom: 20px;"><?php _e( 'Your testimonial have been added as draft and not published yet.  We have notified administrator that there is new <em>incoming</em> verified testimonial from you.', 'testimonial_submission' ); ?></p>
					<p><?php _e( 'Thank you for your testimonial.', 'testimonial_submission' ); ?></p>
				<?php
			else :
				?>
					<p style="margin-bottom: 20px;"><?php printf( __( 'Hi %s,', 'testimonial_submission' ), $testimonial['name']); ?></p>
					<p><?php _e( 'You have verified your testimonial before.', 'testimonial_submission' ); ?></p>
				<?php
			endif;
		}
	}

	/**
	 * Display verification result based on ID given
	 * 
	 * @author Fikri Rasyid
	 * 
	 * @return void
	 */
	function render_page(){
		// Redirect to homepage if no id given
		if( !isset( $_GET['id'] ) )
			wp_redirect( home_url() );

		// Verification id
		$verification_id = $_GET['id'];

		// Display verification message, wrapped by themes header and footer
		get_header();

		do_action( 'before_testimonial_submission_verification_page' );

		?>
			<div id="verification-wrap" style="display: block; width: 90%; max-width: 640px; margin: 0 auto; padding: 80px 0; text-align: center;">
				<div id="verification-message" style="font-size: 16px; line-height: 1.8;">
					<?php $this->message( $verification_id ); ?>
				</div>
			</div>
		<?php
		do_action( 'after_testimonial_submission_verification_page' );

		get_footer();

		die();
	}
}
new Testimonials_Submission_Verification;