<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	5 July 2014
 * @file name	:	views/project/tmpl/inviteuser.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Invite Users for Private Invite project (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 
 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");
 
 $jbuser = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
 
 $invited_user_id = explode(',', $this->project->invite_user_id);		//get the array of list of user ids already invited.
 
 $action = JRoute::_('index.php?option=com_jblance&view=project&layout=inviteuser&id='.$this->project->id);
 
 JblanceHelper::setJoomBriToken();
 ?>
<script>
<!--
function validateForm(f){
	if(!jQuery("input[name='invite_userid[]']:checked").length){
		alert('<?php echo JText::_('COM_JBLANCE_PLEASE_SELECT_USERS_FROM_THE_LIST', true); ?>');
		return false;
	}
	else {
		var form = document.frmInviteUser;
		form.task.value = 'project.saveinviteuser';
		form.submit();
	}
}
//-->
</script>
<form action="<?php echo $action; ?>" method="post" name="frmInviteUser" id="frmInviteUser" class="form-validate form-horizontal" onsubmit="return validateForm(this);" enctype="multipart/form-data">
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_INVITE_USERS').' : '.$this->project->project_title; ?></div>
	<?php
	for ($i=0, $x=count($this->rows); $i < $x; $i++){
		$row = $this->rows[$i];
		
		$checked = (in_array($row->user_id, $invited_user_id)) ? 'checked' : '';
		$status = $jbuser->isOnline($row->user_id);		//get user online status
		
		$isFavourite = JblanceHelper::checkFavorite($row->user_id, 'profile');
		?>
	<div class="media">
	
		<input type="checkbox" name="invite_userid[]" id="invite_userid_<?php echo $row->user_id; ?>" value="<?php echo $row->user_id; ?>" <?php echo $checked; ?> style="float: left; margin: 20px 10px 0 0;"/>
		<?php
		$attrib = 'width=48 height=48 class="img-polaroid"';
		$avatar = JblanceHelper::getLogo($row->user_id, $attrib);
		echo !empty($avatar) ? LinkHelper::GetProfileLink($row->user_id, $avatar, '', '', ' pull-left') : '&nbsp;' ?>
		<div class="media-body">
			<h5 class="media-heading">
				<?php $stats = ($status) ? 'online' : 'offline'; ?>
				<span class="online-status <?php echo $stats; ?>" title="<?php echo JText::_('COM_JBLANCE_'.strtoupper($stats)); ?>"></span>
				<?php echo LinkHelper::GetProfileLink($row->user_id, $row->name); ?> <small><?php echo $row->username; ?></small>
				<span id="fav-msg-<?php echo $row->user_id; ?>" class="pull-right">
					<?php if($isFavourite > 0) : ?>
					<a onclick="favourite('<?php echo $row->user_id; ?>', -1,'profile');" href="javascript:void(0);" class="btn btn-mini btn-danger"><span class="icon-minus-sign icon-white"></span> <?php echo JText::_('COM_JBLANCE_REMOVE_FAVOURITE')?></a>
					<?php else : ?>
					<a onclick="favourite('<?php echo $row->user_id; ?>', 1,'profile');" href="javascript:void(0);" class="btn btn-mini"><span class="icon-plus-sign"></span> <?php echo JText::_('COM_JBLANCE_ADD_FAVOURITE')?></a>
					<?php endif; ?>
				</span>
			</h5>
			<div>
				<?php $rate = JblanceHelper::getAvarageRate($row->user_id, true); ?>
				<?php if($row->rate > 0){ ?>
				<span class="font14" style="margin-left: 10px;"><?php echo JblanceHelper::formatCurrency($row->rate, true, true, 0).'/'.JText::_('COM_JBLANCE_HR'); ?></span>
				<?php } ?>
			</div>
			<?php if(!empty($row->id_category)){ ?>
			<div class="boldfont font12">
				<?php echo JText::_('COM_JBLANCE_SKILLS'); ?>: <?php echo JblanceHelper::getCategoryNames($row->id_category); ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="lineseparator"></div>
 	<?php 
	}
 ?>
	<div class="pagination">
		<?php echo $this->pageNav->getListFooter(); ?>
	</div>
	<div class="form-actions">
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_INVITE_USERS'); ?>" class="btn btn-primary" />
	</div>
	<input type="hidden" name="option" value="com_jblance" />	
	<input type="hidden" name="view" value="project" />
	<input type="hidden" name="layout" value="inviteuser" />
	<input type="hidden" name="task" value="" />	
	<input type="hidden" name="id" value="<?php echo $this->project->id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>