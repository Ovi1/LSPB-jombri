<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	22 March 2012
 * @file name	:	controllers/user.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 jimport('joomla.application.controller');

class JblanceControllerUser extends JControllerLegacy {
	
	function __construct(){
		parent :: __construct();
	}
	
	//1.Save user Profile
	function saveProfile(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	
		// Initialize variables
		$app  	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$db		= JFactory::getDbo();
		$id		= $app->input->get('id', 0, 'int');
		$post 	= $app->input->post->getArray();
		$jbuser	= JTable::getInstance('jbuser', 'Table');
		//$jbuser->load($id);
		
		$id_category 	= $app->input->get('id_category', '', 'array');
		JArrayHelper::toInteger($id_category);
		if(count($id_category) > 0 && !(count($id_category) == 1 && empty($id_category[0]))){
			$proj_categ = implode(',', $id_category);
		}
		elseif($id_category[0] == 0){
			$proj_categ = 0;
		}
		$post['id_category'] = $proj_categ;
		
		if(!$jbuser->save($post)){
			JError::raiseError(500, $jbuser->getError());
		}
		
		//update the name
		$query = "UPDATE #__users SET name=".$db->quote($post['name'])." WHERE id=".$db->quote($user->id);
		$db->setQuery($query);
		if(!$db->execute()){
			JError::raiseError($db->getErrorNum(), $db->getErrorMsg());
		}
	
		$fields = JblanceHelper::get('helper.fields');		// create an instance of the class fieldsHelper
		$fields->saveFieldValues('profile', $user->id, $post);
	
		//update the privacy post settings to the feed table.
		/*$query = "UPDATE #__jblance_feed f SET f.access=".$post['show_post']." WHERE f.actor=".$db->quote($user->id);
		$db->setQuery($query);
		$db->execute();*/
	
		//Trigger the plugin event to feed the activity - after user save profile
		JPluginHelper::importPlugin('joombri');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onUserSaveProfile', array($jbuser, false));
	
		$link = JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
		$msg = JText::_('COM_JBLANCE_PROFILE_SAVED_SUCCESSFULLY');
		$this->setRedirect($link, $msg);
	}
	
	function savePortfolio(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// Initialize variables
		$app  	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$row	= JTable::getInstance('portfolio', 'Table');
		$post 	= $app->input->post->getArray();
		$id		= $app->input->get('id', 0, 'int');
		
		if($id > 0)
			$row->load($id);
		
		$post['id'] = $id;
		$post['user_id'] = $user->id;
		
		$id_category 	= $post['id_category'];
		if(count($id_category) > 0 && !(count($id_category) == 1 && empty($id_category[0]))){
			$proj_categ = implode(',', $id_category);
		}
		elseif($id_category[0] == 0){
			$proj_categ = 0;
		}
		$post['id_category'] = $proj_categ;
		
		//save the portfolio image file attachment `if` checked
		$chkAttach = $app->input->get('chk-portfoliopicture', 0, 'int');
		$attachedFile = $app->input->get('attached-file-portfoliopicture', '', 'string');
		
		if($chkAttach){
			$post['picture'] = $attachedFile;
			
			//delete if there is already attached
			$attFile = explode(';', $row->picture);
			$filename = $attFile[1];
			$delete = JBPORTFOLIO_PATH.'/'.$filename;
			if(JFile::exists($delete))
				unlink($delete);
		}
		else {
			$attFile = explode(';', $attachedFile);
			$filename = $attFile[1];
			$delete = JBPORTFOLIO_PATH.'/'.$filename;
			if(JFile::exists($delete))
				unlink($delete);
		}
		
		//save the portfolio file attachment `if` checked
		for($i=1; $i<=5; $i++){
			$chkAttach = $app->input->get('chk-portfolioattachment'.$i, 0, 'int');
			$attachedFile = $app->input->get('attached-file-portfolioattachment'.$i, '', 'string');
			$attachmentColumnNum = 'attachment'.$i;
			
			if($chkAttach){
				$post['attachment'.$i] = $attachedFile;
			
				//delete if there is already attached
				$attFile = explode(';', $row->$attachmentColumnNum);
				$filename = $attFile[1];
				$delete = JBPORTFOLIO_PATH.'/'.$filename;
				if(JFile::exists($delete))
					unlink($delete);
			}
			else {
				$attFile = explode(';', $attachedFile);
				$filename = $attFile[1];
				$delete = JBPORTFOLIO_PATH.'/'.$filename;
				if(JFile::exists($delete))
					unlink($delete);
			}
		}
		if(!$row->save($post)){
			JError::raiseError(500, $row->getError());
		}
		
		$msg	= JText::_('COM_JBLANCE_PORTFOLIO_SAVED_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=user&layout=editportfolio', false);
		$this->setRedirect($return, $msg);
	}
	
