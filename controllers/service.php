<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	05 November 2014
 * @file name	:	controllers/service.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 jimport('joomla.application.controller');
 
class JblanceControllerService extends JControllerLegacy {
 
 	function __construct(){
 		parent :: __construct();
 	}
 	
 	//1.Save Service
 	function saveService(){
 		// Check for request forgeries
 		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
 		
 		// Initialize variables
 		$app	= JFactory::getApplication();
 		$user	= JFactory::getUser();
 		$row	= JTable::getInstance('service', 'Table');
 		$post 	= $app->input->post->getArray();
 		$id		= $app->input->get('id', 0, 'int');
 		$now 	= JFactory::getDate();
 		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
 		
 		$config 		= JblanceHelper::getConfig();
 		$reviewServices = $config->reviewServices;
 		
 		$isNew = ($id > 0) ? false : true;
 		
 		if(!$isNew)
 			$row->load($id);
 		
 		// if the service is new, set the create date else set modify date. If the service is new and it does not require approval by admin, set the publish up date.
 		if($isNew){
 			$post['create_date'] = $now->toSql();
 			if(!$reviewServices){
 				$post['publish_up'] = $now->toSql();
 			}
 		}
 		else {
 			$post['modify_date'] = $now->toSql();
 		}
 		
 		//check if the service is to be reviewed by admin. If so, set the approved=0
 		if($reviewServices){
 			$post['approved'] = 0;
 		}
 		
 		$post['user_id'] = $user->id;
 		
 		//process category ids
 		$id_category 	= $app->input->get('id_category', '', 'array');
 		JArrayHelper::toInteger($id_category);
 		if(count($id_category) > 0 && !(count($id_category) == 1 && empty($id_category[0]))){
 			$categ = implode(',', $id_category);
 		}
 		elseif($id_category[0] == 0){
 			$categ = 0;
 		}
 		$post['id_category'] = $categ;
 		
 		//process extra add-ons
 		$extras	= $app->input->get('extras', null, 'array');
 		$registry = new JRegistry();
 		$registry->loadArray($extras);
 		$post['extras'] = $registry->toString();
 		
 		//process service files
 		$serviceFiles	= $app->input->get('serviceFiles', null, 'array');
 		$registry = new JRegistry();
 		$registry->loadArray($serviceFiles);
 		$post['attachment'] = $registry->toString();
 		
 		if($isNew){
 			//deduct charge per service from seller'a account
 			$plan = JblanceHelper::whichPlan($user->id);
 			$chargePerService	 = $plan->flChargePerService;
 			
 			if($chargePerService > 0){
 				$transDtl = JText::_('COM_JBLANCE_CHARGE_PER_SERVICE').' - '.$post['service_title'];
 				JblanceHelper::updateTransaction($user->id, $transDtl, $chargePerService, -1);
 				$msg_debit = JText::sprintf('COM_JBLANCE_YOUR_ACCOUNT_DEBITED_WITH_CURRENCY_FOR_POSTING_SERVICE', JblanceHelper::formatCurrency($chargePerService));
 				$app->enqueueMessage($msg_debit);
 			}
 		}
 		
 		if(!$row->save($post)){
 			JError::raiseError(500, $row->getError());
 		}
 		
 		//send email to admin if the service needs review (everytime the service is saved)
 		if($reviewServices || !$row->approved){
 			$jbmail->sendAdminServicePendingApproval($row->id);
 		}
 		
 		$msg	= JText::_('COM_JBLANCE_SERVICE_SAVED_SUCCESSFULLY').' : '.$row->service_title;
 		$return	= JRoute::_('index.php?option=com_jblance&view=service&layout=myservice', false);
 		$this->setRedirect($return, $msg);
 	}
 	
 	function placeOrder(){
 		// Check for request forgeries
 		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
 		
 		$app  		= JFactory::getApplication();
 		$post 		= $app->input->post->getArray();
 		$row 		= JTable::getInstance('serviceorder', 'Table');
 		$now 		= JFactory::getDate();
 		$user 		= JFactory::getUser();
 		$service_id	= $app->input->get('service_id', 0, 'int');		// id of service
 		$svcHelper 	= JblanceHelper::get('helper.service');		// create an instance of the class ServiceHelper
 		$service 	= JTable::getInstance('service', 'Table');
 		$userType   = JblanceHelper::getUserType($user->id);	//get the current user type
 		$escrow		= JTable::getInstance('escrow', 'Table');
 		
 		$service->load($service_id);
 		$isMine = ($service->user_id == $user->id);
 		
 		// check if the user is guest
 		if($user->guest){
 			//return to same page after login
 			$returnUrl = JRoute::_('index.php?option=com_jblance&view=service&layout=viewservice&id='.$service_id, false);
 			$msg = JText::_('COM_JBLANCE_MUST_BE_LOGGED_IN_TO_ACCESS_THIS_PAGE');
 			$link_login  = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($returnUrl), false);
 			$this->setRedirect($link_login, $msg, 'warning');
 		}
 		// check if the user doesn't have JoomBri Profile - or he is just a Joomla user
 		if($userType->joomlauser){
 			$msg = JText::_('COM_JBLANCE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE_CHOOSE_YOUR_ROLE');
 			$link	= JRoute::_('index.php?option=com_jblance&view=guest&layout=showfront', false);
 			$this->setRedirect($link, $msg, 'error');
 			return false;
 		}
 		
