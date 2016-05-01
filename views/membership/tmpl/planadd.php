<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	16 March 2012
 * @file name	:	views/membership/tmpl/planadd.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows list of available Plans (jblance)
 */
defined('_JEXEC') or die('Restricted access');

//JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

$doc = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/simplemodal.css");
//$doc->addStyleSheet("components/com_jblance/css/pricing.css");
$doc->addScript("components/com_jblance/js/simplemodal.js");

$app = JFactory::getApplication();
$model = $this->getModel();
$user = JFactory::getUser();

$config = JblanceHelper::getConfig();
$currencysym = $config->currencySymbol;
$taxname = $config->taxName;
$taxpercent = $config->taxPercent;

$hasJBProfile = JblanceHelper::hasJBProfile($user->id);

JText::script('COM_JBLANCE_CLOSE');

$link_usergroup = JRoute::_('index.php?option=com_jblance&view=guest&layout=showfront', false);
$link_subscr_history = JRoute::_('index.php?option=com_jblance&view=membership&layout=planhistory');


$step = $app->input->get('step', 0, 'int');
$planInRow = 3; // number of plans in a row. Default is 3. Use values between 1 to 4 and do not go beyond
$span = round(12 / ($planInRow + 1));
$span = 'col-md-' . $span;
?>
<script type="text/javascript">
<!--
    function gotoRegistration() {
        var form = document.userFormJob;
        form.task.value = 'guest.grabplaninfo';

        if (validateForm()) {
            form.submit();
        }
    }
    function addSubscr() {
        var form = document.userFormJob;
        form.task.value = 'membership.upgradesubscription';
        if (validateForm()) {
            form.submit();
        }
    }
    function validateForm() {
        if (!jQuery("input[name='plan_id']:checked").length) {
            alert('<?php echo JText::_('COM_JBLANCE_PLEASE_CHOOSE_YOUR_PLAN', true); ?>');
            return false;
        } else {
            if (!jQuery("input[name='gateway']:checked").length) {
                alert('<?php echo JText::_('COM_JBLANCE_PLEASE_SELECT_PAYMENT_GATEWAY', true); ?>');
                return false;
            }
            return true;
        }
    }
    function checkZeroPlan(planAmt, planId) {
        if (planAmt == 0) {
            jQuery("#div-gateway").slideUp();
        } else {
            jQuery("#div-gateway").slideDown();
        }

        jQuery("label.active").removeClass("active btn-success");
        jQuery("#lbl_plan_id" + planId).addClass("active btn-success");

        jQuery("html, body").animate({
            scrollTop: jQuery("#div-gateway").offset().top
        }, 500);
    }
//-->
</script>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3><?php echo JText::_('COM_JBLANCE_BUY_SUBSCR'); ?>

            <?php if ($hasJBProfile) { ?>
                <div class="pull-right">
                    <a href="<?php echo $link_subscr_history; ?>" class="btn btn-primary"><i class="material-icons">access_time</i><?php echo JText::_('COM_JBLANCE_SUBSCR_HISTORY'); ?></a>
                </div>
                <?php
            }
            ?>
            <p>
        </h3>
    </div>
    <div class="panel-body">
<?php

        if ($step)
    echo JblanceHelper::getProgressBar($step);
