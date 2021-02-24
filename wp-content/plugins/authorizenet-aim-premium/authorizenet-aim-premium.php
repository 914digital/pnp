<?php
/*
Plugin Name: WooCommerce Authorize.net Premium
Description: Premium quality Authorize.net extension for WooCommerce stores. Authorize.net Certified Solution. Based on updated Authorize.net APIs.
Version: 13.8
Plugin URI: https://www.indatos.com/products/authorize-net-woocommerce-plugin-certified-solution/?ref=autho-aim-premium
Author: Indatos Technologies
Author URI: http://www.indatos.com?ref=autho-aim-premium
License: Under GPL2   
WC requires at least: 3.3.0
WC tested up to: 4.9.0
*/


add_action('plugins_loaded', 'woocommerce_tech_authoaim_init', 0);

include( plugin_dir_path( __FILE__ ) . 'internal/admin.php');
include( plugin_dir_path( __FILE__ ) . 'internal/core.php');
include( plugin_dir_path( __FILE__ ) . 'internal/utils.php');
include( plugin_dir_path( __FILE__ ) . 'process/payments.php');

$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$plugin_version = $plugin_data['Version'];

define('AUTHNET_PRO_VERSION', $plugin_version);


function woocommerce_tech_authoaim_init() {

   if ( !class_exists( 'WC_Payment_Gateway' ) ) 
      return;

   /**
   * Localisation
   */
   load_plugin_textdomain('wc-tech-authoaim', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

   /**
   * Authorize.net AIM Payment Gateway class
   */
   class WC_Tech_Authoaim extends WC_Payment_Gateway 
   {
      protected $msg = array();

      public function __construct(){

         $this->id               = 'authorizeaim';
         $this->method_title     = __('Authorize.net Premium', 'wc-tech-authoaim');
         $this->icon             = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/images/logo.gif';
         $this->has_fields       = true;
         $this->init_form_fields();
         $this->init_settings();
         $this->title            = $this->settings['title'];
         $this->description      = $this->settings['description'];
         $this->login            = $this->settings['login_id'];
         $this->mode             = $this->settings['working_mode'];
         $this->transaction_key  = $this->settings['transaction_key'];
         $this->auth_only_auto_capture  = ( isset($this->settings['auth_only_auto_capture'] ) ) ? $this->settings['auth_only_auto_capture'] : "";
         $this->how_to_authonly  = ( isset($this->settings['how_to_authonly'] ) ) ? $this->settings['how_to_authonly'] : "";
         $this->success_message  = $this->settings['success_message'];
         $this->failed_message   = $this->settings['failed_message'];
         $this->cardtypes        = $this->settings['cardtypes'];
         $this->license          = $this->settings['license'];
         $this->liveurl          = 'https://api.authorize.net/xml/v1/request.api';
         $this->testurl          = 'https://apitest.authorize.net/xml/v1/request.api';
         $this->msg['message']   = "";
         $this->msg['class']     = "";
         $this->supports         = array('products','refunds','default_credit_card_form',);



         if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
         } else {
            add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
         }

         add_action('woocommerce_receipt_authorizeaim', array(&$this, 'receipt_page'));
         add_action('woocommerce_thankyou_authorizeaim',array(&$this, 'thankyou_page'));
      }

      function init_form_fields()
      {

         $this->form_fields = Indatos_Auth_Admin::admin_settings_fields();
      }

      /**
       * Admin Panel Options
       * 
      **/
      public function admin_options()
      {

         echo '<h3>'.__('Authorize.net Premium Payment Gateway', 'wc-tech-authoaim').'</h3>';
         echo '<p>Authorize.net is most popular payment gateway for online payment processing. For any support connect with Tech Support team on <a href="https://www.indatos.com/wordpress-support/?ref=plugin-authnet-pro">Our Support Site</a>. For GDPR details, please contact plugin support.</p>';
         echo '<table class="form-table">';
         $this->generate_settings_html();
         echo '</table>';

      } 

      public function process_admin_options()
      {
         $this->init_settings();
         $this->settings['title'] = $_POST['woocommerce_authorizeaim_title'];
         $this->settings['description'] = $_POST['woocommerce_authorizeaim_description'];
         $this->settings['login_id']  = $_POST['woocommerce_authorizeaim_'.'login_id'];
         $this->settings['transaction_key'] =  $_POST['woocommerce_authorizeaim_'.'transaction_key'];
         $this->settings['working_mode'] = $_POST['woocommerce_authorizeaim_'.'working_mode'];
         $this->settings['success_message'] = $_POST['woocommerce_authorizeaim_'.'success_message'];;
         $this->settings['failed_message'] = $_POST['woocommerce_authorizeaim_'.'failed_message'];;
         $this->settings['cardtypes']  = $_POST['woocommerce_authorizeaim_'.'cardtypes'];;
         $this->settings['license']  = $_POST['woocommerce_authorizeaim_'.'license'];
         $this->settings['transaction_type']  = $_POST['woocommerce_authorizeaim_'.'transaction_type'];
         $this->settings['how_to_authonly']  = $_POST['woocommerce_authorizeaim_'.'how_to_authonly'];
         $this->settings['auth_only_auto_capture']  = $_POST['woocommerce_authorizeaim_'.'auth_only_auto_capture'];
         $this->settings['enabled']  = ( $_POST['woocommerce_authorizeaim_'.'enabled'] == 1 ) ? 'yes' : 'no' ;

         if( isset( $_POST['woocommerce_authorizeaim_license'] ) && $_POST['woocommerce_authorizeaim_license'] != '' )
            delete_transient( 'plugin_upgrade_authorizenet-aim-premium' );

         return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ), 'yes' );
      }


      /*Get Icon*/
      public function get_icon() {
         $icon = '';
         if( is_array( $this->cardtypes ) ) {
            $card_types = array_reverse( $this->cardtypes );
            foreach ( $card_types as $card_type ) {
               $icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/' . $card_type . '.png' ) . '" alt="' . $card_type . '" />';
            }
         }

         return apply_filters( 'woocommerce_authorizenet_icon', $icon, $this->id );
      }

      public function get_payment_method_image_url( $type ) {

         $image_type = strtolower( $type );
         return  WC_HTTPS::force_https_url( plugins_url( 'images/' . $image_type . '.png' , __FILE__ ) ); 
      }
      /*Get Icon*/




      /*Get Card Types*/
      function get_card_type($number)
      {
         return Indatos_Auth_Admin::get_card_processor_type($number);

      }// End of getcard type function



      /*Start of credit card form */
      public function payment_fields() {
         if ( $this->description ) 
            echo apply_filters( 'wc_authorizenet_description', wpautop(wp_kses_post( wptexturize(trim($this->description) ) ) ) );
         $this->form();
      }

      public function field_name( $name ) {
         return $this->supports( 'tokenization' ) ? '' : ' name="' . esc_attr( $this->id . '-' . $name ) . '" ';
      }

      public function form() {
         wp_enqueue_script( 'wc-credit-card-form' );
         $fields = array();
         $cvc_field = '<p class="form-row form-row-last">
			<label for="' . esc_attr( $this->id ) . '-card-cvc">' . __( 'Card Code', 'woocommerce' ) . ' <span class="required">*</span></label>
			<input id="' . esc_attr( $this->id ) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="' . esc_attr__( 'CVC', 'woocommerce' ) . '" ' . $this->field_name( 'card-cvc' ) . '/>
		</p>';
         $default_fields = array(
            'card-number-field' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr( $this->id ) . '-card-number">' . __( 'Card Number', 'woocommerce' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-card-number" class="input-text wc-credit-card-form-card-number" type="tel" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" ' . $this->field_name( 'card-number' ) . ' />
			</p>',
            'card-expiry-field' => '<p class="form-row form-row-first">
				<label for="' . esc_attr( $this->id ) . '-card-expiry">' . __( 'Expiry (MM/YY)', 'woocommerce' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="' . esc_attr__( 'MM / YY', 'woocommerce' ) . '" ' . $this->field_name( 'card-expiry' ) . ' />
			</p>',
            'card-cvc-field'  => $cvc_field
         );

         $fields = wp_parse_args( $fields, apply_filters( 'woocommerce_credit_card_form_fields', $default_fields, $this->id ) );
?>

<fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class='wc-credit-card-form wc-payment-form'>
   <?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>
   <?php
         foreach ( $fields as $field ) {
            echo $field;
         }
   ?>
   <?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
   <div class="clear"></div>
</fieldset>
<?php

      }
      /*End of credit card form*/




      function process_refund($order_id, $amount = null, $reason = '')
      {


         global $woocommerce;
         $order                   = new WC_Order($order_id);
         $woocommre_fund_data     = get_post_meta( $order_id, 'woocommre_fund_data', true );
         if ($woocommre_fund_data == ''){
            $order->add_order_note('Order is processed with older version of Auth Plugin. Does not support refund.');
            return false;
         }


         $woocommre_fund_data     = unserialize($woocommre_fund_data);


         $tx_status   = Indatos_Auth_Payments::get_tx_settlement_status($woocommre_fund_data[0], $this->settings, $this);



         if(    $tx_status->transaction->transactionStatus == 'capturedPendingSettlement' ){
            //void
            if( $amount == $order->get_total() ) {

               $void_tx_status = Indatos_Auth_Payments::void_after_capture($order_id, $woocommre_fund_data[0], $this->settings, $this, $reason);


               if ($void_tx_status->transactionResponse->responseCode  == 1){

                  $order->add_order_note('Order/Transaction Voided. Auth Code: '. $void_tx_status->transactionResponse->authCode . ' Transaction ID: '. $void_tx_status->transactionResponse->refTransID );

                  $order->add_order_note('Voided Tx amount '. $amount .'. Reason of void/refund: '.$reason );

                  update_post_meta( $order_id, 'woocomm_capture_status', '2' );

                  update_post_meta( $order_id, 'woocomm_refund_status', '1' );
                  return true;

               }else{
                  return false;
               }


            }else{
               return new WP_Error( 'broke',   'Transaction related to this order is not settled yet by Authorize.net. You cannot do partial refunds for unsettled transactions. To void the order and transaction, can be done only for full amount.');

            }



         }else{
            //refund





            $wc_auth = new WC_Tech_Authoaim();


            if($wc_auth->mode == 'true'){
               $process_url = $wc_auth->testurl;
               $authorizeaim_args['x_test_request'] = FALSE;
            }
            else{
               $process_url = $wc_auth->liveurl;
               $authorizeaim_args['x_test_request'] = FALSE;
            }


            $xml_refund_request = '<createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
               <merchantAuthentication>
               <name>'.$wc_auth->login.'</name>
               <transactionKey>'.$wc_auth->transaction_key.'</transactionKey>
               </merchantAuthentication>
               <refId>123456</refId>
               <transactionRequest>
               <transactionType>refundTransaction</transactionType>
               <amount>'.$amount.'</amount>
               <payment>
               <creditCard>
               <cardNumber>'.substr($woocommre_fund_data[1],4).'</cardNumber>
               <expirationDate>XXXX</expirationDate>
               </creditCard>
               </payment>
               <refTransId>'.$woocommre_fund_data[0].'</refTransId>
               </transactionRequest>
               </createTransactionRequest>';
            $headers = array(
               "Content-type: text/xml",
               "Content-length: " . strlen($xml_refund_request),
               "Connection: close",
            );
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL,$process_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_refund_request);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $data = curl_exec($ch); 

            $xml_response =simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOWARNING) or die("Error: Cannot create object");

            if ( $xml_response->transactionResponse->responseCode == "1"){

               update_post_meta( $order_id, 'woocomm_refund_status', '1' );
               $order->add_order_note('Refund of amount '. $amount .'. Message from Authorize.net: '. $xml_response->transactionResponse->messages->messages->description . 'Transaction ID: '. $xml_response->transactionResponse->transId .'. Reason of refund: '.$reason );
               return true;

            }else if ($xml_response->transactionResponse->responseCode == "3" ){

               update_post_meta( $order_id, 'woocomm_refund_status', '0' );
               $order->add_order_note('Refund Failed of amount '. $amount .'. Message from Authorize.net: '. $xml_response->transactionResponse->errors->error->errorText.'. Transaction which are successfully settled can only be refunded. Reason of refund: '.$reason );
               return new WP_Error( 'broke',  "Transaction which are successfully settled can only be refunded. Please wait while transaction related to order is settled. Usually a period of 24 hours.");

            }else{
               $order->add_order_note('Refund Failed of amount'. $amount .'. Error: '. $xml_response->transactionResponse->errors->error->errorText .'. Reason of refund: '.$reason);
               return new WP_Error( 'broke',   'Error: '. $xml_response->transactionResponse->errors->error->errorText );
            }


         }/// rf


      }


      public function thankyou_page($order_id) 
      {


      }

      /**
      * Receipt Page
      **/
      function receipt_page($order)
      {
         echo '<p>'.__('Thank you for your order.', 'wc-tech-authoaim').'</p>';

      }


      function validate_fields()
      {
         if( empty( $_POST[ 'authorizeaim-card-number' ]) ) {
            wc_add_notice(  'Credit Card is required!', 'error' );
            return false;
         }


         if( strlen( $_POST[ 'authorizeaim-card-number' ]) < 12 ||  strlen( $_POST[ 'authorizeaim-card-number' ]) > 19 ) {
            wc_add_notice(  'Credit Card number is invalid!', 'error' );
            return false;
         }


         if( empty( $_POST[ 'authorizeaim-card-expiry' ]) ) {
            wc_add_notice(  'Credit Card expiry date is required!', 'error' );
            return false;
         }

         if( strlen( $_POST[ 'authorizeaim-card-expiry' ]) < 5 ) {
            wc_add_notice(  'Credit Card expiry date is invalid!', 'error' );
            return false;
         }

         if( empty( $_POST[ 'authorizeaim-card-cvc' ]) ) {
            wc_add_notice(  'Credit Card CVC/CVV2 code is required!', 'error' );
            return false;
         }

         if( strlen( $_POST[ 'authorizeaim-card-cvc' ]) < 3 ||  strlen( $_POST[ 'authorizeaim-card-cvc' ]) > 4 )  {
            wc_add_notice(  'Credit Card CVC/CVV2 code is invalid!', 'error' );
            return false;
         }

         return true;
      }

      /**
       * Process the payment and return the result
      **/
      function process_payment($order_id)
      {
         global $woocommerce;
         $order = new WC_Order($order_id);

         if($this->mode == 'true'){
            $process_url = $this->testurl;
         }
         else{
            $process_url = $this->liveurl;
         }

         // $params = $this->generate_authorizeaim_params($order);

         $tx_response   = Indatos_Auth_Payments::auth_charge_card($order, $this->settings, $this);


         if ( strtoupper( $tx_response->messages->resultCode ) == "OK" ){

            if( $tx_response->transactionResponse->responseCode == '1' ){

               if ($order->get_status() != 'completed') {

                  $current_transaction_id  = (string)$tx_response->transactionResponse->transId ;
                  $last_four               = (string)$tx_response->transactionResponse->accountNumber;


                  $woocommerce->cart->empty_cart();

                  if( $this->settings['transaction_type'] == "AUTH_ONLY" ){

                     if( $this->how_to_authonly == 'hold'){
                        $order->update_status('on-hold');
                        $order->add_order_note('Transaction held for review. (Authorize Only transaction). Order status changed from pending-payment to on-hold' );

                     }else{
                        $order->payment_complete( $current_transaction_id );
                     }

                     $order->add_order_note('Card Authorization Approved. Auth Code: '. (string)$tx_response->transactionResponse->authCode . '. Transaction ID: '. $current_transaction_id );
                     $order->add_order_note("Card Last 4: ". (string)$tx_response->transactionResponse->accountNumber . " ". (string)$tx_response->transactionResponse->accountType);



                     $ref_data = array(  $current_transaction_id , $last_four);

                     update_post_meta( $order->get_id(), 'auth_data', serialize($ref_data) );
                     update_post_meta( $order->get_id(), 'woocomm_capture_status', '0' );
                     update_post_meta( $order->get_id(), 'woo_authnet_tx_id',$current_transaction_id );
                     update_post_meta( $order->get_id(), '_transaction_id',$current_transaction_id );




                  }else{
                     $order->payment_complete( $current_transaction_id );
                     $order->add_order_note($this->success_message. ' Auth Code: '.  (string)$tx_response->transactionResponse->authCode . '. Transaction ID: '.  $current_transaction_id );

                     $order->add_order_note("Card Last 4: ". (string)$tx_response->transactionResponse->accountNumber . " ". (string)$tx_response->transactionResponse->accountType);
                  }

                  $order->add_order_note("AVS Status - ". Indatos_Auth_Payments::get_avs_message( (string)$tx_response->transactionResponse->avsResultCode) );

                  $ref_data = array(  $current_transaction_id , $last_four);

                  update_post_meta( $order->get_id(), 'woo_authnet_tx_id', $current_transaction_id);
                  update_post_meta( $order->get_id(), 'woocommre_fund_data', serialize($ref_data) );
                  update_post_meta( $order->get_id(), 'woocomm_refund_status', '0' );



               }

               return array('result'   => 'success',
                            'redirect'  => $order->get_checkout_order_received_url() );
            }
            else if( $tx_response->transactionResponse->responseCode  == '2' ){
               $order->add_order_note($this->failed_message);
               $order->add_order_note("AVS Status - ". Indatos_Auth_Payments::get_avs_message( (string)$tx_response->transactionResponse->avsResultCode) );
               wc_add_notice( __('(Transaction Error): ', 'wc-tech-authoaim')  .  $tx_response->transactionResponse->errors->error->errorText, 'error' );
               return;
            }

            else if( $tx_response->transactionResponse->responseCode == '3' ){
               $order->add_order_note($this->failed_message);
               $order->add_order_note("AVS Status - ". Indatos_Auth_Payments::get_avs_message( (string)$tx_response->transactionResponse->avsResultCode) );
               wc_add_notice( __('(Transaction Error): ', 'wc-tech-authoaim')  . (string)$tx_response->transactionResponse->errors->error->errorText, 'error' );
               return;
            }

            else if( $tx_response->transactionResponse->responseCode == '4' ){
               $order->add_order_note("AVS Status - ". Indatos_Auth_Payments::get_avs_message( (string)$tx_response->transactionResponse->avsResultCode) );
               $order->add_order_note( __('(Transaction On-Hold):', 'wc-tech-authoaim')  .  (string)$tx_response->transactionResponse->errors->error->errorText, 'error' );
               $order->update_status('on-hold');
               return array('result'   => 'success',
                            'redirect'  => $order->get_checkout_order_received_url() );
            }
            else{

               $order->add_order_note($this->failed_message);
               wc_add_notice( __('(Transaction Error): ', 'wc-tech-authoaim') .  (string)$tx_response->transactionResponse->errors->error->errorText, 'error' );
               return;
            }
         }
         else if ( strtoupper( $tx_response->messages->resultCode ) == "ERROR" ) {

            if( isset( $tx_response->transactionResponse->errors->error->errorText ) ){

               $order->add_order_note( "Transaction Failed. Error Code: ". (string)$tx_response->messages->message->code . " ". (string)$tx_response->messages->text. " ".  (string)$tx_response->transactionResponse->errors->error->errorText );

               wc_add_notice( __('(Transaction Error): ', 'wc-tech-authoaim') .  (string)$tx_response->messages->message->text . " ". (string)$tx_response->transactionResponse->errors->error->errorText, 'error' );

            }else{

               $order->add_order_note( "Transaction Failed. Error Code: ". (string)$tx_response->messages->message->code . " ". (string)$tx_response->messages->text );
               wc_add_notice( __('(Transaction Error): ', 'wc-tech-authoaim') .  (string)$tx_response->messages->message->text , 'error' );

            }

            $order->update_status('failed');
            return;
         }else{
            $order->add_order_note($this->failed_message);
            $order->update_status('failed');
            wc_add_notice( __('(Transaction Error):', 'wc-tech-authoaim') . ' Error processing payment.', 'error' );
            return;
         }

      }

   }




   /**
    * Add this Gateway to WooCommerce
   **/
   function woocommerce_add_tech_authoaim_gateway($methods) 
   {
      $methods[] = 'WC_Tech_Authoaim';
      return $methods;
   }
   add_filter('woocommerce_payment_gateways', 'woocommerce_add_tech_authoaim_gateway' );

   function techauth_aim_add_custom_box() {

      $screens = array( 'shop_order' );
      foreach ( $screens as $screen ) {
         add_meta_box(
            'myplugin_sectionid',
            __( 'Capture with Authorize.net', 'myplugin_textdomain' ),
            'techauth_aim_inner_custom_box',
            $screen,
            'side',
            'high'
         );
      }
   }
   add_action( 'add_meta_boxes', 'techauth_aim_add_custom_box' );

   function techauth_aim_inner_custom_box($post)
   {
      wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );
      global $woocommerce;
      $order                   = new WC_Order($post->ID);

      $woo_auth_data      = get_post_meta( $post->ID, 'auth_data', true );
      $woocomm_capture_status   = get_post_meta( $post->ID, 'woocomm_capture_status', true );


      if ($woocomm_capture_status == "0" ){
         echo '<label for="myplugin_new_field">';
         _e( 'Select to Capture for Order #'.$post->ID.'. <br/>Total Amount to Capture: '.get_woocommerce_currency_symbol() . $order->get_total().'.<br/>Use Void to cancel authorization.<br/>', 'myplugin_textdomain' );
         echo '</label> ';

         echo '<select name="woocomm_capture_status" id="woocomm_capture_status">
            <option value="0" >Do Nothing</option>
            <option value="1" >Capture: '.get_woocommerce_currency_symbol().$order->get_total().'</option>        
            <option value="2" >Void: '.get_woocommerce_currency_symbol().$order->get_total().'</option>        
            </select>';

      }
      if ($woocomm_capture_status == "" ){

         echo '<select name="woocomm_capture_status_dis" id="woocomm_refund_status_dis">
            <option value="0" >Order is not Eligible for Capture</option>
            </select><input type="hidden" value="" name="woocomm_capture_status" />';

      }
      if ($woocomm_capture_status == "1" ){

         echo '<select name="woocomm_capture_status_dis" id="" disabled>
            <option value="0" >Do Nothing</option>
            <option value="1" selected >Already Captured for '.get_woocommerce_currency_symbol().$order->get_total().'</option>        
            </select><input type="hidden" value="3" name="woocomm_capture_status" />';

      }
      if ($woocomm_capture_status == "2" ){

         echo '<select name="woocomm_capture_status_dis" id="" disabled>
            <option value="0" >Do Nothing</option>
            <option value="1" selected >Already Voided for '.get_woocommerce_currency_symbol().$order->get_total().'</option>        
            </select><input type="hidden" value="3" name="woocomm_capture_status" />';

      }

   }
   /////

   function techauth_aim_capture_postdata( $post_id ) {

      // Check if our nonce is set.
      if ( ! isset( $_POST['myplugin_inner_custom_box_nonce'] ) )
         return $post_id;

      $nonce = $_POST['myplugin_inner_custom_box_nonce'];

      // Verify that the nonce is valid.
      if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_custom_box' ) )
         return $post_id;

      // If this is an autosave, our form has not been submitted, so we don't want to do anything.
      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
         return $post_id;

      // Check the user's permissions.
      if ( 'page' == $_POST['post_type'] ) {

         if ( ! current_user_can( 'edit_page', $post_id ) )
            return $post_id;

      } else {

         if ( ! current_user_can( 'edit_post', $post_id ) )
            return $post_id;
      }


      $ref_status = sanitize_text_field( $_POST['woocomm_capture_status'] );


      if ($ref_status == 1){
         global $woocommerce;
         $order                   = new WC_Order($post_id);
         $woo_auth_data     = get_post_meta( $post_id, 'auth_data', true );
         $woo_auth_data     = unserialize($woo_auth_data);

         $wc_auth = new WC_Tech_Authoaim();


         if($wc_auth->mode == 'true'){
            $process_url = $wc_auth->testurl;
            $authorizeaim_args['x_test_request'] = FALSE;
         }
         else{
            $process_url = $wc_auth->liveurl;
            $authorizeaim_args['x_test_request'] = FALSE;
         }

         ##
         $xml_capture_request = '<createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
           <merchantAuthentication>
             <name>'.$wc_auth->login.'</name>
             <transactionKey>'.$wc_auth->transaction_key.'</transactionKey>
           </merchantAuthentication>
           <refId>'.wc_clean( $post_id ).'</refId>
           <transactionRequest>
             <transactionType>priorAuthCaptureTransaction</transactionType>
             <amount>'.$order->get_total().'</amount>
             <refTransId>'.$woo_auth_data[0].'</refTransId>
            <order>
              <invoiceNumber>'.$order->get_order_number().'</invoiceNumber>
              <description>Cart Checkout</description>
            </order>
           </transactionRequest>
         </createTransactionRequest>';
         $headers = array(
            "Content-type: text/xml",
            "Content-length: " . strlen($xml_capture_request),
            "Connection: close",
         );
         $ch = curl_init(); 
         curl_setopt($ch, CURLOPT_URL,$process_url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_TIMEOUT, 10);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_capture_request);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
         $data = curl_exec($ch); 

         $xml_response =simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOWARNING) or die("Error: Cannot create object");

         ###

         if ($xml_response->transactionResponse->responseCode  == 1){
            $order->add_order_note('Amount '.get_woocommerce_currency_symbol() . $order->get_total() .' Captured. Tx Auth Code: '. $xml_response->transactionResponse->authCode  . ' Transaction ID: '. $xml_response->transactionResponse->transId  );
            update_post_meta( $post_id, 'woocomm_capture_status', '1' );

            if ($order->get_status() == 'on-hold') {
               $order->payment_complete( (string)$xml_response->transactionResponse->transId );
            }

         }



      }
      //void
      if ($ref_status =="2"){
         global $woocommerce;
         $order                   = new WC_Order($post_id);
         $woo_auth_data     = get_post_meta( $post_id, 'auth_data', true );
         $woo_auth_data     = unserialize($woo_auth_data);

         $wc_auth = new WC_Tech_Authoaim();



         if($wc_auth->mode == 'true'){
            $process_url = $wc_auth->testurl;
            $authorizeaim_args['x_test_request'] = FALSE;
         }
         else{
            $process_url = $wc_auth->liveurl;
            $authorizeaim_args['x_test_request'] = FALSE;
         }

         ##
         $xml_void_request = '<createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
         <merchantAuthentication>
         <name>'.$wc_auth->login.'</name>
         <transactionKey>'.$wc_auth->transaction_key.'</transactionKey>
         </merchantAuthentication>
         <refId>'.wc_clean( $post_id ).'</refId>
         <transactionRequest>
         <transactionType>voidTransaction</transactionType>
         <refTransId>'.$woo_auth_data[0].'</refTransId>
         </transactionRequest>
         </createTransactionRequest>';
         $headers = array(
            "Content-type: text/xml",
            "Content-length: " . strlen($xml_void_request),
            "Connection: close",
         );
         $ch = curl_init(); 
         curl_setopt($ch, CURLOPT_URL,$process_url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_TIMEOUT, 10);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_void_request);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
         $data = curl_exec($ch); 

         $xml_void_response =simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOWARNING) or die("Error: Cannot create object");
         ###
         if ($xml_void_response->transactionResponse->responseCode  == 1){
            $order->add_order_note('Authorization Voided. '. $xml_void_response->transactionResponse->authCode . 'Transaction ID: '. $xml_void_response->transactionResponse->refTransID );
            update_post_meta( $post_id, 'woocomm_capture_status', '2' );
         }

      }

   }

   add_action( 'save_post', 'techauth_aim_capture_postdata' );


   add_action('woocommerce_order_status_changed','authnet_plugin_check_change');
   function authnet_plugin_check_change($post_id){

      $db_ref_status = sanitize_text_field( get_post_meta($post_id, 'woocomm_capture_status', true) );

      if( $db_ref_status == 0 ){

         $wc_auth = new WC_Tech_Authoaim();
         global $woocommerce;
         $order = new WC_Order($post_id);  
         $current_order_status = $order->get_status();

         if( $wc_auth->auth_only_auto_capture == "yes" ){

            $capture_flag = false;

            if( $wc_auth->how_to_authonly == "hold" && ( $current_order_status == 'completed' || $current_order_status == 'processing') ){
               $capture_flag = true;
            }else if( $wc_auth->how_to_authonly == "process" && ( $current_order_status == 'completed')  ){

               $capture_flag = true;

            }

            if($capture_flag){

               $woo_auth_data     = get_post_meta( $post_id, 'auth_data', true );
               $woo_auth_data     = unserialize($woo_auth_data);

               if($wc_auth->mode == 'true'){
                  $process_url = $wc_auth->testurl;
                  $authorizeaim_args['x_test_request'] = FALSE;
               }
               else{
                  $process_url = $wc_auth->liveurl;
                  $authorizeaim_args['x_test_request'] = FALSE;
               }

               ##
               $xml_capture_request = '<createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
           <merchantAuthentication>
             <name>'.$wc_auth->login.'</name>
             <transactionKey>'.$wc_auth->transaction_key.'</transactionKey>
           </merchantAuthentication>
           <refId>'.wc_clean( $post_id ).'</refId>
           <transactionRequest>
             <transactionType>priorAuthCaptureTransaction</transactionType>
             <amount>'.$order->get_total().'</amount>
             <refTransId>'.$woo_auth_data[0].'</refTransId>
            <order>
              <invoiceNumber>'.$order->get_order_number().'</invoiceNumber>
              <description>Cart Checkout</description>
            </order>
           </transactionRequest>
         </createTransactionRequest>';
               $headers = array(
                  "Content-type: text/xml",
                  "Content-length: " . strlen($xml_capture_request),
                  "Connection: close",
               );
               $ch = curl_init(); 
               curl_setopt($ch, CURLOPT_URL,$process_url);
               curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
               curl_setopt($ch, CURLOPT_TIMEOUT, 10);
               curl_setopt($ch, CURLOPT_POST, true);
               curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_capture_request);
               curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
               curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
               $data = curl_exec($ch); 

               $xml_response =simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOWARNING) or die("Error: Cannot create object");

               ###

               if ($xml_response->transactionResponse->responseCode  == 1){
                  $order->add_order_note('Amount '.get_woocommerce_currency_symbol() . $order->get_total() .'Auto Captured. Tx Auth Code: '. $xml_response->transactionResponse->authCode  . ' Transaction ID: '. $xml_response->transactionResponse->transId  );
                  update_post_meta( $post_id, 'woocomm_capture_status', '1' );

                  if ($order->get_status() == 'on-hold') {
                     $order->payment_complete( (string)$xml_response->transactionResponse->transId );
                  }

               }
            }







         }



      }
   }



}



$core  = new Indatos_Authorizenetpro_Core();
$utils  = new Indatos_Authorizenetpro_Utils();

add_filter('admin_footer', 'add_authnet_plugin_jquery_data');

function add_authnet_plugin_jquery_data()
{

   global $parent_file;


   if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['section'] ) && $_GET['section'] = 'authorizeaim' ) {

      echo '<script>';
      echo 'jQuery(document).ready(function($){ $("#woocommerce_authorizeaim_transaction_type").on("change", function(){ if($("#woocommerce_authorizeaim_transaction_type").val() == "AUTH_ONLY"){ $(".auth_only_show").closest("tr").show();}else{ $(".auth_only_show").closest("tr").hide();} }); if($("#woocommerce_authorizeaim_transaction_type").val() == "AUTH_ONLY"){ $(".auth_only_show").closest("tr").show();}else{ $(".auth_only_show").closest("tr").hide();} });';
      echo '</script>';

   }

}
