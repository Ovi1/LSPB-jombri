<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	30 May 2012
 * @file name	:	views/message/tmpl/compose.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Inbox of Private Messages (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.tooltip');

JHtml::_('script', 'media/jui/js/jquery.autocomplete.min.js', false, false, false, false, true);

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/upclick-min.js");
$doc->addScript("components/com_jblance/js/utility.js");

$app = JFactory::getApplication();
$config = JblanceHelper::getConfig();
$reviewMessages = $config->reviewMessages;

$recUsername = $app->input->get('username', '', 'string');
$subject = $app->input->get('subject', '', 'string');

JblanceHelper::setJoomBriToken();
?>

<script type="text/javascript">
<!--
  function validateForm(f) {
    var valid = document.formvalidator.isValid(f);

    if (valid === true) {
      //
    }
    else {
      alert ('<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>');
      return false;
    }
    return true;
  }

  jQuery(document).ready(function ($) {
    attachFile('uploadmessage', 'message.attachfile');

    $("#recipient").on("change", function () {
      checkUsername($("#recipient-x"));
    });

    $("#recipient").autocomplete({
      serviceUrl: "index.php?option=com_jblance&task=message.getautocompleteusername&<?php echo JSession::getFormToken() . '=1'; ?>",
      type: "POST",
      paramName: "recipient",
      minChars: 1,
      onSearchStart: function (query) {
        $("#recipient-x").val("");
      },
      transformResult: function (response) {
        var resp = jQuery.parseJSON(response);//console.log(resp);
        return {
          suggestions: $.map(resp, function (value, key) {
            return {value: value, data: key};
          })
        };
      },
      onSelect: function (suggestion) {
        $("#recipient-x").val(suggestion.data);
        $("#recipient").triggerHandler("change");
      }
    });
  });
//-->
</script>

<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_COMPOSE'); ?></div>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormMessage" id="userFormMessage" class="form-validate form-vertical" onsubmit="return validateForm(this);" enctype="multipart/form-data">

  <div id="status_recipient" class="dis-inl-blk"></div>
  
  
  <div class="form-group">
  <div class="input-group">
    <input placeholder="<?php echo JText::_('COM_JBLANCE_TO'); ?>:" type="text" name="recipient" id="recipient" value="<?php echo $recUsername; ?>" class="form-control required" />
    <input type="hidden" name="recipient" id="recipient-x">
    <?php
    $tipmsg2 = JHtml::tooltipText(JText::_('COM_JBLANCE_TO'), JText::_('COM_JBLANCE_RECIPIENT_WARNING'));
    ?>

    <span class="input-group-addon">
      <i class="hasTooltip material-icons" title="<?php echo $tipmsg2; ?>">help</i>
    </span>
  </div>
  </div>
    <div class="form-group">
    <input class="form-control required" type="text" name="subject" id="subject" value="<?php echo $subject; ?>" placeholder="<?php echo JText::_('COM_JBLANCE_SUBJECT'); ?>:"/>
    </div>
    <div class="form-group">
  <textarea class="form-control" style="width: 100%" name="message" id="message" rows="5"
             placeholder="<?php echo JText::_('COM_JBLANCE_MESSAGE'); ?>:">
  </textarea>
    </div>
    <div class="form-group">
    <div id="ajax-container-uploadmessage"></div>
    <div id="file-attached-uploadmessage"></div>
    
        <?php
    $tipmsg = JHtml::tooltipText(JText::_('COM_JBLANCE_ATTACH_FILE'), JText::_('COM_JBLANCE_ALLOWED_FILE_TYPES') . ' : ' . $config->projectFileText . '<br>' . JText::_('COM_JBLANCE_MAXIMUM_FILE_SIZE') . ' : ' . $config->projectMaxsize . ' kB');
    ?>
    <div class="input-group">
    <input class="form-control" type="upload" id="uploadmessage" value="<?php echo JText::_('COM_JBLANCE_ATTACH_FILE'); ?>">
    <div class="input-group-addon">
    <i class=" hasTooltip material-icons" title="<?php echo $tipmsg; ?>">help</i>
    </div>
  <?php if ($reviewMessages) : ?>
    <p class="jbbox-warning"><?php echo JText::_('COM_JBLANCE_MESSAGE_WILL_BE_MODERATED_BEFORE_SENT_TO_RECIPIENT'); ?></p>
  <?php endif; ?>
  </div>  
    
    </div>
    <div class="clearfix"></div>
  <div class="">
        <button type="button" onclick="javascript:history.back();" class="btn btn-default"><i class="material-icons">cancel</i><?php echo JText::_('COM_JBLANCE_BACK'); ?></button>
    <button type="submit" name="submit" id="submit" class="btn btn-primary pull-right"><?php echo JText::_('COM_JBLANCE_SEND'); ?>  <i class="material-icons">send</i>
    </button>
  </div>

  <input type="hidden" name="option" value="com_jblance" />			
  <input type="hidden" name="task" value="message.sendcompose" />	
  <input type="hidden" name="id" value="0" />
  <input type="hidden" name="type" value="COM_JBLANCE_OTHER" />
  <?php echo JHtml::_('form.token'); ?>
  
</form>