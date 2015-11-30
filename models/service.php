<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	04 November 2014
 * @file name	:	models/service.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 jimport('joomla.application.component.model');
 
 class JblanceModelService extends JModelLegacy {
 	
 	function getMyService(){
 		$app  = JFactory::getApplication();
 		$db	  = JFactory::getDbo();
 		$user = JFactory::getUser();
 		
 		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
 		$limitstart	= $app->input->get('limitstart', 0, 'int');
 		
 		$query = "SELECT s.*,(SELECT COUNT(*) FROM #__jblance_service_order so WHERE so.service_id=s.id) buycount FROM #__jblance_service s".
 				 " WHERE s.user_id=".$db->quote($user->id).
 				 " ORDER BY s.id DESC";//echo $query;
 		$db->setQuery($query);
 		$db->execute();
 		$total = $db->getNumRows();
 		
 		jimport('joomla.html.pagination');
 		$pageNav = new JPagination($total, $limitstart, $limit);
 		
 		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
 		$rows = $db->loadObjectList();
 		
 		$return[0] = $rows;
 		$return[1] = $pageNav;
 		return $return;
 	}
 	
 	function getListService(){
 		$app  = JFactory::getApplication();
 		$db   = JFactory::getDbo();
 		
 		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
 		$limitstart	= $app->input->get('limitstart', 0, 'int');
 		
 		$keyword	= $app->input->get('keyword', '', 'string');
 		$id_categ	= $app->input->get('id_categ', array(), 'array');
 		JArrayHelper::toInteger($id_categ);
 		
 		$text		= $db->quote('%'.$db->escape($keyword, true).'%', false);
 		$wheres2 	= array();
 		$wheres2[] 	= 's.service_title LIKE '.$text;
 		$wheres2[] 	= 's.description LIKE '.$text;
 		$queryStrings[] = '((' . implode( ') OR (', $wheres2 ) . '))';
 		
 		if(count($id_categ) > 0 && !(count($id_categ) == 1 && empty($id_categ[0]))){
 			if(is_array($id_categ)){
 				$miniquery = array();
 				foreach($id_categ as $cat){
 					$miniquery[] = "FIND_IN_SET(".$cat.", s.id_category)";
 				}
 				$querytemp = '('.implode(' OR ', $miniquery).')';
 			}
 			$queryStrings[] = $querytemp;
 		}
 		
 		$queryStrings[] = "s.approved=1";
 		$queryStrings[] = "ps.date_expire > NOW()";
 		$where =  implode(' AND ', $queryStrings);
 		
 		$query = "SELECT s.* FROM #__jblance_service s ".
 	 			 "LEFT JOIN #__jblance_plan_subscr ps ON s.user_id = ps.user_id ".
 	 			 "WHERE ".$where." ".
 				 "ORDER BY s.id DESC";
 		$db->setQuery($query);//echo $query;
 		$db->execute();
 		$total = $db->getNumRows();
 		
 		jimport('joomla.html.pagination');
 		$pageNav = new JPagination($total, $limitstart, $limit);
 		
 		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
 		$rows = $db->loadObjectList();
 		
 		$return[0] = $rows;
 		$return[1] = $pageNav;
 		return $return;
 	}
 	
 	function getServiceBought(){
 		$app  = JFactory::getApplication();
 		$db   = JFactory::getDbo();
 		$user = JFactory::getUser();
 		
 		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
 		$limitstart	= $app->input->get('limitstart', 0, 'int');
 		
 		$query = "SELECT so.id,so.service_id,so.price totalprice,so.duration totalduration,so.p_status,so.p_percent,".
 				 " s.service_title,s.attachment FROM #__jblance_service_order so".
 	 			 " LEFT JOIN #__jblance_service s ON s.id=so.service_id".
 				 " WHERE so.user_id=".$db->quote($user->id).
 				 " ORDER BY so.id DESC";
 		$db->setQuery($query);//echo $query;
 		$db->execute();
 		$total = $db->getNumRows();
 		
 		jimport('joomla.html.pagination');
 		$pageNav = new JPagination($total, $limitstart, $limit);
 		
 		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
 		$rows = $db->loadObjectList();
 		
 		$return[0] = $rows;
 		$return[1] = $pageNav;
 		return $return;
 	}
 	
 	function getServiceSold(){
 		$app  = JFactory::getApplication();
 		$db   = JFactory::getDbo();
 		$user = JFactory::getUser();
 		$id   = $app->input->get('id', 0, 'int');
 		
 		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
 		$limitstart	= $app->input->get('limitstart', 0, 'int');
 		
 		if($id > 0)
 			$queryStrings[] = "s.id=".$db->quote($id);
 		
 		$queryStrings[] = "s.user_id=".$db->quote($user->id);
 		
 		$where =  implode (' AND ', $queryStrings);
 		
 		$query = "SELECT so.id,so.service_id,so.price totalprice,so.duration totalduration,so.p_status,so.p_percent,".
 	 			 " s.service_title,s.attachment FROM #__jblance_service_order so".
 	 			 " LEFT JOIN #__jblance_service s ON s.id=so.service_id".
 				 " WHERE ".$where.
 				 " ORDER BY so.id DESC";
 		$db->setQuery($query);//echo $query;
 		$db->execute();
 		$total = $db->getNumRows();
 		
 		jimport('joomla.html.pagination');
 		$pageNav = new JPagination($total, $limitstart, $limit);
 		
 		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
 		$rows = $db->loadObjectList();
 		
 		$return[0] = $rows;
 		$return[1] = $pageNav;
 		return $return;
 	}
 	
 	function getServiceProgress(){
 		$app  = JFactory::getApplication();
 		$db   = JFactory::getDbo();
 		$user = JFactory::getUser();
 		$id   = $app->input->get('id', 0, 'int');	//service order id
 		
 		$isOwnedOperation = JblanceHelper::checkOwnershipOfOperation($id, 'serviceprogress');	//check ownership
 		if(!$isOwnedOperation){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
 		$query = "SELECT so.user_id buyer_id, so.extras addons, so.price totalprice, so.duration totalduration, so.p_status, so.p_percent,so.id order_id, so.p_started, so.p_updated, so.p_ended, ".
 	 			 "s.id service_id, s.user_id seller_id, s.service_title, s.price baseprice, s.duration baseduration FROM #__jblance_service_order so".
 	 			 " LEFT JOIN #__jblance_service s ON s.id=so.service_id".
 				 " WHERE so.id=".$db->quote($id);//echo $query;
 		$db->setQuery($query);
 		$row = $db->loadObject();
 		
 		$query = "SELECT * FROM #__jblance_message ".
 				 "WHERE project_id=".$db->quote($id)." AND type='COM_JBLANCE_SERVICE' AND deleted=0 ".// AND (idFrom = ".$user->id." OR idTo = ".$user->id.") ".
 				 "ORDER BY id";//echo $query;
 		$db->setQuery($query);
 		$messages = $db->loadObjectList();
 		
 		//update the status of the messages to be read
 		$query = "UPDATE #__jblance_message SET is_read=1 WHERE idTo=".$db->quote($user->id)." AND project_id=".$db->quote($id)." AND type='COM_JBLANCE_SERVICE'";
 		$db->setQuery($query);
 		if(!$db->execute()){
 			JError::raiseError(500, $db->getError());
 		}
 		
 		$return[0] = $row;
 		$return[1] = $messages;
 		return $return;
 	}
 	
 	function getEditService(){
 		$app   = JFactory::getApplication();
 		$db	   = JFactory::getDbo();
 		$id    = $app->input->get('id', 0, 'int');
 		$user  = JFactory::getUser();
 		$isNew = ($id > 0) ? false : true;
 		
 		//check if the owner is editing the service
 		$isOwnedOperation = JblanceHelper::checkOwnershipOfOperation($id, 'service');
 		if($id > 0 && !$isOwnedOperation){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE');
 			$app->enqueueMessage($msg, 'error');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->redirect($link);
 			return false;
 		}
 		
 		//check if the user's plan is expired or not approved. If so, do not allow him to create service
 		$planStatus = JblanceHelper::planStatus($user->id);
 		if($isNew && ($planStatus == 1 || $planStatus == 2)){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_ALLOWED_TO_DO_OPERATION_NO_ACTIVE_SUBSCRIPTION');
 			$app->enqueueMessage($msg, 'error');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->redirect($link);
 			return false;
 		}
 		
 		//check if the user has enough fund to create new services. This should be checked for new services only
 		$plan = JblanceHelper::whichPlan($user->id);
 		$chargePerService	 = $plan->flChargePerService;
 		
 		if($isNew && ($chargePerService > 0)){
 			$totalFund = JblanceHelper::getTotalFund($user->id);
 			if($totalFund < $chargePerService){
 				$msg = JText::sprintf('COM_JBLANCE_BALANCE_INSUFFICIENT_TO_POST_SERVICE', JblanceHelper::formatCurrency($chargePerService));
 				$app->enqueueMessage($msg, 'error');
 				$link	= JRoute::_('index.php?option=com_jblance&view=membership&layout=depositfund', false);
 				$app->redirect($link);
 				return false;
 			}
 		}
 		
 		$row = JTable::getInstance('service', 'Table');
 		$row->load($id);
 		
 		//show reason if service is not approved.
 		if(!$isNew && !$row->approved){
 			$msg = empty($row->disapprove_reason) ? JText::_('COM_JBLANCE_SERVICE_PENDING_APPROVAL_FROM_ADMIN') : $row->disapprove_reason;
 			$app->enqueueMessage(nl2br($msg), 'Error');
 		}
 		
 		$return[0] = $row;
 		return $return;
 		
 	}
 	
 	function getViewService(){
 		$app  = JFactory::getApplication();
 		$db	  = JFactory::getDbo();
 		$user = JFactory::getUser();
 		$id   = $app->input->get('id', 0, 'int');
 		
 		$row = JTable::getInstance('service', 'Table');
 		$row->load($id);
 		
 		$isMine = ($row->user_id == $user->id);
 		
 		//redirect non-seller to service list if the service is not approved.
 		if(!$row->approved && !$isMine){
 			$msg = JText::_('COM_JBLANCE_SERVICE_PENDING_APPROVAL_FROM_ADMIN');
 			$link_dash  = JRoute::_('index.php?option=com_jblance&view=service&layout=listservice', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link_dash);
 		}
 		elseif(!$row->approved && $isMine){
 			$msg = empty($row->disapprove_reason) ? JText::_('COM_JBLANCE_SERVICE_PENDING_APPROVAL_FROM_ADMIN') : $row->disapprove_reason;
 			$app->enqueueMessage(nl2br($msg), 'Error');
 		}
 		
 		//get the rating list for the service
 		$query =  "SELECT r.*,so.service_id,so.id order_id FROM #__jblance_rating r ".
				  "LEFT JOIN #__jblance_service_order so ON  (so.id=r.project_id AND r.type='COM_JBLANCE_SERVICE') ".
				  "WHERE r.type='COM_JBLANCE_SERVICE' AND so.service_id=".$db->quote($id)." AND r.actor <>".$db->quote($row->user_id); //echo $query;
 		$db->setQuery($query);
 		$ratings = $db->loadObjectList();
 		
 		//update the service hits
 		$query = $db->getQuery(true);
 		$query->clear()
			->update($db->quoteName('#__jblance_service'))
			->set($db->quoteName('hits').'='.$db->quoteName('hits').'+1')
			->where('id='.(int)$id);
		$db->setQuery($query);
		$db->execute();
 		
 		$return[0] = $row;
 		$return[1] = $ratings;
 		return $return;
 	}
 	
 	function getRateService(){
 		$app  	  = JFactory::getApplication();
 		$db   	  = JFactory::getDbo();
 		$db   	  = JFactory::getDbo();
 		$user	  = JFactory::getUser();
 		$order_id = $app->input->get('id', 0, 'int');	//service order id
 		
 		//check if the current user or actor has already rated. If rated, redirect him
 		$hasRated = self::hasRated($order_id, $user->id);
 		if($hasRated){
 			$msg = JText::sprintf('COM_JBLANCE_YOU_HAVE_ALREADY_RATED_USER');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
 		$query = " SELECT s.service_title,so.id order_id,s.user_id seller_id,so.user_id buyer_id FROM #__jblance_service_order so".
 				 " LEFT JOIN #__jblance_service s ON so.service_id=s.id".
 				 " WHERE so.id=".$db->quote($order_id);
 		$db->setQuery($query);//echo $query;
 		$row = $db->loadObject();
 	
 		$return[0] = $row;
 		return $return;
 	}
 	
 	/* Misc Functions */
 	
 	//get the service order status in "My Services"
 	function getServiceProgressCounts($service_id){
 		$db   = JFactory::getDbo();
 		$user = JFactory::getUser();
 		$notYetStarted = 0;
 		$inProgress = 0;
 		$completed = 0;
 		$html =  '';
 		
 		$query = "SELECT * FROM #__jblance_service_order so ".
 				 "WHERE so.service_id=".$db->quote($service_id);//echo $query;
 		$db->setQuery($query);
 		$rows = $db->loadObjectList();
 		foreach($rows as $row){
 			if($row->p_status == '')
 				$notYetStarted++;
 			elseif($row->p_status == 'COM_JBLANCE_INITIATED' || $row->p_status == 'COM_JBLANCE_IN_PROGRESS'){
 				$inProgress++;
 			}
 			elseif($row->p_status == 'COM_JBLANCE_COMPLETED')
 				$completed++;
 		}
 		
 		if($notYetStarted > 0)
 			$html .= JText::_('COM_JBLANCE_NOT_YET_STARTED').': '.$notYetStarted;
 		if($inProgress > 0)
 			$html .= ' '.JText::_('COM_JBLANCE_IN_PROGRESS').': '.$inProgress;
 		if($completed > 0)
 			$html .= ' '.JText::_('COM_JBLANCE_COMPLETED').': '.$completed;

 		return $html;
 	}
 	
 	public static function hasRated($order_id, $actor){
 		$db = JFactory::getDbo();

 		$query = "SELECT COUNT(*) FROM #__jblance_rating WHERE project_id = ".$db->quote($order_id)." AND actor =".$db->quote($actor)." AND type='COM_JBLANCE_SERVICE'";
 		$db->setQuery($query);
		$count = $db->loadResult();
 		if($count > 0)
 			return 1;
 		else
 			return 0;
 	}
 	
 	function servicePurchaseCount($service_id){
 		$db = JFactory::getDbo();
 		
 		$query = "SELECT COUNT(*) FROM #__jblance_service_order so WHERE so.service_id=".$db->quote($service_id);
 		$db->setQuery($query);
 		$count = $db->loadResult();
 		return $count;
 	}
 }