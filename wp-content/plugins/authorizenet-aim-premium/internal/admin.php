<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Indatos_Auth_Admin
{
   
   public static function admin_settings_fields()
   {
      
      return array(
            'enabled'      => array(
                  'title'        => __('Enable/Disable', 'wc-tech-authoaim'),
                  'type'         => 'checkbox',
                  'label'        => __('Enable Authorize.net Payment Module.', 'wc-tech-authoaim'),
                  'default'      => 'no'),
            'title'        => array(
                  'title'        => __('Title:', 'wc-tech-authoaim'),
                  'type'         => 'text',
                  'description'  => __('This controls the name pf payment method which the user sees during checkout.', 'wc-tech-authoaim'),
                  'default'      => __('Authorize.net', 'wc-tech-authoaim')),
            'description'  => array(
                  'title'        => __('Description:', 'wc-tech-authoaim'),
                  'type'         => 'textarea',
                  'description'  => __('This controls the description which the user sees during checkout.', 'wc-tech-authoaim'),
                  'default'      => __('Pay securely by Credit or Debit Card through Authorize.net Secure Servers.', 'wc-tech-authoaim')),
            'license'     => array(
                  'title'        => __('Plugin License Key', 'wc-tech-authoaim'),
                  'type'         => 'text',
                  'description'  => __('AUTH-PRO-XXXX-XXXX-XXXX-XXXX License key is required to download plugin updates. You can find license key in mail received after purchase. If you face any issue with license key, please contact customer support for assistance.')),
            'login_id'     => array(
                  'title'        => __('API Login ID', 'wc-tech-authoaim'),
                  'type'         => 'text',
                  'description'  => __('This is API Login ID')),
            'transaction_key' => array(
                  'title'        => __('Transaction Key', 'wc-tech-authoaim'),
                  'type'         => 'password',
                  'description'  =>  __('API Transaction Key', 'wc-tech-authoaim')),
            'success_message' => array(
                  'title'        => __('Transaction Success Message', 'wc-tech-authoaim'),
                  'type'         => 'textarea',
                  'description'=>  __('Message to be displayed on successful transaction.', 'wc-tech-authoaim'),
                  'default'      => __('Your payment has been procssed successfully.', 'wc-tech-authoaim')),
            'failed_message'  => array(
                  'title'        => __('Transaction Failed Message', 'wc-tech-authoaim'),
                  'type'         => 'textarea',
                  'description'  =>  __('Message to be displayed on failed transaction.', 'wc-tech-authoaim'),
                  'default'      => __('Your transaction has been declined.', 'wc-tech-authoaim')),
            'working_mode'    => array(
                  'title'        => __('API Mode'),
                  'type'         => 'select',
                  'options'      => array('false'=>'Live/Production Account Mode', 'true'=>'Sandbox/Developer Account Mode'),
                  'description'  => "Live mode to use actual API keys. Sandbox mode to use developer account API keys. Live account keys do not work with sandbox mode." ),
            'transaction_type'    => array(
                  'title'        => __('Transaction Type'),
                  'type'         => 'select',
                  'options'      => array('AUTH_CAPTURE' => 'Authorize and Capture', 'AUTH_ONLY' => 'Authorize Only (Capture later manually from order page)')),
            'how_to_authonly'    => array(
                  'title'        => __('Hold Or Process?'),
                  'type'         => 'select',
                  'options'      => array('hold'=>'Hold for review after transaction', 'process' => 'Set as processing'),
                  'description'  => "For Auth Only or Authorization Only transaction, you want to hold after payment for manual review or you would like to set it as processing",
                  'default'      => 'hold',
                  'class'        => 'auth_only_show',
               ),
            'auth_only_auto_capture'    => array(
               'title'        => __('Auto Capture?'),
               'type'         => 'select',
               'options'      => array('yes'=>'Auto Capture after order is set to process/complete.', 'no' => 'Do not Auto Capture. Will capture from order page manually.'),
               'description'  => "For Auth Only or Authorization Only transaction.",
               'default'      => 'no',
               'class'        => 'auth_only_show',
            ),
            'cardtypes' => array(
                   'title'    => __( 'Accepted Cards', 'woocommerce-idts-authnet' ),
                   'type'     => 'multiselect',
                   'class'    => 'chosen_select',
                   'css'      => 'width: 350px;',
                   'desc_tip' => __( 'Select the card types to accept.', 'wc-tech-authoaim' ),
                   'options'  => array(
                       'visa'       => 'Visa',
                       'mastercard' => 'MasterCard',
                       'amex'       => 'American Express',
                       'discover'   => 'Discover',
                       'jcb'        => 'JCB',
                       'diners'     => 'Diners Club',
                   ),
				'default' => array( 'visa', 'mastercard', 'amex', 'discover' ),),
          
         );
      
   }
   
   public static function get_card_processor_type($number)
   {
      $number=preg_replace('/[^\d]/','',$number);
		    if (preg_match('/^3[47][0-9]{13}$/',$number))
		    {
		        return 'amex';
		    }
		    elseif (preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',$number))
		    {
		        return 'dinersclub';
		    }
		    elseif (preg_match('/^6(?:011|5[0-9][0-9])[0-9]{12}$/',$number))
		    {
		        return 'discover';
		    }
		    elseif (preg_match('/^(?:2131|1800|35\d{3})\d{11}$/',$number))
		    {
		        return 'jcb';
		    }
		    elseif (preg_match('/^5[1-5][0-9]{14}$/',$number))
		    {
		        return 'mastercard';
		    }
		    elseif (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/',$number))
		    {
		        return 'visa';
		    }
		    else
		    {
		        return 'unknown card';
		    }
      
      
   }
   
   
  
}



