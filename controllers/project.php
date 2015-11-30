<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	23 March 2012
 * @file name	:	controllers/project.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 jimport('joomla.application.controller');

class JblanceControllerProject extends JControllerLegacy {
	
	function __construct(){
		parent :: __construct();
	}

	function saveProject(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// Initialize variables
		$app  	= JFactory::getApplication();
		$db 	= JFactory::getDbo();
		$user 	= JFactory::getUser();
		$row	= JTable::getInstance('project', 'Table');
		$post 	= $app->input->post->getArray();
		$id		= $app->input->get('id', 0, 'int');
		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		$isNew	= false;
		$totalAmount = $app->input->get('totalamount', 0, 'float');
		
		$config 		= JblanceHelper::getConfig();
		$reviewProjects = $config->reviewProjects;
		
		//load the project value if the project is 'edit'
		if($id > 0)
			$row->load($id);
		else
			$isNew = true;	// this is a new project
		
		$post['publisher_userid'] = $user->id;
		//$post['description']  = $app->input->get('description', '', 'string');
		
		$id_category 	= $app->input->get('id_category', '', 'array');
		JArrayHelper::toInteger($id_category);
		if(count($id_category) > 0 && !(count($id_category) == 1 && empty($id_category[0]))){
			$proj_categ = implode(',', $id_category);
		}
		elseif($id_category[0] == 0){
			$proj_categ = 0;
		}
		$post['id_category'] = $proj_categ;
		
		if($post['project_type'] == 'COM_JBLANCE_FIXED'){
			$budgetRange = explode('-', $post['budgetrange_fixed']);
		}
		elseif($post['project_type'] == 'COM_JBLANCE_HOURLY'){
			$budgetRange = explode('-', $post['budgetrange_hourly']);
		}
		$post['budgetmin'] = $budgetRange[0];
		$post['budgetmax'] = $budgetRange[1];
		
		//save the commitment value
		$commitment	= $app->input->get('commitment', null, 'array');
		$registry = new JRegistry();
		$registry->loadArray($commitment);
		$post['commitment'] = $registry->toString();
		
		//save the params value
		$params	= $app->input->get('params', null, 'array');
		$registry = new JRegistry();
		$registry->loadArray($params);
		$post['params'] = $registry->toString();
		
		if($isNew){
			$now = JFactory::getDate();
			$post['create_date'] = $now->toSql();
			$post['status'] = 'COM_JBLANCE_OPEN';
			
			//check if the project is to be reviewed by admin. If so, set the approved=0
			if($reviewProjects){
				$post['approved'] = 0;
			}
			
			// deduce `charge per project` if amount is > 0
			$plan = JblanceHelper::whichPlan($user->id);
			$chargePerProject = $plan->buyChargePerProject;
			
			if($chargePerProject > 0){
				$transDtl = JText::_('COM_JBLANCE_CHARGE_PER_PROJECT').' - '.$post['project_title'];
				JblanceHelper::updateTransaction($user->id, $transDtl, $chargePerProject, -1);
				$msg_debit = JText::sprintf('COM_JBLANCE_YOUR_ACCOUNT_DEBITED_WITH_CURRENCY_FOR_POSTING_PROJECT', JblanceHelper::formatCurrency($chargePerProject));
				$app->enqueueMessage($msg_debit);
			}
		}
		
		// deduce the amount from user's account
		if($totalAmount > 0){
			//check if the user has enough fund to promote project
			$totalFund = JblanceHelper::getTotalFund($user->id);
			if($totalFund < $totalAmount){
				$msg = JText::_('COM_JBLANCE_BALANCE_INSUFFICIENT_TO_PROMOTE_PROJECT');
				
				$id_link = '';
				if($post['id'] > 0)
					$id_link = '&id='.$post['id'];
				
				$link	= JRoute::_('index.php?option=com_jblance&view=project&layout=editproject'.$id_link, false);
				$this->setRedirect($link, $msg, 'error');
				return false;
			}
			else {
				$post['profit_additional'] = $row->profit_additional + $post['totalamount'];
				$transDtl = JText::_('COM_JBLANCE_PROJECT_PROMOTION_FEE_FOR').' - '.$post['project_title'];
				JblanceHelper::updateTransaction($user->id, $transDtl, $totalAmount, -1);
			}
		}
		
		if(!$row->save($post)){
			JError::raiseError(500, $row->getError());
		}
		
		//save the custom field value for project
		$fields = JblanceHelper::get('helper.fields');		// create an instance of the class fieldsHelper
		$fields->saveFieldValues('project', $row->id, $post);
		
		JBMediaHelper::uploadFile($post, $row);		//remove and upload files
		
		//update the project posting limit in the plan subscription table
		if($isNew){
			$finance = JblanceHelper::get('helper.finance');		// create an instance of the class FinanceHelper
			$finance->updateProjectLeft($user->id);
		}
		
		//Trigger the plugin event to feed the activity - buyer pick freelancer
		JPluginHelper::importPlugin('joombri');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onProjectAfterSave', array($row, $isNew));
		
		//send email to admin if the project needs review (only for new project)
		if($isNew){
			if($reviewProjects)
				$jbmail->sendAdminProjectPendingApproval($row->id);
		}
		
		// if the project in "private invite, send him to freelancer selection page else send notification"
		if($post['is_private_invite']){
			$msg	= JText::_('COM_JBLANCE_PROJECT_SAVED_SUCCESSFULLY').' : '.$row->project_title;
			$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=inviteuser&id='.$row->id, false);
			$this->setRedirect($return, $msg);
		}
		else {
			//send new project notification if the project doesn't need review
			if(!$reviewProjects)
				$jbmail->sendNewProjectNotification($row->id, $isNew);
			
			$msg	= JText::_('COM_JBLANCE_PROJECT_SAVED_SUCCESSFULLY').' : '.$row->project_title;
			$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject', false);
			$this->setRedirect($return, $msg);
		}
	}
	
