<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	08 August 2012
 * @file name	:	gateways/class.moneybookers.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

defined('_JEXEC') or die('Restricted access');

class moneybookers_class {
	
	var $fields = array();
	var $payconfig = array();
	var $details = array();
	
	function moneybookers_class($payconfig, $details){
		$this->moneybookers_url = 'https://www.moneybookers.com/app/payment.pl';
		$this->payconfig = $payconfig;
		$this->details = $details;
	}
	
	function moneybookersPayment(){
		$app  	= JFactory::getApplication();
		$buy  	= $app->input->get('buy', '', 'string');	//either buy deposit or plan
	
		$payconfig   = $this->payconfig;
		$paytoEmail  = $payconfig->mbPaymentEmail;
		$mbCurrency = $payconfig->mbCurrency;
	
		$details = $this->details;
		$amount = $details['amount'];
		$taxrate = $details['taxrate'];
		$totamt = (float)($amount + $amount * ($taxrate/100));	//the total amount is calculated as moneybookers does not support tax parameter.
		$orderid = $details['orderid'];
		$itemname = $details['itemname'];
		$item_num = $details['item_num'];
		$invoiceNo = $details['invoiceNo'];
	
		$link_return = JRoute::_(JURI::base().'index.php?option=com_jblance&view=membership&layout=thankpayment&oid='.$orderid.'&buy='.$buy);
		$link_status = JRoute::_(JURI::base().'index.php?option=com_jblance&task=membership.returnafterpayment&gateway=moneybookers');
	
		$this->add_field('pay_to_email', $paytoEmail);
		$this->add_field('transaction_id', $invoiceNo);
		$this->add_field('return_url', $link_return);	//this is where the user is returned after payment.
		$this->add_field('return_url_target', 1);
		$this->add_field('cancel_url_target', 1);
		$this->add_field('status_url', $link_status);	// this is were POST data is sent
		$this->add_field('status_url2', $paytoEmail);
		$this->add_field('dynamic_descriptor', 'Freelance');
		$this->add_field('language', 'EN');
		$this->add_field('confirmation_note', 'We wish you have pleasure visiting our site again!');
		$this->add_field('amount', $totamt);
		$this->add_field('currency', $mbCurrency);
		$this->add_field('detail1_description', 'Item Name:');
		$this->add_field('detail1_text', $itemname);
		$this->add_field('rec_period', 1);
		$this->add_field('rec_grace_period', 1);
		$this->add_field('rec_cycle', 'day');
		$this->add_field('ondemand_max_currency', 'EUR');
		$this->submit_moneybookers_post(); // auto submit the fields to moneybookers
	?>
	<script>
		document.moneybookers_form.submit();
	</script>
	<?php
	}
	
	function add_field($field, $value){
		$this->fields["$field"] = $value;
	}
	
	function submit_moneybookers_post(){
		echo '<form method="post" name="moneybookers_form" action="'.$this->moneybookers_url.'">';
		foreach ($this->fields as $name => $value){
			echo '<input type="hidden" name="'.$name.'" value="'.$value.'"/>';
		}
		echo JText::sprintf('COM_JBLANCE_PAYMENT_REDIRECTION_PAGE', 'Moneybookers');
		echo '<br><br><input type="submit" value="'.JText::_('COM_JBLANCE_PROCEED_FOR_PAYMENT').'">';
		echo '</form>';
	}
	
	//15.Return moneybookers
	function moneybookersReturn($data){
	
		if($this->validate_ipn($data)){
			$invoice_num = array_key_exists('transaction_id', $data) ? $data['transaction_id'] : '';		// get the invoice number from the post variable
			$return['success'] = true;
			$return['invoice_num'] = $invoice_num;
		}
		else
			$return['success'] = false;
		
		return $return;
	}
	
	function validate_ipn($data){
		
		$payconfig  = $this->payconfig;
		$mbSecret	= $payconfig->mbSecret;
		
		$md5sig = md5( $data['merchant_id'].$data['transaction_id'].strtoupper(md5($mbSecret)).$data['mb_amount'].$data['mb_currency'].$data['status'] );
		$md5sig =  strtoupper($md5sig);
		
		if(($data['status'] == '2') && ($md5sig == $data['md5sig'])){
			return true;
		}
		else {
			return false;
		}
	}
}