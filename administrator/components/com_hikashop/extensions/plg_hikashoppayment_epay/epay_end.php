<?php
/**
 * @package		HikaShop for Joomla!
 * @version		1.5.5
 * @author		hikashop.com
 * @copyright	(C) 2010-2011 HIKARI SOFTWARE. All rights reserved.
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<script type="text/javascript" src="http://www.epay.dk/js/standardwindow.js"></script>
<div class="hikashop_epay_end" id="hikashop_epay_end">
	<span id="hikashop_epay_end_message" class="hikashop_epay_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$method->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_epay_end_spinner" class="hikashop_epay_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
			<br /><br />
		<?php echo JText::_('POPUP_BLOCKER_MESSAGE_EPAY'); ?>
	<br/><br/>
	<form action="https://ssl.ditonlinebetalingssystem.dk/popup/default.asp" method="post" name="ePay" target="ePay_window" id="ePay">
		<div id="hikashop_epay_end_image" class="hikashop_epay_end_image">
			<input id="hikashop_epay_button" type="button" onClick="open_ePay_window()" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
		<?php
			foreach( $vars as $name => $value ) {
				echo '<input type="hidden" name="'.$name.'" value="'.((string)$value).'" />';
			}
			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration("window.addEvent('domready', function() {open_ePay_window();});");
			JRequest::setVar('noform',1);
		?>
	</form>
</div>
