<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	27 March 2012
 * @file name	:	views/project/tmpl/rateuser.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Rate user (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 
 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/barrating.js");
 $doc->addStyleSheet("components/com_jblance/css/barrating.css");

 $project 		 = $this->project;
 $select 		 = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 $config		 = JblanceHelper::getConfig();
 $showUsername 	 = $config->showUsername;
 $nameOrUsername = ($showUsername) ? 'username' : 'name';
 
 //find the current user is the buyer or freelancer. If the user is buyer, then the target is freelancer and vice versa
 $user = JFactory::getUser();
 if($user->id == $project->publisher_userid){
 	$actor = $project->publisher_userid;
 	$target = $project->assigned_userid;
 	$target_rate_type = 'COM_JBLANCE_FREELANCER';	//freelancer is equal to seller
 }
 elseif($user->id == $project->assigned_userid){
 	$actor = $project->assigned_userid;
 	$target = $project->publisher_userid;
 	$target_rate_type = 'COM_JBLANCE_BUYER';
 }
 
?>
<script type="text/javascript">
<!--
function validateForm(f){
	var valid = document.formvalidator.isValid(f);
	
	if(valid == true){
		
    }
    else {
		alert('<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>');
		return false;
    }
	return true;
}
jQuery(document).ready(function($){
    $("#quality_clarity").barrating("show", {
        showSelectedRating:true
    });
    $("#communicate").barrating("show", {
        showSelectedRating:true
    });
    $("#expertise_payment").barrating("show", {
        showSelectedRating:true
    });
    $("#professional").barrating("show", {
        showSelectedRating:true
    });
    $("#hire_work_again").barrating("show", {
        showSelectedRating:true
    });
});
//-->
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormProject" id="userFormProject" class="form-validate form-horizontal" onsubmit="return validateForm(this);">
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_RATE_USER'); ?></div>
	<div class="control-group">
		<label class="control-label"><?php echo JText::_('COM_JBLANCE_PROJECT_NAME'); ?>: </label>
		<div class="controls font16">
			<?php echo $project->project_title; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label"><?php echo JText::_('COM_JBLANCE_NAME'); ?>: </label>
		<div class="controls">
			<?php
			$target_user = JFactory::getUser($target);
			echo $target_user->$nameOrUsername.' ('.JText::_($target_rate_type).')'; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="quality_clarity"><?php echo ($target_rate_type == 'COM_JBLANCE_BUYER') ? JText::_('COM_JBLANCE_CLARITY_SPECIFICATION') : JText::_('COM_JBLANCE_QUALITY_OF_WORK'); ?>: </label>
		<div class="controls brating">
			<?php $rating = $select->getSelectRating('quality_clarity', 5);
			echo $rating; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="communicate"><?php echo JText::_('COM_JBLANCE_COMMUNICATION'); ?>: </label>
		<div class="controls brating">
			<?php $rating = $select->getSelectRating('communicate', 5);
			echo $rating; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="expertise_payment"><?php echo ($target_rate_type == 'COM_JBLANCE_BUYER') ? JText::_('COM_JBLANCE_PAYMENT_PROMPTNESS') : JText::_('COM_JBLANCE_EXPERTISE'); ?>: </label>
		<div class="controls brating">
			<?php $rating = $select->getSelectRating('expertise_payment', 5);
			echo $rating; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="professional"><?php echo JText::_('COM_JBLANCE_PROFESSIONALISM'); ?>: </label>
		<div class="controls brating">
			<?php $rating = $select->getSelectRating('professional', 5);
			echo $rating; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="hire_work_again"><?php echo ($target_rate_type == 'COM_JBLANCE_BUYER') ? JText::_('COM_JBLANCE_WORK_AGAIN') : JText::_('COM_JBLANCE_HIRE_AGAIN'); ?>: </label>
		<div class="controls brating">
			<?php $rating = $select->getSelectRating('hire_work_again', 5);
			echo $rating; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="comments"><?php echo JText::_('COM_JBLANCE_COMMENTS'); ?>: </label>
		<div class="controls">
			<textarea name="comments" rows="5" class="input-xlarge required"></textarea>
		</div>
	</div>
	<div class="form-actions">
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SUBMIT'); ?>" class="btn btn-primary" />
	</div>
	
	<input type="hidden" name="option" value="com_jblance" />			
	<input type="hidden" name="task" value="project.saverateuser" />
	<!-- <input type="hidden" name="id" value="<?php //echo $this->rate->id ; ?>" /> -->
	<input type="hidden" name="actor" value="<?php echo $actor; ?>" />
	<input type="hidden" name="target" value="<?php echo $target; ?>" />
	<input type="hidden" name="project_id" value="<?php echo $project->id; ?>" />
	<input type="hidden" name="rate_type" value="<?php echo $target_rate_type; ?>" />
	<input type="hidden" name="type" value="COM_JBLANCE_PROJECT" />
	<?php echo JHtml::_('form.token'); ?>
	</form>