	function saveBid(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  		= JFactory::getApplication();
		$jbmail 	= JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		$projhelp 	= JblanceHelper::get('helper.project');		// create an instance of the class ProjectHelper
		$finance 	= JblanceHelper::get('helper.finance');		// create an instance of the class FinanceHelper
		$user 		= JFactory::getUser();
		$post 		= $app->input->post->getArray();
		$id			= $app->input->get('id', 0, 'int');
		$row 		= JTable::getInstance('bid', 'Table');
		$message 	= JTable::getInstance('message', 'Table');
		$project_id = $app->input->get('project_id', 0, 'int');
		$proj_detail = $projhelp->getProjectDetails($project_id);
		$isNew = false;
		$now = JFactory::getDate();
		$config 	 = JblanceHelper::getConfig();
		
		if($id > 0)
			$row->load($id);
		else 
			$isNew = true;
		
		// if the placed bid is new, check for bids left
		$lastSubscr = $finance->getLastSubscription($user->id);
		if($isNew && ($lastSubscr->bids_allowed > 0 && $lastSubscr->bids_left <= 0)){
			$msg = JText::sprintf('COM_JBLANCE_NOT_ALLOWED_TO_BID_PROJECT_LIMIT_EXCEEDED');
			$link	= JRoute::_('index.php?option=com_jblance&view=membership&layout=planadd', false);
			$this->setRedirect($link, $msg, 'error');
			return false;
		}
		
		$post['user_id'] = $user->id;
		$post['outbid'] = $app->input->get('outbid', 0, 'int');
		
		if($isNew){
			$post['bid_date'] = $now->toSql();
			
			// deduce `charge per bid` if amount is > 0
			$plan = JblanceHelper::whichPlan($user->id);
			$chargePerBid = $plan->flChargePerBid;
			
			if($chargePerBid > 0){
				$transDtl = JText::_('COM_JBLANCE_CHARGE_PER_BID').' - '.$proj_detail->project_title;
				JblanceHelper::updateTransaction($user->id, $transDtl, $chargePerBid, -1);
				$msg_debit = JText::sprintf('COM_JBLANCE_YOUR_ACCOUNT_DEBITED_WITH_CURRENCY_FOR_BIDDING_PROJECT', JblanceHelper::formatCurrency($chargePerBid));
				$app->enqueueMessage($msg_debit);
			}
		}
		
		//if the freelancer has denied the bid and is editing, reset the status. Edit option is available if the project is reopened by buyer.
		$row->status = '';
		
		if(!$row->save($post)){
			JError::raiseError(500, $row->getError());
		}
		
		//if the project requires NDA and not signed
		$is_nda_signed = $app->input->get('is_nda_signed', 0, 'int');
		if($proj_detail->is_nda && $is_nda_signed){
			jimport('joomla.filesystem.folder');
			jbimport('pdflib.fpdf');
		
			if(!JFolder::exists(JBBIDNDA_PATH)){
				if(JFolder::create(JBBIDNDA_PATH)){
					if(JFile::exists(JPATH_SITE.'/images/index.html')){
						JFile::copy(JPATH_SITE.'/images/index.html', JBBIDNDA_PATH.'/index.html');
					}
				}
			}
		
			$new_doc = "nda_".$row->id."_".$proj_detail->id."_".strtotime("now").".pdf";	//file name format: nda_BIDID_PROJECTID_time.pdf
			$dest = JBBIDNDA_PATH.'/'.$new_doc;
		
			//replace texts in the file
			$dformat = $config->dateFormat;
			$ndaFile = JPATH_COMPONENT.'/images/nda.txt';
			$ndaText = file_get_contents($ndaFile);
			
			$siteURL 	 	= JURI::root();
			$projectName 	= $proj_detail->project_title;
			$startDate 		= JHtml::_('date', $proj_detail->start_date, $dformat.' H:i:s', false);
			$bidderName 	= JFactory::getUser($row->user_id)->name;
			$bidDate 		= JHtml::_('date', $row->bid_date, $dformat.' H:i:s', false);
			$publisherName 	= JFactory::getUser($proj_detail->publisher_userid)->name;
		
			$tags 	= array("[SITEURL]", "[PROJECTNAME]", "[STARTDATE]", "[BIDDERNAME]", "[BIDDATE]", "[PUBLISHERNAME]");
			$tagsValues = array("$siteURL", "$projectName", "$startDate", "$bidderName", "$bidDate", "$publisherName");
			
			$ndaText = str_replace($tags, $tagsValues, $ndaText);
		
			$pdf = new PDF();
			$title = JText::_('COM_JBLANCE_NON_DISCLOSURE_AGREEMENT');
			$pdf->SetTitle($title);
		
			$pdf->PrintChapter($ndaText);
			$pdf->Output($dest, 'F');
			
			$row->attachment = $title.';'.$new_doc;
		}
		
		// save the changes after updating nda
		if(!$row->store()){
			JError::raiseError(500, $row->getError());
		}
		$row->checkin();
		
		//update the project posting limit in the plan subscription table
		if($isNew){
			$finance = JblanceHelper::get('helper.finance');		// create an instance of the class FinanceHelper
			$finance->updateBidsLeft($user->id);
		}
		
		//save and send PM
		$send_pm = $app->input->get('sendpm', 0, 'int');
		if($send_pm){
			//save the file attachment `if` checked
			$chkAttach = $app->input->get('chk-uploadmessage', 0, 'int');
			$attachedFile = $app->input->get('attached-file-uploadmessage', '', 'string');
			
			if($chkAttach){
				$post['attachment'] = $attachedFile;
			}
			else {
				$attFile = explode(';', $attachedFile);
				$filename = $attFile[1];
				$delete = JBMESSAGE_PATH.'/'.$filename;
				if(JFile::exists($delete))
					unlink($delete);
			}
			
			$post['date_sent'] = $now->toSql();
			$post['idFrom'] = $user->id;
			$post['idTo'] = $proj_detail->publisher_userid;
			$post['type'] = 'COM_JBLANCE_PROJECT';
			
			//check if messages to be moderated
			$reviewMessages = $config->reviewMessages;
			if($reviewMessages)
				$post['approved'] = 0;

			if(!$message->save($post)){
				JError::raiseError(500, $message->getError());
			}
			
			//if message does not require moderation, send PM notification email to recipient else send to admin for approval
			if($reviewMessages)
				$jbmail->sendAdminMessagePendingApproval($message->id);
			else
				$jbmail->sendMessageNotification($message->id);
		}
		
		//send mail to publisher and outbid notify
		/* if($isNew){
			$jbmail->sendNewBidNotification($row->id, $project_id);
		} */
		$jbmail->sendNewBidNotification($row->id, $project_id, $isNew);
		
		//send out bid notification to bidders with higher amount
		$jbmail->sendOutBidNotification($row->id, $project_id);
		
		//Trigger the plugin event to feed the activity - buyer pick freelancer
		JPluginHelper::importPlugin('joombri');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBidAfterSave', array($row, $proj_detail->publisher_userid, $isNew));
		
		$msg	= ($isNew) ? JText::_('COM_JBLANCE_BID_PLACED_SUCCESSFULLY') : JText::_('COM_JBLANCE_BID_EDITED_SUCCESSFULLY');
		$msg   .= ' - '.$proj_detail->project_title;
		$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmybid', false);
		$this->setRedirect($return, $msg);
	}
	
