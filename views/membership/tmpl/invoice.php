<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	06 August 2012
 * @file name	:	views/membership/tmpl/invoice.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Generate and Print invoice (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 $app 			 = JFactory::getApplication();
 $config 		 = JblanceHelper::getConfig();
 $dformat 		 = $config->dateFormat;
 $invoicedetails = $config->invoiceDetails;
 $tax_name	 	 = $config->taxName;
 
 $type 		= $app->input->get('type', '', 'string');
 $usertype 	= $app->input->get('usertype', '', 'string');
?>
	<button type="button" class="btn" onclick="window.print();"><i class="icon-print"></i> <?php echo JText::_('COM_JBLANCE_PRINT'); ?></button>
	<div class="sp10">&nbsp;</div>
	<table style="width: 100%;">
		<tr>
			<?php if($type == 'project') { ?>
			<td style="background: #ccc; padding: 20px"><strong><?php echo JText::_('COM_JBLANCE_PROJECTID'); ?></strong>: <?php echo $this->row->id; ?></td>
			<?php } elseif($type == 'service') { ?>
			<td style="background: #ccc; padding: 20px"><strong><?php echo JText::_('COM_JBLANCE_SERVICE_ORDER_ID'); ?></strong>: <?php echo $this->row->id; ?></td>
			<?php } else { ?>
			<td style="background: #ccc; padding: 20px"><strong><?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?></strong>: <?php echo $this->row->invoiceNo; ?></td>
			<?php }?>
			<td style="background: #ccc; padding: 20px"><strong><?php echo JText::_('COM_JBLANCE_INVOICE_DATE'); ?></strong>: <?php echo JHtml::_('date', $this->row->invoiceDate, $dformat, true); ?></td>
		</tr>
		<tr>
			<td valign="top" style="padding: 20px">
				<?php echo JText::_('COM_JBLANCE_INVOICE_TO'); ?>:<br />
				<address>
					<strong><?php echo !empty($this->row->biz_name) ? $this->row->biz_name.'<br/>' : ''; ?></strong>
					<strong><?php echo $this->row->name; ?></strong><br />
					<?php echo $this->row->address; ?><br>
					<?php echo JblanceHelper::getLocationNames($this->row->id_location, 'only-location', ','); ?><br>
					<?php echo $this->row->postcode; ?><br>
					<?php echo JText::_('COM_JBLANCE_EMAIL'); ?>: <?php echo $this->row->email; ?>
				</address>
			</td>
			<td valign="top" style="padding: 20px">
				<?php echo JText::_('COM_JBLANCE_PROVIDED_BY'); ?>:<br />
				<address>
					<strong><?php echo $app->get('sitename');?></strong> <br />
					<?php echo JUri::base(); ?><br/>
					<?php echo $invoicedetails; ?>
				</address>
			</td>
		</tr>
		<?php if(!($type == 'project' || $type == 'service')) { ?>
		<tr>
			<td style="background: #ccc; padding: 10px" colspan="2"><strong><?php echo JText::_('COM_JBLANCE_PAY_MODE'); ?>:</strong> <?php echo JblanceHelper::getGwayName($this->row->gateway); ?></td>
		</tr>
		<?php } ?>
	</table>
	<table style="width: 100%;">
		<thead>
		<tr>
			<th align="left"><?php echo JText::_('COM_JBLANCE_DATE'); ?></th>
			<th align="left"><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?></th>
			<th align="left"><?php echo JText::_('COM_JBLANCE_STATUS'); ?></th>
			<th align="right"><?php echo JText::_('COM_JBLANCE_AMOUNT'); ?></th>
		</tr>
		</thead>
		<!-- section for deposit fund -->
		<?php if($type == 'deposit'): ?>
		<tr>
			<td><?php echo JHtml::_('date', $this->row->invoiceDate, $dformat, true); ?></td>
			<td><?php echo JText::_('COM_JBLANCE_DEPOSIT_FUNDS'); ?></td>
			<td><?php echo JblanceHelper::getPaymentStatus($this->row->approved); ?></td>
			<td style="text-align:right;"><?php echo JblanceHelper::formatCurrency($this->row->amount); ?></td>
		</tr>
			<?php 
			$subtotalName = JText::_('COM_JBLANCE_DEPOSIT_FEE').' (+)';
			$subtotalAmt = $this->row->total - $this->row->amount;
			$total = $this->row->total;
			?>
		<!-- section for deposit fund END-->
		
		<!-- section for withdraw fund -->
		<?php elseif($type == 'withdraw'): ?>
		<tr>
			<td><?php echo JHtml::_('date', $this->row->invoiceDate, $dformat, true); ?></td>
			<td><?php echo JText::_('COM_JBLANCE_WITHDRAW_FUNDS'); ?></td>
			<td><?php echo JblanceHelper::getPaymentStatus($this->row->approved); ?></td>
			<td style="text-align:right;"><?php echo JblanceHelper::formatCurrency($this->row->amount); ?></td>
		</tr>
			<?php 
			$subtotalName = JText::_('COM_JBLANCE_WITHDRAWAL_FEE').' (-)';
			$subtotalAmt = $this->row->withdrawFee;
			$total = $this->row->finalAmount;
			?>
		<!-- section for withdraw fund END-->
		
		<!-- section for subscription -->
		<?php elseif($type == 'plan'): ?>
		<tr>
			<td><?php echo JHtml::_('date', $this->row->invoiceDate, $dformat, true); ?></td>
			<td><?php echo JText::sprintf('COM_JBLANCE_PURCHASE_OF', $this->row->planname); ?></td>
			<td><?php echo JblanceHelper::getPaymentStatus($this->row->approved); ?></td>
			<td style="text-align:right;"><?php echo JblanceHelper::formatCurrency($this->row->price); ?></td>
		</tr>
			<?php 
			$subtotalName = $tax_name.' '.$this->row->tax_percent.' %'.' (+)';
			$subtotalAmt = ($this->row->tax_percent/100)*$this->row->price;
			$total = $subtotalAmt + $this->row->price;
			?>
		<!-- section for subscription END-->
		
		<!-- section for project -->
		<?php elseif($type == 'project'): ?>
		<tr>
			<td><?php echo JHtml::_('date', $this->row->invoiceDate, $dformat, true); ?></td>
			<td><?php echo JText::sprintf('COM_JBLANCE_PROJECT_COMMISSION_FOR_PROJECT_NAME', '<b>'.$this->row->project_title.'</b>'); ?></td>
			<td><?php echo JblanceHelper::getPaymentStatus(1); ?></td>
			<td style="text-align:right;">(-) <?php echo JblanceHelper::formatCurrency($this->row->commission_amount); ?></td>
		</tr>
		<?php 
		$totalPaid = 0;
		for($i=0, $n=count($this->escrows); $i < $n; $i++){
			$escrow = $this->escrows[$i];
			$totalPaid += $escrow->amount;
		?>
		<tr>
			<td nowrap="nowrap"><?php echo JHtml::_('date', $escrow->date_accept, $dformat, true); ?></td>
			<td>
				<?php 
				if($escrow->project_type == 'COM_JBLANCE_FIXED')
					echo JText::_('COM_JBLANCE_ESCROW_PAYMENT'); 
				if($escrow->project_type == 'COM_JBLANCE_HOURLY')
					echo JText::sprintf('COM_JBLANCE_PAYMENT_FOR_HOURS', $escrow->pay_for);
				?>
			</td>
			<td><?php echo '-'; ?></td>
			<td style="text-align:right;"><?php echo JblanceHelper::formatCurrency($escrow->amount); ?></td>
		</tr>
		<?php 
		}
		?>
			<?php 
			$subtotalName = JText::_('COM_JBLANCE_SUBTOTAL');
			$subtotalAmt = -$this->row->commission_amount + $totalPaid;
			$total = -$this->row->commission_amount + $totalPaid;
			?>
		<!-- section for project END-->
		
		<!-- section for service -->
		<?php elseif($type == 'service'): ?>
		<?php 
		$totalPaid = 0;
		for($i=0, $n=count($this->escrows); $i < $n; $i++){
			$escrow = $this->escrows[$i];
			$totalPaid += $escrow->amount;
		?>
		<tr>
			<td nowrap="nowrap"><?php echo JHtml::_('date', $escrow->date_accept, $dformat, true); ?></td>
			<td>
				<?php 
				echo JText::_('COM_JBLANCE_ESCROW_PAYMENT'); 
				?>
			</td>
			<td><?php echo '-'; ?></td>
			<td style="text-align:right;"><?php echo ($usertype=='freelancer') ? '' : '(-) '; ?><?php echo JblanceHelper::formatCurrency($escrow->amount); ?></td>
		</tr>
		<?php 
		}
		?>
			<?php 
			$subtotalName = JText::_('COM_JBLANCE_SUBTOTAL');
			$subtotalAmt = ($usertype == 'freelancer') ? ($totalPaid) : ($totalPaid);
			$total = $subtotalAmt;
			?>
		<!-- section for service END-->
		<?php endif; ?>
		<tr>
			<td colspan="3" align="right"><?php echo $subtotalName; ?>:</td>
			<td colspan="1" align="right">
				<?php echo JblanceHelper::formatCurrency($subtotalAmt); ?>
			</td>
		</tr>
		<tr>
			<td colspan="3" align="right"> </td>
			<td colspan="1" align="right"><hr></td>
		</tr>
		<tr>
			<td colspan="3" align="right"><?php echo JText::_('COM_JBLANCE_TOTAL'); ?> :</td>
			<td colspan="1" align="right">
				<?php echo '<b>'.JblanceHelper::formatCurrency($total, true, true).'</b>'; ?>
			</td>
		</tr>
		<tr>
			<td colspan="3"><?php echo JText::_('COM_JBLANCE_WE_THANK_YOU_FOR_YOUR_BUSINESS'); ?></td>
		</tr>
	</table>