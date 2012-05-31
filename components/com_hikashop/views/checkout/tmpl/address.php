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
<?php
if($this->identified){
?>
<div <?php
	if($this->has_shipping){
		echo 'id="hikashop_checkout_address" class="hikashop_checkout_address"';
	}else{
		echo 'id="hikashop_checkout_address_billing_only" class="hikashop_checkout_address_billing_only"';
	} ?>>
	<div <?php
		if($this->has_shipping){
			echo 'id="hikashop_checkout_address_left_part" class="hikashop_checkout_address_left_part"';
		}else{
			echo 'id="hikashop_checkout_billing_address" class="hikashop_checkout_billing_address"';
		} ?>>
		<fieldset>
			<legend><?php echo JText::_('HIKASHOP_BILLING_ADDRESS');?></legend>
	<?php
			$this->type = 'billing';
			echo $this->loadTemplate('view');
			if($this->has_shipping){
	?>
		</fieldset>
	</div>
	<div id="hikashop_checkout_address_right_part" class="hikashop_checkout_address_right_part">
		<fieldset>
			<legend><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS');?></legend>
			<?php
			$checked = '';
			$style = '';
			if($this->shipping_address==$this->billing_address){
				$checked = 'checked="checked" ';
				$style = ' style="display:none"';
			}?>
			<input class="hikashop_checkout_shipping_same_address inputbox" <?php echo $checked; ?>type="checkbox" id="same_address" name="same_address" value="yes" alt="Same address" onclick="return hikashopSameAddress(this.checked);" />
			<label for="same_address"><?php echo JText::_('SAME_AS_BILLING');?></label>
			<div class="hikashop_checkout_shipping_div" id="hikashop_checkout_shipping_div" <?php echo $style;?>>
			<?php
				$this->type = 'shipping';
				echo $this->loadTemplate('view');
			?>
			</div>
	<?php } ?>
		</fieldset>
	</div>
</div>
<div style="clear:both"></div>
<?php
}else{
}
