<?php
/**
 * Generic Functions for Course Interactions on both the Frontend and Backend of LearnDash MailChimp
 *
 * @since		1.0.0
 *
 * @package LearnDash_MailChimp
 * @subpackage LearnDash_MailChimp/core/shortcodes
 */

defined( 'ABSPATH' ) || die();

final class LearnDash_MailChimp_Shortcode_Course {

    /**
	 * LearnDash_MailChimp_Shortcode_Course constructor.
	 * 
	 * @since		1.0.0
	 */
	function __construct() {
	
		add_shortcode( 'ld_mailchimp', array( $this, 'course_shortcode' ) );
		
	}
	
    /**
     * Add Subscription Form via Shortcode
     * 
     * @param		array  $atts    Shortcode Atts
     * @param		string $content Shortcode Content. Unused.
     *                                            
     * @access		public
     * @since		1.0.0
     * @return		string HTML
     */
    public function course_shortcode( $atts, $content = '' ) {

        global $post;
		
        if ( ! is_user_logged_in() ) return '';

		$user_id = get_current_user_id();
		$user_data = get_userdata($user_id);
		$user_email = $user_data->user_email;
		
		$atts = shortcode_atts(
			array( // a few default values
				'course_id' => '',
			),
			$atts,
			'ld_mailchimp'
		);
		
		$post_type = get_post_type();
		$course_id = false;
			
		// You can determine Course from other LearnDash Content Types
		if ( empty( $atts['course_id'] ) && 
			strpos( $post_type, 'sfwd' ) !== false ) {
			
			if ( $post_type == 'sfwd-courses' ) {
				$course_id = get_the_ID();
			}
			else {
				
				$post_meta = get_post_meta( get_the_ID(), '_' . $post_type, true );
				
				if ( isset( $post_meta[ $post_type . '_course' ] ) && 
					$post_meta[ $post_type . '_course' ] != '' ) {
					$course_id = $post_meta[ $post_type . '_course' ];
				}
				
			}
			
		}
		else {
			if ( ! empty( $atts['course_id'] ) ) {
				$course_id = trim( $atts['course_id'] );
			}
		}
		
		if ( $course_id ) {
			
			$list_id = ld_mailchimp_get_option( 'mailchimp_list' );
			$segment_id = get_post_meta( $course_id, 'ld_mailchimp_course_segment_' . $list_id, true );
			
			if ( $list_id && 
			   $segment_id && 
			   LDMAILCHIMP()->mailchimp_api ) {
				
				$subscribed_emails = ld_mailchimp_get_list_segment_emails( $segment_id, $list_id );
				
				if ( in_array( $user_email, $subscribed_emails ) ) {
					return apply_filters( 'learndash_mailchimp_already_subscribed_text', __( 'Subscribed!', 'learndash-mailchimp' ) );
				} else{
					return ld_mailchimp_subscribe_form( $course_id );
				}
				
			}
			
		}
		
		return '';
		
    }

}

$instance = new LearnDash_MailChimp_Shortcode_Course();