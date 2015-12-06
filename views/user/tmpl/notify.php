<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	11 September 2012
 * @file name	:	views/user/tmpl/notify.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Email Notification settings (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('jquery.framework');
 
 $doc 	 = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/btngroup.js");

 $user = JFactory::getUser();
 $model = $this->getModel();
 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 
 $jbuser = JblanceHelper::get('helper.user');
 $userGroup = $jbuser->getUserGroupInfo($user->id, null);
?>
<?php include_once(JPATH_COMPONENT.'/views/profilemenu.php'); ?>
<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_EMAIL_SETTINGS'); ?></div>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormNotify" class="form-validate form-horizontal" enctype="multipart/form-data ">
  
  <div class="panel panel-default">
      <div class="panel-heading"><h3><i class="material-icons">notifications</i> <?php echo JText::_('COM_JBLANCE_RECEIVE_INDIVIDUAL_NOTIFICATIONS_WHEN'); ?></h3></div>
   <div class="panel-body">
	<div class="form-group"  style="display: none;">
		<label class="control-label"><?php echo JText::_('COM_JBLANCE_FREQUENCY_OF_UPDATES'); ?>:</label>
		<div class="checkbox-inline">
			<?php echo  $model->getSelectUpdateFrequency('frequency', $this->row->frequency ? $this->row->frequency : 'instantly'); ?>
		</div>
	</div>
	<div class="form-group"  style="display: none;">
		<label class="control-label"><?php echo JText::_('COM_JBLANCE_FREQUENCY_OF_UPDATES'); ?>:</label>
		<div class="controls">
			<label class="checkbox-inline">
			<?php echo  $model->getSelectUpdateFrequency('frequency', $this->row->frequency ? $this->row->frequency : 'instantly'); ?>
			</label>
		</div>
	</div>
	<?php if($userGroup->allowBidProjects){ ?>
	<div class="form-group">
		<label class="control-label"><?php echo JText::_('COM_JBLANCE_NOTIFY_WHEN_RELEVANT_PROJECT_GETS_POSTED'); ?>:</label>
		<div class="controls">
			<label class="checkbox-inline">
			<?php echo $select->YesNoBool('notifyNewProject', $this->row->notifyNewProject); ?>
			</label>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label"><?php echo JText::_('COM_JBLANCE_NOTIFY_BID_WON_CHOSEN_BY_BUYER'); ?>:</label>
		<div class="controls">
			<label class="checkbox-inline">
			<?php echo $select->YesNoBool('notifyBidWon', $this->row->notifyBidWon); ?>
			</label>
		</div>
	</div>
	<?php } ?>
	<?php if($userGroup->allowPostProjects){ ?>
	<div class="form-group">
		<label class="control-label"><?php echo JText::_('COM_JBLANCE_NOTIFY_BID_NEW_ACCEPTED_DENIED'); ?>:</label>
		<div class="">
			<label class="checkbox-inline">
			<?php echo $select->YesNoBool('notifyBidNewAcceptDeny', $this->row->notifyBidNewAcceptDeny); ?>
			</label>
		</div>
	</div>
	<?php } ?>
	<div class="form-group">
		<label class="control-label"><?php echo JText::_('COM_JBLANCE_NOTIFY_NEW_FORUM_MESSAGE'); ?>:</label>
		<div class="controls">
			<label class="checkbox-inline">
			<?php echo $select->YesNoBool('notifyNewForumMessage', $this->row->notifyNewForumMessage); ?>
			</label>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label"><?php echo JText::_('COM_JBLANCE_NOTIFY_NEW_PRIVATE_MESSAGE'); ?>:</label>
		<div class="controls">
			<label class="checkbox-inline">
			<?php echo $select->YesNoBool('notifyNewMessage', $this->row->notifyNewMessage); ?>
			</label>
		</div>
	</div>
<div class="clearfix"></div>
	<div class="btn-group pull-rightl">
    <input type="button" onclick="javascript:history.back()" value="<?php echo JText::_('COM_JBLANCE_CANCEL'); ?>" class="btn btn-default"/>
    	<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SAVE'); ?>" class="btn btn-primary" />
	</div>
  </div>
  </div>
	<input type="hidden" name="option" value="com_jblance" />			
	<input type="hidden" name="task" value="user.savenotify" />		
	<input type="hidden" name="id" value="<?php echo $this->row->id;?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>