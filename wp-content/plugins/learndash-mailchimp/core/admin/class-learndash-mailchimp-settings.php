<?php
/**
 * The Admin Side LearnDash MailChimp
 *
 * @since		1.0.0
 *
 * @package LearnDash_MailChimp
 * @subpackage LearnDash_MailChimp/core/admin
 */

defined( 'ABSPATH' ) || die();

final class LearnDash_MailChimp_Admin {

	/**
	 * LearnDash_MailChimp_Admin constructor.
	 * 
	 * @since		1.0.0
	 */
	function __construct() {
		
		if ( isset( $_REQUEST[ 'ld_mailchimp_delete_segments_submit' ] ) ) {
			
			add_action( 'admin_init', array( $this, 'delete_all_segments' ) );
			
		}
		
		if ( ! isset( $_REQUEST[ 'ld_mailchimp_course_unlisted_submit' ] ) ) {
			
			// Show notice if Courses do not have corresponding segments
			add_action( 'admin_init', array( $this, 'add_missing_segments_notice' ) );
			
		}
		else {
			
			add_action( 'admin_init', array( $this, 'add_missing_segments' ) );
			
		}
		
		// Creates a (temporary) Submenu Item for our Admin Page
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		
		// Register our Fields as WP Settings
		add_action( 'admin_init', array( $this, 'register_options' ) );
		
		// Localize the admin.js
		add_filter( 'ld_mailchimp_localize_admin_script', array( $this, 'localize_script' ) );
		
		// Enqueue our Styles/Scripts on our Settings Page
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		// Fix <title> tag for the Settings Page
		add_filter( 'admin_title', array( $this, 'admin_title' ), 10, 2 );
		
		// Add our Settings Tab to a "Tab Group" within a Settings Page
		add_action( 'learndash_admin_tabs_set', array( $this, 'admin_tabs' ) );
			
		// Fakes the Current Menu Item
		add_filter( 'parent_file', array( $this, 'fix_parent_file' ) );

		// Fakes the current Submenu Item
		add_filter( 'submenu_file', array( $this, 'fix_submenu_file' ), 10, 2 );
		
		// Legacy "Settings API" support
		add_action( 'ld_mailchimp_fieldhelpers_do_field', array( $this, 'ld_mailchimp_after_setting_output_hook' ), 99, 4 );
		
		// Add API Key Status Indicator
		add_action( 'ld_mailchimp_after_setting_output', array( $this, 'api_key_status_indicator' ), 10, 4 );
		
		// Button to delete all Segments and Course Associations
		add_action( 'ld_mailchimp_delete_segments_button', array( $this, 'delete_segments_button' ) );
		
	}
	
	/**
	 * Creates a (temporary) Submenu Item for our Admin Page
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function admin_menu() {
		
		// We're hooking into options-general.php so that we have better access to WP's Settings API
		$page_hook = add_submenu_page(
			'options-general.php',
			_x( 'MailChimp', 'MailChimp Tab Label', 'learndash-mailchimp' ),
			_x( 'MailChimp', 'MailChimp Tab Label', 'learndash-mailchimp' ),
			'manage_options',
			'learndash_mailchimp',
			array( $this, 'admin_page' )
		);
		
		global $submenu;
	
		// Ensure that we don't have errors from a non-existing Index for non-Admins
		if ( isset( $submenu['options-general.php'] ) ) {
			
			$settings_index = null;
			foreach ( $submenu['options-general.php'] as $key => $menu_item ) {

				// Index 2 is always the child page slug
				if ( $menu_item[2] == 'learndash_mailchimp' ) {
					$settings_index = $key;
					break;
				}

			}

			// Unset from the Submenu
			unset( $submenu['options-general.php'][ $settings_index ] );

		}
		
	}
	
	/**
	 * Output our Admin Page (Finally!)
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		HTML
	 */
	public function admin_page() {
			
		settings_errors(); ?>

		<div id="tab_container">

			<form method="post" action="options.php">

				<?php settings_fields( 'learndash_mailchimp' ); ?>

				<?php do_settings_sections( 'learndash_mailchimp' ); ?>

				<?php submit_button(); ?>

			</form>

		</div>

		<?php
		
	}
	
