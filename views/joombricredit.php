<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	13 March 2012
 * @file name	:	views/admproject/tmpl/editproject.php
 * @copyright   :	Copyright (C) 2012 - 2015 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows JoomBri Lance Credit (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 $config = JblanceHelper::getConfig();
?>

<div align="center">
	<?php $creditvisible = (!$config->showJoombriCredit) ? 'style=display:none' : ''; ?>
	<div class="jbl_footer" id="showcredit" <?php echo $creditvisible; ?>><img src="components/com_jblance/images/joombri16.png" border="0" width="16" align="middle"/> Powered By JoomBri Freelance - <a href="http://www.joombri.in" target="_blank">Freelance Directory Component</a></div>	
	<?php 
		$filename = JPATH_COMPONENT_ADMINISTRATOR.'/jblance.xml';
		$xml 	  = simplexml_load_file($filename);
		$version  = $xml->{'version'};
	?>
	<div id="jbversioninfo" style="display:none;"><?php echo $version; ?></div>
</div>