?>
        <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormJob" enctype="multipart/form-data">

            <?php
            if ($hasJBProfile)
                echo JText::_('COM_JBLANCE_CHOOSE_SUBSCR_PAYMENT');
            else
                echo JText::_('COM_JBLANCE_SUBSCR_WELCOME');
            ?>
            </p>
            <?php
            if (!$hasJBProfile) {
                $session = JFactory::getSession();
                $ugid = $session->get('ugid', 0, 'register');
                $jbuser = JblanceHelper::get('helper.user');
                $groupName = $jbuser->getUserGroupInfo(null, $ugid)->name;
                echo JText::sprintf('COM_JBLANCE_USERGROUP_CHOSEN_CLICK_TO_CHANGE', $groupName, $link_usergroup);
            };
            ?>

            <?php
            if (empty($this->rows)) {
                echo '<p class="jbbox-warning">' . JText::_('COM_JBLANCE_NO_PLAN_ASSIGNED_FOR_USERGROUP') . '</p>';
            } else {
                $infos = $model->buildPlanInfo($this->rows[0]->id);
                //get the array of plan ids, the user has subscribed to.
                $planArray = array();
                foreach ($this->plans as $plan) {
                    $planArray[] = $plan->planid;
                }
                $totPlans = count($this->rows);
                for ($i = 0; $i < $totPlans; $i++) {
                    $row = $this->rows[$i];
                    $nprice = '';
                    if (($row->discount > 0) && in_array($row->id, $planArray) && ($row->price > 0)) {
                        $nprice = $row->price - (($row->price / 100) * $row->discount);
                        $npriceNoformat = $nprice;
                        $nprice = JblanceHelper::formatCurrency($nprice, true, false, 0);
                    }
                    $infos = $model->buildPlanInfo($row->id);

                    if ($i % $planInRow == 0) {
                        ?>
                        <div class="row">
                            <div class="pricing comparison">
                                <ul class="col-md-3">
                                    <li class="lead grey"><h3><?php echo JText::_('COM_JBLANCE_PLAN_NAME'); ?></h3></li>
                                    <li><?php echo JText::_('COM_JBLANCE_BONUS_FUND'); ?></li>
                                    <?php foreach ($infos as $info) { ?>
                                        <li><?php echo $info->key; ?></li>
                                    <?php } ?>
                                    <li class="lead grey"><h4><?php echo JText::_('COM_JBLANCE_PRICE'); ?></h4></li>
                                </ul>
                                <?php
                            }
                            ?>
                            <ul class="<?php echo $span; ?>">
                                <li class="lead blue"><h3><?php echo $row->name; ?></h3></li>
                                <li><?php echo JblanceHelper::formatCurrency($row->bonusFund, true, false, 0); ?></li>
                                <?php
                                foreach ($infos as $info) {
                                    ?>
                                    <li><?php echo $info->value; ?></li>
                                <?php } ?>
                                <li class="lead grey">
                                    <h4>
                                        <?php echo $nprice ? '<span style="float:left; color:red; text-decoration:line-through">' . ' ' . JblanceHelper::formatCurrency($row->price, true, false, 0) . '</span><span>' . $nprice . '</span>' : JblanceHelper::formatCurrency($row->price, true, false, 0); ?> <span class="divider">/</span> 
                                        <?php
                                        if ($row->days > 100 && $row->days_type == 'years')
                                            echo JText::_('COM_JBLANCE_LIFETIME');
                                        else {
                                            ?>
                                            <span class=""><?php echo $row->days . ' '; ?> </span>
                                            <?php
                                            echo getDaysType($row->days_type);
                                        }
                                        ?>
                                    </h4>
                                </li>
                                <li>
                                    <!-- Disable the plans if the limit is exceeded -->
                                    <?php if ($user->id > 0 && $row->time_limit > 0 && in_array($row->id, $planArray) && $this->plans[$row->id]->plan_count >= $row->time_limit) : ?>
                                        <button type="button" class="btn btn-block disabled" onclick="javascript:modalAlert('<?php echo JText::_('COM_JBLANCE_LIMIT_EXCEEDED', true); ?>', '<?php echo JText::sprintf('COM_JBLANCE_PLAN_PURCHASE_LIMIT_MESSAGE', $row->time_limit, array('jsSafe' => true)); ?>');"><?php echo JText::_('COM_JBLANCE_SELECT'); ?></button>
                                    <?php else: ?>
                                        <label for="plan_id<?php echo $row->id; ?>" id="lbl_plan_id<?php echo $row->id; ?>" class="btn btn-primary btn-block">
                                            <input type="radio" name="plan_id" id="plan_id<?php echo $row->id; ?>" value="<?php echo $row->id; ?>" class="jb-hidefield" onclick="javascript:checkZeroPlan('<?php echo $nprice ? $npriceNoformat : $row->price; ?>', '<?php echo $row->id; ?>');" />
                                            <?php echo JText::_('COM_JBLANCE_SELECT'); ?>
                                        </label>
                                    <?php endif; ?>
                                </li>
                            </ul>
                            <input type="hidden" name="planname<?php echo $row->id; ?>"   id="planname<?php echo $row->id; ?>"   value="<?php echo $row->name; ?>" />
                            <input type="hidden" name="planperiod<?php echo $row->id; ?>" id="planperiod<?php echo $row->id; ?>" value="<?php echo $row->days . ' ' . ucfirst(getDaysType($row->days_type)); ?>" />
                            <input type="hidden" name="plancredit<?php echo $row->id; ?>" id="plancredit<?php echo $row->id; ?>" value="<?php echo $row->bonusFund; ?>" />
                            <input type="hidden" name="price<?php echo $row->id; ?>" 	  id="price<?php echo $row->id; ?>" 	 value="<?php echo $nprice ? $nprice : $row->price; ?>" />
                            <?php if ($i % $planInRow == ($planInRow - 1) || $i == ($totPlans - 1)) { ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
                <div id="div-gateway" class="panel panel-default">
                    <div  class="panel-body">
                        <div class="form-group">
                            <label class="control-label" for="delivery"><?php echo JText::_('COM_JBLANCE_PAYMENT'); ?>:</label>
                            <div class="input-group">
                                <?php
                                $list_paymode = $model->getRadioPaymode('gateway', '', '');
                                echo $list_paymode;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($taxpercent > 0) { ?>
                    <p class="jbbox-info">
                        <?php echo JText::sprintf('COM_JBLANCE_TAX_APPLIES', $taxname, $taxpercent); ?>
                    </p>
                <?php }
                ?>

                <div class="form-actions">
                    <?php if ($hasJBProfile) : ?>
                        <input type="button" class="btn btn-primary btn-block" value="<?php echo JText::_('COM_JBLANCE_CONTINUE') ?>" onclick="addSubscr();"/>
                    <?php else : ?>
                        <input type="button" class="btn btn-primary btn-block" value="<?php echo JText::_('COM_JBLANCE_CONTINUE'); ?>" onclick="gotoRegistration();" />
                    <?php endif; ?>
                </div>

                <?php
            }
            ?>
            <input type="hidden" name="option" value="com_jblance">
            <input type="hidden" name="task" value="">
            <?php echo JHtml::_('form.token'); ?>
        </form>
        <?php

        function getDaysType($daysType) {
            if ($daysType == 'days')
                $lang = JText::_('COM_JBLANCE_DAYS');
            elseif ($daysType == 'weeks')
                $lang = JText::_('COM_JBLANCE_WEEKS');
            elseif ($daysType == 'months')
                $lang = JText::_('COM_JBLANCE_MONTHS');
            elseif ($daysType == 'years')
                $lang = JText::_('COM_JBLANCE_YEARS');
            return $lang;
        }
        ?>
    </div>
</div>