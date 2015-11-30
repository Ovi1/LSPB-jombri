<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	30 May 2012
 * @file name	:	controllers/message.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 jimport('joomla.application.controller');

class JblanceControllerMessage extends JControllerLegacy {
	
	function __construct(){
		parent :: __construct();
	}
	
	function sendMessage(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  		= JFactory::getApplication();
		$now 		= JFactory::getDate();
		$user 		= JFactory::getUser();
		$post 		= $app->input->post->getArray();
		$message 	= JTable::getInstance('message', 'Table');
		$project_id = $app->input->get('project_id', 0, 'int');		// this id is either project id or service order id depending on the "type"
		$parent_id 	= $app->input->get('parent', 0, 'int');
		$type 		= $app->input->get('type', 'COM_JBLANCE_PROJECT', 'string');
		$return 	= $app->input->get('return', '', 'string');
		$jbmail 	= JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		
		$message->date_sent = $now->toSql();
		
		//save the file attachment `if` checked
		$chkAttach = $app->input->get('chk-uploadmessage', 0, 'int');
		$attachedFile = $app->input->get('attached-file-uploadmessage', '', 'string');
		
		if($chkAttach){
			$message->attachment = $attachedFile;
		}
		else {
			$attFile = explode(';', $attachedFile);
			$filename = $attFile[1];
			$delete = JBMESSAGE_PATH.'/'.$filename;
			if(JFile::exists($delete))
				unlink($delete);
		}
		
		//check if messages to be moderated
		$config 		= JblanceHelper::getConfig();
		$reviewMessages = $config->reviewMessages;
		if($reviewMessages)
			$message->approved = 0;
		
		if(!$message->save($post)){
			JError::raiseError(500, $message->getError());
		}
		
		//if message does not require moderation, send PM notification email to recipient else send to admin for approval
		if($reviewMessages)
			$jbmail->sendAdminMessagePendingApproval($message->id);
		else 
			$jbmail->sendMessageNotification($message->id);
		
		$msg	= JText::_('COM_JBLANCE_MESSAGE_SENT_SUCCESSFULLY');
		$return = base64_decode($return);
		/* if($type == 'COM_JBLANCE_PROJECT' || $type == 'COM_JBLANCE_OTHER')
			$return	= JRoute::_('index.php?option=com_jblance&view=message&layout=read&id='.$parent_id, false);
		else
			$return	= JRoute::_('index.php?option=com_jblance&view=service&layout=serviceprogress&id='.$project_id, false); */
		$this->setRedirect($return, $msg);
	}
	
	function sendCompose(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  			= JFactory::getApplication();
		$now 			= JFactory::getDate();
		$user 			= JFactory::getUser();
		$post 			= $app->input->post->getArray();
		$message 		= JTable::getInstance('message', 'Table');
		$recipient 		= $app->input->get('recipient', '', 'string');
		$recipientInfo 	= JFactory::getUser($recipient);		//get the recipient info from the recipient's username
		$jbmail 		= JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		
		//check if the recipient info is valid/username exists
		if(empty($recipientInfo)){
			$msg = JText::_('COM_JBLANCE_INVALID_USERNAME');
			$link	= JRoute::_('index.php?option=com_jblance&view=message&layout=compose', false);
			$this->setRedirect($link, $msg, 'error');
			return false;
		}
		
		$message->date_sent = $now->toSql();
		$message->idFrom = $user->id;
		$message->idTo = $recipientInfo->id;
		
		//save the file attachment `if` checked
		$chkAttach = $app->input->get('chk-uploadmessage', 0, 'int');
		$attachedFile = $app->input->get('attached-file-uploadmessage', '', 'string');
		
		if($chkAttach){
			$message->attachment = $attachedFile;
		}
		else {
			$attFile = explode(';', $attachedFile);
			$filename = $attFile[1];
			$delete = JBMESSAGE_PATH.'/'.$filename;
			if(JFile::exists($delete))
				unlink($delete);
		}
		
		//check if messages to be moderated
		$config 		= JblanceHelper::getConfig();
		$reviewMessages = $config->reviewMessages;
		if($reviewMessages)
			$message->approved = 0;
		
		if(!$message->save($post)){
			JError::raiseError(500, $message->getError());
		}
		
		//send PM notification email
		$post['idFrom'] = $user->id;
		$post['idTo'] = $recipientInfo->id;
		
		//if message does not require moderation, send PM notification email to recipient else send to admin for approval
		if($reviewMessages)
			$jbmail->sendAdminMessagePendingApproval($message->id);
		else
			$jbmail->sendMessageNotification($message->id);
		
		$msg	= JText::_('COM_JBLANCE_MESSAGE_SENT_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=message&layout=inbox', false);
		$this->setRedirect($return, $msg);
	}
	
	function saveReport(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  	= JFactory::getApplication();
		$link 	= $app->input->get('link', '', 'string');
		
		$reportHelper = JblanceHelper::get('helper.report');		// create an instance of the class ReportHelper
		$result = $reportHelper->createReport();
		
		$msg = (!$result) ? JText::_('COM_JBLANCE_ALREADY_REPORTED') : JText::_('COM_JBLANCE_REPORTED_SUCCESSFULLY');
		//echo $msg;exit;
		$return	= base64_decode($link);
		$this->setRedirect($return, $msg);
	}
	
	
	//AJAX functions
	//2.Hide/remove Message
	function processMessage(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		JblanceHelper::processMessage();
	}
	
	function getAutocompleteUsername(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		$app 	= JFactory::getApplication();
		$db  	= JFactory::getDbo();
		$config = JblanceHelper::getConfig();
		
		$showUsername	= $config->showUsername;
		$nameOrUsername = ($showUsername) ? 'username' : 'name';
		
		$search = $app->input->get('recipient', '', 'string');
		$result = array();
		
		// Some simple validation
		if (is_string($search) && strlen($search) > 2 && strlen($search) < 64){
			$query = "SELECT u.username,u.name FROM #__users u ".
					 "WHERE u.block=0 AND u.username LIKE ".$db->quote('%'.$search.'%')." OR u.name LIKE ".$db->quote('%'.$search.'%');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
		
			for($i = 0; $i < count($rows); $i++){
				$row = $rows[$i];
				$result[$row->username] = $row->$nameOrUsername;
			}
		}
		
		echo json_encode($result);
		exit;
		
	}
	
	function attachFile(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		JBMediaHelper::messageAttachFile();
	}
	
	//download file
	function download(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
	
		JBMediaHelper::downloadFile();
	}
}