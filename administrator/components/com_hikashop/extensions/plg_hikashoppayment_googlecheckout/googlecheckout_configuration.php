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
	echo '<tr><td colspan="2"><strong>The Google Checkout payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.</strong></td></tr>';
}
?>
<tr>
	<td colspan="2">
	<?php
	$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
	echo $httpsHikashop.'index.php?option=com_hikashop&amp;ctrl=checkout&amp;task=notify&amp;notif_payment=googlecheckout&amp;tmpl=component';
	?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][merchant_id]">
			Merchant ID
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][merchant_id]" value="<?php echo @$this->element->payment_params->merchant_id; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][merchant_key]">
			Merchant KEY
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][merchant_key]" value="<?php echo @$this->element->payment_params->merchant_key; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][currency]">
			Currency
		</label>
	</td>
	<td>
		<?php 
		$values = array();
		$values[] = JHTML::_('select.option', 'USD', JText::_('USD'));	
		$values[] = JHTML::_('select.option', 'GBP', JText::_('GBP'));	
		echo JHTML::_('select.genericlist', $values, "data[payment][payment_params][currency]", 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->currency ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][sandbox]">
			Sandbox
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][sandbox]" , '',@$this->element->payment_params->sandbox ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][server_to_server]">
			Server to Server
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][server_to_server]" , '',@$this->element->payment_params->server_to_server ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][charge_and_ship]">
			Charge And Ship
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][charge_and_ship]" , '',@$this->element->payment_params->charge_and_ship ); ?>
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
		<label for="data[payment][payment_params][invalid_status]">
			<?php echo JText::_( 'INVALID_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][invalid_status]",@$this->element->payment_params->invalid_status); ?>
	</td>
</tr>
