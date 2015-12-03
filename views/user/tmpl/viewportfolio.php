<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	29 November 2012
 * @file name	:	views/user/tmpl/viewportfolio.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Lets user to view porfolio (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 $row = $this->row;
 $config = JblanceHelper::getConfig();
 $dformat = $config->dateFormat;
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">
	<div class="jbl_h3title">
		<?php echo JText::_('COM_JBLANCE_PORTFOLIO_DETAILS').' - '.$row->title; ?>
	</div>
	<div class="row-fluid">
		<?php
		if($this->row->picture){
			$attachment = explode(";", $this->row->picture);
			$showName = $attachment[0];
			$fileName = $attachment[1];
			$imgLoc = JBPORTFOLIO_URL.$fileName;
		?>
		<p class="jb-aligncenter"><img src='<?php echo $imgLoc; ?>' class="img-polaroid" style="max-width: 450px; width: 95%" /></p>
		<?php 
		} ?>
		
		<?php 
		if(!empty($this->row->video_link)){
		$youtubeUrl =  JUri::getInstance($this->row->video_link);
		$videoId = $youtubeUrl->getVar('v'); ?>

		<div class="jb-aligncenter">
			<object width="640" height="390">
				<param name="movie" value="https://www.youtube.com/v/<?php echo $videoId; ?>?version=3"></param>
				<param name="allowScriptAccess" value="always"></param>
				<embed src="https://www.youtube.com/v/<?php echo $videoId; ?>?version=3" type="application/x-shockwave-flash" allowscriptaccess="always" width="640" height="390"></embed>
			</object>
		</div>
		<?php 
		}
		?>
		<div class="clearfix"></div>
		<h4><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?>:</h4>
		<p><?php echo nl2br($row->description); ?></p>
		
		<h4><?php echo JText::_('COM_JBLANCE_SKILLS'); ?>:</h4>
		<p><?php echo JblanceHelper::getCategoryNames($row->id_category); ?></p>
		
		<h4><?php echo JText::_('COM_JBLANCE_WEB_ADDRESS'); ?>:</h4>
		<p><?php echo !empty($row->link) ? $row->link : '<span class="redfont">'.JText::_('COM_JBLANCE_NOT_MENTIONED').'</span>'; ?></p>
		
		<h4><?php echo JText::_('COM_JBLANCE_DURATION'); ?>:</h4>
		<p>
			<?php
			if( ($row->start_date != "0000-00-00 00:00:00" ) && ($row->finish_date!= "0000-00-00 00:00:00") ){
			?>
				<?php echo JHtml::_('date', $this->row->start_date, $dformat).' &harr; '.JHtml::_('date', $this->row->finish_date, $dformat); ?>
			<?php 
			}
			else
				echo '<span class="redfont">'.JText::_('COM_JBLANCE_NOT_MENTIONED').'</span>';
			?>
		</p>
		<h4><?php echo JText::_('COM_JBLANCE_ATTACHMENT'); ?>:</h4>
		<?php 
		$count = 0;
		for($i=1; $i<=5; $i++){
			$attachmentColumnNum = 'attachment'.$i;
			if($row->$attachmentColumnNum){ 
				$count++;
			?>
		<p><i class="material-icons">file_download</i> <?php echo LinkHelper::getPortfolioDownloadLink('portfolio', $this->row->id, 'user.download', $attachmentColumnNum); ?></p>
		<?php 
			}
		} 
		if($count == 0)
			echo JText::_('COM_JBLANCE_ATTACHMENT_NOT_FOUND');
		?>
	</div>
</form>