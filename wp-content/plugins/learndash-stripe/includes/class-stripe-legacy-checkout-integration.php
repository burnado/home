<?php
/**
* Stripe legacy checkout integration class
*/
class LearnDash_Stripe_Legacy_Checkout_Integration {
	
	/**
	 * Plugin options
	 * @var array
	 */
	private $options;

	/**
	 * Stripe secret key
	 * @var string
	 */
	private $secret_key;

	/**
	 * Stripe publishable key
	 * @var string
	 */
	private $publishable_key;

	/**
	 * Plugin default payment button
	 * @var string
	 */
	private $default_button;

	/**
	 * Variable to hold the Stripe Button HTML. This variable can be checked from other methods.
	 */
	private $stripe_button;

	/**
	 * Variable to hold the Course object we are working with.
	 */
	private $course;

	
	private $stripe_script_loaded_once = false;


	/**
	 * Class construction function
	 */
	public function __construct() {
		$this->options         			= 	get_option( 'learndash_stripe_settings', array() );
	
		$this->secret_key      			= 	$this->get_secret_key();
		$this->publishable_key 			= 	$this->get_publishable_key();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'learndash_payment_button', array( $this, 'payment_button' ), 10, 2 );

		add_action( 'init', array( $this, 'process_checkout' ) );
		add_action( 'get_footer', array( $this, 'get_footer' ) );
		add_action( 'wp_footer', array( $this, 'alert_error' ) );
	}

	/**
	 * Load necessary scripts and stylesheets
	 */
	public function enqueue_scripts() {

		//if ( is_singular( 'sfwd-courses' ) ) {
			//if ( $this->key_is_empty() || $this->course_is_free() || $this->course_price_is_less_than( 0.50 ) ) {
			if ( $this->key_is_empty() ) {
				return;
			}

			wp_enqueue_style( 'ld-stripe-style', LEARNDASH_STRIPE_PLUGIN_URL . 'assets/css/learndash-stripe-style.css', array(), LEARNDASH_STRIPE_VERSION );

			// If the user is logged in. Check if they already have access to the Course. So we don't load the Stripe JS.
			//$queried_object = get_queried_object();
			//if (sfwd_lms_has_access( $queried_object->ID)) {
			//	return;
			//}

			wp_enqueue_script( 'learndash_stripe_checkout_handler', LEARNDASH_STRIPE_PLUGIN_URL . 'assets/js/learndash-stripe-checkout-handler.js', array( 'jquery' ), LEARNDASH_STRIPE_VERSION, true );

			$stripe_args = array(
				'name'            => get_bloginfo( 'name', 'raw' ),
				'publishable_key' => $this->publishable_key,
			);

			wp_localize_script( 'learndash_stripe_checkout_handler', 'LD_Stripe_Handler', $stripe_args );
		//}
	}


	function get_footer() {
		if ( is_admin() ) return;
		
		if ( empty( $this->stripe_button ) ) {
			wp_dequeue_script('learndash_stripe_checkout_handler');
		}
	}
	/**
	 * Output modified payment button
	 * @param  string $default_button Learndash default payment button
	 * @param  array  $params         Button parameters
	 * @return string                 Modified button
	 */
	public function payment_button( $default_button, $params = null ) {
		if ( $this->key_is_empty() ) {
			return $default_button;
		}

		// We only hook into valid course price types 'paynow' or 'subscribe'. 
		// For now with LD < 2.3 the course_price_type is not sent as part of params. So we can't check it. 
		//if ( ( !isset( $params['course_price_type'] ) ) || ( $params['course_price_type'] != "paynow" ) || ( $params['course_price_type'] != "subscribe" ) ) {
		//	return $default_button;
		//}

		// Also ensure the price it not zero
		if ( ( !isset( $params['price'] ) ) || ( empty( $params['price'] ) ) ) {
			return $default_button;
		}
		
		$this->default_button = $default_button;

		if (isset($params['post'])) {
			$this->course = $params['post'];
		}

		$this->stripe_button = $this->stripe_button();
		
		if (!empty($this->stripe_button))
			return $default_button . $this->stripe_button();
		else 
			return $default_button;
	}

	/**
	 * Stripe config function
	 */
	public function config() {
		
		require_once LEARNDASH_STRIPE_PLUGIN_PATH . 'vendor/autoload.php';

		\Stripe\Stripe::setApiKey( $this->secret_key );
	}


	/**
	 * Process stripe checkout
	 */
	public function process_checkout() {
		$transaction_status = array();
		$transaction_status['stripe_message_type'] 	= '';
		$transaction_status['stripe_message'] 		= '';

		//error_log('in '. __FUNCTION__ ."\r\n", 3, ABSPATH .'/ld_debug.log');
		//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>' ."\r\n", 3, ABSPATH .'/ld_debug.log');

		if ( isset( $_POST['action'] ) && $_POST['action'] == 'stripe' ) {

			$this->config();

			if ( ( isset( $_POST['stripe_token_id'] ) ) && ( !empty( $_POST['stripe_token_id'] ) ) ) { 
				$token_id = esc_attr( $_POST['stripe_token_id'] );
			} else {
				$token_id = 0;
			}
			$transaction_status['token_id'] = $token_id;
			

			if (isset($_POST['stripe_token_email'])) {
				$token_email = esc_attr( $_POST['stripe_token_email'] );
			} else {
				$token_email = '';
			}
			$transaction_status['token_email'] = $token_email;
			
			if ( ( isset( $_POST['stripe_course_id'] ) ) && (!empty( $_POST['stripe_course_id'] ) ) ) {
				$course_id   = esc_attr( $_POST['stripe_course_id'] );
			} else {
				$course_id = 0;
			}
			$transaction_status['course_id'] = $course_id;

			if ( ! $this->is_transaction_legit( $_POST ) ) {
				$transaction_status['stripe_message_type'] = 'error';
				$transaction_status['stripe_message'] = __( 'The course form data doesn\'t match with the official course data. Cheatin\' huh?', 'learndash-stripe' );
				$this->show_notification( $transaction_status );
			}

			if ( is_user_logged_in() ) {
				$user_id     = get_current_user_id();
				$customer_id = get_user_meta( $user_id, 'stripe_customer_id', true );
				$customer_id = $this->add_stripe_customer( $user_id, $customer_id, $token_email, $token_id );

			} else {
				// Needed a flag so we know at the end of this was a new user vs existing user so we can return the correct message. 
				// The problem was at the end if this is an existing user there is no email. So the message was incorrect. 
				$is_new_user = false;

				$user = get_user_by( 'email', $token_email );

				if ( false === $user ) {

					$password = wp_generate_password( 18, true, false );
					$new_user = $this->create_user( $token_email, $password );

					if ( ! is_wp_error( $new_user ) ) {
						$user_id     = $new_user;
						$user        = get_user_by( 'ID', $user_id );
						$customer_id = get_user_meta( $user_id, 'stripe_customer_id', true );
						$customer_id = $this->add_stripe_customer( $user_id, $customer_id, $token_email, $token_id );

						// Need to allow for older versions of WP. 
						global $wp_version;
						if (version_compare($wp_version, '4.3.0', '<')) {
						    wp_new_user_notification( $user_id, $password );
						} else if (version_compare($wp_version, '4.3.0', '==')) {
						    wp_new_user_notification( $user_id, 'both' );						
						} else if (version_compare($wp_version, '4.3.1', '>=')) {
						    wp_new_user_notification( $user_id, null, 'both' );
						}
						$is_new_user = true;				
						
					} else {
						$error_code = $new_user->get_error_code();
						$transaction_status['stripe_message_type'] = 'error';
						$transaction_status['stripe_message'] = __( 'Failed to create a new user account. Please try again. Reason: ', 'learndash-stripe' ) . $new_user->get_error_message( $error_code );
						$this->show_notification( $transaction_status );
					}

				} else {
					$user_id = $user->ID;
					$customer_id = get_user_meta( $user_id, 'stripe_customer_id', true );
					$customer_id = $this->add_stripe_customer( $user_id, $customer_id, $token_email, $token_id );
				}
			}

			$site_name = get_bloginfo( 'name' );
			if ( 'paynow' == $_POST['stripe_price_type'] ) {
				try {
					$charge = \Stripe\Charge::create( array(
						'amount'   => esc_attr( $_POST['stripe_price'] ),
						'currency' => esc_attr( strtolower( $_POST['stripe_currency'] ) ),
						'customer' => $customer_id,
						'description' => sprintf( '%s: %s', $site_name, stripslashes( sanitize_text_field( $_POST['stripe_name'] ) ) ),
						'receipt_email' => $user->user_email,
					) );

					add_user_meta( $user_id, 'stripe_charge_id', $charge->id, false );

				} catch ( \Stripe\Error\Card $e ) {
					// Card is declined
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'];
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\RateLimit $e ) {
					// Too many requests made to the API
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'];
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\InvalidRequest $e ) {
					// Invalid parameters suplied to the API
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please contact website administrator.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\Authetication $e ) {
					// Authentication failed
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please contact website administrator.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\ApiConnection $e ) {
					// Network communication with Stripe failed
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\Base $e ) {
					// Generic error
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( Exception $e ) {
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );
				}

			} elseif ( 'subscribe' == $_POST['stripe_price_type'] ) {

				$id = esc_attr( $_POST['stripe_plan_id'] ) . '-' . substr( md5( time() ), 0, 5 );

				switch ( $_POST['stripe_interval'] ) {
					case 'D':
						$interval = 'day';
						break;

					case 'W':
						$interval = 'week';
						break;
					
					case 'M':
						$interval = 'month';
						break;

					case 'Y':
						$interval = 'year';
						break;
				}

				try {
					$plan_ids = get_post_meta( $course_id, 'stripe_plan_id', false );		
					// error_log('plan_id['. $plan_id.']'."\r\n", 3, ABSPATH .'/ld_debug.log');

					if ( empty( $plan_ids ) ) {

						$plan_args = array(
							// Required
							'amount'   => esc_attr( $_POST['stripe_price'] ),
							'currency' => strtolower( $this->options['currency'] ),
							'id'       => $id,
							'interval' => $interval,
							'product'  => array(
								'name'     => stripslashes( sanitize_text_field( $_POST['stripe_name'] ) ),
							),
							// Optional
							'interval_count' => esc_attr( $_POST['stripe_interval_count'] ),
						);
						//error_log('plan_args<pre>'. print_r($plan_args, true) .'</pre>'."\r\n", 3, ABSPATH .'/ld_debug.log');
						
						$plan = \Stripe\Plan::create( $plan_args );
						//error_log('in create plan<pre>'. print_r($plan, true) .'</pre>'."\r\n", 3, ABSPATH .'/ld_debug.log');

						add_post_meta( $course_id, 'stripe_plan_id', $id, false );

						$current_id = $id;

					} else {
						try {
							$last_id = end( $plan_ids );
							reset( $plan_ids );

							$plan = \Stripe\Plan::retrieve( array( 
								'id'     => $last_id, 
								'expand' => array( 'product' ),
							) );
							// error_log('plan<pre>'. print_r($plan, true) .'</pre>'."\r\n", 3, ABSPATH .'/ld_debug.log');

							if ( 
								$plan->amount         != $_POST['stripe_price'] ||
								$plan->currency       != strtolower( $this->options['currency'] ) ||
								$plan->id       	  != $last_id ||
								$plan->interval       != $interval ||
								htmlspecialchars_decode( $plan->product->name )           != stripslashes( sanitize_text_field( $_POST['stripe_name'] ) ) ||
								$plan->interval_count != $_POST['stripe_interval_count']
							) {
								// Delete the old plan first
								// Don't delete the old plan as old subscription may 
								// still attached to it
								// $plan->delete();

								// Create a new plan
								$plan = \Stripe\Plan::create( array(
									// Required
									'amount'   => esc_attr( $_POST['stripe_price'] ),
									'currency' => strtolower( $this->options['currency'] ),
									'id'       => $id,
									'interval' => $interval,
									'product'  => array(
										'name'     => stripslashes( sanitize_text_field( $_POST['stripe_name'] ) ),
									),
									// Optional
									'interval_count' => esc_attr( $_POST['stripe_interval_count'] ),
								) );

								add_post_meta( $course_id, 'stripe_plan_id', $id, false );

								$current_id = $id;
							} else {
								$current_id = $last_id;
							}

						} catch ( Exception $e ) {
							// Create a new plan
							$plan = \Stripe\Plan::create( array(
								// Required
								'amount'   => esc_attr( $_POST['stripe_price'] ),
								'currency' => strtolower( $this->options['currency'] ),
								'id'       => $id,
								'interval' => $interval,
								'product'  => array(
									'name'     => stripslashes( sanitize_text_field( $_POST['stripe_name'] ) ),
								),
								// Optional
								'interval_count' => esc_attr( $_POST['stripe_interval_count'] ),
							) );

							add_post_meta( $course_id, 'stripe_plan_id', $id, false );

							$current_id = $id;
						}
					}

					$subscription = \Stripe\Subscription::create( array(
						'customer' => $customer_id,
						'items' => array(
							array(
								'plan' => $current_id
							)
						)
					) );

					add_user_meta( $user_id, 'stripe_subscription_id', $subscription->id, false );

					add_user_meta( $user_id, 'stripe_plan_id', $current_id, false );

				} catch ( \Stripe\Error\Card $e ) {
					// Card is declined
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'];
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\RateLimit $e ) {
					// Too many requests made to the API
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'];
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\InvalidRequest $e ) {
					// Invalid parameters suplied to the API
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please contact website administrator.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\Authetication $e ) {
					// Authentication failed
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please contact website administrator.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\ApiConnection $e ) {
					// Network communication with Stripe failed
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\Base $e ) {
					// Generic error
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( Exception $e ) {
					$error = __( 'Unknown error.', 'learndash-stripe' );

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );
				}
			}

			// If charge or subscription is successful
			
			// Associate course with user
			$this->associate_course( $course_id, $user_id );

			$transaction = $_POST;

			// Log transaction
			$this->record_transaction( $transaction, $course_id, $user_id, $token_email );

			if ( ! empty( $this->options['return_url'] ) ) {
				wp_redirect( $this->options['return_url'] );
				exit();
			} 

			// Fall through to this if there is not a valid redirect URL. Again I hate using sessions for this. Not time to rewrite all this logic for now. 
			if ($is_new_user == true) {
				$transaction_status['stripe_message'] = __( 'The transaction was successful. Please check your email and log in to access the course.', 'learndash-stripe' );
			} else {
				if ( is_user_logged_in() ) {
					$transaction_status['stripe_message'] = __( 'The transaction was successful. You now have access the course.', 'learndash-stripe' );
				} else {
					$transaction_status['stripe_message'] = __( 'The transaction was successful. Please log in to access the course.', 'learndash-stripe' );
				}
			}
			$this->show_notification( $transaction_status );
		}
	}

	/**
	 * Create user if not exists
	 * 
	 * @param  string $username 
	 * @param  string $password 
	 * @return int               Newly created user ID
	 */
	public function create_user( $email, $password, $username = '' ) {
		if ( empty( $username ) ) {
			$username = preg_replace( '/(.*)\@(.*)/', '$1', $email );
		}
		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			if ( $user_id->get_error_code() == 'existing_user_login' ) {
				$random_chars = str_shuffle( substr( md5( time() ), 0, 5 ) );
				$username = $username . '-' . $random_chars;
				$user_id = $this->create_user( $email, $password, $username );
			}
		}

		do_action( 'learndash_stripe_after_create_user', $user_id );

		return $user_id;
	}

	function show_notification( $transaction_status = array()) {
		//$unique_id = sha1(json_encode( $transaction_status ));
		
		//$transient_id = 'ld';
		//if (isset($transaction_status['course_id'])) {
		//	$transient_id .= '_'. $transaction_status['course_id'];
		//}
		$unique_id = wp_generate_password( 10, false, false );
		$transient_id = 'ld_'. $unique_id;

		set_transient( $transient_id, $transaction_status, HOUR_IN_SECONDS);
		
		$redirect_url = add_query_arg('ld-trans-id', $unique_id);
		wp_redirect( $redirect_url );
		exit();
	}

	/**
	 * Associate course with user
	 * @param  int $course_id Post ID of a course
	 * @param  int $user_id   ID of a user
	 */
	public function associate_course( $course_id, $user_id ) {
		ld_update_course_access( $user_id, $course_id );
	}

	/**
	 * Record transaction in database
	 * @param  array  $transaction  Transaction data passed through $_POST
	 * @param  int    $course_id    Post ID of a course
	 * @param  int    $user_id      ID of a user
	 * @param  string $user_email   Email of the user
	 */
	public function record_transaction( $transaction, $course_id, $user_id, $user_email ) {
		// ld_debug( 'Starting Transaction Creation.' );

		$transaction['user_id']   = $user_id;
		$transaction['course_id'] = $course_id;

		$course_title = $_POST['stripe_name'];

		// ld_debug( 'Course Title: ' . $course_title );

		$post_id = wp_insert_post( array( 'post_title' => "Course {$course_title} Purchased By {$user_email}", 'post_type' => 'sfwd-transactions', 'post_status' => 'publish', 'post_author' => $user_id ) );

		// ld_debug( 'Created Transaction. Post Id: ' . $post_id );

		foreach ( $transaction as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}

	/**
	 * Set secret key used in this class
	 */
	public function set_secret_key() {
		if ( isset( $this->options['test_mode'] ) && 1 == $this->options['test_mode'] && ! empty( $this->options['secret_key_test'] ) ) {
			$key = $this->options['secret_key_test'];

		} elseif ( ( ! isset( $this->options['test_mode'] ) || 1 != $this->options['test_mode'] ) && ! empty( $this->options['secret_key_live']) ) {
			$key = $this->options['secret_key_live'];

		} else {
			return $key = '';
		}		

		return $key;
	}

	/**
	 * Return secret key used in this class
	 */
	public function get_secret_key() {
		return $this->set_secret_key();
	}

	/**
	 * Set publishable key used in this class
	 */
	public function set_publishable_key() {
		if ( isset( $this->options['test_mode'] ) && 1 == $this->options['test_mode'] && ! empty( $this->options['publishable_key_test'] ) ) {

			$key = $this->options['publishable_key_test'];

		} elseif ( ( ! isset( $this->options['test_mode'] ) || 1 != $this->options['test_mode'] ) && ! empty( $this->options['publishable_key_live'] ) ) {

			$key = $this->options['publishable_key_live'];

		} else {
			return $key = '';
		}		

		return $key;
	}

	/**
	 * Return publishable key used in this class
	 */
	public function get_publishable_key() {
		return $this->set_publishable_key();
	}

	/**
	 * Check if Stripe transaction is legit
	 * @param  array  $post 	Transaction form submit $_POST
	 * @return boolean       	True if legit, false otherwise
	 */
	public function is_transaction_legit( $post ) {
		if ( wp_verify_nonce( $post['stripe_nonce'], 'stripe-nonce-' . $post['stripe_course_id'] . $post['stripe_price'] . $post['stripe_price_type'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if key is empty
	 * @return bool True if empty, false otherwise
	 */
	public function key_is_empty() {
		if ( empty( $this->secret_key ) && empty( $this->publishable_key ) ) {
			return true;
		}
	}

	/**
	 * Check if course is free
	 * @return bool True if free, false otherwise
	 */
	/*
	public function course_is_free() {
		$id      = get_the_ID();
		$course  = get_post( $id );

		$meta                = get_post_meta( $id, '_sfwd-courses', true );
		$course_price_type   = @$meta['sfwd-courses_course_price_type'];
		$course_price        = @$meta['sfwd-courses_course_price'];

		if ( 'free' == $course_price_type ) {
			return true;
		}
	}
	*/
	
	/**
	 * Check if course price is less than certain price
	 * @return bool True if less, false otherwise
	 */
	/*
	public function course_price_is_less_than( $price = 0 ) {
		$id      = get_the_ID();
		$course  = get_post( $id );

		$meta                = get_post_meta( $id, '_sfwd-courses', true );
		$course_price        = @$meta['sfwd-courses_course_price'];

		if ( $price > $course_price ) {
			return true;
		}
	}
	*/
	
	/**
	 * Check if PayPal is used or not.
	 * @return boolean True if active, false otherwise.
	 */
	public function is_paypal_active() {
		if ( version_compare( LEARNDASH_VERSION, '2.4.0', '<' ) ) {
			$ld_options   = learndash_get_option( 'sfwd-courses' );
			$paypal_email = isset( $ld_options['paypal_email'] ) ? $ld_options['paypal_email'] : '';
		} else {
			$paypal_email = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_PayPal', 'paypal_email' );
		}

		if ( ! empty( $paypal_email ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Stripe payment button
	 * @return string Payment button
	 */
	public function stripe_button() {

		//$id      = get_the_ID();
		//$course  = get_post( $id );
		
		if (empty($this->course)) return;
		//$id = $this->course_id;
		
		$user_id = get_current_user_id();
		$user_email = '';

		if ( 0 != $user_id ) {
			$user = get_userdata( $user_id );
			$user_email = ( '' != $user->user_email ) ? $user->user_email : '';
		}

		$meta                = get_post_meta( $this->course->ID, '_sfwd-courses', true );
		$course_price        = @$meta['sfwd-courses_course_price'];
		$course_price_type   = @$meta['sfwd-courses_course_price_type'];
		$course_image        = get_the_post_thumbnail_url( $this->course->ID, 'medium' );
		$custom_button_url   = @$meta['sfwd-courses_custom_button_url'];
		$currency            = strtolower( $this->options['currency'] );

		$course_interval_count = get_post_meta( $this->course->ID, 'course_price_billing_p3', true );
		$course_interval       = get_post_meta( $this->course->ID, 'course_price_billing_t3', true );

		$course_name      = $this->course->post_title;
		$course_id        = $this->course->ID;
		$course_plan_id   = 'learndash-course-' . $this->course->ID;

		$course_price = preg_replace( '/.*?(\d+(?:\.?\d+))/', '$1', $course_price );

		if ( ! $this->is_zero_decimal_currency( $this->options['currency'] ) ) {
			$course_price = $course_price * 100;
		}
		
		if ( $this->is_paypal_active() ) {
			$stripe_button_text  = apply_filters( 'learndash_stripe_purchase_button_text', __( 'Use a Credit Card', 'learndash-stripe' ) );		
		} else {
			if (class_exists('LearnDash_Custom_Label')) {
				$stripe_button_text  = apply_filters( 'learndash_stripe_purchase_button_text', LearnDash_Custom_Label::get_label( 'button_take_this_course' ) );		
			} else {
				$stripe_button_text  = apply_filters( 'learndash_stripe_purchase_button_text', __( 'Take This Course', 'learndash-stripe' ) );
			}
		}

		$stripe_button = '';
		$stripe_button .= '<script src="https://checkout.stripe.com/checkout.js"></script>';

		$stripe_button .= '<div class="learndash_checkout_button learndash_stripe_button">';
		$stripe_button .= '<form id="learndash-stripe-checkout-'. $course_id .'" class="learndash-stripe-checkout" name="" action="" method="post">';
		$stripe_button .= '<input type="hidden" name="action" value="stripe" />';
		$stripe_button .= '<input type="hidden" name="stripe_email" value="' . $user_email . '" />';
		$stripe_button .= '<input type="hidden" name="stripe_course_id" value="' . $course_id . '" />';
		$stripe_button .= '<input type="hidden" name="stripe_plan_id" value="' . $course_plan_id . '" />';
		$stripe_button .= '<input type="hidden" name="stripe_name" value="' . $course_name . '" />';
		$stripe_button .= '<input type="hidden" name="stripe_currency" value="' . $currency . '" />';
		$stripe_button .= '<input type="hidden" name="stripe_price" value="' . $course_price . '" />';
		$stripe_button .= '<input type="hidden" name="stripe_price_type" value="' . $course_price_type . '" />';
		
		if ( 'subscribe' == $course_price_type ) {

			$stripe_button .= '<input type="hidden" name="stripe_interval_count" value="' . $course_interval_count . '" />';
			$stripe_button .= '<input type="hidden" name="stripe_interval" value="' . $course_interval . '" />';
		}

		$stripe_button_nonce = wp_create_nonce( 'stripe-nonce-' . $course_id . $course_price . $course_price_type );
		$stripe_button .= '<input type="hidden" name="stripe_nonce" value="' . $stripe_button_nonce . '" />';

		//$stripe_button .= '<button id="learndash-stripe-checkout" class="btn-join button">'. $stripe_button_text . '</button>';
		$stripe_button .= '<input id="learndash-stripe-checkout-button-'. $course_id .'" class="learndash-stripe-checkout-button btn-join button" type="button" value="'. $stripe_button_text .'">';
		$stripe_button .= '</form>';
		$stripe_button .= '</div>';

		return $stripe_button;
	}

	/**
	 * Check if Stripe currency ISO code is zero decimal currency
	 * 
	 * @param  string  $currency Stripe currency ISO code
	 * @return boolean           True if zero decimal|false otherwise
	 */
	public function is_zero_decimal_currency( $currency = '' ) {
		$currency = strtoupper( $currency );

		$zero_decimal_currencies = array(
			'BIF',
			'CLP',
			'DJF',
			'GNF',
			'JPY',
			'KMF',
			'KRW',
			'MGA',
			'PYG',
			'RWF',
			'VND',
			'VUV',
			'XAF',
			'XOF',
			'XPF',
		);

		if ( in_array( $currency, $zero_decimal_currencies ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add Customer to Stripe
	 * @param int    $user_id     ID of a user
	 * @param int    $customer_id Stripe customer ID
	 * @param string $token_email Email of a user, got from token
	 * @param string $token_id    Token ID
	 * @return string Stripe customer ID
	 */
	public function add_stripe_customer( $user_id, $customer_id, $token_email, $token_id ) {
		$this->config();

		try {
			$customer = \Stripe\Customer::retrieve( $customer_id );

			if ( isset( $customer->deleted ) && $customer->deleted ) {
				$customer = \Stripe\Customer::create( array(
					'email'  => $token_email,
					'source' => $token_id,
				) );
			}

			$customer_id = $customer->id;

			update_user_meta( $user_id, 'stripe_customer_id', $customer_id );
			
		} catch ( Exception $e ) {
			$customer = \Stripe\Customer::create( array(
				'email'  => $token_email,
				'source' => $token_id,
			) );

			$customer_id = $customer->id;

			update_user_meta( $user_id, 'stripe_customer_id', $customer_id );
		}

		return $customer_id;
	}

	/**
	 * Output Stripe error alert
	 */
	public function alert_error() {
		//if ( !is_singular( 'sfwd-courses' ) ) return;
		
		if ( ( isset( $_GET['ld-trans-id'] ) ) && ( !empty( $_GET['ld-trans-id'] ) ) ) {
			
			//$queried_object = get_queried_object();
			//error_log('queried_object<pre>'. print_r($queried_object, true) .'</pre>' ."\r\n", 3, ABSPATH .'/ld_debug.log');
			
			//$transient_id = 'ld_'. $queried_object->ID .'_'. $_GET['ld-trans-id'];
			$transient_id = 'ld_'. $_GET['ld-trans-id'];
			//error_log('transient_id['. $transient_id .']' ."\r\n", 3, ABSPATH .'/ld_debug.log');

			$transaction_status = get_transient( $transient_id );
			//error_log('transaction_status<pre>'. print_r($transaction_status, true) .'</pre>' ."\r\n", 3, ABSPATH .'/ld_debug.log');
			
			delete_transient( $transient_id );
			if (!empty( $transaction_status ) ) {

				if ( ( isset( $transaction_status['stripe_message'] ) ) && ( !empty( $transaction_status['stripe_message'] ) ) && ( isset( $transaction_status['stripe_message_type'] ) ) ) {
			
					if ( $transaction_status['stripe_message_type'] == 'error' ) {
						?>
						<script type="text/javascript">
						jQuery( document ).ready( function() { 
							if (jQuery('.learndash_checkout_buttons').length) {
								jQuery( '<p class="learndash-error"><?php echo htmlentities($transaction_status['stripe_message'], ENT_QUOTES) ?></p>' ).insertAfter( '.learndash_checkout_buttons' );
							} else if (jQuery('#learndash_course_content').length) {
								jQuery( '<p class="learndash-error"><?php echo htmlentities($transaction_status['stripe_message'], ENT_QUOTES) ?></p>' ).insertBefore( '#learndash_course_content' );
							}
						});
						</script>
						<?php
					} else if ( $transaction_status['stripe_message_type'] != 'error' ) {
						?>
						<script type="text/javascript">
						jQuery( document ).ready( function() { 
							if (jQuery('#learndash_course_content').length) {
								jQuery( '<p class="learndash-success"><?php echo htmlentities($transaction_status['stripe_message'], ENT_QUOTES) ?></p>' ).insertBefore( '#learndash_course_content' );
							}
						});
						</script>
						<?php
					}
				}
			}
		}
	}
}

new LearnDash_Stripe_Legacy_Checkout_Integration();