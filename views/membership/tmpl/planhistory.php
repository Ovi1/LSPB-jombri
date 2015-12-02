<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	16 March 2012
 * @file name	:	views/membership/tmpl/planhistory.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows plan history subscribed by user (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('jquery.framework');
 JHtml::_('behavior.modal', 'a.jb-modal');
 
 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/simplemodal.js");
 $doc->addStyleSheet("components/com_jblance/css/simplemodal.css");

 JText::script('COM_JBLANCE_CLOSE');
 JText::script('COM_JBLANCE_YES');

 $config = JblanceHelper::getConfig();
 $dformat = $config->dateFormat;
 $currencysym = $config->currencySymbol;
 
 $link_plan_add  = JRoute::_('index.php?option=com_jblance&view=membership&layout=planadd');
 $action = JRoute::_('index.php?option=com_jblance&view=membership&layout=planhistory');
?>
<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_SUBSCR_HISTORY'); ?>
  <p class="pull-right"><a href="<?php echo $link_plan_add; ?>" <?php echo $onclick; ?> class="btn btn-primary"><i class="material-icons">shopping_cart</i> <?php echo JText::_('COM_JBLANCE_GET_NEW_SUBSCR'); ?></a></p>  
</div>
<?php
	$onclick = ''; 
	if(isset($this->rows[0])) 
	if($this->rows[0]->approved == 0){	//the recent subscr is pending approval
		$ttl = JText::_('COM_JBLANCE_CANCEL_SUBSCR', true);
		$msg = JText::_('COM_JBLANCE_PENDING_SUBSCR_CANCEL_FIRST', true);
		$onclick="onclick='javascript:modalAlert(\"$ttl\", \"$msg\");return false;'";
	}
?>
<form action="<?php echo $action; ?>" method="post" name="userFormJob" enctype="multipart/form-data">

	<?php 
	if($this->finish) echo "<p>$this->finish</p>";
	 ?>
      <?php
      if(count($this->rows) > 0){ ?>
		<div id="no-more-tables">
		<table class="table table-bordered table-hover table-striped table-condensed">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?></th>
					<th><?php echo JText::_('COM_JBLANCE_PLAN_NAME'); ?></th>
					<th class="center"><?php echo JText::_('COM_JBLANCE_STATUS'); ?></th>
					<th class="center"><?php echo JText::_('COM_JBLANCE_DAYS_LEFT'); ?></th>
					<th class="center"><?php echo JText::_('COM_JBLANCE_PRICE')." ($currencysym)"; ?></th>
					<th class="center"><?php echo JText::_('COM_JBLANCE_ACTION'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
						<div class="pagination"><?php echo $this->pageNav->getListFooter(); ?></div>
					</td>
				</tr>
			</tfoot>
			<tbody>
            <?php
            $k = 0;
            foreach ($this->rows AS $row){
				if($row->gateway == 'banktransfer')
					$link_checkout  = JRoute::_('index.php?option=com_jblance&view=membership&layout=bank_transfer&id='.$row->id.'&type=plan');
				elseif($row->gateway != 'banktransfer')
					$link_checkout  = JRoute::_('index.php?option=com_jblance&view=membership&layout=check_out&id='.$row->id.'&type=plan&repeat=1');

				$link_plandetail	= JRoute::_('index.php?option=com_jblance&view=membership&layout=plandetail&id='.$row->id);
				$link_cancelsubscr  = JRoute::_('index.php?option=com_jblance&task=membership.cancelsubscr&id='.$row->id.'&'.JSession::getFormToken().'=1');
				$link_invoice 		=  JRoute::_('index.php?option=com_jblance&view=membership&layout=invoice&id='.$row->id.'&tmpl=component&print=1&type=plan');
	            ?>
				<tr>
					<td data-title="<?php echo JText::_('COM_JBLANCE_INVOICE_NO'); ?>">
						<?php echo $row->invoiceNo; ?>
					</td>
					<td data-title="<?php echo JText::_('COM_JBLANCE_PLAN_NAME'); ?>">
						<a href="<?php echo $link_plandetail; ?>"><?php echo $row->name; ?></a>
					</td>
					<td data-title="<?php echo JText::_('COM_JBLANCE_STATUS'); ?>" class="text-center">
						<img src="components/com_jblance/images/s<?php echo $row->approved;?>.png" />
					</td>
                   <td data-title="<?php echo JText::_('COM_JBLANCE_DAYS_LEFT'); ?>" class="center">
                    	<?php echo ($row->daysleft >= 0) ? $row->daysleft : '0'; ?>
                    </td>
					<td data-title="<?php echo JText::_('COM_JBLANCE_PRICE')." ($currencysym)"; ?>" class="text-right">
						<?php echo JblanceHelper::formatCurrency($row->price, false); ?>
					</td>
					<td class="text-center">
						<div class="btn-group">
							<?php if(!$row->approved): ?>
							<a class="btn btn-small" title="<?php echo JText::_('COM_JBLANCE_CHECKOUT'); ?>" href="<?php echo $link_checkout; ?>"><i class="material-icons">shopping_cart</i> </a>
							<a class="btn btn-small" title="<?php echo JText::_('COM_JBLANCE_CANCEL_SUBSCR'); ?>" href="javascript:modalConfirm('<?php echo JText::_('COM_JBLANCE_CANCEL_SUBSCR', true); ?>', '<?php echo JText::_('COM_JBLANCE_CONFIRM_CANCEL_SUBSCR', true); ?>', '<?php echo $link_cancelsubscr; ?>');"><i class="material-icons">remove_circle</i></a>
							<?php endif; ?>
							<a class="btn btn-small jb-modal" title="<?php echo JText::_('COM_JBLANCE_PRINT_INVOICE'); ?>" href="<?php echo $link_invoice; ?>" rel="{handler: 'iframe', size: {x: 800, y: 600}}"><i class="material-icons">print</i></a>
						</div>
					</td>
                </tr>
                <?php
            $k = 1 - $k;
            }
                ?>
			</tbody>
			
		</table>
		</div>
		<div class="lineseparator"></div>
		
		<ul class="inline">
			<li><img src="components/com_jblance/images/s0.png" alt=""> <?php echo JText::_('COM_JBLANCE_APPROVAL_PENDING'); ?> | </li>
			<li><img src="components/com_jblance/images/s1.png" alt=""> <?php echo JText::_('COM_JBLANCE_APPROVED'); ?> | </li>
			<li><img src="components/com_jblance/images/s2.png" alt=""> <?php echo JText::_('COM_JBLANCE_CANCELLED'); ?> | </li>
			<li><img src="components/com_jblance/images/s3.png" alt=""> <?php echo JText::_('COM_JBLANCE_EXPIRED'); ?></li>
		</ul>
		<?php 
      }
      else 
      {
      	echo JText::_('COM_JBLANCE_NO_SUBSCR');
      }
		?>
</form>