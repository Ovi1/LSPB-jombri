<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	16 March 2012
 * @file name	:	models/guest.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 jimport('joomla.application.component.model');
 
 class JblanceModelMembership extends JModelLegacy {
 	
 	//17.Subscribe to a Plan
	function getPlanAdd(){
		$app = JFactory::getApplication();
		$db	= JFactory::getDbo();
		$user 	= JFactory::getUser();
		
		//get the user group id from the session
		$session = JFactory::getSession();
		$ugid = $session->get('ugid', 0, 'register');
		
		if(empty($ugid)){
			$jbuser = JblanceHelper::get('helper.user');
			$ugroup = $jbuser->getUserGroupInfo($user->id, null);
			$ugid = $ugroup->id;
			
			//if user group is empty, the user is not subscribed to any JoomBri group. So direct him to usergroup selection page
			if(empty($ugid)){
				$msg = JText::_('COM_JBLANCE_PLEASE_SELECT_USERGROUP_BEFORE_CONTINUE');
				$return	= JRoute::_('index.php?option=com_jblance&view=guest&layout=showfront', false);
				$app->enqueueMessage($msg, 'warning');
				$app->redirect($return);
			}
		}
    
	  	$query = "SELECT p.id AS planid, SUM(s.access_count) plan_count FROM #__jblance_plan p ".
			     "LEFT JOIN #__jblance_plan_subscr s ON s.plan_id = p.id ".
			     "WHERE s.approved=1 AND s.user_id=".$db->quote($user->id)." ".
			   	 "GROUP BY p.id";
	    $db->setQuery($query);
	   	$plans = $db->loadObjectList('planid');
	    
	    $query = "SELECT p.* FROM #__jblance_plan AS p ".
			     "WHERE p.ug_id=".$db->quote($ugid)." AND p.published = 1 and p.invisible = 0 ".
			     "ORDER BY p.ordering ASC";
	    $db->setQuery($query);
	    $rows = $db->loadObjectList();
	    
		$return[0] = $rows;
		$return[1] = $plans;
		return $return;
	}
	
	//16.History of plans Subscribed to
	function getPlanHistory(){
		$app 	= JFactory::getApplication();
		$user 	= JFactory::getUser();
		$db		= JFactory::getDbo();
		$subid 	= $app->input->get('subid', 0, 'int');
		
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart	= $app->input->get('limitstart', 0, 'int');
	
		$query = "SELECT s.*,p.name,p.id plan_id,(TO_DAYS(s.date_expire) - TO_DAYS(NOW())) AS daysleft ".
				 "FROM #__jblance_plan_subscr AS s ".
				 "LEFT JOIN #__jblance_plan AS p ON p.id = s.plan_id ".
				 "WHERE s.user_id = ".$db->quote($user->id)." AND p.published = 1 ".
				 "ORDER BY s.id DESC";
		$db->setQuery($query);
		$db->execute();
		$total = $db->getNumRows();
		
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
		
		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$rows = $db->loadObjectList();
	
		$finish = '';
		if($subid > 0){
			$query = "SELECT finish_msg FROM #__jblance_plan WHERE id = ".$db->quote($subid);
			$db->setQuery($query);
			$finish = $db->loadResult();
		}
		$return[0] = $rows;
		$return[1] = $finish;
		$return[2] = $pageNav;
		return $return;
	}
	
	//20.Subscription Checkout
	function getPlanCheckout(){
		$app  = JFactory::getApplication();
		//get the subscription id from the session
		$session = JFactory::getSession();
		$id = $session->get('id', 0, 'upgsubscr');
	
		//the subscr id is not set in session, get from the 'GET' request
		if(($id == 0))
			$id = $app->input->get('id', 0, 'int');
	
		$subscr	= JTable::getInstance('plansubscr', 'Table');
		$subscr->load($id);
	
		$plan	= JTable::getInstance('plan', 'Table');
		$plan->load($subscr->plan_id);
	
		$return[0] = $subscr;
		$return[1] = $plan;
	
		return $return;
	}
	
	//20.Deposit Checkout
	function getDepositCheckout(){
		$app = JFactory::getApplication();
		$id  = $app->input->get('id', 0, 'int');
		
		$deposit	= JTable::getInstance('deposit', 'Table');
		$deposit->load($id);	
		
		$return[0] = $deposit;
		return $return;
	}
	
	//19.Plan Bank Transfer
	function getPlanBankTransfer(){
	
		$app = JFactory::getApplication();
		$id  = $app->input->get('id', 0, 'int');
		$subscr	= JTable::getInstance('plansubscr', 'Table');
		$subscr->load($id);
	
		$plan	= JTable::getInstance('plan', 'Table');
		$plan->load($subscr->plan_id);
	
		$payconfig = JblanceHelper::getPaymodeInfo('banktransfer');
	
		$return[0] = $subscr;
		$return[1] = $plan;
		$return[2] = $payconfig;
	
		return $return;
	}
	//19.Bank Transfer
	function getDepositBankTransfer(){
	
		$app = JFactory::getApplication();
		$id  = $app->input->get('id', 0, 'int');
		$deposit	= JTable::getInstance('deposit', 'Table');
		$deposit->load($id);
	
		$payconfig = JblanceHelper::getPaymodeInfo('banktransfer');
	
		$return[0] = $deposit;
		$return[1] = $payconfig;
	
		return $return;
	}
	
	//11.Show Balance
	function getTransaction(){
		$app = JFactory::getApplication();
		$user	= JFactory::getUser();
		$db		= JFactory::getDbo();
	
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart	= $app->input->get('limitstart', 0, 'int');
	
		$query = "SELECT MAX(id) FROM #__jblance_transaction WHERE user_id = ".$db->quote($user->id);
		$db->setQuery($query);
		$id_max = $db->loadResult();
	
		$last_trans = JTable::getInstance('transaction', 'Table');
		$last_trans->load($id_max);
		
		
		$query = "SELECT * FROM #__jblance_transaction ".
				 "WHERE user_id =".$db->quote($user->id)." ORDER BY date_trans DESC";
		$db->setQuery($query);
		$db->execute();
		$total = $db->getNumRows();
		
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
		
		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$rows = $db->loadObjectList();
		
		$return[0] = $rows;
		$return[1] = $pageNav;
		$return[2] = $last_trans;
		return $return;
	}
	
	function getWithdrawFund(){
		
		$app  = JFactory::getApplication();
		$db = JFactory::getDbo();
		$gateway = $app->input->get('gateway', '', 'string');
		
		$query = "SELECT * FROM #__jblance_paymode ".
				 "WHERE published=1 AND withdraw=1 ".
				 "ORDER BY ordering";
		$db->setQuery($query);
		$paymodes = $db->loadObjectList();
		
		// get the JForm object
		jimport('joomla.form.form');
		$pathToGatewayXML = JPATH_COMPONENT_SITE."/gateways/forms/$gateway.xml";
		if(file_exists($pathToGatewayXML)){
			$form = JForm::getInstance($gateway, $pathToGatewayXML, array('control' => 'params', 'load_data' => false));
		}
		else
			$form = null;
		
		$return[0] = $paymodes;
		$return[1] = $form;
		return $return;
	}
	
	function getEscrow(){
		$db = JFactory::getDbo();
		$user	= JFactory::getUser();
		
		$query = "SELECT id AS value, project_title AS text FROM #__jblance_project ".
				 "WHERE publisher_userid=".$db->quote($user->id)." AND status=".$db->quote('COM_JBLANCE_CLOSED')." AND paid_status <> ".$db->quote('COM_JBLANCE_PYMT_COMPLETE');
		$db->setQuery($query);
		$projects = $db->loadObjectList();
		
		if(empty($projects)){
			$lists = JText::_('COM_JBLANCE_NO_PROJECTS_WITH_CHOSEN_WINNERS');
		}
		else {
			$types[] = JHtml::_('select.option', '', '- '.JText::_('COM_JBLANCE_PLEASE_SELECT').' -');
			foreach($projects as $item){
				$types[] = JHtml::_('select.option', $item->value, $item->text);
			}
			$lists 	= JHtml::_('select.genericlist', $types, 'project_id', "class='span4 required' size='8' onclick='fillProjectInfo();'", 'value', 'text', '');
		}

		$return[0] = $lists;
		return $return;
	}
	
	function getManagepay(){
		$app 	= JFactory::getApplication();
		$db 	= JFactory::getDbo();
		$user 	= JFactory::getUser();
		jimport('joomla.html.pagination');
		
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart	= $app->input->get('limitstart', 0, 'int');
		
		$query = "SELECT e.*,COALESCE(p.project_title, s.service_title) title FROM #__jblance_escrow e ".
				 "LEFT JOIN #__jblance_project p ON (p.id=e.project_id AND e.type = 'COM_JBLANCE_PROJECT') ".
				 "LEFT JOIN #__jblance_service_order so ON (so.id=e.project_id AND e.type = 'COM_JBLANCE_SERVICE') ".
				 "LEFT JOIN #__jblance_service s ON (s.id=so.service_id) ".
				 "WHERE from_id= ".$db->quote($user->id)." ".
				 "ORDER BY id DESC";
		$db->setQuery($query);
		$escrow_out = $db->loadObjectList();
		
		$query = "SELECT e.*,COALESCE(p.project_title, s.service_title) title FROM #__jblance_escrow e ".
				 "LEFT JOIN #__jblance_project p ON (p.id=e.project_id AND e.type = 'COM_JBLANCE_PROJECT') ".
				 "LEFT JOIN #__jblance_service_order so ON (so.id=e.project_id AND e.type = 'COM_JBLANCE_SERVICE') ".
				 "LEFT JOIN #__jblance_service s ON (s.id=so.service_id) ".
				 "WHERE to_id= ".$db->quote($user->id)." ".
				 "ORDER BY id DESC";
		$db->setQuery($query);
		$escrow_in = $db->loadObjectList();
		
		$query = "SELECT * FROM #__jblance_withdraw WHERE user_id= ".$db->quote($user->id)." ORDER BY approved, date_withdraw DESC";
		$db->setQuery($query);
		$db->execute();
		$total = $db->getNumRows();
		$pageNavWithdraw = new JPagination($total, $limitstart, $limit);
		$db->setQuery($query, $pageNavWithdraw->limitstart, $pageNavWithdraw->limit);
		$withdraws = $db->loadObjectList();
		
		$query = "SELECT * FROM #__jblance_deposit WHERE user_id= ".$db->quote($user->id)." ORDER BY approved, date_deposit DESC";
		$db->setQuery($query);
		$db->execute();
		$total = $db->getNumRows();
		$pageNavDeposit = new JPagination($total, $limitstart, $limit);
		$db->setQuery($query, $pageNavDeposit->limitstart, $pageNavDeposit->limit);
		$deposits = $db->loadObjectList();
		
		$return[0] = $escrow_out;
		$return[1] = $escrow_in;
		$return[2] = $withdraws;
		$return[3] = $deposits;
		$return[4] = $pageNavWithdraw;
		$return[5] = $pageNavDeposit;
		return $return;
	}
	
	function getInvoice(){
		$app 	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$id 	= $app->input->get('id', 0, 'int');
		$type 	= $app->input->get('type', '', 'string');
		$db 	= JFactory::getDbo();
		$escrows = '';
		
		if($type == 'plan'){
			$query = "SELECT ps.*,ps.date_buy AS invoiceDate,p.name AS planname,u.email,u.name,ju.biz_name,ju.address,ju.id_location,ju.postcode FROM #__jblance_plan_subscr ps ".
					 "LEFT JOIN #__users u ON ps.user_id=u.id ".
					 "LEFT JOIN #__jblance_plan p ON p.id=ps.plan_id ".
					 "LEFT JOIN #__jblance_user ju ON ju.user_id=ps.user_id ".
					 "WHERE ps.id=".$db->quote($id)." AND ps.user_id=".$db->quote($user->id);
		}
		elseif($type == 'deposit'){
			$query = "SELECT d.*,d.date_deposit AS invoiceDate,u.email,u.name,ju.biz_name,ju.address,ju.id_location,ju.postcode FROM #__jblance_deposit d ".
					 "LEFT JOIN #__users u ON d.user_id=u.id ".
					 "LEFT JOIN #__jblance_user ju ON ju.user_id=d.user_id ".
					 "WHERE d.id=".$db->quote($id)." AND d.user_id=".$db->quote($user->id);
		}
		elseif($type == 'withdraw'){
			$query = "SELECT w.*,w.date_withdraw AS invoiceDate,u.email,u.name,ju.biz_name,ju.address,ju.id_location,ju.postcode FROM #__jblance_withdraw w ".
					 "LEFT JOIN #__users u ON w.user_id=u.id ".
					 "LEFT JOIN #__jblance_user ju ON ju.user_id=w.user_id ".
					 "WHERE w.id=".$db->quote($id)." AND w.user_id=".$db->quote($user->id);
		}
		elseif($type == 'project'){
			$usertype 	= $app->input->get('usertype', '', 'string');
			if($usertype == 'freelancer'){
				$query = "SELECT p.*, p.accept_date AS invoiceDate,p.lancer_commission AS commission_amount,u.email,u.name,ju.biz_name,ju.address,ju.id_location,ju.postcode FROM #__jblance_project p ".
						 "LEFT JOIN #__users u ON p.assigned_userid=u.id ".
						 "LEFT JOIN #__jblance_user ju ON ju.user_id=p.assigned_userid ".
						 "WHERE p.id=".$db->quote($id)." AND p.assigned_userid=".$db->quote($user->id);
			}
			elseif($usertype == 'buyer'){
				$query = "SELECT p.*, p.accept_date AS invoiceDate,p.buyer_commission AS commission_amount,u.email,u.name,ju.biz_name,ju.address,ju.id_location,ju.postcode FROM #__jblance_project p ".
						 "LEFT JOIN #__users u ON p.publisher_userid=u.id ".
						 "LEFT JOIN #__jblance_user ju ON ju.user_id=p.publisher_userid ".
						 "WHERE p.id=".$db->quote($id)." AND p.publisher_userid=".$db->quote($user->id);
			}
			//get the escrow payment made for this project
			$queryEscrow = 	"SELECT e.*,p.project_type FROM #__jblance_escrow e ".
							"LEFT JOIN #__jblance_project p ON p.id=e.project_id ".
					 		"WHERE e.status=".$db->quote('COM_JBLANCE_ACCEPTED')." AND e.project_id=".$db->quote($id);//echo $queryEscrow;
			$db->setQuery($queryEscrow);
			$escrows = $db->loadObjectList();
		}
		elseif($type == 'service'){
			$usertype 	= $app->input->get('usertype', '', 'string');
			if($usertype == 'freelancer'){
			$query = "SELECT so.*,s.service_title,so.order_date AS invoiceDate,u.email,u.name,ju.biz_name,ju.address,ju.id_location,ju.postcode FROM #__jblance_service_order so ".
					 "LEFT JOIN #__jblance_service s ON s.id=so.service_id ".
					 "LEFT JOIN #__users u ON so.user_id=u.id ".
					 "LEFT JOIN #__jblance_user ju ON ju.user_id=so.user_id ".
					 "WHERE so.id=".$db->quote($id)." AND s.user_id=".$db->quote($user->id);
			}
			elseif($usertype == 'buyer'){
			$query = "SELECT so.*,s.service_title,so.order_date AS invoiceDate,u.email,u.name,ju.biz_name,ju.address,ju.id_location,ju.postcode FROM #__jblance_service_order so ".
					 "LEFT JOIN #__jblance_service s ON s.id=so.service_id ".
					 "LEFT JOIN #__users u ON so.user_id=u.id ".
					 "LEFT JOIN #__jblance_user ju ON ju.user_id=so.user_id ".
					 "WHERE so.id=".$db->quote($id)." AND so.user_id=".$db->quote($user->id);
			}
			//get the escrow payment made for this service
			$queryEscrow = "SELECT e.* FROM #__jblance_escrow e  ".
						   "LEFT JOIN #__jblance_service_order so ON so.id=e.project_id ".
						   "WHERE e.status=".$db->quote('COM_JBLANCE_ACCEPTED')." AND e.project_id=".$db->quote($id)." AND e.type='COM_JBLANCE_SERVICE'";//echo $queryEscrow;
			$db->setQuery($queryEscrow);
			$escrows = $db->loadObjectList();
		}
		
		$db->setQuery($query);//echo $query;
		$row = $db->loadObject();
		
		$return[0] = $row;
		$return[1] = $escrows;
		//$return[1] = $billingAddress;
		return $return;
	}
	
	function getPlanDetail(){
		$app = JFactory::getApplication();
		$db	 = JFactory::getDbo();
		$id  = $app->input->get('id', 0, 'int');
		
		$query = "SELECT s.*,p.id planid,p.name FROM #__jblance_plan_subscr s ".
				 "JOIN #__jblance_plan p ON p.id=s.plan_id ".
				 "WHERE s.id=".$db->quote($id);
		$db->setQuery($query);
		$row = $db->loadObject();
		
		$return[0] = $row;
		return $return;
	}
	
	function getThankPayment(){
		$app  = JFactory::getApplication();
		$oid  = $app->input->get('oid', 0, 'int');
		$buy  = $app->input->get('buy', '', 'string');	//either buy deposit or plan
		$db	  = JFactory::getDbo();
		
		$obj = new stdClass();
		
		if($buy == 'plan'){
			$row	= JTable::getInstance('plansubscr', 'Table');
			$row->load($oid);
			
			$query = "SELECT * FROM #__jblance_plan WHERE id = ".$db->quote($row->plan_id);
			$db->setQuery($query);
			$plan = $db->loadObject();
			
			$obj->itemName 	= JText::_('COM_JBLANCE_BUY_SUBSCR').' - '.$plan->name;
			$obj->invoiceNo = $row->invoiceNo;
			$obj->status 	= $row->approved;
			$obj->amount 	= (float)($row->price + $row->price * ($row->tax_percent/100));
			$obj->gateway 	= JblanceHelper::getPaymodeInfo($row->gateway)->gateway_name;
			$obj->lnk_continue 	= JRoute::_('index.php?option=com_jblance&view=membership&layout=planhistory', false);
			$obj->lnk_invoice 	= JRoute::_('index.php?option=com_jblance&view=membership&layout=invoice&id='.$row->id.'&tmpl=component&print=1&type=plan');
		}
		elseif($buy == 'deposit'){
			$row 	= JTable::getInstance('deposit', 'Table');
			$row->load($oid);
			
			$obj->itemName 	=  JText::_('COM_JBLANCE_DEPOSIT_FUNDS');
			$obj->invoiceNo = $row->invoiceNo;
			$obj->status 	= $row->approved;
			$obj->amount 	= $row->total;
			$obj->gateway 	= JblanceHelper::getPaymodeInfo($row->gateway)->gateway_name;
			$obj->lnk_continue 	= JRoute::_('index.php?option=com_jblance&view=membership&layout=managepay', false);
			$obj->lnk_invoice 	= JRoute::_('index.php?option=com_jblance&view=membership&layout=invoice&id='.$row->id.'&tmpl=component&print=1&type=deposit');
		}
		
		$return[0] = $obj;
		return $return;
	}
	
	/* Misc Functions */
	
	//13.getSelectPaymode
	function getSelectPaymode($var, $default, $disabled){
		$app = JFactory::getApplication();
		$db	= JFactory::getDbo();
	
		$option = '';
		if($disabled == 1)
			$option = 'disabled';
	
		//make selection salutation
		$query = "SELECT gwcode AS value, gateway_name AS text FROM #__jblance_paymode ".
				 "WHERE published=1 ".
				 "ORDER BY ordering";
		$db->setQuery($query);
		$paymodes = $db->loadObjectList();
	
		foreach($paymodes as $item){
			$types[] = JHtml::_('select.option', $item->value, JText::_($item->text));
		}
	
		$lists 	= JHtml::_('select.genericlist', $types, $var, 'class="form-control" size="1" style="width:200px;"  '.$option.'', 'value', 'text', $default);
		return $lists;
	}
	
	//13.getRadioPaymode
	function getRadioPaymode($var, $default, $disabled){
		$app = JFactory::getApplication();
		$db	= JFactory::getDbo();
		$html = '';
		$i = 0;
	
		$option = '';
		if($disabled == 1)
			$option = 'disabled';
	
		$query = "SELECT gwcode AS value, gateway_name AS text FROM #__jblance_paymode ".
				 "WHERE published=1 ".
				 "ORDER BY ordering";
		$db->setQuery($query);
		$paymodes = $db->loadObjectList();
		
		if(empty($paymodes)){
			$html = '<p class="jbbox-warning">'.JText::_('COM_JBLANCE_NO_PAYMENT_GATEWAY_ENABLED').'</p>';
			return $html;
		}
	
		foreach($paymodes as $item){
			$checked = $img = '';
			$imgPath = "components/com_jblance/gateways/images/$item->value.png";
			
			//keep the first element checked
			if(++$i == 1) $checked="checked";
			
			if(JFile::exists($imgPath))
				$img = '<img class="img-polaroid" title="" src="'.$imgPath.'" alt="'.$item->text.'" style="margin-left:10px; width:100px; height:30px;">';
			
			$html .=  "\n\t".
					  '<label class="radio" style="min-height:42px;">'.
					  '<input type="radio" style="float:none;" name="'.$var.'" id="'.$var.'" value="'.$item->value.'" '.$checked.'>'.
					  $img.
					  '<span style="margin-left: 10px;">'.JText::_($item->text).'</span>'.
					  '</label>';
		}
		$html .= "\n";
		return $html;
	}
	
	function buildPlanInfo($planId){
		
		//initialize variables
		$jbuser = JblanceHelper::get('helper.user');		// create an instance of the class userHelper
		$planInfo = $jbuser->getPlanInfo($planId);
		JHtml::_('bootstrap.tooltip');
	
		//get the plan details for the plan id passed
		$ugid = $planInfo->ug_id;
	
		//get the user group of the plan and thereby, get the usergroup info.
		$ugInfo = $jbuser->getUserGroupInfo(null, $ugid);
		
		$bidPostProjectAllowed = false;
		if($ugInfo->allowBidProjects && $ugInfo->allowPostProjects)
			$bidPostProjectAllowed = true;
	
		$i = 0;
		$infos = '';
		//get the keys and values for the allowed functions.
		if($ugInfo->allowBidProjects){
			
			if($bidPostProjectAllowed){
				$infos[$i] = new stdClass();
				$infos[$i]->key = "<span class='lead grey'>".JText::_('COM_JBLANCE_AS_FREELANCER')."</span>";
				$infos[$i]->value = "<span class='lead grey'>&nbsp;</span>";
				$i++;
			}
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_PROJECT_COMMISSION');
			$infos[$i]->value = "<span class=hasTooltip title='".JText::_('COM_JBLANCE_WHICHEVER_HIGHER')."'>".JblanceHelper::formatCurrency($planInfo->flFeeAmtPerProject).' '.JText::_('COM_JBLANCE_OR').' '.$planInfo->flFeePercentPerProject.'%'."</span>";
			//$infos[$i]->value = JblanceHelper::formatCurrency($planInfo->flFeeAmtPerProject).' '.JText::_('COM_JBLANCE_OR').' '.$planInfo->flFeePercentPerProject.'%';
			$i++;
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_CHARGE_PER_BID');
			$infos[$i]->value = ($planInfo->flChargePerBid == 0) ? JText::_('COM_JBLANCE_FREE') : JblanceHelper::formatCurrency($planInfo->flChargePerBid);
			$i++;
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_SERVICE_COMMISSION');
			$infos[$i]->value = $planInfo->flFeePercentPerService.'%';
			$i++;
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_CHARGE_PER_SERVICE');
			$infos[$i]->value = JblanceHelper::formatCurrency($planInfo->flChargePerService);
			$i++;
						
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_NUM_PORTFOLIOS_ALLOWED');
			$infos[$i]->value = ($planInfo->portfolioCount == 0) ? "<img src=".JURI::root()."components/com_jblance/images/s0.png width=12 alt=No />" : $planInfo->portfolioCount;
			$i++;
	
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_NUM_BIDS_ALLOWED');
			$infos[$i]->value = ($planInfo->flBidCount == 0) ? JText::_('COM_JBLANCE_UNLIMITED') : $planInfo->flBidCount;
			$i++;
	
		}
		if($ugInfo->allowPostProjects){
			if($bidPostProjectAllowed){
				$infos[$i] = new stdClass();
				$infos[$i]->key = "<span class='lead grey'>".JText::_('COM_JBLANCE_AS_BUYER')."</span>";
				$infos[$i]->value = "<span class='lead grey'>&nbsp;</span>";
				$i++;
			}
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_PROJECT_COMMISSION');
			$infos[$i]->value = "<span class=hasTooltip title='".JText::_('COM_JBLANCE_WHICHEVER_HIGHER')."'>".JblanceHelper::formatCurrency($planInfo->buyFeeAmtPerProject).' '.JText::_('COM_JBLANCE_OR').' '.$planInfo->buyFeePercentPerProject.'%'."</span>";
			//$infos[$i]->value = JblanceHelper::formatCurrency($planInfo->buyFeeAmtPerProject).' '.JText::_('COM_JBLANCE_OR').' '.$planInfo->buyFeePercentPerProject.'%';
			$i++;
	
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_CHARGE_PER_PROJECT');
			$infos[$i]->value = ($planInfo->buyChargePerProject == 0) ? JText::_('COM_JBLANCE_FREE') : JblanceHelper::formatCurrency($planInfo->buyChargePerProject);
			$i++;
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_NUM_PROJECTS_ALLOWED');
			$infos[$i]->value = ($planInfo->buyProjectCount == 0) ? JText::_('COM_JBLANCE_UNLIMITED') : $planInfo->buyProjectCount;
			$i++;
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_FEATURED_PROJECT');
			$infos[$i]->value = ($planInfo->buyFeePerFeaturedProject == 0) ? JText::_('COM_JBLANCE_FREE') : JblanceHelper::formatCurrency($planInfo->buyFeePerFeaturedProject);
			$i++;
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_URGENT_PROJECT');
			$infos[$i]->value = ($planInfo->buyFeePerUrgentProject == 0) ? JText::_('COM_JBLANCE_FREE') : JblanceHelper::formatCurrency($planInfo->buyFeePerUrgentProject);
			$i++;
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_PRIVATE_PROJECT');
			$infos[$i]->value = ($planInfo->buyFeePerPrivateProject == 0) ? JText::_('COM_JBLANCE_FREE') : JblanceHelper::formatCurrency($planInfo->buyFeePerPrivateProject);
			$i++;
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_SEALED_PROJECT');
			$infos[$i]->value = ($planInfo->buyFeePerSealedProject == 0) ? JText::_('COM_JBLANCE_FREE') : JblanceHelper::formatCurrency($planInfo->buyFeePerSealedProject);
			$i++;
			
			$infos[$i] = new stdClass();
			$infos[$i]->key = JText::_('COM_JBLANCE_NDA_PROJECT');
			$infos[$i]->value = ($planInfo->buyFeePerNDAProject == 0) ? JText::_('COM_JBLANCE_FREE') : JblanceHelper::formatCurrency($planInfo->buyFeePerNDAProject);
			$i++;
		}
	
		return $infos;
	}
 	
	function countManagePayPending($type){
		$db 	= JFactory::getDbo();
		$user	= JFactory::getUser();
		
		if($type == 'deposit')
			$query = "SELECT COUNT(*) FROM #__jblance_deposit WHERE user_id= ".$db->quote($user->id)." AND approved=0";
		elseif($type == 'withdraw')
			$query = "SELECT COUNT(*) FROM #__jblance_withdraw WHERE user_id= ".$db->quote($user->id)." AND approved=0";
		elseif($type == 'escrowout')
			$query = "SELECT COUNT(*) FROM #__jblance_escrow WHERE from_id= ".$db->quote($user->id)." AND status=''";
		elseif($type == 'escrowin')
			$query = "SELECT COUNT(*) FROM #__jblance_escrow WHERE to_id= ".$db->quote($user->id)." AND status='COM_JBLANCE_RELEASED'";
		
		$db->setQuery($query);
		$total 	= $db->loadResult();
		return $total;
	}
 }