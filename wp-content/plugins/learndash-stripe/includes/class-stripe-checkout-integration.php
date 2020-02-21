<?php

/**
* Stripe checokout integration class
*/
class LearnDash_Stripe_Checkout_Integration {
    
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
     * Stripe endpoint secret
     * @var string
     */
    private $endpoint_secret;

    /**
     * Plugin default payment button
     * @var string
     */
    private $default_button;

    /**
     * Variable to hold the Stripe Button HTML. This variable can be checked from other methods.
     * @var string
     */
    private $stripe_button;

    /**
     * Variable to hold the Course object we are working with.
     * @var object
     */
    private $course;

    /**
     * Class construction function
     */
    public function __construct() {
        $this->options                  =   get_option( 'learndash_stripe_settings', array() );
    
        $this->secret_key               =   $this->get_secret_key();
        $this->publishable_key          =   $this->get_publishable_key();
        $this->endpoint_secret          =   @$this->options['endpoint_secret'];

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'learndash_payment_button', array( $this, 'payment_button' ), 10, 2 );
        add_action( 'init', array( $this, 'process_checkout' ) );
        add_action( 'wp_footer', array( $this, 'output_transaction_message' ) );
    }

    /**
     * Stripe config function
     */
    public function config() {
        require_once LEARNDASH_STRIPE_PLUGIN_PATH . 'vendor/autoload.php';

        \Stripe\Stripe::setApiKey( $this->secret_key );
    }

    /**
     * Enqueue style and scripts
     * @return void
     */
    public function enqueue_scripts() {
        if ( ! is_singular( 'sfwd-courses' ) ) {
            return;
        }

        wp_enqueue_style( 'ld-stripe-style', LEARNDASH_STRIPE_PLUGIN_URL . 'assets/css/learndash-stripe-style.css', array(), LEARNDASH_STRIPE_VERSION );
    }

    /**
     * Output modified payment button
     * @param  string $default_button Learndash default payment button
     * @param  array  $params         Button parameters
     * @return string                 Modified button
     */
    public function payment_button( $default_button, $params = null ) {
        if ( $this->key_is_empty() || empty( $this->endpoint_secret ) ) {
            return $default_button;
        }

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
     * Stripe payment button
     * @return string Payment button
     */
    public function stripe_button() {
        if ( empty( $this->course ) ) return;
        
        $course_name      = $this->course->post_title;
        $course_id        = $this->course->ID;
        $course_plan_id   = 'learndash-course-' . $this->course->ID;
        
        $user_id = get_current_user_id();
        $user_email = null;

        if ( 0 != $user_id ) {
            $user = get_userdata( $user_id );
            $user_email = ( '' != $user->user_email ) ? $user->user_email : null;
        }

        $meta                = get_post_meta( $this->course->ID, '_sfwd-courses', true );
        $course_price        = @$meta['sfwd-courses_course_price'];

        $course_price = preg_replace( '/.*?(\d+(?:\.?\d+))/', '$1', $course_price );

        if ( ! $this->is_zero_decimal_currency( $this->options['currency'] ) ) {
            $course_price = $course_price * 100;
        }

        $course_price_type   = @$meta['sfwd-courses_course_price_type'];
        $course_image        = get_the_post_thumbnail_url( $this->course->ID, 'medium' );
        $custom_button_url   = @$meta['sfwd-courses_custom_button_url'];
        $currency            = strtolower( $this->options['currency'] );

        $course_interval_count = get_post_meta( $this->course->ID, 'course_price_billing_p3', true );
        $course_interval       = get_post_meta( $this->course->ID, 'course_price_billing_t3', true );

        switch ( $course_interval ) {
            case 'D':
                $course_interval = 'day';
                break;

            case 'W':
                $course_interval = 'week';
                break;

            case 'M':
                $course_interval = 'month';
                break;

            case 'Y':
                $course_interval = 'year';
                break;
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

        $this->config();

        $course_page_url = get_permalink( $this->course->ID );
        $success_url = ! empty( $this->options['return_url'] ) ? $this->options['return_url'] : $course_page_url;
        $success_url = add_query_arg( array( 
            'ld_stripe'  => 'success',
            'session_id' => '{CHECKOUT_SESSION_ID}',
        ), $success_url );
        $course_images = ! empty( $course_image ) ? array( $course_image ) : null;
        $client_reference_id = array(
            'course_id' => $course_id
        );
        $client_reference_id = array_map( function( $key, $value ) {
            return "{$key}={$value}";
        }, array_keys( $client_reference_id ), $client_reference_id );
        $client_reference_id = implode( ';', $client_reference_id );

        $line_items = array( array(
            'name' => $course_name,
            'images' => $course_images,
            'amount' => $course_price,
            'currency' => $currency,
            'quantity' => 1,
        ) );

        $subscription_data = null;
        if ( 'subscribe' === $course_price_type ) {
            if ( empty( $course_interval ) || empty( $course_interval_count ) || empty( $course_price ) ) {
                return;
            }

            $plan_id = get_post_meta( $course_id, 'stripe_plan_id', false );
            $plan_id = end( $plan_id );

            if ( ! empty( $plan_id ) ) {
                try {
                    $plan = \Stripe\Plan::retrieve( array( 
                        'id'     => $plan_id, 
                        'expand' => array( 'product' ),
                    ) );
                    // error_log('plan<pre>'. print_r($plan, true) .'</pre>'."\r\n", 3, ABSPATH .'/ld_debug.log');

                    if ( 
                        ( isset( $plan ) && is_object( $plan ) ) &&
                        $plan->amount         != $course_price ||
                        $plan->currency       != strtolower( $currency ) ||
                        $plan->id             != $plan_id ||
                        $plan->interval       != $course_interval ||
                        htmlspecialchars_decode( $plan->product->name )           != stripslashes( sanitize_text_field( $course_name ) ) ||
                        $plan->interval_count != $course_interval_count
                    ) {
                        // Don't delete the old plan as old subscription may 
                        // still attached to it

                        // Create a new plan
                        $plan = \Stripe\Plan::create( array(
                            // Required
                            'amount'   => esc_attr( $course_price ),
                            'currency' => strtolower( $currency ),
                            'id'       => $course_plan_id . '-' . $this->generate_random_string( 5 ),
                            'interval' => $course_interval,
                            'product'  => array(
                                'name'     => stripslashes( sanitize_text_field( $course_name ) ),
                            ),
                            // Optional
                            'interval_count' => esc_attr( $course_interval_count ),
                        ) );

                        $plan_id = $plan->id;

                        add_post_meta( $course_id, 'stripe_plan_id', $plan_id, false );
                    }
                } catch ( Exception $e ) {
                    // Create a new plan
                    $plan = \Stripe\Plan::create( array(
                        // Required
                        'amount'   => esc_attr( $course_price ),
                        'currency' => strtolower( $currency ),
                        'id'       => $course_plan_id . '_' . $this->generate_random_string( 5 ),
                        'interval' => $course_interval,
                        'product'  => array(
                            'name' => stripslashes( sanitize_text_field( $course_name ) ),
                        ),
                        // Optional
                        'interval_count' => esc_attr( $course_interval_count ),
                    ) );

                    $plan_id = $plan->id;

                    add_post_meta( $course_id, 'stripe_plan_id', $plan_id, false );
                }
            } else {
                // Create a new plan
                $plan = \Stripe\Plan::create( array(
                    // Required
                    'amount'   => esc_attr( $course_price ),
                    'currency' => strtolower( $currency ),
                    'id'       => $course_plan_id,
                    'interval' => $course_interval,
                    'product'  => array(
                        'name' => stripslashes( sanitize_text_field( $course_name ) ),
                    ),
                    // Optional
                    'interval_count' => esc_attr( $course_interval_count ),
                ) );

                $plan_id = $plan->id;

                add_post_meta( $course_id, 'stripe_plan_id', $plan_id, false );
            }

            $subscription_data = array(
                'items' => array( array(
                    'plan' => $plan_id
                ) )
            );

            $line_items = null;
        }

        $session = \Stripe\Checkout\Session::create( array(
            'customer_email' => $user_email,
            'payment_method_types' => array( 'card' ),
            'line_items' => $line_items,
            'client_reference_id' => $client_reference_id,
            'success_url' => $success_url,
            'cancel_url' => $course_page_url,
            'payment_intent_data' => array(
                'receipt_email' => $user_email,
            ),
            'subscription_data' => $subscription_data
        ) );

        $stripe_button .= '<div class="learndash_checkout_button learndash_stripe_button">';
            $stripe_button .= '<a id="learndash-stripe-checkout-button-'. $course_id .'" class="learndash-stripe-checkout-button btn-join button">' . $stripe_button_text . '</a>';
        $stripe_button .= '</div>';


$stripe_button .= <<<JS
<script src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">
    jQuery( document ).ready( function( $ ) {
        var stripe = Stripe( '{$this->publishable_key}' );

        $( '.learndash-stripe-checkout-button' ).on( 'click', function(e) {
            e.preventDefault();
            stripe.redirectToCheckout({
                sessionId: '{$session->id}'
            }).then(function (result) {
                if (result.error.message.length > 0) {
                    alert(result.error.message);
                }
            });
        });
    });
</script>
JS;

        return $stripe_button;
    }

    /**
     * Process Stripe new checkout
     * @return void
     */
    public function process_checkout() {
        if ( ! isset( $_GET['learndash-integration'] ) || $_GET['learndash-integration'] != 'stripe' ) {
            return;
        }

        $this->config();

        $payload = @file_get_contents( 'php://input' );
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $this->endpoint_secret
            );
        } catch( \UnexpectedValueException $e ) {
            http_response_code( 400 );
            exit();
        } catch( \Stripe\Error\SignatureVerification $e ) {
            http_response_code( 400 );
            exit();
        }

        // Handle the checkout.session.completed event
        if ( $event->type == 'checkout.session.completed' ) {
            $session = $event->data->object;

            $customer = \Stripe\Customer::retrieve( $session->customer );

            $email_address = $customer->email;
            $user_id = $this->get_user( $email_address );

            $client_reference_id = explode( ';', $session->client_reference_id );
            $course_id = null;
            foreach ( $client_reference_id as $value ) {
                if ( strpos( $value, 'course_id' ) !== false ) {
                    preg_match( '/course_id=(\d+)/', $value, $match );
                    $course_id = $match[1];
                }
            }

            // Associate course with user
            $this->associate_course( $course_id, $user_id );

            // Log transaction
            $this->record_transaction( $session, $course_id, $user_id, $email_address );
        }

        http_response_code( 200 );
    }

    /**
     * Output transaction message
     * @return void
     */
    public function output_transaction_message() {
        if ( ! isset( $_GET['ld_stripe'] ) || empty( $_GET['ld_stripe'] ) ) {
            return;
        }

        switch ( $_GET['ld_stripe'] ) {
            case 'success':
                $message = __( 'Your transaction was successful. Please log in to access your course.', 'learndash-stripe' );
                break;
            
            default:
                $message = false;
                break;
        }

        if ( ! $message ) {
            return;
        }

        ?>

        <script type="text/javascript">
            jQuery( document ).ready( function( $ ) {
                alert( '<?php echo $message ?>' );
            });
        </script>

        <?php
    }

    /**
     * Get user ID of the customer
     * @param  string $email User email address
     * @return int           User ID
     */
    public function get_user( $email ) {
        $user = get_user_by( 'email', $email );

        if ( false === $user ) {
            $password = wp_generate_password( 18, true, false );
            $new_user = $this->create_user( $email, $password );

            if ( ! is_wp_error( $new_user ) ) {
                $user_id     = $new_user;
                $user        = get_user_by( 'ID', $user_id );
                $customer_id = get_user_meta( $user_id, 'stripe_customer_id', true );
                $customer_id = $this->add_stripe_customer( $user_id, $customer_id, $email );

                // Need to allow for older versions of WP. 
                global $wp_version;
                if ( version_compare( $wp_version, '4.3.0', '<' ) ) {
                    wp_new_user_notification( $user_id, $password );
                } else if ( version_compare( $wp_version, '4.3.0', '==' ) ) {
                    wp_new_user_notification( $user_id, 'both' );                       
                } else if ( version_compare( $wp_version, '4.3.1', '>=' ) ) {
                    wp_new_user_notification( $user_id, null, 'both' );
                }
            }
        } else {
            $user_id = $user->ID;
            $customer_id = get_user_meta( $user_id, 'stripe_customer_id', true );
            $customer_id = $this->add_stripe_customer( $user_id, $customer_id, $email );
        }

        return $user_id;
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
     * @param  array  $session  Transaction data passed through $_POST
     * @param  int    $course_id    Post ID of a course
     * @param  int    $user_id      ID of a user
     * @param  string $user_email   Email of the user
     */
    public function record_transaction( $session, $course_id, $user_id, $user_email ) {
        // ld_debug( 'Starting Transaction Creation.' );
        
        $transaction = array(
            'stripe_sesion_id' => $session->id,
            'stripe_client_reference_id' => $session->client_reference_id,
            'stripe_customer' => $session->customer,
            'customer_email' => $user_email,
            'user_id' => $user_id,
            'course_id' => $course_id,
            'course_title' => get_the_title( $course_id ),
        );

        // ld_debug( 'Course Title: ' . $course_title );

        $post_id = wp_insert_post( array( 'post_title' => "Course {$transaction['course_title']} Purchased By {$user_email}", 'post_type' => 'sfwd-transactions', 'post_status' => 'publish', 'post_author' => $user_id ) );

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
     * Check if key is empty
     * @return bool True if empty, false otherwise
     */
    public function key_is_empty() {
        if ( empty( $this->secret_key ) && empty( $this->publishable_key ) ) {
            return true;
        }
    }
    
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
     * @param string $email       Email of a user, got from token
     * @param string $token_id    Token ID
     * @return string Stripe customer ID
     */
    public function add_stripe_customer( $user_id, $customer_id, $email ) {
        $this->config();

        try {
            $customer = \Stripe\Customer::retrieve( $customer_id );

            if ( isset( $customer->deleted ) && $customer->deleted ) {
                $customer = \Stripe\Customer::create( array(
                    'email'  => $email,
                ) );
            }

            $customer_id = $customer->id;

            update_user_meta( $user_id, 'stripe_customer_id', $customer_id );
            
        } catch ( Exception $e ) {
            $customer = \Stripe\Customer::create( array(
                'email'  => $email,
            ) );

            $customer_id = $customer->id;

            update_user_meta( $user_id, 'stripe_customer_id', $customer_id );
        }

        return $customer_id;
    }

    /**
     * Generate random string
     * @param  integer $length Length of the random string
     * @return string          Random string
     */
    public function generate_random_string(  $length = 3 ) {
        return substr( md5( microtime() ), 0, $length );
    }
}

new LearnDash_Stripe_Checkout_Integration();