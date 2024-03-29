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
<h1><?php echo JText::_('ADDRESS_INFORMATION');?></h1>
<div id="hikashop_address_form_span_iframe">
	<form action="<?php echo hikashop_completeLink('user&task=saveaddress'); ?>" method="post" name="hikashop_address_form" enctype="multipart/form-data">
			<table>
			<?php 
			foreach($this->extraFields['address'] as $fieldName => $oneExtraField) {
			?>
				<tr>
					<td class="key">
						<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
					</td>
					<td>
						<?php echo $this->fieldsClass->display($oneExtraField,$this->address->$fieldName,'data[address]['.$fieldName.']'); ?>
					</td>
				</tr>
			<?php }	?>
			</table>
		<input type="hidden" name="ctrl" value="user"/>
		<input type="hidden" name="task" value="saveaddress"/>
		<input type="hidden" name="data[address][address_user_id]" value="<?php echo $this->user_id;?>"/>
		<input type="hidden" name="data[address][address_id]" value="<?php echo (int)@$this->address->address_id;?>"/>
		<?php
		echo JHTML::_( 'form.token' ); 
		echo $this->cart->displayButton(JText::_('OK'),'ok',$this->params,hikashop_completeLink('user&task=saveaddress'),'if(hikashopCheckChangeForm(\'address\',\'hikashop_address_form\')) document.forms[\'hikashop_address_form\'].submit(); return false;','style="float:right"');
		?>
	</form>
</div>
