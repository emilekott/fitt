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
<div class="hikashop_hsbc_end" id="hikashop_hsbc_end">
	<span id="hikashop_hsbc_end_message" class="hikashop_hsbc_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$method->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_hsbc_end_spinner" class="hikashop_hsbc_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br/>
	<form id="hikashop_hsbc_form" name="hikashop_hsbc_form" action="https://www.cpi.hsbc.com/servlet" method="post">
		<div id="hikashop_hsbc_end_image" class="hikashop_hsbc_end_image">
			<input id="hikashop_hsbc_button" type="submit" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
		<?php
			foreach( $vars as $name => $value ) {
				echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" />';
			}
			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration("window.addEvent('domready', function() {document.getElementById('hikashop_hsbc_form').submit();});");
			JRequest::setVar('noform',1);
		?>
	</form>
</div>