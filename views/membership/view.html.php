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
?>
<?php include_once(JPATH_COMPONENT.'/views/jbmenu.php'); ?>
<div class="sp10">&nbsp;</div>
<?php
/**
 * HTML View class for the Jblance component
 */
class JblanceViewMembership extends JViewLegacy {
	
	function display($tpl = null){
		$app  	= JFactory::getApplication();
		$layout = $app->input->get('layout', 'planadd', 'string');
		$model	= $this->getModel();
		$user	= JFactory::getUser();
		$userid = $user->id;
		
		JblanceHelper::isAuthenticated($userid, $layout);
		
		if($layout == 'planadd'){
			$return = $model->getPlanAdd();
			$rows = $return[0];
			$plans = $return[1];
		
			$this->assignRef('rows', $rows);
			$this->assignRef('plans', $plans);
		}
		elseif($layout == 'planhistory'){
			$return = $model->getPlanHistory();
			$rows = $return[0];
			$finish = $return[1];
			$pageNav = $return[2];
			
			$this->assignRef('rows', $rows);
			$this->assignRef('finish', $finish);
			$this->assignRef('pageNav', $pageNav);
		}
		elseif($layout == 'check_out'){
			
			$type = $app->input->get('type', 'plan', 'string'); //get the type of purchase to call different functions
			
			if($type == 'plan'){
				$return = $model->getPlanCheckout();
				$subscr = $return[0];
				$plan = $return[1];
			
				$this->assignRef('plan', $plan);
				$this->assignRef('subscr', $subscr);
			}
			elseif($type == 'deposit'){
				$return = $model->getDepositCheckout();
				$deposit = $return[0];
			
				$this->assignRef('deposit', $deposit);
			}
		}
		elseif($layout == 'bank_transfer'){
			$type = $app->input->get('type', 'plan', 'string'); //get the type of purchase to call different functions
			
			if($type == 'plan'){
				$return = $model->getPlanBankTransfer();
				$subscr = $return[0];
				$plan = $return[1];
				$payconfig = $return[2];
			
				$this->assignRef('plan', $plan);
				$this->assignRef('subscr', $subscr);
				$this->assignRef('payconfig', $payconfig);
			}
			elseif($type == 'deposit'){
				$return = $model->getDepositBankTransfer();
				$deposit = $return[0];
				$payconfig = $return[1];
			
				$this->assignRef('deposit', $deposit);
				$this->assignRef('payconfig', $payconfig);
			}
		}
		elseif($layout == 'transaction'){
			$return = $model->getTransaction();
		
			$rows = $return[0];
			$pageNav = $return[1];
			$last_trans = $return[2];
		
			$this->assignRef('rows', $rows);
			$this->assignRef('pageNav', $pageNav);
			$this->assignRef('last_trans', $last_trans);
		}
		elseif($layout == 'withdrawfund'){
			$return = $model->getWithdrawFund();
			$paymodes = $return[0];
			$form = $return[1];
			$this->assignRef('paymodes', $paymodes);
			$this->assignRef('form', $form);
		}
		elseif($layout == 'escrow'){
			$return = $model->getEscrow();
			$lists = $return[0];
			$this->assignRef('lists', $lists);
		}
		elseif($layout == 'managepay'){
			$return = $model->getManagepay();
			$escrow_out = $return[0];
			$escrow_in = $return[1];
			$withdraws = $return[2];
			$deposits = $return[3];
			$pageNavWithdraw = $return[4];
			$pageNavDeposit = $return[5];
		
			$this->assignRef('escrow_out', $escrow_out);
			$this->assignRef('escrow_in', $escrow_in);
			$this->assignRef('withdraws', $withdraws);
			$this->assignRef('deposits', $deposits);
			$this->assignRef('pageNavWithdraw', $pageNavWithdraw);
			$this->assignRef('pageNavDeposit', $pageNavDeposit);
		}
		elseif($layout == 'invoice'){
			$return = $model->getInvoice();
			$row = $return[0];
			$escrows = $return[1];
			//$billingAddress = $return[1];
		
			$this->assignRef('row', $row);
			$this->assignRef('escrows', $escrows);
			//$this->assignRef('billing', $billingAddress);
		}
		elseif($layout == 'plandetail'){
			$return = $model->getPlanDetail();
			$row = $return[0];
			$this->assignRef('row', $row);
		}
		elseif($layout == 'thankpayment'){
			$return = $model->getThankPayment();
			$row = $return[0];
			$this->assignRef('row', $row);
		}
		
		
		parent::display($tpl);
	}
	
	
}