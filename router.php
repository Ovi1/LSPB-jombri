<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	12 March 2012
 * @file name	:	controllers/admconfig.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 require_once(JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/jblance.php');
/**
 * @param	array	A named array
 * @return	array
 */
function JblanceBuildRoute(&$query){
	$segments = array();
	$db = JFactory::getDbo();
	
	$task = null;
	//$segments[0] would either be view or task only. Both are not set at once.
	 if(isset($query['view'])){
	 	$segments[] = $query['view']; 
		unset($query['view']); 
	}
	else {
		if(isset($query['task'])){
		 	$segments[] = $query['task'];
			$task = $query['task'];
			unset($query['task']); 
		}
	}
	
	$layout = '';
	//$segments[1] would be layout
	if(isset($query['layout'])){
		$segments[] = $query['layout'];
		$layout = $query['layout'];
		unset($query['layout']);
	};
	
	//$segments[2] would be id
	if(isset($query['id'])){
		$id = $query['id'];
		$q = null;
		switch($layout){
			case 'detailproject':
			case 'editproject':
			case 'placebid':
			case 'pickuser':
			case 'inviteuser':
				$q = "SELECT project_title FROM #__jblance_project WHERE id=".$db->quote($id);
				break;
			case 'viewprofile':
				$q = "SELECT username FROM #__users WHERE id=".$db->quote($id);
				break;
			case 'viewservice';
			case 'editservice';
				$q = "SELECT service_title from #__jblance_service where id=".$db->quote($id);
				break;
		}
		if(!empty($q)){
			$db->setQuery($q);
			$title = $db->loadResult();
			if(!empty($title)){
				$title = JblanceHelper::parseTitle($title);
				$segments[] = (!empty($title)) ? $id . '-' . $title : $id;
			}
			else
				$segments[] = $query['id'];
		}
		else
			$segments[] = $query['id'];
		
		unset($query['id']);
	}
	return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Formats:
 *
 * index.php?/jblance/task/id/Itemid
 *
 * index.php?/jblance/view/layout/id/Itemid
 */
function JblanceParseRoute($segments){
	$vars 	= array();
	$count 	= count($segments);
	
	/*
	$segments[0] would either be task or view.
	If $segments[0] is task, then $segments[1] would be id
	If $segments[0] is view, then $segments[1] would be layout and $segments[2] would be id
	*///$segments[0] would either be task or view. Switch case the tasks
	switch ($segments[0]){
		case 'project.reopenproject':
		case 'project.removeproject':
		case 'project.paymentcomplete':
		case 'project.repostproject':
			$vars['task'] = $segments[0];
			$vars['id']	  = $segments[1];
			break;
		case 'project.acceptbid':
		case 'project.denybid':
		case 'project.retractbid':
			$vars['task'] = $segments[0];
			$vars['id']	  = $segments[1];
			break;
		case 'membership.cancelsubscr':
		case 'membership.releaseescrow':
		//case 'membership.cancelescrow':
		case 'membership.acceptescrow':
			$vars['task'] = $segments[0];
			$vars['id']	  = $segments[1];
			break;
		case 'guest.saveusernew':
			$vars['task'] = $segments[0];
			break;
		case 'user.deleteportfolio':
			$vars['task'] = $segments[0];
			$vars['id']	  = $segments[1];
			break;
		case 'message.download':
		case 'project.download':
		case 'user.download':
			$vars['task'] = $segments[0];
			$vars['id']	  = $segments[1];
			break;
		default:
			break;
	}

	$layout = '';
	if(isset($segments[1]))
		$layout = $segments[1];
	
	$vars['layout'] = $layout;
	
	switch($layout){
		//guest
		case 'register':
			$vars['view'] = 'guest';
			break;
		case 'showfront':
			$vars['view'] = 'guest';
			break;
		case 'usergroupfield':
			$vars['view'] = 'guest';
			break;

		//Membership
		case 'bank_transfer':
			$vars['view'] = 'membership';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'check_out':
			$vars['view'] = 'membership';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'depositfund':
			$vars['view'] = 'membership';
			break;
		case 'escrow':
			$vars['view'] = 'membership';
			break;
		case 'invoice':
			$vars['view'] = 'membership';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'managepay':
			$vars['view'] = 'membership';
			break;
		case 'planadd':
			$vars['view'] = 'membership';
			break;
		case 'plandetail':
			$vars['view'] = 'membership';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'planhistory':
			$vars['view'] = 'membership';
			break;
		case 'thankpayment':
			$vars['view'] = 'membership';
			if($count == 3) $vars['oid']	= $segments[2];
			break;
		case 'transaction':
			$vars['view'] = 'membership';
			break;
		case 'withdrawfund':
			$vars['view'] = 'membership';
			break;
			
		//message
		case 'compose':
			$vars['view'] = 'message';
			break;
		case 'inbox':
			$vars['view'] = 'message';
			break;
		case 'read':
			$vars['view'] = 'message';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'report':
			$vars['view'] = 'message';
			if($count == 3) $vars['id']	= $segments[2];
			break;
			
		//project
		case 'detailproject':
			$vars['view'] = 'project';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'editproject':
			$vars['view'] = 'project';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'inviteuser':
			$vars['view'] = 'project';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'invitetoproject':
			$vars['view'] = 'project';
			if($count == 3) $vars['id'] = $segments[2];
			break;
		case 'listproject':
			$vars['view'] = 'project';
			break;
		case 'pickuser':
			$vars['view'] = 'project';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'placebid':
			$vars['view'] = 'project';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'projectprogress':
			$vars['view'] = 'project';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'rateuser':
			$vars['view'] = 'project';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'searchproject':
			$vars['view'] = 'project';
			break;
		case 'showmybid':
			$vars['view'] = 'project';
			break;
		case 'showmyproject':
			$vars['view'] = 'project';
			break;
			
		//service
		case 'editservice':
			$vars['view'] = 'service';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'listservice':
			$vars['view'] = 'service';
			break;
		case 'myservice':
			$vars['view'] = 'service';
			break;
		case 'rateservice':
			$vars['view'] = 'service';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'servicebought':
			$vars['view'] = 'service';
			break;
		case 'servicesold':
			$vars['view'] = 'service';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'serviceprogress':
			$vars['view'] = 'service';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'viewservice':
			$vars['view'] = 'service';
			if($count == 3) $vars['id']	= $segments[2];
			break;
			
		//user
		case 'dashboard':
			$vars['view'] = 'user';
			break;
		case 'editpicture':
			$vars['view'] = 'user';
			break;
		case 'editprofile':
			$vars['view'] = 'user';
			break;
		case 'notify':
			$vars['view'] = 'user';
			break;
		case 'editportfolio':
			$vars['view'] = 'user';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'userlist':
			$vars['view'] = 'user';
			//if($count == 3) $vars['letter']	= $segments[2];
			break;
		case 'viewportfolio':
			$vars['view'] = 'user';
			if($count == 3) $vars['id']	= $segments[2];
			break;
		case 'viewprofile':
			$vars['view'] = 'user';
			if($count == 3) $vars['id']	= $segments[2];
			break;
	}
	
	if($segments[0] == 'project' && $layout == '')
		$vars['view'] = 'project'; 
	
	return $vars;
}