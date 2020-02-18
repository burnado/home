<?php
/**
 * Subscription Form for LearnDash Mailchimp
 * This can be overridden by the Theme under ./learndash-mailchimp/subscribe-form.php 
 *
 * @since		1.0.0
 *
 * @package LearnDash_MailChimp
 * @subpackage LearnDash_MailChimp/core/views
 */

defined( 'ABSPATH' ) || die(); ?>

<form action="" method="post">
	
	<input type="hidden" name="ld_mailchimp_course_id" value="<?php echo $course_id; ?>">
	<?php wp_nonce_field( "ld_mailchimp_subscribe_course_id_$course_id", 'ld_mailchimp_subscribe_course_nonce' ); ?>

	<input type="submit" name="ld_mailchimp_submit_subscribed" value="<?php echo ld_mailchimp_get_option( 'subscription_message', __( 'Subscribe to our newsletter', 'learndash-mailchimp' ) ); ?>" />

</form>