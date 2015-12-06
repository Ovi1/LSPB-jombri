<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	30 May 2012
 * @file name	:	views/message/tmpl/read.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Read a message thread (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.tooltip');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/utility.js");
$doc->addScript("components/com_jblance/js/upclick-min.js");

$user = JFactory::getUser();
$config = JblanceHelper::getConfig();
$dformat = $config->dateFormat;
$showUsername = $config->showUsername;
$reviewMessages = $config->reviewMessages;

$nameOrUsername = ($showUsername) ? 'username' : 'name';

//process the from and to id based on the current user and the parent message.
//if parent fromId is equal to current userid, no need to swap them
if ($user->id == $this->parent->idFrom) {
  $idFrom = $this->parent->idFrom;
  $idTo = $this->parent->idTo;
} else {
  $idFrom = $this->parent->idTo;
  $idTo = $this->parent->idFrom;
}

$link_inbox = JRoute::_('index.php?option=com_jblance&view=message&layout=inbox');

JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
  function validateForm(f) {
    var valid = document.formvalidator.isValid(f);

    if (valid == true) {

    }
    else {
      alert('<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>');
      return false;
    }
    return true;
  }

  jQuery(document).ready(function ($) {
    attachFile('uploadmessage', 'message.attachfile');

    //Scrolls the comment list to the bottom
    $("#messageList").scrollTop($("#messageList").prop("scrollHeight"));
  });
//-->
</script>
<div class="jbl_h3title">
  <?php
  if ($this->parent->approved == 1)
    echo $this->parent->subject;
  else
    echo '<i>' . JText::_('COM_JBLANCE_PRIVATE_MESSAGE_WAITING_FOR_MODERATION') . '</i>';
  ?>
  <div class="pull-right"><a href="<?php echo $link_inbox; ?>" class="btn btn-primary"><span><?php echo JText::_('COM_JBLANCE_INBOX'); ?></span></a></div>
</div> 
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormMessage" id="userFormMessage" class="form-validate" onsubmit="return validateForm(this);" enctype="multipart/form-data">
  <div class="panel panel-default">
    <div class="panel-body">
      <div id="messageList">
        <?php
        for ($i = 0, $x = count($this->rows); $i < $x; $i++) {
          $row = $this->rows[$i];
          $userDtl = JFactory::getUser($row->idFrom);
          ?>
          <div class="media border-b"  id="jbl_feed_item_<?php echo $row->id; ?>">
            <div class="col-md-1 col-sm-2 col-xs-3">
            <?php
            $attrib = 'width=auto height=56 class=" img-circle"';
            $avatar = JblanceHelper::getLogo($row->idFrom, $attrib);

            echo!empty($avatar) ? LinkHelper::GetProfileLink($row->idFrom, $avatar, '', '', ' pull-left') : '&nbsp;'
            ?>
          </div>
            <div class="col-md-11 col-sm-10 col-xs-9  msg-body">
              <span class="text-msg">
                <p>
                <?php
                if ($row->approved == 1) {
                  echo $row->message;
                } else {
                  echo '<small>' . JText::_('COM_JBLANCE_PRIVATE_MESSAGE_WAITING_FOR_MODERATION') . '</small>';
                }
                ?>
                </p>
              </span>
              <div class="clearfix"></div>
                <small class="info text-muted  pull-right">
                  <div class="pull-left author"><i class="material-icons ">account_circle</i> <?php echo LinkHelper::GetProfileLink($row->idFrom, $userDtl->$nameOrUsername); ?> </div>
                  
                  <i class="material-icons">access_time</i> <?php echo JHtml::_('date', $row->date_sent, $dformat, true); ?>  
                  <span id="feed_hide_<?php echo $row->id; ?>" class="help-inline">
                    <a onclick="processMessage('<?php echo $row->id; ?>', 'message.processmessage');" href="javascript:void(0);">
                      <i class="material-icons">delete</i> <?php echo JText::_('COM_JBLANCE_REMOVE'); ?>
                    </a>
                  </span>
                  <!-- Show attachment if found -->
                  <?php if (!empty($row->attachment) && $row->approved == 1) : ?>
                    | <span>
                      <i class="icon-download"></i>
                      <?php echo LinkHelper::getDownloadLink('message', $row->id, 'message.download'); ?>
                    </span>
                    <?php
                  endif;
                  ?>
                </small>
            </div>
          </div>
          <?php
        }
        ?>
      </div>

      <div class="form-group">
        <textarea name="message" id="message" rows="4" class="form-control required" placeholder="<?php echo JText::_('COM_JBLANCE_ENTER_MESSAGE'); ?>">
        </textarea>
      </div>
      <div class="form-group">
        <div id="ajax-container-uploadmessage"></div>
        <div id="file-attached-uploadmessage"></div>
        <div class="pull-right">
          <?php
          $tipmsg = JHtml::tooltipText(JText::_('COM_JBLANCE_ATTACH_FILE'), JText::_('COM_JBLANCE_ALLOWED_FILE_TYPES') . ' : ' . $config->projectFileText . '<br>' . JText::_('COM_JBLANCE_MAXIMUM_FILE_SIZE') . ' : ' . $config->projectMaxsize . ' kB');
          ?>
          <i class="material-icons hasTooltip" title="<?php echo $tipmsg; ?>">info</i>
          <button class="btn btn-link"  type="button" id="uploadmessage" value="<?php echo JText::_('COM_JBLANCE_ATTACH_FILE'); ?>">
            <i class="material-icons">attach_file</i>
          </button>
          <button class="btn btn-link" id="btnsend" type="submit" value="<?php echo JText::_('COM_JBLANCE_SEND'); ?>"> <i class="material-icons">send</i></button>
                
        </div>
      </div>
      </div>
    </div>
    <?php if ($reviewMessages) : ?>
      <p class="jbbox-warning"><?php echo JText::_('COM_JBLANCE_MESSAGE_WILL_BE_MODERATED_BEFORE_SENT_TO_RECIPIENT'); ?></p>
    <?php endif; ?>
  </div>

  <input type="hidden" name="option" value="com_jblance" />			
  <input type="hidden" name="task" value="message.sendmessage" />	
  <input type="hidden" name="idFrom" value="<?php echo $idFrom; ?>" />
  <input type="hidden" name="idTo" value="<?php echo $idTo; ?>" />
  <input type="hidden" name="id" value="0" />
  <input type="hidden" name="subject" value="<?php echo $this->parent->subject; ?>" />
  <input type="hidden" name="project_id" value="<?php echo $this->parent->project_id; ?>" />
  <input type="hidden" name="parent" value="<?php echo $this->parent->id; ?>" />
  <input type="hidden" name="type" value="<?php echo $this->parent->type; ?>" />
  <input type="hidden" name="return" value="<?php echo base64_encode(JFactory::getURI()->toString()); ?>" />
  <?php echo JHtml::_('form.token'); ?>
</form>