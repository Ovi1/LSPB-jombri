<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	02 April 2012
 * @file name	:	views/membership/tmpl/depositfund.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Fund Deposit Form (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.tooltip');

$model = $this->getModel();
$config = JblanceHelper::getConfig();

$currencysym = $config->currencySymbol;
$minFund = $config->fundDepositMin;
?>
<script type="text/javascript">
<!--
    function validateForm(f) {
        var valid = document.formvalidator.isValid(f);
        var minFund = parseInt('<?php echo $minFund; ?>');

        if (jQuery("#amount").val() < minFund) {
            alert('<?php echo JText::sprintf('COM_JBLANCE_MINIMUM_DEPOSIT_AMOUNT_IS', JblanceHelper::formatCurrency($minFund), array('jsSafe' => true)); ?>');
            return false;
        } else {
            if (valid == true) {

            } else {
                var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
                if (jQuery("#amount").hasClass("invalid")) {
                    msg = msg + '\n\n* ' + '<?php echo JText::_('COM_JBLANCE_PLEASE_ENTER_AMOUNT_IN_NUMERIC_ONLY', true); ?>';
                }
                alert(msg);
                return false;
            }
            return true;
        }
    }
//-->	
</script>
<div class="panel panel-default">
    <div class="panel-heading"><h3><?php echo JText::_('COM_JBLANCE_DEPOSIT_FUNDS'); ?></h3></div>
    <div class="panel-body">
        <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm" id="userForm" class="form-validate form-inline" onsubmit="return validateForm(this);">
        
            <div class="form-group">
                <label for="amount" class="control-label"><?php echo JText::_('COM_JBLANCE_DEPOSIT_AMOUNT'); ?>:</label>
                <div class="input-group">
                    <input type="text" name="amount" id="amount" class="form-control required validate-numeric" />
                    <span class="input-group-addon"><?php echo $currencysym; ?></span>
            
                 

                </div>
            </div>

            <div class="form-group">
                <label class="control-label" for="gateway"><?php echo JText::_('COM_JBLANCE_PAYMENT_METHOD'); ?>:</label>

                    <?php
                    $list_paymode = $model->getSelectPaymode('gateway', '', '');
                    echo $list_paymode;
                    ?>	
       
            </div>
            <div class="form-group">
            <div class="input-group">
                <input type="submit" value="<?php echo JText::_('COM_JBLANCE_CONTINUE') ?>" class="btn btn-success" />
            </div>
            </div>
        <p class="help-block">
                        <?php echo JText::sprintf('COM_JBLANCE_MINIMUM_DEPOSIT_AMOUNT_IS', JblanceHelper::formatCurrency($minFund)); ?>
                </p>
            <input type="hidden" name="option" value="com_jblance" />			
            <input type="hidden" name="task" value="membership.savedepositfund" />	
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>