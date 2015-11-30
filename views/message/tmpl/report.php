<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	15 July 2012
 * @file name	:	views/message/tmpl/report.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Report Items (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('behavior.formvalidator');
 
 $app  = JFactory::getApplication();
 $model = $this->getModel();
 
 $report = $app->input->get('report', '', 'string');
 $link 	 = $app->input->get('link', '', 'string');
 $id 	 = $app->input->get('id', 0, 'int');
 
 //redirect if the reporting is disabled
 $config = JblanceHelper::getConfig();
 $enableReporting = $config->enableReporting;
 
 if(!$enableReporting){
 	$app = JFactory::getApplication();
 	$msg = JText::_('COM_JBLANCE_REPORTING_DISABLED');
 	$app->enqueueMessage($msg, 'error');
 	$app->redirect(base64_decode($link));
 }
?>
<script type="text/javascript">
<!--
function validateForm(f){
	var valid = document.formvalidator.isValid(f);
	
	if(valid == true){
		
    }
    else {
		alert('<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>');
		return false;
    }
	return true;
}
//-->
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormProject" id="userFormProject" class="form-validate form-horizontal" onsubmit="return validateForm(this);">
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_REPORT_THIS'); ?></div>
	<!-- <div class="control-group">
		<label class="control-label"><?php echo JText::_('COM_JBLANCE_URL_VIOLATION'); ?> :</label>
		<div class="controls">
			<?php //echo base64_decode($link); ?>
		</div>
	</div> -->
	<div class="control-group">
		<label class="control-label" for="category"><?php echo JText::_('COM_JBLANCE_REPORT_CATEGORY'); ?> :</label>
		<div class="controls">
			<?php echo $model->getSelectReportCategory(); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="message"><?php echo JText::_('COM_JBLANCE_MESSAGE'); ?> :</label>
		<div class="controls">
			<textarea name="message" id="message" rows="5" class="input-xlarge required"></textarea>
		</div>
	</div>
	<div class="form-actions">
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SUBMIT'); ?>" class="btn btn-primary"/> 
	</div>
	
	<input type="hidden" name="option" value="com_jblance" />			
	<input type="hidden" name="task" value="message.savereport" />	
	<input type="hidden" name="reportitemid" value="<?php echo $id; ?>" />
	<input type="hidden" name="report" value="<?php echo $report; ?>" />
	<input type="hidden" name="link" value="<?php echo $link; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>