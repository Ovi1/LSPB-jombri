<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	04 November 2014
 * @file name	:	views/service/view.html.php
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
 <div class="sp10">&nbsp;</div>
 <?php
 /**
  * HTML View class for the Jblance component
  */
 class JblanceViewService extends JViewLegacy {
 	
 	function display($tpl = null){
 		$app  	= JFactory::getApplication();
 		$layout = $app->input->get('layout', 'myservice', 'string');
 		$model	= $this->getModel();
 		$user	= JFactory::getUser();
 	
 		JblanceHelper::isAuthenticated($user->id, $layout);
 		
 		if($layout == 'myservice'){
 			$return  = $model->getMyService();
 			$rows 	 = $return[0];
			$pageNav = $return[1];
			
			$this->assignRef('rows', $rows);
			$this->assignRef('pageNav', $pageNav);
 		}
 		elseif($layout == 'listservice'){
 			$return  = $model->getListService();
 			$rows 	 = $return[0];
			$pageNav = $return[1];
			
			$this->assignRef('rows', $rows);
			$this->assignRef('pageNav', $pageNav);
 		}
 		elseif($layout == 'servicebought'){
 			$return  = $model->getServiceBought();
 			$rows 	 = $return[0];
			$pageNav = $return[1];
			
			$this->assignRef('rows', $rows);
			$this->assignRef('pageNav', $pageNav);
 		}
 		elseif($layout == 'serviceprogress'){
 			$return   = $model->getServiceProgress();
 			$row 	  = $return[0];
 			$messages = $return[1];
			
			$this->assignRef('row', $row);
			$this->assignRef('messages', $messages);
 		}
 		elseif($layout == 'servicesold'){
 			$return  = $model->getServiceSold();
 			$rows 	 = $return[0];
			$pageNav = $return[1];
			
			$this->assignRef('rows', $rows);
			$this->assignRef('pageNav', $pageNav);
 		}
 		elseif($layout == 'editservice'){
 			$return = $model->getEditService();
 			$row 	= $return[0];
 		
 			$this->assignRef('row', $row);
 		}
 		elseif($layout == 'viewservice'){
 			$return  = $model->getViewService();
 			$row 	 = $return[0];
 			$ratings = $return[1];
 		
 			$this->assignRef('row', $row);
 			$this->assignRef('ratings', $ratings);
 		}
 		elseif($layout == 'rateservice'){
 			$return = $model->getRateService();
 			$row 	= $return[0];
 			
 			$this->assignRef('row', $row);
 		}
 		
 		parent::display($tpl);
 		
 		}
 }
 