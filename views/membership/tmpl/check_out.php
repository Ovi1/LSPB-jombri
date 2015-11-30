<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	21 March 2012
 * @file name	:	views/membership/tmpl/check_out.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Displays the checkout page (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 $app			= JFactory::getApplication();
 $config 		= JblanceHelper::getConfig();
 $tax_name	 	= $config->taxName;
 $repeat 		= $app->input->get('repeat', 0, 'int');
 $type 			= $app->input->get('type', 'plan', 'string');
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormJob" enctype="multipart/form-data">
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_CHECKOUT'); ?></div>
	<!-- ************************************************************** plan checkout section ******************************************* -->
	<?php if($type == 'plan') : ?>
		<?php  
		$introtext = 'COM_JBLANCE_CHECKOUT_INFO'.($repeat ? '_REPEAT' : '' );
		echo JText::sprintf($introtext, '<b>'.$this->subscr->invoiceNo.'</b>' ); ?>
		<div class="sp10">&nbsp;</div>
		<div class="well well-small jbbox-gradient">
			<h3><?php echo JText::_('COM_JBLANCE_CART'); ?></h3>
			<table style="width: 100%;">
				<thead>
					<tr>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_NAME'); ?></th>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?></th>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_PAY_MODE'); ?></th>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_PLAN_DURATION'); ?></th>
					<th><?php echo JText::_('COM_JBLANCE_FUND'); ?></th>
					<th><?php echo JText::_('COM_JBLANCE_TOTAL'); ?></th>
					</tr>
				</thead>
				<tr>
					<td><?php echo $this->plan->name; ?></td>
					<td><?php echo $this->subscr->invoiceNo ;?></td>
					<td><?php echo JblanceHelper::getPaymodeInfo($this->subscr->gateway)->gateway_name; ?></td>
					<td><?php echo $this->plan->days.' '.getDaysType($this->plan->days_type); ?></td>
					<td class="text-right"><?php echo JblanceHelper::formatCurrency($this->subscr->fund, false); ?></td>
					<td class="text-right"><?php echo JblanceHelper::formatCurrency($this->subscr->price, false); ?></td>
				</tr>
				<tr>
					<td colspan="5" class="text-right"><?php echo $tax_name.' '.$this->subscr->tax_percent ;?>% :</td>
					<td colspan="4" class="text-right">
						<?php
							$taxamt = ($this->subscr->tax_percent/100)*$this->subscr->price;
							echo JblanceHelper::formatCurrency($taxamt, false);
						?>
					</td>
				</tr>
				<tr>
					<td colspan="5" class="text-right"> </td>
					<td colspan="4" class="text-right"><hr></td>
				</tr>
				<tr>
					<td colspan="5" class="text-right"><?php echo JText::_('COM_JBLANCE_TOTAL'); ?> :</td>
					<td colspan="4" class="text-right">
						<?php
							$total = $taxamt + $this->subscr->price;
							echo '<b>'.JblanceHelper::formatCurrency($total).'</b>';
						?>
					</td>
				</tr>
				<tr>
					<td colspan="7"><hr></td>
				</tr>
				<tr>
					<td colspan="7" align="center"><input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_JBLANCE_CHECKOUT'); ?>" /></td>
				</tr>
			</table>
		</div>
		
		<input type="hidden" name="id" value="<?php echo $this->subscr->id; ?>" />
		<input type="hidden" name="paymode" value="<?php echo $this->subscr->gateway; ?>" />
		<input type="hidden" name="price" value="<?php echo $total; ?>" />
		<!-- ************************************************************** deposit checkout section ******************************************* -->
		<?php elseif($type == 'deposit') : ?>
		<div class="sp10">&nbsp;</div>
		<div class="well well-small jbbox-gradient">
			<h2><?php echo JText::_('COM_JBLANCE_CART'); ?></h2>
			<table style="width: 100%;">
				<thead>
					<tr>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_NAME'); ?></th>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?></th>
					<th class="text-left"><?php echo JText::_('COM_JBLANCE_PAY_MODE'); ?></th>
					<th><?php echo JText::_('COM_JBLANCE_FUND'); ?></th>
					<th><?php echo JText::_('COM_JBLANCE_TOTAL'); ?></th>
					</tr>
				</thead>
				<tr>
					<td><?php echo JText::_('COM_JBLANCE_DEPOSIT_FUNDS'); ?></td>
					<td><?php echo $this->deposit->invoiceNo ;?></td>
					<td><?php echo JblanceHelper::getGwayName($this->deposit->gateway) ;?></td>
					<td class="text-right"><?php echo JblanceHelper::formatCurrency($this->deposit->amount, false); ?></td>
					<td class="text-right"><?php echo JblanceHelper::formatCurrency($this->deposit->amount, false); ?></td>
				</tr>
				<tr>
					<td colspan="4" class="text-right"><?php echo JText::_('COM_JBLANCE_DEPOSIT_FEE').' ('.JblanceHelper::formatCurrency($this->deposit->feeFixed).' + '.$this->deposit->feePerc.'%)' ;?>:</td>
					<td colspan="3" class="text-right">
						<?php
							$fee = ($this->deposit->feePerc/100)*$this->deposit->amount + $this->deposit->feeFixed;
							echo JblanceHelper::formatCurrency($fee, false);
						?>
					</td>
				</tr>
				<tr>
					<td colspan="4" class="text-right"> </td>
					<td colspan="3" class="text-right"><hr></td>
				</tr>
				<tr>
					<td colspan="4" class="text-right"><?php echo JText::_('COM_JBLANCE_TOTAL'); ?> :</td>
					<td colspan="3" class="text-right">
						<?php
							$total = $this->deposit->total;
							echo '<b>'.JblanceHelper::formatCurrency($total).'</b>';
						?>
					</td>
				</tr>
				<tr>
					<td colspan="7"><hr></td>
				</tr>
				<tr>
					<td colspan="7" align="center"><input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_JBLANCE_CHECKOUT'); ?>" /></td>
				</tr>
	
			</table>
		</div>
		<input type="hidden" name="id" value="<?php echo $this->deposit->id; ?>" />
		<input type="hidden" name="paymode" value="<?php echo $this->deposit->gateway; ?>" />
		<input type="hidden" name="price" value="<?php echo $total; ?>" />
		<?php endif; ?>
		
	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="task" value="membership.processpaymentmethod" />
	<input type="hidden" name="buy" value="<?php echo $type; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php 
	function getDaysType($daysType){
		if($daysType == 'days')
			$lang = JText::_('COM_JBLANCE_DAYS');
		elseif($daysType == 'weeks')
			$lang = JText::_('COM_JBLANCE_WEEKS');
		elseif($daysType == 'months')
			$lang = JText::_('COM_JBLANCE_MONTHS');
		elseif($daysType == 'years')
			$lang = JText::_('COM_JBLANCE_YEARS');
		return $lang;
	}
?>