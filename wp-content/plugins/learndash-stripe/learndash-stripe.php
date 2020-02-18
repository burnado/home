<?php
/**
 * Plugin Name: LearnDash LMS - Stripe Integration
 * Plugin URI: 
 * Description:	Integrate Stripe payment gateway with LearnDash. 
 * Version: 1.3.0
 * Author: LearnDash
 * Author URI: http://www.learndash.com/
 * Text Domain: learndash-stripe
 * Domain Path: languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Check if class name already exists
if ( ! class_exists( 'LearnDash_Stripe' ) ) :

/**
* Main class
*
* @since  0.1
*/
final class LearnDash_Stripe {
	
	/**
	 * The one and only true LearnDash_Stripe instance
	 *
	 * @since 0.1
	 * @access private
	 * @var object $instance
	 */
	private static $instance;

	/**
	 * Instantiate the main class
	 *
	 * This function instantiates the class, initialize all functions and return the object.
	 * 
	 * @since 0.1
	 * @return object The one and only true LearnDash_Stripe instance.
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ( ! self::$instance instanceof LearnDash_Stripe ) ) {

			self::$instance = new LearnDash_Stripe();
			self::$instance->setup_constants();
			
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
		}

		return self::$instance;
	}	

	/**
	 * Function for setting up constants
	 *
	 * This function is used to set up constants used throughout the plugin.
	 *
	 * @since 0.1
	 */
	public function setup_constants() {

		// Plugin version
		if ( ! defined( 'LEARNDASH_STRIPE_VERSION' ) ) {
			define( 'LEARNDASH_STRIPE_VERSION', '1.3.0' );
		}

		// Plugin file
		if ( ! defined( 'LEARNDASH_STRIPE_FILE' ) ) {
			define( 'LEARNDASH_STRIPE_FILE', __FILE__ );
		}		

		// Plugin folder path
		if ( ! defined( 'LEARNDASH_STRIPE_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_STRIPE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL
		if ( ! defined( 'LEARNDASH_STRIPE_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_STRIPE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Load text domain used for translation
	 *
	 * This function loads mo and po files used to translate text strings used throughout the 
	 * plugin.
	 *
	 * @since 0.1
	 */
	public function load_textdomain() {

		// Set filter for plugin language directory
		$lang_dir = dirname( plugin_basename( LEARNDASH_STRIPE_FILE ) ) . '/languages/';
		$lang_dir = apply_filters( 'learndash_stripe_languages_directory', $lang_dir );

		// Load plugin translation file
		load_plugin_textdomain( 'learndash-stripe', false, $lang_dir );

		// include translation/update class
		include LEARNDASH_STRIPE_PLUGIN_PATH . 'includes/class-translations-ld-stripe.php';
	}

	/**
	 * Includes all necessary PHP files
	 *
	 * This function is responsible for including all necessary PHP files.
	 *
	 * @since  0.1
	 */
	public function includes() {
		$options = get_option( 'learndash_stripe_settings', array() );

		if ( is_admin() ) {
			include LEARNDASH_STRIPE_PLUGIN_PATH . '/includes/admin/settings/class-settings.php';
		}

		if ( isset( $options['integration_type'] ) && $options['integration_type'] === 'legacy_checkout' ) {
			include LEARNDASH_STRIPE_PLUGIN_PATH . '/includes/class-stripe-legacy-checkout-integration.php';
		} else {
			include LEARNDASH_STRIPE_PLUGIN_PATH . '/includes/class-stripe-checkout-integration.php';
		}
	}
}

endif; // End if class exists check

/**
 * The main function for returning instance
 *
 * @since 0.1
 * @return object The one and only true instance.
 */
function learndash_stripe() {
	return LearnDash_Stripe::instance();
}

// Run plugin
learndash_stripe();