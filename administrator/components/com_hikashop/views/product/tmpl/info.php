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
						<label for="data[product][product_code]">
							<?php echo JText::_( 'PRODUCT_CODE' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[product][product_code]" value="<?php echo $this->escape(@$this->element->product_code); ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[product][product_tax_id]">
							<?php echo JText::_( 'TAXATION_CATEGORY' ); ?>
						</label>
					</td>
					<td>
						<?php echo $this->categoryType->display('data[product][product_tax_id]',@$this->element->product_tax_id);?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[product][product_tax_id]">
							<?php echo JText::_( 'MANUFACTURER' ); ?>
						</label>
					</td>
					<td>
						<?php echo $this->manufacturerType->display('data[product][product_manufacturer_id]',@$this->element->product_manufacturer_id);?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[product][product_sale_start]">
							<?php echo JText::_( 'PRODUCT_SALE_START' ); ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('calendar', hikashop_getDate((@$this->element->product_sale_start?@$this->element->product_sale_start:''),'%Y-%m-%d %H:%M'), 'data[product][product_sale_start]','product_sale_start','%Y-%m-%d %H:%M','size="20"'); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[product][product_sale_end]">
							<?php echo JText::_( 'PRODUCT_SALE_END' ); ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('calendar', hikashop_getDate((@$this->element->product_sale_end?@$this->element->product_sale_end:''),'%Y-%m-%d %H:%M'), 'data[product][product_sale_end]','product_sale_end','%Y-%m-%d %H:%M','size="20"'); ?>
					</td>
				</tr>
				<?php
				if(hikashop_level(1) && $this->config->get('product_contact',0)==1){ ?>
				<tr>
					<td class="key">
						<?php echo JText::_('DISPLAY_CONTACT_BUTTON'); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[product][product_contact]" , '',@$this->element->product_contact ); ?>
					</td>
				</tr>
				<?php }
				if(hikashop_level(1) && $this->config->get('product_waitlist',0)==1){ ?>
				<tr>
					<td class="key">
						<?php echo JText::_('DISPLAY_WAITLIST_BUTTON'); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[product][product_waitlist]" , '',@$this->element->product_waitlist ); ?>
					</td>
				</tr>
				<?php }
				$this->setLayout('common');
				echo $this->loadTemplate();
				?>
			</table>