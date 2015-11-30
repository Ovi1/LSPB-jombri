<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	12 November 2014
 * @file name	:	views/service/tmpl/listservice.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	List of all services (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 JHtml::_('formbehavior.chosen', '#id_categ');
 
 $app  = JFactory::getApplication();
 $n = count($this->rows);
 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 
 $keyword	  = $app->input->get('keyword', '', 'string');
 $id_categ	  = $app->input->get('id_categ', array(), 'array');
 JArrayHelper::toInteger($id_categ);
 ?>
<form action="<?php echo JRoute::_('index.php?option=com_jblance&view=service&layout=listservice'); ?>" method="post" name="userForm" class="form-inline">	
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_SERVICES'); ?></div>
	
	<div class="row-fluid">
		<div class="span12">
			<div class="pull-right">
			<input type="text" name="keyword" id="keyword" value="<?php echo $keyword; ?>" class="input-large" placeholder="<?php echo JText::_('COM_JBLANCE_KEYWORDS'); ?>" />
  			<?php 
			$attribs = 'class="input-large" size="1"';
			$categtree = $select->getSelectCategoryTree('id_categ[]', $id_categ, 'COM_JBLANCE_ALL_CATEGORIES', $attribs, '', true);
			echo $categtree; ?>
  			<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SEARCH'); ?>" class="btn btn-primary" />
  			</div>
		</div>
	</div>
	<div class="sp10">&nbsp;</div>
	<?php 
	if($n){ ?>
	<div class="row-fluid">
	<ul class="thumbnails">
		<?php 
		for($i=0; $i < $n; $i++){
			$row = $this->rows[$i];
			$attachments = JBMediaHelper::processAttachment($row->attachment, 'service');		//from the list, show the first image
			$link_view	= JRoute::_('index.php?option=com_jblance&view=service&layout=viewservice&id='.$row->id);
			$sellerInfo = JFactory::getUser($row->user_id);
		?>
		<li class="span4 thumbfix">
			<div class="thumbnail">
				<a href="<?php echo $link_view; ?>"><img style="width: 300px; height: 150px;" src="<?php echo $attachments[0]['location']; ?>" /></a>
				<div class="caption">
					<div class="row-fluid">
						<div class="span6"><span class="boldfont"><?php echo JblanceHelper::formatCurrency($row->price); ?></span></div>
						<div class="span6 text-right"><span><small><i class="icon-time"></i> <?php echo JText::plural('COM_JBLANCE_N_DAYS', $row->duration); ?></small></span></div>
					</div>
					<div class="title_container"><a href="<?php echo $link_view; ?>"><?php echo $row->service_title; ?></a></div>
					<div class="">
						<?php
						$attrib = 'width=32 height=32 class=""';
						$avatar = JblanceHelper::getLogo($row->user_id, $attrib);
						echo !empty($avatar) ? LinkHelper::GetProfileLink($row->user_id, $avatar) : '&nbsp;' ?>
						<span><?php echo LinkHelper::GetProfileLink($row->user_id, $sellerInfo->username); ?></span>
					</div>
				</div>
			</div>
		</li>
		<?php
		} ?>
	</ul>
	</div>
	<?php	
	}
	else { ?>
	<div class="alert">
		<?php echo JText::_('COM_JBLANCE_NO_SERVICE_POSTED_OR_MATCHING_YOUR_QUERY'); ?>
	</div>
	<?php 
	}
	?>
	<div class="pagination pagination-centered clearfix">
		<div class="display-limit pull-right">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pageNav->getLimitBox(); ?>
		</div>
		<?php echo $this->pageNav->getPagesLinks(); ?>
	</div>
</form>