<?php
/*
Plugin Name:  Builder Typewriter
Plugin URI:   https://themify.me/addons/builder-typewriter
Version:      1.1.5
Description:  This Builder addon allows you to create a module with typewriter effect. It requires to use with the latest version of any Themify theme or the Themify Builder plugin.
Author:       Themify
Author URI:   https://themify.me/
Text Domain:  builder-typewriter
Domain Path:  /languages
*/

/* Exit if accessed directly */
defined( 'ABSPATH' ) or die( '-1' );


class Builder_Typewriter {
	
	public $version;
	public $url;
	private $dir;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return	A single instance of this class.
	 */
	public static function get_instance() {
		if ( version_compare( PHP_VERSION, '5.3.0' ) < 0 ) {
			add_action( 'admin_notices', array( __CLASS__ ,'builder_typewriter_admin_notice' ));
			return;
		}
		static $instance = null;
		if($instance===null){
			$instance = new self;
		}
		return $instance;
	}

	/**
	 * Constructor
	 *
	 * @access	private
	 * @return	void
	 */
	private function __construct() {
		$this->constants();

		add_action( 'plugins_loaded', array( $this, 'i18n' ), 5 );
		add_action( 'themify_builder_setup_modules', array( $this, 'register_module' ) );
		add_filter( 'plugin_row_meta', array( $this, 'themify_plugin_meta'), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'action_links') );
		add_filter('themify_main_script_vars',array( $this, 'minify_vars' ),10,1);
	}

	private function constants() {
		$data = get_file_data( __FILE__, array( 'Version' ) );
		$this->version = $data[0];
		$this->url = trailingslashit( plugin_dir_url( __FILE__ ) );
		$this->dir = trailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function themify_plugin_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$row_meta = array(
			  'changelogs'    => '<a href="' . esc_url( 'https://themify.me/changelogs/' ) . basename( dirname( $file ) ) .'.txt" target="_blank" aria-label="' . esc_attr__( 'Plugin Changelogs', 'themify' ) . '">' . esc_html__( 'View Changelogs', 'themify' ) . '</a>'
			);
	 
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}
	public function action_links( $links ) {
		if ( is_plugin_active( 'themify-updater/themify-updater.php' ) ) {
			$tlinks = array(
			 '<a href="' . admin_url( 'index.php?page=themify-license' ) . '">'.__('Themify License', 'themify') .'</a>',
			 );
		} else {
			$tlinks = array(
			 '<a href="' . esc_url('https://themify.me/docs/themify-updater-documentation') . '">'. __('Themify Updater', 'themify') .'</a>',
			 );
		}
		return array_merge( $links, $tlinks );
	}
	public function i18n() {
		load_plugin_textdomain( 'builder-typewriter', false, '/languages' );
	}

	public function admin_enqueue() {
		wp_enqueue_script( 'themify-builder-typewriter-admin-scripts', themify_enque($this->url . 'assets/admin-scripts.js'), array( 'jquery' ), $this->version,true );
	}

	public function register_module() {
		Themify_Builder_Model::register_directory( 'templates', $this->dir . 'templates' );
		Themify_Builder_Model::register_directory( 'modules', $this->dir . 'modules' );
               
	}

        public function minify_vars($vars){
            $vars['minify']['js']['jquery.typer.themify'] = themify_enque($this->url . 'assets/jquery.typer.themify.js',true);
            return $vars;
        }
        
        public function builder_typewriter_admin_notice() {
        ?>
            <div class="error">
                    <p><?php _e( 'This addon requires PHP 5.3 or higher.', 'builder-typewriter' ); ?></p>
            </div>
        <?php
                deactivate_plugins( plugin_basename( __FILE__ ) );
        }
}

Builder_Typewriter::get_instance();