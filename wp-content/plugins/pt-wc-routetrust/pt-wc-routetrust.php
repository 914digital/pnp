<?php
/**
 * Plugin Name: RouteTrust Integration
 * Plugin URI: 
 * Description: Integrates RouteTrust API actions
 * Version: 1.0
 * Author: Gabriel Reguly
 * Author URI: 
 * Requires at least: 5.5
 * Tested up to: 5.5.1
 *
 * WC requires at least: 4.0
 * WC tested up to: 4.7.1
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'init', 'pt_wc_rt_process_post' );
function pt_wc_rt_process_post() {

	if ( isset( $_POST['pt_wc_rt_search_tollfree'] ) ) {

		//wp_die('search tollfree' );

		
		$phone_number = '8335721800';

		//echo 'pt_wc_rt_get_number_details( ' . $phone_number .' )<pre>' . print_r( pt_wc_rt_get_number_details( $phone_number, true ), 1 ) . '</pre>';
		//echo 'pt_wc_rt_reserve_number( ' . $phone_number .' )<pre>' . print_r( pt_wc_rt_reserve_number( $phone_number ), 1 ) . '</pre>';
		//echo 'pt_wc_rt_release_number( ' . $phone_number .' )<pre>' . print_r( pt_wc_rt_release_number( $phone_number, true ), 1 ) . '</pre>';
		//echo 'pt_wc_rt_order_number( ' . $phone_number .' )<pre>' . print_r( pt_wc_rt_order_number( $phone_number ), 1 ) . '</pre>';

		get_template_part( 'toolfree-phone-number-result' );
		exit;

	}
}

add_action( 'wp_loaded', 'pt_wc_rt_process_get' );
function pt_wc_rt_process_get() {

	if ( isset( $_GET['pt_wc_rt_phone'] ) ) {

		pt_wc_rt_add_phone_to_cart();

	}
}


function pt_wc_rt_order_number( $number ) {

	$url   = esc_url_raw( pt_wc_rt_get_routetrust_url() . '/api/rt800/numbers/' . $number . '/order');

	//https://rtx.portal.routetrust.com/api/rt800/numbers/8335721800/order

	$args  = array(

			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
								'Authorization' => 'Bearer ' . pt_wc_rt_get_token(),
							),

		);

	$response = wp_remote_post( $url, $args );

	if ( '200' != wp_remote_retrieve_response_code( $response ) ) {

		/*$message = esc_html__( 'Error ordering number details: ', 'pt-wc-routetrust' ) .  ' 
								<pre> ' . print_r( $response, 1 ) . '</pre>
								<pre> ' . print_r( $args, 1 ) . '</pre>';
		wp_die( $message );/**/

		return false;

	} else {

		$response_body = wp_remote_retrieve_body( $response );
		$json          = json_decode( $response_body );
		
		/**/
		$json_pretty = json_encode( json_decode( $response_body ), JSON_PRETTY_PRINT );
		$message = '<pre>' . print_r( $json, 1 ) . '	</pre>';
		$message .= 'ARGS <pre> ' . print_r( $url, 1 ) . '</pre>';
		wp_die( $message );/**/

		return $json;

	}
}


function pt_wc_rt_maybe_order_phone_numbers( $order_id ) {

	$order = wc_get_order( $order_id );

	if ( $order ) {

		foreach ( $order->get_items() as $order_item ) {

			$product = $order_item->get_product();

			if ( 'yes' == get_post_meta( $product->get_id(), 'is_routetrust', true ) ) {

				// process only 1 time
				if ( 'yes' != get_post_meta( $product->get_id(), 'is_routetrust_ordered', true ) ) {

					$phone_number = $product->get_sku();

					//pt_wc_rt_reserve_number( $phone_number );

					pt_wc_rt_order_number( $phone_number );

					update_post_meta( $product->get_id(), 'is_routetrust_ordered', 'yes' );

				}
			}
		}
	}
}


