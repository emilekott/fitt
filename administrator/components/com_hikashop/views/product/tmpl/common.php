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
						<label for="product_quantity">
							<?php echo JText::_( 'PRODUCT_QUANTITY' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[product][product_quantity]" <?php echo is_numeric(@$this->element->product_max_per_order)? '' : 'onfocus="if(isNaN(parseInt(this.value))) this.value=\'\';"'; ?> value="<?php echo @$this->element->product_quantity; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[product][product_min_per_order]">
							<?php echo JText::_( 'PRODUCT_MIN_QUANTITY_PER_ORDER' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[product][product_min_per_order]" value="<?php echo (int)@$this->element->product_min_per_order; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[product][product_max_per_order]">
							<?php echo JText::_( 'PRODUCT_MAX_QUANTITY_PER_ORDER' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[product][product_max_per_order]" <?php echo is_numeric(@$this->element->product_max_per_order)? '' : 'onfocus="if(isNaN(parseInt(this.value))) this.value=\'\';"'; ?> value="<?php echo @$this->element->product_max_per_order; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="product_weight">
							<?php echo JText::_( 'PRODUCT_WEIGHT' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[product][product_weight]" value="<?php echo @$this->element->product_weight; ?>"/><?php echo $this->weight->display('data[product][product_weight_unit]',@$this->element->product_weight_unit); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_( 'PRODUCT_VOLUME' ); ?>
					</td>
					<td>
						<table>
							<tr>
								<td>
									<?php echo JText::_( 'PRODUCT_LENGTH' ); ?>
								</td>
								<td>
									<input size="10" type="text" name="data[product][product_length]" value="<?php echo @$this->element->product_length; ?>"/>
								</td>
								<td>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo JText::_( 'PRODUCT_WIDTH' ); ?>
								</td>
								<td>
									<input size="10" type="text" name="data[product][product_width]" value="<?php echo @$this->element->product_width; ?>"/>
								</td>
								<td>
									<?php echo $this->volume->display('data[product][product_dimension_unit]',@$this->element->product_dimension_unit); ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo JText::_( 'PRODUCT_HEIGHT' ); ?>
								</td>
								<td>
									<input size="10" type="text" name="data[product][product_height]" value="<?php echo @$this->element->product_height; ?>"/>
								</td>
								<td>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="product_published">
							<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[product][product_published]" , '',@$this->element->product_published	); ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<fieldset>
						<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
						<?php
						if(hikashop_level(2)){
							$acltype = hikashop_get('type.acl');
							echo $acltype->display('product_access',@$this->element->product_access,'product');
						}else{
							echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
						} ?>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<?php
						JPluginHelper::importPlugin( 'hikashop' );
						$dispatcher =& JDispatcher::getInstance();
						$html = array();
						$dispatcher->trigger( 'onProductDisplay', array( & $this->element, & $html ) );
						if(!empty($html)){
							foreach($html as $h){
								echo $h;
							}
						}
						?>
					</td>
				</tr>