	function savePickUser(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// Initialize variables
		$app  	= JFactory::getApplication();
		$db 	= JFactory::getDbo();
		$id		= $app->input->get('id', 0, 'int');	//proj id
		$row 	= JTable::getInstance('project', 'Table');
		$post 	= $app->input->post->getArray();
		
		$row->load($id);
		$row->status = 'COM_JBLANCE_FROZEN';
	
		if(!$row->save($post)){
			JError::raiseError(500, $row->getError());
		}
		
		//send bid won notification to user
		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		$jbmail->sendBidWonNotification($id);
		
		//Trigger the plugin event to feed the activity - buyer pick freelancer
		JPluginHelper::importPlugin('joombri');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBuyerPickFreelancer', array($row->publisher_userid, $row->assigned_userid, $row->id));
		
		$msg = JText::_('COM_JBLANCE_USER_PICKED_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject', false);
		$this->setRedirect($return, $msg);
	}
	
	function reopenProject(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  	= JFactory::getApplication();
		$id 	= $app->input->get('id', 0, 'int');
	
		//update status project
		$project = JTable::getInstance('project', 'Table');
		$project->id = $id;
		$project->status = 'COM_JBLANCE_OPEN';
		$project->assigned_userid = 0;
		
		if(!$project->check())
			JError::raiseError(500, $project->getError());
		
		if(!$project->store())
			JError::raiseError(500, $project->getError());
		
		$project->checkin();
		
		$msg = JText::_('COM_JBLANCE_PROJECT_REOPENED_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject', false);
		$this->setRedirect($return, $msg);
	}
	
