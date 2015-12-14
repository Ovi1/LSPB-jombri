<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	23 March 2012
 * @file name	:	models/project.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 jimport('joomla.application.component.model');
 
 class JblanceModelProject extends JModelLegacy {
 	
 	function getEditProject(){
 		
 		$app  = JFactory::getApplication();
 		$db   = JFactory::getDbo();
 		$user = JFactory::getUser();
 		$id   = $app->input->get('id', 0, 'int');
 		$finance = JblanceHelper::get('helper.finance');		// create an instance of the class FinanceHelper
 		
 		$isOwnedOperation = JblanceHelper::checkOwnershipOfOperation($id, 'project');
 		if($id > 0 && !$isOwnedOperation){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
 		//check if the user's plan has expired or not approved. If so, do not allow him to post new project
 		$planStatus = JblanceHelper::planStatus($user->id);
 		if(($id == 0) && ($planStatus == 1 || $planStatus == 2)){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_ALLOWED_TO_DO_OPERATION_NO_ACTIVE_SUBSCRIPTION');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
 		//check if the user has enough fund to post new projects. This should be checked for new projects only
 		$plan = JblanceHelper::whichPlan($user->id);
 		$chargePerProject = $plan->buyChargePerProject;
 		
 		if( ($chargePerProject > 0) && ($id == 0) ){
 			$totalFund = JblanceHelper::getTotalFund($user->id);
 			if($totalFund < $chargePerProject){
 				$msg = JText::sprintf('COM_JBLANCE_BALANCE_INSUFFICIENT_TO_POST_PROJECT', JblanceHelper::formatCurrency($chargePerProject));
 				$link	= JRoute::_('index.php?option=com_jblance&view=membership&layout=depositfund', false);
 				$app->enqueueMessage($msg, 'error');
 				$app->redirect($link);
 				return false;
 			}
 		}
 		
 		//check if the user has any project limit. If any and exceeds, then disallow him
 		$lastSubscr = $finance->getLastSubscription($user->id);
 		if(($id == 0) && ($lastSubscr->projects_allowed > 0 && $lastSubscr->projects_left == 0)){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_ALLOWED_TO_POST_PROJECT_LIMIT_EXCEEDED');
 			$link	= JRoute::_('index.php?option=com_jblance&view=membership&layout=planadd', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
 		$row = JTable::getInstance('project', 'Table');
 		$row->load($id);
 		
 		$query = 'SELECT * FROM #__jblance_project_file WHERE project_id='.$db->quote($id);
 		$db->setQuery($query);
 		$projfiles = $db->loadObjectList();
 		
 		$query = "SELECT * FROM #__jblance_custom_field ".
 				 "WHERE published=1 AND field_for=".$db->quote('project')." ".
 				 "ORDER BY ordering";
 		$db->setQuery($query);
 		$fields = $db->loadObjectList();
 		
 		$return[0] = $row;
 		$return[1] = $projfiles;
 		$return[2] = $fields;
 		return $return;
 	}
 	
 	function getShowMyProject(){
 		$app  = JFactory::getApplication();
 		$db	  = JFactory::getDbo();
 		$user = JFactory::getUser();
 		
 		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
 		$limitstart	= $app->input->get('limitstart', 0, 'int');
 		
 		$query = 'SELECT * FROM #__jblance_project p WHERE p.publisher_userid='.$db->quote($user->id).' ORDER BY p.id DESC';
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
 	
 	function getListProject(){
 		$app  = JFactory::getApplication();
 		$db	  = JFactory::getDbo();
 		$user = JFactory::getUser();
 		$now  = JFactory::getDate();
 		$where = array();
 		
 		// Load the parameters.
 		$params = $app->getParams();
 		$param_status = $params->get('param_status', 'open');
 		$param_upgrade = $params->get('param_upgrade', 'all');
 		$param_categid = (int)$params->get('id_categ', '');
 		
 		if($param_status == 'open')
 			$where[] = "p.status=".$db->quote('COM_JBLANCE_OPEN');
 		elseif($param_status == 'frozen')
 			$where[] = "p.status=".$db->quote('COM_JBLANCE_FROZEN');
 		elseif($param_status == 'closed')
 			$where[] = "p.status=".$db->quote('COM_JBLANCE_CLOSED');
 		
 		if($param_upgrade == 'featured')
 			$where[] = "p.is_featured=1";
 		elseif($param_upgrade == 'urgent')
 			$where[] = "p.is_urgent=1";
 		elseif($param_upgrade == 'private')
 			$where[] = "p.is_private=1";
 		elseif($param_upgrade == 'sealed')
 			$where[] = "p.is_sealed=1";
 		
 		if(!empty($param_categid))
 			$where[] = "FIND_IN_SET(".$param_categid.", p.id_category)";
 		
 		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
 		$limitstart	= $app->input->get('limitstart', 0, 'int');
 		
 		$where[] = "p.approved=1";
 		$where[] = "p.start_date < ".$db->quote($now);
 		
 		$where = (count($where) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '');
 		
 		$query = "SELECT p.*,(TO_DAYS(p.start_date) - TO_DAYS(NOW())) AS daydiff FROM #__jblance_project p ".
 				 $where." ".
 				 "ORDER BY p.is_featured DESC, p.id DESC";//echo $query;
 		$db->setQuery($query);
 		$db->execute();
 		$total = $db->getNumRows();
 		
 		jimport('joomla.html.pagination');
 		$pageNav = new JPagination($total, $limitstart, $limit);
 		
 		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
 		$rows = $db->loadObjectList();
 		
 		$return[0] = $rows;
 		$return[1] = $pageNav;
 		$return[2] = $params;
 		return $return;
 	}
 	
 	function getDetailProject(){
 		$app  = JFactory::getApplication();
 		$db	  = JFactory::getDbo();
 		$user = JFactory::getUser();
 		$id   = $app->input->get('id', 0, 'int');
 		
 		$config 		 = JblanceHelper::getConfig();
 		$sealProjectBids = $config->sealProjectBids;
 		
 		$row = JTable::getInstance('project', 'Table');
 		$row->load($id);
 		
 		//get the location info
 		$location  = JTable::getInstance('location', 'Table');
 		if($row->id_location > 0){
 			$location->load($row->id_location);
 			$this->setState('projectLocation', $location->params);
 		}
 		
 		//redirect the project to login page if the project is a `private` project and user is not logged in
 		if($row->is_private && $user->guest){
 			$url 	= JFactory::getURI()->toString();
 			$msg = JText::_('COM_JBLANCE_PRIVATE_PROJECT_LOGGED_IN_TO_SEE_DESCRIPTION');
 			$link_login  = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($url), false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link_login);
 		}
 		
 		//redirect the user to dashboard if the project is not approved.
 		if(!$row->approved){
 			$msg = JText::_('COM_JBLANCE_PROJECT_PENDING_APPROVAL_FROM_ADMIN');
 			$link_dash  = JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link_dash);
 		}
 		
 		//redirect to dashboard if this is private invite project
 		if($row->is_private_invite){
			$isMine = ($row->publisher_userid == $user->id);
			$invite_ids = explode(',', $row->invite_user_id);
			if(!in_array($user->id, $invite_ids) && !$isMine){
				$msg = JText::_('COM_JBLANCE_THIS_IS_A_PRIVATE_INVITE_PROJECT_VISIBLE_TO_OWNER_INVITEES');
 				$link_dash  = JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 				$app->enqueueMessage($msg, 'error');
 				$app->redirect($link_dash);
			}
 		}
 		
 		//get project files
 		$query = 'SELECT * FROM #__jblance_project_file WHERE project_id='.$db->quote($id);
 		$db->setQuery($query);
 		$projfiles = $db->loadObjectList();
 		
 		//if the project is sealed, get the particular bid row for the bidder.
 		$projHelper = JblanceHelper::get('helper.project');		// create an instance of the class ProjectHelper
 		$hasBid = $projHelper->hasBid($row->id, $user->id);
 		
 		$bidderQuery = 'TRUE';
 		if(($sealProjectBids || $row->is_sealed) && $hasBid){
 			$bidderQuery = " b.user_id=".$db->quote($user->id);
 		}
 		
 		//for nda projects, bid count should inlcude only signed bids
 		$ndaQuery = 'TRUE';
 		if($row->is_nda)
 			$ndaQuery = " b.is_nda_signed=1";
 		
 		//get bid info
 		$query ="SELECT b.*, u.username, u.name FROM #__jblance_bid b ".
 				"INNER JOIN #__users u ON b.user_id=u.id ".
 				"WHERE b.project_id =".$db->quote($id)." AND ".$bidderQuery." AND ".$ndaQuery;//echo $query;
 		$db->setQuery($query);
 		$bids = $db->loadObjectList();
 		
 		$query = "SELECT * FROM #__jblance_custom_field ".
 				 "WHERE published=1 AND field_for=".$db->quote('project')." ".
 				 "ORDER BY ordering";
 		$db->setQuery($query);
 		$fields = $db->loadObjectList();
 		
 		//get the forum list
 		$query = "SELECT * FROM #__jblance_forum ".
 				 "WHERE project_id=".$db->quote($row->id)." ".
 				 "ORDER BY date_post ASC";
 		$db->setQuery($query);//echo $query;
 		$forums = $db->loadObjectList();
 		
 		$return[0] = $row;
 		$return[1] = $projfiles;
 		$return[2] = $bids;
 		$return[3] = $fields;
 		$return[4] = $forums;
 		return $return;
 	}
 	
 	function getPlaceBid(){
 		$app  = JFactory::getApplication();
 		$db	  = JFactory::getDbo();
 		$user = JFactory::getUser();
 		$id   = $app->input->get('id', 0, 'int');	//id is the "project id"
 		$finance = JblanceHelper::get('helper.finance');		// create an instance of the class FinanceHelper
 		
 		$project = JTable::getInstance('project', 'Table');
 		$project->load($id);
 		
 		// Project author is allowed to bid on his own project
 		if($project->publisher_userid == $user->id){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_ALLOWED_TO_BID_ON_YOUR_OWN_PROJECT');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
 		//project in Frozen/Closed should not be allowed to bid
 		if($project->status != 'COM_JBLANCE_OPEN'){
 			$link = JRoute::_('index.php?option=com_jblance&view=project&layout=listproject', false);
 			$app->redirect($link);
 			return;
 		}
 		
 		//redirect to dashboard if this is private invite project
 		if($project->is_private_invite){
 			$invite_ids = explode(',', $project->invite_user_id);
 			if(!in_array($user->id, $invite_ids)){
 				$msg = JText::_('COM_JBLANCE_THIS_IS_A_PRIVATE_INVITE_PROJECT_VISIBLE_TO_OWNER_INVITEES');
 				$link_dash  = JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 				$app->enqueueMessage($msg, 'error');
 				$app->redirect($link_dash);
 			}
 		}
 		
 		
 		//get the bid id
 		$query = "SELECT id FROM #__jblance_bid WHERE project_id=".$db->quote($id)." AND user_id=".$db->quote($user->id);
 		$db->setQuery($query);
 		$bid_id = $db->loadResult();
 		
 		$bid = JTable::getInstance('bid', 'Table');
 		$bid->load($bid_id);
 		
 		//check if the user's plan is expired or not approved. If so, do not allow him to bid new on project
 		$planStatus = JblanceHelper::planStatus($user->id);
 		if( empty($bid_id) && ($planStatus == 1 || $planStatus == 2) ){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_ALLOWED_TO_DO_OPERATION_NO_ACTIVE_SUBSCRIPTION');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
 		//check if the user has enough fund to bid new on projects. This should be checked for new bids only
 		$plan = JblanceHelper::whichPlan($user->id);
 		$chargePerBid = $plan->flChargePerBid;
 		
 		if( ($chargePerBid > 0) && (empty($bid_id)) ){	// bid_id will be empty for new bids
 			$totalFund = JblanceHelper::getTotalFund($user->id);
 			if($totalFund < $chargePerBid){
 				$msg = JText::sprintf('COM_JBLANCE_BALANCE_INSUFFICIENT_TO_BID_PROJECT', JblanceHelper::formatCurrency($chargePerBid));
 				$link	= JRoute::_('index.php?option=com_jblance&view=membership&layout=depositfund', false);
 				$app->enqueueMessage($msg, 'error');
 				$app->redirect($link);
 				return false;
 			}
 		}
 		
 		//check if the user has any bid limit. If any and exceeds, then disallow him
 		$lastSubscr = $finance->getLastSubscription($user->id);
 		if(empty($bid_id) && ($lastSubscr->bids_allowed > 0 && $lastSubscr->bids_left <= 0)){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_ALLOWED_TO_BID_PROJECT_LIMIT_EXCEEDED');
 			$link	= JRoute::_('index.php?option=com_jblance&view=membership&layout=planadd', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
 		$return[0] = $project;
 		$return[1] = $bid;
 		return $return;
 	}
 	
 	function getProjectProgress(){
 		$app  = JFactory::getApplication();
 		$db   = JFactory::getDbo();
 		$user = JFactory::getUser();
 		$id   = $app->input->get('id', 0, 'int');	//bid id
 		
 		$isOwnedOperation = JblanceHelper::checkOwnershipOfOperation($id, 'projectprogress');	//check ownership
 		if(!$isOwnedOperation){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
 		//get the project id from bid id
 		$bid  = JTable::getInstance('bid', 'Table');
 		$bid->load($id);
 		
 		//get the project details
 		$project  = JTable::getInstance('project', 'Table');
 		$project->load($bid->project_id);
 	
 		$query = "SELECT p.id project_id, p.publisher_userid buyer_id, p.assigned_userid freelancer_id, p.project_title, ".
 				 " b.id bid_id, b.amount, b.delivery, b.p_status, b.p_percent, b.p_started, b.p_updated, b.p_ended FROM #__jblance_bid b".
 				 " LEFT JOIN #__jblance_project p ON p.id=b.project_id".
 				 " WHERE b.id=".$db->quote($id);//echo $query;
 		$db->setQuery($query);
 		$row = $db->loadObject();
 	
 		$query = "SELECT * FROM #__jblance_message ".
 				 "WHERE project_id=".$db->quote($project->id)." AND type='COM_JBLANCE_PROJECT' AND deleted=0 AND ".
 				 "(idFrom = ".$db->quote($project->publisher_userid)." OR idTo = ".$db->quote($project->publisher_userid).") AND (idFrom = ".$db->quote($project->assigned_userid)." OR idTo = ".$db->quote($project->assigned_userid).") ".
 				 "ORDER BY id";//echo $query;
 		$db->setQuery($query);
 		$messages = $db->loadObjectList();
 		
 		//update the status of the messages to be read
 		$query = "UPDATE #__jblance_message SET is_read=1 WHERE idTo=".$db->quote($user->id)." AND project_id=".$db->quote($project->id)." AND type='COM_JBLANCE_PROJECT'";
 		$db->setQuery($query);
 		if(!$db->execute()){
 			JError::raiseError(500, $db->getError());
 		}
 	
 		$return[0] = $row;
 		$return[1] = $messages;
 		return $return;
 	}
 	
 	function getShowMyBid(){
 		$db	  = JFactory::getDbo();
 		$user = JFactory::getUser();
 		
 		$query = "SELECT b.*,p.project_title,p.status proj_status,p.assigned_userid,p.publisher_userid,".
 				 "p.paid_amt,p.paid_status,p.lancer_commission,p.is_featured,p.is_urgent,p.is_private,p.is_sealed,p.is_nda,p.project_type,p.is_private_invite FROM #__jblance_bid b ".
 				 "LEFT JOIN #__jblance_project p ON b.project_id=p.id ".
 				 "WHERE user_id =".$db->quote($user->id)." ORDER BY b.id DESC";//echo $query;
 		$db->setQuery($query);
 		$rows = $db->loadObjectList();
 		
 		$return[0] = $rows;
 		return $return;
 	}
 	
 	function getPickUser(){
 		
 		$app  = JFactory::getApplication();
 		$db   = JFactory::getDbo();
 		$id   = $app->input->get('id', 0, 'int');	//proj id
 		
 		$project = JTable::getInstance('project', 'Table');
 		$project->load($id);
 		
 		$query ="SELECT b.*,u.username,u.name,p.project_title,p.project_type,p.commitment FROM #__jblance_bid b ".
 	 			"LEFT JOIN #__jblance_project p ON b.project_id=p.id ".
 				"INNER JOIN #__users u ON b.user_id=u.id ".
 				//"WHERE b.project_id =".$id." AND b.status =''";
 				"WHERE b.project_id =".$db->quote($id)." AND TRUE";
 		$db->setQuery($query);
 		$rows = $db->loadObjectList();
 		
 		$return[0] = $rows;
 		$return[1] = $project;
 		return $return;
 	}
 	
 	function getRateUser(){
 		$app  		= JFactory::getApplication();
 		$db   		= JFactory::getDbo();
 		$user	  	= JFactory::getUser();
 		$project_id = $app->input->get('id', 0, 'int');	//project id
 		
 		//check if the current user or actor has already rated. If rated, redirect him
 		$hasRated = self::hasRated($project_id, $user->id);
 		if($hasRated){
 			$msg = JText::sprintf('COM_JBLANCE_YOU_HAVE_ALREADY_RATED_USER');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
		/* $rate = JTable::getInstance('rating', 'Table');
		$rate->load($id); */
		
		//get info project
		$project = JTable::getInstance('project', 'Table');
		$project->load($project_id);
 		
 		//$return[0] = $rate;
 		$return[1] = $project;
 		return $return;
 	}
 	
 	//7.Search Project
 	function getSearchProject(){
 	
 		// Initialize variables
 		$app  = JFactory::getApplication();
 		$user = JFactory::getUser();
 		$db   = JFactory::getDbo();
 		$now  = JFactory::getDate();
 	
		$keyword	= $app->input->get('keyword', '', 'string');
		$phrase	  	= $app->input->get('phrase', 'any', 'string');
		$id_categ	= $app->input->get('id_categ', array(), 'array');
		$proj_type	= $app->input->get('project_type', array(), 'array');
		$id_locate  = $app->input->get('id_location', array(), 'array');
		$budget 	= $app->input->get('budget', '', 'string');
		$status		= $app->input->get('status', 'COM_JBLANCE_OPEN', 'string');
		
		JArrayHelper::toInteger($id_categ);
		JArrayHelper::toInteger($id_locate);
 	
 		$keyword = preg_replace("/\s*,\s*/", ",", $keyword); //remove the spaces before and after the commas(,)
 		switch ($phrase) {
 			case 'exact':
 				$text		= $db->quote('%'.$db->escape($keyword, true).'%', false);
 				$wheres2 	= array();
 				$wheres2[] 	= 'p.project_title LIKE '.$text;
 				$wheres2[] 	= 'ju.biz_name LIKE '.$text;
 				$wheres2[] 	= 'cv.value LIKE '.$text;
 				$wheres2[] 	= 'p.description LIKE '.$text;
 				$queryStrings[] = '(' . implode( ') OR (', $wheres2 ) . ')';
 				break;
 	
 			case 'all':
 			case 'any':
 			default:
 				$words = explode(',', $keyword);
 				$wheres = array();
 				foreach ($words as $word) {
 					$word		= $db->quote('%'.$db->escape($word, true).'%', false);
 					$wheres2 	= array();
 					$wheres2[] 	= 'p.project_title LIKE '.$word;
 					$wheres2[] 	= 'ju.biz_name LIKE '.$word;
 					$wheres2[] 	= 'cv.value LIKE '.$word;
 					$wheres2[] 	= 'p.description LIKE '.$word;
 					$wheres[] 	= implode(' OR ', $wheres2);
 				}
 				$queryStrings[] = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
 				break;
 		}
 	
 		if(count($id_categ) > 0 && !(count($id_categ) == 1 && empty($id_categ[0]))){
 			if(is_array($id_categ)){
 				$miniquery = array();
 				foreach($id_categ as $cat){
 					$miniquery[] = "FIND_IN_SET(".$cat.", p.id_category)";
 				}
 				$querytemp = '('.implode(' OR ', $miniquery).')';
 			}
 			$queryStrings[] = $querytemp;
 		}
 		
 		if(count($id_locate) > 0 && ($id_locate[0] > 1)){
 			$location	 = JTable::getInstance('location', 'Table');
 			$children_id = $location->getChildren($id_locate[0]);
 			JArrayHelper::toInteger($children_id); 	//sanitize array
 			$children_id = implode(",", $children_id);
 			$queryStrings[] = "p.id_location IN (".$children_id.")";
 		}
 		
 		if(count($proj_type) > 0){
 			$wheres3 = array();
 			if(isset($proj_type['fixed']))
 				$wheres3[] = "p.project_type = ".$db->quote($proj_type['fixed']);
 			if(isset($proj_type['hourly']))
 				$wheres3[] = "p.project_type = ".$db->quote($proj_type['hourly']);
 			
 			$queryStrings[] = '((' . implode( ') OR (', $wheres3 ) . '))';
 		}
 		
 		if(!empty($budget)){
 			$buget_exp = explode(',', $budget);
 			$queryStrings[] = "p.budgetmin >= ".$db->quote($buget_exp[0])." AND p.budgetmax <= ".$db->quote($buget_exp[1]);
 		}
 		
 		if($status != 'any'){
 			$queryStrings[] = "p.status=".$db->quote($status);
 		}
 		
 		$queryStrings[] = "p.approved=1";
 		$queryStrings[] = "p.start_date < ".$db->quote($now);
 		
 		$where =  implode (' AND ', $queryStrings);
 	
 		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
 		$limitstart	= $app->input->get('limitstart', 0, 'int');
 	
 		$query ="SELECT DISTINCT p.*,(TO_DAYS(p.start_date) - TO_DAYS(NOW())) AS daydiff FROM #__jblance_project p".
 				" LEFT JOIN #__jblance_user ju ON p.publisher_userid = ju.user_id".
 				" LEFT JOIN #__jblance_custom_field_value cv ON cv.projectid=p.id".
 				" WHERE ".$where.
 				" ORDER BY p.id DESC";
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
 	
 	function getInviteUser(){
 		$app = JFactory::getApplication();
 		$db	= JFactory::getDbo();
 		$where = array();
 		
 		$project_id = $app->input->get('id', 0, 'int');
 		$project 	= JTable::getInstance('project', 'Table');
 		$project->load($project_id);
 		$id_categ = explode(',', $project->id_category);
 		
 		$isOwnedOperation = JblanceHelper::checkOwnershipOfOperation($project_id, 'project');
 		if(!$isOwnedOperation){
 			$msg = JText::sprintf('COM_JBLANCE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE');
 			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link);
 			return false;
 		}
 		
 		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
 		$limitstart	= $app->input->get('limitstart', 0, 'int');
 		
 		if(count($id_categ) > 0 && !(count($id_categ) == 1 && empty($id_categ[0]))){
 			if(is_array($id_categ)){
 				$miniquery = array();
 				foreach($id_categ as $cat){
 					$miniquery[] = "FIND_IN_SET(".$cat.", ju.id_category)";
 				}
 				$querytemp = '('.implode(' OR ', $miniquery).')';
 			}
 			$queryStrings[] = $querytemp;
 		}
 		
 		$queryStrings[] = "u.block=0";
 		
 		$queryStrings[] = "u.id <> ".$db->quote($project->publisher_userid);	// do not list the project owner
 		
 		$where = (count($queryStrings) ? ' WHERE (' . implode( ') AND (', $queryStrings ) . ') ' : '');
 		
 		$query = "SELECT DISTINCT ju.*,u.username,u.name,ug.name AS grpname FROM #__jblance_user ju ".
 				"LEFT JOIN #__users u ON ju.user_id=u.id ".
 				"LEFT JOIN #__jblance_usergroup ug ON ju.ug_id=ug.id ".
 				$where.
 				"ORDER BY u.name";//echo $query;
 		$db->setQuery($query);
 		$db->execute();
 		$total = $db->getNumRows();
 		
 		//if there are no matching users, redirect to project edit page
 		if($total == 0){
 			$msg = JText::sprintf('COM_JBLANCE_NO_MATCHING_SKILLS_RECHOOSE');
 			$link	= JRoute::_('index.php?option=com_jblance&view=project&layout=editproject&id='.$project_id, false);
 			$app->enqueueMessage($msg, 'notice');
 			$app->redirect($link);
 			return false;
 		}
 		
 		jimport('joomla.html.pagination');
 		$pageNav = new JPagination($total, $limitstart, $limit);
 		
 		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
 		$rows = $db->loadObjectList();
 		
 		$return[0] = $rows;
 		$return[1] = $project;
 		$return[2] = $pageNav;
 		return $return;
 	}
 	
 	function getInviteToProject(){
 		$app  = JFactory::getApplication();
 		$db	  = JFactory::getDbo();
 		$user = JFactory::getUser();
 	
 		$query = 'SELECT id AS value, project_title AS text, invite_user_id FROM #__jblance_project p '.
 				'WHERE p.publisher_userid='.$db->quote($user->id).' AND p.status='.$db->quote('COM_JBLANCE_OPEN').' AND p.approved=1 '.
 				'ORDER BY p.id DESC';
 		$db->setQuery($query);
 		$rows = $db->loadObjectList();
 	
 		$return[0] = $rows;
 		return $return;
 	
 	}
 	
 	/* Misc Functions */
 	function countBids($id){
 		$db = JFactory::getDbo();
 		$row = JTable::getInstance('project', 'Table');
 		$row->load($id);
 		
 		//for nda projects, bid count should include only signed bids
 		$ndaQuery = 'TRUE';
 		if($row->is_nda)
 			$ndaQuery = "is_nda_signed=1";
 		
 		$query = "SELECT COUNT(*) FROM #__jblance_bid WHERE project_id = ".$db->quote($id)." AND ".$ndaQuery;
 		$db->setQuery($query);
 		$total = $db->loadResult();
 		return $total;
 	}
 	
 	/* function getRate($pid, $userid){
 		$db = JFactory::getDbo();
 		$query = "SELECT id,quality_clarity FROM #__jblance_rating WHERE project_id = ".$db->quote($pid)." AND target =".$db->quote($userid);
 		$db->setQuery($query);
 		$rate = $db->loadObject();
 		return $rate;
 	} */
 	
 	function getLabelProjectStatus($status){
 		
 		if($status == 'COM_JBLANCE_OPEN')
 			$statusLabel = 'label label-success';
 		elseif($status == 'COM_JBLANCE_FROZEN')
 			$statusLabel = 'label label-warning';
 		elseif($status == 'COM_JBLANCE_CLOSED')
 			$statusLabel = 'label label-info';
 		elseif($status == 'COM_JBLANCE_EXPIRED')
 			$statusLabel = 'label label-danger';
 		
 		return '<span class="'.$statusLabel.'">'.JText::_($status).'</span>';
 	}
 	
 	function getMaxMinBudgetLimit($type){
 		$db = JFactory::getDbo();
 		$query = "SELECT 0 minlimit, MAX(budgetmax) maxlimit FROM #__jblance_budget ".
				 "WHERE project_type=".$db->quote($type);
 		$db->setQuery($query);
 		$limit = $db->loadObject();
 		return $limit;
 	}
 	
  	public static function hasRated($project_id, $actor){
 		$db = JFactory::getDbo();

 		$query = "SELECT COUNT(*) FROM #__jblance_rating WHERE project_id = ".$db->quote($project_id)." AND actor =".$db->quote($actor)." AND type='COM_JBLANCE_PROJECT'";
 		$db->setQuery($query);
		$count = $db->loadResult();
 		if($count > 0)
 			return 1;
 		else
 			return 0;
 	}
 }