 		// check if the user is a freelancer or service owner
 		if($isMine || !$userType->buyer){
 			$msg = JText::_('COM_JBLANCE_NOT_ALLOWED_TO_ORDER_SERVICE');
 			$link	= JRoute::_('index.php?option=com_jblance&view=service&layout=viewservice&id='.$service_id, false);
 			$this->setRedirect($link, $msg, 'error');
 			return false;
 		}
 		
 		//check if the user's plan is expired or not approved. If so, do not allow him to order service
 		$planStatus = JblanceHelper::planStatus($user->id);
 		if($planStatus == 1 || $planStatus == 2){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_ALLOWED_TO_DO_OPERATION_NO_ACTIVE_SUBSCRIPTION');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$this->setRedirect($link, $msg, 'error');
 			return false;
 		}
 		
 		$post['user_id'] = $user->id;
 		$post['status'] = 'COM_JBLANCE_ACCEPTED';
 		$post['order_date'] = $now->toSql();
 		
 		//process extra add-ons
 		$extras	= $app->input->get('extras', null, 'array');
 		$registry = new JRegistry();
 		$registry->loadArray($extras);
 		$post['extras'] = $registry->toString();
 		
 		//calculate the total amount and duration as they could have been tampered
 		$return = $svcHelper->calculateServiceTotalPrice($service_id, $extras);
 		$post['price'] = $return['totalPrice'];
 		$post['duration'] = $return['totalDuration'];
 		
 		//check if the buyer has enough money to buy the service
 		$totalFund = JblanceHelper::getTotalFund($user->id);
 		if($totalFund < $return['totalPrice']){
 			$msg = JText::sprintf('COM_JBLANCE_BALANCE_INSUFFICIENT_TO_BUY_SERVICE', JblanceHelper::formatCurrency($return['totalPrice']));
 			$link	= JRoute::_('index.php?option=com_jblance&view=membership&layout=depositfund', false);
 			$this->setRedirect($link, $msg, 'error');
 			return false;
 		}
 		
 		if(!$row->save($post)){
 			throw new Exception($row->getError(), 500);
 		}
 		
 		//deduct the amount from buyer and credit the seller after less service fee 
 		$plan 		= JblanceHelper::whichPlan($service->user_id);		//get the plan details of the seller to get the service fee
 		$serviceFee	= $plan->flFeePercentPerService;	//service fee in percent
 		
 		//deduct buyer
 		$transDtl = JText::_('COM_JBLANCE_SERVICE_ORDER').' - '.$service->service_title;
 		$trans_buyer = JblanceHelper::updateTransaction($user->id, $transDtl, $return['totalPrice'], -1);
 		$msg_debit = JText::sprintf('COM_JBLANCE_YOUR_ACCOUNT_DEBITED_WITH_CURRENCY_FOR_BUYING_SERVICE', JblanceHelper::formatCurrency($return['totalPrice']));
 		$app->enqueueMessage($msg_debit);
 		$app->enqueueMessage(JText::_('COM_JBLANCE_RELEASE_PAYMENT_WHEN_SATISFIED'));	
 		
 		//calculate the escrow amount (less service fee)
 		$amountToCredit = $return['totalPrice'] - round((($serviceFee / 100) * $return['totalPrice']), 2);
 		
 		//save to escrow table
 		$escrow->from_id		= $user->id;
 		$escrow->to_id			= $service->user_id;
 		$escrow->date_transfer	= $now->toSql();
 		$escrow->note			= '';
 		$escrow->amount			= $amountToCredit;
 		$escrow->project_id		= $row->id;		//this has to be service order id instead of service id. Changed since v1.7
 		$escrow->type			= 'COM_JBLANCE_SERVICE';
 		$escrow->from_trans_id	= $trans_buyer->id;
 		
