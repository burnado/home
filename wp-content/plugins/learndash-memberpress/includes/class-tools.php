<?php
/**
* Tools class
*/
class Learndash_Memberpress_Tools
{
	
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'retroactive_access_menu' ) );
		add_action( 'admin_notices', array( $this, 'retroactive_access_notice' ) );
		add_action( 'wp_ajax_learndash_memberpress_retroactive', array( $this, 'retroactive_access_process' ) );
		add_action( 'admin_head', array( $this, 'retroactive_access_page_style' ) );
	}

	public function retroactive_access_menu()
	{
		add_submenu_page( 'learndash_memberpress_not_exists', __( 'LearnDash Memberpress', 'learndash-memberpress' ), __( 'LearnDash Memberpress', 'learndash-memberpress' ), 'manage_options', 'learndash-memberpress', array( $this, 'learndash_memberpress_page') );
	}

	public function retroactive_access_notice()
	{
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'learndash-memberpress' ) {
			return;
		}

		$options = get_option( 'learndash_memberpress', array() );

		if ( isset( $options['retroactive_access'] ) && $options['retroactive_access'] == 1 ) {
			return;
		}

		?>

		<div id="message" class="notice notice-warning">
			<p><?php printf( __( 'After configuring LearnDash - Memberpress integration settings, please <a href="%s">click here</a> to start retroactive member access process.', 'learndash-memberpress' ), 'admin.php?page=learndash-memberpress&action=retroactive_access&_wpnonce=' . wp_create_nonce( 'learndash_memberpress' ) ); ?></p>
		</div>

		<?php
	}

	public function learndash_memberpress_page()
	{
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'retroactive_access' )
		{
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'learndash_memberpress' ) ) {
				return;
			}

			?>

			<div class="wrap">
				<div class="learndash-memberpress-message">
					<p><?php _e( 'The process is being done. Please be patient.', 'learndash-memberpress' ); ?></p>
				</div>
				<div class="learndash-memberpress-progress-bar-wrapper">
					<div class="learndash-memberpress-spinner"></div>
					<div class="learndash-memberpress-progress-bar-inner-wrapper">
						<div class="learndash-memberpress-progress-bar"></div>
						</div>
					<div class="clear"></div>
				</div>
			</div>

			<script type="text/javascript">
				jQuery( document ).ready( function( $ ) {
					$( window ).load( function() {
						process_step( 1 );
					} );

					function process_step( step ) {
						$.ajax( {
							url: ajaxurl,
							type: 'POST',
							dataType: 'json',
							data: {
								'action': 'learndash_memberpress_retroactive',
								'step': step,
							},
							success: function( response ) {
								if ( 'done' == response.step ) {
									$( '.learndash-memberpress-progress-bar' ).animate( {
										width: response.percentage + '%' },
										50, function() {
										// Animation complete.
									} );

									setTimeout( function() {
										$( '.learndash-memberpress-message' ).remove();
										$( '.learndash-memberpress-progress-bar' ).remove();
										$( '.learndash-memberpress-spinner' ).remove();

										$( '.learndash-memberpress-progress-bar-wrapper' ).html( '<p>' + '<?php _e( 'The process is complete.', 'learndash-memberpress' ); ?>' + '</p>');

										location.href = '<?php echo admin_url( 'edit.php?post_type=memberpressproduct' ); ?>';
									}, 2000 );
								} else {
									$( '.learndash-memberpress-progress-bar' ).animate( {
										width: response.percentage + '%' },
										50, function() {
										// Animation complete.
									} );

									process_step( parseInt( response.step ) );
								}
							}
						} )
						.fail( function( response ) {
							if ( window.console && window.console.log ) {
								console.log( response );
							}
						} );
					}
				});
			</script>

			<?php
		}
	}

	public function retroactive_access_process()
	{
		global $wpdb;
		$mepr_db = new MeprDb();

		$per_batch   = 100;
		$step        = intval( $_POST['step'] );
		$offset      = $per_batch * ( $step - 1 );
		$trans_total = MeprTransaction::get_count();
		$subs_total  = MeprDB::get_count( $mepr_db->subscriptions );
		$total 		 = intval( $trans_total + $subs_total );
		$percentage  = $step / ( $total / $per_batch ) * 100;
		$percentage  = $percentage >= 100 ? 100 : $percentage;

		if ( ceil( $total / $per_batch ) < $step ) {
			$options = get_option( 'learndash_memberpress', array() );
			$options['retroactive_access'] = 1;
			update_option( 'learndash_memberpress', $options );

			echo json_encode( array( 'percentage' => 100, 'step' => 'done' ) );
		} else {
			$per_batch = $per_batch / 2;
			$offset    = $per_batch * ( $step - 1 );

			// Transactions
			$query = "SELECT id, user_id, product_id, trans_num FROM {$mepr_db->transactions} WHERE status = 'complete' LIMIT {$per_batch} OFFSET {$offset}";
			$transactions = $wpdb->get_results( $query, OBJECT );

			foreach ( $transactions as $transaction ) {
				$trans_obj = new MeprTransaction( $trans_obj->id );

				if ( $trans_obj->subscription() ) {
					continue;
				}

				$courses = maybe_unserialize( get_post_meta( $transaction->product_id, '_learndash_memberpress_courses', true ) );

				foreach ( $courses as $course ) {
					Learndash_Memberpress_Integration::add_course_access( $course, $transaction->user_id, $transaction->trans_num );
				}
			}

			// Subscriptions
			$query = "SELECT id, user_id, product_id, subscr_id FROM {$mepr_db->subscriptions} WHERE status = 'active' LIMIT {$per_batch} OFFSET {$offset}";
			$subscriptions = $wpdb->get_results( $query, OBJECT );

			foreach ( $subscriptions as $subscription ) {
				$courses = maybe_unserialize( get_post_meta( $subscription->product_id, '_learndash_memberpress_courses', true ) );

				foreach ( $courses as $course ) {
					Learndash_Memberpress_Integration::add_course_access( $course, $subscription->user_id, $subscription->subscr_id );
				}
			}

			$step += 1;

			echo json_encode( array( 'percentage' => $percentage, 'step' => $step ) );
		}

		wp_die();
	}

	public function retroactive_access_page_style()
	{
		if ( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'retroactive_access' ) {
			return;
		}

		?>

		<style type="text/css">
			.learndash-memberpress-progress-bar-wrapper {
				border: 1px solid #c6c6bb;
				padding: 10px;
			}

			.learndash-memberpress-progress-bar-inner-wrapper {
				padding: 0;
				margin: 5px 0 0;
				background-color: #fff;
				display: block;
				float: left;
				line-height: 1;
				width: 94.8%;
			}

			.learndash-memberpress-progress-bar {
				background-color: #3498db;
				border: 1px solid #fff;
				height: 15px;
				width: 0;
				max-width: 100%;
			}

			.learndash-memberpress-spinner {
				border: 4px solid #f3f3f3; /* Light grey */
			    border-top: 4px solid #3498db; /* Blue */
			    background-color: #fff;
			    border-radius: 50%;
			    margin-right: 10px;
			    width: 20px;
			    height: 20px;
			    animation: spin 1s linear infinite;
			    float: left;
			}

			@keyframes spin {
				0% { transform: rotate( 0deg ); }
				100% { transform: rotate( 360deg ); }
			}

			.clear {
				clear: both;
			}
		</style>

		<?php
	}
}

new Learndash_Memberpress_Tools();