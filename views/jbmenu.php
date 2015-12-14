<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	22 March 2012
 * @file name	:	views/jbmenu.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Displays the JoomBri menu items (jblance)
 */
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/selectnav.min.js");
$app = JFactory::getApplication();
$tmpl = $app->input->get('tmpl', '', 'string');
$preview = $app->input->get('preview', 0, 'int');
$layout = $app->input->get('layout', '', 'string');

$config = JblanceHelper::getConfig();
$limit = $config->feedLimitDashboard;

$model = $this->getModel();
$user = JFactory::getUser();
$userid = $app->input->get('id', 0, 'int');
$hasJBProfile = JblanceHelper::hasJBProfile($user->id);

$link_edit_profile = JRoute::_('index.php?option=com_jblance&view=user&layout=editprofile');
$link_messages = JRoute::_('index.php?option=com_jblance&view=message&layout=inbox');
$link_home = JRoute::_('index.php');
$link_logout = JRoute::_('index.php?option=com_users&task=user.logout&' . JSession::getFormToken() . '=1&return=' . base64_encode($link_home));
?>

<!--Hide the menu for guest layout not print-->
<?php if ($hasJBProfile && $tmpl == '') : ?>
  <?php
  $jbmenu = JblanceHelper::get('helper.menu');
  $activeLink = $jbmenu->getActiveLink();
  $active = $jbmenu->getActiveId($activeLink);

  $menus = $jbmenu->getJBMenuItems();
  $processedMenus = $jbmenu->processJBMenuItems($menus);

  $notifys = JblanceHelper::getFeeds($limit, 'notify'); //get the notificataion feeds
  $newMsgs = JblanceHelper::countUnreadMsg();

  if ($processedMenus) {
    ?>

    <script type="text/javascript">
      <!--
      function showElement(layer) {
        var myLayer = document.getElementById(layer);
        if (myLayer.style.display === "none") {
          myLayer.style.display = "block";
          myLayer.backgroundPosition = "top";
        }
        else {
          myLayer.style.display = "none";
        }

        //set the status to read
        var myRequest = jQuery.ajax({
          url: "index.php?option=com_jblance&task=user.setfeedread&<?php echo JSession::getFormToken() . "=1"; ?>",
          method: "POST",
          success: function (response) {
            if (response == "OK") {
              //nothing
            }
            else {
              alert(":(");
            }
          }
        });
      }

      /* window.addEvent('domready', function(){
       selectnav('jbnav');
       }); */
      //-->
    </script>

    <nav class="navbar navbar-primary">
      <!--<a href="<?php echo JRoute::_('index.php?option=com_jblance&view=guest&layout=showfront'); ?>" class="<?php echo $active == 0 ? 'active' : 'active'; ?>"><?php echo JText::_('Home'); ?></a>
      -->
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo $link_edit_profile; ?>">
                   <?php
          $att = "class='img-thumbnail img-responsive'";
          $avatar = JblanceHelper::getLogo($userid, $att);
          echo $avatar;
          ?>
          </a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav">
            <?php
            foreach ($processedMenus as $menu) {
//              $class	= empty( $menu->childs ) ? 'class="dropdown"' : '';
              ?>
            <li<?php echo $active == $menu->item->id ? ' class="active"' : ''; ?> class="dropdown ">
                <?php
                if ($menu->item->type == 'separator') {
                  $atrib = 'class "dropdwon" ';
                  $href = 'javascript:void(0);';
                } else {
                  $href = JRoute::_($menu->item->link);
                }
                ?>
                <a href="<?php echo $href; ?>"<?php echo $active === $menu->item->id ? ' class="active"' : ''; ?>><?php echo JText::_($menu->item->title); ?></a>
                <?php
                if (!empty($menu->childs)) {
                  ?>
                  <ul class="dropdown-menu">
                    <?php
                    foreach ($menu->childs as $child) {
                      ?>
                      <li>
                        <a class="" href="<?php echo JRoute::_($child->link); ?>"><?php echo JText::_($child->title); ?></a>
                      </li>
                      <?php
                    }
                    ?>
                  </ul>
                  <?php
                }
                ?>
              </li>
              <?php
            }
            ?>
          </ul>

          <ul class="nav navbar-nav navbar-right">
            <li id="jbMenuNotify">
              <a href="javascript:void(0);" onclick="javascript:showElement('notify-menu')">
                <i class="material-icons">notifications_none</i>
              </a>
              <?php
              $countUnreadFeeds = countUnreadFeeds();
              if ($countUnreadFeeds) :
                ?>
                <span class="notify-count"><?php echo $countUnreadFeeds; ?></span>
              <?php endif; ?>
              <div id="notify-menu" class="notify-menu" style="display: none;">
                <a href="javascript:void(0);" style="float: right; padding: 10px;" onclick="javascript:showElement('notify-menu')">
                  <img alt="" src="components/com_jblance/images/remove.gif" title="<?php echo JText::_('COM_JBLANCE_CLOSE'); ?>" alt="img">
                </a>
                <div class="jbl_h3title">
                  <?php echo JText::_('COM_JBLANCE_NOTIFICATIONS'); ?>
                </div>
                <div style="max-height: 400px;overflow:auto;">
                  <?php
                  if (count($notifys)) {
                    for ($i = 0, $n = count($notifys); $i < $n; $i++) {
                      $notify = $notifys[$i];
                      ?>
                      <div class="media border-b">
                        <?php echo $notify->logo; ?>
                        <div class="media-body">
                          <?php echo $notify->title; ?>
                          <div>
                            <i class="icon-calendar"></i> <?php echo $notify->daysago; ?>
                          </div>
                        </div>
                      </div>
                      <?php
                    }
                  } else {
                    ?>
                    <div class="font16" style="padding: 5px; border-bottom: none;">
                      <?php
                      echo JText::_('COM_JBLANCE_NO_NEW_NOTIFICATION');
                      ?>
                    </div>
                  <?php } ?>
                </div>
              </div>
            </li>
            <li id="jbMenuInbox">
              <a href="<?php echo $link_messages; ?>" title="New Messages">
                <!--<img src="components/com_jblance/images/notify_mail.png" alt="img" title="">-->
                <i class="material-icons">message</i>
              </a>
              <?php if ($newMsgs) : ?>
                <span class="notify-count"><?php echo $newMsgs; ?></span>
              <?php endif; ?>
            </li>
            <li class="jbMenuIconLogout">
              <a href="<?php echo $link_logout; ?>" title="<?php echo JText::_('JLOGOUT'); ?>">
                <i class="material-icons">exit_to_app</i>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="clearfix"></div>
    <?php
  }
endif;
?>

<?php

function countUnreadFeeds() {
  $db = JFactory::getDbo();
  $user = JFactory::getUser();

  $query = "SELECT COUNT(is_read) isRead FROM #__jblance_feed WHERE target=" . $db->quote($user->id) . " AND is_read=0";
  $db->setQuery($query);
  $total = $db->loadResult();
  return $total;
}
