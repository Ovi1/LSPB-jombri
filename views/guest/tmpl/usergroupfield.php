<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	16 March 2012
 * @file name	:	views/guest/tmpl/usergroupfield.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	User Groups (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 JHtml::_('behavior.formvalidator');
 JHtml::_('bootstrap.tooltip');
 
 $doc 	 = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");
 
 $app = JFactory::getApplication();
 $user= JFactory::getUser();
 $model = $this->getModel();
 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper

 //set the chosen plan in the session
 $session = JFactory::getSession();
 $ugid = $session->get('ugid', 0, 'register');
 $accountInfo 	= $session->get('userInfo', null, 'register');
 
 $jbuser = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
 $userInfo = $jbuser->getUserGroupInfo(null, $ugid);
 
 $config 	  = JblanceHelper::getConfig();
 $currencysym = $config->currencySymbol;
 $currencycod = $config->currencyCode;
 $maxSkills   = $config->maxSkills;
 
 JHtml::_('formbehavior.chosen', '#id_category', null, array('max_selected_options' => $maxSkills, 'placeholder_text_multiple' => JText::_('COM_JBLANCE_PLEASE_SELECT_SKILLS_FROM_THE_LIST')));
 
 //if the user is already registered, accoutnInfo will be empty.
 if(empty($accountInfo)){
 	$accountInfo['username'] = $user->username;
 	$accountInfo['name'] = $user->name;
 }
 
 $step = $app->input->get('step', 0, 'int');
 JText::script('COM_JBLANCE_CLOSE');
 
 JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
<!--
function validateForm(f){
	if(jQuery("#id_category").length){
		if(!jQuery("#id_category option:selected").length){
			alert('<?php echo JText::_('COM_JBLANCE_PLEASE_SELECT_SKILLS_FROM_THE_LIST', true); ?>');
			return false;
		}
	}
	if (document.formvalidator.isValid(f)) {
		
    }
    else {
	    var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
	    if(jQuery("#rate").length && jQuery("#rate").hasClass("invalid")){
	    	msg = msg+'\n\n* '+'<?php echo JText::_('COM_JBLANCE_PLEASE_ENTER_AMOUNT_IN_NUMERIC_ONLY', true); ?>';
	    }
		alert(msg);
		return false;
    }
	return true;
}

<?php if($maxSkills > 0){ ?>
jQuery(document).ready(function($){
	if($("#id_category").length){
		$("#id_category").change(updateSkillCount);
		updateSkillCount();
	}
});
<?php } ?>

function updateSkillCount(){
	sel = jQuery("#id_category option:selected").length;
	jQuery("#skill_left_span").html(sel);
}
//-->
</script>

<div class="panel panel-default">
    <div class="panel-heading">
        	<h3><?php echo JText::_('COM_JBLANCE_PROFILE_INFO'); ?></h3>

    </div>
    <div class="panel-body">
        <?php 
if($step)
	echo JblanceHelper::getProgressBar($step); 
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userGroup" class="form-validate" onsubmit="return validateForm(this);" enctype="multipart/form-data" novalidate>
        <fieldset class="col-md-4">
		<legend><?php echo JText::_('COM_JBLANCE_USER_INFORMATION'); ?></legend>
		<div class="form-group">
			<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_USERNAME'); ?>:</label>
			<div class="controls">
				<?php echo $accountInfo['username']; ?>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label nopadding"><?php echo JText::_('COM_JBLANCE_NAME'); ?>:</label>
			<div class="controls">
				<?php echo $accountInfo['name']; ?>
			</div>
		</div>
		<!-- Company Name should be visible only to users who can post job -->
		<?php if($userInfo->allowPostProjects) : ?>
		<div class="form-group">
			<label class="control-label" for="biz_name"><?php echo JText::_('COM_JBLANCE_BUSINESS_NAME'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<input class="form-control required" type="text" name="biz_name" id="biz_name" value="" />
			</div>
		</div>
		<?php endif; ?>
		<!-- Skills and hourly rate should be visible only to users who can work/bid -->
		<?php if($userInfo->allowBidProjects) : ?>
		<div class="form-group">
			<label class="control-label" for="rate"><?php echo JText::_('COM_JBLANCE_HOURLY_RATE'); ?> <span class="redfont">*</span>:</label>
			<div class="input-group">
					<span class="input-group-addon"><?php echo $currencysym; ?></span>
					<input class="form-control required validate-numeric" type="text" name="rate" id="rate" value="" />
					<span class="input-group-addon"><?php echo $currencycod.' / '.JText::_('COM_JBLANCE_HOUR'); ?></span>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="id_category"><?php echo JText::_('COM_JBLANCE_SKILLS'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<?php if($maxSkills > 0){ ?>
				<div class="bid_project_left pull-left">
		    		<div><span id="skill_left_span" class="font26"><?php echo '0';?></span>/<span><?php echo $maxSkills; ?></span></div>
		    		<div><?php echo JText::_('COM_JBLANCE_SKILLS'); ?></div>
				</div>
				<div class="clearfix"></div>
				<?php } ?>
				<?php 
				//$attribs = 'class="form-control required" size="20" multiple ';
				//$categtree = $select->getSelectCategoryTree('id_category[]', 0, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '', true);
				//echo $categtree; 
				//$attribs = '';
				//$select->getCheckCategoryTree('id_category[]', array(), $attribs); ?>
				<?php
				$attribs = "class='input-xxlarge required' multiple";
				echo $select->getSelectCategoryTree('id_category[]', 0, '', $attribs, '', true);
				?>
			</div>
		</div>
		<?php endif; ?>
	</fieldset>
	
    <fieldset class="col-md-8">
		<legend><?php echo JText::_('COM_JBLANCE_CONTACT_INFORMATION'); ?></legend>
		<div class="form-group">
			<label class="control-label" for="address"><?php echo JText::_('COM_JBLANCE_ADDRESS'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
			<textarea name="address" id="address" rows="3" class="form-control required"></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="level1"><?php echo JText::_('COM_JBLANCE_LOCATION'); ?> <span class="redfont">*</span>:</label>
			<div class="controls controls-row" id="location_info">
				<?php 
				$attribs = array('class' => 'form-control required', 'data-level-id' => '1', 'onchange' => 'getLocation(this, \'project.getlocationajax\');');
				
				echo $select->getSelectLocationCascade('location_level[]', '', 'COM_JBLANCE_PLEASE_SELECT', $attribs, 'level1');
				?>
				<input type="hidden" name="id_location" id="id_location" value="" />
				<div id="ajax-container" class="dis-inl-blk"></div>	
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="postcode"><?php echo JText::_('COM_JBLANCE_ZIP_POSTCODE'); ?> <span class="redfont">*</span>:</label>
			<div class="controls">
				<input class="form-control required" type="text" name="postcode" id="postcode" value="" />
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="mobile"><?php echo JText::_('COM_JBLANCE_CONTACT_NUMBER'); ?> :</label>
			<div class="controls">
				<input class="form-control" type="text" name="mobile" id="mobile" value="" />
			</div>
		</div>
	</fieldset>
	
	<!-- Show the following profile fields only for JoomBri Profile -->
	<?php 
	$joombriProfile = false;
	$profileInteg = JblanceHelper::getProfile();
	$profileUrl = $profileInteg->getEditURL();
	if($profileInteg instanceof JoombriProfileJoombri){
		$joombriProfile = true;
	}
	
	if($joombriProfile){
		if(empty($this->fields)){
		echo '<p class="jbbox-warning">'.JText::_('COM_JBLANCE_NO_PROFILE_FIELD_ASSIGNED_FOR_USERGROUP').'</p>';
		}
		$fields = JblanceHelper::get('helper.fields');		// create an instance of the class fieldsHelper
		
		$parents = $children = array();
		//isolate parent and childr
		foreach($this->fields as $ct){
			if($ct->parent == 0)
				$parents[] = $ct;
			else
				$children[] = $ct;
		}
		
		if(count($parents)){
			foreach($parents as $pt){ ?>
        <fieldset class="col-md-12">
			<legend><?php echo JText::_($pt->field_title); ?></legend>
			<?php
			foreach($children as $ct){
				if($ct->parent == $pt->id){ ?>
				<div class="form-group">
					<?php
					$labelsuffix = '';
					if($ct->field_type == 'Checkbox') $labelsuffix = '[]'; //added to validate checkbox
					?>
					<label class="control-label" for="custom_field_<?php echo $ct->id.$labelsuffix; ?>"><?php echo JText::_($ct->field_title); ?> <span class="redfont"><?php echo ($ct->required)? '*' : ''; ?></span>:</label>
					<div class="controls controls-row">
						<?php $fields->getFieldHTML($ct); ?>
					</div>
				</div>
			<?php
				}
			} ?>
		</fieldset>
			<?php
			}
		}
	}
	?>
        <div class="row"></div>
        <div class="form-actions" style="margin-top: 10px;">
		<input type="submit" value="<?php echo JText::_('COM_JBLANCE_SAVE'); ?>" class="btn btn-primary btn-block" />
	
	</div>
	<input type="hidden" name="option" value="com_jblance">
	<input type="hidden" name="task" value="guest.saveusernew">
	<?php echo JHtml::_('form.token'); ?>
</form>	
    </div>
    </div>