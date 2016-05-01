<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	28 March 2012
 * @file name	:	views/project/tmpl/searchproject.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Search projects (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', '#id_categ', null, array('placeholder_text_multiple' => JText::_('COM_JBLANCE_FILTER_PROJECT_BY_SKILLS')));
JHtml::_('formbehavior.chosen', '#id_location');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/btngroup.js");
$doc->addScript("components/com_jblance/js/bootstrap-slider.js");
$doc->addStyleSheet("components/com_jblance/css/slider.css");

$app = JFactory::getApplication();
$user = JFactory::getUser();
$model = $this->getModel();
$now = JFactory::getDate();
$projHelper = JblanceHelper::get('helper.project');  // create an instance of the class ProjectHelper
$select = JblanceHelper::get('helper.select');  // create an instance of the class SelectHelper
$userHelper = JblanceHelper::get('helper.user');  // create an instance of the class UserHelper

$keyword = $app->input->get('keyword', '', 'string');
$phrase = $app->input->get('phrase', 'any', 'string');
$id_categ = $app->input->get('id_categ', array(), 'array');
$id_location = $app->input->get('id_location', array(), 'array');
$proj_type = $app->input->get('project_type', array('fixed' => 'COM_JBLANCE_FIXED', 'hourly' => 'COM_JBLANCE_HOURLY'), 'array');
$budget = $app->input->get('budget', '', 'string');
$status = $app->input->get('status', 'COM_JBLANCE_OPEN', 'string');

JArrayHelper::toInteger($id_categ);
JArrayHelper::toInteger($id_location);

$config = JblanceHelper::getConfig();
$currencysym = $config->currencySymbol;
$currencycode = $config->currencyCode;
$dformat = $config->dateFormat;
$sealProjectBids = $config->sealProjectBids;
$showUsername = $config->showUsername;

$nameOrUsername = ($showUsername) ? 'username' : 'name';

$action = JRoute::_('index.php?option=com_jblance&view=project&layout=searchproject');
?>
<script type="text/javascript">
<!--
    jQuery(document).ready(function ($) {
        $("#budget").sliderz({});

    });
//-->
</script>

<form class="form-vertical" action="<?php echo $action; ?>" method="get" name="userFormJob" enctype="multipart/form-data">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="col-md-12">
                <div id="filter-bar">
                    <div class="input-group input-group-lg">
                        <input type="text" name="keyword" id="keyword" value="<?php echo $keyword; ?>" class="form-control hasTooltip"
                               placeholder="<?php echo JText::_('COM_JBLANCE_SEARCH_KEYWORD_TIPS'); ?>" 
                               />
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary hasTooltip" title="<?php echo JHtml::tooltipText('COM_JBLANCE_SEARCH'); ?>"><i class="material-icons">search</i></button>
                            <a href="<?php echo $action; ?>" class="btn btn-default hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"><i class="material-icons">remove_circle_outline</i></a>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <label class="control-label" for="status">
                    <?php echo JText::_('COM_JBLANCE_PROJECT_STATUS'); ?>
                </label>

                <?php
                $attribs = "class='form-control'";
                $list_status = $select->getSelectProjectStatus('status', $status, 'COM_JBLANCE_ANY', $attribs, '');
                echo $list_status;
                ?>

            </div>


            <div class="col-md-2">
                <label class="control-label" for="project_type"><?php echo JText::_('COM_JBLANCE_PROJECT_TYPE'); ?></label>
                <div class="checkbox">
                    <label class="control-label" for="">
                        <input type="checkbox" id="fixed" name="project_type[fixed]" value="COM_JBLANCE_FIXED" <?php
                        if (isset($proj_type['fixed'])) {
                            echo 'checked';
                        }
                        ?> ><?php echo JText::_('COM_JBLANCE_FIXED'); ?>
                    </label> 
                    <label>
                        <input type="checkbox" id="hourly" name="project_type[hourly]" value="COM_JBLANCE_HOURLY" <?php
                        if (isset($proj_type['hourly'])) {
                            echo 'checked';
                        }
                        ?> ><?php echo JText::_('COM_JBLANCE_HOURLY'); ?>
                    </label> 

                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <?php
                    $list_phrase = $select->getRadioSearchPhrase('phrase', $phrase);
                    echo $list_phrase;
                    ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-2">
                    <label><?php echo JText::_('COM_JBLANCE_LOCATION'); ?></label>
                    <?php
                    $attribs2 = "class='form-control'";
                    echo $select->getSelectLocationTree('id_location', $id_location, '', 'COM_JBLANCE_ALL_LOCATIONS', $attribs2, '');
                    ?>
                </div>
            </div>
            <div class="col-md-12">

                <?php
                $attribs = "class='form-control required' MULTIPLE";
                echo $select->getSelectCategoryTree('id_categ[]', $id_categ, '', $attribs, '', true);
                ?>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="label" for="project_type"><?php echo JText::_('COM_JBLANCE_BUDGET'); ?></label>
                    <div class="controls">
                        <label class="radio">
                            <?php
                            $limit = $model->getMaxMinBudgetLimit('COM_JBLANCE_FIXED');
                            $sliderValue = (empty($budget)) ? $limit->minlimit . ',' . $limit->maxlimit : $budget;
                            ?>
                            <b style="margin-right: 15px;"><?php echo JblanceHelper::formatCurrency($limit->minlimit, true, false, 0); ?></b>
                            <input type="text" name="budget" id="budget" class="form-control" value="<?php echo $budget; ?>" data-slider-min="<?php echo $limit->minlimit; ?>" data-slider-max="<?php echo $limit->maxlimit; ?>" data-slider-step="50" data-slider-value="[<?php echo $sliderValue; ?>]" style="display: none; margin-top: 20px;" />
                            <b style="margin-left: 15px;"><?php echo JblanceHelper::formatCurrency($limit->maxlimit, true, false, 0); ?></b>		
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Body content-->
    <div class="panel panel-default">
        <div class="panel-heading"><h3><?php echo JText::_('COM_JBLANCE_SEARCH_RESULTS'); ?></h3></div>
        <div class="panel-body">
            <?php
            for ($i = 0, $x = count($this->rows); $i < $x; $i++) {
                $row = $this->rows[$i];
                $buyer = $userHelper->getUser($row->publisher_userid);
                $daydiff = $row->daydiff;

                if ($daydiff == -1) {
                    $startdate = JText::_('COM_JBLANCE_YESTERDAY');
                } elseif ($daydiff == 0) {
                    $startdate = JText::_('COM_JBLANCE_TODAY');
                } else {
                    $startdate = JHtml::_('date', $row->start_date, $dformat, true);
                }

                // calculate expire date and check if expired
                $expiredate = JFactory::getDate($row->start_date);
                $expiredate->modify("+$row->expires days");
                $isExpired = ($now > $expiredate) ? true : false;

                /* if($isExpired)
                  $statusLabel = 'label';
                  else */
                if ($row->status == 'COM_JBLANCE_OPEN') {
                    $statusLabel = 'label label-success';
                } elseif ($row->status == 'COM_JBLANCE_FROZEN') {
                    $statusLabel = 'label label-warning';
                } elseif ($row->status == 'COM_JBLANCE_CLOSED') {
                    $statusLabel = 'label label-info';
                } elseif ($row->status == 'COM_JBLANCE_EXPIRED') {
                    $statusLabel = 'label label-danger';
                }

                $bidsCount = $model->countBids($row->id);

                //calculate average bid
                $avg = $projHelper->averageBidAmt($row->id);
                $avg = round($avg, 0);

                // 'private invite' project shall be visible only to invitees and project owner
                $isMine = ($row->publisher_userid == $user->id);
                if ($row->is_private_invite) {
                    $invite_ids = explode(',', $row->invite_user_id);
                    if (!in_array($user->id, $invite_ids) && !$isMine) {
                        continue;
                    }
                }
                ?>
            <div id="projectBox" class="clearfix">
                             <ul class="promotions">
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
                    <div class="col-md-2 col-xs-4 col-sm-4">
                        <?php
                        $attrib = 'class="img-responsive"';
                        $avatar = JblanceHelper::getLogo($row->publisher_userid, $attrib);
                        echo!empty($avatar) ? LinkHelper::GetProfileLink($row->publisher_userid, $avatar) : '&nbsp;'
                        ?>
                    </div>
                    <div class="col-md-4 col-xs-6 col-sm-4">
                        <h4 class="media-heading">
                            <?php echo LinkHelper::getProjectLink($row->id, $row->project_title); ?>
                        </h4>
                        <div class="font14">
                            <strong><?php echo JText::_('COM_JBLANCE_POSTED_BY'); ?></strong>: <?php echo LinkHelper::GetProfileLink($row->publisher_userid, $buyer->$nameOrUsername); ?>
                        </div>
                        <div class="font14">
                            <strong><?php echo JText::_('COM_JBLANCE_SKILLS_REQUIRED'); ?></strong>: <?php echo JblanceHelper::getCategoryNames($row->id_category, 'tags-link', 'project'); ?>
                        </div>
                        <div class="font14">
                            <strong><?php echo JText::_('COM_JBLANCE_LOCATION'); ?></strong>: <span class=""><?php echo JblanceHelper::getLocationNames($row->id_location); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-12">

                        <i class="material-icons">label</i> <?php echo JText::_('COM_JBLANCE_BIDS'); ?> : 
                        <?php if ($sealProjectBids || $row->is_sealed) : ?>
                            <span class="label label-info"><?php echo JText::_('COM_JBLANCE_SEALED'); ?></span>
                        <?php else : ?>
                            <span class="badge badge-info"><?php echo $bidsCount; ?></span>
                        <?php endif; ?>
                    </div>

                    <div id="status" class="col-md-3">
                        <span class="<?php echo $statusLabel; ?>"><?php echo JText::_($row->status); ?></span>

                        <div class="status">
                            <div class="bid_project_left text-center">
                                <?php if ($sealProjectBids || $row->is_sealed) : ?>
                                    <span class="label label-info"><?php echo JText::_('COM_JBLANCE_SEALED'); ?></span>
                                <?php else : ?>
                                    <span class="font16 boldfont"><?php echo JblanceHelper::formatCurrency($avg, true, false, 0); ?></span><?php echo ($row->project_type == 'COM_JBLANCE_HOURLY') ? ' / ' . JText::_('COM_JBLANCE_HR') : ''; ?>
                                <?php endif; ?>
                            </div>
                            <div class="avg"><?php echo JText::_('COM_JBLANCE_AVG_BID'); ?></div>
                        </div>
                    </div>
                </div>
            <div class="lineseparator"></div>
                <?php
            }
            ?>
            <?php if (!count($this->rows)) { ?>
                <div class="alert alert-info">
                    <?php echo JText::_('COM_JBLANCE_NO_MATCHING_RESULTS_FOUND'); ?>
                </div>
            <?php } ?>
            <div class="pagination pagination-centered clearfix">
                <div class="display-limit pull-right">
                    <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                    <?php echo $this->pageNav->getLimitBox(); ?>
                </div>
                <?php echo $this->pageNav->getPagesLinks(); ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="option" value="com_jblance" />
    <input type="hidden" name="view" value="project" />
    <input type="hidden" name="layout" value="searchproject" />
    <input type="hidden" name="task" value="" />
</form>