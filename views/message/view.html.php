<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	30 May 2012
 * @file name	:	views/message/view.html.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 jimport('joomla.application.component.view');

 $document = JFactory::getDocument();
 $direction = $document->getDirection();
 $config = JblanceHelper::getConfig();
 
 if($config->loadBootstrap){
 	JHtml::_('bootstrap.loadCss', true, $direction);
 }
 
 $document->addStyleSheet("components/com_jblance/css/style.css");
 if($direction === 'rtl')
 	$document->addStyleSheet("components/com_jblance/css/style-rtl.css");
?>
<?php include_once(JPATH_COMPONENT.'/views/jbmenu.php'); ?>
<?php
/**
 * HTML View class for the Jblance component
 */
class JblanceViewMessage extends JViewLegacy {

	function display($tpl = null){
		$app  	= JFactory::getApplication();
		$layout = $app->input->get('layout', 'inbox', 'string');
		$model	= $this->getModel();
		$user	= JFactory::getUser();
		
		JblanceHelper::isAuthenticated($user->id, $layout);
		
		if($layout == 'inbox'){
			$return = $model->getInbox();
			$msgs = $return[0];
			$out_msgs = $return[1];
			$newMsg = $return[2];
			$newOutMsg = $return[3];
			
			$this->assignRef('msgs', $msgs);
			$this->assignRef('out_msgs', $out_msgs);
			$this->assignRef('newMsg', $newMsg);
			$this->assignRef('newOutMsg', $newOutMsg);
		}
		if($layout == 'read'){
			$return = $model->getMessageRead();
			$parent = $return[0];
			$rows = $return[1];
			
			$this->assignRef('parent', $parent);
			$this->assignRef('rows', $rows);
		}
		if($layout == 'compose'){
			/* $return = $model->getMessageRead();
			$parent = $return[0];
			$rows = $return[1];
			
			$this->assignRef('parent', $parent);
			$this->assignRef('rows', $rows); */
		}
		
        parent::display($tpl);

	}
}