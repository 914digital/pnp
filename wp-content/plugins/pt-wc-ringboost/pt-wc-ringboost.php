<?php
/**
 * Plugin Name: RingBoost Integration
 * Plugin URI: 
 * Description: Integrates RingBoost API actions
 * Version: 1.3
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

add_action( 'init', 'pt_wc_rb_process_search' );
function pt_wc_rb_process_search() {

	if ( isset( $_POST['pt_wc_rb_search_local'] ) || isset( $_GET['pt_wc_rb_paged_search'] ) ) {

		get_template_part( 'local-phone-number-result' );
		exit;

	}
}


add_action( 'wp_loaded', 'pt_wc_rb_process_get' );
function pt_wc_rb_process_get() {

	if ( isset( $_GET['pt_wc_rb_phone'] ) ) {

		pt_wc_rb_add_phone_to_cart();

	}
}


function pt_wc_rb_order_number( $number ) {

	$url   = esc_url_raw( pt_wc_rb_get_ringboost_url() . '/local/' . $number . '/order');

	$args  = array(

			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
								'Authorization' => 'Bearer ' . pt_wc_rb_get_token(),
							),

		);

	$response = wp_remote_post( $url, $args );

	if ( '200' != wp_remote_retrieve_response_code( $response ) ) {

		return false;

	} else {

		$response_body = wp_remote_retrieve_body( $response );
		$result        = json_decode( $response_body );
		
		return $result;

	}
}


function pt_wc_rb_maybe_order_phone_numbers( $order_id ) {

	$order = wc_get_order( $order_id );

	if ( $order ) {

		foreach ( $order->get_items() as $order_item ) {

			$product = $order_item->get_product();

			if ( 'yes' == get_post_meta( $product->get_id(), 'is_ringboost', true ) ) {

				// process only 1 time
				if ( 'yes' != get_post_meta( $product->get_id(), 'is_ringboost_ordered', true ) ) {

					$phone_number = $product->get_sku();

					//pt_wc_rb_reserve_number( $phone_number );

					pt_wc_rb_order_number( $phone_number );

					update_post_meta( $product->get_id(), 'is_ringboost_ordered', 'yes' );

				}
			}
		}
	}
}


function pt_wc_rb_remove_phone_from_cart( $cart_item_key, $cart ) {

	$product = wc_get_product( $cart->cart_contents[ $cart_item_key ][ 'product_id' ] );

	if ( 'yes' != get_post_meta( $product->get_id(), 'is_ringboost', true ) ) {

		return;

	}

	$phone_number = $product->get_sku();

	$release = pt_wc_rb_release_number( $phone_number );

	if ( 'Number released' == $release->result ) {


	} else {

		wc_add_notice( sprintf( esc_html__( 'Error while releasing %s, please try again.'), $phone_number ), 'error' );

	}
}


function pt_wc_rb_restore_phone_to_cart( $cart_item_key, $cart ) {

	$product = wc_get_product( $cart->cart_contents[ $cart_item_key ][ 'product_id' ] );

	if ( 'yes' != get_post_meta( $product->get_id(), 'is_ringboost', true ) ) {

		return;

	}

	$phone_number = $product->get_sku();
	$reserve = pt_wc_rb_reserve_number( $phone_number );


	if ( 'Number reserved' == $reserve->result ) {


	} else {

		wc_add_notice( sprintf( esc_html__( 'Error while reserving %s, please try again.'), $phone_number ), 'error' );

	}
}


function pt_wc_rb_add_phone_to_cart() {

	$phone_number = sanitize_text_field( $_GET['pt_wc_rb_phone'] );
	$details      = pt_wc_rb_get_number_details( $phone_number );

	if ( ! $details ) {

		wc_add_notice( sprintf( esc_html__( 'Error while getting price for %s, please try again.'), $phone_number ), 'error' );

	} else {

		/*
			stdClass Object
			(
			    [phone] => 2124330061
			    [area_code] => 212
			    [city] => NEW YORK
			    [state] => NY
			    [categories] => Array
			        (
			            [0] => 00XY
			            [1] => Name
			        )

			    [call_for_price] => 
			    [price] => 549
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

			$product->set_price( $details->price );
			$product->set_regular_price( $details->price );
			$product->set_virtual( 'yes' );
			$product->set_sku( $phone_number );
			$product->set_stock_status( 'instock' );
			$product->set_stock_quantity( 1 );
			$product->set_manage_stock( 'yes' );
			$product->set_image_id( 76 ); // fixed image id

			$product->save();

			update_post_meta( $product_id, 'is_ringboost', 'yes' );

		}

		$reserve = pt_wc_rb_reserve_number( $phone_number );

		if ( 'Number reserved' == $reserve->result ) {

			$add_to_cart = add_query_arg( 'add-to-cart', $product_id , wc_get_checkout_url() );
			wp_safe_redirect( $add_to_cart );
			exit;

		} else {

			wc_add_notice( sprintf( esc_html__( 'Error while reserving %s, please try again.'), $phone_number ), 'error' );

		}
	}
}


function pt_wc_rb_get_ringboost_url() {

	$url = 'https://partner.ringboost.com/';

	return $url;
}


function pt_wc_rb_get_token() {

	$token = 'yoJpbJbqepwUq1ECNlclrNnxKYrDiRqNdOAwQyOHXmrCJm7l3ldwme8qkp460MHL';

	return $token;
}


function pt_wc_rb_search_number() {

	if ( isset( $_REQUEST['pt_wc_rb_area'] ) && sanitize_text_field( $_REQUEST['pt_wc_rb_area'] ) ) {

		$area_code = '&area_code='. sanitize_text_field( $_REQUEST['pt_wc_rb_area'] );

	} else {

		$area_code = '';

	}

	if ( isset( $_REQUEST['pt_wc_rb_vanity'] ) && sanitize_text_field( $_REQUEST['pt_wc_rb_vanity'] ) ) {

		$vanity = '&vanity='. sanitize_text_field( $_REQUEST['pt_wc_rb_vanity'] );

	} else {

		$vanity = '';

	}

	if ( isset( $_REQUEST['pt_wc_rb_page'] ) ) {

		$page = intval( $_REQUEST['pt_wc_rb_page'] ) . '&';

	} else {

		$page = '1&';

	}

	$url   = esc_url_raw( pt_wc_rb_get_ringboost_url() . '/local?call_for_price=false&per_page=10&page=' . $page . $vanity . $area_code );

	$args  = array(

			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
								'Authorization' => 'Bearer ' . pt_wc_rb_get_token(),
							),

		);

	$response = wp_remote_get( $url, $args );

	if ( '200' != wp_remote_retrieve_response_code( $response ) ) {

		$message = esc_html__( 'Error searching number: ', 'pt-wc-ringboost' ) .  ' 
								<pre> ' . print_r( $response, 1 ) . '</pre>
								<pre> ' . print_r( $args, 1 ) . '</pre>';

		return false;

	} else {

		$response_body = wp_remote_retrieve_body( $response );
		$result        = json_decode( $response_body );

		return $result;

	}	
}


function pt_wc_rb_get_number_details( $number, $debug = false ) {

	$url   = esc_url_raw( pt_wc_rb_get_ringboost_url() . '/local/' . $number );

	$args  = array(

			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
								'Authorization' => 'Bearer ' . pt_wc_rb_get_token(),
							),

		);

	$response = wp_remote_get( $url, $args );

	if ( '200' != wp_remote_retrieve_response_code( $response ) ) {

		if ( $debug ) {

			$message = esc_html__( 'Error getting number details: ', 'pt-wc-ringboost' ) .  ' 
									<pre> ' . print_r( $response, 1 ) . '</pre>
									<pre> ' . print_r( $args, 1 ) . '</pre>';
			echo $message;
		}

		return false;

	} else {

		$response_body = wp_remote_retrieve_body( $response );
		$result        = json_decode( $response_body );

		if ( $debug ) {
			
			$json_pretty = json_encode( json_decode( $response_body ), JSON_PRETTY_PRINT );
			$message = '<pre>' . print_r( $json_pretty, 1 ) . '	</pre>';
			$message .= 'ARGS <pre> ' . print_r( $url, 1 ) . '</pre>';
			echo $message;

		}

		return $result;

	}
}


function pt_wc_rb_reserve_number( $number ) {

	$url   = esc_url_raw( pt_wc_rb_get_ringboost_url() . '/local/' . $number . '/reserve');

	$args  = array(

			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
								'Authorization' => 'Bearer ' . pt_wc_rb_get_token(),
							),

		);

	$response = wp_remote_post( $url, $args );

	if ( '200' != wp_remote_retrieve_response_code( $response ) ) {

		return false;

	} else {

		$response_body = wp_remote_retrieve_body( $response );
		$result        = json_decode( $response_body );

		return $result;

	}
}


function pt_wc_rb_release_number( $number, $debug = false ) {

	$url   = esc_url_raw( pt_wc_rb_get_ringboost_url() . '/local/' . $number . '/release');

	$args  = array(

			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
								'Authorization' => 'Bearer ' . pt_wc_rb_get_token(),
							),

		);

	$response = wp_remote_post( $url, $args );

	if ( '200' != wp_remote_retrieve_response_code( $response ) ) {

		if ( $debug ) {

			$message = esc_html__( 'Error releasing number: ', 'pt-wc-ringboost' ) .  ' 
									<pre> ' . print_r( $response, 1 ) . '</pre>
									<pre> ' . print_r( $args, 1 ) . '</pre>';
			wp_die( $message );

		}

		return false;

	} else {

		$response_body = wp_remote_retrieve_body( $response );
		$result        = json_decode( $response_body );

		return $result;

	}
}


add_action( 'plugins_loaded', 'pt_wc_rb_plugins_loaded', 20 );
function pt_wc_rb_plugins_loaded() {

	if ( ! class_exists( 'woocommerce' ) ) {  // Exit if WooCommerce isn't available
		return false;
	}

	add_action( 'woocommerce_remove_cart_item', 'pt_wc_rb_remove_phone_from_cart', 10, 2 );
	add_action( 'woocommerce_restore_cart_item', 'pt_wc_rb_restore_phone_to_cart', 10, 2 );

	add_action( 'woocommerce_thankyou', 'pt_wc_rb_maybe_order_phone_numbers' ); 

	// i18n
	load_plugin_textdomain( 'pt-wc-ringboost', false, '/pt-wc-ringboost/languages' );

}
