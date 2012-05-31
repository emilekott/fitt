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
						<label for="data[shipping][shipping_published]">
							<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
						</label>
					</td>
					<td>
						<input type="hidden" name="subtask" value="<?php echo JRequest::getCmd('subtask','');?>"/>
						<?php echo JHTML::_('select.booleanlist', "data[shipping][shipping_published]" , '',@$this->element->shipping_published	); ?>
					</td>
				</tr>
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
						<label for="data[shipping][shipping_price]">
							<?php echo JText::_( 'PRICE' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_price]" value="<?php echo @$this->element->shipping_price; ?>" /><?php echo $this->data['currency']->display('data[shipping][shipping_currency_id]',@$this->element->shipping_currency_id); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_price][shipping_percentage]">
							<?php echo JText::_( 'DISCOUNT_PERCENT_AMOUNT' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_percentage]" value="<?php echo (float)@$this->element->shipping_params->shipping_percentage; ?>" />%
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_min_price]">
							<?php echo JText::_( 'SHIPPING_MIN_PRICE' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_min_price]" value="<?php echo @$this->element->shipping_params->shipping_min_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_max_price]">
							<?php echo JText::_( 'SHIPPING_MAX_PRICE' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_max_price]" value="<?php echo @$this->element->shipping_params->shipping_max_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_virtual_included]">
							<?php echo JText::_( 'INCLUDE_VIRTUAL_PRODUCTS_PRICE' ); ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[shipping][shipping_params][shipping_virtual_included]" , '',@$this->element->shipping_params->shipping_virtual_included	); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_min_weight]">
							<?php echo JText::_( 'SHIPPING_MIN_WEIGHT' ); ?>
						</label><br/>
						<label for="data[shipping][shipping_params][shipping_max_weight]">
							<?php echo JText::_( 'SHIPPING_MAX_WEIGHT' ); ?>
						</label>
					</td>
					<td>
						<div style="float:left;">
								<input type="text" name="data[shipping][shipping_params][shipping_min_weight]" value="<?php echo @$this->element->shipping_params->shipping_min_weight; ?>"/>
								<br/>
								<input type="text" name="data[shipping][shipping_params][shipping_max_weight]" value="<?php echo @$this->element->shipping_params->shipping_max_weight; ?>"/>
						</div>
						<div style="float:left;">
									<?php echo $this->data['weight']->display('data[shipping][shipping_params][shipping_weight_unit]',@$this->element->shipping_params->shipping_weight_unit); ?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_zip_prefix]">
							<?php echo JText::_( 'SHIPPING_PREFIX' ); ?>
						</label><br/>
						<label for="data[shipping][shipping_params][shipping_min_zip]">
							<?php echo JText::_( 'SHIPPING_MIN_ZIP' ); ?>
						</label><br/>
						<label for="data[shipping][shipping_params][shipping_max_zip]">
							<?php echo JText::_( 'SHIPPING_MAX_ZIP' ); ?>
						</label><br/>
						<label for="data[shipping][shipping_params][shipping_zip_suffix]">
							<?php echo JText::_( 'SHIPPING_SUFFIX' ); ?>
						</label>
					</td>
					<td>
						<div style="float:left;">
								<input type="text" name="data[shipping][shipping_params][shipping_zip_prefix]" value="<?php echo @$this->element->shipping_params->shipping_zip_prefix; ?>"/>
								<br/>
								<input type="text" name="data[shipping][shipping_params][shipping_min_zip]" value="<?php echo @$this->element->shipping_params->shipping_min_zip; ?>"/>
								<br/>
								<input type="text" name="data[shipping][shipping_params][shipping_max_zip]" value="<?php echo @$this->element->shipping_params->shipping_max_zip; ?>"/>
								<br/>
								<input type="text" name="data[shipping][shipping_params][shipping_zip_suffix]" value="<?php echo @$this->element->shipping_params->shipping_zip_suffix; ?>"/>
						</div>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_min_volume]">
							<?php echo JText::_( 'SHIPPING_MIN_VOLUME' ); ?>
						</label><br/>
						<label for="data[shipping][shipping_params][shipping_max_volume]">
							<?php echo JText::_( 'SHIPPING_MAX_VOLUME' ); ?>
						</label>
					</td>
					<td>
						<div style="float:left;">
							<input type="text" name="data[shipping][shipping_params][shipping_min_volume]" value="<?php echo @$this->element->shipping_params->shipping_min_volume; ?>"/>
							<br/>
							<input type="text" name="data[shipping][shipping_params][shipping_max_volume]" value="<?php echo @$this->element->shipping_params->shipping_max_volume; ?>"/>
						</div>
						<div style="float:left;">
							<?php echo $this->data['volume']->display('data[shipping][shipping_params][shipping_size_unit]',@$this->element->shipping_params->shipping_size_unit); ?>
						</div>
					</td>
				</tr>