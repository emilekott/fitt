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
		<label for="data[payment][payment_params][url]">
			<?php echo JText::_( 'URL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][url]" value="<?php echo @$this->element->payment_params->url; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][login_id]">
			<?php echo JText::_( 'AUTHORIZE_LOGIN_ID' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][login_id]" value="<?php echo @$this->element->payment_params->login_id; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][transaction_key]">
			<?php echo JText::_( 'AUTHORIZE_TRANSACTION_KEY' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][transaction_key]" value="<?php echo @$this->element->payment_params->transaction_key; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][md5_hash]">
			<?php echo JText::_( 'AUTHORIZE_MD5_HASH' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][md5_hash]" value="<?php echo @$this->element->payment_params->md5_hash; ?>" />
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
		<label for="data[payment][payment_params][api]">
			<?php echo JText::_( 'API' ); ?>
		</label>
	</td>
	<td>
		<?php 
		$values = array();
		$values[] = JHTML::_('select.option', 'sim',JText::_('SIM'));
		$values[] = JHTML::_('select.option', 'aim',JText::_('AIM'));
		echo JHTML::_('select.genericlist',   $values, "data[payment][payment_params][api]" , 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->api ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][ask_ccv]">
			<?php echo JText::_( 'CARD_VALIDATION_CODE' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][ask_ccv]",'',@$this->element->payment_params->ask_ccv);?>
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
		<label for="data[payment][payment_params][x_logo_url]">
			<?php echo JText::_( 'LOGO' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][x_logo_url]" value="<?php echo @$this->element->payment_params->x_logo_url; ?>" />
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