	function removeProject(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
	
		$app  	= JFactory::getApplication();
		$db 	= JFactory::getDbo();
		$user 	= JFactory::getUser();
		$id 	= $app->input->get('id', 0, 'int');
		
		$query = "SELECT COUNT(*) FROM #__jblance_project WHERE id=".$db->quote($id)." AND publisher_userid=".$db->quote($user->id);
		$db->setQuery($query);
		if($db->loadResult() > 0){
			$queries = array();
			$queries[] = "DELETE FROM #__jblance_project WHERE id=".$db->quote($id)." AND publisher_userid=".$db->quote($user->id);
			$queries[] = "DELETE FROM #__jblance_bid WHERE project_id=".$db->quote($id);
			foreach($queries as $query){
				$db->setQuery($query);
				$db->execute();
			}
		}
	
		$msg = JText::_('COM_JBLANCE_PROJECT_DELETED_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject', false);
		$this->setRedirect($return, $msg);
	}
	
	function acceptBid(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  	  = JFactory::getApplication();
		$id 	  = $app->input->get('id', 0, 'int');		// bid id
		$now 	  = JFactory::getDate();
		$projHelp = JblanceHelper::get('helper.project');		// create an instance of the class ProjectHelper
		
		//update the bid status to 'accept'
		$bid = JTable::getInstance('bid', 'Table');
		$bid->load($id);
		$bid->status = 'COM_JBLANCE_ACCEPTED';
		
		if(!$bid->check())
			JError::raiseError(500, $bid->getError());
		
		if(!$bid->store())
			JError::raiseError(500, $bid->getError());
		
		$bid->checkin();
		
		//update project status to 'close'
		$project = JTable::getInstance('project', 'Table');
		$project->load($bid->project_id);
		$project->status = 'COM_JBLANCE_CLOSED';
		$project->accept_date = $now->toSql();
		
		/* if(!$project->check())
			JError::raiseError(500, $project->getError());
		
		if(!$project->store())
			JError::raiseError(500, $project->getError());
		
		$project->checkin(); */
		
		//calculate the project fee for buyer and debit him [ONLY FOR FIXED PROJECT TYPE - FOR HOURLY PROJECTS DEBIT COMMISSION FOR EACH FUND TRANSFERS]
		/*if($project->project_type == 'COM_JBLANCE_FIXED'){
			$fee_from_buyer  = $projHelp->calculateProjectFee($project->publisher_userid, $bid->amount, 'buyer');
			$transDtl = JText::sprintf('COM_JBLANCE_PROJECT_COMMISSION_FOR_PROJECT_NAME', $project->project_title);
			JblanceHelper::updateTransaction($project->publisher_userid, $transDtl, $fee_from_buyer, -1);
			
			//update transaction table for freelancer
			$fee_from_lancer = $projHelp->calculateProjectFee($project->assigned_userid, $bid->amount, 'freelancer');
			$transDtl = JText::sprintf('COM_JBLANCE_PROJECT_COMMISSION_FOR_PROJECT_NAME', $project->project_title);
			JblanceHelper::updateTransaction($project->assigned_userid, $transDtl, $fee_from_lancer, -1);
			
			//update the profit of the project
			$project->profit = $fee_from_buyer + $fee_from_lancer;
			
			//update the buyer & freelancer commission 
			$project->buyer_commission = $fee_from_buyer;
			$project->lancer_commission = $fee_from_lancer;
		} */
		
		if(!$project->check())
			JError::raiseError(500, $project->getError());
		
		if(!$project->store())
			JError::raiseError(500, $project->getError());
		
		$project->checkin();
		
/* 		//rating actor:buyer and target:freelancer (buyer rating freelancer)
		$rate_buyer = JTable::getInstance('rating', 'Table');
		$rate_buyer->id = 0;
		$rate_buyer->actor = $project->publisher_userid;
		$rate_buyer->target = $project->assigned_userid;
		$rate_buyer->project_id = $project->id;
		
		if(!$rate_buyer->check())
			JError::raiseError(500, $rate_buyer->getError());
		
		if(!$rate_buyer->store())
			JError::raiseError(500, $rate_buyer->getError());
		
		$rate_buyer->checkin();
		
		//rating actor:freelancer and target:buyer (freelancer rating buyer)
		$rate_lancer = JTable::getInstance('rating', 'Table');
		$rate_lancer->id = 0;
		$rate_lancer->actor = $project->assigned_userid;
		$rate_lancer->target = $project->publisher_userid;
		$rate_lancer->project_id = $project->id;
		
		if(!$rate_lancer->check())
			JError::raiseError(500, $rate_lancer->getError());
		
		if(!$rate_lancer->store())
			JError::raiseError(500, $rate_lancer->getError());
		
		$rate_lancer->checkin(); */
		
		//send bid accept notification to publisher
		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		$jbmail->sendProjectAcceptedNotification($bid->project_id, $bid->user_id);
		
		//Trigger the plugin event to feed the activity - buyer pick freelancer
		JPluginHelper::importPlugin('joombri');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onFreelancerAcceptBid', array($project->assigned_userid, $project->publisher_userid, $project->id));
		
		$msg = JText::_('COM_JBLANCE_BID_ACCEPTED_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmybid', false);
		$this->setRedirect($return, $msg);
	}
	
