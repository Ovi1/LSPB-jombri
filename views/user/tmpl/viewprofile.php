<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	22 March 2012
 * @file name	:	views/user/tmpl/viewprofile.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	View user profile (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework'); 
 JHtml::_('behavior.modal', 'a.jb-modal');
 JHtml::_('behavior.tabstate');
 
 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");
 
 $app  	 = JFactory::getApplication();
 $model  = $this->getModel();
 $user 	 = JFactory::getUser();
 $userid = $app->input->get('id', 0, 'int');
 if(empty($userid)){		// get the current userid if not passed
	$userid = $user->id;
 }
 
 $isMine = ($user->id == $userid);
 $hasJBProfileForViewer  = JblanceHelper::hasJBProfile($user->id);	//check if the profile viewer has JB profile
 $jbuser = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
 $userInfo = $jbuser->getUserGroupInfo($userid, null); 		// this holds the info of profile owner
 if($hasJBProfileForViewer)
 	$viewerInfo = $jbuser->getUserGroupInfo($user->id, null); 	// this holds the info of profile viewer
 
 $config		  = JblanceHelper::getConfig();
 $enableReporting = $config->enableReporting;
 $enableAddThis   = $config->enableAddThis;
 $addThisPubid 	  = $config->addThisPubid;
 $showUsername 	  = $config->showUsername;
 $showBizName 	  = $config->showBizName;
 
 $nameOrUsername = ($showUsername) ? 'username' : 'name';
 
 $uri 	= JFactory::getURI();
 
 $link_sendpm = JRoute::_('index.php?option=com_jblance&view=message&layout=compose&username='.$this->userInfo->username);
 $link_report = JRoute::_('index.php?option=com_jblance&view=message&layout=report&id='.$userid.'&report=profile&link='.base64_encode($uri)/* .'&tmpl=component' */);
 $link_edit_profile = JRoute::_('index.php?option=com_jblance&view=user&layout=editprofile');
 $link_edit_picture = JRoute::_('index.php?option=com_jblance&view=user&layout=editpicture');
 
 JblanceHelper::setJoomBriToken();
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="viewProfile" class="form-horizontal">
<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PROFILE').' - '.$this->userInfo->name; ?></div>
	
	<!-- Do not show send message & edit link to the profile owner -->
	<div class="page-actions">
	<?php if($enableAddThis & !$isMine) : ?>
		<div id="social-bookmark" class="page-action pull-left">
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style ">
			<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
			<a class="addthis_button_tweet"></a>
			<a class="addthis_button_google_plusone" g:plusone:size="medium"></a> 
			<a class="addthis_counter addthis_pill_style"></a>
			</div>
			<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=<?php echo $addThisPubid; ?>"></script>
			<!-- AddThis Button END -->
		</div>
	<?php endif; ?>
	<?php if($isMine) : ?>
		<div id="edit-profile" class="page-action">
		 	<a href="<?php echo $link_edit_profile; ?>"><i class="icon-edit"></i> <?php echo JText::_('COM_JBLANCE_EDIT_PROFILE'); ?></a>
		</div>
	<?php else : ?>
		<?php if($enableReporting) : ?>
		<div id="report-this" class="page-action">
		    <a href="<?php echo $link_report; ?>"><i class="icon-warning-sign"></i> <?php echo JText::_('COM_JBLANCE_REPORT_USER'); ?></a>
		</div>
		<?php endif; ?>
		<div id="send-message" class="page-action">
		    <a href="<?php echo $link_sendpm; ?>"><i class="icon-comment"></i> <?php echo JText::_('COM_JBLANCE_SEND_MESSAGE'); ?></a>
		</div>
	<?php endif; ?>
	</div>
	
	<fieldset>
		<legend><?php echo JText::_('COM_JBLANCE_USER_INFORMATION'); ?></legend>
		<div class="row-fluid">
			<div class="span5">
				<?php
				$att = "class='thumbnail'";
				$avatar = JblanceHelper::getLogo($userid, $att);
				echo $avatar;
				?><br>
				<?php if($isMine) : ?>
				<a href="<?php echo $link_edit_picture; ?>"><i class="icon-picture"></i> <?php echo JText::_('COM_JBLANCE_EDIT_PICTURE'); ?></a>
				<?php endif; ?>
				<?php if(!$isMine && $userInfo->allowBidProjects && $hasJBProfileForViewer && $viewerInfo->allowPostProjects ) : ?><!-- show invite to project to non-profile-owner, if profile owner can bid and profile viewer can post project -->
				<div class="row-fluid">
					<div class="span12">
						<?php $link_invite = JRoute::_('index.php?option=com_jblance&view=project&layout=invitetoproject&id='.$userid.'&tmpl=component'); ?>
						<a href="<?php echo $link_invite; ?>" class="btn btn-success btn-block btn-large jb-modal" rel="{handler: 'iframe', size: {x: 650, y: 400}}"><i class="icon-envelope icon-white"></i> <?php echo JText::_('COM_JBLANCE_INVITE_TO_PROJECT'); ?></a>
					</div>
				</div>
				<div class="sp10">&nbsp;</div>
				<div class="row-fluid">
					<div class="span6">
						<?php 
						$isFavourite = JblanceHelper::checkFavorite($userid, 'profile');	// check if profile owner is favoured by viewer ?>
						<span id="fav-msg-<?php echo $userid; ?>">
							<?php if($isFavourite > 0) : ?>
							<a onclick="favourite('<?php echo $userid; ?>', -1,'profile');" href="javascript:void(0);" class="btn btn-danger btn-block"><span class="icon-minus-sign icon-white"></span> <?php echo JText::_('COM_JBLANCE_REMOVE_FAVOURITE')?></a>
							<?php else : ?>
							<a onclick="favourite('<?php echo $userid; ?>', 1,'profile');" href="javascript:void(0);" class="btn btn-block"><span class="icon-plus-sign"></span> <?php echo JText::_('COM_JBLANCE_ADD_FAVOURITE')?></a>
							<?php endif; ?>
						</span>
					</div>
					<div class="span6">
						<a class="btn btn-block" href="<?php echo $link_sendpm; ?>"><i class="icon-comment"></i> <?php echo JText::_('COM_JBLANCE_SEND_MESSAGE'); ?></a>
					</div>
				</div>
				<?php endif; ?>
			</div>
			<div class="span7">
				<h2><?php echo  $this->userInfo->name; ?> <small><?php echo  $this->userInfo->username; ?></small></h2>
				
				<!-- Company Name should be visible only to users who can post project -->
				<?php if($userInfo->allowPostProjects && $showBizName) : ?>
				<div class="control-group">
					<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_BUSINESS_NAME'); ?>: </label>
					<div class="controls">
						<?php echo $this->userInfo->biz_name; ?>
					</div>
				</div>
				<?php endif; ?>
				
				<!-- Skills and hourly rate should be visible only to users who can work/bid -->
				<?php if($userInfo->allowBidProjects) : ?>
				<div class="control-group">
					<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_HOURLY_RATE'); ?>: </label>
					<div class="controls">
						<?php echo JblanceHelper::formatCurrency($this->userInfo->rate, true, true).' / '.JText::_('COM_JBLANCE_HOUR'); ?>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_SKILLS'); ?>: </label>
					<div class="controls">
						<?php echo JblanceHelper::getCategoryNames($this->userInfo->id_category); ?>
					</div>
				</div>
				<?php endif; ?>
				<div class="control-group">
					<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_AVERAGE_RATING'); ?>: </label>
					<div class="controls">
						<?php $rate = JblanceHelper::getAvarageRate($this->userInfo->user_id, true); ?>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_STATUS'); ?>: </label>
					<div class="controls">
						<?php
						//get user online status
						$status = $jbuser->isOnline($this->userInfo->user_id);
						?>
						<?php if($status) : ?>
							<span class="label label-success"><?php echo JText::_('COM_JBLANCE_ONLINE'); ?></span>
						<?php else : ?>
							<span class="label"><?php echo JText::_('COM_JBLANCE_OFFLINE'); ?></span>
						<?php endif; ?>	
					</div>
				</div>
			</div>
		</div>
	</fieldset>
	<?php if($isMine) : ?>
	<fieldset>
		<legend><?php echo JText::_('COM_JBLANCE_CONTACT_INFORMATION'); ?></legend>
		<div class="control-group">
			<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_ADDRESS'); ?>: </label>
			<div class="controls">
				<?php echo nl2br($this->userInfo->address); ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_LOCATION'); ?>: </label>
			<div class="controls">
				<?php echo JblanceHelper::getLocationNames($this->userInfo->id_location); ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_ZIP_POSTCODE'); ?>: </label>
			<div class="controls">
				<?php echo $this->userInfo->postcode; ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_CONTACT_NUMBER'); ?>: </label>
			<div class="controls">
				<?php echo $this->userInfo->mobile; ?>
			</div>
		</div>
	</fieldset>
	<?php endif; ?>
	<?php 
		$fields = JblanceHelper::get('helper.fields');		// create an instance of the class FieldsHelper
		$parents = array();$children = array();
	//isolate parent and childr
	foreach($this->fields as $ct){
		if($ct->parent == 0)
			$parents[] = $ct;
		else
			$children[] = $ct;
	}
		
	if(count($parents)){
		foreach($parents as $pt){ ?>
	<fieldset>
		<legend><?php echo JText::_($pt->field_title); ?></legend>
		<?php
		foreach($children as $ct){
			if($ct->parent == $pt->id){ ?>
				<?php
				$labelsuffix = '';
				if($ct->field_type == 'Checkbox') $labelsuffix = '[]'; //added to validate checkbox
				?>
			<div class="control-group">
				<label class="control-label nopadding" for="custom_field_<?php echo $ct->id.$labelsuffix; ?>"><?php echo JText::_($ct->field_title); ?>:</label>
			<div class="controls">
				<?php $fields->getFieldHTMLValues($ct, $userid, 'profile'); ?>
			</div>
		</div>
		<?php
			}
		} ?>
	</fieldset>
			<?php
		}
	}
	?>
	<?php if($userInfo->allowAddPortfolio) : ?>
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PORTFOLIO') ?></div>
	<?php 
	if(count($this->portfolios)) :
	?>
	<div class="row-fluid">
		<ul class="thumbnails">
		<?php 
		for($i=0, $n=count($this->portfolios); $i < $n; $i++){
			$portfolio 		= $this->portfolios[$i];
			$link_view_portfolio 	= JRoute::_('index.php?option=com_jblance&view=user&layout=viewportfolio&id='.$portfolio->id);
			
			//get the portfolio image info
			if($portfolio->picture) {
				$attachment = explode(";", $portfolio->picture);
				$showName = $attachment[0];
				$fileName = $attachment[1];
				$imgLoc = JBPORTFOLIO_URL.$fileName;
			}
			else 
				$imgLoc = 'components/com_jblance/images/no_portfolio.png';
			?>
			<li class="span4">
				<div class="thumbnail">
					<img style="width: 300px; height: 150px;" src="<?php echo $imgLoc; ?>" alt="" title="">
					<div class="caption">
						<h3><?php echo $portfolio->title; ?></h3>
						<p><a href="<?php echo $link_view_portfolio; ?>" class="btn"><?php echo JText::_('COM_JBLANCE_PORTFOLIO_DETAILS'); ?></a></p>
					</div>
				</div>
			</li>
			<?php 
			}
			?>
		</ul>
	</div>
	<?php 
	else : 
		echo JText::_('COM_JBLANCE_NO_PORTFOLIO_FOUND');
	endif; ?> <!-- end of portfolio count -->
	<div class="lineseparator"></div>
	<?php endif; ?>
		
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'freelancer')); ?>
	
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'freelancer', JText::_('COM_JBLANCE_FREELANCER', true)); ?>
	<!-- project history -->
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PROJECTS_HISTORY'); ?></div>
	<?php 
	if(count($this->fprojects)) : ?>
	<div id="no-more-tables">
	<table class="table table-bordered table-hover table-striped">
		<thead>
			<tr>
				<th>#</th>
				<th><?php echo JText::_('COM_JBLANCE_PROJECT_NAME'); ?></th>
				<th><?php echo JText::_('COM_JBLANCE_RATED_BY'); ?></th>
				<th><?php echo JText::_('COM_JBLANCE_RATING_FROM_PUBLISHER'); ?></th>
				<th><?php echo JText::_('COM_JBLANCE_COMMENTS'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		for($i=0, $n=count($this->fprojects); $i < $n; $i++){
			$fproject = $this->fprojects[$i];
			$buyer 	  = JFactory::getUser($fproject->publisher_userid);
		?>
		<tr>
			<td data-title="#">
				<?php echo $i+1;?>
			</td>
			<td data-title="<?php echo JText::_('COM_JBLANCE_PROJECT_NAME'); ?>">
				<?php echo LinkHelper::getProjectLink($fproject->id, $fproject->project_title); ?>
			</td>
			<td data-title="<?php echo JText::_('COM_JBLANCE_RATED_BY'); ?>">
				<?php echo LinkHelper::GetProfileLink($fproject->publisher_userid, $buyer->$nameOrUsername); ?>
			</td>
			<td data-title="<?php echo JText::_('COM_JBLANCE_RATING_FROM_PUBLISHER'); ?>">
				<?php
				$rate = JblanceHelper::getUserRateProject($fproject->assigned_userid, $fproject->id);
				JblanceHelper::getRatingHTML($rate);
				?>
			</td>
			<td data-title="<?php echo JText::_('COM_JBLANCE_COMMENTS'); ?>">
				<?php 
				if($rate > 0)
					echo $fproject->comments;
				else 
					echo '<i>'.JText::_('COM_JBLANCE_NOT_YET_RATED').'</i>';
				?>
			</td>
		</tr>
		<?php 
			$k = 1 - $k;
		} ?>
		</tbody>
	</table>
	</div>
	<?php 
		else : 
			echo JText::_('COM_JBLANCE_NO_PROJECTS_FOUND');
		endif;	
	?>
	
	<div class="sp20">&nbsp;</div>
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_RATING'); ?></div>
	<?php 
	if(!empty($this->frating->quality_clarity)) : ?>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_QUALITY_OF_WORK'); ?>: </label>
		<div class="controls">
			<?php JblanceHelper::getRatingHTML($this->frating->quality_clarity); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_COMMUNICATION'); ?>: </label>
		<div class="controls">
			<?php JblanceHelper::getRatingHTML($this->frating->communicate); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_EXPERTISE'); ?>: </label>
		<div class="controls">
			<?php JblanceHelper::getRatingHTML($this->frating->expertise_payment); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PROFESSIONALISM'); ?>: </label>
		<div class="controls">
			<?php JblanceHelper::getRatingHTML($this->frating->professional); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_HIRE_AGAIN'); ?>: </label>
		<div class="controls">
			<?php JblanceHelper::getRatingHTML($this->frating->hire_work_again); ?>
		</div>
	</div>
	<?php 
	else : 
		echo JText::_('COM_JBLANCE_RATING_NOT_FOUND');
	endif; ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>		<!-- end of freelancer tab -->
	
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'buyer', JText::_('COM_JBLANCE_BUYER', true)); ?>
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PROJECTS_HISTORY'); ?></div>
	<?php 
	if(count($this->bprojects)) : ?>
	<div id="no-more-tables">
	<table class="table table-bordered table-hover table-striped">
		<thead>
			<tr>
				<th>#</th>
				<th><?php echo JText::_('COM_JBLANCE_PROJECT_NAME'); ?></th>
				<th><?php echo JText::_('COM_JBLANCE_RATED_BY'); ?></th>
				<th><?php echo JText::_('COM_JBLANCE_RATING_FROM_FREELANCER'); ?></th>
				<th><?php echo JText::_('COM_JBLANCE_COMMENTS'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		for($i=0, $n=count($this->bprojects); $i < $n; $i++){
			$bproject 		= $this->bprojects[$i];
			$freelancer = JFactory::getUser($bproject->assigned_userid);
		?>
		<tr>
			<td data-title="#">
				<?php echo $i+1;?>
			</td>
			<td data-title="<?php echo JText::_('COM_JBLANCE_PROJECT_NAME'); ?>">
				<?php echo LinkHelper::getProjectLink($bproject->id, $bproject->project_title); ?>
			</td>
			<td data-title="<?php echo JText::_('COM_JBLANCE_RATED_BY'); ?>">
				<?php echo LinkHelper::GetProfileLink($bproject->assigned_userid, $freelancer->$nameOrUsername); ?>
			</td>
			<td data-title="<?php echo JText::_('COM_JBLANCE_RATING_FROM_FREELANCER'); ?>">
				<?php
				$rate = JblanceHelper::getUserRateProject($bproject->publisher_userid, $bproject->id);
				JblanceHelper::getRatingHTML($rate);
				?>
			</td>
			<td data-title="<?php echo JText::_('COM_JBLANCE_COMMENTS'); ?>">
				<?php 
				if($rate > 0)
					echo $bproject->comments;
				else 
					echo '<i>'.JText::_('COM_JBLANCE_NOT_YET_RATED').'</i>';
				?>
			</td>
		</tr>
		<?php 
			$k = 1 - $k;
		} ?>
		</tbody>
	</table>
	</div>
	<?php 
		else : 
			echo JText::_('COM_JBLANCE_NO_PROJECTS_FOUND');
		endif;	
	?>
	<div class="sp20">&nbsp;</div>
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_RATING'); ?></div>
	<?php 
	if(!empty($this->brating->quality_clarity)) : ?>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_CLARITY_SPECIFICATION'); ?>: </label>
		<div class="controls">
			<?php JblanceHelper::getRatingHTML($this->brating->quality_clarity); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_COMMUNICATION'); ?>: </label>
		<div class="controls">
			<?php JblanceHelper::getRatingHTML($this->brating->communicate); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PAYMENT_PROMPTNESS'); ?>: </label>
		<div class="controls">
			<?php JblanceHelper::getRatingHTML($this->brating->expertise_payment); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_PROFESSIONALISM'); ?>: </label>
		<div class="controls">
			<?php JblanceHelper::getRatingHTML($this->brating->professional); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_WORK_AGAIN'); ?>: </label>
		<div class="controls">
			<?php JblanceHelper::getRatingHTML($this->brating->hire_work_again); ?>
		</div>
	</div>
	<?php 
	else : 
		echo JText::_('COM_JBLANCE_RATING_NOT_FOUND');
	endif; ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>		<!-- end of buyer tab -->
	
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	<input type="hidden" name="option" value="com_jblance">
	<input type="hidden" name="task" value="">
	<?php echo JHtml::_('form.token'); ?>
</form>	
