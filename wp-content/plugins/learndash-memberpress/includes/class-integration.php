<?php
/**
* Integration class
*/
class Learndash_Memberpress_Integration
{
	
	public function __construct()
	{
		add_action( 'mepr-product-options-tabs', array( $this, 'learndash_tab' ) );
		add_action( 'mepr-product-options-pages', array( $this, 'learndash_tab_page' ) );
		add_action( 'mepr-membership-save-meta', array( $this, 'save_post_meta' ) );

		// Associate or disasociate course when MP transaction status is changed
		add_action( 'mepr-txn-transition-status', array( $this, 'transaction_transition_status' ), 10, 3 );
		// Disassociate course when MP transaction is expired
		add_action( 'mepr-transaction-expired', array( $this, 'transaction_expired' ), 10, 2 );
		// Disassociate course when MP transaction is deleted
		add_action( 'mepr_pre_delete_transaction', array( $this, 'delete_transaction' ), 10, 1 );

		// Associate or disasociate course when MP subscription status is changed
		add_action( 'mepr_subscription_transition_status', array( $this, 'subscription_transition_status' ), 10, 3 );
		// Disassociate course when MP subscription is deleted
		add_action( 'mepr_subscription_pre_delete', array( $this, 'delete_subscription' ), 10, 1 );

		// Corporate account hooks
		// Remove access on corporate account removal
		add_action( 'delete_user_meta', array( $this, 'remove_corporate_account_access' ), 10, 4 );

		// Remove course increment record if a course unenrolled manually
		add_action( 'learndash_update_course_access', array( $this, 'remove_access_increment_count' ), 10, 4 );
	}

	/**
	 * Output new tab for LearnDash on MemberPress membership edit screen
	 * 
	 * @param  array  $product MemberPress product information
	 */
	public function learndash_tab( $product )
	{
		?>

		<a class="nav-tab main-nav-tab" href="#" id="learndash"><?php _e( 'LearnDash', 'learndash-memberpress' ); ?></a>

		<?php
	}

