<?php
/**
 * Adds the licensing settings..
 *
 * @since		1.0.0
 *
 * @package LearnDash_MailChimp
 * @subpackage LearnDash_MailChimp/licensing
 */

defined( 'ABSPATH' ) || die();

final class LearnDash_MailChimp_License_Field {

	/**
	 * LearnDash_MailChimp_License_Field constructor.
	 *
	 * @since		1.0.0
	 */
	function __construct() {
		
		// License Settings Section
		add_action( 'sfwd_lms-nss_license_footer', array( $this, 'licensing_settings_section' ) );
		
		// Enqueue some minimal styles
		add_action( 'admin_page_nss_plugin_license-sfwd_lms-settings', array( $this, 'enqueue_styles' ) );
		
	}

	/**
	 * The Licensing Settings Section in which we output our Settings Fields
	 *
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function licensing_settings_section() {
		
		settings_errors( 'ld_mailchimp_support' ); 

		?>

		<div class="learndash-mailchimp-wrap wrap">

			<h2>
				<?php echo LDMAILCHIMP()->plugin_data['Name']; ?>
			</h2>
			
			<div class="rbp-support-licensing-form">

				<form method="post">

					<?php LDMAILCHIMP()->support->licensing_fields(); ?>
					<?php LDMAILCHIMP()->support->beta_checkbox(); ?>

				</form>

			</div>

			<?php LDMAILCHIMP()->support->support_form(); ?>
			
		</div>

		<?php
		
	}
	
	public function enqueue_styles() {
		
		LDMAILCHIMP()->support->enqueue_all_scripts();
		
		wp_enqueue_style( 'learndash-mailchimp-admin' );
		
	}
	
}

$instance = new LearnDash_MailChimp_License_Field();