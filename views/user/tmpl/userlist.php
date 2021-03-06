<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	11 June 2012
 * @file name	:	views/user/tmpl/userlist.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	User list page (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen', '.advancedSelect');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/utility.js");
$doc->addScript("components/com_jblance/js/barrating.js");
$doc->addScript("components/com_jblance/js/bootstrap-slider.js");
$doc->addStyleSheet("components/com_jblance/css/barrating.css");
$doc->addStyleSheet("components/com_jblance/css/slider.css");

$app = JFactory::getApplication();
$jbuser = JblanceHelper::get('helper.user');  // create an instance of the class UserHelper
$select = JblanceHelper::get('helper.select');  // create an instance of the class SelectHelper
$user = JFactory::getUser();
$model = $this->getModel();

$letter = $app->input->get('letter', '', 'string');
$actionLetter = (!empty($letter)) ? '&letter=' . $letter : '';

$config = JblanceHelper::getConfig();
$showUsername = $config->showUsername;
$showBizName = $config->showBizName;
$nameOrUsername = ($showUsername) ? 'user' : 'username';

$keyword = $app->input->get('keyword', '', 'string');
$id_category = $app->input->get('id_category', array(), 'array');
$id_location = $app->input->get('id_location', array(), 'array');
$hourly_rate = $app->input->get('hourly_rate', '', 'string');
$rating = $app->input->get('rating', 0, 'int');
$ordering = $app->input->get('ordering', 'u.name asc', 'string');

JArrayHelper::toInteger($id_category);
JArrayHelper::toInteger($id_location);

// Load the parameters.
$params = $app->getParams();
$show_search = $params->get('show_search', false);

$action = JRoute::_('index.php?option=com_jblance&view=user&layout=userlist' . $actionLetter);
$actionAll = JRoute::_('index.php?option=com_jblance&view=user&layout=userlist');

JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
    <!--
    jQuery(document).ready(function($) {
    $("#rating").barrating("show", {
    showSelectedRating:false
    });

    $("#hourly_rate").sliderz({});
    });
    //-->
</script>
<form action="<?php echo $action; ?>" method="post" name="userFormJob" enctype="multipart/form-data">
    <!-- show search fields if enabled -->
    <?php if ($show_search) : ?>
        <div class="panel panel-default">
        <div class="col-md-12">
            <div class="form-group">
                
                <div class="input-group input-group-lg">
                    <input type="text" name="keyword" id="keyword" class="form-control" value="<?php echo $keyword; ?>" placeholder="<?php echo JText::_('COM_JBLANCE_KEYWORDS'); ?>"/>
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary hasTooltip" title="<?php echo JHtml::tooltipText('COM_JBLANCE_SEARCH'); ?>"><i class="material-icons">search</i></button>
                        <button class="btn btn-default hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"><i class="material-icons">remove_circle</i></button>

                    </span>
                </div>
                <span input-group-btn>
                    <div class="ordering-select pull-right">
                        <span class="boldfont"><?php echo JText::_('COM_JBLANCE_SORT_BY'); ?>:</span>
                        <?php
                        $attribs = "class='form-control advancedSelect' size='1'";
                        echo $model->getSelectUserlistOrdering('ordering', $ordering, $attribs);
                        ?>		
                    </div>
                </span>
            </div>
        </div>



        <div class="row">
            <div class="col-md-4">
                <?php
                $attribs = 'class="input-xlarge advancedSelect" size="1"';
                $categtree = $select->getSelectCategoryTree('id_category[]', $id_category, 'COM_JBLANCE_ALL_SKILLS', $attribs, '', true);
                echo $categtree;
                ?>
            </div>
            <div class="col-md-4">
                <?php
                $attribs = "class='form-control dvancedSelect' size='1'";
                echo $select->getSelectLocationTree('id_location[]', $id_location, '', 'COM_JBLANCE_ALL_LOCATIONS', $attribs, '');
                ?>
            </div>
        </div>


            <div class="row">
            <div class="col-md-6">
                <div class="control-group">
                    <label class="control-label" for="rating"><?php echo JText::_('COM_JBLANCE_RATING'); ?></label>
                    <div class="controls brating" title="<?php echo JText::_('COM_JBLANCE_RATING_ABOVE'); ?>">
                        <?php echo $select->getSelectRating('rating', $rating); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="control-group">
                    <label class="control-label" for="project_type"><?php echo JText::_('COM_JBLANCE_HOURLY_RATE'); ?></label>
                    <div class="controls">
                        <label class="radio">
                            <?php
                            $limit = $model->getMaxMinHourlyLimit();
                            $sliderValue = (empty($hourly_rate)) ? $limit->minlimit . ',' . $limit->maxlimit : $hourly_rate;
                            ?>
                            <b style="margin-right: 15px;"><?php echo JblanceHelper::formatCurrency($limit->minlimit, true, false, 0); ?></b>
                            <input type="text" name="hourly_rate" id="hourly_rate" class="input-xlarge" value="<?php echo $hourly_rate; ?>" data-slider-min="<?php echo $limit->minlimit; ?>" data-slider-max="<?php echo $limit->maxlimit; ?>" data-slider-step="10" data-slider-value="[<?php echo $sliderValue; ?>]" style="display: none; margin-top: 20px;" />
                            <b style="margin-left: 15px;"><?php echo JblanceHelper::formatCurrency($limit->maxlimit, true, false, 0); ?></b>		
                        </label>
                    </div>
                </div>
            </div>
        </div>
        </div>
    <?php endif; ?>
    <div class="panel">
    <div class="panel-body">
        <div class="panel-heading"><h3><?php echo $this->escape($this->params->get('page_heading', JText::_('COM_JBLANCE_USERLIST'))); ?></h3>
    <!-- hide alpha index if search form is enabled -->
    <?php if (!$show_search) : ?>
  
            <?php
            echo JHtml::_('link', $actionAll, '#', array('title' => JText::_('COM_JBLANCE_ALL'), 'class' => 'btn btn-mini'));
            foreach (range('A', 'Z') as $i) :
                $link_comp_index = JRoute::_('index.php?option=com_jblance&view=user&layout=userlist&letter=' . strtolower($i), false);
                if (strcasecmp($letter, $i) == 0)
                    echo JHtml::_('link', $link_comp_index, $i, array('title' => $i, 'class' => 'btn btn-sm btn-primary active'));
                else
                    echo JHtml::_('link', $link_comp_index, $i, array('title' => $i, 'class' => 'btn btn-default btn-sm'));
            endforeach;
            ?>	
   
    <?php endif; ?>
        
        </div>

    <?php
    for ($i = 0, $x = count($this->rows); $i < $x; $i++) {
        $row = $this->rows[$i];
        $status = $jbuser->isOnline($row->user_id);  //get user online status
        $viewerInfo = $jbuser->getUserGroupInfo($user->id, null);  // this holds the info of profile viewer
        $isFavourite = JblanceHelper::checkFavorite($row->user_id, 'profile');
        $isMine = ($user->id == $row->user_id);
        $link_sendpm = JRoute::_('index.php?option=com_jblance&view=message&layout=compose&username=' . $row->username);
        ?>
        <div id="userlist" class="media">
            <?php
            $attrib = 'class="media-object" style="min-width:100%; height:80px;"';
            $avatar = JblanceHelper::getLogo($row->user_id, $attrib);
            echo!empty($avatar) ? LinkHelper::GetProfileLink($row->user_id, $avatar, '', '', 'media-left') : '&nbsp;'
            ?>
            <div class="media-body">
                <h4 class="media-heading">
                    <?php $stats = ($status) ? 'online' : 'offline'; ?>
                    <span class="online-status <?php echo $stats; ?>" title="<?php echo JText::_('COM_JBLANCE_' . strtoupper($stats)); ?>"></span>
                    <?php echo LinkHelper::GetProfileLink($row->user_id, $row->$nameOrUsername); ?> <?php echo JblanceHelper::getLocationNames($row->id_location, 'only-flag'); ?>
                    <!-- show Add to Favorite button to others and registered users and who can post project -->
                    <?php if (!$user->guest && !$isMine && $viewerInfo->allowPostProjects) { ?>
                        <span  class="pull-right">
                            <span id="fav-msg-<?php echo $row->user_id; ?>">
                                <?php if ($isFavourite > 0) : ?>
                                    <a onclick="favourite('<?php echo $row->user_id; ?>', -1, 'profile');" href="javascript:void(0);" class="btn btn-mini btn-danger"><i class="material-icons">remove_circle</i> <?php echo JText::_('COM_JBLANCE_REMOVE_FAVOURITE') ?></a>
                                <?php else : ?>
                                    <a onclick="favourite('<?php echo $row->user_id; ?>', 1, 'profile');" href="javascript:void(0);" class="btn btn-mini btn-success"><i class="material-icons">add_circle_outline</i> <?php echo JText::_('COM_JBLANCE_ADD_FAVOURITE') ?></a>
                                <?php endif; ?>
                            </span>				
                            <a class="btn btn-mini btn-primary" href="<?php echo $link_sendpm; ?>"><i class="material-icons">comment</i> <?php echo JText::_('COM_JBLANCE_SEND_MESSAGE'); ?></a>
                        <?php } ?>
                    </span>
                </h4>
                <div>
                    <?php $rate = JblanceHelper::getAvarageRate($row->user_id, true); ?>
                    <?php if ($row->rate > 0) { ?>
                        <span class="font14"><?php echo JblanceHelper::formatCurrency($row->rate, true, true, 0) . '/' . JText::_('COM_JBLANCE_HR'); ?></span>
                    <?php } ?>
                </div>
                <?php if (!empty($row->id_category)) { ?>
                    <div class="boldfont font12">
                        <?php echo JText::_('COM_JBLANCE_SKILLS'); ?>: <?php echo JblanceHelper::getCategoryNames($row->id_category, 'tags-link', 'user'); ?>
                    </div>
                <?php } ?>
                <?php if (!empty($row->biz_name) && $showBizName) : ?>
                    <span class="boldfont font12"><?php echo JText::_('COM_JBLANCE_BUSINESS_NAME'); ?>: </span><?php echo $row->biz_name; ?>
                <?php endif; ?>
          <!-- <strong><?php echo JText::_('COM_JBLANCE_USERGROUP'); ?> : </strong><?php echo $row->grpname; ?> | 
                <?php if ($status) : ?>
                      <span class="label label-success"><?php echo JText::_('COM_JBLANCE_ONLINE'); ?></span>
                <?php else : ?>
                      <span class="label"><?php echo JText::_('COM_JBLANCE_OFFLINE'); ?></span>
                <?php endif; ?> -->
            </div>
        </div>
        <div class="lineseparator"></div>
    <?php } ?>
    <?php if (!count($this->rows)) { ?>
        <div class="alert alert-info">
            <?php echo JText::_('COM_JBLANCE_NO_USER_OR_MATCHING_YOUR_QUERY'); ?>
        </div>
    <?php } ?>
                    <div class="pull-right">
                        <div class="display-limit">
                            <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                            <?php echo $this->pageNav->getLimitBox(); ?>
                        </div>
                    </div>
            <div class="pagination pagination-centered">
            <?php echo $this->pageNav->getPagesLinks(); ?>
        </div>
</div>
</div>
</form> 