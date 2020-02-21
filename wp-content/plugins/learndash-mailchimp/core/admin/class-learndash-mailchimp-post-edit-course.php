<?php
/**
 * Post Edit Screen additions for LearnDash MailChimp
 *
 * @since		1.0.0
 *
 * @package LearnDash_MailChimp
 * @subpackage LearnDash_MailChimp/core/admin
 */

defined( 'ABSPATH' ) || die();

final class LearnDash_MailChimp_Settings_Course {
	
	/**
	 * LearnDash_MailChimp_Settings_Course constructor.
	 * 
	 * @since		1.0.0
	 */
	function __construct() {
		
		if ( $api_key_validity = get_transient( 'ld_mailchimp_api_key_validity' ) == 'valid' ) {
			
			add_action('add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			
			add_action( 'save_post', array( $this, 'save_post' ) );
			
			add_action( 'post_updated', array( $this, 'post_updated'), 10, 3 );
			
		}
		
	}

    /**
     * Add the Metabox to the Course Edit Page
     * 
     * @access		public
     * @since		1.0.0
     * @return		void
     */
    public function add_meta_boxes() {
		
		add_meta_box(
			'ld_mailchimp_course_metabox',
			__( 'MailChimp Course Options', 'learndash-mailchimp' ),
			array( $this, 'course_metabox_content' ),
			'sfwd-courses',
			'side'
		);
		
    }

    /**
     * Holds the Metabox Content
     * 
     * @access		public
     * @since		1.0.0
     * @return		void
     */
    public function course_metabox_content() {
		
		// Legacy support
		$value = ld_mailchimp_get_field( 'display_subscription_form' );
		$value = ( ! is_array( $value ) ) ? array( $value ) : $value;
		
		ld_mailchimp_checkbox_callback( array(
			'name' => 'display_subscription_form',
			'label' => __( 'Show Subscription Form', 'learndash-mailchimp' ),
			'value' => $value,
			'group' => 'ld_mailchimp_course_metabox',
		) );
		
		ld_mailchimp_init_field_group( 'ld_mailchimp_course_metabox' );
		
		wp_nonce_field( 'ld_mailchimp_save_subscription_form_setting', 'ld_mailchimp_save_subscription_form_setting_nonce' );
		
    }

    /**
     * Save Post Meta and create Segment if necessary
     * 
     * @param		integer $post_id WP_Post ID
     *                                  
     * @access		public
     * @since		1.0.0
     * @return		void
     */
    public function save_post( $post_id ) {
		
		// Make sure we should be here!
		if ( ! isset( $_POST['ld_mailchimp_save_subscription_form_setting_nonce'] ) ||
			 ! wp_verify_nonce( $_POST['ld_mailchimp_save_subscription_form_setting_nonce'], 'ld_mailchimp_save_subscription_form_setting' ) ||
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || 
			( defined( 'DOING_AJAX' ) && DOING_AJAX ) || 
			( false !== wp_is_post_revision( $post_id ) ) || 
			 ! current_user_can( 'edit_posts' ) ) return;
		
		// Only run for Courses
        if ( isset( $_POST['post_type'] ) && 
			$_POST['post_type'] == 'sfwd-courses' ) {
			
			$list_id = ld_mailchimp_get_option( 'mailchimp_list' );
			
            // If a segment doesn't exist, Create one for the User automatically
            $segment_id = get_post_meta( $post_id, 'ld_mailchimp_course_segment_' . $list_id, true );
			
            if ( ! $segment_id ) {
				
                $course = get_post( $post_id );
				
                $segment_id = ld_mailchimp_add_segment_to_list( $course, $list_id );
				
                if ( $segment_id ) {
                    update_post_meta( $post_id, 'ld_mailchimp_course_segment_' . $list_id, $segment_id );
                }
				
            }
			
        }
		
    }

    /**
     * Update Segment Name to match any changes to the Course
     * 
     * @param		integer $post_id     WP_Post ID
     * @param		object  $post_after  WP_Post Object after changes
     * @param		object  $post_before WP_Post Object before changes
     *                  
     * @access		public
     * @since		1.0.0
     * @return		void
     */
    public function post_updated( $post_id, $post_after, $post_before ) {
		
		if ( get_post_type( $post_id ) !== 'sfwd-courses' ) return;
		
        $list_id = ld_mailchimp_get_option( 'mailchimp_list' );
		
        $course_name_before = $post_before->post_title;
        $course_name_after = $post_after->post_title;
		
        if ( $course_name_after != $course_name_before ) {
				
			$segment_id = get_post_meta( $post_id, 'ld_mailchimp_course_segment_' . $list_id, true );
			$emails = ld_mailchimp_get_list_segment_emails( $segment_id, $list_id );

			if ( $segment_id ) {

				$result = ld_mailchimp_update_segment( $segment_id, $course_name_after, $list_id, $emails );

			}
			else {

				// Need to create Segment first
				$segment_id = ld_mailchimp_add_segment_to_list( $post_after, $list_id );

				if ( $segment_id ) {

					update_post_meta( $post_id, 'ld_mailchimp_course_segment_' . $list_id, $segment_id );

					$result = ld_mailchimp_update_segment( $segment_id, $course_name_after, $list_id, $emails );

				}

			}
			
        }
		
    }

}

$instance = new LearnDash_MailChimp_Settings_Course();