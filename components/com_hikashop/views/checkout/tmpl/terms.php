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
<div id="hikashop_checkout_terms" class="hikashop_checkout_terms">
	<input class="hikashop_checkout_terms_checkbox" id="hikashop_checkout_terms_checkbox" type="checkbox" name="hikashop_checkout_terms" value="1" <?php echo $this->terms_checked; ?> />
	<?php
		$text = JText::_('PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER');
		$terms_article = $this->config->get('checkout_terms');
		if(!empty($terms_article)){
			JHTML::_('behavior.modal');
			$text = '<a href="'.JRoute::_('index.php?option=com_content&view=article&id='.$terms_article.'&tmpl=component').'" class="modal" rel="{handler: \'iframe\', size: {x: 450, y: 480}}" target="_blank">'.$text.'</a>';
		}
	?>
	<label for="hikashop_checkout_terms_checkbox"><?php echo $text; ?></label>
</div>