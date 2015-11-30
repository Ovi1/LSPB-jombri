<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	16 March 2012
 * @file name	:	views/guest/view.html.php
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

/**
 * HTML View class for the Jblance component
 */
class JblanceViewGuest extends JViewLegacy {

	function display($tpl = null){
		$app  	= JFactory::getApplication();
		$layout = $app->input->get('layout', 'showfront', 'string');
		$model	= $this->getModel();
		
		if($layout == 'showfront'){
			
			$return = $model->getShowFront();
			$userGroups = $return[0];
			$this->assignRef('userGroups', $userGroups);
		}
		elseif($layout == 'usergroupfield'){
			$fields = $model->getUserGroupField();
		
			$this->assignRef('fields', $fields);
		}
		
        parent::display($tpl);

	}
}