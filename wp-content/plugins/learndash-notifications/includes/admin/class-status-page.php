<?php

if ( class_exists( 'LearnDash_Settings_Page' ) ) :

class LearnDash_Notifications_Status_Page extends LearnDash_Settings_Page
{

	public function __construct() 
	{	
		$this->parent_menu_page_url		=	'edit.php?post_type=ld-notification';
		$this->menu_page_capability		=	LEARNDASH_ADMIN_CAPABILITY_CHECK;
		$this->settings_page_id 		= 	'ld-notifications-status';
		$this->settings_page_title 		= 	__( 'LearnDash Notifications Status', 'learndash-notifications' );
		$this->settings_tab_title		=	__( 'Status', 'learndash-notifications' );
		$this->settings_tab_priority	=	3;
		$this->show_settings_page_function	=	array( $this, 'show_settings_page' );
			
		parent::__construct(); 
	}

	public function show_settings_page()
	{
		global $wpdb;
		$values = get_option( 'learndash_notifications_status', array() );
		include_once LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'templates/admin/status-page.php';
	}
}

add_action( 'learndash_settings_pages_init', function() {
	LearnDash_Notifications_Status_Page::add_page_instance();
} );

endif;