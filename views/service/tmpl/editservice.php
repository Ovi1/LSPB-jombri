<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	04 November 2014
 * @file name	:	views/service/tmpl/editservice.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	List of services provided by users (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', '#id_category');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/dropzone.js");
$doc->addScript("components/com_jblance/js/utility.js");
$doc->addScript("components/com_jblance/js/autosize.js");

$select = JblanceHelper::get('helper.select');  // create an instance of the class SelectHelper
$user = JFactory::getUser();
$config = JblanceHelper::getConfig();
$currencysym = $config->currencySymbol;
$currencycod = $config->currencyCode;
$minBasePrice = $config->minServiceBasePrice;
$reviewServices = $config->reviewServices;

$row = $this->row;
$isNew = ($row->id == 0) ? true : false;
$title = $isNew ? JText::_('COM_JBLANCE_ADD_SERVICE') : JText::_('COM_JBLANCE_EDIT_SERVICE');

$attachments = JBMediaHelper::processAttachment($row->attachment, 'service', false);
$registry = new JRegistry();
$registry->loadArray($attachments);
$mockFile = $registry->toString();

//get the service charge and fees based on the plan
$plan = JblanceHelper::whichPlan($user->id);
$chargePerService = $plan->flChargePerService;
$serviceFee = $plan->flFeePercentPerService;

JblanceHelper::setJoomBriToken();
?>

<script type="text/javascript">
<!--
    function validateForm(f) {

        var valid = document.formvalidator.isValid(f);
        var filecount = jQuery("input[name='serviceFiles[]']").length;
        var minBasePrice = parseInt('<?php echo $minBasePrice; ?>');

        //validate price
        if (jQuery("#price").val() < minBasePrice) {
            alert('<?php echo JText::sprintf('COM_JBLANCE_MINIMUM_SERVICE_BASE_PRICE_IS', JblanceHelper::formatCurrency($minBasePrice), array('jsSafe' => true)); ?>');
            jQuery("#price").focus();
            return false;
        }
        //validate duration
        if (jQuery("#duration").val() <= 0) {
            alert('<?php echo JText::_('COM_JBLANCE_ENTER_VALUE_GREATER_THAN_EQUAL_TO_ONE', true); ?>');
            jQuery("#duration").focus();
            return false;
        }
        //validate file
        if (filecount == 0) {
            alert('<?php echo JText::_('COM_JBLANCE_MUST_UPLOAD_FILE_BEFORE_SAVING', true); ?>');
            return false;
        }
        //validate fast delivery duration
        if (jQuery("#extra-fast-enabled:checked").length) {
            if (parseInt(jQuery("#extra-fast-duration").val()) >= parseInt(jQuery("#duration").val())) {
                alert('<?php echo JText::_('COM_JBLANCE_FAST_DELIVERY_CANNOT_GREATER_THAN_EQUAL_TO_BASE_DURATION', true); ?>');
                jQuery("#extra-fast-duration").focus();
                return false;
            }
        }

        if (valid == true) {
            jQuery("#submitbtn").prop("disabled", true);
            jQuery("#submitbtn").val('<?php echo JText::_('COM_JBLANCE_SAVING', true); ?>');

        } else {
            var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
            alert(msg);
            return false;
        }
        return true;
    }

    jQuery(document).ready(function ($) {
        $("input.service-extra-checkbox").on("click", processExtraFields);
        $("input.service-extra-checkbox").triggerHandler("click");
    });

    var processExtraFields = function () {
        var row = jQuery(this).data("extra-row");	//get the row name or number

        if (this.checked)
            jQuery("input[type='text'][data-extra-row='" + row + "']").addClass("required").prop("required", "required");
        else
            jQuery("input[type='text'][data-extra-row='" + row + "']").removeClass("required").removeProp("required");

    }

    jQuery(document).ready(function ($) {
        createDropzone("#drop-zone", '<?php echo $mockFile; ?>', 'service');

        autosize($("#description"));
    });
//-->
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormProject" id="userFormProject" class="form-validate" onsubmit="return validateForm(this);" enctype="multipart/form-data" novalidate>
    <div class="jbl_h3title"><?php echo $title; ?></div>
    <div class="panel panel-default">
        <div class="panel-body">
                <div class="col-md-6">
            <fieldset>
                    <div class="panel panel-primary">
                        <div class="panel-heading"><legend><?php echo JText::_('COM_JBLANCE_SERVICE_DETAILS'); ?></legend></div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="control-label" for="service_title"><?php echo JText::_('COM_JBLANCE_SERVICE_TITLE'); ?> :</label>
                                <input type="text" class="form-control required hasTooltip" name="service_title" id="service_title" title="<?php echo JHtml::tooltipText(JText::_('COM_JBLANCE_TT_SERVICE_TITLE')); ?>" value="<?php echo $row->service_title; ?>" />
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="id_category"><?php echo JText::_('COM_JBLANCE_SKILLS'); ?> :</label>
                                <div class="input-group">
                                    <?php
                                    $attribs = "class='form-control required' size='5' MULTIPLE";
                                    echo $select->getSelectCategoryTree('id_category[]', explode(',', $this->row->id_category), '', $attribs, '', true);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="description"><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?> :</label>

                                <textarea name="description" id="description" class="form-control required hasTooltip" rows="5" 
                                          title="<?php echo JHtml::tooltipText(JText::_('COM_JBLANCE_TT_SERVICE_DESCTIPTION')); ?>">
                                              <?php echo $row->description; ?>
                                </textarea>

                            </div>
                        </div>
                    </div>
            </fieldset>
                </div>

                <div class="col-md-6">
            <fieldset>
                    <div class="panel panel-primary">
                        <div class="panel-heading"><legend><?php echo JText::_('COM_JBLANCE_PRICE_AND_DURATION'); ?></legend>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="control-label" for="price"><?php echo JText::_('COM_JBLANCE_I_WILL_DO_FOR'); ?> :</label>
                                <div class="input-group">

                                    <span class="input-group-addon"><?php echo $currencysym; ?></span>
                                    <input class="form-control required validate-numeric" type="text" name="price" id="price" value="<?php echo $row->price; ?>" />

                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="duration"><?php echo JText::_('COM_JBLANCE_I_WILL_DO_IN'); ?> :</label>
                                <div class="input-group">

                                    <input class="form-control required validate-numeric" type="text" name="duration" id="duration" value="<?php echo $row->duration; ?>" />
                                    <span class="input-group-addon"><?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?></span>

                                </div>
                            </div>
                        </div>
                    </div>
            </fieldset>
                </div>
            <div class="row"></div>
            <fieldset>
                <div class="panel panel-default">
                    <div class="panel-heading"><legend><?php echo JText::_('COM_JBLANCE_SERVICE_IMAGES'); ?></legend>
                    </div>
                    <div class="panel-body">   
                        <div class="upload-dropzone" id="drop-zone">
                            <?php echo JText::_('COM_JBLANCE_DRAG_DROP_FILES_HERE'); ?>
                        </div>
                        <div id="actions">
                            <div class="text-center">
                                <div class="btn-group" role="group">
                                    <!-- The fileinput-button span is used to style the file input field as button -->
                                    <button type="button" class="btn btn-success fileinput-button dz-clickable"> 
                                        <i class="material-icons">add</i><?php echo JText::_('COM_JBLANCE_ADD_FILES'); ?>
                                    </button>
                                    <button type="button" class="btn btn-primary start">
                                        <i class="material-icons">file_upload</i><?php echo JText::_('COM_JBLANCE_START_UPLOAD'); ?>
                                    </button>
                                    <button type="reset" class="btn btn-danger cancel">
                                        <i class="material-icons">cancel</i><?php echo JText::_('COM_JBLANCE_CANCEL_UPLOAD'); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- The global file processing state -->
                                    <span class="fileupload-process">
                                        <div class="progress">
                                            <div id="total-progress" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                                <div class="bar bar-success" style="width: 0%;" data-dz-uploadprogress=""></div>
                                            </div>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="table table-striped" class="files" id="previews">
                            <div id="template" class="file-row">
                                <!-- This is used as the file preview template -->
                                <div>
                                    <span class="preview"><img class="img-polaroid" data-dz-thumbnail /> </span>
                                </div>
                                <div>
                                    <p class="name" data-dz-name></p>
                                    <strong class="error text-danger" data-dz-errormessage></strong>
                                </div>
                                <div>
                                    <p class="size" data-dz-size></p>
                                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                        <div class="bar bar-success" style="width: 0%;" data-dz-uploadprogress></div>
                                    </div>
                                </div>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-primary start">
                                        <i class="material-icons">file_upload</i> <span><?php echo JText::_('COM_JBLANCE_START_UPLOAD'); ?></span>
                                    </button>
                                    <button data-dz-remove class="btn btn-warning cancel">
                                        <i class="material-icons">cancel</i> <span><?php echo JText::_('COM_JBLANCE_CANCEL'); ?></span>
                                    </button>
                                    <button data-dz-remove class="btn btn-danger delete">
                                        <i class="material-icons">delete</i><span><?php echo JText::_('COM_JBLANCE_DELETE'); ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        </fieldset>

                        <fieldset>
                            <div class="panel panel-default">
                                <div class="panel-heading"><legend><?php echo JText::_('COM_JBLANCE_INSTRUCTIONS_TO_BUYERS'); ?></legend></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="control-label" for="description"><?php echo JText::_('COM_JBLANCE_DESCRIPTION'); ?> :</label> 

                                        <textarea name="instruction" id="instruction" class="form-control hasTooltip" rows="10" title="<?php echo JHtml::tooltipText(JText::_('COM_JBLANCE_TT_SERVICE_INSTRUCTION')); ?>"><?php echo $row->instruction; ?></textarea>

                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="panel panel-success">
                                <div class="panel-heading"><legend><?php echo JText::_('COM_JBLANCE_ADD_ONS'); ?></legend></div>
                                <div class="panel-body">

                                    <?php
                                    $options = 3;
                                    $registry = new JRegistry;
                                    $registry->loadString($row->extras);
                                    $extras = $registry->toObject();

                                    //if is set, then set the value else initialise
                                    if (!isset($extras->fast)) {
                                        $checked = '';
                                        $price = '';
                                        $duration = '';
                                    } else {
                                        $checked = ($extras->fast->enabled) ? 'checked' : '';
                                        $price = $extras->fast->price;
                                        $duration = $extras->fast->duration;
                                    }
                                    ?>
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="hidden" name="extras[fast][enabled]" value="0" /> <!-- this is added when checkbox is not checked -->
                                                    <input type="checkbox" id="extra-fast-enabled" name="extras[fast][enabled]" class="service-extra-checkbox" value="1" <?php echo $checked; ?> data-extra-row="fast" /> 
                                                    <span class="label label-success"><?php echo JText::_('COM_JBLANCE_FAST_DELIVERY'); ?></span> 
                                                    <?php echo JText::_('COM_JBLANCE_FAST_DELIVERY_DESC'); ?>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><?php echo $currencysym; ?></span>
                                                    <input class="form-control validate-numeric" placeholder="<?php echo JText::_('COM_JBLANCE_FOR'); ?>" type="text" name="extras[fast][price]" id="extra-fast-price" value="<?php echo $price; ?>" data-extra-row="fast" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <input class="form-control validate-numeric" type="text" name="extras[fast][duration]" placeholder="<?php echo JText::_('COM_JBLANCE_IN'); ?>" id="extra-fast-duration" value="<?php echo $duration; ?>" data-extra-row="fast" />
                                                    <span class="input-group-addon"><?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?></span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <?php
                                    for ($i = 0; $i < $options; $i++) {
                                        if (!isset($extras->$i)) {
                                            $checked = '';
                                            $description = '';
                                            $price = '';
                                            $duration = '';
                                        } else {
                                            $checked = ($extras->$i->enabled) ? 'checked' : '';
                                            $description = $extras->$i->description;
                                            $price = $extras->$i->price;
                                            $duration = $extras->$i->duration;
                                        }
                                        ?>
                                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                                            <div class="row">
                                                <div class="well">
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">
                                                                <label>
                                                                    <input type="hidden" name="extras[<?php echo $i; ?>][enabled]" value="0" /> <!-- this is added when checkbox is not checked -->
                                                                    <input type="checkbox" id="extra-<?php echo $i; ?>-enabled" name="extras[<?php echo $i; ?>][enabled]" class="service-extra-checkbox" value="1" <?php echo $checked; ?> data-extra-row="<?php echo $i; ?>" /> 
                                                                </label>
                                                            </span>
                                                            <input type="text" class="extra-description form-control" name="extras[<?php echo $i; ?>][description]" id="extra-<?php echo $i; ?>-desc" placeholder="<?php echo JText::_('COM_JBLANCE_I_WILL'); ?>" value="<?php echo $description; ?>" data-extra-row="<?php echo $i; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><?php echo $currencysym; ?></span>
                                                            <input class="form-control validate-numeric" type="text" name="extras[<?php echo $i; ?>][price]" id="extra-<?php echo $i; ?>-price" value="<?php echo $price; ?>" data-extra-row="<?php echo $i; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <input class="form-control validate-numeric" placeholder="<?php echo JText::_('COM_JBLANCE_IN'); ?>" type="text" name="extras[<?php echo $i; ?>][duration]" id="extra-<?php echo $i; ?>-duration" value="<?php echo $duration; ?>" data-extra-row="<?php echo $i; ?>" />
                                                            <span class="input-group-addon"><?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php }
                                    ?>
                                </div>
                            </div>
                        </fieldset>


                <?php if ($chargePerService > 0 || $serviceFee > 0) { ?>
                    <div class="alert alert-info">
                        <h4><?php echo JText::_('COM_JBLANCE_CHARGES'); ?></h4>
                        <ul>
                            <?php if ($chargePerService > 0) : ?>
                                <li>
                                    <?php echo JText::sprintf('COM_JBLANCE_CHARGE_PER_SERVICE_INFO', JblanceHelper::formatCurrency($chargePerService)); ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($serviceFee > 0) : ?>
                                <li>
                                    <?php echo JText::sprintf('COM_JBLANCE_SERVICE_FEE_INFO', $serviceFee); ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php } ?>

                <?php if ($reviewServices) { ?>
                    <div class="alert alert-block">
                        <h3><?php echo JText::_('COM_JBLANCE_APPROVAL_NOTICE'); ?></h3>
                        <?php echo JText::_('COM_JBLANCE_SERVICE_WILL_BE_REVIEWED_BY_ADMIN_BEFORE_PUBLISH'); ?>
                    </div>
                <?php } ?>

                <div class="form-actions">
                    <input type="submit" value="<?php echo JText::_('COM_JBLANCE_SAVE_SERVICE'); ?>" class="btn btn-primary" id="submitbtn" /> 
                    <input type="button" value="<?php echo JText::_('COM_JBLANCE_CANCEL'); ?>" onclick="javascript:history.back();" class="btn btn-default" />
                </div>

                <input type="hidden" name="option" value="com_jblance" /> 
                <input type="hidden" name="task" value="service.saveservice" /> 
                <input type="hidden" name="id" value="<?php echo $row->id; ?>" />
                <?php echo JHtml::_('form.token'); ?>
                    </div>
                </div>
        </div>
    </div>
</form>