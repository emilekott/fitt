<?php
/**
 * @package		 HikaShop for Joomla!
 * @subpackage Payment Plug-in for RBS Worldpay Business Gateway.
 * @version		 0.0.1
 * @author		 brainforge.co.uk
 * @copyright	 (C) 2011 Brainforge derive from Paypal plug-in by HIKARI SOFTWARE. All rights reserved.
 * @license		 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * In order to configure and use this plug-in you must have a RBS Worldpay Business Gateway account.
 * RBS Worldpay Business Gateway is sometimes refered to as 'Select Junior'.
 */
defined('_JEXEC') or die('Restricted access');
?>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][instid]">
			<?php echo 'Installation ID'; ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][instid]" value="<?php echo @$this->element->payment_params->instid; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][url]">
			<?php echo JText::_( 'URL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][url]" size="60" value="<?php echo @$this->element->payment_params->url; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][descProductName]">
			<?php echo 'Use Single Product Name in Description'; ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][descProductName]" , '',@$this->element->payment_params->descProductName	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][desc]">
			<?php echo 'Default Description'; ?>
		</label>
	</td>
	<td>
		<textarea id="rbsworldpay_desc" name="data[payment][payment_params][desc]" rows="5" cols="40"><?php echo $this->element->payment_params->desc; ?></textarea>
		<br/>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][address_type]">
			<?php echo 'Customer Address'; ?>
		</label>
	</td>
	<td>
		<?php
  		$values = array();
  		$values[] = JHTML::_('select.option', '',JText::_('NO_ADDRESS') );
  		$values[] = JHTML::_('select.option', 'billing',JText::_('HIKASHOP_BILLING_ADDRESS'));
  		$values[] = JHTML::_('select.option', 'shipping',JText::_('HIKASHOP_SHIPPING_ADDRESS'));
  		$values[] = JHTML::_('select.option', 'billing,shipping','Both addresses');
	 	  echo JHTML::_('select.genericlist', $values, 'data[payment][payment_params][address_type]', 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->address_type);
    ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][fixContact]">
			<?php echo 'Fix Contact'; ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][fixContact]" , '',@$this->element->payment_params->fixContact	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][hideContact]">
			<?php echo 'Hide Contact'; ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][hideContact]" , '',@$this->element->payment_params->hideContact	); ?>
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
		<label for="data[payment][payment_params][showVars]">
			<?php echo 'Show Parameters'; ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][showVars]" , '',@$this->element->payment_params->showVars	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][testMode]">
			<?php echo 'Test Mode'; ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', "data[payment][payment_params][testMode]" , '',@$this->element->payment_params->testMode	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][hostname]">
			<?php echo 'Notification hostname'; ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][hostname]" size="60" value="<?php echo @$this->element->payment_params->hostname; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][ips]">
			<?php echo JText::_( 'IPS' ); ?>
		</label>
	</td>
	<td>
		<textarea id="rbsworldpay_ips" name="data[payment][payment_params][ips]" rows="5" cols="40"><?php echo (!empty($this->element->payment_params->ips) && is_array($this->element->payment_params->ips)?trim(implode(',',$this->element->payment_params->ips)):''); ?></textarea>
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
		<label for="data[payment][payment_params][redirect_button]">Redirect to Worldpay Button</label>
	</td>
	<td>
		<textarea id="redirect_button" name="data[payment][payment_params][redirect_button]" rows="5" cols="40"><?php echo  htmlspecialchars($this->element->payment_params->redirect_button); ?></textarea>
	</td>
</tr>
