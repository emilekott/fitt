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
<?php
if(!function_exists('curl_init')) {
	echo '<tr><td colspan="2"><strong>The Paypal Pro payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.</strong></td></tr>';
}
?><tr>
	<td class="key">
		<label for="data[payment][payment_params][login]">
			<?php echo JText::_( 'USERNAME' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][login]" value="<?php echo @$this->element->payment_params->login; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][password]">
			<?php echo JText::_( 'PASSWORD' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][password]" value="<?php echo @$this->element->payment_params->password; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][signature]">
			<?php echo JText::_( 'SIGNATURE' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][signature]" value="<?php echo @$this->element->payment_params->signature; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][environnement]">
			<?php echo JText::_( 'ENVIRONNEMENT' ); ?>
		</label>
	</td>
	<td>
		<?php 
		$values = array();
		$values[] = JHTML::_('select.option', 'production', 'Production');
		$values[] = JHTML::_('select.option', 'sandbox', 'Sandbox');
		$values[] = JHTML::_('select.option', 'beta-sandbox', 'Beta-Sandbox');
		echo JHTML::_('select.genericlist',   $values, "data[payment][payment_params][environnement]" , 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->environnement ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][instant_capture]">
			Instant Capture
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][instant_capture]" , '',@$this->element->payment_params->instant_capture ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][ask_ccv]">
			Ask CCV
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][ask_ccv]" , '',@$this->element->payment_params->ask_ccv ); ?>
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
		<label for="data[payment][payment_params][verified_status]">
			<?php echo JText::_( 'VERIFIED_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][verified_status]",@$this->element->payment_params->verified_status); ?>
	</td>
</tr>
