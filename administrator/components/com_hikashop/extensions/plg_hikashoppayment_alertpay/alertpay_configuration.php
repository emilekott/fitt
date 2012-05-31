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
		<label for="data[payment][payment_params][email]">
			<?php echo JText::_( 'HIKA_EMAIL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][email]" value="<?php echo @$this->element->payment_params->email; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][security_code]">
			<?php echo JText::_( 'IPN_SECURITY_CODE' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][security_code]" value="<?php echo @$this->element->payment_params->security_code; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][status_url]">
			<?php echo JText::sprintf( 'STATUS_URL',$this->element->payment_name ); ?>
		</label>
	</td>
	<td>
		<?php echo str_replace( '&', '&amp;',@$this->element->payment_params->status_url); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][address_type]">
			<?php echo JText::_( 'PAYPAL_ADDRESS_TYPE' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['address']->display('data[payment][payment_params][address_type]',@$this->element->payment_params->address_type); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][notification]">
			<?php echo JText::sprintf( 'ALLOW_NOTIFICATIONS_FROM_X', $this->element->payment_name);  ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][notification]" , '',@$this->element->payment_params->notification	); ?>
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
<tr>
	<td class="key">
		<label for="data[payment][payment_params][cancel_url]">
			<?php echo JText::_( 'CANCEL_URL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][cancel_url]" value="<?php echo @$this->element->payment_params->cancel_url; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][return_url]">
			<?php echo JText::_( 'RETURN_URL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][return_url]" value="<?php echo @$this->element->payment_params->return_url; ?>" />
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
		<label for="data[payment][payment_params][verified_status]">
			<?php echo JText::_( 'VERIFIED_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][verified_status]",@$this->element->payment_params->verified_status); ?>
	</td>
</tr>