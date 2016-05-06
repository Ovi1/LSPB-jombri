<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	08 August 2012
 * @file name	:	views/membership/tmpl/thankpayment.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Thanks page after payment (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('behavior.modal', 'a.jb-modal');
 
 $app  = JFactory::getApplication();
 $type = $app->input->get('type', '', 'string');
?>
<!-- display pages based on the type -->
<div class="panel panel-default">
<div class="panel-body">
<?php if($type == 'cancel') : ?>
<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PAYMENT_CANCELLED'); ?></div>
<p class="jbbox-warning">
	<span class="font16"><?php echo JText::_('COM_JBLANCE_YOUR_PAYMENT_IS_CANCELLED'); ?></span>
</p>

<?php else : ?>
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_JBLANCE_THANK_YOU'); ?></legend>
	<p class="text-center text-success"><?php echo JText::_('COM_JBLANCE_WE_THANK_YOU_FOR_YOUR_PAYMENT'); ?></p>
	
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_ITEM_NAME'); ?>: </label>
		<div class="controls">
			<?php echo $this->row->itemName; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?>: </label>
		<div class="controls">
			<?php echo $this->row->invoiceNo; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_APPROVED'); ?>: </label>
		<div class="controls">
			<img src="components/com_jblance/images/s<?php echo $this->row->status;?>.png" alt="Status">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PAY_MODE'); ?>: </label>
		<div class="controls">
			<?php echo $this->row->gateway; ?>
		</div>
	</div>
	<div class="form-actions">
		<a rel="{handler: 'iframe', size: {x: 650, y: 500}}" href="<?php echo $this->row->lnk_invoice; ?>" class="jb-modal btn btn-info"><span><?php echo JText::_('COM_JBLANCE_PRINT_INVOICE'); ?></span></a>
		<a href="<?php echo $this->row->lnk_continue; ?>" class="btn btn-success"><span><?php echo JText::_('COM_JBLANCE_CONTINUE'); ?></span></a>
	</div>
</fieldset>
	
<?php endif; ?>
</div>
</div>