	/**
	 * Regsiter Options for each Field
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function register_options() {
		
		if ( false === get_option( 'learndash_mailchimp' ) ) {
			add_option( 'learndash_mailchimp' );
		}
		
		add_settings_section(
			'learndash_mailchimp',
			__return_null(),
			'__return_false',
			'learndash_mailchimp'
		);
		
		// Holds all non-repeater values
		$global_values = get_option( 'learndash_mailchimp' );
		
		$fields = array(
			array(
				'type' => 'text',
				'settings_label' => __( 'MailChimp API Key', 'learndash-mailchimp' ),
				'name_base' => 'learndash_mailchimp',
				'name' => 'api_key',
				'no_init' => true,
				'option_field' => true,
				'input_atts' => array(
					'pattern' => '.*?-.*?',
					'title' => __( 'All MailChimp API Keys have a hyphen in them', 'learndash-mailchimp' ),
				),
			),
		);
		
		if ( $api_key_validity = get_transient( 'ld_mailchimp_api_key_validity' ) == 'valid' ) {
			
			$mailchimp_lists = array();
			
			if ( LDMAILCHIMP()->mailchimp_api ) {
			
				$results = LDMAILCHIMP()->mailchimp_api->get( '/lists/' );

				if ( isset( $results['lists'] ) ) {

					foreach ( $results['lists'] as $list ) {
						$mailchimp_lists[ $list['id'] ] = $list['name'];
					}

				}
				
				asort( $mailchimp_lists );
				
			}
			
			$valid_api_key_fields = array(
				array(
					'type' => 'select',
					'settings_label' => __( 'MailChimp List', 'learndash-mailchimp' ),
					'name_base' => 'learndash_mailchimp',
					'name' => 'mailchimp_list',
					'no_init' => true,
					'option_field' => true,
					'options' => array(
						'' => __( '-- Select a List --', 'learndash-mailchimp' ),
					) + $mailchimp_lists,
				),
				array(
					'type' => 'textarea',
					'settings_label' => __( 'Subscription Registration Message', 'learndash-mailchimp' ),
					'name_base' => 'learndash_mailchimp',
					'name' => 'subscription_message',
					'no_init' => true,
					'option_field' => true,
					'input_atts' => array(
						'placeholder' => __( 'Subscribe to our newsletter', 'learndash-mailchimp' ),
					),
				),
			);
			
			if ( $list_id = ld_mailchimp_get_option( 'mailchimp_list' ) ) {
		
				$courses = new WP_Query( array(
					'post_type' => 'sfwd-courses',
					'posts_per_page' => -1,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'ld_mailchimp_course_segment_' . $list_id,
							'compare' => 'EXISTS',
						),
					),
				) );
				
				if ( $courses->have_posts() ) {
					
					$valid_api_key_fields[] = array(
						'type' => 'hook',
						'settings_label' => __( 'Delete All Created Segments', 'learndash-mailchimp' ),
						'name' => 'delete_segments_button',
						'count' => count( $courses->posts ),
					);
					
				}
				
			}
			
			$fields = array_merge( $fields, $valid_api_key_fields );
			
		}
		
		foreach ( $fields as $field ) {
			
			$field = wp_parse_args( $field, array(
				'settings_label' => '',
			) );
			
			$callback = 'ld_mailchimp_' . $field['type'] . '_callback';
			
			add_settings_field(
				$field['name'],
				$field['settings_label'],
				( is_callable( $callback ) ) ? 'ld_mailchimp_' . $field['type'] . '_callback' : 'ld_mailchimp_missing_callback',
				'learndash_mailchimp',
				'learndash_mailchimp',
				$field
			);
			
		}
		
		register_setting( 'learndash_mailchimp', 'learndash_mailchimp' );
		
	}
	
	/**
	 * Localize the Admin.js with some values from PHP-land
	 * 
	 * @param	  array $l10n Array holding all our Localizations
	 *														
	 * @access	  public
	 * @since	  1.0.0
	 * @return	  array Modified Array
	 */
	public function localize_script( $l10n ) {
		
		$l10n['ajax'] = admin_url( 'admin-ajax.php' );
		
		return $l10n;
		
	}
	
	/**
	 * Enqueue our CSS/JS on our Settings Page
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function admin_enqueue_scripts() {
		
		global $current_screen;

		if ( $current_screen->base == 'settings_page_learndash_mailchimp' ) {
			
			wp_enqueue_style( 'learndash-mailchimp-admin' );
			
		}
		
	}
	
	/**
	 * Fix the Admin Title since our pages "don't exist"
	 * 
	 * @param		string $admin_title The page title, with extra context added
	 * @param		string $title       The original page title
	 *                                               
	 * @access		public
	 * @since		1.0.0
	 * @return		string Admin Title
	 */
	public function admin_title( $admin_title, $title ) {
		
		global $current_screen;
		
		if ( $current_screen->base == 'settings_page_learndash_mailchimp' ) {
			return __( 'LearnDash MailChimp Settings', 'learndash-mailchimp' ) . $admin_title;
		}
		
		return $admin_title;
		
	}
	
