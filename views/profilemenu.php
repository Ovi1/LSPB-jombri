<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	13 March 2012
 * @file name	:	views/profilemenu.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows sub-menu on profile pages (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 $link_editportfolio	 = JRoute::_('index.php?option=com_jblance&view=user&layout=editportfolio');
 $link_email_preferences = JRoute::_('index.php?option=com_jblance&view=user&layout=notify');
 $link_edit_account		 = JRoute::_('index.php?option=com_users&view=profile&layout=edit');
 
 $app  	= JFactory::getApplication();
 $layout = $app->input->get('layout', '', 'string');
 $user = JFactory::getUser();
 
 $jbuser 				= JblanceHelper::get('helper.user');
 $ugInfo 				= $jbuser->getUserGroupInfo($user->id, null);
?>
<ul class="nav nav-tabs">
	<li class="<?php echo ($layout == 'editprofile') ? 'active' : ''; ?>">
	    <?php
		$profileInteg = JblanceHelper::getProfile();
		//$link_edit_profile = $profileInteg->getEditURL();
		$link_edit_profile = JRoute::_('index.php?option=com_jblance&view=user&layout=editprofile');
		?>
		<a href="<?php echo $link_edit_profile; ?>"><?php echo JText::_('COM_JBLANCE_EDIT_PROFILE'); ?></a>
	</li>
	<li class="<?php echo ($layout == 'editpicture') ? 'active' : ''; ?>">
		<?php 
		$avatars = JblanceHelper::getAvatarIntegration();
		$link_edit_picture = $avatars->getEditURL();
		?>
		<a href="<?php echo $link_edit_picture; ?>"><?php echo JText::_('COM_JBLANCE_EDIT_PICTURE'); ?></a>
	</li>
	<?php if($ugInfo->allowAddPortfolio) : ?>
	<li class="<?php echo ($layout == 'editportfolio') ? 'active' : ''; ?>">
		<a href="<?php echo $link_editportfolio; ?>"><?php echo JText::_('COM_JBLANCE_EDIT_PORTFOLIO'); ?></a>
	</li>
	<?php endif; ?>
	<li class="<?php echo ($layout == 'notify') ? 'active' : ''; ?>">
		<a href="<?php echo $link_email_preferences; ?>"><?php echo JText::_('COM_JBLANCE_EMAIL_PREFERENCES'); ?></a>
	</li>
	<!-- <li class="<?php echo ($layout == 'edit') ? 'active' : ''; ?>">
		<a href="<?php echo $link_edit_account; ?>"><?php echo JText::_('COM_JBLANCE_EDIT_ACCOUNT'); ?></a>
	</li> -->
</ul>