	function denyBid(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  	= JFactory::getApplication();
		$row = JTable::getInstance('bid', 'Table');
	
		$id = $app->input->get('id', 0, 'int');	//bid id
		$row->load($id);
		$row->status = 'COM_JBLANCE_DENIED';
		
		$project = JTable::getInstance('project', 'Table');
		$project->load($row->project_id);
	
		if(!$row->check())
			JError::raiseError(500, $row->getError());
		
		if(!$row->store())
			JError::raiseError(500, $row->getError());
		
		$row->checkin();
		
		//send bid denied notification to publisher
		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		$jbmail->sendProjectDeniedNotification($row->project_id, $row->user_id);
		
		//Trigger the plugin event to feed the activity - buyer pick freelancer
		JPluginHelper::importPlugin('joombri');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onFreelancerDenyBid', array($project->assigned_userid, $project->publisher_userid, $project->id));
		
		$msg = JText::_('COM_JBLANCE_BID_DENIED_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmybid', false);
		$this->setRedirect($return, $msg);
	}
	
	function retractBid(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
	
		$app  	= JFactory::getApplication();
		$row = JTable::getInstance('bid', 'Table');
		$id = $app->input->get('id', 0, 'int');	//bid id
		$row->load($id);
		
		//remove the bid attachement
		$attachedFile = $row->attachment;
		if($attachedFile){
			$attFile = explode(';', $attachedFile);
			$filename = $attFile[1];
			$delete = JBBIDNDA_PATH.'/'.$filename;
			if(JFile::exists($delete))
				unlink($delete);
		}
	
		if(!$row->delete($id))
			JError::raiseError(500, $row->getError());

		$msg = JText::_('COM_JBLANCE_BID_RETRACTED_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmybid', false);
		$this->setRedirect($return, $msg);
	}
	
