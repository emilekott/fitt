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
<div class="iframedoc" id="iframedoc"></div>
<div>
	<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=discount" method="post" name="adminForm" enctype="multipart/form-data">
		<table class="admintable" width="700px" style="margin:auto">
			<tr>
				<td valign="top">
					<table class="admintable" style="margin:auto">
						<tr>
							<td class="key">
								<label for="data[discount][discount_code]">
									<?php echo JText::_( 'DISCOUNT_CODE' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[discount][discount_code]" value="<?php echo $this->escape(@$this->element->discount_code); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[discount][discount_type]">
									<?php echo JText::_( 'DISCOUNT_TYPE' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->type->display('data[discount][discount_type]',@$this->element->discount_type,true); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[discount][discount_flat_amount]">
									<?php echo JText::_( 'DISCOUNT_FLAT_AMOUNT' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[discount][discount_flat_amount]" value="<?php echo @$this->element->discount_flat_amount; ?>" /><?php echo $this->currency->display('data[discount][discount_currency_id]',@$this->element->discount_currency_id); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[discount][discount_percent_amount]">
									<?php echo JText::_( 'DISCOUNT_PERCENT_AMOUNT' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[discount][discount_percent_amount]" value="<?php echo @$this->element->discount_percent_amount; ?>" />
							</td>
						</tr>
						<tr id="hikashop_tax">
							<td class="key">
								<label for="data[discount][discount_tax_id]">
									<?php echo JText::_( 'TAXATION_CATEGORY' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->categoryType->display('data[discount][discount_tax_id]',@$this->element->discount_tax_id);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[discount][discount_used_times]">
									<?php echo JText::_( 'DISCOUNT_USED_TIMES' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[discount][discount_used_times]" value="<?php echo @$this->element->discount_used_times; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[discount][discount_published]">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', "data[discount][discount_published]" , '',@$this->element->discount_published	); ?>
							</td>
						</tr>
					</table>
				</td>
				<td>
					<table class="admintable" style="margin:auto">
						<tr>
							<td class="key">
								<label for="data[discount][discount_start]">
									<?php echo JText::_( 'DISCOUNT_START_DATE' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('calendar', (@$this->element->discount_start?hikashop_getDate(@$this->element->discount_start,'%Y-%m-%d %H:%M'):''), 'data[discount][discount_start]','discount_start','%Y-%m-%d %H:%M','size="20"'); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[discount][discount_end]">
									<?php echo JText::_( 'DISCOUNT_END_DATE' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('calendar', (@$this->element->discount_end?hikashop_getDate(@$this->element->discount_end,'%Y-%m-%d %H:%M'):''), 'data[discount][discount_end]','discount_end','%Y-%m-%d %H:%M','size="20"'); ?>
							</td>
						</tr>
						<?php if(hikashop_level(1)){
								echo $this->loadTemplate('restrictions');
							}else{ ?>
						<tr>
							<td class="key">
								<label>
									<?php echo JText::_('RESTRICTIONS'); ?>
								</label>
							</td>
							<td>
								<?php echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>'; ?>
							</td>
						</tr>
						<?php }
 ?>
					</table>
				</td>
			</tr>
		</table>
		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo @$this->element->discount_id; ?>" />
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="discount" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>