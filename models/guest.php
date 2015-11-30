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
 
 class JblanceModelGuest extends JModelLegacy {
 	
 	function getShowFront(){

 		$app 	= JFactory::getApplication();
 		$user	= JFactory::getUser();
 		$db 	= JFactory::getDbo();
 		//if the user has JoomBri profile, redirect him to the dashboard
 		$hasJBProfile = JblanceHelper::hasJBProfile($user->id);	
 		if($hasJBProfile){
 			$link = JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
 			$app->redirect($link);
 		}
 		
 		$query	= 'SELECT * FROM #__jblance_usergroup '.
		 		  'WHERE published=1 '.
		 		  'ORDER BY ordering';
 		$db->setQuery($query);
 		$userGroups = $db->loadObjectList();
 	
 		$return[0] = $userGroups;
 		return $return;
 	}
 	
 	function getUserGroupField($userid = null){
 	
 		$app = JFactory::getApplication();
 		$db	= JFactory::getDbo();
 	
 		$session = JFactory::getSession();
 		$ugid = $session->get('ugid', 0, 'register');	//user group id during the registration
 	
 		if(empty($ugid)){
 			if(empty($userid)){		// get the current userid if not passed
 				$user = JFactory::getUser();
 				$userid = $user->id;
 			}
 			$jbuser = JblanceHelper::get('helper.user');
 			$ugroup = $jbuser->getUserGroupInfo($userid, null);
 			$ugid = $ugroup->id;
 		}
 	
 		$jbfields = JblanceHelper::get('helper.fields');		// create an instance of the class FieldsHelper
 		$fields   = $jbfields->getUserGroupTypeFields($ugid);
 	
 		return $fields;
 	}
 	
 	/* Misc Functions */

 	
 	
 }