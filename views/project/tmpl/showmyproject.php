<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	23 March 2012
 * @file name	:	views/project/tmpl/showmyproject.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	List of projects posted by the user (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('behavior.modal', 'a.jb-modal');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/simplemodal.js");
$doc->addStyleSheet("components/com_jblance/css/simplemodal.css");

$model = $this->getModel();
$projhelp = JblanceHelper::get('helper.project');  // create an instance of the class ProjectHelper
$user = JFactory::getUser();
$config = JblanceHelper::getConfig();
$enableEscrowPayment = $config->enableEscrowPayment;
$showUsername = $config->showUsername;
$dformat = $config->dateFormat;
$sealProjectBids = $config->sealProjectBids;
$nameOrUsername = ($showUsername) ? 'username' : 'name';

$plan = JblanceHelper::whichPlan($user->id);
$chargePerProject = $plan->buyChargePerProject;

JText::script('COM_JBLANCE_CLOSE');
JText::script('COM_JBLANCE_YES');

$link_edit_project = JRoute::_('index.php?option=com_jblance&view=project&layout=editproject');
?>

<div class="panel panel-default">
    <div class="panel-heading"><h3>
            <?php echo JText::_('COM_JBLANCE_MY_PROJECTS'); ?>

            <p class="pull-right"><a href="<?php echo $link_edit_project; ?>" class="btn btn-primary"><?php echo JText::_('COM_JBLANCE_POST_NEW_PROJECT'); ?></a></p>
        </h3>

    </div>
    <div class="panel-body">
        <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">

            <?php if (empty($this->rows)) : ?>
                <div class="alert alert-info">
                    <?php echo JText::_('COM_JBLANCE_NO_PROJECTS_YET'); ?>
                </div>
            <?php else : ?>
                <?php
                for ($i = 0; $i < count($this->rows); $i++) {
                    $row = $this->rows[$i];

                    $link_edit = JRoute::_('index.php?option=com_jblance&view=project&layout=editproject&id=' . $row->id);
                    $link_pick_user = JRoute::_('index.php?option=com_jblance&view=project&layout=pickuser&id=' . $row->id);
                    $link_transfer = JRoute::_('index.php?option=com_jblance&view=membership&layout=escrow');
                    $link_del = JRoute::_('index.php?option=com_jblance&task=project.removeproject&id=' . $row->id . '&' . JSession::getFormToken() . '=1');
                    $link_reopen_proj = JRoute::_('index.php?option=com_jblance&task=project.reopenproject&id=' . $row->id . '&' . JSession::getFormToken() . '=1');
                    $link_repost_proj = JRoute::_('index.php?option=com_jblance&task=project.repostproject&id=' . $row->id . '&' . JSession::getFormToken() . '=1');
                    $bidsCount = $model->countBids($row->id);
                    $bidInfo = $projhelp->getBidInfo($row->id, $row->assigned_userid);
                    $link_invoice = JRoute::_('index.php?option=com_jblance&view=membership&layout=invoice&id=' . $row->id . '&tmpl=component&print=1&type=project&usertype=buyer');
                    $link_pay_comp = JRoute::_('index.php?option=com_jblance&task=project.paymentcomplete&id=' . $row->id . '&' . JSession::getFormToken() . '=1');
                    $link_invite_user = JRoute::_('index.php?option=com_jblance&view=project&layout=inviteuser&id=' . $row->id);

                    //if the project is private-invite but has not chosen users, alert
                    $noInvitees = false;
                    if ($row->is_private_invite && empty($row->invite_user_id)) {
                        $noInvitees = true;
                    }
                    ?>
                    <div id="myprojects" class="col-md-4">

                        <?php echo $model->getLabelProjectStatus($row->status); ?>
                        <div class="thumbnail">
                            <h5>
                                <?php echo LinkHelper::getProjectLink($row->id, $row->project_title); ?>
                                <?php
                                if ($row->approved == 0)
                                    echo '&nbsp;<span class="label label-danger">' . JText::_('COM_JBLANCE_PENDING_APPROVAL') . '</span>';
                                ?>
                               <span class="label label-success"><?php echo (!empty($bidInfo->p_status)) ? JText::_($bidInfo->p_status) : JText::_('COM_JBLANCE_NOT_YET_STARTED'); ?></span>
                               <span class="badge badge-info" data-toggle="tooltip" data-placement="top" title="<?php echo  JText::_('COM_JBLANCE_BIDS'); ?>"><?php echo $bidsCount; ?></span>
                                                  <?php if (($row->buyer_commission > 0) && ($row->status == 'COM_JBLANCE_CLOSED')) { ?>
                                    <a rel="{handler: 'iframe', size: {x: 650, y: 500}}" href="<?php echo $link_invoice; ?>" class="jb-modal btn btn-link btn-sm"><?php echo JText::_('COM_JBLANCE_PRINT_INVOICE'); ?></a>
                                    <?php if (!empty($bidInfo->attachment)) : ?>
                                        <div style="display: inline;">
                                            <?php echo LinkHelper::getDownloadLink('nda', $bidInfo->bidid, 'project.download', 'btn btn-primary btn-small'); ?>
                                        </div>
                                        <?php
                                    endif;
                                    ?>
                                <?php } ?> 
                               <ul class="promotions" style="margin-top: 5px;">
                                    <?php if ($row->is_featured) : ?>
                                        <li data-promotion="featured"><?php echo JText::_('COM_JBLANCE_FEATURED'); ?></li>
                                    <?php endif; ?>
                                    <?php if ($row->is_private) : ?>
                                        <li data-promotion="private"><?php echo JText::_('COM_JBLANCE_PRIVATE'); ?></li>
                                    <?php endif; ?>
                                    <?php if ($row->is_urgent) : ?>
                                        <li data-promotion="urgent"><?php echo JText::_('COM_JBLANCE_URGENT'); ?></li>
                                    <?php endif; ?>
                                    <?php if ($sealProjectBids || $row->is_sealed) : ?>
                                        <li data-promotion="sealed"><?php echo JText::_('COM_JBLANCE_SEALED'); ?></li>
                                    <?php endif; ?>
                                    <?php if ($row->is_nda) : ?>
                                        <li data-promotion="nda"><?php echo JText::_('COM_JBLANCE_NDA'); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </h5>
                             <div class="actions">
                                <?php
                                if ($row->status == 'COM_JBLANCE_OPEN' || $row->status == 'COM_JBLANCE_EXPIRED') {
                                    $expiredate = JFactory::getDate();
                                    $expiredate->modify("+$row->expires days");
                                    $expiredate = JHtml::_('date', $expiredate, $dformat, false);
                                    $repostConfirmMessage = JText::sprintf('COM_JBLANCE_CONFIRM_REPOST_PROJECT', JblanceHelper::formatCurrency($chargePerProject), $expiredate, true);
                                    ?>
                                    <a href="<?php echo $link_edit; ?>" class="btn btn-primary btn-block btn-sm"><?php echo JText::_('COM_JBLANCE_EDIT'); ?></a>
                                    <a href="javascript:void(0);" class="btn btn-danger btn-block btn-sm" onclick="javascript:modalConfirm('<?php echo JText::_('COM_JBLANCE_DELETE', true); ?>', '<?php echo JText::_('COM_JBLANCE_CONFIRM_DELETE_PROJECT', true); ?>', '<?php echo $link_del; ?>');" ><?php echo JText::_('COM_JBLANCE_DELETE'); ?></a>
                                    <?php if ($row->status == 'COM_JBLANCE_EXPIRED') { ?>
                                        <a href="javascript:void(0);" class="btn btn-warning btn-block btn-sm" onclick="javascript:modalConfirm('<?php echo JText::_('COM_JBLANCE_REPOST', true); ?>', '<?php echo $repostConfirmMessage; ?>', '<?php echo $link_repost_proj; ?>');" ><?php echo JText::_('COM_JBLANCE_REPOST'); ?></a>
                                    <?php } ?>
                                    <?php if (($row->status == 'COM_JBLANCE_OPEN' || $row->status == 'COM_JBLANCE_EXPIRED') && $bidsCount > 0) { ?> 
                                        <a href="<?php echo $link_pick_user; ?>" class="btn btn-info btn-block btn-sm"><?php echo JText::_('COM_JBLANCE_PICK_USER'); ?></a>
                                    <?php }
                                    ?>
                                    <?php
                                } elseif ($row->status == 'COM_JBLANCE_CLOSED') {
                                    $hasRated = $model->hasRated($row->id, $user->id);

                                    if ($bidInfo->p_status == 'COM_JBLANCE_COMPLETED' && !$hasRated) {
                                        $link_rate = JRoute::_('index.php?option=com_jblance&view=project&layout=rateuser&id=' . $row->id);
                                        ?>
                                        <a href="<?php echo $link_rate; ?>" class="btn btn-success btn-block btn-sm"><i class="material-icons">star</i><?php echo JText::_('COM_JBLANCE_RATE_FREELANCER'); ?></a>
                                        <?php
                                    }
                                } elseif ($row->status == 'COM_JBLANCE_FROZEN') {
                                    //bid status check
                                    $detail_chosen = JFactory::getUser($row->assigned_userid);

                                    if ($bidInfo->status == 'COM_JBLANCE_DENIED') {
                                        ?>
                                        <div class="alert alert-error">
                                            <strong><?php echo JText::_('COM_JBLANCE_STATUS_DENIED_BY') . ' - ' . $detail_chosen->$nameOrUsername; ?></strong><br>
                                            <a href="<?php echo $link_pick_user; ?>" class="btn btn-info btn-block btn-sm"><?php echo JText::_('COM_JBLANCE_PICK_USER'); ?></a>
                                            <a href="<?php echo $link_reopen_proj; ?>" class="btn btn-warning btn-block btn-sm"><?php echo JText::_('COM_JBLANCE_REOPEN'); ?></a>
                                        </div>

                                        <?php
                                    } elseif ($bidInfo->status == '') {
                                        ?>
                                        <div class="alert">
                                            <strong><?php echo JText::_('COM_JBLANCE_STATUS_WAITING'); ?></strong><br>
                                            <a href="<?php echo $link_reopen_proj; ?>" class="btn btn-warning btn-sm"><?php echo JText::_('COM_JBLANCE_REOPEN'); ?></a>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
             
                                <?php if ($noInvitees) : ?>
                                    <a href="<?php echo $link_invite_user; ?>" class="btn btn-inverse btn-block btn-sm"><?php echo JText::_('COM_JBLANCE_INVITE_USERS'); ?></a>

                                <?php endif; ?>
                            </div>
                            <div class="pay-status">
                                <?php
                                if ($row->status == 'COM_JBLANCE_CLOSED') {
                                    $link_progress = JRoute::_('index.php?option=com_jblance&view=project&layout=projectprogress&id=' . $bidInfo->bidid); // id is the bid id and NOT project id
                                    ?>
                                    <a href="<?php echo $link_progress; ?>" class="btn btn-primary btn-block btn-sm"><?php echo JText::_('COM_JBLANCE_VIEW_PROGRESS'); ?></a>
                                    <div class="progress" title="<?php echo JText::_('COM_JBLANCE_PROGRESS') . ' : ' . $bidInfo->p_percent . '%'; ?>">
                                        <div class="progress-bar progress-bar-primary" style="width: <?php echo $bidInfo->p_percent; ?>%"><?php echo $bidInfo->p_percent; ?>%</div>
                                    </div>
                                <?php } ?>
                                <div class="payment-status">
                                    <?php if ($enableEscrowPayment) { ?>
                                        <?php
                                        if ($row->status == 'COM_JBLANCE_CLOSED' && $row->project_type == 'COM_JBLANCE_FIXED') {
                                            $perc = ($row->paid_amt / $bidInfo->bidamount) * 100;
                                            $perc = round($perc, 2) . '%';
                                            ?>
                                            <div class="progress" title="<?php echo JText::_('COM_JBLANCE_PAYMENT_STATUS') . ' : ' . $perc; ?>">
                                                <div class="progress-bar progress-bar-warning" style="width: <?php echo $perc; ?>">
                                                <?php echo JText::_('COM_JBLANCE_PAYMENT_STATUS') . ' : ' . $perc; ?>
                                                </div>
                                            </div>
                                            <?php
                                            if ($perc < 100) {
                                                ?>
                                                <a class="btn btn-primary btn-block btn-sm" href="<?php echo $link_transfer; ?>"><?php echo JText::_('COM_JBLANCE_PAY_NOW'); ?></a>
                                                <?php
                                            }
                                        } elseif ($row->status == 'COM_JBLANCE_CLOSED' && $row->project_type == 'COM_JBLANCE_HOURLY') {
                                            ?>
                                            <?php if ($row->paid_status != 'COM_JBLANCE_PYMT_COMPLETE') { ?>
                                                <a class="btn btn-primary btn-block btn-sm" href="<?php echo $link_transfer; ?>"><?php echo JText::_('COM_JBLANCE_PAY_NOW'); ?></a>
                                            <?php } ?>
                                            <?php
                                            //show mark payment as complete button for partially paid status
                                            if ($row->paid_status == 'COM_JBLANCE_PYMT_PARTIAL') {
                                                ?>
                                                <a href="javascript:void(0);" class="btn btn-primary btn-block btn-sm" onclick="javascript:modalConfirm('<?php echo JText::_('COM_JBLANCE_PAYMENT_COMPLETE'); ?>', '<?php echo JText::_('COM_JBLANCE_CONFIRM_PAYMENT_COMPLETE'); ?>', '<?php echo $link_pay_comp; ?>');" ><?php echo JText::_('COM_JBLANCE_PAYMENT_COMPLETE'); ?></a>
                                                <?php
                                            }
                                            if ($row->paid_status == 'COM_JBLANCE_PYMT_COMPLETE') {
                                                ?>
                                                <div class="progress" title="<?php echo JText::_('COM_JBLANCE_PAYMENT_STATUS') . ' : ' . '100%'; ?>">
                                                    <div class="progress-bar progress-bar-success " style="width: <?php echo '100%'; ?>">
                                                       <?php echo JText::_('COM_JBLANCE_PAYMENT_STATUS') . ' : ' . '100%'; ?>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        <?php }
                                        ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php endif; ?>
                    <div class="row"></div>
                    <div class="pull-right">
                        <div class="display-limit">
                            <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                            <?php echo $this->pageNav->getLimitBox(); ?>
                        </div>
                    </div>
            <div class="pagination pagination-centered">
                        <?php echo $this->pageNav->getPagesLinks(); ?>
        </div>
            <input type="hidden" name="option" value="com_jblance" />			
            <input type="hidden" name="task" value="" />	
        </form>

    </div>
</div>
<script type="text/javascript">
$(function () {
$('.badge').tooltip('toggle');
})
</script>