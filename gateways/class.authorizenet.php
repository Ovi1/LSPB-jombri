<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	09 August 2012
 * @file name	:	gateways/class.authorizenet.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

defined('_JEXEC') or die('Restricted access');

class authorizenet_class {
	
	var $fields = array();
	var $payconfig = array();
	var $details = array();
	
	function authorizenet_class($payconfig, $details){
		$this->authorizenet_url = 'https://secure.authorize.net/gateway/transact.dll';
		$this->payconfig = $payconfig;
		$this->details = $details;
	}
	
	function authorizenetPayment(){
		
		$payconfig   = $this->payconfig;
		$authTestmode 	= $payconfig->authTestmode;
		$authAPILogin 	= $payconfig->authAPILogin;
		$authTransKey	= $payconfig->authTransKey;
	
		$details 	= $this->details;
		$amount 	= $details['amount'];
		$taxrate 	= $details['taxrate'];
		$totamt 	= (float)($amount + $amount * ($taxrate/100));	//the total amount is calculated as authorize.net does not support tax parameter.
		$orderid 	= $details['orderid'];
		$itemname 	= $details['itemname'];
		$item_num 	= $details['item_num'];
		$invoiceNo 	= $details['invoiceNo'];
		$user 	 	= JFactory::getUser($details['user_id']);
	
		$link_status = JRoute::_(JURI::base().'index.php?option=com_jblance&task=membership.returnafterpayment&gateway=authorizenet');
	
		$this->authorizenet_url = ($authTestmode) ? 'https://test.authorize.net/gateway/transact.dll' : 'https://secure.authorize.net/gateway/transact.dll';
		
		require_once(JPATH_SITE.'/components/com_jblance/gateways/includes/authorizenet/AuthorizeNetSIM.php');
		
		$fp_timestamp = time();
		$fp_sequence = $invoiceNo; // Enter an invoice or other unique number.
		$fingerprint = AuthorizeNetSIM_Form::getFingerprint($authAPILogin, $authTransKey, $totamt, $fp_sequence, $fp_timestamp);
		
		$this->add_field('x_login', $authAPILogin);
		$this->add_field('x_fp_hash', $fingerprint);
		$this->add_field('x_description', $itemname);	//this is where the user is returned after payment.
		$this->add_field('x_cust_id', $user->username);
		$this->add_field('x_first_name', $user->name);
		$this->add_field('x_email', $user->email);	// this is were POST data is sent
		$this->add_field('x_email_customer', true);
		$this->add_field('x_amount', $totamt);
		$this->add_field('x_invoice_num', $invoiceNo);
		$this->add_field('x_tax', $taxrate);
		$this->add_field('x_fp_timestamp', $fp_timestamp);
		$this->add_field('x_fp_sequence', $fp_sequence);
		$this->add_field('x_version', '3.1');
		$this->add_field('x_show_form', 'payment_form');
		$this->add_field('x_test_request', false);
		$this->add_field('x_method', 'cc');
		$this->add_field('x_receipt_link_method', 'POST');
		$this->add_field('x_receipt_link_text', 'Return to Merchant Site');
		$this->add_field('x_receipt_link_url', $link_status);
		$this->submit_authorizenet_post(); // auto submit the fields to authorizenet
	?>
	<script>
		document.authorizenet_form.submit();
	</script>
	<?php
	}
	
	function add_field($field, $value){
		$this->fields["$field"] = $value;
	}
	
	function submit_authorizenet_post(){
		echo '<form method="post" name="authorizenet_form" action="'.$this->authorizenet_url.'">';
		foreach ($this->fields as $name => $value){
			echo '<input type="hidden" name="'.$name.'" value="'.$value.'"/>';
		}
		echo JText::sprintf('COM_JBLANCE_PAYMENT_REDIRECTION_PAGE', 'Authorize.Net');
		echo '<br><br><input type="submit" value="'.JText::_('COM_JBLANCE_PROCEED_FOR_PAYMENT').'">';
		echo '</form>';
	}
	
	//15.Return authorizenet
	function authorizenetReturn($data){
	
		if($this->validate_ipn($data)){
			$invoice_num = array_key_exists('x_invoice_num', $data) ? $data['x_invoice_num'] : '';		// get the invoice number from the post variable
			$return['success'] = true;
			$return['invoice_num'] = $invoice_num;
		}
		else
			$return['success'] = false;
		
		return  $return;
	}
	
	function validate_ipn($data){
		
		if($data['x_response_code'] == 1)
			return true;
		else
			return false;
	}
}