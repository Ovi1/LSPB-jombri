<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	30 May 2012
 * @file name	:	models/message.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 jimport('joomla.application.component.model');
 
 class JblanceModelMessage extends JModelLegacy {
 	
 	function getInbox(){
		$db 	= JFactory::getDbo();
		$user = JFactory::getUser();
		
		$query = "SELECT * FROM #__jblance_message". 
				 " WHERE (idTo=".$db->quote($user->id)." OR idFrom=".$db->quote($user->id).") AND parent=0 AND deleted=0".
				 " ORDER BY date_sent DESC";
		$db->setQuery($query);
		$msgs = $db->loadObjectList();
		
		//count total received new messages
		$newMsg = 0;
		foreach($msgs as $msg){
			$newMsg += JblanceHelper::countUnreadMsg($msg->id);
		}
		
		$query = "SELECT * FROM #__jblance_message". 
				 " WHERE idFrom=".$db->quote($user->id)." AND parent=0 AND deleted=0".
				 " ORDER BY date_sent DESC";
		$db->setQuery($query);
		$out_msgs = $db->loadObjectList();
		
		//count total sent new messages
		$newOutMsg = 0;
		foreach($out_msgs as $out_msg){
			$newOutMsg += JblanceHelper::countUnreadMsg($out_msg->id);
		}
		
		$return[0] = $msgs;
		$return[1] = $out_msgs;
		$return[2] = $newMsg;
		$return[3] = $newOutMsg;
		return $return;
	}
	
	function getMessageRead(){
		$app  	= JFactory::getApplication();
		$db 	= JFactory::getDbo();
		$id 	= $app->input->get('id', 0, 'int');
		$user	= JFactory::getUser();
		$app 	= JFactory::getApplication();
		
		$query = "SELECT * FROM #__jblance_message WHERE id=".$db->quote($id)." AND deleted=0";//echo $query;
		$db->setQuery($query);
		$parent = $db->loadObject();
		
		//check if the parent message is deleted.
		if(empty($parent)){
			$msg	= JText::_('COM_JBLANCE_THIS_MESSAGE_IS_DELETED');
			$link	= JRoute::_('index.php?option=com_jblance&view=message&layout=inbox');
			$app->enqueueMessage($msg, 'error');
			$app->redirect($link);
			return false;
		}
			
		$query = "SELECT * FROM #__jblance_message 
				  WHERE (id=".$db->quote($id)." OR parent=".$db->quote($id).") AND deleted=0 AND (idFrom = ".$db->quote($user->id)." OR idTo = ".$db->quote($user->id).")
				  ORDER BY id";//echo $query;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		if(empty($rows)){
			$msg = JText::sprintf('COM_JBLANCE_ACCESS_FORBIDDEN');
			$link	= JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false);
			$app->enqueueMessage($msg, 'error');
			$app->redirect($link);
		}
		
		//update the status of the messages to be read
		$query = "UPDATE #__jblance_message SET is_read=1 WHERE idTo=".$db->quote($user->id)." AND (id=".$db->quote($id)." OR parent=".$db->quote($id).")";
		$db->setQuery($query);
		if(!$db->execute()){
			JError::raiseError(500, $db->getError());
		}
		
		$return[0] = $parent;
		$return[1] = $rows;
		return $return;
		
	}
	
	function getSelectReportCategory(){
		$config = JblanceHelper::getConfig();
		$categories = $config->reportCategory;
		$values = explode(";", $categories);
		
		$put[] = JHtml::_('select.option', '', '- '.JText::_('COM_JBLANCE_PLEASE_SELECT').' -');
		foreach($values as $value){
			if($value){
				$put[] = JHtml::_('select.option', $value, JText::_($value), 'value', 'text');
			}
		}
		
		$lists 	= JHtml::_('select.genericlist', $put, 'category', "class='input-medium required' size='1'", 'value', 'text', '');
		return $lists;
	}
 	
 }