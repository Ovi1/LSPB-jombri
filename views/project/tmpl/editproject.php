<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	23 March 2012
 * @file name	:	views/project/tmpl/editproject.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Post / Edit project (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 JHtml::_('behavior.modal', 'a.jb-modal');
 JHtml::_('bootstrap.tooltip');
 JHtml::_('formbehavior.chosen', '.advancedSelect');
 JHtml::_('formbehavior.chosen', '#id_category', null, array('placeholder_text_multiple'=>JText::_('COM_JBLANCE_SELECT_SKILLS_RELATE_TO_PROJECT')));
 
 $doc 	 = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/btngroup.js");
 $doc->addScript("components/com_jblance/js/utility.js");
 $doc->addScript("components/com_jblance/js/autosize.js");
 
 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 $finance = JblanceHelper::get('helper.finance');		// create an instance of the class FinanceHelper
 $user = JFactory::getUser();

 $config 		  = JblanceHelper::getConfig();
 $currencysym 	  = $config->currencySymbol;
 $fileLimitConf   = $config->projectMaxfileCount;
 $reviewProjects  = $config->reviewProjects;
 $sealProjectBids = $config->sealProjectBids;
 $seoOptimize 	  = $config->seoProjectOptimize;
 $projectUpgrades = $config->projectUpgrades;

 $isNew = ($this->row->id == 0) ? 1 : 0;
 $title = $isNew ? JText::_('COM_JBLANCE_POST_NEW_PROJECT') : JText::_('COM_JBLANCE_EDIT_PROJECT');

 //get the project upgrade amounts based on the plan
 $plan 				 = JblanceHelper::whichPlan($user->id);
 $featuredProjectFee = $plan->buyFeePerFeaturedProject;
 $urgentProjectFee 	 = $plan->buyFeePerUrgentProject;
 $privateProjectFee	 = $plan->buyFeePerPrivateProject;
 $sealedProjectFee	 = $plan->buyFeePerSealedProject;
 $ndaProjectFee		 = $plan->buyFeePerNDAProject;
 $chargePerProject	 = $plan->buyChargePerProject;
 
 $totalFund = JblanceHelper::getTotalFund($user->id);
 JText::script('COM_JBLANCE_CLOSE');
 
 $ndaFile = JURI::root().'components/com_jblance/images/nda.txt';
 
 JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
function validateForm(f){
	var valid = document.formvalidator.isValid(f);

	//check for validity of hours per day
	var checkedval = jQuery("input[name='project_type']:checked").val();
	if(checkedval == "COM_JBLANCE_HOURLY"){
		var cmt_prd = jQuery("#commitment_period").val();
		var cmt_int = jQuery("#commitment_interval").val();
		if(cmt_int == "COM_JBLANCE_DAY" && cmt_prd > 24){
			alert('<?php echo JText::_('COM_JBLANCE_HOURS_PER_DAY_EXCEEDED', true); ?>');
			return false;
		}
	}
	
	if(valid == true){
		var isNew = '<?php echo $isNew; ?>';
		var grandTotal = 0;
		var totalFund = parseFloat('<?php echo $totalFund; ?>');
		//check for grand_total = charge_per_project + project_upgrade_fee < total_fund for new project
		//grand_total = project_upgrade_fee < total_fund for old project
		if(isNew == 1)
			grandTotal = parseFloat('<?php echo $chargePerProject; ?>') + parseFloat(jQuery("#totalamount").val());
		else
			grandTotal = parseFloat(jQuery("#totalamount").val());

		jQuery("#subtotal").html(grandTotal);

		if(totalFund < grandTotal){
			alert('<?php echo JText::_('COM_JBLANCE_BALANCE_INSUFFICIENT_TO_PROMOTE_PROJECT', true); ?>');
			return false;
		}
		jQuery("#submitbtn").prop("disabled", true);
		jQuery("#submitbtn").prop("value", '<?php echo JText::_('COM_JBLANCE_SAVING'); ?>');
		
    }
    else {
	    var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
	    if($("expires").hasClass("invalid")){
	    	msg = msg+'\n\n* '+'<?php echo JText::_('COM_JBLANCE_PLEASE_ENTER_VALUE_IN_NUMERIC_ONLY', true); ?>';
	    }
		alert(msg);
		return false;
    }
	return true;
}

function updateTotalAmount(el){
	var element = jQuery(el).attr("name");
	var tot = parseFloat(jQuery("#totalamount").val());
	var fee = 0;
	
	if(element == "is_featured")
		fee = parseFloat('<?php echo $featuredProjectFee; ?>');
	else if(element == "is_urgent")
		fee = parseFloat('<?php echo $urgentProjectFee; ?>');
	else if(element == "is_private")
		fee = parseFloat('<?php echo $privateProjectFee; ?>');
	else if(element == "is_sealed")
		fee = parseFloat('<?php echo $sealedProjectFee; ?>');
	else if(element == "is_nda")
		fee = parseFloat('<?php echo $ndaProjectFee; ?>');

	if(jQuery("#"+element+":checked").length){
		tot = parseFloat(tot + fee);
	}
	else {
		tot = parseFloat(tot - fee);
	}
	jQuery("#subtotal").html(tot);
	jQuery("#totalamount").val(tot);
}

jQuery(document).ready(function($){
	$("input[name='project_type']").on("click", updateProjectTypeFields);
	$("input[name='commitment[undefined]']").on("click", updateCommitmentTypeFields);
	updateProjectTypeFields();
	updateCommitmentTypeFields();
});

function updateProjectTypeFields(){
	var checkedval = jQuery("input[name='project_type']:checked").val();
	if(checkedval == "COM_JBLANCE_FIXED"){
		jQuery("div[data-project-type='hourly']").css("display", "none");
		jQuery("div[data-project-type='fixed']").css("display", "block");
		jQuery("#budgetrange_fixed").addClass("required").prop("required", "required");
		jQuery("#budgetrange_hourly").removeClass("required").removeProp("required");
		jQuery("#project_duration").removeClass("required").removeProp("required");
	}
	else if(checkedval == "COM_JBLANCE_HOURLY"){
		jQuery("div[data-project-type='hourly']").css("display","block");
		jQuery("div[data-project-type='fixed']").css("display","none");
		jQuery("#budgetrange_fixed").removeClass("required").removeProp("required");;
		jQuery("#budgetrange_hourly").addClass("required").prop("required", "required");
		jQuery("#project_duration").addClass("required").prop("required", "required");
	}
}

function updateCommitmentTypeFields(){
	var checkedval = jQuery("input[name='commitment[undefined]']:checked").val();
	if(checkedval == "sure"){
		jQuery("#commitment_period").addClass("required").prop("required", "required");
	}
	else if(checkedval == "notsure"){
		jQuery("#commitment_period").removeClass("required").removeProp("required");
	}
}

function editLocation(){
	jQuery("#level1").css("display", "inline-block").addClass("required");
}

jQuery(document).ready(function() {
	autosize(jQuery("#description"));
});
//-->
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormProject" id="userFormProject" class="form-validate form-horizontal" onsubmit="return validateForm(this);" enctype="multipart/form-data" novalidate>
	<div class="jbl_h3title"><?php echo $title; ?></div>
	<?php 
	$lastSubscr = $finance->getLastSubscription($user->id);
	if($lastSubscr->projects_allowed > 0) :
	?>
	<div class="bid_project_left" style="float:right">
	    <div><span class="font26"><?php echo $lastSubscr->projects_left; ?></span>/<span><?php echo $lastSubscr->projects_allowed; ?></span></div>
	    <div><?php echo JText::_('COM_JBLANCE_PROJECTS_LEFT'); ?></div>
	</div>
	<?php endif; ?>
	<fieldset>
		<legend><?php echo JText::_('COM_JBLANCE_YOUR_PROJECT_DETAILS'); ?></legend>
		<div class="control-group">
			<label class="control-label" for="project_title"><?php echo JText::_('COM_JBLANCE_PROJECT_TITLE'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<input type="text" class="input-xxlarge required" name="project_title" id="project_title" value="<?php echo $this->row->project_title;?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="id_category"><?php echo JText::_('COM_JBLANCE_PROJECT_CATEGORIES'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<?php 
				//$attribs = 'class="form-control required" size="20" multiple ';
				//$defaultCategory = empty($this->row->id_category) ? 0 : explode(',', $this->row->id_category);
				//$categtree = $select->getSelectCategoryTree('id_category[]', $defaultCategory, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '', true);
				//echo $categtree; 
				//$attribs = '';
				//$select->getCheckCategoryTree('id_category[]', explode(',', $this->row->id_category), $attribs);
				?>
				<?php 
				$attribs = "class='input-xxlarge required' size='5' MULTIPLE";
				echo $select->getSelectCategoryTree('id_category[]', explode(',', $this->row->id_category), '', $attribs, '', true); ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="start_date"><?php echo JText::_('COM_JBLANCE_PUBLISH_DATE'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				 <?php 
				 $now = JFactory::getDate()->toSql();
				 $startdate = (empty($this->row->start_date)) ? $now : $this->row->start_date;
				 echo JHtml::_('calendar', $startdate, 'start_date', 'start_date', '%Y-%m-%d', array('class'=>'input-small required', 'size'=>'20',  'maxlength'=>'32'));
				 ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="expires"><?php echo JText::_('COM_JBLANCE_EXPIRES'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<div class="input-prepend input-append">
					<input type="text" class="input-small required validate-numeric" name="expires" id="expires" value="<?php echo $this->row->expires; ?>" />
					<span class="add-on"><?php echo JText::_('COM_JBLANCE_DAYS'); ?></span>
				</div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="project_type"><?php echo JText::_('COM_JBLANCE_PROJECT_TYPE'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<label class="radio" <?php echo $isNew ? 'style="display:block;"' : 'style="display:none;"';?>>
				<?php 
				$default = empty($this->row->project_type) ?  'COM_JBLANCE_FIXED' : $this->row->project_type;
				$project_type = $select->getRadioProjectType('project_type', $default);
				echo  $project_type; 
				?>
				</label>
				<span class="label label-info"><?php echo JText::_($this->row->project_type); ?></span>
			</div>
		</div>
		<!-- fields for fixed projects -->
		<div data-project-type="fixed">
			<div class="control-group">
				<label class="control-label" for="budgetrange_fixed"><?php echo JText::_('COM_JBLANCE_BUDGET'); ?> <span class="redfont">*</span>:</label>
				<div class="controls">
					<?php 
					$attribs = 'class="input-xlarge required advancedSelect"';
					$default = $this->row->budgetmin.'-'.$this->row->budgetmax;
					echo $select->getSelectBudgetRange('budgetrange_fixed', $default, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '', 'COM_JBLANCE_FIXED');
					?>
				</div>
			</div>
		</div>
		<!-- fields for hourly projects -->
		<div data-project-type="hourly">
			<div class="control-group">
				<label class="control-label" for="budgetrange_hourly"><?php echo JText::_('COM_JBLANCE_BUDGET'); ?> <span class="redfont">*</span>:</label>
				<div class="controls">
					<?php 
					$attribs = 'class="input-xlarge required advancedSelect"';
					$default = $this->row->budgetmin.'-'.$this->row->budgetmax;
					echo $select->getSelectBudgetRange('budgetrange_hourly', $default, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '', 'COM_JBLANCE_HOURLY');
					?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="project_duration"><?php echo JText::_('COM_JBLANCE_PROJECT_DURATION'); ?> <span class="redfont">*</span>:</label>
				<div class="controls">
					<?php 
					$attribs = 'class="input-xlarge required advancedSelect"';
					echo $select->getSelectProjectDuration('project_duration', $this->row->project_duration, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '');
					?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="budgetrange"><?php echo JText::_('COM_JBLANCE_HOURS_OF_WORK_REQUIRED'); ?> <span class="redfont">*</span>:</label>
				<div class="controls">
					<label class="radio inline">
						<?php 
						// Convert the commitment params field to an array.
						$commitment = new JRegistry;
						$commitment->loadString($this->row->commitment);
						?>
						<input class="" type="radio" id="commitment_defined_option" name="commitment[undefined]" value="sure" <?php echo ($commitment->get('undefined') == 'sure') ? 'checked' : ''; ?> />
					</label>
						<input type="text" class="form-control validate-numeric" name="commitment[period]" id="commitment_period" maxlength="3" size="3" value="<?php echo $commitment->get('period'); ?>" />&nbsp;<?php echo JText::_('COM_JBLANCE_HOURS_PER'); ?>&nbsp;
						<select name="commitment[interval]" id="commitment_interval" class="form-control advancedSelect">
		                	<option value="COM_JBLANCE_DAY" <?php echo ($commitment->get('interval') == 'COM_JBLANCE_DAY') ? 'selected' : ''; ?>><?php echo JText::_('COM_JBLANCE_DAY'); ?></option>
		                	<option value="COM_JBLANCE_WEEK" <?php echo ($commitment->get('interval') == 'COM_JBLANCE_WEEK') ? 'selected' : ''; ?>><?php echo JText::_('COM_JBLANCE_WEEK'); ?></option>
		                	<option value="COM_JBLANCE_MONTH" <?php echo ($commitment->get('interval') == 'COM_JBLANCE_MONTH') ? 'selected' : ''; ?>><?php echo JText::_('COM_JBLANCE_MONTH'); ?></option>
		            	</select>
	            	
	            	<label class="radio">
						<input type="radio" id="commitment_undefined_option" name="commitment[undefined]" value="notsure" <?php echo ($commitment->get('undefined', 'notsure') == 'notsure') ? 'checked' : ''; ?>  />
						<?php echo JText::_('COM_JBLANCE_NOT_SURE'); ?>
					</label>
				</div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="level1"><?php echo JText::_('COM_JBLANCE_LOCATION'); ?> <span class="redfont">*</span>:</label>
			<?php 
			if($this->row->id_location > 0){ ?>
				<div class="controls">
					<?php echo JblanceHelper::getLocationNames($this->row->id_location); ?>
					<button type="button" class="btn btn-mini" onclick="editLocation();"><?php echo JText::_('COM_JBLANCE_EDIT'); ?></button>
				</div>
			<?php 	
			}
			?>
			<div class="controls controls-row" id="location_info">
				<?php 
				$attribs = array('class' => 'form-control', 'data-level-id' => '1', 'onchange' => 'getLocation(this, \'project.getlocationajax\');');
				
				if($this->row->id_location == 0){
					$attribs['class'] = 'form-control required';
					$attribs['style'] = 'display: inline-block;';
				}
				else {
					$attribs['style'] = 'display: none;';
				}
				echo $select->getSelectLocationCascade('location_level[]', '', 'COM_JBLANCE_PLEASE_SELECT', $attribs, 'level1');
				?>
				<input type="hidden" name="id_location" id="id_location" value="<?php echo $this->row->id_location; ?>" />
				<div id="ajax-container" class="dis-inl-blk"></div>	
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="description"><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<textarea name="description" id="description" class="input-xxlarge required hasTooltip" rows="5" style="max-height: 300px;"><?php echo JFilterInput::getInstance()->clean($this->row->description, 'string'); ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo JText::_('COM_JBLANCE_ATTACHMENT'); ?> :</label>
			<div class="controls">
				<?php
				for($i=0; $i < $fileLimitConf; $i++){
				?>
				<input name="uploadFile<?php echo $i;?>" type="file" id="uploadFile<?php echo $i;?>" /><br>
				<?php 
				} ?>
				<input name="uploadLimit" type="hidden" value="<?php echo $fileLimitConf;?>" />
				<?php 
				$tipmsg = JHtml::tooltipText(JText::_('COM_JBLANCE_ATTACH_FILE').'::'.JText::_('COM_JBLANCE_ALLOWED_FILE_TYPES').' : '.$config->projectFileText.'<br>'.JText::_('COM_JBLANCE_MAXIMUM_FILE_SIZE').' : '.$config->projectMaxsize.' kB');
				?>
				<img src="components/com_jblance/images/tooltip.png" class="hasTooltip" title="<?php echo $tipmsg; ?>"/>
				<div class="lineseparator"></div>
				<?php 
				foreach($this->projfiles as $projfile){ ?>
				<label class="checkbox">
					<input type="checkbox" name=file-id[] value="<?php echo $projfile->id; ?>" />
  					<?php echo LinkHelper::getDownloadLink('project', $projfile->id, 'project.download'); ?>
				</label>
				<?php	
				}
				?>
			</div>
		</div>
	</fieldset>
	
	<?php 
	$fields = JblanceHelper::get('helper.fields');		// create an instance of the class fieldsHelper
	
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
			<div class="control-group">
					<?php
					$labelsuffix = '';
					if($ct->field_type == 'Checkbox') $labelsuffix = '[]'; //added to validate checkbox
					?>
				<label class="control-label" for="custom_field_<?php echo $ct->id.$labelsuffix; ?>"><?php echo JText::_($ct->field_title); ?><span class="redfont"><?php echo ($ct->required)? '*' : ''; ?></span>:</label>
				<div class="controls">
					<?php $fields->getFieldHTML($ct, $this->row->id, 'project'); ?>
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
	<?php if($seoOptimize || $projectUpgrades){ ?>
	<a class="btn btn-success" data-toggle="collapse" data-target="#more-options"><?php echo JText::_('COM_JBLANCE_MORE_OPTIONS'); ?> &raquo;</a></p>
	<?php } ?>
	<div class="collapse" id="more-options">
	<?php if($seoOptimize){ ?>
	<fieldset>
		<legend><?php echo JText::_('COM_JBLANCE_SEO_OPTIMIZATION'); ?></legend>
		<div class="control-group">
			<label class="control-label" for="metadesc"><?php echo JText::_('COM_JBLANCE_META_DESCRIPTION'); ?>:</label>
			<div class="controls">
				<textarea name="metadesc" id="metadesc" rows="3" class="input-xlarge"><?php echo $this->row->metadesc; ?></textarea>
				<?php 
				$tipmsg = JHtml::tooltipText(JText::_('COM_JBLANCE_META_DESCRIPTION'), JText::_('COM_JBLANCE_META_DESCRIPTION_TIPS'));
				?>
				<img src="components/com_jblance/images/tooltip.png" class="hasTooltip" title="<?php echo $tipmsg; ?>"/>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="metakey"><?php echo JText::_('COM_JBLANCE_META_KEYWORDS'); ?>:</label>
			<div class="controls">
				<textarea name="metakey" id="metakey" rows="3" class="input-xlarge"><?php echo $this->row->metakey; ?></textarea>
				<?php 
				$tipmsg = JHtml::tooltipText(JText::_('COM_JBLANCE_META_KEYWORDS'), JText::_('COM_JBLANCE_META_KEYWORDS_TIPS'));
				?>
				<img src="components/com_jblance/images/tooltip.png" class="hasTooltip" title="<?php echo $tipmsg; ?>"/>
			</div>
		</div>
	</fieldset>
	<?php } ?>
	
	<?php if($projectUpgrades){ ?>
	<fieldset>
		<legend><?php echo JText::_('COM_JBLANCE_PROMOTE_YOUR_LISTING'); ?></legend>
		<ul class="upgrades">
			<!-- The project once set as 'Featured' should not be able to change again -->
			<li class="project_upgrades">
				<div class="pad">
					<?php if(!$this->row->is_featured) : ?>
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" class="project_upgrades" onclick="updateTotalAmount(this);" /> 
                    <span class="upgrade featured"></span> 
                    <p><?php echo JText::_('COM_JBLANCE_FEATURED_PROJECT_DESC'); ?></p>
					<span class="price"><?php echo JblanceHelper::formatCurrency($featuredProjectFee); ?></span>
					
					<?php else : ?>
					<span class="upgrade featured"></span>
					<p><?php echo JText::_('COM_JBLANCE_THIS_IS_A_FEATURED_PROJECT'); ?></p>
					<?php endif; ?>
					<div class="clearfix"></div>
				</div>
			</li>
			<!-- The project once set as 'Urgent' should not be able to change again -->
			<li class="project_upgrades">
				<div class="pad">
					<?php if(!$this->row->is_urgent) : ?>
                    <input type="checkbox" id="is_urgent" name="is_urgent" value="1" class="project_upgrades" onclick="updateTotalAmount(this);" /> 
                    <span class="upgrade urgent"></span> 
                    <p><?php echo JText::_('COM_JBLANCE_URGENT_PROJECT_DESC'); ?></p>
					<span class="price"><?php echo JblanceHelper::formatCurrency($urgentProjectFee); ?></span>
					<?php else : ?>
					<span class="upgrade urgent"></span>
					<p><?php echo JText::_('COM_JBLANCE_THIS_IS_AN_URGENT_PROJECT'); ?></p>
					<?php endif; ?>
					<div class="clearfix"></div>
				</div>
			</li>
			<!-- The project once set as 'Private' should not be able to change again -->
			<li class="project_upgrades">
				<div class="pad">
					<?php if(!$this->row->is_private) : ?>
					<input type="checkbox" id="is_private" name="is_private" value="1" class="project_upgrades" onclick="updateTotalAmount(this);" />
                    <span class="upgrade private"></span> 
                    <p><?php echo JText::_('COM_JBLANCE_PRIVATE_PROJECT_DESC'); ?></p>
					<span class="price"><?php echo JblanceHelper::formatCurrency($privateProjectFee); ?></span>
					<?php else : ?>
					<span class="upgrade private"></span>
					<p><?php echo JText::_('COM_JBLANCE_THIS_IS_A_PRIVATE_PROJECT'); ?></p>
					<?php endif; ?>
					<div class="clearfix"></div>
				</div>
			</li>
			<!-- The project once set as 'Sealed' should not be able to change again -->
			<li class="project_upgrades">
				<div class="pad">
					<?php if(!($sealProjectBids || $this->row->is_sealed)) : ?>
					<input type="checkbox" id="is_sealed" name="is_sealed" value="1" class="project_upgrades" onclick="updateTotalAmount(this);" />
                    <span class="upgrade sealed"></span> 
                    <p><?php echo JText::_('COM_JBLANCE_SEALED_PROJECT_DESC'); ?></p>
					<span class="price"><?php echo JblanceHelper::formatCurrency($sealedProjectFee); ?></span>
					<?php else : ?>
					<span class="upgrade sealed"></span>
					<p><?php echo JText::_('COM_JBLANCE_THIS_IS_A_SEALED_PROJECT'); ?></p>
					<?php endif; ?>
					<div class="clearfix"></div>
				</div>
			</li>
			<!-- The project once set as 'NDA' should not be able to change again -->
			<li class="project_upgrades">
				<div class="pad">
					<?php if(!$this->row->is_nda) : ?>
					<input type="checkbox" id="is_nda" name="is_nda" value="1" class="project_upgrades" onclick="updateTotalAmount(this);" />
                    <span class="upgrade nda"></span> 
                    <p><?php echo JText::sprintf('COM_JBLANCE_NDA_PROJECT_DESC', $ndaFile); ?></p>
					<span class="price"><?php echo JblanceHelper::formatCurrency($ndaProjectFee); ?></span>
					<?php else : ?>
					<span class="upgrade nda"></span>
					<p><?php echo JText::_('COM_JBLANCE_THIS_IS_A_NDA_PROJECT'); ?></p>
					<?php endif; ?>
					<div class="clearfix"></div>
				</div>
			</li>
			<!-- The project once set as 'Private Invide' should not be able to change again -->
			<li class="project_upgrades">
				<div class="pad">
					<input type="checkbox" id="is_private_invite" name="is_private_invite" value="1" class="project_upgrades" <?php echo ($this->row->is_private_invite) ? 'checked' : ''; ?> />
                    <span class="upgrade invite"></span> 
                    <p><?php echo JText::_('COM_JBLANCE_PRIVATE_INVITE_PROJECT_DESC'); ?></p>
					<div class="clearfix"></div>
				</div>
			</li>
			<li class="project_upgrades">
				<div class="pad">
					<div class="row-fluid">
						<div class="span4">
							<?php echo JText::_('COM_JBLANCE_CURRENT_BALANCE') ?> : <span class="font16 boldfont"><?php echo JblanceHelper::formatCurrency($totalFund); ?></span>
						</div>
						<div class="span4">
							<?php if($chargePerProject > 0 && $isNew) : ?>
							<?php echo JText::_('COM_JBLANCE_CHARGE_PER_PROJECT'); ?> : <span class="font16 boldfont"><?php echo JblanceHelper::formatCurrency($chargePerProject); ?></span>
							<?php endif; ?>
						</div>
						<div class="span4">
							<?php echo JText::_('COM_JBLANCE_TOTAL')?> : <span class="font16 boldfont"><?php echo $currencysym; ?><span id="subtotal">0.00</span></span>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</li>
		</ul>
	</fieldset>
	<?php } ?>
	</div>
	
	<div class="font14 boldfont">
	<?php 
	if($reviewProjects && !$this->row->approved){ ?>
		<div class="jbbox-info"><?php echo JText::_('COM_JBLANCE_PROJECT_WILL_BE_REVIEWED_BY_ADMIN_BEFORE_LIVE'); ?></div>
	<?php 
	}
	?>
	</div>
	
	<?php 
	if(!empty($this->row->invite_user_id)) { ?>
	<div class="jbbox-info">
	<?php
		echo JText::_('COM_JBLANCE_INVITATION_SENT_TO_USERS');
		$invite_user_ids = explode(',', $this->row->invite_user_id);
		foreach($invite_user_ids as $key=>$val){
			$inviteUserInfo = JFactory::getUser($val);
			echo $inviteUserInfo->username.', ';
		} ?>
	</div>
	<?php 
	} ?>
	
	<div class="form-actions">
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SAVE_PROJECT'); ?>" class="btn btn-primary" id="submitbtn" /> 
		<input type="button" value="<?php echo JText::_('COM_JBLANCE_CANCEL'); ?>" onclick="javascript:history.back();" class="btn" />
	</div>
	
	<input type="hidden" name="option" value="com_jblance" /> 
	<input type="hidden" name="task" value="project.saveproject" /> 
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="totalamount" id="totalamount" value="0.00" />
	<?php echo JHtml::_('form.token'); ?>
</form>