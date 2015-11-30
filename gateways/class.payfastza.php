<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	22 March 2012
 * @file name	:	gateways/class.payfastza.php
 * @copyright   :	Copyright (C) 2012 - 2013 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

defined('_JEXEC') or die('Restricted access');

class payfastza_class {
	var $payfastza_url = '';
	var $fields = array();
	var $payconfig = array();
	var $details = array();
	
	function payfastza_class($payconfig, $details){
		$this->payfastza_url = 'https://www.payfast.co.za/eng/process';
		$this->payconfig = $payconfig;
		$this->details = $details;
	}
	
	function payfastzaPayment(){
	
		$payconfig   	= $this->payconfig;
		$merchant_id 	= $payconfig->payfastzaMerchantId;
		$merchant_key  	= $payconfig->payfastzaMerchantKey;
		$testmode  		= $payconfig->payfastTestmode;
	
		$details	= $this->details;
		$amount 	= $details['amount'];
		$taxrate 	= $details['taxrate'];
		$totamt 	= (float)($amount + $amount * ($taxrate/100));	$totamt = round($totamt, 2);	
		$orderid 	= $details['orderid'];
		$itemname 	= $details['itemname'];
		$item_num 	= $details['item_num'];
		$invoiceNo 	= $details['invoiceNo'];
		$user 	 	= JFactory::getUser($details['user_id']);
	
		$link_status = JURI::base().'index.php?option=com_jblance&task=membership.returnafterpayment&gateway=payfastza';
		$link_cancel = JRoute::_(JURI::base().'index.php?option=com_jblance&view=membership&layout=thankpayment&type=cancel');
		
		$this->payfastza_url = ($testmode) ? 'https://sandbox.payfast.co.za/eng/process' : 'https://www.payfast.co.za/eng/process';
	
		$this->add_field('merchant_id', $merchant_id);
		$this->add_field('merchant_key', $merchant_key);
		$this->add_field('return_url', $link_status);
		$this->add_field('cancel_url', $link_cancel);
		$this->add_field('notify_url', $link_status);
		$this->add_field('name_first', $user->name);
		$this->add_field('email_address', $user->email);
		$this->add_field('m_payment_id', $invoiceNo);
		$this->add_field('amount', $totamt);
		$this->add_field('item_name', $itemname);
		$this->add_field('signature', '');
		$this->submit_payfastza_post(); // auto submit the fields to payfastza
		?>
		
		
	<script>
		document.payfastza_form.submit();
	</script>
	<?php
	}
   
	function add_field($field, $value){
		$this->fields["$field"] = $value;
	}

	function submit_payfastza_post(){
		echo '<form method="post" name="payfastza_form" action="'.$this->payfastza_url.'">';
		foreach ($this->fields as $name => $value){
			echo '<input type="hidden" name="'.$name.'" value="'.$value.'"/>';
		}
		echo JText::sprintf('COM_JBLANCE_PAYMENT_REDIRECTION_PAGE', 'PayFast');
		echo '<br><br><input type="submit" value="'.JText::_('COM_JBLANCE_PROCEED_FOR_PAYMENT').'">';
		echo '</form>';
	}
	
	
	//15.Return PayFast
	function payfastzaReturn($data){
		
		$payconfig  = $this->payconfig;
		$testmode  	= $payconfig->payfastTestmode;
		$this->payfastza_url = ($testmode) ? 'https://sandbox.payfast.co.za/eng/process' : 'https://www.payfast.co.za/eng/process';
		$return = array();

		$response = $this->validate_ipn($data);
		if($response['payment_status'] == 'COMPLETE'){
			$invoice_num = array_key_exists('m_payment_id', $response) ? $response['m_payment_id'] : '';	// get the invoice number from the post variable
			$return['success'] = true;
			$return['invoice_num'] = $invoice_num;
		}
		else 
			$return['success'] = false;
		
		return $return;
	}
   
	function validate_ipn($data){
		
		$payconfig  = $this->payconfig;
		$testmode  	= $payconfig->payfastTestmode;
		
		// Variable Initialization
		$pmtToken = isset( $data['pt'] ) ? $data['pt'] : null;
		
		if(!empty($pmtToken)){
			// Variable Initialization
			$error = false;
			$authToken = $payconfig->payfastzaAuthToken;//'0a1e2e10-03a7-4928-af8a-fbdfdfe31d43';
			$req = 'pt='. $pmtToken .'&at='. $authToken;
			$response = array();
			$host = $testmode ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
		
			//// Connect to server
			if(!$error){
				// Construct Header
				$header = "POST /eng/query/fetch HTTP/1.0\r\n";
				$header .= 'Host: '. $host ."\r\n";
				$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$header .= 'Content-Length: '. strlen( $req ) ."\r\n\r\n";
		
				// Connect to server
				$socket = fsockopen('ssl://'. $host, 443, $errno, $errstr, 10);
		
				if(!$socket){
					$error = true;
					print('errno = '. $errno .', errstr = '. $errstr);
				}
			}
		
			// Get data from server
			if(!$error){
				// Send command to server
				fputs($socket, $header . $req);
		
				// Read the response from the server
				$res = '';
				$headerDone = false;
		
				while(!feof($socket)){
					$line = fgets($socket, 1024);
		
					// Check if we are finished reading the header yet
					if(strcmp( $line, "\r\n" ) == 0){
						// read the header
						$headerDone = true;
					}
					// If header has been processed
					else if($headerDone){
						// Read the main response
						$res .= $line;
					}
				}
		
				// Parse the returned data
				$lines = explode("\n", $res);
			}
		
			//// Interpret the response from server
			if(!$error){
				$result = trim($lines[0]);
		
				// If the transaction was successful
				if(strcmp( $result, 'SUCCESS' ) == 0){
					// Process the reponse into an associative array of data
					for($i = 1; $i < count( $lines ); $i++){
						list( $key, $val ) = explode("=", $lines[$i]);
						$response[urldecode( $key )] = stripslashes(urldecode($val));
					}
				}
				// If the transaction was NOT successful
				else if(strcmp($result, 'FAIL') == 0){
					// Log for investigation
					$error = true;
				}
			}
			// Close socket if successfully opened
			if($socket)
				fclose($socket);
		}
		return $response;
	}
}

?>