	function saveRateUser(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  		= JFactory::getApplication();
		$post 		= $app->input->post->getArray();
		//$id 		= $app->input->get('id', 0, 'int');
		$rate_type 	= $app->input->get('rate_type', '', 'string');
		$now 		= JFactory::getDate();
		
		$row = JTable::getInstance('rating', 'Table');
		//$row->load($id);
		
		$post['rate_date'] = $now->toSql();
		
		if(!$row->save($post))
			JError::raiseError(500, $row->getError());
		
		$msg = JText::_('COM_JBLANCE_USER_RATING_SAVED_SUCCESSFULLY');
		
		/*	If I rate a buyer, I'm a Freelancer. Hence direct me to showmybid page.
		 * 	If I rate a freelancer, I'm a buyer. Hence direct me to showmyproject page.
		 */
		
		if($rate_type == 'COM_JBLANCE_BUYER')
			$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmybid', false);
		elseif($rate_type == 'COM_JBLANCE_FREELANCER')
			$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject', false);
		else
			$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=listproject', false);
		
		//Trigger the plugin event to feed the activity - buyer pick freelancer
		JPluginHelper::importPlugin('joombri');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onUserRating', array($row->actor, $row->target, $row->project_id));
		
		$this->setRedirect($return, $msg);
	}
	
	function paymentComplete(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  	= JFactory::getApplication();
		$id 	= $app->input->get('id', 0, 'int');
		$user = JFactory::getUser();
		
		//update status project
		$project = JTable::getInstance('project', 'Table');
		$project->load($id);
		$project->paid_status = 'COM_JBLANCE_PYMT_COMPLETE';
		
		if(!$project->check())
			JError::raiseError(500, $project->getError());
		
		if(!$project->store())
			JError::raiseError(500, $project->getError());
		
		$project->checkin();
		
		//send send hourly project payment complete notification
		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		$jbmail->sendProjectPaymentCompleteNotification($id, $user->id);
		
		$msg = JText::_('COM_JBLANCE_PAYMENT_MARKED_COMPLETE_SUCCESSFULLY');
		$redirect_url = empty($_SERVER['HTTP_REFERER']) ? JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard', false) : $_SERVER['HTTP_REFERER'];
		$this->setRedirect($redirect_url, $msg);
		return false;
	}
	
