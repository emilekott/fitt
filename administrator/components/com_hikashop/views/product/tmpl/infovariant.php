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
			<table class="admintable" width="100%">
				<tr>
					<td class="key">
						<label for="product_code">
							<?php echo JText::_( 'PRODUCT_CODE' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[product][product_code]" value="<?php echo $this->escape(@$this->element->product_code); ?>" />
					</td>
				</tr>
				<?php 
					if(!empty($this->element->characteristics)){
						foreach($this->element->characteristics as $characteristic){
							?>
							<tr>
								<td class="key">
									<label>
										<?php echo $characteristic->characteristic_value; ?>
									</label>
								</td>
								<td>
									<?php echo $this->characteristicHelper->display('characteristic['.$characteristic->characteristic_id.']',(int)@$characteristic->default_id,@$characteristic->values);?>
								</td>
							</tr>
							<?php 
						}
					}
					$this->setLayout('common');
					echo $this->loadTemplate();
				?>
			</table>
			<?php
				if(@$this->variant){
					echo '<input type="hidden" name="variant" value="1" />';
					echo '<input type="hidden" name="parent_id" value="'.$this->element->product_parent_id.'" />';
				} 
			?>
			<input type="hidden" name="data[product][product_tax_id]" value="<?php echo $this->element->product_tax_id; ?>" />
			<input type="hidden" name="data[product][product_type]" value="variant" />
			<input type="hidden" name="data[product][product_parent_id]" value="<?php echo $this->element->product_parent_id; ?>" />