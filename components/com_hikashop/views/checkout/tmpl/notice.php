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
<script type="text/javascript">
setTimeout( 'try{	window.top.document.getElementById(\'sbox-window\').close(); }catch(err){ window.top.SqueezeBox.close(); }', <?php echo (int)$this->config->get('popup_display_time',2000);?> );
</script>
<div id="hikashop_notice_box_content" class="hikashop_notice_box_content" >
	<div id="hikashop_notice_box_message" >
		<?php echo hikashop_display(JText::_('PRODUCT_SUCCESSFULLY_ADDED_TO_CART'),'success',true); ?>
	</div>
	<br />
	<div id="hikashop_add_to_cart_continue_div">
		<?php echo $this->cartClass->displayButton(JText::_('CONTINUE_SHOPPING'),'continue_shopping',$this->params,'','try{	window.top.document.getElementById(\'sbox-window\').close(); }catch(err){ window.top.SqueezeBox.close(); } return false;','id="hikashop_add_to_cart_continue_button"'); ?>
	</div>
	<div id="hikashop_add_to_cart_checkout_div">
		<?php echo $this->cartClass->displayButton(JText::_('PROCEED_TO_CHECKOUT'),'to_checkout',$this->params,hikashop_completeLink('checkout'.$this->url_itemid),'window.top.location=\''.hikashop_completeLink('checkout'.$this->url_itemid).'\';return false;','id="hikashop_add_to_cart_checkout_button"'); ?>
	</div>
</div>
