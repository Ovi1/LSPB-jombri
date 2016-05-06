<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	13 November 2014
 * @file name	:	views/service/tmpl/servicesold.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	List of services buyer has bought (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('behavior.modal', 'a.jb-modal');
 
 $model 	  = $this->getModel();
 $user 		  = JFactory::getUser();
 $financeHelp = JblanceHelper::get('helper.finance');		// create an instance of the class FinanceHelper
 ?>
<div class="panel panel-default">
<div class="panel-body">
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_SERVICES_SOLD'); ?></div>

	<?php if(empty($this->rows)) : ?>
	<div class="alert alert-info">
  		<?php echo JText::_('COM_JBLANCE_NO_SERVICES_BOUGHT_YET'); ?>
	</div>
	<?php else : ?>
	<?php
	for($i=0; $i < count($this->rows); $i++){
		$row 		   = $this->rows[$i];
		$attachments   = JBMediaHelper::processAttachment($row->attachment, 'service');
		$link_progress = JRoute::_('index.php?option=com_jblance&view=service&layout=serviceprogress&id='.$row->id);	// id is the service order id and NOT service id
		$link_invoice  = JRoute::_('index.php?option=com_jblance&view=membership&layout=invoice&id='.$row->id.'&tmpl=component&print=1&type=service&usertype=freelancer');
		
		$hasPaid = $financeHelp->hasPaid($row->id, 'COM_JBLANCE_SERVICE');
	?>
	<div class="row">
		<div class="col-md-2">
			<img class="img-responsive" src="<?php echo $attachments[0]['thumb']; ?>" width="80px" />
		</div>
		<div class="col-md-6">
			<h3>
				<?php echo $row->service_title; ?>
			</h3>
			<p>
				<a href="<?php echo $link_progress; ?>" class="btn btn-primary btn-small"><?php echo JText::_('COM_JBLANCE_UPDATE_PROGRESS'); ?></a>
				<?php 
				$hasRated = $model->hasRated($row->id, $user->id);
				if($row->p_status == 'COM_JBLANCE_COMPLETED' && !$hasRated){ 
					$link_rate = JRoute::_('index.php?option=com_jblance&view=service&layout=rateservice&id='.$row->id); ?>
				<a href="<?php echo $link_rate; ?>" class="btn btn-primary btn-small"><?php echo JText::_('COM_JBLANCE_RATE_BUYER'); ?></a>
				<?php } ?>
				<?php 
				if($row->p_status == 'COM_JBLANCE_COMPLETED' && $hasPaid){ ?>
					<a rel="{handler: 'iframe', size: {x: 650, y: 500}}" href="<?php echo $link_invoice; ?>" class="jb-modal btn btn-success btn-small"><?php echo JText::_('COM_JBLANCE_PRINT_INVOICE'); ?></a>
				<?php 
				} ?>
			</p>
		</div>
		<div class="col-md-4 text-center">
			<span class="font20"><?php echo JblanceHelper::formatCurrency($row->totalprice, true, false, 0); ?></span> 
			<?php echo JText::_('COM_JBLANCE_IN'); ?> <span class="font16"><?php echo JText::plural('COM_JBLANCE_N_DAYS', $row->totalduration); ?></span>
			<div class="small">
				<span class="label label-success"><?php echo (!empty($row->p_status)) ? JText::_($row->p_status) : JText::_('COM_JBLANCE_NOT_YET_STARTED'); ?></span>
			</div>
			<?php if(!empty($row->p_status)){ ?>
			<div class="progress" title="<?php echo JText::_('COM_JBLANCE_PROGRESS').' : '.$row->p_percent.'%'; ?>">
				<div class="progress-bar progress-bar-success" style="width: <?php echo $row->p_percent; ?>%">
                                <?php echo JText::_('COM_JBLANCE_PROGRESS').' : '.$row->p_percent.'%'; ?>
                                </div>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="lineseparator"></div>
	<?php	
	}
	?>
	<?php endif; ?>

	<div class="pagination pagination-centered clearfix">
		<div class="display-limit pull-right">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pageNav->getLimitBox(); ?>
		</div>
		<?php echo $this->pageNav->getPagesLinks(); ?>
	</div>
</form>
</div>
</div>