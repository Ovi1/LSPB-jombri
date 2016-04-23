<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	22 March 2012
 * @file name	:	views/user/tmpl/editpicture.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Edit profile picture (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/utility.js");
$doc->addScript("components/com_jblance/js/cropit.js");

$user = JFactory::getUser();
$model = $this->getModel();
$config = JblanceHelper::getConfig();
$jbuser = JblanceHelper::get('helper.user');  // create an instance of the class UserHelper
$userInfo = $jbuser->getUser($this->row->user_id);

$upload_type = (empty($userInfo->picture)) ? 'NO_UPLOAD_CROP' : 'CROP_ONLY';

JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
  jQuery(document).ready(function ($) {
    JoomBri.uploadCropPicture('user.uploadpicture', '<?php echo JblanceHelper::getLogoUrl($this->row->user_id, ""); ?>', '<?php echo JblanceHelper::getLogoUrl($this->row->user_id, "original"); ?>');
  });
//-->
</script>
<?php include_once(JPATH_COMPONENT . '/views/profilemenu.php'); ?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="profilePicture" id="profilePicture" class="form-validate" enctype="multipart/form-data">
  <div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PROFILE_PICTURE'); ?>
    <div class="btn-group pull-right upload-icons">
      <button type="button" class="btn btn-primary select-image-btn">
        <i class="material-icons">attach_file</i>
        <?php echo JText::_('COM_JBLANCE_UPLOAD_NEW'); ?>
      </button>
      <button type="button" class="btn btn-success crop-save" style="/*display: none;*/">
        <i class="material-icons">save</i>
        <?php echo JText::_('COM_JBLANCE_CROP_AND_SAVE'); ?>
      </button>
      <button type="button" class="btn btn-danger remove-picture" data-user-id="<?php echo $this->row->user_id; ?>" data-remove-task="user.removepicture"><i class="material-icons">delete</i> 
        <?php echo JText::_('COM_JBLANCE_REMOVE_PICTURE'); ?>
      </button>
    </div>
  </div>
  <div id="upload-message" class="center-block col-md-12"></div>
  <div class="row">
    <div class="col-md-6">
      <div class="cropit-image-view">
        <input type="file" name="profile_file" class="cropit-image-input hidden" />
        <div class="cropit-image-preview-container">
          <div class="cropit-image-preview"></div>
        </div>

        <div class="slider-wrapper">
          <input type="range" class="cropit-image-zoom-input" min="0" max="1" step="0.01">
        </div>
      </div>
    </div>
    <div class="col-md-6">
        <h4><?php echo JText::_('COM_JBLANCE_THUMBNAIL'); ?>:</h4>
        <div class="current-profile-picture">
          <div class="cropit-image-preview" style="cursor: auto;"></div>
        </div>
 
  </div>
  </div>
  <input type="hidden" name="option" value="com_jblance" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="user_id" id="user_id" value="<?php echo $this->row->user_id; ?>" />
  <input type="hidden" name="upload_type" id="upload_type" value="<?php echo $upload_type; ?>" />
  <?php echo JHtml::_('form.token'); ?>

</form>	
