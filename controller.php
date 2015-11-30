<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	12 March 2012
 * @file name	:	controller.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class JblanceController extends JControllerLegacy {
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false){
		// Get the document object.
		$document	= JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName	 = JFactory::getApplication()->input->get('view', 'guest');
		$vFormat = $document->getType();
		$lName	 = JFactory::getApplication()->input->get('layout', 'showfront');

		if ($view = $this->getView($vName, $vFormat)) {
			// Do any specific processing by view.
			switch ($vName) {
				
				case 'guest':
					$model = $this->getModel($vName);
					break;

				case 'membership':
					$model = $this->getModel($vName);
					break;
				
				case 'message':
					$model = $this->getModel($vName);
					break;
				
				case 'project':
					$model = $this->getModel($vName);
					break;
					
				case 'service':
					$model = $this->getModel($vName);
					break;

				case 'user':
					$model = $this->getModel($vName);
					break;
					
				default:
					$model = $this->getModel('Guest');
					break;
			}

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			//$view->assignRef('document', $document);
			$view->document = $document;

			$view->display();
		}
	}
	}