	/**
	 * Adds the Admin Tab using LD v2.4's new method
	 * 
	 * @param		string $admin_menu_section Admin Menu Section
	 *                                               
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function admin_tabs( $admin_menu_section = '' ) {

		if ( $admin_menu_section == 'admin.php?page=learndash_lms_settings' ) {
			
			learndash_add_admin_tab_item(
				'admin.php?page=learndash_lms_settings',
				array(
					'id' => 'settings_page_learndash_mailchimp',
					'link' => add_query_arg( array( 'page' => 'learndash_mailchimp' ), 'options-general.php' ),
					'name' => _x( 'MailChimp', 'MailChimp Tab Label', 'learndash-mailchimp' ),
				),
				40
			);
			
		}
		
	}
	
	/**
	 * Fakes the Current Menu Item
	 * 
	 * @param		string $parent_file Parent Menu Item
	 *														
	 * @access		public
	 * @since		1.0.0
	 * @return		string Modified String
	 */
	public function fix_parent_file( $parent_file ) {
	
		global $current_screen;
		global $self;

		if ( $current_screen->base == 'settings_page_learndash_mailchimp' ) {
				
			// Render this as the Active Page Menu
			$parent_file = 'admin.php?page=learndash_lms_settings';

			// Ensure the top-level "Settings" doesn't show as active
			$self = 'learndash-lms';

		}

		return $parent_file;

	}
	
	/**
	 * Fakes the current Submenu Item
	 * 
	 * @param		string $submenu_file Current Menu Item
	 * @param		string $parent_file  Parent Menu Item
	 *
	 * @access		public
	 * @since		1.0.0
	 * @return		string Modified String
	 */
	public function fix_submenu_file( $submenu_file, $parent_file ) {

		global $current_screen;

		if ( $current_screen->base == 'settings_page_learndash_mailchimp' ) {
				
			$submenu_file = 'admin.php?page=learndash_lms_settings';

		}

		return $submenu_file;

	}
		
	/**
	 * Support the old hook as closely as we really can
	 * 
	 * @param		string $type  Field type
	 * @param		array  $args  Field args
	 * @param		string $name  Field name
	 * @param		mixed  $value Field value
	 *                             
	 * @access		public
	 * @since		1.0.4
	 * @return		void
	 */
	public function ld_mailchimp_after_setting_output_hook( $type, $args, $name, $value ) {
		
		do_action( 'ld_mailchimp_after_setting_output', $type, $args, $name, $value );
		
	}
	
	/**
	 * Outputs the API Key Valid/Invalid indicator
	 * 
	 * @param		string $html HTML
	 * @param		array  $args Field Args
	 *                            
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function api_key_status_indicator( $type, $args, $name, $value ) {
		
		if ( ! isset( $args['name'] ) ||
			! isset( $args['name_base'] ) || 
			$args['name'] !== 'api_key' || 
		   $args['name_base'] !== 'learndash_mailchimp' ) return false;
		
		$validity = get_transient( 'ld_mailchimp_api_key_validity' );
		
		if ( $validity == 'valid' ) : ?>
			<span class="api-key-status valid">
				<?php _e( 'Valid API Key', 'learndash-mailchimp' ); ?>
			</span>
		<?php elseif ( $validity !== false ) : ?>
			<span class="api-key-status invalid">
				<?php _e( 'Invalid API Key', 'learndash-mailchimp' ); ?>
			</span>
		<?php endif;
		
		wp_nonce_field( 'ld_mailchimp_check_api_key', 'ld_mailchimp_check_api_key_nonce' );
		
	}
	
	/**
	 * Shows the Delete Segments Button
	 * 
	 * @param 		array $args Field Args
	 *
	 * @access		public
	 * @since		1.0.4
	 * @return		void
	 */
	public function delete_segments_button( $args ) { ?>
		
		<div class="button-container">
			<input type="submit" name="ld_mailchimp_delete_segments_submit" class="button-primary" value="<?php printf( __( 'Delete %s Segment(s)', 'learndash-mailchimp' ), $args['count'] ); ?>" />
		</div>

		<p class="description">
			<?php _e( 'This will delete all of the List Segments for your Courses in MailChimp and remove the association between them.', 'learndash-mailchimp' ); ?>
		</p>

		<?php wp_nonce_field( 'ld_mailchimp_delete_segments', 'ld_mailchimp_delete_segments_nonce' ); ?>

		<?php
		
	}
	
