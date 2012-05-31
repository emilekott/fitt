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
				<tr>
					<td class="key">
						<label for="shipping_tax_id">
							<?php echo JText::_( 'TAXATION_CATEGORY' ); ?>
						</label>
					</td>
					<td>
						<?php echo $this->data['categoryType']->display('data[shipping][shipping_tax_id]',@$this->element->shipping_tax_id,true);?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_min_price]">
							<?php echo JText::_( 'SHIPPING_MIN_PRICE' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_min_price]" value="<?php echo @$this->element->shipping_params->shipping_min_price; ?>" /><?php echo $this->data['currency']->display('data[shipping][shipping_currency_id]',@$this->element->shipping_currency_id); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][post_code]">
							<?php echo JText::_( 'POST_CODE' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][post_code]" value="<?php echo @$this->element->shipping_params->post_code; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][additional_fee]">
							<?php echo 'Additional fee'; ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][additional_fee]" value="<?php echo @$this->element->shipping_params->additional_fee; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][services]">
							<?php echo JText::_( 'SHIPPING_SERVICES' ); ?>
						</label>
					</td>
					<td>
						<input name="data[shipping][shipping_params][EXPRESS]" type="checkbox" value="EXPRESS" <?php echo (!empty($this->element->shipping_params->EXPRESS)?'checked="checked"':'');?>/>Express<br/>
						<input name="data[shipping][shipping_params][STANDARD]" type="checkbox" value="STANDARD" <?php echo (!empty($this->element->shipping_params->STANDARD)?'checked="checked"':'');?>/>Standard<br/>
						<input name="data[shipping][shipping_params][AIR]" type="checkbox" value="AIR" <?php echo (!empty($this->element->shipping_params->AIR)?'checked="checked"':'');?>/>By air<br/>
						<input name="data[shipping][shipping_params][SEA]" type="checkbox" value="SEA" <?php echo (!empty($this->element->shipping_params->SEA)?'checked="checked"':'');?>/>By sea
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][reverse_order]">
							<?php echo 'Reverse order of services'; ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[shipping][shipping_params][reverse_order]" , '',@$this->element->shipping_params->reverse_order ); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_group]">
							<?php echo 'Group products together'; ?>
						</label>
					</td>
					<td>
						<?php
						if(!isset($this->element->shipping_params->shipping_group)) $this->element->shipping_params->shipping_group = 1;
						echo JHTML::_('select.booleanlist', "data[shipping][shipping_params][shipping_group]" , '',$this->element->shipping_params->shipping_group ); ?>
					</td>
				</tr>