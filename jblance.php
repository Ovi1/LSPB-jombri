<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	12 March 2012
 * @file name	:	jblance.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

include_once(JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/jblance.php');	//include this helper file to make the class accessible in all other PHP files
include_once(JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/link.php');	//include this helper file to make the class accessible in all other PHP files
JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jblance/tables');

if(!defined('JBLANCE_FREE_MODE')){
	define('JBLANCE_FREE_MODE', 0);		//It's assumed in JB Lance that, it is never in free mode.
}

// Execute the task.
$controller	= JControllerLegacy::getInstance('Jblance');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

include_once('components/com_jblance/views/joombricredit.php');