	function repostProject(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  	= JFactory::getApplication();
		$id 	= $app->input->get('id', 0, 'int');
		$user = JFactory::getUser();
		$now = JFactory::getDate();
		
		//update status project
		$project = JTable::getInstance('project', 'Table');
		$project->load($id);
		$project->status = 'COM_JBLANCE_OPEN';
		$project->start_date = $now->toSql();
		
		if(!$project->check())
			JError::raiseError(500, $project->getError());
		
		if(!$project->store())
			JError::raiseError(500, $project->getError());
		
		$project->checkin();
		
		// deduce `charge per project` if amount is > 0
		$plan = JblanceHelper::whichPlan($user->id);
		$chargePerProject = $plan->buyChargePerProject;
		
		if($chargePerProject > 0){
			$transDtl = JText::_('COM_JBLANCE_REPOST').' - '.$project->project_title;
			JblanceHelper::updateTransaction($user->id, $transDtl, $chargePerProject, -1);
			$msg_debit = JText::sprintf('COM_JBLANCE_YOUR_ACCOUNT_DEBITED_WITH_CURRENCY_FOR_POSTING_PROJECT', JblanceHelper::formatCurrency($chargePerProject));
			$app->enqueueMessage($msg_debit);
		}
		
		/* //send send hourly project payment complete notification
		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		$jbmail->sendProjectPaymentCompleteNotification($id, $user->id); */
		
		$msg = JText::_('COM_JBLANCE_PROJECT_REPOSTED_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject', false);
		$this->setRedirect($return, $msg);
		return false;
	}
	
	function saveInviteUser(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		// Initialize variables
		$app  		= JFactory::getApplication();
		$db 		= JFactory::getDbo();
		$id			= $app->input->get('id', 0, 'int');	//proj id
		$project	= JTable::getInstance('project', 'Table');
		$post 		= $app->input->post->getArray();
		$jbmail 	= JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		$invite_ids = $app->input->get('invite_userid', array(), 'array');
		JArrayHelper::toInteger($invite_ids);
		
		$config 		= JblanceHelper::getConfig();
		$reviewProjects = $config->reviewProjects;
		
		if(count($invite_ids) > 0 && !(count($invite_ids) == 1 && empty($invite_ids[0]))){
			$user_ids = implode(',', $invite_ids);
		}
		elseif($invite_ids[0] == 0){
			$user_ids = 0;
		}
		
		$project->load($id);
		$project->invite_user_id = $user_ids;
		
		if(!$project->check())
			JError::raiseError(500, $project->getError());
		
		if(!$project->store())
			JError::raiseError(500, $project->getError());
		
		$project->checkin();
		
		
		//send private project invitation to selected user if the project doesn't need review
		if(!$reviewProjects)
			$jbmail->sendNewProjectNotification($id, true);
		
		$msg = JText::_('COM_JBLANCE_INVITATION_SENT_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject', false);
		$this->setRedirect($return, $msg);
		return false;
	}
	
	function saveInviteToProject(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
	
		// Initialize variables
		$app  		= JFactory::getApplication();
		$user_id	= $app->input->get('user_id', 0, 'int');	//id of the user being invited
		$project_id	= $app->input->get('project_id', 0, 'int');	//proj id
		$project	= JTable::getInstance('project', 'Table');
		$jbmail 	= JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
	
		$project->load($project_id);
	
		//if invite user id is 0, it is new else append
		if($project->invite_user_id == 0 || empty($project->invite_user_id)){
			$project->invite_user_id = $user_id;
		}
		else {
			$project->invite_user_id .= ','.$user_id;
		}
	
		if(!$project->check())
			JError::raiseError(500, $project->getError());
	
		if(!$project->store())
			JError::raiseError(500, $project->getError());
	
		$project->checkin();
	
		//send invitation to the user to bid on this project
		$jbmail->sendInviteToProjectNotification($project_id, $user_id);
	
		
		$msg = JText::_('COM_JBLANCE_INVITATION_SENT_SUCCESSFULLY');
		echo '<p class="alert alert-success">'.$msg.'</p>';
		
		$doc = JFactory::getDocument();
		$js = "javascript:window.top.setTimeout('window.parent.SqueezeBox.close()', 5000);";
		$doc->addScriptDeclaration($js);
		
		return false;
	}
	