 		if(!$escrow->store()){
 			JError::raiseError(500, $escrow->getError());
 		}
 		$escrow->checkin();
 		
 		//send service order notification to seller
 		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
 		$jbmail->sendServiceOrderNotification($row->id, $service_id);
 		
 		$msg	= JText::_('COM_JBLANCE_SERVICE_ORDER_PLACED_SUCCESSFULLY').' : '.$service->service_title;
 		$return	= JRoute::_('index.php?option=com_jblance&view=service&layout=servicebought', false);
 		$this->setRedirect($return, $msg, 'message');
 	}
 	
 	function saveRateUser(){
 		// Check for request forgeries
 		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
 	
 		$app  		= JFactory::getApplication();
 		$post 		= $app->input->post->getArray();
 		//$id 		= $app->input->get('id', 0, 'int');
 		$rate_type 	= $app->input->get('rate_type', '', 'string');
 		$now = JFactory::getDate();
 	
 		$row = JTable::getInstance('rating', 'Table');
 		//$row->load($id);
 		
 		$post['rate_date'] = $now->toSql();
 	
 		if(!$row->save($post))
 			JError::raiseError(500, $row->getError());
 	
 		$msg = JText::_('COM_JBLANCE_USER_RATING_SAVED_SUCCESSFULLY');
 	
 		/*	If I rate a buyer, I'm a Freelancer. Hence direct me to myservice page.
 		 * 	If I rate a freelancer, I'm a buyer. Hence direct me to servicebought page.
 		*/
 	
 		if($rate_type == 'COM_JBLANCE_BUYER')
 			$return	= JRoute::_('index.php?option=com_jblance&view=service&layout=myservice', false);
 		elseif($rate_type == 'COM_JBLANCE_FREELANCER')
 			$return	= JRoute::_('index.php?option=com_jblance&view=service&layout=servicebought', false);
 		else
 			$return	= JRoute::_('index.php?option=com_jblance&view=service&layout=listservice', false);
 	
 		/* //Trigger the plugin event to feed the activity - buyer pick freelancer
 		JPluginHelper::importPlugin('joombri');
 		$dispatcher = JDispatcher::getInstance();
 		$dispatcher->trigger('onUserRating', array($row->actor, $row->target, $row->project_id)); */
 	
 		$this->setRedirect($return, $msg);
 	}
 	
 	/* Misc Functions */
 	
 	function updateProgress(){
 		// Check for request forgeries
 		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
 		
 		$app  		= JFactory::getApplication();
 		$post 		= $app->input->post->getArray();//print_r($post);exit;
 		$now		= JFactory::getDate();
 		$order 		= JTable::getInstance('serviceorder', 'Table');
 		$orderId	= $app->input->get('id', 0, 'int');
 		$status		= $app->input->get('p_status', '', 'string');
 		$statusInit	= $app->input->get('status_initiated', 0, 'boolean');
 		
 		if($statusInit)
 			$status = 'COM_JBLANCE_INITIATED';
 		
 		$order->load($orderId);
 		
 		$order->p_status = $status;
 		$order->p_percent = $post['p_percent'];
 		$order->p_updated = $now->toSql();
 		
 		
 		//set the started date only for the first time
 		if($status == 'COM_JBLANCE_INITIATED'){
 			if($order->p_started == '0000-00-00 00:00:00'){
 				$order->p_started = $now->toSql();
 			}
 		}
 		elseif($status == 'COM_JBLANCE_COMPLETED'){
 			if($order->p_ended == '0000-00-00 00:00:00'){
 				$order->p_ended = $now->toSql();
 			}
 		}
 		
 		if(!$order->store()){
 			JError::raiseError(500, $order->getError());
 		}
 		$order->checkin();
 		
 		//send service progress notification to buyer
 		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
 		if(!empty($status))
 			$jbmail->sendServiceProgressNotification($orderId, $order->service_id);
 		
 		$msg	= JText::_('COM_JBLANCE_SERVICE_PROGRESS_UPDATED_SUCCESSFULLY');
 		$return	= JRoute::_('index.php?option=com_jblance&view=service&layout=serviceprogress&id='.$orderId, false);
 		$this->setRedirect($return, $msg, 'message');
 	}
 	
 	function serviceuploadfile(){
 		// Check for request forgeries
 		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
 	
 		JBMediaHelper::serviceuploadfile();
 	}
 	
 	function removeServiceFile(){
 		// Check for request forgeries
 		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
 	
 		JBMediaHelper::removeServiceFile();
 	}
 	
 	
}
 	