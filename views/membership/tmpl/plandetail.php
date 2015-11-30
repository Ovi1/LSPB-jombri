<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	06 August 2012
 * @file name	:	views/membership/tmpl/plandetail.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows plan details (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 $model = $this->getModel();
 $jbuser =JblanceHelper::get('helper.user');
 
 $config = JblanceHelper::getConfig();
 $dformat = $config->dateFormat;
?>
<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PLAN_DETAILS'); ?></div>

<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_JBLANCE_SUBSCR_INFO'); ?></legend>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?>: </label>
		<div class="controls">
			<?php echo $this->row->invoiceNo; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PLAN_NAME'); ?>: </label>
		<div class="controls">
			<?php echo $this->row->name; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_APPROVED'); ?>: </label>
		<div class="controls">
			<img src="components/com_jblance/images/s<?php echo $this->row->approved;?>.png" alt="Status">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_DATE_BUY'); ?>: </label>
		<div class="controls">
			<?php echo $this->row->date_buy != "0000-00-00 00:00:00" ? JHtml::_('date', $this->row->date_buy, $dformat.' H:i:s', true) :  "&nbsp;"; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PLAN_DURATION'); ?>: </label>
		<div class="controls">
			<?php echo $this->row->date_approval != "0000-00-00 00:00:00" ? JHtml::_('date', $this->row->date_approval, $dformat) :  "&nbsp;"; ?> &harr; <?php echo $this->row->date_expire != "0000-00-00 00:00:00" ? JHtml::_('date', $this->row->date_expire, $dformat) :  "&nbsp;"; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_JBLANCE_PAYMENT_INFO'); ?></legend>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_TAX'); ?>: </label>
		<div class="controls">
			<?php echo $this->row->tax_percent; ?>%
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_TOTAL_AMOUNT'); ?>: </label>
		<div class="controls">
			<?php echo JblanceHelper::formatCurrency($this->row->price); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PAY_MODE'); ?>: </label>
		<div class="controls">
			<?php echo JblanceHelper::getGwayName($this->row->gateway); ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_JBLANCE_FUND_INFO'); ?></legend>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_BONUS_FUND'); ?>: </label>
		<div class="controls">
			<?php echo JblanceHelper::formatCurrency($this->row->fund); ?>
		</div>
	</div>
		<?php 
		$ugroup = $jbuser->getUserGroupInfo($this->row->user_id, null);
		if($ugroup->allowPostProjects){
		?>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PROJECTS_LEFT'); ?>: </label>
		<div class="controls">
		<?php 
		if($this->row->projects_allowed > 0)
			echo $this->row->projects_left.' / '.$this->row->projects_allowed; 
		else
			echo JText::_('COM_JBLANCE_UNLIMITED');
		?>
		</div>
	</div>
		<?php	
		}
		if($ugroup->allowBidProjects){
		?>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_BIDS_LEFT'); ?>: </label>
		<div class="controls">
			<?php 
			if($this->row->bids_allowed > 0)
				echo $this->row->bids_left.' / '.$this->row->bids_allowed; 
			else
				echo JText::_('COM_JBLANCE_UNLIMITED');
			?>
		</div>
	</div>
		<?php	
		}
		?>
		<?php 
		$infos = $model->buildPlanInfo($this->row->planid);
		$html = "";
		foreach($infos as $info){
			$html .= "<div class=\"control-group\">";
			$html .= "<label class=\"control-label nopadding\">".$info->key.": </label>";
			$html .= "<div class=\"controls\">".$info->value."</div>";
			$html .= "</div>";
		}
		echo $html;
		?>
</fieldset>