	function updateProgress(){
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	
		$app  		= JFactory::getApplication();
		$post 		= $app->input->post->getArray();
		$now		= JFactory::getDate();
		$bid 		= JTable::getInstance('bid', 'Table');
		$bidId		= $app->input->get('id', 0, 'int');
		$status		= $app->input->get('p_status', '', 'string');
		$statusInit	= $app->input->get('status_initiated', 0, 'boolean');
	
		if($statusInit)
			$status = 'COM_JBLANCE_INITIATED';
	
		$bid->load($bidId);
	
		$bid->p_status = $status;
		$bid->p_percent = $post['p_percent'];
		$bid->p_updated = $now->toSql();
	
	
		//set the started date only for the first time
		if($status == 'COM_JBLANCE_INITIATED'){
			if($bid->p_started == '0000-00-00 00:00:00'){
				$bid->p_started = $now->toSql();
			}
		}
		elseif($status == 'COM_JBLANCE_COMPLETED'){
			if($bid->p_ended == '0000-00-00 00:00:00'){
				$bid->p_ended = $now->toSql();
			}
		}
	
		if(!$bid->store()){
			JError::raiseError(500, $bid->getError());
		}
		$bid->checkin();
	
		//send project progress notification to buyer
		$jbmail = JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		if(!empty($status))
			$jbmail->sendProjectProgressNotification($bidId, $bid->project_id);
	
		$msg	= JText::_('COM_JBLANCE_PROJECT_PROGRESS_UPDATED_SUCCESSFULLY');
		$return	= JRoute::_('index.php?option=com_jblance&view=project&layout=projectprogress&id='.$bidId, false);
		$this->setRedirect($return, $msg, 'message');
	}
	/* Misc Functions */
	
	//download file
	function download(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
	
		JBMediaHelper::downloadFile();
	}
	
	// submit forum
	function submitForum(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		$app  		= JFactory::getApplication();
		$now 		= JFactory::getDate();
		$row 		= JTable::getInstance('forum', 'Table');
		$message 	= $app->input->get('message', '', 'string');
		$userid 	= $app->input->get('user_id', 0, 'int');
		$projectid 	= $app->input->get('project_id', 0, 'int');
		$post 		= array();
		$jbmail 	= JblanceHelper::get('helper.email');		// create an instance of the class EmailHelper
		$projHelper	= JblanceHelper::get('helper.project');		// create an instance of the class ProjectHelper
		
		//show whether name/username
		$config = JblanceHelper::getConfig();
		$showUsername = $config->showUsername;
		$nameOrUsername = ($showUsername) ? 'username' : 'name';
		
		//get user details
		$user = JFactory::getUser($userid);
		
		//get project details 
		$jbproject = $projHelper->getProjectDetails($projectid);
		
		$post['date_post'] = $now->toSql();
		$post['message'] = $message;
		$post['user_id'] = $userid;
		$post['project_id'] = $projectid;
		$post['project_title'] = $jbproject->project_title;
		$post['publisher_userid'] = $jbproject->publisher_userid;
			if($message){
				$result = $row->save($post);
				if($result){
					$username = '<span>'.$user->$nameOrUsername.'<span class="pull-right">'.JText::_('COM_JBLANCE_RECENTLY').'</span></span>';
					$message = '<p>'.strip_tags($post['message']).'</p>';
					echo "<li>$username"."$message</li>";
					
					//send notification to the users who are in the forum
					$jbmail->sendForumMessageNotification($post);
				}
				else 
					JError::raiseError(500, $row->getError());
		}
		exit;
	}
	
	function getLocationAjax(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		
		JblanceHelper::getLocation();
	}
}