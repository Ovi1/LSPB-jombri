<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	21 March 2012
 * @file name	:	views/membership/tmpl/bank_transfer.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Page showing details of bank for Bank Transfer mode (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 $bank_account  	= $this->payconfig->btAccnum;
 $bank_name    		= $this->payconfig->btBankname;
 $acc_holder_name	= $this->payconfig->btAccHoldername;
 $iban				= $this->payconfig->btIBAN;
 $swift				= $this->payconfig->btSWIFT;
 $emailnotify		= $this->payconfig->btNotifyEmail;
 $faxnofity			= $this->payconfig->btNotifyFaxno;

 $app  			= JFactory::getApplication();
 $config 		= JblanceHelper::getConfig();
 $tax_name	 	= $config->taxName;
 $link_balance	= JRoute::_('index.php?option=com_jblance&view=membership&layout=transaction');	
 $type 			= $app->input->get('type', 'plan', 'string');
?>
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PAYMENT_INFO'); ?></div>
	<div class="panel panel-default">
            <div class="panel-heading"><h3 class="jbj_manual"><?php echo JText::_('COM_JBLANCE_CART'); ?></h3></div>
            <div class="panel-body">
                <table class="table table-bordered table-responsive">
			<!-- ************************************************************** plan banktransfer section ******************************************* -->
			<?php if($type == 'plan') : ?>
			<thead>
			<tr>
				<th class="text-left"><?php echo JText::_('COM_JBLANCE_NAME'); ?></th>
				<th class="text-left"><?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?></th>
				<th><?php echo JText::_('COM_JBLANCE_FUND'); ?></th>
				<th><?php echo JText::_('COM_JBLANCE_TOTAL'); ?></th>
			</tr>
			</thead>
			<tr>
				<td><?php echo $this->plan->name; ?></td>
				<td><?php echo $this->subscr->invoiceNo ;?></td>
				<td class="text-right"><?php echo JblanceHelper::formatCurrency($this->subscr->fund, false); ?></td>
				<td class="text-right"><?php echo JblanceHelper::formatCurrency($this->subscr->price, false); ?></td>
			</tr>
			<tr>
				<td colspan="3" class="text-right"><?php echo $tax_name.' '.$this->subscr->tax_percent ;?>% :</td>
				<td colspan="2" class="text-right">
					<?php
						$taxamt = ($this->subscr->tax_percent/100) * $this->subscr->price;
						echo JblanceHelper::formatCurrency($taxamt, false);
					?>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="text-right"> </td>
				<td colspan="2" class="text-right"><hr></td>
			</tr>
			<tr>
				<td colspan="3" class="text-right"><?php echo JText::_('COM_JBLANCE_TOTAL'); ?> :</td>
				<td colspan="2" class="text-right">
					<?php
						$total = $taxamt + $this->subscr->price;
						echo '<b>'.JblanceHelper::formatCurrency($total).'</b>';
					?>
				</td>
			</tr>
			<tr>
				<td colspan="7"><hr></td>
			</tr>
			<!-- ************************************************************** deposit banktransfer section ******************************************* -->
			<?php elseif($type == 'deposit') : ?>
			<thead>
			<tr>
				<th class="text-left"><?php echo JText::_('COM_JBLANCE_NAME'); ?></th>
				<th class="text-left"><?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?></th>
				<th><?php echo JText::_('COM_JBLANCE_FUND'); ?></th>
				<th><?php echo JText::_('COM_JBLANCE_TOTAL'); ?></th>
			</tr>
			</thead>
			<tr>
				<td><?php echo JText::_('COM_JBLANCE_DEPOSIT_FUNDS'); ?></td>
				<td><?php echo $this->deposit->invoiceNo ;?></td>
				<td class="text-right"><?php echo JblanceHelper::formatCurrency($this->deposit->amount, false); ?></td>
				<td class="text-right"><?php echo JblanceHelper::formatCurrency($this->deposit->amount, false); ?></td>
			</tr>
			<tr>
				<td colspan="3" class="text-right"><?php echo JText::_('COM_JBLANCE_DEPOSIT_FEE').' ('.JblanceHelper::formatCurrency($this->deposit->feeFixed).' + '.$this->deposit->feePerc.'%)' ;?>:</td>
				<td colspan="2" class="text-right">
					<?php
						$fee = ($this->deposit->feePerc/100)*$this->deposit->amount + $this->deposit->feeFixed;
						echo JblanceHelper::formatCurrency($fee, false);
					?>
				</td>
			</tr>
<!--			<tr>
				<td colspan="3" class="text-right"> </td>
				<td colspan="2" class="text-right"><hr></td>
			</tr>-->
			<tr>
				<td colspan="3" class="text-right"><?php echo JText::_('COM_JBLANCE_TOTAL'); ?> :</td>
				<td colspan="2" class="text-right">
					<?php
						$total = $this->deposit->total;
						echo '<b>'.JblanceHelper::formatCurrency($total).'</b>';
					?>
				</td>
			</tr>
<!--			<tr>
				<td colspan="7"><hr></td>
			</tr>-->
			<?php endif; ?>
		</table>
	
	
	<table class="table table-bordered table-hover table-condensed">
		<thead>
			<tr>
				<th colspan="2"><?php echo JText::_('COM_JBLANCE_BANK_ACCOUNT_INFO'); ?> </th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo JText::_('COM_JBLANCE_BANK_NAME'); ?>:</td>
				<td><?php echo $bank_name; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JBLANCE_BANK_ACCOUNT_NAME'); ?>:</td>
				<td> <?php echo $acc_holder_name; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JBLANCE_ACCOUNT_NO'); ?>:</td>
				<td><?php echo $bank_account; ?></td>
			</tr>
		<?php if(!empty($iban)): ?>
			<tr>
				<td><?php echo JText::_('COM_JBLANCE_IBAN'); ?>:</td>
				<td><?php echo $iban; ?></td>
			</tr>
		<?php endif; ?>
			<?php if(!empty($swift)): ?>
			<tr>
				<td><?php echo JText::_('COM_JBLANCE_SWIFT'); ?>:</td>
				<td><?php echo $swift; ?></td>
			</tr>
		<?php endif; ?>
		</tbody>
		<thead>
			<tr>
			<th colspan="2"><?php echo JText::_('COM_JBLANCE_NOTIFICATION_INFO'); ?> </th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo JText::_('COM_JBLANCE_EMAIL'); ?>:</td>
				<td><?php echo $emailnotify; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JBLANCE_FAX'); ?>:</td>
				<td><?php echo $faxnofity; ?></td>
			</tr>
		</tbody>
	</table>
	
	<div class="jbbox-info">
		<?php echo JText::_('COM_JBLANCE_MANUAL_TRANSER_WHATS_NEXT'); ?>
	</div>
	
	<div class="form-actions">
		<input type="button" onclick="location.href='<?php echo $link_balance; ?>';" value="<?php echo JText::_('COM_JBLANCE_CONTINUE'); ?>" class="btn btn-success btn-block"/>
	</div>
                </div>
	</div>