<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	03 April 2012
 * @file name	:	views/membership/tmpl/withdrawfund.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Withdraw Fund Form (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 
 $app  			= JFactory::getApplication();
 $user 			= JFactory::getUser();
 $config 		= JblanceHelper::getConfig();
 $currencysym 	= $config->currencySymbol;
 $minWithdraw	= $config->withdrawMin;
 $totalFund 	= JblanceHelper::getTotalFund($user->id);
 
 $step 	  		= $app->input->get('step', '1', 'int');
 $gateway 		= $app->input->get('gateway', '', 'string');
 if($gateway)
 	$gwInfo = JblanceHelper::getPaymodeInfo($gateway);
?>
<script type="text/javascript">
<!--
function validateForm(f){
	var valid = document.formvalidator.isValid(f);
	var minWithdraw = parseInt('<?php echo $minWithdraw; ?>');

	if(jQuery("#amount").val() < minWithdraw){
		alert('<?php echo JText::sprintf('COM_JBLANCE_MINIMUM_WITHDRAW_AMOUNT_IS', JblanceHelper::formatCurrency($minWithdraw), array('jsSafe'=>true)); ?>');
		return false;				
	}
	else {
		if(valid == true){
			
	    }
	    else {
			var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
	    	if(jQuery("#amount").hasClass("invalid")){
		    	msg = msg+'\n\n* '+'<?php echo JText::_('COM_JBLANCE_PLEASE_ENTER_AMOUNT_IN_NUMERIC_ONLY', true); ?>';
		    }
			alert(msg);
			return false;
	    }
		return true;
	}
}
//-->
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm" id="userForm" class="form-validate form-horizontal" onsubmit="return validateForm(this)">

	<?php if($step == 1) : ?>
		<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_WITHDRAW_FUNDS'); ?></div>
		<table class="table table-bordered table-hover table-striped">
			<thead>
				<tr>
					<th width="35%"><?php echo JText::_('COM_JBLANCE_METHOD'); ?></th>
					<th width="40%"><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?></th>
					<th width="15%"><?php echo JText::_('COM_JBLANCE_FEE'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			for($i=0, $n=count($this->paymodes); $i < $n; $i++){
				$mode = $this->paymodes[$i]; 
				$link_step2	 = JRoute::_('index.php?option=com_jblance&view=membership&layout=withdrawfund&step=2&gateway='.$mode->gwcode); ?>
				<tr>
					<td>
						<a href="<?php echo $link_step2; ?>"><?php echo $mode->gateway_name; ?></a>
					</td>
					<td>
						<?php echo $mode->withdrawDesc; ?>
					</td>
					<td>
						<?php echo JblanceHelper::formatCurrency($mode->withdrawFee); ?>
					</td>
				</tr>
			<?php
			}
			?>
			</tbody>	
		</table>
	<?php elseif($step == 2) : ?>
		<div class="jbl_h3title"><?php echo JText::sprintf('COM_JBLANCE_WITHDRAW_VIA', $gwInfo->gateway_name); ?></div>
		<div class="control-group">
			<label class="control-label" for="gateway"><?php echo JText::_('COM_JBLANCE_PAYMENT_METHOD'); ?> :</label>
			<div class="controls">
				<?php echo $gwInfo->gateway_name; ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="amount"><?php echo JText::_('COM_JBLANCE_WITHDRAW_AMOUNT'); ?> :</label>
			<div class="controls">
				<div class="input-prepend">
					<span class="add-on"><?php echo $currencysym; ?></span>
					<input type="text" name="amount" id="amount" class="input-small required validate-numeric" />
				</div>
				<span class="help-inline">
					<em>(<?php echo JText::_('COM_JBLANCE_YOUR_BALANCE').' : '.JblanceHelper::formatCurrency($totalFund); ?>)</em><br>
					<em>(<?php echo JText::_('COM_JBLANCE_MIN_AMOUNT').' : '.JblanceHelper::formatCurrency($minWithdraw); ?>)</em><br>
				</span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo JText::_('COM_JBLANCE_WITHDRAWAL_FEE'); ?>:</label>
			<div class="controls">
				<?php echo JblanceHelper::formatCurrency($gwInfo->withdrawFee); ?>	
				<input type="hidden" name=withdrawFee value="<?php echo $gwInfo->withdrawFee; ?>" />
			</div>
		</div>
		<?php
		// Iterate through the fields and display them.
		foreach($this->form->getFieldset('withdraw') as $field):
		    // If the field is hidden, only use the input.
		    if ($field->hidden):
		        echo $field->input;
		    else:
		    ?>
		    <div class="control-group">
		        <?php echo $field->label; ?>
		        <div class="controls"><?php echo $field->input ?></div>
		   </div>
		    <?php
		    endif;
		endforeach;
		?>
		
		<div class="form-actions">
			<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SUBMIT')?>" class="btn btn-primary" />
		</div>
	<?php elseif($step == 3) : ?>

	<?php endif; ?>
	
	<input type="hidden" name="option" value="com_jblance" />			
	<input type="hidden" name="task" value="membership.savewithdrawfund" />
	<input type="hidden" name="gateway" value="<?php echo $gateway; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>