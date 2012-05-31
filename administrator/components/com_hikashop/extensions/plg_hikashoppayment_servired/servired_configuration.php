<?php
defined('_JEXEC') or die('Restricted access');
?>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][url]">
			<?php echo 'Servired URL'; ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][url]" value="<?php echo @$this->element->payment_params->url; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][merchantId]">
			<?php echo 'Shop Id'; ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][merchantId]" value="<?php echo @$this->element->payment_params->merchantId; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][merchantName]">
			<?php echo 'Shop Name'; ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][merchantName]" value="<?php echo @$this->element->payment_params->merchantName; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][terminalId]">
			<?php echo 'Terminal ID'; ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][terminalId]" value="<?php echo @$this->element->payment_params->terminalId; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][encriptionKey]">
			<?php echo 'Clave de encriptación'; ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][encriptionKey]" value="<?php echo @$this->element->payment_params->encriptionKey; ?>" />
	</td>
</tr>

<tr>
	<td class="key">
		<label for="data[payment][payment_params][invalid_status]">
			<?php echo JText::_( 'INVALID_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][invalid_status]",@$this->element->payment_params->invalid_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pending_status]">
			<?php echo JText::_( 'PENDING_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][pending_status]",@$this->element->payment_params->pending_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verified_status]">
			<?php echo JText::_( 'VERIFIED_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][verified_status]",@$this->element->payment_params->verified_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][debug]">
			<?php echo JText::_( 'DEBUG' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][debug]" , '',@$this->element->payment_params->debug	); ?>
	</td>
</tr>