	/**
	 * Shows an Admin Notice if Segments need to be created for our chosen List
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function add_missing_segments_notice() {

		if ( ! isset( $_GET['page'] ) || 
			$_GET['page'] !== 'learndash_mailchimp' ) return false;
		
		if ( ! $list_id = ld_mailchimp_get_option( 'mailchimp_list' ) ) return false;
		
		$courses = new WP_Query( array(
			'post_type' => 'sfwd-courses',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'ld_mailchimp_course_segment_' . $list_id,
					'compare' => 'NOT EXISTS',
				),
			),
		) );
		
		if ( $courses->have_posts() ) :

			ob_start(); ?>
			
			<form method="post">
				
				<div>
					
					<p>
						<strong>
							<?php printf( 
								__( 'You have %s %s not in your List', 'learndash-mailchimp' ),
								$courses->post_count,
								( $courses->post_count > 1 ) ? LearnDash_Custom_Label::get_label( 'courses' ) : LearnDash_Custom_Label::get_label( 'course' )
							); ?>
						</strong>
					</p>

					<?php wp_nonce_field( 'ld_mailchimp_add_missing_segments', 'ld_mailchimp_add_missing_segments_nonce' ); ?>
					
				</div>
				
				<div class="button-container">
					<input type="submit" name="ld_mailchimp_course_unlisted_submit" class="button-primary" value="<?php _e( 'Create Segments', 'learndash-mailchimp' ); ?>" />
				</div>
				
				<input type="hidden" name="ld_mailchimp_course_unlisted_submit" class="submit-hidden" value="<?php _e( 'Create Segments', 'learndash-mailchimp' ); ?>" />

			</form>

			<?php 
		
			$message = ob_get_clean();
		
			add_settings_error(
				'learndash_mailchimp',
				'',
				$message,
				'error ld-mailchimp-notice ld-mailchimp-segments-notice'
			);
			
		endif;
		
	}
	
	/**
	 * Deletes all Segments
	 * 
	 * @access		void
	 * @since		1.0.4
	 * @return		void
	 */
	public function delete_all_segments() {
		
		if ( ! $list_id = ld_mailchimp_get_option( 'mailchimp_list' ) ) return false;
		
		if ( ! isset( $_REQUEST['ld_mailchimp_delete_segments_nonce'] ) || 
			! wp_verify_nonce( $_REQUEST[ 'ld_mailchimp_delete_segments_nonce' ], 'ld_mailchimp_delete_segments' ) ) return false;
		
		// get listed courses
		$courses = new WP_Query( array(
			'post_type' => 'sfwd-courses',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'ld_mailchimp_course_segment_' . $list_id,
					'compare' => 'EXISTS',
				),
			)
		) );
		
		global $post;

		if ( $courses->have_posts() ) :
			
			while ( $courses->have_posts() ) : $courses->the_post();
					
				// Delete Segment from list
				ld_mailchimp_remove_course_segment_from_list( $post, $list_id );
				
			endwhile;
		
			wp_reset_postdata();
		
			add_settings_error(
				'learndash_mailchimp',
				'',
				__( 'Segments Deleted Successfully', 'learndash-mailchimp' ),
				'updated ld-mailchimp-notice'
			);
			
		endif;
		
	}
	
    /**
     * Creates missing Segments for Courses that do not have one
     * 
     * @access		public
     * @since		1.0.0
     * @return		void
     */
    public function add_missing_segments() {
		
		if ( ! $list_id = ld_mailchimp_get_option( 'mailchimp_list' ) ) return false;
		
		if ( ! isset( $_REQUEST['ld_mailchimp_add_missing_segments_nonce'] ) || 
			! wp_verify_nonce( $_REQUEST[ 'ld_mailchimp_add_missing_segments_nonce' ], 'ld_mailchimp_add_missing_segments' ) ) return false;
		
		// get unlisted courses
		$courses = new WP_Query( array(
			'post_type' => 'sfwd-courses',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'ld_mailchimp_course_segment_' . $list_id,
					'compare' => 'NOT EXISTS',
				),
			)
		) );
		
		global $post;

		if ( $courses->have_posts() ) :
			
			while ( $courses->have_posts() ) : $courses->the_post();
					
				// Add Course as a Segment in the List
				$segment_id = ld_mailchimp_add_segment_to_list( $post, $list_id );

				if ( $segment_id ) {
					update_post_meta( get_the_ID(), 'ld_mailchimp_course_segment_' . $list_id, $segment_id );
				}
				
			endwhile;
		
			wp_reset_postdata();
		
			add_settings_error(
				'learndash_mailchimp',
				'',
				__( 'Segments Created Successfully', 'learndash-mailchimp' ),
				'updated ld-mailchimp-notice'
			);
			
		endif;
		
    }

}

$instance = new LearnDash_MailChimp_Admin();