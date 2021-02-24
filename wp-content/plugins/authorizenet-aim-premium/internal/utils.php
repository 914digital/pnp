<?php
if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

class Indatos_Authorizenetpro_Utils
{


   public function __construct() 
   {  

      add_action( 'add_meta_boxes', array(&$this,'authnetpro_tx_data_meta') );
   }



   function authnetpro_tx_data_meta()
   {
      global $post;
      $payment_method = get_post_meta($post->ID,'_payment_method',true);      
      if( $payment_method != 'authorizeaim'){

         return;
      }
      add_meta_box(
         'authnetpro_tx_data_meta',
         'Current Status Of Transaction From Authorize.net',
         array(&$this,'render_authnetpro_tx_data_meta'),
         'shop_order',
         'normal',
         'default'
      );
   }


   function render_authnetpro_tx_data_meta()
   {

      global $post;
      $tx_id = get_post_meta($post->ID,'_transaction_id',true);

      if( ! $tx_id ){
         echo "No Details Found.";
         return;
      }
      $transaction_status = array(
         'authorizedPendingCapture'    => 'Authorized and Pending Capture.',
         'capturedPendingSettlement'   => 'Authorized & Captured. Pending Settlement.',
         'communicationError'          => 'Communication Error.',
         'refundSettledSuccessfully'   => 'Refund Settled Successfully.',
         'refundPendingSettlement'     => 'Refunded, And Pending Settlement.',
         'approvedReview'              => 'Approved Mannual Review.',
         'declined'                    => 'Declined.',
         'couldNotVoid'                => 'Could Not Void - Check Authorize.net account for more details.',
         'expired'                     => 'Expired',
         'generalError'                => 'General Error',
         'failedReview'                => 'Failed Manual Review',
         'settledSuccessfully'         => 'Settled Successfully',
         'settlementError'             => 'Settlement Error',
         'underReview'                 => 'Under Review - Please review from Authorize.net account dashboard.',
         'voided'                      => 'Transaction Voided',
         'FDSPendingReview'            => 'Pending  Mannual Review - Fraud Detection Suite',
         'FDSAuthorizedPendingReview'  => 'Card is Authorized But Pending Mannual Review - Fraud Detection Suite',
         'returnedItem'                => 'Returned Item'
      );

      $wc_auth = new WC_Tech_Authoaim();


      if($wc_auth->mode == 'true'){
         $process_url = $wc_auth->testurl;
      }
      else{
         $process_url = $wc_auth->liveurl;
      }

      $xml = '<getTransactionDetailsRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
      <merchantAuthentication>
      <name>'.$wc_auth->login.'</name>
      <transactionKey>'.$wc_auth->transaction_key.'</transactionKey>
      </merchantAuthentication>
      <transId>'.$tx_id.'</transId>
      </getTransactionDetailsRequest>';

      $headers = array(
         "Content-type: text/xml",
         "Content-length: ". strlen($xml),
         "Connection: close"
      );

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $process_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $data = curl_exec($ch);
      curl_close($ch);

      $respone = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOWARNING);
      echo '<table cellpadding="6" cellspacing="0" style="width:100%;">';
      ?>
      <tr>
         <td style="width:200px">Transaction ID</td>
         <td><?php echo (isset($respone->transaction->transId)) ? $respone->transaction->transId : 'NA';?></td>
      </tr>
      <tr class="tx_alt_row">
         <td style="width:200px">Transaction Status</td>
         <td><?php echo (isset($respone->transaction->transactionStatus)) ? $transaction_status[(string)$respone->transaction->transactionStatus] : 'NA';?></td>
      </tr>
      <tr>
         <td style="width:200px">Transaction Auth Code</td>
         <td><?php echo (isset($respone->transaction->authCode)) ? $respone->transaction->authCode : 'NA';?></td>
      </tr>