function pt_wc_rt_remove_phone_from_cart( $cart_item_key, $cart ) {

	$product = wc_get_product( $cart->cart_contents[ $cart_item_key ][ 'product_id' ] );

	if ( 'yes' != get_post_meta( $product->get_id(), 'is_routetrust', true ) ) {

		return;

	}

	$phone_number = $product->get_sku();

	$released = pt_wc_rt_release_number( $phone_number );

	if ( ! $released ) {

		wc_add_notice( sprintf( esc_html__( 'Error while releasing %s, please try again.'), $phone_number ), 'error' );

	}
}


function pt_wc_rt_restore_phone_to_cart( $cart_item_key, $cart ) {

	$product = wc_get_product( $cart->cart_contents[ $cart_item_key ][ 'product_id' ] );

	if ( 'yes' != get_post_meta( $product->get_id(), 'is_routetrust', true ) ) {

		return;

	}

	$phone_number = $product->get_sku();
	$reserved     = pt_wc_rt_reserve_number( $phone_number );

	if ( ! $reserved ) {

		wc_add_notice( sprintf( esc_html__( 'Error while reserving %s, please try again.'), $phone_number ), 'error' );

	}
}


function pt_wc_rt_add_phone_to_cart() {

	$phone_number = sanitize_text_field( $_GET['pt_wc_rt_phone'] );
	$details      = pt_wc_rt_get_number_details( $phone_number );

	if ( ! $details ) {

		wc_add_notice( sprintf( esc_html__( 'Error while getting price for %s, please try again.'), $phone_number ), 'error' );

		wp_die( wc_print_notices( true )) ;

	} else {

		if ( 'R' == $details->fnStatusId ) {

			wc_add_notice( sprintf( esc_html__( 'Number %s is already reserved, please try other number.'), $phone_number ), 'error' );
			return;

		}


		//wp_die( 'details <pre>' . print_r( $details, 1 ) . '</pre>') ;

		/*

			stdClass Object
			(
			    [respOrgId] => RZA09
			    [statusTs] => 2021-01-15T13:00:00.000Z
			    [effectiveTs] => 2021-01-15T13:00:00.000Z
			    [lastUpdateTs] => 2021-01-15T13:02:09.000Z
			    [lastAssignTs] => 
			    [lastDisconnectTs] => 
			    [lastPendingTs] => 
			    [lastReserveTs] => 2021-01-15T13:02:09.000Z
			    [lastSpareTs] => 2017-04-22T11:00:00.000Z
			    [lastTransitionalTs] => 
			    [lastUnavailableTs] => 
			    [lastWorkingTs] => 
			    [lastWorkingRespOrgId] => 
			    [lastWorkingDays] => 
			    [totalWorkingDays] => 
			    [availableTs] => 
			    [templateName] => 
			    [interLataCarriers] => 
			    [intraLataCarriers] => 
			    [tfn] => 8335721800
			    [tfnStatusId] => W (Working)
			    [tfnStatusId] => R (Reserved)
			    [tfnStatusId] => S (Spared)     // Released
			    
			)
		*/

		$product_id = wc_get_product_id_by_sku( $phone_number );

		if ( ! $product_id ) {

			$args = array(	   
				'post_author' => 1, 
				'post_content' => '',
				'post_status' => "publish",
				'post_title' => $phone_number,
				'post_parent' => '',
				'post_type' => "product"
			); 

			// Create a simple WooCommerce product
			$product_id = wp_insert_post( $args );

			$product = wc_get_product( $product_id );

			$product->set_price( $details->price / 100 );
			$product->set_regular_price( $details->price / 100 );
			$product->set_virtual( 'yes' );
			$product->set_sku( $phone_number );
			$product->set_manage_stock( 'yes' );
			$product->set_stock_status( 'instock' );
			$product->set_stock_quantity( 1 );
			$product->set_image_id( 76 ); // fixed image id

			$product->save();

			update_post_meta( $product_id, 'is_routetrust', 'yes' );

		}

		$reserved = pt_wc_rt_reserve_number( $phone_number );

		if ( ! $reserved ) {

			wc_add_notice( sprintf( esc_html__( 'Error while reserving %s, please try again.'), $phone_number ), 'error' );

		} else {

			// add number to cart and redirect to checkout page
			$add_to_cart = add_query_arg( 'add-to-cart', $product_id , wc_get_checkout_url() );
			wp_safe_redirect( $add_to_cart );
			exit;

		}

	}
}


