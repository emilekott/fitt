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
<table cellpadding="0" cellspacing="0" border="0" class="hikashop_contentpane">
<?php if(!$this->simplified_registration){ ?>
	<tr class="hikashop_registration_name_line">
		<td class="key">
			<label id="namemsg" for="register_name">
				<?php echo JText::_( 'HIKA_NAME' ); ?>
			</label>
		</td>
	  	<td>
	  		<input type="text" name="data[register][name]" id="register_name" value="<?php echo $this->escape($this->mainUser->get( 'name' ));?>" class="inputbox required" maxlength="50" /> *
	  	</td>
	</tr>
	<tr class="hikashop_registration_username_line">
		<td class="key">
			<label id="usernamemsg" for="register_username">
				<?php echo JText::_( 'HIKA_USERNAME' ); ?>
			</label>
		</td>
		<td>
			<input type="text" id="register_username" name="data[register][username]" value="<?php echo $this->escape($this->mainUser->get( 'username' ));?>" class="inputbox required validate-username" maxlength="25" /> *
		</td>
	</tr>
<?php }?>
<tr class="hikashop_registration_email_line">
	<td class="key">
		<label id="emailmsg" for="register_email">
			<?php echo JText::_( 'HIKA_EMAIL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" id="register_email" name="data[register][email]" value="<?php echo $this->escape($this->mainUser->get( 'email' ));?>" class="inputbox required validate-email" maxlength="100" /> *
	</td>
</tr>
<?php if(!$this->simplified_registration){ ?>
<tr class="hikashop_registration_password_line">
	<td class="key">
		<label id="pwmsg" for="password">
			<?php echo JText::_( 'HIKA_PASSWORD' ); ?>
		</label>
	</td>
  	<td>
  		<input class="inputbox required validate-password" type="password" id="register_password" name="data[register][password]" value="" /> *
  	</td>
</tr>
<tr class="hikashop_registration_password2_line">
	<td class="key">
		<label id="pw2msg" for="register_password2">
			<?php echo JText::_( 'HIKA_VERIFY_PASSWORD' ); ?>
		</label>
	</td>
	<td>
		<input class="inputbox required validate-passverify" type="password" id="register_password2" name="data[register][password2]" value="" /> *
	</td>
</tr>
<?php }?>
	<?php
		$this->setLayout('custom_fields');
		$this->type = 'user';
		echo $this->loadTemplate();
		if($this->config->get('affiliate_registration',0)){ ?>
<tr class="hikashop_registration_affiliate_line">
	<td colspan="2">
		<input class="hikashop_affiliate_checkbox" id="hikashop_affiliate_checkbox" type="checkbox" name="hikashop_affiliate_checkbox" value="1" <?php echo $this->affiliate_checked; ?> />
		<label for="hikashop_affiliate_checkbox">
		<?php $affiliate_terms = $this->config->get('affiliate_terms',0);
			$label = JText::_('BECOME_A_PARTNER');
			if(!empty($affiliate_terms)){?>
				<span class="hikashop_affiliate_terms_span_link" id="hikashop_affiliate_terms_span_link">
					<a class="hikashop_affiliate_terms_link" id="hikashop_affiliate_terms_link" target="_blank" href="<?php echo JRoute::_('index.php?option=com_content&view=article&id='.$affiliate_terms); ?>"><?php echo $label; ?></a>
				</span><?php
			}else{
				echo $label;
			} ?>
		</label>
	</td>
</tr>
	<?php }
?>
<tr class="hikashop_registration_address_info_line">
	<td colspan="2" height="40">
		<?php echo JText::_( 'ADDRESS_INFORMATION' ); ?>
	</td>
</tr>
<?php
		$this->type = 'address';
		echo $this->loadTemplate();
	?>
<tr class="hikashop_registration_required_info_line">
	<td colspan="2" height="40">
		<?php echo JText::_( 'HIKA_REGISTER_REQUIRED' ); ?>
	</td>
</tr>
</table>
<input type="hidden" name="data[register][id]" value="<?php echo (int)$this->mainUser->get( 'id' );?>" />
<input type="hidden" name="data[register][gid]" value="<?php echo (int)$this->mainUser->get( 'gid' );?>" />
<?php
$additional_check='';
if(empty($this->form_name)){
	$this->form_name = 'hikashop_checkout_form';
	if(JRequest::getVar('hikashop_check_order')) $additional_check='&& hikashopCheckChangeForm(\'order\',\''.$this->form_name.'\')';
}
echo $this->cartClass->displayButton(JText::_('HIKA_REGISTER'),'register',$this->params,'','if(hikashopCheckChangeForm(\'register\',\''.$this->form_name.'\') && hikashopCheckChangeForm(\'user\',\''.$this->form_name.'\') && hikashopCheckChangeForm(\'address\',\''.$this->form_name.'\')'.$additional_check.'){ var button = document.getElementById(\'login_view_action\'); if(button) button.value=\'register\'; document.'.$this->form_name.'.submit();} return false;');
