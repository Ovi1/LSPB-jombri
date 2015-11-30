<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	28 August 2014
 * @file name	:	views/project/tmpl/invitetoproject.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Invite Users to bid on project (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('behavior.formvalidator');
 
 $app  		= JFactory::getApplication();
 $config 	= JblanceHelper::getConfig();
 
 $showUsername 	= $config->showUsername;
 $nameOrUsername = ($showUsername) ? 'username' : 'name';
 $user_id		= $app->input->get('id', 0, 'int');
 
 $link_edit = JRoute::_('index.php?option=com_jblance&view=project&layout=editproject');
 ?>
 <script type="text/javascript">
<!--
function validateForm(f){
	var valid = document.formvalidator.isValid(f);
	if(valid == true){
		
    }
    else {
	    var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
		alert(msg);
		return false;
    }
}
//-->
</script>
 <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormProject" id="userFormProject" class="form-validate form-horizontal" onsubmit="return validateForm(this);">
	<div class="jbl_h3title"><?php echo JText::sprintf('COM_JBLANCE_INVITE_TO_PROJECT', $link_edit); ?></div>
 <div class="row-fluid">
		<div class="span12">
		<?php 
		if(empty($this->projects)) : ?>
			<p class="jbbox-warning"><?php echo JText::sprintf('COM_JBLANCE_NO_ACTIVE_PROJECTS_TO_INVITE', $link_edit); ?></p>
		<?php 
		else : 
		?>
			<label for="project_id"><?php echo JText::_('COM_JBLANCE_PLEASE_SELECT_PROJECT_FROM_LIST_BELOW'); ?>:</label>
			<p>
				<?php 
				 $types[] = JHtml::_('select.option', '', '- '.JText::_('COM_JBLANCE_PLEASE_SELECT').' -'); 
				 foreach($this->projects as $item){
				 	$disabled = false;
				 	$invited_user_id = explode(',', $item->invite_user_id);
				 	if(in_array($user_id, $invited_user_id))
				 		$disabled = true;
				 	$types[] = JHtml::_('select.option', $item->value, $item->text, 'value', 'text', $disabled); 
				 }
				 $lists 	= JHtml::_('select.genericlist', $types, 'project_id', "class='span4 required' size='8'", 'value', 'text', '');
				 echo $lists;
				 
				 ?>
				 <i><span class="help-inline"><?php echo JText::_('COM_JBLANCE_PROJECTS_DISABLED_IF_INVITED'); ?></span></i>
			</p>
			<div class="span12">
				<?php $invitee = JFactory::getUser($user_id);?>
				<p><?php echo JText::sprintf('COM_JBLANCE_YOU_INVITING_USERNAME', '<b>'.$invitee->$nameOrUsername.'</b>'); ?></p>
			</div>
			<div class="form-actions">
				<input type="submit" value="<?php echo JText::_('COM_JBLANCE_INVITE'); ?>" class="btn btn-primary" />
			</div>
		<?php 
		endif; ?>
		</div>
	</div>
	<input type="hidden" name="option" value="com_jblance" />			
	<input type="hidden" name="task" value="project.saveinvitetoproject" />
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>