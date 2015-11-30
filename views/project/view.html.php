<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	23 March 2012
 * @file name	:	views/project/view.html.php
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
class JblanceViewProject extends JViewLegacy {

	function display($tpl = null){
		
		$app  	= JFactory::getApplication();
		$layout = $app->input->get('layout', 'editproject', 'string');
		$model	= $this->getModel();
		$user	= JFactory::getUser();
		
		JblanceHelper::isAuthenticated($user->id, $layout);
		
		if($layout == 'editproject'){
			$return = $model->getEditProject();
			$row = $return[0];
			$projfiles = $return[1];
			$fields = $return[2];
			
			$this->assignRef('row', $row);
			$this->assignRef('projfiles', $projfiles);
			$this->assignRef('fields', $fields);
		}
		elseif($layout == 'showmyproject'){
			$return = $model->getShowMyProject();
			$rows = $return[0];
			$pageNav = $return[1];
			$this->assignRef('rows', $rows);
			$this->assignRef('pageNav', $pageNav);
		}
		elseif($layout == 'listproject'){
			$return = $model->getListProject();
			$rows = $return[0];
			$pageNav = $return[1];
			$params = $return[2];
			$this->assignRef('rows', $rows);
			$this->assignRef('pageNav', $pageNav);
			$this->assignRef('params', $params);
		}
		elseif($layout == 'detailproject'){
			$return 	= $model->getDetailProject();
			$row 		= $return[0];
			$projfiles  = $return[1];
			$bids 		= $return[2];
			$fields 	= $return[3];
			$forums 	= $return[4];
			
			$this->assignRef('row', $row);
			$this->assignRef('projfiles', $projfiles);
			$this->assignRef('bids', $bids);
			$this->assignRef('fields', $fields);
			$this->assignRef('forums', $forums);
			
			//set page title
			$doc = JFactory::getDocument();
			$doc->setTitle($row->project_title);
			if($row->metadesc) $doc->setMetaData('description', $row->metadesc);
			if($row->metakey)  $doc->setMetaData('keywords', $row->metakey);
		}
		elseif($layout == 'placebid'){
			$return = $model->getPlaceBid();
			$project = $return[0];
			$bid = $return[1];
			$this->assignRef('project', $project);
			$this->assignRef('bid', $bid);
		}
		elseif($layout == 'showmybid'){
			$return = $model->getShowMyBid();
			$rows = $return[0];
			$this->assignRef('rows', $rows);
		}
		elseif($layout == 'pickuser'){
			$return = $model->getPickUser();
			$rows = $return[0];
			$project = $return[1];
			$this->assignRef('rows', $rows);
			$this->assignRef('project', $project);
		}
		elseif($layout == 'rateuser'){
			$return = $model->getRateUser();
			//$rate = $return[0];
			$project = $return[1];
			//$this->assignRef('rate', $rate);
			$this->assignRef('project', $project);
		}
		elseif($layout == 'searchproject'){
			$return = $model->getSearchProject();
			$rows = $return[0];
			$pageNav = $return[1];
		
			$this->assignRef('rows', $rows);
			$this->assignRef('pageNav', $pageNav);
		}
		elseif($layout == 'inviteuser'){
			$return  = $model->getInviteUser();
			$rows 	 = $return[0];
			$project = $return[1];
			$pageNav = $return[2];
		
			$this->assignRef('rows', $rows);
			$this->assignRef('project', $project);
			$this->assignRef('pageNav', $pageNav);
		}
		elseif($layout == 'invitetoproject'){
			$return  = $model->getInviteToProject();
			$projects 	 = $return[0];
			$this->assignRef('projects', $projects);
		}
		elseif($layout == 'projectprogress'){
			$return  = $model->getProjectProgress();
			$row = $return[0];
			$messages = $return[1];
			
			$this->assignRef('row', $row);
			$this->assignRef('messages', $messages);
		}
		
        parent::display($tpl);

	}
}