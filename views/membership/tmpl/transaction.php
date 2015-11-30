<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	27 March 2012
 * @file name	:	views/membership/tmpl/transaction.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows user transactions (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 $user	= JFactory::getUser();
 $config = JblanceHelper::getConfig();
 $dformat = $config->dateFormat;
 $currencysym = $config->currencySymbol;
 $total_amt = JblanceHelper::getTotalFund($user->id);
 $total_withdraw = JblanceHelper::getWithdrawRequest($user->id);
 
 $action	= JRoute::_('index.php?option=com_jblance&view=membership&layout=transaction');
?>
<form action="<?php echo $action; ?>" method="post" name="userFormJob" enctype="multipart/form-data">	
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_TRANSACTION_HISTORY'); ?></div>
	<div class="well well-small pull-right span3 text-center font16">
		<b>
			<?php echo JText::_('COM_JBLANCE_CURRENT_BALANCE'); ?> : <?php echo JblanceHelper::formatCurrency($total_amt); ?>
			<?php if($total_withdraw > 0) : 
			$tipmsg = JHtml::tooltipText(JText::sprintf('COM_JBLANCE_AVAILABLE_BALANCE_LESS_THAN_CURRENT', JblanceHelper::formatCurrency($total_withdraw)));
			?>
			<img src="components/com_jblance/images/tooltip.png" class="hasTooltip" title="<?php echo $tipmsg; ?>"/>
			<?php endif; ?>
		</b>
	</div>
	<div class="clearfix"></div>
	
	<table class="table table-bordered table-hover table-striped">
		<caption><?php echo JText::_('COM_JBLANCE_LAST_TRANSACTION'); ?></caption>
		<tr>
			<td width="25%"><?php echo JText::_('COM_JBLANCE_DATE'); ?>:</td>
			<td width="75%"><?php  echo JHtml::_('date', $this->last_trans->date_trans, $dformat); ?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?>:</td>
			<td><?php echo $this->last_trans->transaction; ?></td>
		</tr>
		<tr>
			<?php
				if($this->last_trans->fund_plus > 0){
					$title = JText::_('COM_JBLANCE_PLUS');
					$value = $this->last_trans->fund_plus;
				}
				else {
					$title = JText::_('COM_JBLANCE_MINUS');
					$value = $this->last_trans->fund_minus;
				}
			?>
			<td><?php echo $title; ?>:</td>
			<td><?php echo JblanceHelper::formatCurrency($value); ?></td>
		</tr>
	</table>
	<div class="sp20">&nbsp;</div>
		
	<div id="no-more-tables">
	<table class="table table-bordered table-hover table-striped">
		<thead>
			<tr>
				<th>
					<?php echo JText::_('#'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JBLANCE_DATE'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_JBLANCE_MINUS')."($currencysym)"; ?>
				</th>	
				<th>
					<?php echo JText::_('COM_JBLANCE_PLUS')."($currencysym)"; ?>
				</th>			
			</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		for($i=0, $n=count($this->rows); $i < $n; $i++){
			$row = $this->rows[$i];
			?>
			<tr>
				<td data-title="#">
					<?php echo $this->pageNav->getRowOffset($i); ?>
				</td>
				<td data-title="<?php echo JText::_('COM_JBLANCE_DATE'); ?>" nowrap="nowrap">
					<?php  echo JHtml::_('date', $row->date_trans, $dformat); ?>				
				</td>
				<td data-title="<?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?>">
					<?php echo $row->transaction; ?>
				</td>
				<td data-title="<?php echo JText::_('COM_JBLANCE_MINUS'); ?>" class="text-right">
					<?php echo $row->fund_minus > 0  ? JblanceHelper::formatCurrency($row->fund_minus, false) : '-'; ?> 
				</td>
				<td data-title="<?php echo JText::_('COM_JBLANCE_PLUS'); ?>" class="text-right">
					<?php echo $row->fund_plus > 0  ? JblanceHelper::formatCurrency($row->fund_plus, false) : '-'; ?> 
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<div class="pagination pagination-centered clearfix">
						<div class="display-limit pull-right">
							<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
							<?php echo $this->pageNav->getLimitBox(); ?>
						</div>
						<?php echo $this->pageNav->getPagesLinks(); ?>
					</div>
				</td>
			</tr>
		</tfoot>
	</table>
	</div>

	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>