<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	21 March 2012
 * @file name	:	models/user.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 jimport('joomla.application.component.model');
 
 class JblanceModelUser extends JModelLegacy {
 	
 	function getDashboard(){
 		$user	= JFactory::getUser();
 		$db 	= JFactory::getDbo();
 		$config = JblanceHelper::getConfig();
 		//$userInfo = $this->getJBuserInfo($user->get('id'));
 		
 		$jbuser = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
 		$userInfo = $jbuser->getUserGroupInfo($user->id, null);
 	
 		// Convert the params field to an array.
 		$registry = new JRegistry;
		$registry->loadString($userInfo->params);
 		$dbElements = $registry->toArray();
 		
 		$limit = $config->feedLimitDashboard;
 		$feeds = JblanceHelper::getFeeds($limit);
 		
 		$pendings = $this->pendingActions($user->id);
 	
 		$return[0] = $dbElements;
 		$return[1] = $userInfo;
 		$return[2] = $feeds;
 		$return[3] = $pendings;
 	
 		return $return;
 	}
 	
 	function pendingActions($userid){
 		$db 				 = JFactory::getDbo();
 		$config 			 = JblanceHelper::getConfig();
 		$enableEscrowPayment = $config->enableEscrowPayment;
 		$result 			 = array();
 		$userType			 = JblanceHelper::getUserType($userid);
 		
 		$link_my_bid 		= JRoute::_('index.php?option=com_jblance&view=project&layout=showmybid');
 		$link_my_project 	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject');
 		$link_project_list 	= JRoute::_('index.php?option=com_jblance&view=project&layout=listproject');
 		$link_my_service	= JRoute::_('index.php?option=com_jblance&view=service&layout=myservice');
 		$link_svc_bought	= JRoute::_('index.php?option=com_jblance&view=service&layout=servicebought');
 		$link_svc_sold		= JRoute::_('index.php?option=com_jblance&view=service&layout=servicesold');
 		$link_managepay		= JRoute::_('index.php?option=com_jblance&view=membership&layout=managepay');
 		
 		//get no of bid offers
 		$query = "SELECT COUNT(*) FROM #__jblance_project p ".
				 "LEFT JOIN #__jblance_bid b ON p.id=b.project_id ".
				 "WHERE b.user_id=".$db->quote($userid)." AND p.assigned_userid=".$db->quote($userid)." AND b.status=".$db->quote('');
 		$db->setQuery($query);
 		$bid_offers = $db->loadResult();
 		if($bid_offers > 0){
 			$link = JHtml::_('link', $link_my_bid, '<i class="material-icons">pageview</i>', array());
 			$result['bid_offers'] = JText::sprintf('COM_JBLANCE_PENDING_X_BID_OFFERS_REQUIRING_ACTION', '<b>'.$bid_offers.'</b>').' '.$link;
 		}
 		
 		//get no of bids and projects
 		$query = "SELECT p.id, COUNT(b.id) bidcount FROM #__jblance_project p ".
				 "INNER JOIN #__jblance_bid b ON p.id=b.project_id ".
				 "WHERE p.publisher_userid=".$db->quote($userid)." AND p.status=".$db->quote('COM_JBLANCE_OPEN')." ". 
				 "GROUP BY p.id";//echo $query;
 		$db->setQuery($query);
 		$projects = $db->loadObjectList();
 		$tot_proj = count($projects);
 		
 		$tot_bids = 0;
 		foreach($projects as $project){
 			$tot_bids += $project->bidcount;
 		}
 		if($tot_proj > 0){
 			$link = JHtml::_('link', $link_my_project, '<i class="material-icons">pageview</i>', array());
 			$result['project_bids'] = JText::sprintf('COM_JBLANCE_PENDING_X_BIDS_FROM_Y_PROJECTS_REQUIRING_ACTION', '<b>'.$tot_bids.'</b>', '<b>'.$tot_proj.'</b>').' '.$link;
 		}
 		
 		// get no of invited projects
 		$query = "SELECT COUNT(*) FROM #__jblance_project p ". 
				 "LEFT JOIN #__jblance_bid b ON p.id=b.project_id ".
				 "WHERE p.status='COM_JBLANCE_OPEN' AND FIND_IN_SET(".$db->quote($userid).", p.invite_user_id) AND b.id IS NULL";
 		$db->setQuery($query);
 		$invites = $db->loadResult();
 		if($invites > 0){
 			$link = JHtml::_('link', $link_project_list, '<i class="material-icons">pageview</i>', array());
 			$result['invites'] = JText::sprintf('COM_JBLANCE_PENDING_X_PROJECT_INVITATION_REQUIRING_ACTION', '<b>'.$invites.'</b>').' '.$link;
 		}
 		
 		//get services yet to be started
 		$query = "SELECT COUNT(*) FROM #__jblance_service_order so ".
				 "LEFT JOIN #__jblance_service s ON so.service_id=s.id ".
				 "WHERE s.user_id=".$db->quote($userid)." AND so.p_status = ''";
 		$db->setQuery($query);
 		$service_not_started = $db->loadResult();
 		if($service_not_started > 0){
 			$link = JHtml::_('link', $link_my_service, '<i class="material-icons">pageview</i>', array());
 			$result['service_not_started'] = JText::sprintf('COM_JBLANCE_PENDING_X_SERVICES_YET_TO_START', '<b>'.$service_not_started.'</b>').' '.$link;
 		}
 		
 		//get services in progress
 		$query = "SELECT COUNT(*) FROM #__jblance_service_order so ".
				 "LEFT JOIN #__jblance_service s ON so.service_id=s.id ".
				 "WHERE s.user_id=".$db->quote($userid)." AND (so.p_status = 'COM_JBLANCE_INITIATED' OR so.p_status = 'COM_JBLANCE_IN_PROGRESS')";
 		$db->setQuery($query);
 		$service_in_progress = $db->loadResult();
 		if($service_in_progress > 0){
 			$link = JHtml::_('link', $link_my_service, '<i class="material-icons">pageview</i>', array());
 			$result['service_in_progress'] = JText::sprintf('COM_JBLANCE_PENDING_X_SERVICES_IN_PROGRESS', '<b>'.$service_in_progress.'</b>').' '.$link;
 		}
 		
 		//get pending rating for project and service
 		if($userType->freelancer){
 			// for project
 			$query = "SELECT COUNT(*) FROM #__jblance_project p ".
 					"LEFT JOIN #__jblance_bid b ON b.project_id=p.id ".
 					"WHERE p.assigned_userid=".$db->quote($userid)." AND b.p_status='COM_JBLANCE_COMPLETED' AND ".
 					"p.id NOT IN (SELECT project_id FROM #__jblance_rating r where r.actor=".$db->quote($userid)." and r.type='COM_JBLANCE_PROJECT')";
 			$db->setQuery($query);
 			$seller_rating = $db->loadResult();
 			
 			if($seller_rating > 0){
 				$link = JHtml::_('link', $link_my_bid, '<i class="material-icons">pageview</i>', array());
 				$result['rating-project'] = JText::sprintf('COM_JBLANCE_PENDING_X_USERS_RATE_BID_REQUIRING_ACTION', '<b>'.$seller_rating.'</b>').' '.$link;
 			}
 			// for service
 			$query = "SELECT COUNT(*) FROM #__jblance_service s ".
					 "LEFT JOIN #__jblance_service_order so ON s.id=so.service_id ".
					 "WHERE s.user_id=".$db->quote($userid)." AND p_status='COM_JBLANCE_COMPLETED' AND ".
					 "so.id NOT IN (SELECT project_id FROM #__jblance_rating r WHERE r.actor=".$db->quote($userid)." AND r.type='COM_JBLANCE_SERVICE')";
 			$db->setQuery($query);
 			$seller_rating = $db->loadResult();
 			
 			if($seller_rating > 0){
 				$link = JHtml::_('link', $link_svc_sold, '<i class="material-icons">pageview</i>', array());
 				$result['rating-service'] = JText::sprintf('COM_JBLANCE_PENDING_X_USERS_RATE_SERVICE_SOLD_REQUIRING_ACTION', '<b>'.$seller_rating.'</b>').' '.$link;
 			}
 		}
 		elseif($userType->buyer){
 			// for project
 			$query = "SELECT COUNT(*) FROM #__jblance_project p ".
					 "LEFT JOIN #__jblance_bid b ON b.project_id=p.id ".
					 "WHERE p.publisher_userid=".$db->quote($userid)." AND b.p_status='COM_JBLANCE_COMPLETED' AND ".
					 "p.id NOT IN (SELECT project_id FROM #__jblance_rating r where r.actor=".$db->quote($userid)." and r.type='COM_JBLANCE_PROJECT')";
 			$db->setQuery($query);
 			$buyer_rating = $db->loadResult();
 			
 			if($buyer_rating > 0){
 				$link = JHtml::_('link', $link_my_project, '<i class="material-icons">pageview</i>', array());
 				$result['rating-project'] = JText::sprintf('COM_JBLANCE_PENDING_X_USERS_RATE_PROJECT_REQUIRING_ACTION', '<b>'.$buyer_rating.'</b>').' '.$link;
 			}
 			// for service
 			$query = "SELECT COUNT(*) FROM #__jblance_service_order so ".
 					 "WHERE so.user_id=".$db->quote($userid)." AND p_status='COM_JBLANCE_COMPLETED' AND ".
 					 "so.id NOT IN (SELECT project_id FROM #__jblance_rating r WHERE r.actor=".$db->quote($userid)." and r.type='COM_JBLANCE_SERVICE')";
 			$db->setQuery($query);
 			$buyer_rating = $db->loadResult();
 			
 			if($buyer_rating > 0){
 				$link = JHtml::_('link', $link_svc_bought, '<i class="material-icons">pageview</i>', array());
 				$result['rating-service'] = JText::sprintf('COM_JBLANCE_PENDING_X_USERS_RATE_SERVICE_BOUGHT_REQUIRING_ACTION', '<b>'.$buyer_rating.'</b>').' '.$link;
 			}
 		}
 		
 		//check if escrow payment is enabled?
 		if($enableEscrowPayment){
	 		//get pending payments
	 		$query = "SELECT COUNT(*) FROM #__jblance_project p ".
					 "WHERE p.publisher_userid=".$db->quote($userid)." AND p.status=".$db->quote('COM_JBLANCE_CLOSED')." AND p.paid_status <> ".$db->quote('COM_JBLANCE_PYMT_COMPLETE');
	 		$db->setQuery($query);
	 		$payment = $db->loadResult();
	 		if($payment > 0){
	 			$link = JHtml::_('link', $link_my_project, '<i class="material-icons">pageview</i>', array());
	 			$result['payment'] = JText::sprintf('COM_JBLANCE_PENDING_X_PAYMENTS_REQUIRING_ACTION', '<b>'.$payment.'</b>').' '.$link;
	 		}
	 		
	 		//pending incoming escrows
	 		$query = "SELECT COUNT(*) FROM #__jblance_escrow e ".
					 "WHERE e.to_id= ".$db->quote($userid)." AND e.status=".$db->quote('COM_JBLANCE_RELEASED');
	 		$db->setQuery($query);
	 		$in_escrow = $db->loadResult();
	 		if($in_escrow > 0){
	 			$link = JHtml::_('link', $link_managepay, '<i class="material-icons">pageview</i>', array());
	 			$result['in_escrow'] = JText::sprintf('COM_JBLANCE_PENDING_X_INCOMING_ESCROW_PAYMENTS_REQUIRING_ACTION', '<b>'.$in_escrow.'</b>').' '.$link;
	 		}
	 		
	 		//pending outgoing escrows
	 		$query = "SELECT COUNT(*) FROM #__jblance_escrow e ".
					 "WHERE e.from_id= ".$db->quote($userid)." AND e.status=".$db->quote('');
	 		$db->setQuery($query);
	 		$out_escrow = $db->loadResult();
	 		if($out_escrow > 0){
	 			$link = JHtml::_('link', $link_managepay, '<i class="material-icons">pageview</i>', array());
	 			$result['out_escrow'] = JText::sprintf('COM_JBLANCE_PENDING_X_OUTGOING_ESCROW_PAYMENTS_REQUIRING_ACTION', '<b>'.$out_escrow.'</b>').' '.$link;
	 		}
 		}
 		
 		return $result;
 	}
 	
 	function getEditProfile(){
 	
 		$user	= JFactory::getUser();
 		$app  = JFactory::getApplication();
 	
 		$jbuser = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
 		$userInfo = $jbuser->getUser($user->id);
 		
 		$jbfields = JblanceHelper::get('helper.fields');		// create an instance of the class FieldsHelper
 		$fields   = $jbfields->getUserGroupTypeFields($userInfo->ug_id);
 	
 		$return[0] = $userInfo;
 		$return[1] = $fields;
 		return $return;
 	}
 	
 	//2.Edit Profile Picture
 	function getEditPicture(){
 		$app  = JFactory::getApplication();
 		$user = JFactory::getUser();
 		$db	  = JFactory::getDbo();
 		
 		//redirect to the appropriate extension based on integration
 		$avatarInteg = JblanceHelper::getAvatarIntegration();
 		if(!($avatarInteg instanceof JoombriAvatarJoombri)){
 			$url = $avatarInteg->getEditURL();
 			if($url) $app->redirect($url);
 		}
 	
 		$row = JTable::getInstance('jbuser', 'Table');
 	
 		$query = "SELECT id FROM #__jblance_user WHERE user_id =".$db->quote($user->id);
 		$db->setQuery($query);
 		$id_jbuser = $db->loadResult();
 		$row->load($id_jbuser);
 	
 		$return[0] = $row;
 		return $return;
 	}
 	
 	//4.Edit Portfolio
 	function getEditPortfolio(){
 		$app 	= JFactory::getApplication();
 		$user	= JFactory::getUser();
 		$db		= JFactory::getDbo();
 		$id 	= $app->input->get('id', 0, 'int');
 	
 		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
 		$limitstart	= $app->input->get('limitstart', 0, 'int');
 	
 		$row = JTable::getInstance('portfolio', 'Table');
 		$row->load($id);
 	
 		$query = "SELECT * FROM #__jblance_portfolio WHERE user_id =".$db->quote($user->id);
 		$db->setQuery($query);
 		$portfolios = $db->loadObjectList();
 	
 		$return[0] = $row;
 		$return[1] = $portfolios;
 		return $return;
 	}
 	
 	function getUserList(){
 		$app = JFactory::getApplication();
 		$db	= JFactory::getDbo();
 		$where = array();
 		$having = $ratesubQuery = '';
 		
 		//redirect to the appropriate extension based on integration
 		$profileInteg = JblanceHelper::getProfile();
 		if(!($profileInteg instanceof JoombriProfileJoombri)){
 			$url = $profileInteg->getUserListURL();
 			if ($url) $app->redirect($url);
 		}
 		
 		$keyword	 = $app->input->get('keyword', '', 'string');
 		$id_category = $app->input->get('id_category', array(), 'array');
 		$id_location = $app->input->get('id_location', array(), 'array');
 		$hourly_rate = $app->input->get('hourly_rate', '', 'string');
 		$rating		 = $app->input->get('rating', 0, 'int');
 		$ordering	 = $app->input->get('ordering', 'u.name asc', 'string');
 		
 		//sanitize arrays to integer
 		JArrayHelper::toInteger($id_category);
 		JArrayHelper::toInteger($id_location);
 		
 		$text		= $db->quote('%'.$db->escape($keyword, true).'%', false);
 		$wheres2 	= array();
 		$wheres2[] 	= 'u.name LIKE '.$text;
 		$wheres2[] 	= 'u.username LIKE '.$text;
 		$wheres2[] 	= 'ju.biz_name LIKE '.$text;
 		$wheres2[] 	= 'cv.value LIKE '.$text;
 		$queryStrings[] = '(' . implode( ') OR (', $wheres2 ) . ')';
 		
 		if(count($id_category) > 0 && !(count($id_category) == 1 && empty($id_category[0]))){
 			if(is_array($id_category)){
 				$miniquery = array();
 				foreach($id_category as $cat){
 					$miniquery[] = "FIND_IN_SET(".$cat.", ju.id_category)";
 				}
 				$querytemp = '('.implode(' OR ', $miniquery).')';
 			}
 			$queryStrings[] = $querytemp;
 		}
 		
 		if(count($id_location) > 0 && ($id_location[0] > 1)){
 			$location	 = JTable::getInstance('location', 'Table');
 			$children_id = $location->getChildren($id_location[0]);
 			JArrayHelper::toInteger($children_id); 	//sanitize array
 			$children_id = implode(",", $children_id);
 			$queryStrings[] = "ju.id_location IN (".$children_id.")";
 		}
 		
 		if(!empty($hourly_rate)){
 			$hourly_rate_exp = explode(',', $hourly_rate);
 			$queryStrings[] = "ju.rate >= ".$db->quote($hourly_rate_exp[0])." AND ju.rate <= ".$db->quote($hourly_rate_exp[1]);
 		}
 		
 		if($rating > 0){
			$having = " HAVING rating >= ".$db->quote($rating); //only top-rated users having rating above 4
 			
 		}
 				
 		// Load the parameters.
		$params = $app->getParams();
 		$ugids = (int)$params->get('ug_id', '');
 		if(!empty($ugids))
 			$queryStrings[] = "ju.ug_id IN (".$ugids.")";
 		
 		$letter = $app->input->get('letter', '', 'string');
 		$queryStrings[] = "u.name LIKE ".$db->quote($letter.'%');
 		
 		$queryStrings[] = "u.block=0";
 			
 		$where = (count($queryStrings) ? ' WHERE (' . implode( ') AND (', $queryStrings ) . ') ' : '');
 		
 		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
 		$limitstart	= $app->input->get('limitstart', 0, 'int');
 		
 		$ratesubQuery = ",(SELECT AVG((quality_clarity+communicate+expertise_payment+professional+hire_work_again)/5) FROM #__jblance_rating
 						WHERE target=ju.user_id AND quality_clarity <> 0) AS rating";
 		
 		$query = "SELECT DISTINCT ju.*,u.username,u.name,ug.name AS grpname".$ratesubQuery." FROM #__jblance_user ju ".
 				 "LEFT JOIN #__users u ON ju.user_id=u.id ".
 				 "LEFT JOIN #__jblance_usergroup ug ON ju.ug_id=ug.id ".
 				 "LEFT JOIN #__jblance_custom_field_value cv ON cv.userid=u.id ".
 				 $where." ".$having." ".
 				 "ORDER BY ".$ordering;//echo $query;
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
 	
 	function getViewPortfolio(){
 		$app  = JFactory::getApplication();
 		/* $db	  = JFactory::getDbo();
 		$user = JFactory::getUser(); */
 		$id   = $app->input->get('id', 0, 'int');
 		
 		$row = JTable::getInstance('portfolio', 'Table');
 		$row->load($id);
 		
 		$return[0] = $row;
 		return $return;
 	}
 	
 	function getViewProfile(){
 	
 		$app 	= JFactory::getApplication();
 		$db 	= JFactory::getDbo();
 		$user	= JFactory::getUser();
 		$userid = $app->input->get('id', $user->id, 'int');		//get the user id from 'get' variable; else default is current user id
 		
 		$isUserBlocked = JFactory::getUser($userid)->block;
 		//redirect the user to dashboard if the user is blocked.
 		if($isUserBlocked == 1){
 			$msg = JText::_('COM_JBLANCE_USER_ACCOUNT_BANNED_VIEWING_PROFILE_NOT_POSSIBLE');
 			$link_dash  = JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->enqueueMessage($msg, 'error');
 			$app->redirect($link_dash);
 		}
 		
 		//redirect to the appropriate extension based on integration
 		$profileInteg = JblanceHelper::getProfile();
 		if(!($profileInteg instanceof JoombriProfileJoombri)){
 			$url = LinkHelper::GetProfileURL($userid, false);
 			if ($url) $app->redirect($url);
 		}
 		
 		$jbuser = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
 		$userInfo = $jbuser->getUser($userid);
 		
 		$jbfields = JblanceHelper::get('helper.fields');		// create an instance of the class FieldsHelper
 		$fields   = $jbfields->getUserGroupTypeFields($userInfo->ug_id);
 	
 		//freelancer projects
 		$query = "SELECT p.*,r.comments FROM #__jblance_project p ".
 	 			 "LEFT JOIN #__jblance_rating r ON r.project_id=p.id ".
 				 "WHERE p.assigned_userid =".$db->quote($userid)." AND p.status='COM_JBLANCE_CLOSED' AND r.target=".$db->quote($userid)." ".
 				 "ORDER BY p.id DESC LIMIT 10";
 		$db->setQuery($query);
 		$fprojects = $db->loadObjectList();
 		
 		//freelancer rating
 		$query = "SELECT AVG(quality_clarity) quality_clarity,AVG(communicate) communicate,AVG(expertise_payment) expertise_payment, ".
 	 			 "AVG(professional) professional,AVG(hire_work_again) hire_work_again FROM #__jblance_rating ".
				 "WHERE target=".$db->quote($userid)." AND quality_clarity<>0 AND rate_type='COM_JBLANCE_FREELANCER'";
 		$db->setQuery($query);
 		$frating = $db->loadObject();
 		
 		//buyer projects
 		$query = "SELECT p.*,r.comments FROM #__jblance_project p ".
 	 			 "LEFT JOIN #__jblance_rating r ON r.project_id=p.id ".
 				 "WHERE p.publisher_userid =".$db->quote($userid)." AND r.target=".$db->quote($userid)." ".
 				 "ORDER BY p.id DESC LIMIT 10";
 		$db->setQuery($query);
 		$bprojects = $db->loadObjectList();
 		
 		//buyer rating
 		$query = "SELECT AVG(quality_clarity) quality_clarity,AVG(communicate) communicate,AVG(expertise_payment) expertise_payment, ".
 				"AVG(professional) professional,AVG(hire_work_again) hire_work_again FROM #__jblance_rating ".
 				"WHERE target=".$db->quote($userid)." AND quality_clarity <> 0 AND rate_type='COM_JBLANCE_BUYER'";
 		$db->setQuery($query);
 		$brating = $db->loadObject();
 		
 		$query = "SELECT * FROM #__jblance_portfolio WHERE user_id =".$db->quote($userid)." AND published=1";
 		$db->setQuery($query);
 		$portfolios = $db->loadObjectList();
 		
 		$return[0] = $userInfo;
 		$return[1] = $fields;
 		$return[2] = $fprojects;
 		$return[3] = $frating;
 		$return[4] = $bprojects;
 		$return[5] = $brating;
 		$return[6] = $portfolios;
 		return $return;
 	}
 	
 	function getNotify(){
 		
 		$user = JFactory::getUser();
 		$db	  = JFactory::getDbo();
 		$row = JTable::getInstance('notify', 'Table');
 		
 		//load the notification preference for the user.
 		$query = "SELECT id FROM #__jblance_notify WHERE user_id =".$db->quote($user->id);
 		$db->setQuery($query);
 		$id_notify = $db->loadResult();
 		$row->load($id_notify);
 		
 		$return[0] = $row;
 		return $return;
 	}
 	
 	/* Misc Functions */
 	
 	//getJobalertFrequency
 	function getSelectUpdateFrequency($var, $default = 'instantly'){
 		$put[] = JHtml::_('select.option',  'instantly', JText::_('COM_JBLANCE_INSTANTLY'));
 		$put[] = JHtml::_('select.option',  'daily', JText::_('COM_JBLANCE_DAILY'));
 		$frequency = JHtml::_('select.genericlist', $put, $var, "class='required form-control'", 'value', 'text', $default);
 		return $frequency;
 	}
 	
 	function getSelectUserlistOrdering($var, $default = '', $attribs){
 		$put[] = JHtml::_('select.option',  'u.name asc', JText::_('COM_JBLANCE_NAME').' &#9650;');
 		$put[] = JHtml::_('select.option',  'u.name desc', JText::_('COM_JBLANCE_NAME').' &#9660;');
 		$put[] = JHtml::_('select.option',  'u.username asc', JText::_('COM_JBLANCE_USERNAME').' &#9650;');
 		$put[] = JHtml::_('select.option',  'u.username desc', JText::_('COM_JBLANCE_USERNAME').' &#9660;');
 		$put[] = JHtml::_('select.option',  'ju.rate asc', JText::_('COM_JBLANCE_HOURLY_RATE').' &#9650;');
 		$put[] = JHtml::_('select.option',  'ju.rate desc', JText::_('COM_JBLANCE_HOURLY_RATE').' &#9660;');
 		$put[] = JHtml::_('select.option',  'rating asc', JText::_('COM_JBLANCE_RATING').' &#9650;');
 		$put[] = JHtml::_('select.option',  'rating desc', JText::_('COM_JBLANCE_RATING').' &#9660;');
 		
 		return JHtml::_('select.genericlist', $put, $var, "$attribs onchange=\"this.form.submit();\"", 'value', 'text', $default);
 	}
 	
 	function getMaxMinHourlyLimit(){
 		$db = JFactory::getDbo();
 		$query = "SELECT 0 minlimit, IF(MAX(rate)=0, 40, MAX(rate)) maxlimit FROM #__jblance_user";
 		$db->setQuery($query);
 		$limit = $db->loadObject();
 		return $limit;
 	}
 }