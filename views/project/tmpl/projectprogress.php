<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	05 March 2015
 * @file name	:	views/project/tmpl/projectprogress.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Project progress page (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 JHtml::_('bootstrap.tooltip');
 JHtml::_('behavior.tabstate');
 
 $doc 	 = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/btngroup.js");
 $doc->addScript("components/com_jblance/js/utility.js");
 $doc->addScript("components/com_jblance/js/upclick-min.js");
 $doc->addScript("components/com_jblance/js/bootstrap-slider.js");
 $doc->addStyleSheet("components/com_jblance/css/slider.css");
 
 $project = $this->row;
 
 $user 		= JFactory::getUser();
 $config 	= JblanceHelper::getConfig();
 $now		= JFactory::getDate();
 $select 	= JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper

 $showUsername	= $config->showUsername;
 $dformat 		= $config->dateFormat;
 
 //get the freelancer and buyer details
 $buyerInfo 	 = JFactory::getUser($project->buyer_id);
 $freelancerInfo = JFactory::getUser($project->freelancer_id);
 
 $nameOrUsername = ($showUsername) ? 'username' : 'name';
 
 //find the current user is buyer or freelancer
 $isBuyer 		 = ($project->buyer_id == $user->id) ? true : false;
 $isfreelancer   = ($project->freelancer_id == $user->id) ? true : false;
 
 JText::script('COM_JBLANCE_INITIATED');
 JText::script('COM_JBLANCE_IN_PROGRESS');
 JText::script('COM_JBLANCE_COMPLETED');
 
 JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
function validateFormProgress(f){
	jQuery("#submitbtn").prop("disabled", true);
	jQuery("#submitbtn").val('<?php echo JText::_('COM_JBLANCE_SAVING', true); ?>');
	return true;
}
function validateFormMessage(f){
	var message	 = jQuery("#message").val(); 
	//check if the message is not entered
	if(!message.length === 0 || !message.trim()){
		alert('<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>');
		jQuery("#message").focus();
		return false;
	}
	else {
		jQuery("#btnsend").prop("disabled", true);
		jQuery("#btnsend").val('<?php echo JText::_('COM_JBLANCE_SENDING', true); ?>');
		return true;
	}
}
jQuery(document).ready(function($){
	$("#p_percent").sliderz({
		min: 0,
		max: 100,
		step: 10,
		value: 0,
		tooltip: "hide",
		formater: function(value) {
		return value + " %";
		}
	});
	$("#p_percent").on("slide", function(slideEvt) {
		$("#p_percent_text").text(slideEvt.value);
		if(slideEvt.value == 0){
			$("#p_status").val("COM_JBLANCE_INITIATED");
			$("#span_p_status").html(Joomla.JText._('COM_JBLANCE_INITIATED'));
		}
		if(slideEvt.value > 0 && slideEvt.value < 100){
			$("#p_status").val("COM_JBLANCE_IN_PROGRESS");
			$("#span_p_status").html(Joomla.JText._('COM_JBLANCE_IN_PROGRESS'));
		}
		if(slideEvt.value == 100){
			$("#p_status").val("COM_JBLANCE_COMPLETED");
			$("#span_p_status").html(Joomla.JText._('COM_JBLANCE_COMPLETED'));
		}
	});
});

jQuery(document).ready(function($){
	attachFile('uploadmessage', 'message.attachfile');

	//Scrolls the comment list to the bottom
	if($("messageList").length)
		$("#messageList").scrollTop($("#messageList").prop("scrollHeight"));
});
//-->
</script>

	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PROJECT_PROGRESS'); ?></div>
	<div class="row-fluid">
		<div class="span6">
			<fieldset class="form-horizontal">
				<legend><?php echo JText::_('COM_JBLANCE_PROJECT_DETAILS'); ?></legend>
				<div class="control-group">
					<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PROJECT_TITLE'); ?> :</label>
					<div class="controls">
						<span class="font20"><?php echo $project->project_title; ?></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_POSTED_BY'); ?> :</label>
					<div class="controls">
						<?php echo $buyerInfo->$nameOrUsername; ?>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_ASSIGNED_TO'); ?> :</label>
					<div class="controls">
						<?php echo $freelancerInfo->$nameOrUsername; ?>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_BID_AMOUNT'); ?> :</label>
					<div class="controls">
						<?php echo JblanceHelper::formatCurrency($project->amount); ?>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_DELIVERY_IN'); ?> :</label>
					<div class="controls">
						<?php echo JText::plural('COM_JBLANCE_N_DAYS', $project->delivery); ?>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'progress')); ?>
	
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'progress', JText::_('COM_JBLANCE_PROGRESS', true)); ?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="progress-update" class="form-validate" onsubmit="return validateFormProgress(this);">
	<div class="row-fluid">
 		<div class="span12">
 			<fieldset class="form-vertical">
 				<!-- <legend><?php echo JText::_('COM_JBLANCE_PROGRESS'); ?></legend> -->
 				<div class="row-fluid">
 					<div class="span8">
		 				<?php if($isfreelancer) : ?>
		 				<?php if($project->p_status == ''){ ?>
		 				<div class="control-group">
							<label class="control-label"><?php echo JText::_('COM_JBLANCE_HAVE_YOU_INITIATED_WORK'); ?> :</label>
							<div class="controls">
								<?php echo $select->YesNoBool('status_initiated'); ?>
							</div>
						</div>
						<?php }
						else {
						?>
		 				<div class="control-group" id="div_p_percent">
							<label class="control-label"><?php echo JText::_('COM_JBLANCE_PERCENT_OF_COMPLETION'); ?> :</label>
							<div class="controls">
								<input type="text" name="p_percent" id="p_percent" class="input-xlarge" value="<?php echo $project->p_percent; ?>" data-slider-value="<?php echo $project->p_percent; ?>" style="display: none;" />
		 						<span style="margin-left: 15px;"><?php echo JText::_('COM_JBLANCE_CURRENT_PROGRESS'); ?>: <span id="p_percent_text"><?php echo $project->p_percent; ?></span>%</span>
							</div>
						</div>
		 				<div class="control-group">
							<label class="control-label"><?php echo JText::_('COM_JBLANCE_STATUS'); ?> :</label>
							<div class="controls">
								<input type="hidden" name="p_status" id="p_status" value="<?php echo $project->p_status; ?>" />
								<span id="span_p_status"><?php echo JText::_($project->p_status); ?></span>
								
							</div>
						</div>
						<?php } ?>
						<?php elseif($isBuyer) : ?>
						<div class="control-group">
							<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_STATUS'); ?> :</label>
							<div class="controls">
								<?php echo (!empty($project->p_status)) ? JText::_($project->p_status) : JText::_('COM_JBLANCE_NOT_YET_STARTED'); ?>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PERCENT_OF_COMPLETION'); ?> :</label>
							<div class="controls">
								<div class="progress progress-success progress-striped span6">
									<div class="bar" style="width: <?php echo $project->p_percent; ?>%"></div>
								</div>
								&nbsp;<?php echo $project->p_percent; ?>%
							</div>
						</div>
						<?php endif; ?> <!-- end of isfreelancer, isbuyer -->
 					</div>
 					<div class="span4">
		 				<?php if($project->p_started != "0000-00-00 00:00:00") : ?>
						<div class="control-group">
							<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_STARTED'); ?> :</label>
							<div class="controls">
								<?php echo JHtml::_('date', $project->p_started, $dformat.' H:i:s', true); ?>
							</div>
						</div>
						<?php endif; ?>
						<?php if($project->p_updated != "0000-00-00 00:00:00") : ?>
						<div class="control-group">
							<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_LAST_UPDATED'); ?> :</label>
							<div class="controls">
								<?php echo JHtml::_('date', $project->p_updated, $dformat.' H:i:s', true); ?>
							</div>
						</div>
						<?php endif; ?>
						<?php if($project->p_ended != "0000-00-00 00:00:00") : ?>
						<div class="control-group">
							<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_COMPLETED'); ?> :</label>
							<div class="controls">
								<?php echo JHtml::_('date', $project->p_ended, $dformat.' H:i:s', true); ?>
							</div>
						</div>
						<?php endif; ?>
						<?php if($project->p_started != "0000-00-00 00:00:00") : 
						$planned = JFactory::getDate($project->p_started);
						$planned->modify("+$project->delivery days");
						?>
						<div class="control-group">
							<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PLANNED_COMPLETION'); ?> :</label>
							<div class="controls">
								<?php 
								echo JHtml::_('date', $planned, $dformat.' H:i:s', true);
								
								if($project->p_status == 'COM_JBLANCE_INITIATED' || $project->p_status == 'COM_JBLANCE_IN_PROGRESS'){
									echo '<div class="small">['.JblanceHelper::showRemainingDHM($planned, 'LONG', 'COM_JBLANCE_PROJECT_NOT_COMPLETED_IN_TIME').']</div>'; 
								}
								?>
							</div>
						</div>
						<?php endif; ?>
 					</div>
 				</div>
 			</fieldset>
 		</div>
	</div>
	<?php if($isfreelancer && $project->p_status != 'COM_JBLANCE_COMPLETED') : ?>
	<div class="form-actions">
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_UPDATE_PROGRESS'); ?>" class="btn btn-primary" id="submitbtn" /> 
	</div>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_jblance" /> 
	<input type="hidden" name="task" value="project.updateprogress" /> 
	<input type="hidden" name="id" value="<?php echo $project->bid_id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
	<?php echo JHtml::_('bootstrap.endTab'); ?>		<!-- end of progress tab -->
	
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'messages', JText::_('COM_JBLANCE_MESSAGES', true)); ?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="progress-message" id="progress-message" class="form-validate" onsubmit="return validateFormMessage(this);">	
	<?php 
	//initialise message varibles
	if($user->id == $project->freelancer_id){
		$idFrom = $project->freelancer_id;
		$idTo = $project->buyer_id;
	}
	else {
		$idFrom = $project->buyer_id;
		$idTo = $project->freelancer_id;
	}
	?>
	<?php 
	$count =  count($this->messages); 
	if($count > 0){
	?>
	<div  id="messageList" class="jb-chat-panel">
	<ul class="jb-chat">
		<?php
		$parent = (isset($this->messages[0])) ? $this->messages[0]->id : 0;	//parent is the 0th element.
		
		for($i=0, $x=count($this->messages); $i < $x; $i++){
			$message = $this->messages[$i];
			$userDtl = JFactory::getUser($message->idFrom); ?>
			<li class="left clearfix">
				<span class="jb-chat-img pull-left">
					<?php
					$attrib = 'width=56 height=56 class="img-polaroid"';
					$avatar = JblanceHelper::getLogo($message->idFrom, $attrib);
					echo !empty($avatar) ? LinkHelper::GetProfileLink($message->idFrom, $avatar) : '&nbsp;' ?>
				</span>
				<div class="jb-chat-body clearfix">
					<div class="header">
						<strong class="primary-font"><?php echo LinkHelper::GetProfileLink($message->idFrom, $userDtl->$nameOrUsername); ?></strong> 
						<small class="pull-right text-muted">
							<span class="icon-time"></span> <?php echo JHtml::_('date', $message->date_sent, $dformat, true); ?>
						</small>
					</div>
					<p>
					<?php 
					if($message->approved == 1)
						echo $message->message; 
					else
						echo '<small>'.JText::_('COM_JBLANCE_PRIVATE_MESSAGE_WAITING_FOR_MODERATION').'</small>';
					?>
					</p>
				<?php
				if(!empty($message->attachment) && $message->approved == 1) : ?>
					<span>
						<i class="icon-download"></i>
				<?php echo LinkHelper::getDownloadLink('message', $message->id, 'message.download'); ?>
					</span>
				<?php	
				endif;
				?>
				</div>
			</li>
		<?php 
		} ?>
	</ul>
	</div>
	<?php } ?>
	<div class="control-group">
		<div class="controls well">
			<textarea name="message" id="message" rows="3" class="input-block-level required" placeholder="<?php echo JText::_('COM_JBLANCE_ENTER_MESSAGE'); ?>"></textarea>
			<div class="sp10">&nbsp;</div>
			<div id="ajax-container-uploadmessage"></div>
			<div id="file-attached-uploadmessage"></div>
			<div class="pull-left">
				<?php 
				$tipmsg = JHtml::tooltipText(JText::_('COM_JBLANCE_ATTACH_FILE'), JText::_('COM_JBLANCE_ALLOWED_FILE_TYPES').' : '.$config->projectFileText.'<br>'.JText::_('COM_JBLANCE_MAXIMUM_FILE_SIZE').' : '.$config->projectMaxsize.' kB');
				?>
				<img src="components/com_jblance/images/tooltip.png" class="hasTooltip" title="<?php echo $tipmsg; ?>"/>
				<input type="button" id="uploadmessage" value="<?php echo JText::_('COM_JBLANCE_ATTACH_FILE'); ?>" class="btn btn-primary">
			</div>
			<div style="text-align: right;">
				<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SEND'); ?>" class="btn btn-primary"  id="btnsend"/>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="com_jblance" />			
	<input type="hidden" name="task" value="message.sendmessage" />	
	<input type="hidden" name="idFrom" value="<?php echo $idFrom; ?>" />
	<input type="hidden" name="idTo" value="<?php echo $idTo; ?>" />
	<input type="hidden" name="id" value="0" />
	<input type="hidden" name="subject" value="<?php echo $project->project_title; ?>" />
	<input type="hidden" name="project_id" value="<?php echo $project->project_id; ?>" />
	<input type="hidden" name="parent" value="<?php echo $parent; ?>" />
	<input type="hidden" name="type" value="COM_JBLANCE_PROJECT" />
	<input type="hidden" name="return" value="<?php echo base64_encode(JFactory::getURI()->toString())?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
	<?php echo JHtml::_('bootstrap.endTab'); ?>		<!-- end of messages tab -->
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>