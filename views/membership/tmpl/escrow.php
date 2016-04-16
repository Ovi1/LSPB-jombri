<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	02 April 2012
 * @file name	:	views/membership/tmpl/escrow.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Escrow Payment Form (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 
 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");
 
 $user = JFactory::getUser();
 $config = JblanceHelper::getConfig();
 $currencysym = $config->currencySymbol;
 
 $totalFund = JblanceHelper::getTotalFund($user->id);
 
 JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
function validateForm(f){
	var valid = document.formvalidator.isValid(f);
	
	if(valid == true){
		
    }
    else {
		var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
    	if($("amount").hasClass("invalid")){
	    	msg = msg+'\n\n* '+'<?php echo JText::_('COM_JBLANCE_PLEASE_ENTER_AMOUNT_IN_NUMERIC_ONLY', true); ?>';
	    }
		alert(msg);
		return false;
    }
	return true;
}

jQuery(document).ready(function($){
	jQuery("#pay_for").on("keyup", function(e){
		var pay_for = jQuery("#pay_for").val();
		var amt = parseFloat(jQuery("#bid_amount").val() * jQuery("#pay_for").val());
		jQuery("#amount").val(amt);
	});

	jQuery("input[name='reason']").on("click", updateReason);
});

function updateReason(){
	if(jQuery("#full_payment_option:checked").length || jQuery("#partial_payment_option:checked").length){
		jQuery("#projectBox").css("display", "block");
		jQuery("#project_id").addClass("required").prop("required", "required");
	}
	else if(jQuery("#other_reason_option:checked").length){
		jQuery("#projectBox").css("display", "none");
		if(jQuery("#project_id"))
			jQuery("#project_id").removeClass("required").removeProp("required").val("");
		jQuery("#pay_for").removeClass("required").removeProp("required");
		jQuery("#div_pay_for").css("display", "none");
	}
}
//-->
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormProject" id="userFormProject" class="form-validate form-horizontal" onsubmit="return validateForm(this);">
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_ESCROW_PAYMENT'); ?></div>
	<div class="row">
		<div class="span6">
			<strong><?php echo JText::_('COM_JBLANCE_PLEASE_SELECT_ONE_OF_THE_FOLLOWING'); ?>:</strong>
			<p>
				<label class="radio">
					<input type="radio" name="reason" id="full_payment_option" value="full_payment" checked /><?php echo JText::_('COM_JBLANCE_FULL_FINAL_PAYMENT_FOR_COMPLETED_PROJECT'); ?>
				</label>
				<label class="radio">
					<input type="radio" name="reason" id="partial_payment_option" value="partial_payment" /> <?php echo JText::_('COM_JBLANCE_PARTIAL_PAYMENT_FOR_PROJECT'); ?>
				</label>
				<label class="radio">
					<input type="radio" name="reason" id="other_reason_option" value="other" /> <?php echo JText::_('COM_JBLANCE_OTHER_REASON'); ?>
				</label>
			</p>
		</div>
	</div>
	<div class="lineseparator"></div>
	
	<div class="control-group" id="projectBox">
		<label class="control-label" for="project_id"><?php echo JText::_('COM_JBLANCE_PROJECT'); ?> :</label>
		<div class="controls">
			<?php echo $this->lists; ?>
			<input type="hidden" name="proj_balance" id="proj_balance" value="" />
			<strong><span id="proj_balance_div" class="help-inline"></span></strong>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="recipient"><?php echo JText::_('COM_JBLANCE_USERNAME'); ?> :</label>
		<div class="controls">
			<input type="text" name="recipient" id="recipient" value="" class="form-control required" onchange="checkUsername(this);" />
			<span id="status_recipient" class="help-inline"></span>
		</div>
	</div>
	<div class="control-group" id="div_pay_for" style="display: none;">
		<label class="control-label" for="pay_for"><?php echo JText::_('COM_JBLANCE_PAY_FOR'); ?> :</label>
		<div class="controls">
			<div class="input-append">
				<input type="text" name="pay_for" id="pay_for" class="input-small required validate-numeric" />
				<span class="add-on"><?php echo JText::_('COM_JBLANCE_HOURS'); ?></span>
			</div>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="amount"><?php echo JText::_('COM_JBLANCE_AMOUNT'); ?> :</label>
		<div class="controls">
			<div class="input-prepend">
				<span class="add-on"><?php echo $currencysym; ?></span>
				<input type="text" name="amount" id="amount" class="input-small required validate-numeric" value="" />
				<input type="hidden" name="bid_amount" id="bid_amount" value="" />
			</div>
			<span class="help-inline">
				<em>(<?php echo JText::_('COM_JBLANCE_YOUR_BALANCE').' : '.JblanceHelper::formatCurrency($totalFund); ?>)</em>
			</span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="note"><?php echo JText::_('COM_JBLANCE_NOTES'); ?> :</label>
		<div class="controls">
			<input type="text" name="note" id="note" class="input-xlarge" />
		</div>
	</div>
	<div class="form-actions">
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_TRANSFER')?>" class="btn btn-primary" />
	</div>
	
	<input type="hidden" name="option" value="com_jblance" />			
	<input type="hidden" name="task" value="membership.saveescrow" />
	<?php echo JHtml::_('form.token'); ?>
</form>