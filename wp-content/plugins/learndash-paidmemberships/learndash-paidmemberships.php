<?php
/**
 * @package LearnDash LMS - Paid Memberships Pro
 * @version 1.1.1
 */
/*
/*
Plugin Name: LearnDash LMS - Paid Memberships Pro
Plugin URI: http://www.learndash.com
Description: LearnDash integration with the Paid Memberships Pro plugin that allows to control the course's access by a user level.
Version: 1.1.1
Author: LearnDash
Author URI: http://www.learndash.com
Text Domain: learndash-paidmemberships
Doman Path: /languages/

*/


if ( ! class_exists( 'Learndash_Paidmemberships' ) ) {

class Learndash_Paidmemberships {
	public static function define_constants() {
		// Plugin version
		if ( ! defined( 'LEARNDASH_PMP_VERSION' ) ) {
			define( 'LEARNDASH_PMP_VERSION', '1.1.1' ); 
		}

		// Plugin file
		if ( ! defined( 'LEARNDASH_PMP_FILE' ) ) {
			define( 'LEARNDASH_PMP_FILE', __FILE__ );
		}		

		// Plugin folder path
		if ( ! defined( 'LEARNDASH_PMP_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_PMP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL
		if ( ! defined( 'LEARNDASH_PMP_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_PMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	public static function i18nize() {
		load_plugin_textdomain( 'learndash-paidmemberships', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 	

		// include translations class
		include LEARNDASH_PMP_PLUGIN_PATH . 'includes/class-translations-ld-paidmemberships.php';
	}

	public static function addResources(){
		// wp_enqueue_style('ld_paidmemberships', plugins_url('css/ld_paidmemberships.css', __FILE__));
		//wp_enqueue_script('ld_paidmemberships', plugins_url('js/propanel.js', __FILE__), array('jquery'));
	}

	public static function admin_init(){
		add_meta_box("credits_meta", "Require Membership", array('Learndash_Paidmemberships', "course_level_list"), "sfwd-courses", "side", "low");
	}

	public static function course_level_list(){
		global $post;
		global $wpdb;
		global $membership_levels;
		if(!isset($wpdb->pmpro_membership_levels))
		{
			_e("Please enable Paid Memberships Pro Plugin, and create some levels", 'learndash-paidmemberships');
			return;
		}	
		$membership_levels = $wpdb->get_results( "SELECT * FROM {$wpdb->pmpro_membership_levels}", OBJECT );
		?>
		
		<?php
		$course_id = learndash_get_course_id($post->ID);
		$level_course_option = get_option('_level_course_option');
		$array_levels=explode(",",$level_course_option[$course_id]);
		
		for($num_cursos=0;$num_cursos<sizeof($membership_levels);$num_cursos++)
		{
			$checked="";
			for($tmp_array_levels=0;$tmp_array_levels<sizeof($array_levels);$tmp_array_levels++){
				if($array_levels[$tmp_array_levels]==$membership_levels[$num_cursos]->id){	
					$checked="checked";
				}
			}
			?>
			<p><input type="checkbox" name="level-curso[<?php echo $num_cursos ?>]" value="<?php echo $membership_levels[$num_cursos]->id; ?>" <?php echo $checked; ?>> <?php echo $membership_levels[$num_cursos]->name; ?></p>
			<?php
		}
	}

	public static function level_courses_list(){
		global $wpdb;
		?>		
		<h3 class="topborder"><?php _e('LearnDash', 'learndash-paidmemberships');?></h3>
		
		
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label><?php _e('Courses', 'learndash-paidmemberships');?>:</label></th>
					<td>

		<?php
		echo "<ul>";


		
		$querystr = "SELECT wposts.* FROM $wpdb->posts wposts WHERE wposts.post_type = 'sfwd-courses' AND wposts.post_status = 'publish' ORDER BY wposts.post_title";
		
		$actual_level = $_REQUEST['edit'];
		$level_course_option = get_option('_level_course_option');
		
		$my_query = $wpdb->get_results($querystr, OBJECT);
		
		if( $my_query ) {
			$tmp_num_cursos=0;
			foreach( $my_query as $s ) {
				$checked = '';
				$tmp_levels_course=explode(",",@$level_course_option[$s->ID]);
				if(in_array($actual_level, $tmp_levels_course)){
					$checked = 'checked';
				}
				?>
				<li><input type="checkbox" name="cursos[<?php echo $tmp_num_cursos; ?>]" value="<?php echo $s->ID ?>" <?php echo $checked; ?>> <?php echo $s->post_title; ?></li>
				<?php
				$tmp_num_cursos+=1;
			}


			echo "</ul>";


		}
		
		?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		
	}

	public static function generate_access_list($course_id, $levels){
		global $wpdb;
		$levels_sql = implode(',', $levels);
		$users = $wpdb->get_results("SELECT * FROM {$wpdb->pmpro_memberships_users} WHERE membership_id IN ($levels_sql) AND status='active'");
		$user_ids = array();
		foreach($users as $user){
			$user_ids[] = $user->user_id;			
		}

		$meta = get_post_meta( $course_id, '_sfwd-courses', true );
		Learndash_Paidmemberships::reassign_access_list($course_id, $user_ids);
	}

	public static function reassign_access_list($course_id, $access_list) {
		$meta = get_post_meta( $course_id, '_sfwd-courses', true );
		$old_access_list = explode(",", $meta['sfwd-courses_course_access_list']);
		foreach ($access_list as $user_id) {
			if(!in_array($user_id, $old_access_list))
				ld_update_course_access($user_id, $course_id); //Add user who was not in old list
		}
		foreach ($old_access_list as $user_id) {
			if(!in_array($user_id, $access_list))
				ld_update_course_access($user_id, $course_id, true); //Remove user who was in old list but not in new list
		}
		$meta = get_post_meta( $course_id, '_sfwd-courses', true );
	
		$level_course_option = get_option('_level_course_option');	
		if(!empty($level_course_option[$course_id]))
			$meta['sfwd-courses_course_price_type'] = 'closed';

	//		$meta['sfwd-courses_course_price'] = 'Membership';
		update_post_meta( $course_id, '_sfwd-courses', $meta );
	}

	public static function save_level_details($saveid){
		global $wpdb;
		$users_pro_list=$wpdb->get_results("SELECT * FROM {$wpdb->pmpro_memberships_users} WHERE membership_id = '$saveid' AND status='active'", ARRAY_N);
		//$users_pro_list_id=$wpdb->get_results("SELECT user_id FROM {$wpdb->pmpro_memberships_users} WHERE membership_id = '$saveid'", ARRAY_N);
		
		$new_courses = $_POST['cursos'] ? $_POST['cursos'] : array();

		$courses = get_posts(array(
			'post_type' => 'sfwd-courses',
			'post_status' => 'publish',
			'posts_per_page'   => -1
		));

		$courses_levels = get_option('_level_course_option');

		foreach($courses as $course){
			$refresh = false;
			$levels = @$courses_levels[$course->ID] ? explode(',', @$courses_levels[$course->ID]) : array();

			//If the course is in the level and it wasn't add it
			if(array_search($course->ID, $new_courses) !== FALSE && array_search($saveid, $levels) === FALSE){
				$refresh = true;
				$levels[] = $saveid;
				$courses_levels[$course->ID] = implode(',', $levels);

				self::insert_course( $saveid, $course->ID );
			}

			// When the course is not in the level but it was
			else if(array_search($course->ID, $new_courses) === FALSE && array_search($saveid, $levels) !== FALSE){				
				$refresh = true;
				$level_index = array_search($saveid, $levels);
				unset($levels[$level_index]);
				$courses_levels[$course->ID] = implode(',', $levels);

				self::delete_course_by_membership_id_course_id( $saveid, $course->ID );
			}

			if($refresh){
				self::generate_access_list($course->ID, $levels);
			}
		}

		update_option("_level_course_option",$courses_levels);
	}

	public static function save_details($post_id) {
		global $post;
		global $table_prefix, $wpdb;

		if( isset( $_POST['post_type'] ) && $_POST['post_type'] == 'sfwd-courses'){
			$course_id = learndash_get_course_id($post->ID);
			$meta = get_post_meta( $course_id, '_sfwd-courses', true );
			$current_access_list = $meta['sfwd-courses_course_access_list'];
			$current_access_list = explode( ',', $current_access_list );

			$level_course_option = get_option('_level_course_option');

			if ( isset( $_POST["level-curso"] ) && is_array( $_POST["level-curso"] ) ) {
				$access_list = array();
				$levels_list = array();

				// Delete old course page ID from pmpro_membership_pages table
				self::delete_course_by_course_id( $post_id );

				$tmp_levels_list=0;
				foreach ($_POST["level-curso"] as $x) {
					$users_pro_list=$wpdb->get_results("SELECT * FROM {$wpdb->pmpro_memberships_users} WHERE membership_id = '$x' AND status='active'", ARRAY_N);

					// Add new course page IDs to pmpro_membership_pages table
					self::insert_course( $x, $post_id );

					foreach ($users_pro_list as $user_pro){
						$access_list[]=$user_pro[1];			
					}
					$levels_list[].=$x;			
				}

				$levels_list_tmp=implode(',',$levels_list);
				$level_course_option[$course_id] = $levels_list_tmp;

				$access_list = array_merge( $current_access_list, $access_list );
				Learndash_Paidmemberships::reassign_access_list($course_id, $access_list);			
			} else {
				// Delete old course page ID from pmpro_membership_pages table
				self::delete_course_by_course_id( $post_id );

				$level_course_option[$course_id] = '';
			}
			update_option("_level_course_option", $level_course_option);
		}
	}

	/**
	 * Update user course access on user memberhip level change
	 * 
	 * @param  int $level        ID of new membership level
	 * @param  int $user_id      ID of a WP_User
	 * @param  int $cancel_level ID of old membership level
	 */
	public static function user_change_level( $level, $user_id, $cancel_level ) {
		// Add approval check if PMPro approval addon is active
		if ( class_exists( 'PMPro_Approvals' ) ) {
			if ( PMPro_Approvals::requiresApproval( $level ) && ! PMPro_Approvals::isApproved( $user_id, $level ) ) {
				return;
			}
		}

		$all_levels    = pmpro_getAllLevels();
		$active_levels = pmpro_getMembershipLevelsForUser( $user_id );

		$active_levels_ids = array();
		if ( is_array( $active_levels ) ) {
			foreach ( $active_levels as $active_level ) {
				$active_levels_ids[] = $active_level->id;
			}
		}

		if ( is_array( $all_levels ) ) {
			foreach ( $all_levels as $all_level ) {
				if ( in_array( $all_level->id, $active_levels_ids ) ) {
					continue;
				}

				Learndash_Paidmemberships::update_course_access( $all_level->id, $user_id, $remove = true );	
			}
		}

		foreach ( $active_levels_ids as $active_level_id ) {
			// enroll users
			Learndash_Paidmemberships::update_course_access( $active_level_id, $user_id );	
		}
	}

	/**
	 * Update user course access on approval (requires approval add-on)
	 * 
	 * @param  int    $meta_id    ID of meta key
	 * @param  int    $object_id  ID of a WP_User
	 * @param  string $meta_key   Meta key
	 * @param  string $meta_value Meta value
	 */
	public static function update_access_on_approval( $meta_id, $object_id, $meta_key, $meta_value ) {
		preg_match( '/pmpro_approval_(\d+)/', $meta_key, $matches );

		if ( isset( $matches[0] ) && false !== strpos( $matches[0], 'pmpro_approval' ) ) {
			$level = $matches[1];
			if ( 'approved' == $meta_value['status'] ) {
				Learndash_Paidmemberships::update_course_access( $level, $object_id );
			} else {
				Learndash_Paidmemberships::update_course_access( $level, $object_id, $remove = true );
			}
		}
	}

	/**
	 * Get a membership level's associated courses
	 * 
	 * @param  int    $level ID of a membership level
	 * @return array         Courses IDs that belong to a level
	 */
	public static function get_level_courses( $level ) {
		$courses_levels = get_option( '_level_course_option', array() );

		$courses = array();
		foreach ( $courses_levels as $course_id => $levels ) {
			$levels = explode( ',', $levels );
			if ( in_array( $level, $levels ) ) {
				$courses[] = $course_id;
			}
		}

		return $courses;
	}

	/**
	 * Update course access
	 * 
	 * @param  int  $level   ID of a membership level
	 * @param  int  $user_id ID of WP_User
	 * @param  boolean $remove  True to remove course access|false otherwise
	 */
	public static function update_course_access( $level, $user_id, $remove = false ) {
		$courses = Learndash_Paidmemberships::get_level_courses( $level );

		foreach ( $courses as $course_id ) {
			ld_update_course_access( $user_id, $course_id, $remove );
		}
	}

	/**
	 * Update course access when order is updated
	 * 
	 * @param  object $order Object of an order
	 */
	public static function udpate_course_access_on_order_update( $order ) {		
		switch ( $order->status ) {
			case 'success':
				self::give_course_access_by_order( $order );
				break;
			
			case 'cancelled':
			case 'error':
			case 'pending':
			case 'refunded':
			case 'review':
				self::remove_course_access_by_order( $order );
				break;
		}
	}

	/**
	 * Remove user course access when an order is deleted
	 * 
	 * @param  int    $order_id ID of an order
	 * @param  object $order    Order object
	 */
	public static function remove_course_access_on_order_deletion( $order_id, $order ) {
		$level    = $order->getMembershipLevel();
		$user     = $order->getUser();
		$courses  = self::get_courses_by_level_id( $level->id );
		
		if ( is_array( $courses ) && is_object( $user ) ) {
			foreach ( $courses as $course_id ) {
				ld_update_course_access( $user->ID, $course_id, true );
			}
		}
	}

	/**
	 * Remove course access by given order
	 * 
	 * @param  object $order Order object
	 */
	public static function remove_course_access_by_order( $order ) {
		$level    = $order->getMembershipLevel();
		$user     = $order->getUser();
		$courses  = self::get_courses_by_level_id( $level->id );
		
		if ( is_array( $courses ) && is_object( $user ) ) {
			foreach ( $courses as $course_id ) {
				ld_update_course_access( $user->ID, $course_id, true );
			}
		}
	}

	/**
	 * Give course access by given order
	 *
	 * @param object $order Order object
	 */
	public static function give_course_access_by_order( $order ) {
		$level = $order->getMembershipLevel();
		$user  = $order->getUser();

		$courses = self::get_courses_by_level_id( $level->id );
		if ( is_array( $courses ) && is_object( $user ) ) {
			foreach ( $courses as $course_id ) { 
				ld_update_course_access( $user->ID, $course_id, false );
			}
		}
	}

	/**
	 * Give user course access if he already has access to a particular course even though he's not a member of the course's membership
	 *
	 * @param bool  $hasaccess Whether user has access or not
	 * @param int   $mypost Course WP_Post
	 * @param int   $myuser WP_User
	 * @param array $mypost List of membership levels that protect this course
	 * @return boolean Returned $hasaccess
	 */
	public static function has_course_access( $hasaccess, $mypost, $myuser, $post_membership_levels ) {
		if ( 'sfwd-courses' == $mypost->post_type ) {
			if ( sfwd_lms_has_access( $mypost->ID, $myuser->ID ) ) {
				$hasaccess = true;
			}
		}

		return $hasaccess;
	}

	/**
	 * Get courses that belong to a certain level ID
	 * 
	 * @param  int    $level_id ID of a level
	 * @return array            Array of courses
	 */
	public static function get_courses_by_level_id( $level_id ) {
		$courses_levels = get_option( '_level_course_option' );

		$courses = array();
		foreach ( $courses_levels as $course_id => $levels ) {
			$levels = explode( ',', $levels );
			if ( in_array( $level_id, $levels ) ) {
				$courses[] = $course_id;
			}
		}

		return $courses;
	}

	/**
	 * Add new course page IDs to pmpro_membership_pages table
	 * 
	 * @param  int    $membership_id 	ID of PMP membership level
	 * @param  int    $course_id        ID of a Learndash course
	 * @since  1.0.7
	 */
	public static function insert_course( $membership_id, $course_id )
	{
		global $wpdb;

		$wpdb->insert(
			"{$wpdb->pmpro_memberships_pages}",
			array( 
				'membership_id' => $membership_id,
				'page_id' => $course_id,
			),
			array( '%d', '%d' )
		);
	}

	/**
	 * Delete course page ID from pmpro_membership_pages table
	 * 
	 * @param  int  $course_id ID of a LearnDash course
	 * @since 1.0.7
	 */
	public static function delete_course_by_course_id( $course_id )
	{
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->pmpro_memberships_pages}",
			array( 'page_id' => $course_id ),
			array( '%d' )
		);
	}

	/**
	 * Delete course page ID from pmpro_membership_pages table
	 * 
	 * @param  int  $course_id ID of a LearnDash course
	 * @since 1.0.7
	 */
	public static function delete_course_by_membership_id_course_id( $membership_id, $course_id )
	{
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->pmpro_memberships_pages}",
			array( 'membership_id' => $membership_id, 'page_id' => $course_id ),
			array( '%d', '%d' )
		);
	}

	/**
	 * Update plugin data when the plugin is updated
	 */
	public static function update_plugin_data()
	{
		$plugin_version = '1.1.0';
		$saved_version  = get_option( 'ld_pmpro_version' );

		if ( false === $saved_version || version_compare( $saved_version, $plugin_version, '<' ) ) {

			$lvl_courses = get_option( '_level_course_option' );

			if ( is_array( $lvl_courses ) ) {
				foreach ( $lvl_courses as $course_id => $level_string ) {
					self::delete_course_by_course_id( $course_id );

					if ( empty( trim( $level_string ) ) ) {
						continue;
					}

					$levels = explode( ',', $level_string );

					foreach ( $levels as $lvl ) {
						self::insert_course( $lvl, $course_id );
					}
				}
			}

			update_option( 'ld_pmpro_version', $plugin_version );
		}
	}
} // end class

Learndash_Paidmemberships::define_constants();

// add_action( 'admin_enqueue_scripts', array( 'Learndash_Paidmemberships', 'addResources' ) );
add_action( 'plugins_loaded', array( 'Learndash_Paidmemberships', 'i18nize' ) );
add_action( 'admin_init', array( 'Learndash_Paidmemberships', 'admin_init' ) );
add_action( 'save_post', array( 'Learndash_Paidmemberships', 'save_details' ), 1000 );
add_action( 'init', array( 'Learndash_Paidmemberships', 'update_plugin_data' ) );

// Integration hooks
add_action( 'pmpro_membership_level_after_other_settings', array( 'Learndash_Paidmemberships', 'level_courses_list' ) );
add_action( 'pmpro_save_membership_level', array( 'Learndash_Paidmemberships', 'save_level_details' ) );

// Update course access when user change membership level
add_action( 'pmpro_after_change_membership_level', array( 'Learndash_Paidmemberships', 'user_change_level' ), 10, 3 );
// Update course access on member approval update
add_action( 'update_user_meta', array( 'Learndash_Paidmemberships', 'update_access_on_approval' ), 10, 4 );
// Update course access when an order is updated
add_action( 'pmpro_updated_order', array( 'Learndash_Paidmemberships', 'udpate_course_access_on_order_update' ), 10, 1 );
// Update course access when an order is deleted
add_action( 'pmpro_delete_order', array( 'Learndash_Paidmemberships', 'remove_course_access_on_order_deletion' ), 10, 2 );
// Update course access when an subscription is cancelled, failed, or payment refunded
add_action( 'pmpro_subscription_expired', array( 'Learndash_Paidmemberships', 'remove_course_access_by_order' ), 10, 1 );
add_action( 'pmpro_subscription_cancelled', array( 'Learndash_Paidmemberships', 'remove_course_access_by_order' ), 10, 1 );
add_action( 'pmpro_subscription_recuring_stopped', array( 'Learndash_Paidmemberships', 'remove_course_access_by_order' ), 10, 1 );

// Regain access to course when subscription recurring is restarted
add_action( 'pmpro_subscription_recuring_restarted', array( 'Learndash_Paidmemberships', 'give_course_access_by_order' ), 10, 1 );

// Remove membership access message if user already has access to a particular course
add_filter( 'pmpro_has_membership_access_filter', array( 'Learndash_Paidmemberships', 'has_course_access' ), 99, 4 ); // priority 99 to make sure the value is returned

} // end if class_exists