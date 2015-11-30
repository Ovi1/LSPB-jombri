<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	09 August 2012
 * @file name	:	gateways/class.twocheckout.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

defined('_JEXEC') or die('Restricted access');

class twocheckout_class {
	
	var $fields = array();
	var $payconfig = array();
	var $details = array();
	
	function twocheckout_class($payconfig, $details){
		$this->twocheckout_url = 'https://www.2checkout.com/checkout/purchase';
		$this->payconfig = $payconfig;
		$this->details = $details;
	}
	
	function twocheckoutPayment(){
		
		$app  	= JFactory::getApplication();
		$buy  	= $app->input->get('buy', '', 'string');	//either buy deposit or plan
		//$user 	 = JFactory::getUser();
	
		$payconfig   = $this->payconfig;
		$twocoTestmode 	= $payconfig->twocoTestmode;
		$twocoVendorID	= $payconfig->twocoVendorID;
	
		$details 	= $this->details;
		$amount 	= $details['amount'];
		$taxrate 	= $details['taxrate'];
		$totamt 	= (float)($amount + $amount * ($taxrate/100));	$totamt = round($totamt, 2);	//the total amount is calculated as authorize.net does not support tax parameter.
		$orderid 	= $details['orderid'];
		$itemname 	= $details['itemname'];
		$item_num 	= $details['item_num'];
		$invoiceNo 	= $details['invoiceNo'];
		$user 	 	= JFactory::getUser($details['user_id']);
	
		$link_return = JRoute::_(JURI::base().'index.php?option=com_jblance&view=membership&layout=thankpayment&oid='.$orderid.'&buy='.$buy);
		$link_status = JRoute::_(JURI::base().'index.php?option=com_jblance&task=membership.returnafterpayment&gateway=twocheckout');
	
		$this->twocheckout_url = 'https://www.2checkout.com/checkout/purchase';
		
		$this->add_field('sid', $twocoVendorID);
		$this->add_field('cart_order_id', $invoiceNo);
		$this->add_field('total', $totamt);
		$this->add_field('c_prod_1', $item_num);
		$this->add_field('c_name_1', $itemname);
		$this->add_field('c_description_1', $itemname);	
		$this->add_field('c_price_1', true);
		
		if($twocoTestmode)
			$this->add_field('demo', 'Y');
		
		$this->add_field('return_url', $link_return);
		$this->add_field('merchant_order_id', $invoiceNo);
		$this->add_field('skip_landing', 1);
		$this->add_field('x_receipt_link_url', $link_status);
		$this->add_field('card_holder_name', $user->name);
		$this->add_field('email', $user->email);
		$this->add_field('id_type', 1);
		$this->add_field('pay_method', 'CC');
		$this->add_field('buy', $buy);
		$this->add_field('oid', $orderid);
		$this->add_field('gateway', 'twocheckout');
		$this->submit_twocheckout_post(); // auto submit the fields to 2Checkout
	
	?>
	<script>
		document.twocheckout_form.submit();
	</script>
	<?php
	}
	
	function add_field($field, $value){
		$this->fields["$field"] = $value;
	}
	
	function submit_twocheckout_post(){
		echo '<form method="post" name="twocheckout_form" action="'.$this->twocheckout_url.'">';
		foreach ($this->fields as $name => $value){
			echo '<input type="hidden" name="'.$name.'" value="'.$value.'"/>';
		}
		echo JText::sprintf('COM_JBLANCE_PAYMENT_REDIRECTION_PAGE', '2Checkout');
		echo '<br><br><input type="submit" value="'.JText::_('COM_JBLANCE_PROCEED_FOR_PAYMENT').'">';
		echo '</form>';
	}
	
	//15.Return 2Checkout
	function twocheckoutReturn($data){
	
		if($this->validate_ipn($data)){
			$invoice_num = array_key_exists('cart_order_id', $data) ? $data['cart_order_id'] : '';		// get the invoice number from the post variable
			$return['success'] = true;
			$return['invoice_num'] = $invoice_num;
		}
		else
			$return['success'] = false;
		
		return $return;
	}
	
	function validate_ipn($data){
		
		$payconfig  	 = $this->payconfig;
		$twocoTestmode 	 = $payconfig->twocoTestmode;
		$twocoVendorID	 = $payconfig->twocoVendorID;
		$twocoSecretword = $payconfig->twocoSecretword;
		
		//calculate md5 hash to compare with the post 'key' - md5 ( secret word + vendor number + order number + total )
		if($twocoTestmode)
			$string_to_hash	= $twocoSecretword.$twocoVendorID.'1'.$data['total'];
		else
			$string_to_hash	= $twocoSecretword.$twocoVendorID.$data['order_number'].$data['total'];
		
		$check_key = strtoupper(md5($string_to_hash));
		
		if($check_key ==  $data['key']){
			return true;
		}
		else {
			return false;
		}
	}
}