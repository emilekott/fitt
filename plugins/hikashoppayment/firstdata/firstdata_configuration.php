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
	echo '<tr><td colspan="2"><strong>The FirstData payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.</strong></td></tr>';
}
?><tr>
	<td class="key">
		<label for="data[payment][payment_params][login]">
			Store ID
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][login]" value="<?php echo @$this->element->payment_params->login; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][password]">
			API Password
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][password]" value="<?php echo @$this->element->payment_params->password; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][domain]">
			Payment Server
		</label>
	</td>
	<td>
		<?php
		$values = array();
		$values[] = JHTML::_('select.option', 'ws.firstdataglobalgateway.com', 'Production Server');
		$values[] = JHTML::_('select.option', 'ws.merchanttest.firstdataglobalgateway.com', 'Test Server');
		echo JHTML::_('select.genericlist',   $values, "data[payment][payment_params][domain]" , 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->domain ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pem_file]">
			PEM file
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][pem_file]" value="<?php echo @$this->element->payment_params->pem_file; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][key_file]">
			KEY file
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][key_file]" value="<?php echo @$this->element->payment_params->key_file; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][key_passwd]">
			KEY password
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][key_passwd]" value="<?php echo @$this->element->payment_params->key_passwd; ?>" />
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
