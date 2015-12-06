<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	30 May 2012
 * @file name	:	views/message/tmpl/inbox.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Inbox of Private Messages (jblance)
 */
defined('_JEXEC') or die('Restricted access');
JHtml::_('jquery.framework');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/utility.js");

$user = JFactory::getUser();

$config = JblanceHelper::getConfig();
$dformat = $config->dateFormat;
$showUsername = $config->showUsername;

$nameOrUsername = ($showUsername) ? 'username' : 'name';
$link_compose = JRoute::_('index.php?option=com_jblance&view=message&layout=compose');

JblanceHelper::setJoomBriToken();
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">
  <div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PRIVATE_MESSAGES'); ?>
    <div class="pull-right"><a href="<?php echo $link_compose; ?>" class="btn btn-primary"><span><?php echo JText::_('COM_JBLANCE_COMPOSE'); ?></span></a></div>
  </div>
  <?php
  //echo JHtml::_('tabs.start', 'panel-tabs', array('useCookie'=>'0'));
  $newTitle = ($this->newMsg > 0) ? ' (<b>' . JText::sprintf('COM_JBLANCE_COUNT_NEW', $this->newMsg) . '</b>)' : '';
  //echo JHtml::_('tabs.panel', JText::_('COM_JBLANCE_RECEIVED').$newTitle, 'received'); 
  ?>
  <table class="table table-hover table-condensed">
    <!-- <thead>
      <tr>
        <th><?php echo JText::_('COM_JBLANCE_FROM'); ?></th>
        <th><?php echo JText::_('COM_JBLANCE_SUBJECT'); ?></th>
        <th><?php echo JText::_('COM_JBLANCE_DATE'); ?></th>
        <th><?php echo JText::_('COM_JBLANCE_ACTION'); ?></th>
      </tr>			
    </thead> -->
    <tbody>
      <?php
      if (count($this->msgs) == 0) {  //Called if there are no messages -> Shows a text that spreads over the whole table
        ?>
        <tr><td colspan='5' align="center"><?php echo JText::_("COM_JBLANCE_NO_MESSAGES"); ?></td></tr>
        <?php
      }
      $k = 0;
      for ($i = 0, $x = count($this->msgs); $i < $x; $i++) {
        $msg = $this->msgs[$i];
        $userFrom = JFactory::getUser($msg->idFrom);
        $userTo = JFactory::getUser($msg->idTo);

        //if the current user is different, then show that name
        if ($user->id == $msg->idFrom) {
    $userInfo = JFactory::getUser($msg->idTo);
  } else {
    $userInfo = JFactory::getUser($msg->idFrom);
  }

  $link_read = JRoute::_('index.php?option=com_jblance&view=message&layout=read&id=' . $msg->id);

        $newMsg = JblanceHelper::countUnreadMsg($msg->id);
        ?>
        <tr id="jbl_feed_item_<?php echo $msg->id; ?>">
          <td><a href="<?php echo $link_read; ?>"><?php echo $userInfo->$nameOrUsername; ?></a></td>
          <td><a href="<?php echo $link_read; ?>"><?php echo ($msg->approved == 1) ? $msg->subject : '<small>' . JText::_('COM_JBLANCE_PRIVATE_MESSAGE_WAITING_FOR_MODERATION') . '</small>'; ?> <?php echo ($newMsg > 0) ? '<span class="label label-info">' . JText::sprintf('COM_JBLANCE_COUNT_NEW', $newMsg) . '</span>' : ''; ?></a></td>
          <td nowrap="nowrap"><?php echo JHtml::_('date', $msg->date_sent, $dformat, true); ?></td>
          <td>
            <span id="feed_hide_<?php echo $msg->id; ?>" class="help-inline">
              <a class="btn btn-mini" onclick="processMessage('<?php echo $msg->id; ?>', 'message.processmessage');" title="<?php echo JText::_('COM_JBLANCE_REMOVE'); ?>" href="javascript:void(0);"><i class="material-icons">delete</i></a>
            </span>
          </td>
        </tr>
        <?php
        $k = 1 - $k;
      }
      ?>
    </tbody>
  </table>

<?php //echo JHtml::_('tabs.end');  ?>
</form>