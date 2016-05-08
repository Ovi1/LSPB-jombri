<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	05 November 2014
 * @file name	:	views/service/tmpl/viewservice.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	List of services provided by users (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('bootstrap.framework');
JHtml::_('bootstrap.carousel');

$user = JFactory::getUser();
$model = $this->getModel();
$config = JblanceHelper::getConfig();
$currencysym = $config->currencySymbol;
$showUsername = $config->showUsername;
$enableAddThis = $config->enableAddThis;
$addThisPubid = $config->addThisPubid;

$row = $this->row;
$nameOrUsername = ($showUsername) ? 'username' : 'name';
$isMine = ($row->user_id == $user->id);
$sellerInfo = JFactory::getUser($row->user_id);

$userType = JblanceHelper::getUserType($user->id);

$link_edit = JRoute::_('index.php?option=com_jblance&view=service&layout=editservice&id=' . $row->id);
?>

<script type="text/javascript">
<!--

    jQuery(document).ready(function ($) {
        $("input.service-extra-checkbox").on("click", updateOrderAmount);
    });

    var updateOrderAmount = function (e) {
        var finalTotal = parseFloat(jQuery("#finaltotal").val());
        var finalDuration = parseFloat(jQuery("#finalduration").val());
        var baseDuration = parseFloat(jQuery("#finalduration").data("base-duration"));
        if (this.checked) {
            //if fast is checked, set the base duration should be changed to fast duration
            finalTotal = finalTotal + parseFloat(jQuery(this).data("price"));

            if (jQuery(this).prop("name") == "extras[fast]") {
                finalDuration = finalDuration + parseFloat(jQuery(this).data("duration")) - baseDuration;
            } else {
                finalDuration = finalDuration + parseFloat(jQuery(this).data("duration"));
            }
        } else {
            finalTotal = finalTotal - parseFloat(jQuery(this).data("price"));

            if (jQuery(this).prop("name") == "extras[fast]") {
                finalDuration = finalDuration - parseFloat(jQuery(this).data("duration")) + baseDuration;
            } else {
                finalDuration = finalDuration - parseFloat(jQuery(this).data("duration"));
            }
        }

        jQuery(".sp-service-order").html(finalTotal);
        jQuery(".sp-service-duration").html(finalDuration);
        jQuery("#finaltotal").val(finalTotal);
        jQuery("#finalduration").val(finalDuration);
    };
//-->
</script>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3><?php echo $row->service_title; ?>
            <!-- show Edit Service only to seller -->
            <?php if ($isMine) : ?>
                <div class="pull-right">
                    <a class="btn btn-primary" href="<?php echo $link_edit; ?>"><i class="material-icons">edit</i> <?php echo JText::_('COM_JBLANCE_EDIT_SERVICE'); ?></a>
                </div>
            <?php endif; ?>
        </h3>
    </div>
    <div class="panel-body">
        <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormProject" id="userFormProject" class="form-validate" enctype="multipart/form-data">
            <div class="clearfix"></div>

            <div class="row">
                <div class="col-md-4">

                    <div class="thumbnail">
                        <?php
                        $attrib = 'width=128px height=128px class="img-responsive"';
                        $avatar = JblanceHelper::getLogo($row->user_id, $attrib);
                        echo!empty($avatar) ? LinkHelper::GetProfileLink($row->user_id, $avatar, '', '', '') : '&nbsp;';
                        ?>
                        <?php echo LinkHelper::GetProfileLink($row->user_id, $this->escape($sellerInfo->$nameOrUsername)); ?>
<?php JblanceHelper::getAvarageRate($row->user_id); ?>
                    </div>

                    <div class="clearfix"></div>
                    <h6 class="fontupper"><?php echo JText::_('COM_JBLANCE_DELIVERY_IN'); ?> <span class="sp-service-duration"><?php echo $row->duration; ?></span> <?php echo strtolower(JText::_('COM_JBLANCE_BID_DAYS')); ?></h6>
                    <button type="submit" class="btn btn-large btn-success btn-block"><?php echo JText::_('COM_JBLANCE_ORDER_NOW'); ?> (<?php echo $currencysym; ?> <span class="sp-service-order"><?php echo $row->price; ?></span>)</button>

                    <ul class="list-inline">
                        <li><p><?php echo JText::_('COM_JBLANCE_AVERAGE_RATING'); ?></p>
                        <?php $serviceRating = JblanceHelper::getServiceRating($row->user_id, $row->id); ?>
<?php echo JblanceHelper::getRatingHTML($serviceRating); ?></li>
                        <li>						<i class="material-icons" title="<?php echo JText::_('COM_JBLANCE_SERVICES_SOLD'); ?>">shopping_cart</i> <?php echo $model->servicePurchaseCount($row->id); ?>
                        </li>

                    </ul>

                    <div class="clearfix"></div>
                    <div class="lineseparator"></div>
                    <div>
                        <h6 style="padding: 20px 0 0 0; margin-bottom: 5px; " class="fontupper font12"><?php echo JText::_('COM_JBLANCE_SIMILAR_SERVICES'); ?></h6>
                        <div><?php echo JblanceHelper::getCategoryNames($row->id_category, 'tags-link', 'service'); ?></div>
                    </div>
                </div>
                <div class="col-md-8">
<?php echo JBMediaHelper::renderImageCarousel($row->attachment, 'service'); ?>

                </div>
                <div class="col-md-12">
                    <div class="clearfix"></div>
                    <div><?php echo nl2br($row->description); ?></div>
                    <?php
                    $registry = new JRegistry;
                    $registry->loadString($row->extras);
                    $extras = $registry->toObject();

                    $a = 0;
                    foreach ($extras as $key => $value) {
                        if ($value->enabled) {
                            $a++;
                        }
                    }
                    if ($a > 0) {
                        ?>
                        <h3><?php echo JText::_('COM_JBLANCE_GET_MORE_WITH_ADD_ONS'); ?>:</h3>
                        <?php
                        foreach ($extras as $key => $value) {
                            if ($value->enabled) {
                                ?>
                                <div class="well well-small">
                                    <div class="row-fluid">
                                        <div class="span10">
                                            <label class="checkbox">
                                                <input type="checkbox" name="extras[<?php echo $key; ?>]" class="service-extra-checkbox" data-price="<?php echo $value->price; ?>" data-duration="<?php echo $value->duration; ?>" /> 
                                                <?php
                                                if ($key == 'fast') :
                                                    echo "<span class='label label-warning'>" . JText::_('COM_JBLANCE_FAST_DELIVERY') . " </span> " . JText::sprintf('COM_JBLANCE_FAST_DELIVER_ORDER_JUST_DAYS', $value->duration);
                                                    ?>
                                                <?php else : ?>
                                                    <?php echo $value->description . ' (+' . JText::plural('COM_JBLANCE_N_DAYS', $value->duration) . ')'; ?>
            <?php endif; ?>
                                            </label>
                                        </div>
                                        <div class="span2">
                                            <span class="boldfont">+ <?php echo JblanceHelper::formatCurrency($value->price); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>

                        <?php if (!empty($row->instruction)) { ?>
                        <div class="alert alert-info">
                        <?php echo nl2br($row->instruction); ?>
                        </div>
<?php } ?>

                    <!-- show the order button non-owner -->
<?php //if(!$isMine) :  ?>
                    <div class="pull-right text-center">
                            <!-- <div><?php echo JText::_('COM_JBLANCE_DELIVERY_IN'); ?> <span class="sp-service-duration"><?php echo $row->duration; ?></span> <?php echo strtolower(JText::_('COM_JBLANCE_BID_DAYS')); ?></div> -->
                        <button type="submit" class="btn btn-large btn-success"><?php echo JText::_('COM_JBLANCE_ORDER_NOW'); ?> (<?php echo $currencysym; ?> <span class="sp-service-order"><?php echo $row->price; ?></span>)</button>
                    </div>
<?php //endif;  ?>
                </div>
            </div>
            <!-- show reviews -->
<?php if (count($this->ratings)) : ?>
             
                    <div class="col-md-12">
                        <h4><?php echo JText::_('COM_JBLANCE_REVIEWS'); ?>:</h4>
                        <?php
                        for ($i = 0, $x = count($this->ratings); $i < $x; $i++) {
                            $rating = $this->ratings[$i];
                            $rate = JblanceHelper::getUserRating($rating->target, $rating->order_id, 'COM_JBLANCE_SERVICE');
                            $rateDate = JFactory::getDate($rating->rate_date);
                            ?>
                            <div class="media">
                                <div class="col-md-1 col-xs-3 col-sm-3">
                                     <?php
                                $attrib = 'width=56px; height=56px; class="img-responsive"';
                                $avatar = JblanceHelper::getLogo($rating->actor, $attrib);
                                echo!empty($avatar) ? LinkHelper::GetProfileLink($rating->actor, $avatar, '', '', ' pull-left') : '&nbsp;'
                                ?>   
                                </div>
                            
                                <div class="media-body">
                                    <span class="media-heading boldfont"><?php echo LinkHelper::GetProfileLink($rating->actor); ?></span>
                                    <span class="dis-inl-blk"><?php echo JblanceHelper::getRatingHTML($rate); ?></span>
                                    <span class="dis-inl-blk font12"><?php echo JblanceHelper::showTimePastDHM($rateDate, 'SHORT'); ?></span>
                                    <div><?php echo $rating->comments; ?></div>
                                </div>
                            </div>
                            <div class="lineseparator"></div>
                            <?php
                        }
                        ?>
                    </div>
<?php endif; ?>
            <input type="hidden" name="option" value="com_jblance" /> 
            <input type="hidden" name="task" value="service.placeorder" /> 
            <input type="hidden" name="service_id" value="<?php echo $row->id; ?>" />
            <input type="hidden" name="finaltotal" id="finaltotal" data-base-price="<?php echo $row->price; ?>" value="<?php echo $row->price; ?>" />
            <input type="hidden" name="finalduration" id="finalduration" data-base-duration="<?php echo $row->duration; ?>" value="<?php echo $row->duration; ?>" />
<?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>