function pt_wc_rt_get_routetrust_url() {

	//$url = 'https://tollfree.portal.routetrust.com';
	$url = 'https://rtx.portal.routetrust.com';

	return $url;
}


function pt_wc_rt_get_token() {

	$url  = esc_url_raw( pt_wc_rt_get_routetrust_url() . '/api/authorization/login' );
	$args = array( 
					'body' => array( 
								'email'    => 'gabriel@ppgr.com.br', 
								'password' => '94Q337EJ',
								),
			);

	$response = wp_remote_post( $url, $args );

	if ( '201' != wp_remote_retrieve_response_code( $response ) ) {

		$message = esc_html__( 'Error creating auth token: ', 'pt-wc-routetrust' ) .  ' 
								Response <pre> ' . print_r( $response, 1 ) . '</pre><br />
								URL<pre> ' . print_r( $url, 1 ) . '</pre>';

		wp_die( $message );


	} else {

		$response_body = wp_remote_retrieve_body( $response );
		$json = json_decode( $response_body );

		//wp_die( $json->rtxJwt );

		return $json->rtxJwt;

	}	
}


function pt_wc_rt_search_number() {

	$body = array( 'pageSize' => 20 );

	if ( isset( $_POST['pt_wc_rt_vanity'] ) && sanitize_text_field( $_POST['pt_wc_rt_vanity'] ) ) {

		$body['search'] = sanitize_text_field( $_POST['pt_wc_rt_vanity'] );

	}

	$url   = esc_url_raw( pt_wc_rt_get_routetrust_url() . '/api/rt800/numbers' );

	$args  = array(

			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
								'Authorization' => 'Bearer ' . pt_wc_rt_get_token(),
							),
			'body'        => $body,

		);

	$response = wp_remote_post( $url, $args );

	if ( '201' != wp_remote_retrieve_response_code( $response ) ) {

		$message = esc_html__( 'Error searching number: ', 'pt-wc-routetrust' ) .  ' 
								<pre> ' . print_r( $response, 1 ) . '</pre>
								<pre> ' . print_r( $args, 1 ) . '</pre>';

		wp_die( $message );
		return false;

	} else {

		$response_body = wp_remote_retrieve_body( $response );
		$json = json_decode( $response_body );

		return $json;

		/**
		$json_pretty = json_encode( $json, JSON_PRETTY_PRINT );
		$message     = 'Response <pre>' . print_r( $json_pretty, 1 ) . '	</pre>';
		$message    .= 'URL <pre> ' . print_r( $url, 1 ) . '</pre>';
		$message    .= 'ARGS <pre> ' . print_r( $args, 1 ) . '</pre>';
		wp_die( $message );/**/


	}
}


function pt_wc_rt_get_number_details( $number, $debug = false ) {

	$url   = esc_url_raw( pt_wc_rt_get_routetrust_url() . '/api/rt800/numbers/' . $number );

	$args  = array(

			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
								'Authorization' => 'Bearer ' . pt_wc_rt_get_token(),
							),

		);

	$response = wp_remote_get( $url, $args );

	if ( '200' != wp_remote_retrieve_response_code( $response ) ) {

		if ( $debug ) {

			$message = esc_html__( 'Error getting number details: ', 'pt-wc-routetrust' ) .  ' 
									<pre> ' . print_r( $response, 1 ) . '</pre>
									<pre> ' . print_r( $args, 1 ) . '</pre>';
			echo $message;
		}

		return false;

	} else {

		$response_body = wp_remote_retrieve_body( $response );
		$json          = json_decode( $response_body );

		if ( $debug ) {
			
			$json_pretty = json_encode( json_decode( $response_body ), JSON_PRETTY_PRINT );
			$message = '<pre>' . print_r( $json, 1 ) . '	</pre>';
			$message .= 'ARGS <pre> ' . print_r( $url, 1 ) . '</pre>';
			echo $message;

		}

		return $json;

	}
}


