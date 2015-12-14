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
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">
  <div class="clearfix"></div>
  <div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_MY_SERVICES'); ?>
    <div class="pull-right">
      <a href="<?php echo $link_edit_service; ?>" class="btn btn-primary"><span><?php echo JText::_('COM_JBLANCE_ADD_SERVICE'); ?></span></a>
    </div>  
  </div>

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
      <div class="col-md-4">
        <div class="thumbnail">
          <img class="img-thumbnail" src="<?php echo $attachments[0]['thumb']; ?>" width="80px" />
          <div class="caption">
            <h4>
              <?php echo $row->service_title; ?>;
              <?php
              if ($row->approved == 0)
                echo '<span class="label label-danger">' . JText::_('COM_JBLANCE_PENDING_APPROVAL') . '</span>';
              ?>
              <br>
              <?php if ($row->buycount > 0) { ?>
                <a href="<?php echo $link_sold; ?>">
                  <span class="label label-success"><?php echo JText::plural('COM_JBLANCE_N_SERVICES_BOUGHT', $row->buycount); ?></span>
                </a>
                <div class="small"><?php echo $getStatusCounts; ?></div>
              <?php } ?>
            </h4>
            <h4><?php echo JblanceHelper::formatCurrency($row->price, true, false, 0); ?></h4> 
            <?php echo JText::_('COM_JBLANCE_IN'); ?> <small class="font16"><?php echo JText::plural('COM_JBLANCE_N_DAYS', $row->duration); ?></small><br>
            <div class="btn-group" role="group">
              <a href="<?php echo $link_view; ?>" class="btn btn-default "><?php echo JText::_('COM_JBLANCE_VIEW_SERVICE'); ?></a>
              <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu">
                <li><a href="<?php echo $link_edit; ?>"><?php echo JText::_('COM_JBLANCE_EDIT_SERVICE'); ?></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    <?php }
    ?>
    <div class="pagination pagination-centered clearfix">
      <div class="display-limit pull-right">
        <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
        <?php echo $this->pageNav->getLimitBox(); ?>
      </div>
      <?php echo $this->pageNav->getPagesLinks(); ?>
    </div>
  <?php endif; ?>
</form>