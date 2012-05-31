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
<div class="hikashop_cardsave_threedsecure" id="hikashop_cardsave_threedsecure">
	<span id="hikashop_cardsave_threedsecure_message" class="hikashop_cardsave_threedsecure_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$method->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_cardsave_threedsecure_spinner" class="hikashop_cardsave_threedsecure_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br/>
	<form id="hikashop_cardsave_form" name="hikashop_cardsave_form" action="<?php echo $vars['url']; ?>" method="post">
		<div id="hikashop_cardsave_threedsecure_image" class="hikashop_cardsave_threedsecure_image">
			<input id="hikashop_cardsave_button" type="submit" value="<?php echo JText::_('VALIDATE CARD');?>" name="" alt="<?php echo JText::_('VALIDATE CARD');?>" />
		</div>
		<input type="hidden" name="PaReq" value="<?php echo htmlspecialchars((string)$vars['req']); ?>" />
		<input type="hidden" name="MD" value="<?php echo htmlspecialchars((string)$vars['ref']); ?>" />
		<input type="hidden" name="TermUrl" value="<?php echo htmlspecialchars((string)$vars['ret']); ?>" />
		<?php
			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration("window.addEvent('domready', function() {document.getElementById('hikashop_cardsave_form').submit();});");
			JRequest::setVar('noform',1);
		?>
	</form>
</div>
