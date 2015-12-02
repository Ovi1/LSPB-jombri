<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	21 March 2012
 * @file name	:	views/user/tmpl/dashboard.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Displays the user Dashboard (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/utility.js");

$model = $this->getModel();
$user = JFactory::getUser();
$config = JblanceHelper::getConfig();
$showFeedsDashboard = $config->showFeedsDashboard;
$enableEscrowPayment = $config->enableEscrowPayment;
$enableWithdrawFund = $config->enableWithdrawFund;

JText::script('COM_JBLANCE_CLOSE');

$link_edit_profile = JRoute::_('index.php?option=com_jblance&view=user&layout=editprofile');
$link_portfolio = JRoute::_('index.php?option=com_jblance&view=user&layout=editportfolio');
$link_messages = JRoute::_('index.php?option=com_jblance&view=message&layout=inbox');
$link_post_project = JRoute::_('index.php?option=com_jblance&view=project&layout=editproject');
$link_list_project = JRoute::_('index.php?option=com_jblance&view=project&layout=listproject');
$link_search_proj = JRoute::_('index.php?option=com_jblance&view=project&layout=searchproject');
$link_my_project = JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject');
$link_my_bid = JRoute::_('index.php?option=com_jblance&view=project&layout=showmybid');
$link_my_services = JRoute::_('index.php?option=com_jblance&view=service&layout=myservice');
$link_service_bght = JRoute::_('index.php?option=com_jblance&view=service&layout=servicebought');
$link_deposit = JRoute::_('index.php?option=com_jblance&view=membership&layout=depositfund');
$link_withdraw = JRoute::_('index.php?option=com_jblance&view=membership&layout=withdrawfund');
$link_escrow = JRoute::_('index.php?option=com_jblance&view=membership&layout=escrow');
$link_transaction = JRoute::_('index.php?option=com_jblance&view=membership&layout=transaction');
$link_managepay = JRoute::_('index.php?option=com_jblance&view=membership&layout=managepay');
$link_subscr_hist = JRoute::_('index.php?option=com_jblance&view=membership&layout=planhistory');
$link_buy_subscr = JRoute::_('index.php?option=com_jblance&view=membership&layout=planadd');

JblanceHelper::setJoomBriToken();

if (!JBLANCE_FREE_MODE) {
  if (!$user->guest) {
    $planStatus = JblanceHelper::planStatus($user->id);

    if ($planStatus == '1') {
      ?>
      <div class="jbbox-warning">
      <?php echo JText::sprintf('COM_JBLANCE_USER_SUBSCRIPTION_EXPIRED', $link_buy_subscr); ?>
      </div>
    <?php } elseif ($planStatus == '2') {
      ?>
      <div class="jbbox-info">
      <?php echo JText::sprintf('COM_JBLANCE_USER_DONT_HAVE_ACTIVE_PLAN', $link_subscr_hist); ?>
      </div>
    <?php
    }
  }
}
?>
<div class="jbl_h3title"><?php echo JText::_($this->userInfo->name) . ' ' . JText::_('COM_JBLANCE_DASHBOARD'); ?></div>

<div class="row-fluid">
  <div class="col-md-3">
    <ul class="list-group">
      <li class="list-group-item nav-header">
<?php echo JText::_('COM_JBLANCE_PROFILE'); ?>
      </li>
      <li class="list-group-item">
        <a href="<?php echo $link_edit_profile; ?>">
          <i class="material-icons">mode_edit</i> <?php echo JText::_('COM_JBLANCE_EDIT_PROFILE'); ?> </a>
      </li>
      <li class="list-group-item">
        <?php
        $avatars = JblanceHelper::getAvatarIntegration();
        $link_edit_picture = $avatars->getEditURL();
        ?>
        <a href="<?php echo $link_edit_picture; ?>"><i class="material-icons">insert_photo</i> <?php echo JText::_('COM_JBLANCE_EDIT_PICTURE'); ?> </a>
      </li>
      <?php if ($this->dbElements['allowAddPortfolio']) : ?>
      <li class="list-group-item">
          <a href="<?php echo $link_portfolio; ?>"><i class="material-icons">work</i> <?php echo JText::_('COM_JBLANCE_PORTFOLIO'); ?> </a>
        </li>
<?php endif; ?>
        <li class="list-group-item">
        <a href="<?php echo $link_messages; ?>"><i class="material-icons">message</i> <?php echo JText::_('COM_JBLANCE_PRIVATE_MESSAGES'); ?> </a>
      </li>
      <li class="list-group-item nav-header">
<?php echo JText::_('COM_JBLANCE_PROJECTS'); ?>
      </li>
<?php if ($this->dbElements['allowPostProjects']) : ?>
      <li class="list-group-item">
          <a href="<?php echo $link_post_project; ?>"><i class="material-icons">add</i> <?php echo JText::_('COM_JBLANCE_POST_NEW_PROJECT'); ?> </a>
        </li>
        <li class="list-group-item">
          <a href="<?php echo $link_my_project; ?>"><i class="material-icons">list</i> <?php echo JText::_('COM_JBLANCE_MY_PROJECTS'); ?> </a>
        </li>
        <li class="list-group-item">
          <a href="<?php echo $link_service_bght; ?>"><i class="material-icons">room_service</i> <?php echo JText::_('COM_JBLANCE_SERVICES_BOUGHT'); ?> </a>
        </li>
<?php endif; ?>
<?php if ($this->dbElements['allowBidProjects']) : ?>
        <li class="list-group-item">
          <a href="<?php echo $link_list_project; ?>"><i class="material-icons">list</i></i> <?php echo JText::_('COM_JBLANCE_LATEST_PROJECTS'); ?> </a>
        </li>
        <li class="list-group-item">
          <a href="<?php echo $link_search_proj; ?>"><i class="material-icons">search</i> <?php echo JText::_('COM_JBLANCE_SEARCH_PROJECTS'); ?> </a>
        </li>
        <li class="list-group-item">
          <a href="<?php echo $link_my_bid; ?>"><i class="material-icons">label</i> <?php echo JText::_('COM_JBLANCE_MY_BIDS'); ?> </a>
        </li>
        <li class="list-group-item">
          <a href="<?php echo $link_my_services; ?>"><i class="material-icons">room_service</i> <?php echo JText::_('COM_JBLANCE_MY_SERVICES'); ?> </a>
        </li>
      <?php endif; ?>
<?php
if (!JBLANCE_FREE_MODE) :
  ?>
        <li class="list-group-item nav-header">
  <?php echo JText::_('COM_JBLANCE_BILLING_AND_FINANCE'); ?>
        </li>
        <li class="list-group-item">
          <a href="<?php echo $link_deposit; ?>"><i class="material-icons">add_circle</i> <?php echo JText::_('COM_JBLANCE_DEPOSIT_FUNDS'); ?> </a>
        </li>
        <!-- check if withdraw fund is enabled -->
        <?php if ($enableWithdrawFund) : ?>
        <li class="list-group-item">
            <a href="<?php echo $link_withdraw; ?>"><i class="material-icons">remove_circle</i> <?php echo JText::_('COM_JBLANCE_WITHDRAW_FUNDS'); ?> </a>
          </li>
  <?php endif; ?>
        <!-- check if escrow payment is enabled -->
        <?php if ($enableEscrowPayment) : ?>
        <li class="list-group-item">
            <a href="<?php echo $link_escrow; ?>"><i class="material-icons">refresh</i> <?php echo JText::_('COM_JBLANCE_ESCROW_PAYMENT'); ?> </a>
          </li>
  <?php endif; ?>
          <li class="list-group-item">
          <a href="<?php echo $link_transaction; ?>"><i class="material-icons">history</i> <?php echo JText::_('COM_JBLANCE_TRANSACTION_HISTORY'); ?> </a>
        </li>
        <li class="list-group-item">
          <a href="<?php echo $link_managepay; ?>"><i class="material-icons">edit</i> <?php echo JText::_('COM_JBLANCE_MANAGE_PAYMENTS'); ?> </a>
        </li>
        <li class="list-group-item">
          <a href="<?php echo $link_subscr_hist; ?>"><i class="material-icons">favorite</i> <?php echo JText::_('COM_JBLANCE_MY_SUBSCRS'); ?> </a>
        </li>
<?php endif; ?>
    </ul>
  </div>

  <div class="col-md-9 list-group-item">
    <!-- pending tasks section -->
    <h3><?php echo JText::_('COM_JBLANCE_TASKS_PENDING'); ?></h3>
    <?php
    if (!empty($this->pendings)) {
      foreach ($this->pendings as $pending) {
        ?>
        <ul class="unstyled">
          <i class="material-icons">warning</i> <?php echo $pending; ?></li>
        </ul>
        <?php
      }
    } else {
      ?>
      <div class="alert alert-info"><?php echo JText::_('COM_JBLANCE_NO_TASK_PENDING_YOUR_ACTION'); ?></div>
  <?php
}
?>
    <div class="lineseparator"></div>

    <!-- news feed section -->
    <?php if ($showFeedsDashboard) : ?>
      <h3><?php echo JText::_('COM_JBLANCE_NEWS_FEED'); ?></h3>
        <?php
        $n = count($this->feeds);
        if ($n == 0) {
          ?>
        <div class="alert alert-info">
        <?php echo JText::_('COM_JBLANCE_NO_NEWSFEEDS_OR_POSTS'); ?>
        </div>
    <?php
  }
  for ($i = 0, $n = count($this->feeds); $i < $n; $i++) {
    $feed = $this->feeds[$i];
    ?>
        <div class="media jb-borderbtm-dot" id="jbl_feed_item_<?php echo $feed->id; ?>">


//TODO
    <?php echo $feed->logo; ?>


          <div class="media-body">
    <?php echo $feed->title; ?>
            <p>
              <i class="material-icons">access_time</i> <?php echo $feed->daysago; ?> 
              <span id="feed_hide_<?php echo $feed->id; ?>" class="help-inline">
    <?php if ($feed->isMine) : ?>
                  <a class="btn btn-mini btn-link" onclick="processFeed('<?php echo $user->id; ?>', '<?php echo $feed->id; ?>', 'remove');" href="javascript:void(0);">
                    <i class="material-icons">remove_circle_outline</i> <?php echo JText::_('COM_JBLANCE_REMOVE'); ?>
                  </a>
    <?php endif; ?>
                <a class="btn btn-mini btn-link" onclick="processFeed('<?php echo $user->id; ?>', '<?php echo $feed->id; ?>', 'hide');" href="javascript:void(0);">
                  <i class="material-icons">visibility_off</i> <?php echo JText::_('COM_JBLANCE_HIDE'); ?>
                </a>
              </span>
            </p>
          </div>
        </div>
    <?php
  }
  ?>
<?php endif; ?>
  </div>
</div>