      <tr class="tx_alt_row">
         <td style="width:200px">AVS Response Code</td>
         <td><?php echo (isset($respone->transaction->AVSResponse)) ? Indatos_Auth_Payments::get_avs_message( (string)$respone->transaction->AVSResponse) : 'NA';?></td>
      </tr>

      <tr>
         <td>Order ID</td>
         <td><?php echo (isset($respone->transaction->order->invoiceNumber)) ? $respone->transaction->order->invoiceNumber : 'NA';?></td>
      </tr>   

      <tr class="tx_alt_row">
         <td>Order Total or Authorized Amount</td>
         <td>$<?php echo (isset($respone->transaction->authAmount)) ? $respone->transaction->authAmount : 'NA';?></td>
      </tr>
      <tr>
         <td>Shipping Amount</td>
         <td>$<?php echo (isset($respone->transaction->shipping->amount)) ? $respone->transaction->shipping->amount : '0.00';?></td>
      </tr>
      <tr class="tx_alt_row">
         <td>Billing Details:</td>
         <td>
            <ul>
               <li>     Name: <?php echo (isset($respone->transaction->billTo->firstName)) ? $respone->transaction->billTo->firstName : 'NA';?>  <?php echo (isset($respone->transaction->billTo->lastName)) ? $respone->transaction->billTo->lastName : 'NA';?></li> 
               <li> Address: <?php echo (string)((isset($respone->transaction->billTo->address)) ? $respone->transaction->billTo->address : 'NA') ;?><li> 
               <li>  City: <?php echo (isset($respone->transaction->billTo->city)) ? $respone->transaction->billTo->city : 'NA';?><li> 
               <li>  State: <?php echo (isset($respone->transaction->billTo->state)) ? $respone->transaction->billTo->state : 'NA';?><li> 
               <li>  Zip: <?php echo (isset($respone->transaction->billTo->zip)) ? $respone->transaction->billTo->zip : 'NA';?><li> 
               <li>  Country: <?php echo (isset($respone->transaction->billTo->country)) ? $respone->transaction->billTo->country : 'NA';?><li> 
               <li>  Phone: <?php echo (isset($respone->transaction->billTo->phoneNumber)) ? $respone->transaction->billTo->phoneNumber : 'NA';?><li> 
               <li>  Email: <?php echo (string)((isset($respone->transaction->customer->email)) ? $respone->transaction->customer->email : 'NA');?><li> 
            </ul>
         </td>
      </tr>   
      <?php if( isset($respone->transaction->shipTo) ):?>
      <tr>
         <td>Shipping Details:</td>
         <td>
            <ul>
           <li>  Name: <?php echo (isset($respone->transaction->shipTo->firstName)) ? $respone->transaction->shipTo->firstName : 'NA';?>  <?php echo (isset($respone->transaction->shipTo->lastName)) ? $respone->transaction->shipTo->lastName : 'NA';?></li> 

            <li> Address: <?php echo (string)((isset($respone->transaction->shipTo->address)) ? $respone->transaction->shipTo->address : 'NA') ;?></li> 
            <li> City: <?php echo (isset($respone->transaction->shipTo->city)) ? $respone->transaction->shipTo->city : 'NA';?></li> 
            <li> State: <?php echo (isset($respone->transaction->shipTo->state)) ? $respone->transaction->shipTo->state : 'NA';?></li> 
            <li> Zip: <?php echo (isset($respone->transaction->shipTo->zip)) ? $respone->transaction->shipTo->zip : 'NA';?></li> 
            <li> Country: <?php echo (isset($respone->transaction->shipTo->country)) ? $respone->transaction->shipTo->country : 'NA';?></li> 

            </ul>
         </td>
      </tr>   

      <?php endif;?>
      <style>.tx_alt_row{background:#f5f5f5}</style>
      <?php    
            echo  '</table><pre style="display:none;">';
            //  $html =  print_r($respone->transaction, 1);
            $html .= '</pre>';
            echo $html;
         }



}