	/**
	 * Output tab content for LearnDash tab on MemberPress membership edit screen
	 * 
	 * @param  array  $product MemberPress product information
	 */
	public function learndash_tab_page( $product )
	{
		$courses = $this->get_learndash_courses();
		$saved_courses = maybe_unserialize( get_post_meta( $product->rec->ID, '_learndash_memberpress_courses', true ) );
		?>
		
		<div class="product_options_page learndash">
			<div class="product-options-panel">
				<div class="ld-memberpress-options">
					<p><strong><?php _e( 'Courses', 'learndash-memberpress' ); ?></strong></p>
					<div class="ld-memberpress-course-options">
					<?php foreach ( $courses as $course ): ?>
						<label for="<?php echo esc_attr( $course->ID ); ?>">
							<input type="checkbox" name="_learndash_memberpress_courses[]" id="<?php echo esc_attr( $course->ID ); ?>" value="<?php echo esc_attr( $course->ID ); ?>" <?php $this->checked_course( $course->ID, $saved_courses ); ?>>
							<?php echo esc_attr( $course->post_title ); ?>
						</label><br>
					<?php endforeach ?>
					</div>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Save LearnDash post meta for MemberPress membership post object
	 * 
	 * @param  array  $product MemberPress product information
	 */
	public function save_post_meta( $product )
	{
		$old_courses = get_post_meta( $product->rec->ID, '_learndash_memberpress_courses', true );
		$new_courses = array_map( 'sanitize_text_field', $_POST['_learndash_memberpress_courses'] );

		// Update associated course in DB so that it will be executed in cron
		$course_update_queue = get_option( 'learndash_memberpress_course_access_update', array() );

		$course_update_queue[ $product->rec->ID ] = array(
			// 'membership_id' => $product->rec->ID,
			'old_courses'   => $old_courses,
			'new_courses'   => $new_courses
		);

		update_option( 'learndash_memberpress_course_access_update', $course_update_queue );

		update_post_meta( $product->rec->ID, '_learndash_memberpress_courses', $new_courses );
	}

	/**
	 * Cron job: update user course access
	 */
	public static function cron_update_course_access()
	{
		// Get course update queue
		$updates = get_option( 'learndash_memberpress_course_access_update', array() );

		foreach ( $updates as $membership_id => $update ) {
			// Get transactions from DB with membership_id
			global $wpdb;
			$mepr_db = new MeprDb();

			// Transactions
			$query   = "SELECT user_id, trans_num, subscription_id FROM {$mepr_db->transactions} WHERE status = 'complete' AND product_id = {$membership_id}";
			$transactions = $wpdb->get_results( $query, OBJECT );

			$old_courses = $update['old_courses'];
			$new_courses = $update['new_courses'];

			// Remove or give access for each transaction
			foreach ( $transactions as $transaction ) {
				if ( empty( $transaction->subscription_id ) ) {
					foreach ( $old_courses as $course_id ) {
						self::remove_course_access( $course_id, $transaction->user_id, $transaction->trans_num );
					}

					foreach ( $new_courses as $course_id ) {
						self::add_course_access( $course_id, $transaction->user_id, $transaction->trans_num );
					}
				}
			}

			// Subscriptions
			$query   = "SELECT user_id, subscr_id FROM {$mepr_db->subscriptions} WHERE status = 'active' AND product_id = {$membership_id}";
			$subscriptions = $wpdb->get_results( $query, OBJECT );

			// Remove or give access for each subscription
			foreach ( $subscriptions as $subscription ) {
				foreach ( $old_courses as $course_id ) {
					self::remove_course_access( $course_id, $subscription->user_id, $subscription->subscr_id );
				}

				foreach ( $new_courses as $course_id ) {
					self::add_course_access( $course_id, $subscription->user_id, $subscription->subscr_id );
				}
			}

			unset( $updates[ $membership_id ] );
		}

		update_option( 'learndash_memberpress_course_access_update', $updates );
	}

	/**
	 * Change LearnDash course status if MemberPress txn status is changed
	 *
	 * @param  string $old_status 	Old status of a transaction
	 * @param  string $new_status 	New status of a transaction
	 * @param  array  $txn 		  	Transaction data	 
	 */
	public function transaction_transition_status( $old_status, $new_status, $txn )
	{
		if ( $txn->subscription() !== false ) {
			return;
		}

		$ld_courses = maybe_unserialize( get_post_meta( $txn->rec->product_id, '_learndash_memberpress_courses', true ) );

		// If no LearnDash course associated, exit
		if ( empty( $ld_courses ) ) {
			return;
		}

		if ( ( $txn->txn_type == 'sub_account' || $old_status != 'complete' ) && $new_status == 'complete' ) {
			foreach ( $ld_courses as $course_id ) {
				self::add_course_access( $course_id, $txn->rec->user_id, $txn->rec->trans_num );
			}
		} elseif ( $old_status == 'complete' && $new_status != 'complete' ) {
			foreach ( $ld_courses as $course_id ) {
				self::remove_course_access( $course_id, $txn->rec->user_id, $txn->rec->trans_num );
			}
		}
	}

	/**
	 * Fired when a MP transaction is expired
	 * 
	 * @param  object $txn        MP transaction object
	 * @param  string $sub_status Subscription status
	 */
	public function transaction_expired( $txn, $sub_status )
	{
		$ld_courses = maybe_unserialize( get_post_meta( $txn->rec->product_id, '_learndash_memberpress_courses', true ) );

		// If no LearnDash course associated, exit
		if ( empty( $ld_courses ) ) { 
			return; 
		}

		// Make sure user is really expired
		$user = new MeprUser( $txn->user_id );
		$subs = $user->active_product_subscriptions( 'ids' );

		if ( ! empty( $subs ) && in_array( $txn->product_id, $subs ) ) { 
			return; 
		}

		foreach ( $ld_courses as $course_id ) { 
			self::remove_course_access( $course_id, $txn->rec->user_id, $txn->rec->trans_num ); 
		}
	}

	/**
	 * Delete LearnDash course association if transaction is deleted
	 * 
	 * @param  int|bool $query Result of $wpdb->query
	 * @param  array 	$args  Args of transaction
	 */
	public function delete_transaction( $txn )
	{
		if ( $txn->subscription() ) {
			return;
		}

		// Bail if the transaction is not complete
		if ( $txn->rec->status != 'complete' ) {
			return;
		}

		$ld_courses = maybe_unserialize( get_post_meta( $txn->product_id, '_learndash_memberpress_courses', true ) );

		// If no LearnDash course associated, exit
		if ( empty( $ld_courses ) ) {
			return;
		}
		
		foreach ( $ld_courses as $course_id ) {
			self::remove_course_access( $course_id, $txn->user_id, $txn->rec->trans_num );
		}
	}

	/**
	 * Change LearnDash course status if MemberPress subscription status is changed
	 *
	 * @param  string $old_status 	Old status of a transaction
	 * @param  string $new_status 	New status of a transaction
	 * @param  array  $txn 		  	Transaction data	 
	 */
	public function subscription_transition_status( $old_status, $new_status, $subscription )
	{
		$ld_courses = maybe_unserialize( get_post_meta( $subscription->product_id, '_learndash_memberpress_courses', true ) );

		// If no LearnDash course associated, exit
		if ( empty( $ld_courses ) ) {
			return;
		}

		if ( $new_status == 'active' ) {
			$first_txn  = $subscription->first_txn();

			foreach ( $ld_courses as $course_id ) {
				self::add_course_access( $course_id, $subscription->user_id, $subscription->subscr_id );
				// Replace start date to keep the drip feeding working
				update_user_meta( $subscription->user_id, 'course_' . $course_id . '_access_from', strtotime( $first_txn->created_at ) );

				self::maybe_update_course_access_timestamp_to_first_subscription( $subscription, $subscription->user_id, $course_id );
			}
		} elseif ( $new_status != 'active' ) {
			// Exit if subscription is not expired yet
			if ( ! $subscription->is_expired() ) {
				return;
			}

			foreach ( $ld_courses as $course_id ) {
				self::remove_course_access( $course_id, $subscription->user_id, $subscription->subscr_id );
			}
		}
	}

	/**
	 * Delete LearnDash course association if subscription is deleted
	 *
	 * 
	 * @param  int|bool $subscription_id   MP subscription ID
	 */
	public function delete_subscription( $subscription_id )
	{
		$subscription = new MeprSubscription( $subscription_id );

		if ( ! $subscription || $subscription->status != 'active' ) {
			return;
		}

		$ld_courses = maybe_unserialize( get_post_meta( $subscription->product_id, '_learndash_memberpress_courses', true ) );

		// If no LearnDash course associated, exit
		if ( empty( $ld_courses ) ) {
			return;
		}
		
		foreach ( $ld_courses as $course_id ) {
			self::remove_course_access( $course_id, $subscription->user_id, $subscription->subscr_id );
		}
	}

	/**
	 * Remove corporate account access
	 *
	 * Hooked to delete_user_meta since there's no available hook in the plugin.
	 * 
	 * @param  array  $meta_ids  
	 * @param  int    $user_id 
	 * @param  string $meta_key  
	 * @param  string $meta_value
	 */
	public function remove_corporate_account_access( $meta_ids, $user_id, $meta_key, $meta_value ) {
		if ( $meta_key != 'mpca_corporate_account_id' ) {
			return;
		}

		$ca  = new MPCA_Corporate_Account( $meta_value );
		$txn = $ca->get_obj(); 

		$ld_courses = maybe_unserialize( get_post_meta( $txn->rec->product_id, '_learndash_memberpress_courses', true ) );

		// If no LearnDash course associated, exit
		if ( empty( $ld_courses ) ) {
			return;
		}

		foreach ( $ld_courses as $course_id ) {
			self::remove_course_access( $course_id, $user_id, $txn->rec->trans_num );
		}
	}

	/**
	 * Remove course access count if a course unenrolled
	 * 
	 * @param  int    $user_id     
	 * @param  int    $course_id   
	 * @param  array  $access_list 
	 * @param  bool   $remove      
	 */
	public function remove_access_increment_count( $user_id, $course_id, $access_list, $remove ) {
		if ( $remove !== true ) {
			return;
		}

		delete_user_meta( $user_id, '_learndash_memberpress_enrolled_courses_access_counter' );
	}

	/**
	 * Add course access
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 * @param int $order_id  Subscription ID or Transaction ID
	 */
	public static function add_course_access( $course_id, $user_id, $order_id )
	{
		self::increment_course_access_counter( $course_id, $user_id, $order_id );
		if ( ! self::is_user_enrolled_to_course( $user_id, $course_id ) ) {
			ld_update_course_access( $user_id, $course_id );
		}
	}

	/**
	 * Add course access
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 * @param int $order_id  Subscription ID or Transaction ID
	 */
	public static function remove_course_access( $course_id, $user_id, $order_id )
	{
		self::decrement_course_access_counter( $course_id, $user_id, $order_id );
		$courses = self::get_courses_access_counter( $user_id );
		if ( ! isset( $courses[ $course_id ] ) || empty( $courses[ $course_id ] ) ) {
			ld_update_course_access( $user_id, $course_id, $remove = true );
		}
	}

	/**
	 * Check if a user is already enrolled to a course
	 * 
	 * @param  integer $user_id   User ID
	 * @param  integer $course_id Course ID
	 * @return boolean            True if enrolled|false otherwise
	 */
	public static function is_user_enrolled_to_course( $user_id = 0, $course_id = 0 ) {
		$enrolled_courses = learndash_user_get_enrolled_courses( $user_id );

		foreach ( $enrolled_courses as $c_id ) {
			if ( $c_id == $course_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get all LearnDash courses
	 * 
	 * @return object LearnDash course
	 */
	private function get_learndash_courses()
	{
		global $wpdb;
		$query = "SELECT posts.* FROM $wpdb->posts posts WHERE posts.post_type = 'sfwd-courses' AND posts.post_status = 'publish' ORDER BY posts.post_title";

		return $wpdb->get_results( $query, OBJECT );
	}

	/**
	 * Check if a course belong to a courses array
	 * If true, output HTML attribute checked="checked"
	 * 
	 * @param  int    $course_id     Course ID
	 * @param  array  $courses_array Course IDs array
	 */
	private function checked_course( $course_id, $courses_array )
	{
		if ( isset( $courses_array ) && is_array( $courses_array ) && in_array( $course_id, $courses_array ) ) {
			echo 'checked="checked"';
		}
	}

	/**
	 * Add enrolled course record to a user
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 * @param int $order_id  Subscription ID or Transaction ID
	 */
	public static function increment_course_access_counter( $course_id, $user_id, $order_id )
	{
		$courses = self::get_courses_access_counter( $user_id );

		if ( isset( $courses[ $course_id ] ) && ! is_array( $courses[ $course_id ] ) ) {
			$courses[ $course_id ] = array();
		}

		if ( ! isset( $courses[ $course_id ] ) || ! is_array( $courses[ $course_id ] ) || ( is_array( $courses[ $course_id ] ) && array_search( $order_id, $courses[ $course_id ] ) === false ) ) {
			// Add order ID to course access counter
			$courses[ $course_id ][] = $order_id;
		}

		update_user_meta( $user_id, '_learndash_memberpress_enrolled_courses_access_counter', $courses );
	}

	/**
	 * Delete enrolled course record from a user
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 * @param int $order_id  Subscription ID or Transaction ID
	 */
	public static function decrement_course_access_counter( $course_id, $user_id, $order_id )
	{
		$courses = self::get_courses_access_counter( $user_id );

		if ( isset( $courses[ $course_id ] ) && is_array( $courses[ $course_id ] ) ) {
			$keys = array_keys( $courses[ $course_id ], $order_id );
			if ( is_array( $keys ) ) {
				foreach ( $keys as $key ) {
					unset( $courses[ $course_id ][ $key ] );
				}
			}
		} elseif ( isset( $courses[ $course_id ] ) && ! is_array( $courses[ $course_id ] ) ) {
			unset( $courses[ $course_id ] );
		}

		update_user_meta( $user_id, '_learndash_memberpress_enrolled_courses_access_counter', $courses );
	}

	/**
	 * Check if a course user access is empty
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 * @return boolean       True if empty|false otherwise
	 */
	private function is_course_user_access_empty( $course_id, $user_id )
	{
		$courses = self::get_courses_access_counter( $user_id );

		if ( $courses[ $courses_id ] < 1 ) {
			return true;
		}

		return false;
	}

	/**
	 * Get user enrolled course access counter
	 * 
	 * @param  int $user_id ID of a user
	 * @return array        Course access counter array
	 */
	public static function get_courses_access_counter( $user_id )
	{
		$courses = get_user_meta( $user_id, '_learndash_memberpress_enrolled_courses_access_counter', true );

		if ( ! empty( $courses ) ) {
			$courses = maybe_unserialize( $courses );
		} else {
			$courses = array();
		}
		
		return $courses;
	}

	/**
	 * Set 'course_' . $course_id . '_access_from' value to the first subscription
	 * 
	 * @param  object $subscription   MeprSubscription object
	 * @param  int    $user_id   	  User ID
	 * @param  int    $course_id 	  Course ID
	 */
	public static function maybe_update_course_access_timestamp_to_first_subscription( $subscription, $user_id, $course_id ) {
		// default to false
		$update = apply_filters( 'learndash_memberpress_update_course_access_timestamp_to_first_subscription', false, $subscription, $course_id );

		if ( $update ) {
			global $wpdb;

			$include_membership = apply_filters( 'learndash_memberpress_update_course_access_timestamp_to_first_subscription_with_same_membership', true, $subscription, $course_id ) ? "AND `product_id` = %d" : "";

			// Get the first subscription
			$query = $wpdb->prepare( "SELECT `id` from {$wpdb->prefix}mepr_subscriptions WHERE `user_id` = %d {$include_membership} ORDER BY `created_at` ASC LIMIT 1", $user_id, $subscription->product_id );
			$first_subscription = $wpdb->get_row( $query );
			$first_subscription = new MeprSubscription( $first_subscription->id );

			if ( $first_subscription ) {
				$first_txn = $first_subscription->first_txn();
				update_user_meta( $user_id, 'course_' . $course_id . '_access_from', strtotime( $first_txn->created_at ) );
			}
		}

	}
}

new Learndash_Memberpress_Integration();