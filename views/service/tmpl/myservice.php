<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	04 November 2014
 * @file name	:	views/service/tmpl/myservice.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	List of services provided by users (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.framework');

$model = $this->getModel();

$link_edit_service = JRoute::_('index.php?option=com_jblance&view=service&layout=editservice');
?>
<div class="panel panel-default">
    <div  class="panel-heading">
        <h3><?php echo JText::_('COM_JBLANCE_MY_SERVICES'); ?>
            <a href="<?php echo $link_edit_service; ?>" class="btn btn-primary pull-right"><?php echo JText::_('COM_JBLANCE_ADD_SERVICE'); ?></a>
        
        </h3>
    </div>
    <div class="panel-body">
        <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">
            <?php if (empty($this->rows)) : ?>
                <div class="alert alert-info">
                    <?php echo JText::_('COM_JBLANCE_NO_SERVICES_POSTED_YET'); ?>
                </div>
            <?php else : ?>
                <?php
                for ($i = 0; $i < count($this->rows); $i++) {
                    $row = $this->rows[$i];
                    $link_edit = JRoute::_('index.php?option=com_jblance&view=service&layout=editservice&id=' . $row->id);
                    $link_view = JRoute::_('index.php?option=com_jblance&view=service&layout=viewservice&id=' . $row->id);
                    $link_sold = JRoute::_('index.php?option=com_jblance&view=service&layout=servicesold&id=' . $row->id);
                    $attachments = JBMediaHelper::processAttachment($row->attachment, 'service');
                    $getStatusCounts = $model->getServiceProgressCounts($row->id);
                    ?>
                    <div class="col-md-3">
                        <div class="thumbnail">
                            <img class="img-responsive" src="<?php echo $attachments[0]['thumb']; ?>"  />
                            <div class="caption">
                                <h5>
                                    <?php echo $row->service_title; ?>;
                                    <?php
                                    if ($row->approved == 0)
                                        echo '<span class="label label-danger">' . JText::_('COM_JBLANCE_PENDING_APPROVAL') . '</span>';
                                    ?>
                                    <?php if ($row->buycount > 0) { ?>
                                        <a href="<?php echo $link_sold; ?>">
                                            <span class="label label-success"><?php echo JText::plural('COM_JBLANCE_N_SERVICES_BOUGHT', $row->buycount); ?></span>
                                        </a>
                                        <div class="small"><?php echo $getStatusCounts; ?></div>
                                    <?php } ?>
                                </h5>
                                <p><?php echo JText::_('COM_JBLANCE_IN'); ?> <?php echo JText::plural('COM_JBLANCE_N_DAYS', $row->duration); ?></p>
                                <p><i class="material-icons">attach_money</i><?php echo JblanceHelper::formatCurrency($row->price, true, false, 0); ?></p> 
                                <!-- Split button -->
                                <div class="btn-group">
                                    <a class="btn btn-primary btn-block"  href="<?php echo $link_view; ?>"><?php echo JText::_('COM_JBLANCE_VIEW_SERVICE'); ?></a></button>
                                    <a class="btn btn-default btn-block" href="<?php echo $link_edit; ?>"><?php echo JText::_('COM_JBLANCE_EDIT_SERVICE'); ?></a></li>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }
                ?>
            <?php endif; ?>
        </form>

    </div>
                    <div class="row-fluid">
                    <div class="pagination pagination-centered pull-right">
                        <div class="display-limit">
                            <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                            <?php echo $this->pageNav->getLimitBox(); ?>
                        </div>
                        <?php echo $this->pageNav->getPagesLinks(); ?>
                    </div>
                    </div>
   
           
</div>
