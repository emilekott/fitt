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
<fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button type="button" onclick="submitbutton('saveproduct');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order',true); ?>" method="post" name="adminForm">
	<table width="100%" class="admintable">
		<tr>
			<td class="key">
				<label for="data[order][product][order_product_name]">
					<?php echo JText::_( 'HIKA_NAME' ); ?>
				</label>
			</td>
			<td>
				<input name="data[order][product][order_product_name]" value="<?php echo $this->escape(@$this->element->order_product_name); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[order][product][order_product_code]">
					<?php echo JText::_( 'PRODUCT_CODE' ); ?>
				</label>
			</td>
			<td>
				<input name="data[order][product][order_product_code]" value="<?php echo $this->escape(@$this->element->order_product_code); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[order][product][order_product_price]">
					<?php echo JText::_( 'UNIT_PRICE' ); ?>
				</label>
			</td>
			<td>
				<input name="data[order][product][order_product_price]" value="<?php echo @$this->element->order_product_price; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[order][product][order_product_tax]">
					<?php echo JText::_( 'VAT' ); ?>
				</label>
			</td>
			<td>
				<input name="data[order][product][order_product_tax]" value="<?php echo @$this->element->order_product_tax; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[order][product][order_product_quantity]">
					<?php echo JText::_( 'PRODUCT_QUANTITY' ); ?>
				</label>
			</td>
			<td>
				<input name="data[order][product][order_product_quantity]" value="<?php echo @$this->element->order_product_quantity; ?>" />
			</td>
		</tr><?php 
		foreach($this->extraFields['item'] as $fieldName => $oneExtraField) {
			?>
				<tr>
					<td class="key">
						<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
					</td>
					<td>
						<?php echo $this->fieldsClass->display($oneExtraField,$this->element->$fieldName,'data[order][product]['.$fieldName.']',false,'',true); ?>
					</td>
				</tr>
			<?php } ?>
		<?php $this->setLayout('notification'); echo $this->loadTemplate();?>
	</table>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][product][order_product_id]" value="<?php echo @$this->element->order_product_id;?>" />
	<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />
	<input type="hidden" name="data[order][product][order_id]" value="<?php echo @$this->element->order_id;?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>