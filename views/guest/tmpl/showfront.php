<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	16 March 2012
 * @file name	:	views/guest/tmpl/showfront.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	User Groups (jblance)
 */
defined('_JEXEC') or die('Restricted access');

//JHtml::_('jquery.framework');
//JHtml::_('behavior.formvalidator');

$doc = JFactory::getDocument();
//$doc->addStyleSheet("components/com_jblance/css/pricing.css");

$app = JFactory::getApplication();
$user = JFactory::getUser();
$model = $this->getModel();

$config = JblanceHelper::getConfig();
$link_dashboard = JRoute::_('index.php?option=com_jblance&view=user&layout=dashboard');

jbimport('fbconnect');
$fb = new FbconnectHelper();
$user_info = $fb->initFbLogin();

//check if app key/secret is empty. If empty, do not show the FB connect button
$showFbConnect = true;
$app_id = $config->fbApikey;
$app_sec = $config->fbAppsecret;
if (empty($app_id) || empty($app_sec)) {
  $showFbConnect = false;
}
?>




<div class="panel panel-default">

<div class="panel-body">
    <?php
$usersConfig = JComponentHelper::getParams('com_users');
if ($usersConfig->get('allowUserRegistration') == '0') {
  ?>
  <div class="alert alert-error">
    <h4><?php echo JText::_('COM_JBLANCE_REGISTRATION_DISABLED'); ?></h4>
  <?php echo JText::_('COM_JBLANCE_REGISTRATION_DISABLED_MESSAGE'); ?>
  </div>
  <?php
}
?>
  <div class="col-md-6">
    <!-- if user is guest -->
<?php if ($user->guest) : ?>
      <div class="jb-loginform">
        <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="login" id="form-login">
          <h3><?php echo JText::_('COM_JBLANCE_MEMBERS_LOGIN'); ?></h3>
          <div class="form-group">
          <div class="input-group">
            <label class="sr-only" for="username"><?php echo JText::_('COM_JBLANCE_USERNAME'); ?>:</label>
            <span class="input-group-addon"><i class="material-icons">account_circle</i></span>
            <input type="text" class="form-control" name="username" id="username" />
            <span class="input-group-btn">
              <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>" class="btn" title="<?php echo JText::_('COM_JBLANCE_FORGOT_YOUR_USERNAME') . '?'; ?>" tabindex="-1">
                <i class="material-icons">help</i>
              </a>
            </span>
          </div>
          </div>
          <div class="form-group">
          <div class="input-group">
            <label class="sr-only" for="password"><?php echo JText::_('COM_JBLANCE_PASSWORD'); ?>:</label>
            <span class="input-group-addon"><i class="material-icons">lock</i></span>
            <input type="password" class="form-control" name="password" id="password" />
            <span class="input-group-btn">
              <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" class="btn " title="<?php echo JText::_('COM_JBLANCE_FORGOT_YOUR_PASSWORD') . '?'; ?>" tabindex="-1">
                <i class="material-icons">help</i>
              </a>
            </span>
          </div>
          </div>
          <div class="checkbox">
            <label class="checkbox">
              <input type="checkbox" alt="Remember me" value="yes" id="remember" name="remember" /><?php echo JText::_('COM_JBLANCE_REMEMBER_ME'); ?>
            </label>
            <input type="submit" value="<?php echo JText::_('COM_JBLANCE_LOGIN'); ?>" name="submit" id="submit" class="btn btn-small" />
            <?php if ($user_info['loginUrl'] != '' && $showFbConnect) { ?>
              <a class="btn btn-fb btn-small" href="<?php echo $user_info['loginUrl']; ?>">
                  <img class="fb" src="components/com_jblance/images/fb.png"><?php echo JText::_('COM_JBLANCE_SIGN_IN_WITH_FACEBOOK'); ?></a> 
              <?php }
            ?>
          </div>

      <input type="hidden" name="option" value="com_users" />
      <input type="hidden" name="task" value="user.login" />
      <input type="hidden" name="return" value="<?php echo base64_encode($link_dashboard); ?>"/>
      <?php echo JHtml::_('form.token'); ?>
      </form>
      </div>
  <?php else : ?>
    <div class="jb-loginform">
      <h4><?php echo JText::sprintf('COM_JBLANCE_WELCOME_USER', $user->name); ?></h4>
    </div>
  <?php endif; ?>
</div>
<div class="clearfix"></div>
<?php
$totGroups = count($this->userGroups);
if ($totGroups == 0) {
  echo '<p class="jbbox-warning">' . JText::_('COM_JBLANCE_NO_USERGROUP_ENABLED') . '</p>';
}
for ($i = 0; $i < $totGroups; $i++) {
  $userGroup = $this->userGroups[$i];
  if ($i % 2 == 0) {
    ?>
      <?php }
    ?>
    <div class="col-md-6 well well-small white userrole">
      <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userGroup" id="userGroup">
        <div class="userrole-name text-center">
          <?php if ($userGroup->approval == 1) : ?>
            <div class="pull-right"><span class="label label-danger"><?php echo JText::_('COM_JBLANCE_REQUIRE_APPROVAL'); ?></span></div>
          <?php endif; ?>
          <h2><?php echo $userGroup->name; ?></h2>
        </div>
        <?php echo stripslashes($userGroup->description); ?>
        <hr>
        <div class="text-center">
          <button class="btn btn-primary btn-large" type="submit" id="btn_ug_id<?php echo $userGroup->id; ?>" onclick="javascript:selectRole('<?php echo $userGroup->id; ?>');"><?php echo JText::_('COM_JBLANCE_CHOOSE_AND_CONTINUE'); ?>
          </button>
        </div>
        <input type="hidden" name="check" value="post" />
        <input type="hidden" name="ugid" value="<?php echo $userGroup->id; ?>" />
        <input type="hidden" name="option" value="com_jblance" />
        <input type="hidden" name="task" value="guest.grabusergroupinfo" />
        <?php echo JHtml::_('form.token'); ?>
      </form>
    </div>
    <?php if ($i % 2 == 1 || $i == ($totGroups - 1)) { ?>
  <?php } ?>
  <?php }

?>

</div>
<p class="jbbox-info">
  <?php echo JText::_('COM_JBLANCE_REQUIRE_APPROVAL_NOTE'); ?>
</p>
</div>