function pt_wc_rt_reserve_number( $number, $debug = false ) {

	$url   = esc_url_raw( pt_wc_rt_get_routetrust_url() . '/api/rt800/numbers/' . $number . '/reserve');

	$args  = array(

			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
								'Authorization' => 'Bearer ' . pt_wc_rt_get_token(),
							),

		);

	$response = wp_remote_post( $url, $args );

	if ( '201' == wp_remote_retrieve_response_code( $response ) ) {

		return true;

	} else {

		if ( $debug ) {

			$message = esc_html__( 'Error reserving number: ', 'pt-wc-routetrust' ) .  ' 
									<pre> ' . print_r( $response, 1 ) . '</pre>
									<pre> ' . print_r( $args, 1 ) . '</pre>';
			wp_die( $message );

		}

>>>>>>> 445ad8f913a86648e831b215d45f3f9ddceed758

		return false;

	}
}


function pt_wc_rt_release_number( $number, $debug = false ) {

	$url   = esc_url_raw( pt_wc_rt_get_routetrust_url() . '/api/rt800/numbers/' . $number . '/spare');

	$args  = array(

			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
								'Authorization' => 'Bearer ' . pt_wc_rt_get_token(),
							),

		);

	$response = wp_remote_post( $url, $args );

	if ( '201' == wp_remote_retrieve_response_code( $response ) ) {

		return true;

	} else {

		if ( $debug ) {

			$message = esc_html__( 'Error releasing number: ', 'pt-wc-routetrust' ) .  ' 
									<pre> ' . print_r( $response, 1 ) . '</pre>
									<pre> ' . print_r( $args, 1 ) . '</pre>';
			wp_die( $message );

		}

		return false;

	}
}