	//4.Delete Portfolio
	function deletePortfolio(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		//Initialise variables
		$app  	= JFactory::getApplication();
		$id 	= $app->input->get('id', 0, 'int');
		$row	= JTable::getInstance('portfolio', 'Table');
	
		$row->delete($id);
		
		$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=editportfolio', false);
		$msg = JText::_('COM_JBLANCE_PORTFOLIO_DELETED_SUCCESSFULLY');
		$this->setRedirect($link, $msg);
	}
	
	//3.Upload Photo
	function uploadPicture(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		JBMediaHelper::uploadPictureMedia();
	}
	
	function removePicture(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		JBMediaHelper::removePictureMedia();
	}
	
	function cropPicture(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		JBMediaHelper::cropPictureMedia();
	}
	
	function attachPortfolioFile(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		JBMediaHelper::portfolioAttachFile();
	}
	
	function saveNotify(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// Initialize variables
		$app  	= JFactory::getApplication();
		$user 	= JFactory::getUser();
		$row	= JTable::getInstance('notify', 'Table');
		$post 	= $app->input->post->getArray();
		
		$row->user_id = $user->id;
		
		if(!$row->save($post)){
			JError::raiseError(500, $row->getError());
		}
		
		$link = JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
		$msg = JText::_('COM_JBLANCE_EMAIL_NOTIFICATION_PREFERENCES_SAVED_SUCCESSFULLY');
		$this->setRedirect($link, $msg);
	}
	
	/* Misc Functions */
	//1. download file
	function download(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		JBMediaHelper::downloadFile();
	}
	
	//2. Hide/remove feeds
	function processFeed(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
	
		$app  		= JFactory::getApplication();
		$db 	  	= JFactory::getDbo();
		$userid 	= $app->input->get('userid', '', 'int');
		$activityid	= $app->input->get('activityid', '', 'int');
		$type 		= $app->input->get('type', '', 'string');
	
		if($type == 'remove')
			$query	= 'DELETE FROM #__jblance_feed WHERE id='.$db->quote($activityid);
		elseif($type == 'hide')
			$query = "INSERT INTO #__jblance_feed_hide(`activity_id`,`user_id`) VALUES(".$db->quote($activityid).", ".$db->quote($userid).")";
	
		$db->setQuery($query);
		if($db->execute())
			echo 'OK';
		else
			echo 'NO';
		exit;
	}
	
	//3. Set the feeds as read
	function setFeedRead(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
	
		$db   = JFactory::getDbo();
		$user = JFactory::getUser();
	
		$query = "UPDATE #__jblance_feed SET is_read=1 WHERE target=".$db->quote($user->id);
		$db->setQuery($query);
		if($db->execute())
			echo 'OK';
		else
			echo 'NO';
		exit;
	}
	
	//3. Add or remove favourite
	function favourite(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
	
		$app  	  = JFactory::getApplication();
		$db   	  = JFactory::getDbo();
		$user 	  = JFactory::getUser();
		$targetId = $app->input->get('targetId', 0, 'int');
		$action   = $app->input->get('action', 0, 'int');	// 1 for insert; -1 for remove
		$response = array();
	
		if($action == 1)
			$query = "INSERT INTO #__jblance_favourite(`actor`,`target`,`type`) VALUES(".$db->quote($user->id).", ".$db->quote($targetId).", 'profile')";
		else
			$query	= 'DELETE FROM #__jblance_favourite WHERE actor='.$db->quote($user->id).' AND target='.$db->quote($targetId).' AND type='.$db->quote('profile');
		$db->setQuery($query);
		
		if($db->execute()){
			$response['result'] = 'OK';
			if($action == 1)
				$response['msg'] = JText::_('COM_JBLANCE_ADDED_AS_FAVORITE');
			else
				$response['msg'] = JText::_('COM_JBLANCE_REMOVED_FROM_FAVOURITE');
		}
		else
			$response['result'] = 'NO';
		
		echo json_encode($response); exit;
	}
}