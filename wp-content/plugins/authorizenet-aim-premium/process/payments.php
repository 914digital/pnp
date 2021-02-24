<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Indatos_Auth_Payments
{
   
      /**
       * Get current user's IP 
       */
      public static function getClientIP()
      {
         if( isset($_SERVER) ) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
               return $_SERVER["HTTP_X_FORWARDED_FOR"];
         
            if (isset($_SERVER["HTTP_CLIENT_IP"]))
               return $_SERVER["HTTP_CLIENT_IP"];

              return $_SERVER["REMOTE_ADDR"];
           }

          
      }
   
   
   public static function void_after_capture($order_id, $tx_id, $settings, $parentObj)
   {
        ##
        $xml_void_request = '<createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
  <merchantAuthentication>
             <name>'.$parentObj->login.'</name>
               <transactionKey>'.$parentObj->transaction_key.'</transactionKey>
           </merchantAuthentication>
  <refId>'.wc_clean( $order_id ).'</refId>
  <transactionRequest>
    <transactionType>voidTransaction</transactionType>
    <refTransId>'.$tx_id.'</refTransId>
   </transactionRequest>
</createTransactionRequest>';
         
       $headers = array(
         "Content-type: text/xml",
         "Content-length: " . strlen($xml_void_request),
         "Connection: close",
         );

            if($parentObj->mode == 'true'){
              $process_url = $parentObj->testurl;
            //  $authorizeaim_args['x_test_request'] = FALSE;
            }
            else{
              $process_url = $parentObj->liveurl;
              //$authorizeaim_args['x_test_request'] = FALSE;
            }
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
      
        
        return $xml_void_response;
      
     
            
           
      
   }
   
   
   public static function get_tx_settlement_status($tx_id, $settings,$parentObj)
   {
      
   $xml  = '<getTransactionDetailsRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
               <merchantAuthentication>
                  <name>'.$parentObj->login.'</name>
               <transactionKey>'.$parentObj->transaction_key.'</transactionKey>
               </merchantAuthentication>
               <transId>'.$tx_id.'</transId>
               </getTransactionDetailsRequest>';
      
       
         $headers = array(
         "Content-type: text/xml",
         "Content-length: " . strlen($xml),
         "Connection: close",
         );

            if($parentObj->mode == 'true'){
              $process_url = $parentObj->testurl;
            //  $authorizeaim_args['x_test_request'] = FALSE;
            }
            else{
              $process_url = $parentObj->liveurl;
              //$authorizeaim_args['x_test_request'] = FALSE;
            }
         $ch = curl_init(); 
         curl_setopt($ch, CURLOPT_URL,$process_url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_TIMEOUT, 10);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
         $data = curl_exec($ch); 

         $xml_response =simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOWARNING) or die("Error: Cannot create object");


            return $xml_response;
   }
    /**
      * Generate authorize.net AIM button link
      **/
      public static function auth_charge_card($order, $settings, $parentObj)
      {
         $exp_date         = explode( "/", sanitize_text_field($_POST['authorizeaim-card-expiry']));
		$exp_month        = str_replace( ' ', '', $exp_date[0]);
		$exp_year         = str_replace( ' ', '',$exp_date[1]);

		if (strlen($exp_year) == 2) {
            $exp_year += 2000;
        }
         $order_ID =  $order->get_id();
          $remove_charas = array("&","\"","\'","<",">");
         
         
         if( !isset($settings['transaction_type']) || $settings['transaction_type'] == ''){
            $tx_type  = 'authCaptureTransaction';
         }else if( $settings['transaction_type'] == "AUTH_ONLY"){
            $tx_type  = 'authOnlyTransaction';
         }else{
            $tx_type  = 'authCaptureTransaction';
         }

         
            
         $xml = '<createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
  <merchantAuthentication>
    <name>'.$parentObj->login.'</name>
    <transactionKey>'.$parentObj->transaction_key.'</transactionKey>
  </merchantAuthentication>
  <refId>'.wc_clean( $order_ID ).'</refId>
  <transactionRequest>
    <transactionType>'.$tx_type.'</transactionType>
    <amount>'.$order->get_total().'</amount>
    <payment>
      <creditCard>
        <cardNumber>'.sanitize_text_field(str_replace(' ','',$_POST['authorizeaim-card-number'])).'</cardNumber>
        <expirationDate>'.$exp_year.'-'.$exp_month.'</expirationDate>
        <cardCode>'.sanitize_text_field($_POST['authorizeaim-card-cvc']).'</cardCode>
      </creditCard>
    </payment>
    <order>
     <invoiceNumber>'.self::xml_filters($order->get_order_number()).'</invoiceNumber>
     <description>Cart Checkout</description>
    </order>
    <lineItems>';
         $line_item_count = 1;
         $order_items = $order->get_items();
         foreach($order_items as $single_item){
            
            if($single_item['line_tax'] == 0){
               $is_tax = 'N';
            }else{
               $is_tax = 'Y';
            }
          
            
            $xml .= '<lineItem>
        <itemId>'.$line_item_count++.'</itemId>
         <name>'.self::xml_filters(substr($single_item['name'], 0, 20)).'</name>
        <description></description>
        <quantity>'.$single_item['qty'].'</quantity>
        <unitPrice>'.number_format(($single_item['subtotal']/$single_item['qty']),2, '.', '').'</unitPrice>
      </lineItem>';
            
           
            
         }
      
         
         
     $xml .=    '
    </lineItems>
    <tax>
      <amount>'. number_format($order->get_total_tax() , 2, ".",'' ) .'</amount>
      <name>Tax</name>
    </tax>
    <shipping>
      <amount>'. number_format($order->get_shipping_total() , 2, ".",'' ).'</amount>
      <name>Shipping</name>
      <description></description>
    </shipping>
    <customer>
      <id>'.$order->get_customer_id().'</id>
      <email>'.$order->get_billing_email().'</email>
    </customer>
    <billTo>
      <firstName>'.self::xml_filters($order->get_billing_first_name(), 50).'</firstName>
      <lastName>'.self::xml_filters($order->get_billing_last_name(), 50).'</lastName>
      <company>'. self::xml_filters($order->get_billing_company(), 50).'</company>
      <address>'. substr(self::xml_filters($order->get_billing_address_1()) .' '. self::xml_filters($order->get_billing_address_2()), 0, 58).'</address>
      <city>'.self::xml_filters($order->get_billing_city(), 40).'</city>
      <state>'.$order->get_billing_state().'</state>
      <zip>'.$order->get_billing_postcode().'</zip>
      <country>'.$order->get_billing_country().'</country>
      <phoneNumber>'. self::xml_filters($order->get_billing_phone()).'</phoneNumber>
    </billTo>
    <shipTo>
      <firstName>'.self::xml_filters($order->get_shipping_first_name(), 50).'</firstName>
      <lastName>'.self::xml_filters($order->get_shipping_last_name(), 50).'</lastName>
      <company>'.self::xml_filters($order->get_shipping_company(), 50).'</company>
      <address>'. substr(self::xml_filters($order->get_shipping_address_1()) .' '. self::xml_filters($order->get_shipping_address_2()),0, 58) .'</address>
      <city>'.self::xml_filters($order->get_shipping_city(), 40).'</city>
      <state>'.$order->get_shipping_state().'</state>
      <zip>'. $order->get_shipping_postcode().'</zip>
      <country>'.$order->get_shipping_country().'</country>
    </shipTo>
    <customerIP>'.self::getClientIP().'</customerIP>
    <retail>
      <marketType>0</marketType>
      <deviceType>8</deviceType>
    </retail>
  </transactionRequest>
</createTransactionRequest>';
         
         $headers = array(
    "Content-type: text/xml",
    "Content-length: " . strlen($xml),
    "Connection: close",
);

         if($parentObj->mode == 'true'){
           $process_url = $parentObj->testurl;
         //  $authorizeaim_args['x_test_request'] = FALSE;
         }
         else{
           $process_url = $parentObj->liveurl;
           //$authorizeaim_args['x_test_request'] = FALSE;
         }
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL,$process_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
$data = curl_exec($ch); 

$xml_response =simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOWARNING) or die("Error: Cannot create object");

         
         return $xml_response;
      }
   
   public static function xml_filters( $data, $size = 0 )
   {
      
      $search           = array("&","\"","\'","<",">");
      $replace          = array("&#38;","&#34;","&#39;","&#60;","&#62;");
      $filtered_data    =  str_replace($search, $replace, $data);
      
      if( $size == 0 ){
         
      return $filtered_data;
      }else{
         return substr($filtered_data, 0, $size);
      }
   }
   
   public static function get_avs_message( $code ) 
   {
      
		$avs_messages = array(
			'A' => __( 'Street Address: Match -- First 5 Digits of ZIP: No Match', 'woocommerce-idts-authnet' ),
			'B' => __( 'Address not provided for AVS check or street address match, postal code could not be verified', 'woocommerce-idts-authnet' ),
			'E' => __( 'AVS Error', 'woocommerce-idts-authnet' ),
			'G' => __( 'Non U.S. Card Issuing Bank', 'woocommerce-idts-authnet' ),
			'N' => __( 'Street Address: No Match -- First 5 Digits of ZIP: No Match', 'woocommerce-idts-authnet' ),
			'P' => __( 'AVS not applicable for this transaction', 'woocommerce-idts-authnet' ),
			'R' => __( 'Retry, System Is Unavailable', 'woocommerce-idts-authnet' ),
			'S' => __( 'AVS Not Supported by Card Issuing Bank', 'woocommerce-idts-authnet'),
			'U' => __( 'Address Information For This Cardholder Is Unavailable', 'woocommerce-idts-authnet' ),
			'W' => __( 'Street Address: No Match -- All 9 Digits of ZIP: Match', 'woocommerce-idts-authnet' ),
			'X' => __( 'Street Address: Match -- All 9 Digits of ZIP: Match', 'woocommerce-idts-authnet' ),
			'Y' => __( 'Street Address: Match - First 5 Digits of ZIP: Match', 'woocommerce-idts-authnet' ),
			'Z' => __( 'Street Address: No Match - First 5 Digits of ZIP: Match', 'woocommerce-idts-authnet' ),
		);
		if ( array_key_exists( $code, $avs_messages ) ) {
			return $avs_messages[$code];
		} else {
			return '';
		}
	}
   
   
}