add_action( 'plugins_loaded', 'pt_wc_rt_plugins_loaded', 20 );
function pt_wc_rt_plugins_loaded() {

	if ( ! class_exists( 'woocommerce' ) ) {  // Exit if WooCommerce isn't available
		return false;
	}

	add_action( 'woocommerce_remove_cart_item', 'pt_wc_rt_remove_phone_from_cart', 10, 2 );
	add_action( 'woocommerce_restore_cart_item', 'pt_wc_rt_restore_phone_to_cart', 10, 2 );

	add_action( 'woocommerce_thankyou', 'pt_wc_rt_maybe_order_phone_numbers' ); 

	//pt_wc_rt_maybe_order_phone_numbers( 343 );


	// Display a Settings link on the main WP Plugins page
	//add_filter( 'plugin_action_links', 'pt_wc_rt_action_links', 10, 2 );

	// our admin functions
	//add_action( 'admin_menu', 'pt_wc_rt_admin_menu', 10 );

	// i18n
	load_plugin_textdomain( 'pt-wc-routetrust', false, '/pt-wc-routetrust/languages' );


	function pt_wc_rt_admin_menu() {

		if ( current_user_can( 'manage_woocommerce' ) ) {

			add_submenu_page( 'woocommerce',
				 __( 'Indica Tudo', 'pt-wc-routetrust' ),
				 __( 'Indica Tudo', 'pt-wc-routetrust' ) ,
				 'manage_woocommerce',
				 'pt_wc_rt_routetrust_integration',
				 'pt_wc_rt_show_menu_page');
		}
	}


	function pt_wc_rt_show_menu_page() {
		
		do_action( 'admin_enqueue_scripts' );

		$tab = 'log';

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'woocommerce_indica_tudo' ) {
			
			if ( isset( $_GET['tab'] ) ) {
			
				if ( in_array( $_GET['tab'], array( 
													'api',
													'coupons',
													'log' ) ) ) {

					$tab = esc_attr( $_GET['tab'] );

				}

			}
		}

		?>

		<div class="wrap woocommerce">
			<div id="icon-woocommerce" class="icon32 icon32-woocommerce-settings"></div>
			<h2 class="nav-tab-wrapper"> 

				<a href="<?php echo admin_url('admin.php?page=woocommerce_indica_tudo&tab=api'); ?>" class="nav-tab <?php if ( $tab == 'api' ) echo 'nav-tab-active'; ?>">
				<?php esc_html_e( 'API', 'pt-wc-routetrust' ); ?></a>

				<a href="<?php echo admin_url('admin.php?page=woocommerce_indica_tudo&tab=coupons'); ?>" class="nav-tab <?php if ( $tab == 'coupons' ) echo 'nav-tab-active'; ?>">
				<?php esc_html_e( 'Coupons', 'pt-wc-routetrust' ); ?></a>

				<a href="<?php echo admin_url('admin.php?page=woocommerce_indica_tudo&tab=log'); ?>" class="nav-tab <?php if ( $tab == 'log' ) echo 'nav-tab-active'; ?>">
				<?php esc_html_e( 'Log', 'pt-wc-routetrust' ); ?></a> 

			</h2>
		<?php

			switch ( $tab ) {

				case 'api':
					pt_wc_rt_display_api_tab();
				break;

				case 'coupons':
					pt_wc_rt_display_coupons_tab();
				break;

				case 'log':
					pt_wc_rt_display_log_tab();
				break;

			}

		?>
		</div>
		<?php
	}


	function pt_wc_rt_display_api_tab() {

		//delete_option( 'coupons_indicatudo');
		//pt_wc_rt_process_api_check();

		//$order = wc_get_order( 90 );
		//pt_wc_rt_process_venda( $order );

		//$order = wc_get_order( 95 );
		//pt_wc_rt_process_venda( $order );

		?>

			<div class="panel woocommerce_options_panel">
				<h1>Login details: <pre><?php print_r( pt_wc_rt_get_api_details() ) ?></pre></h1>
				<p>Last API response</p>

			<?php

				$api_reponse_transient = get_transient( 'company_api_reponse_transient' );
				$api_response          = json_decode( wp_remote_retrieve_body( $api_reponse_transient ), true );

				echo '<pre>' . print_r( $api_response, 1 ) . '</pre>';


				if ( wp_next_scheduled( 'pt_wc_rt_api_check' ) ) {

					/*if ( 0 && false === ( $api_gold_price = get_transient( 'api_gold_price' ) ) ) { 

						// this code runs when there is no valid transient set

						pt_wc_rt_process_api_check();

						if ( false === ( $api_gold_price = get_transient( 'api_gold_price' ) ) ) {

							// no luck again, something is broken

							$api_reponse_transient = get_transient( 'api_reponse_transient' );
							$api_response          = json_decode( wp_remote_retrieve_body( $api_reponse_transient ), true );

							echo ' API Response <pre>' . print_r( $api_response, 1 ) . '</pre>';
							//echo ' BAD RESPONSE <pre>' . print_r( $api_reponse_transient, 1 ) . '</pre>';

						}

					}*/

					?>

					<h2><?php printf( esc_html__( 'Next API update %s', 'pt-wc-routetrust'), pt_wc_rt_better_human_time_diff( wp_next_scheduled( 'pt_wc_rt_api_check' ) ) ); ?></h2>

					<?php			
				}
			?>
			<h3> To do list</h3>
			<ul>
				<li>Cron manager: <a href="<?php echo admin_url( 'tools.php?page=advanced-cron-manager' );?>">pt_wc_rt_api_check</a></li>
				<li>Get a real cron service: <a href="https://wpspeedmatters.com/external-cron-jobs-in-wordpress/">https://wpspeedmatters.com/external-cron-jobs-in-wordpress/</a></li>
			</ul>
		</div>
			<?php
	}


	function pt_wc_rt_display_coupons_tab() {


		$rates = get_option( 'coupon_rates_indicatudo' );

		$coupons = get_option( 'coupons_indicatudo' );
		$list = str_getcsv( $coupons, "\n" );

		unset( $list[0] );

		?>

			<div class="panel woocommerce_options_panel">
				<h1><?php esc_html_e( 'Coupon rates', 'pt-wc-routetrust' ); ?></h1>
				<pre><?php print_r( $rates ); ?></pre>
				<hr />
				<h1><?php esc_html_e( 'Coupons', 'pt-wc-routetrust' ); ?></h1>
				<ol>
					<?php

						foreach( $list as $coupon ) {

							$details = explode( ';', $coupon );
							printf( '<li>%s</li>', $details[0] );

						}

					?>
				</ol>
			</div>

		<?php
	}


	function pt_wc_rt_display_log_tab() {

		$class = '';

		if ( isset($_GET['clear_log'] )	&& 1 == $_GET['clear_log']  && check_admin_referer( 'clear_log' ) ) {

			pt_wc_rt_delete_log();

		}

		?>

			<div class="panel woocommerce_options_panel">

				<h3><?php _e('Logged events', 'pt-wc-routetrust');?> <a href="<?php echo wp_nonce_url( admin_url('admin.php?page=woocommerce_indica_tudo&tab=log&clear_log=1' ), 'clear_log' ); ?>" class="button-primary right">    <?php _e( 'Clear Log', 'pt-wc-routetrust') ?> </a></h3>
				<table class="widefat">
					<thead>
						<tr>
							<th style="width: 150px"><?php _e( 'Timestamp', 'pt-wc-routetrust') ?></th>
							<th><?php _e( 'Event', 'pt-wc-routetrust') ?></th>
							<th><?php _e( 'User', 'pt-wc-routetrust') ?></th>
						</tr>
					</thead>
					<tbody>

						<?php 

						foreach ( pt_wc_rt_get_log() as $event ) {

							if ( ! $event[2] ) {

								$display_name = '&#9889;';

							} else {

								$user_data = get_userdata( $event[2] ); 
								$display_name = $user_data->display_name;

							}

							?>

							<tr <?php echo $class ?>>
								<td><?php echo pt_wc_rt_nice_time( $event[0] ); ?></td>
								<td><?php echo $event[1]; ?></td>
								<td><?php echo $display_name; ?></td>
							</tr>

							<?php 

								if ( empty( $class ) )  {

									$class = ' class="alternate"';

								} else {

									$class = '';

								}
						}
						?>

					</tbody>
				</table>
			</div>

		<?php
	}


	function pt_wc_rt_better_human_time_diff( $from, $to = '', $limit = 3 ) {

		// Since all months/years aren't the same, these values are what Google's calculator says
		$units = apply_filters( 'time_units', array(
				31556926 => array( __('%s year'),  __('%s years') ),
				2629744  => array( __('%s month'), __('%s months') ),
				604800   => array( __('%s week'),  __('%s weeks') ),
				86400    => array( __('%s day'),   __('%s days') ),
				3600     => array( __('%s hour'),  __('%s hours') ),
				60       => array( __('%s min'),   __('%s mins') ),
				1        => array( __('%s sec'),   __('%s secs') ),
		) );

		if ( empty($to) ) {
			$to = time();
		}

		$from = (int) $from;
		$to   = (int) $to;
		
		$t_diff = $to - $from;
		
		$diff = (int) abs( $to - $from );

		$items = 0;
		$output = array();

		foreach ( $units as $unitsec => $unitnames ) {

				if ( $items >= $limit ) {
					break;
				}

				if ( $diff < $unitsec ) {
					continue;
				}

				$numthisunits = floor( $diff / $unitsec );
				$diff         = $diff - ( $numthisunits * $unitsec );
				$items++;

				if ( $numthisunits > 0 ) {
					$output[] = sprintf( _n( $unitnames[0], $unitnames[1], $numthisunits ), $numthisunits );
				}

		}

		// translators: The separator for human_time_diff() which seperates the years, months, etc.
		$separator = _x( ', ', 'human_time_diff' );
		
		if ( ! empty( $output ) ) {

			$human_time = implode( $separator, $output );

		} else {

			$smallest   = array_pop( $units );
			$human_time = sprintf( $smallest[0], 1 );

		}

		if ( $t_diff < 0 ) {

			return sprintf( __( 'in %s' ), $human_time );

		} else {

			return '<strong>' . sprintf( __( 'is %s late' ), $human_time ) . '</strong>';
		}
	}


	// Display a Settings link on the main Plugins page for easy access
	function pt_wc_rt_action_links( $links, $file ) {

		if ( plugin_basename( __FILE__ ) == $file ) {

			$url = get_admin_url() . 'admin.php?page=pt_wc_rt_routetrust_integration&tab=api';

			$pt_wc_rt_settings_link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'pt-wc-routetrust' ) . '</a>';

			// make the 'Settings' link appear first
			array_unshift( $links, $pt_wc_rt_settings_link );
		}

		return $links;
	}


	// logging functionality //

	function pt_wc_rt_nice_time( $time, $args = false ) {

		$defaults = array( 'format' => 'date_and_time' );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		if ( ! $time)
			return false;

		if ( $format == 'date' )
			return date( get_option( 'date_format' ), $time );

		if ( $format == 'time' )
			return date( get_option( 'time_format' ), $time );

		if ( $format == 'date_and_time' ) //get_option( 'time_format' )
			return date( get_option( 'date_format' ), $time ) . " " . date( 'H:i:s', $time );

		return false;
	}


	function pt_wc_rt_log( $event ) {
		$current_user = wp_get_current_user();
		$current_user_id = $current_user->ID;

		$log = get_option( 'pt_wc_rt_log' );

		$time_difference = get_option( 'gmt_offset' ) * 3600;
		$time            = time() + $time_difference;

		if ( ! is_array( $log ) ) {
			$log = array();
			array_push( $log, array( $time, __( 'Log Started.', 'pt-wc-routetrust' ), $current_user_id ) );
		}

		array_push( $log, array( $time, $event, $current_user_id ) );
		return update_option( 'pt_wc_rt_log', $log );
	}


	function pt_wc_rt_get_log() {
		$log = get_option( 'pt_wc_rt_log' );
		// If no log created yet, create one
		if ( ! is_array( $log ) ) {
			$current_user    = wp_get_current_user();
			$current_user_id = $current_user->ID;
			$log             = array();
			$time_difference = get_option( 'gmt_offset' ) * 3600;
			$time            = time() + $time_difference;
			array_push( $log, array( $time, __( 'Log Started.', 'pt-wc-routetrust' ), $current_user_id ) );
			update_option( 'pt_wc_rt_log', $log );
		}
		return array_reverse( get_option( 'pt_wc_rt_log' ) );
	}


	function pt_wc_rt_delete_log() {
		$current_user    = wp_get_current_user();
		$current_user_id = $current_user->ID;
		$log             = array();
		$time_difference = get_option( 'gmt_offset' ) * 3600;
		$time            = time() + $time_difference;
		array_push( $log, array( $time, __( 'Log cleared.', 'pt-wc-routetrust' ), $current_user_id ) );
		update_option( 'pt_wc_rt_log', $log );
	}


	function pt_wc_rt_clear_old_log_entries() {
		$log = get_option( 'pt_wc_rt_log' );
		if ( is_array( $log ) ) {

			$time_difference = get_option( 'gmt_offset' ) * 3600;
			$time            = strtotime( date( 'Y-m-d h:i:s', time() + $time_difference ) . ' - 2 days' );
			$old_time        = -1;

			while ( $old_time < $time  ) {
				$log_entry = array_pop( $log );
				$old_time  = $log_entry[0];
				update_option( 'pt_wc_rt_log', $log );
			}
		}
	}
}
