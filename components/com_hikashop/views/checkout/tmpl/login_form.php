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
<?php if(JPluginHelper::isEnabled('authentication', 'openid')) :
		$lang = &JFactory::getLanguage();
		$lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
		$langScript = 	'var JLanguage = {};'.
						' JLanguage.WHAT_IS_OPENID = \''.JText::_( 'WHAT_IS_OPENID' ).'\';'.
						' JLanguage.LOGIN_WITH_OPENID = \''.JText::_( 'LOGIN_WITH_OPENID' ).'\';'.
						' JLanguage.NORMAL_LOGIN = \''.JText::_( 'NORMAL_LOGIN' ).'\';'.
						' var comlogin = 1;';
		$document = &JFactory::getDocument();
		$document->addScriptDeclaration( $langScript );
		JHTML::_('script', 'openid.js');
endif; ?>
	<p id="com-form-login-username">
		<label for="username"><?php echo JText::_('HIKA_USERNAME') ?></label><br />
		<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
	</p>
	<p id="com-form-login-password">
		<label for="passwd"><?php echo JText::_('HIKA_PASSWORD') ?></label><br />
		<input type="password" id="passwd" name="passwd" class="inputbox" size="18" alt="password" />
	</p>
	<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="com-form-login-remember">
		<label for="remember"><?php echo JText::_('HIKA_REMEMBER_ME') ?></label>
		<input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" alt="Remember Me" />
	</p>
	<?php endif; ?>
	<?php echo $this->cart->displayButton(JText::_('HIKA_LOGIN'),'login',@$this->params,'','var button = document.getElementById(\'login_view_action\'); if(button) button.value=\'login\'; document.hikashop_checkout_form.submit();return false;'); ?>
<?php 
if(version_compare(JVERSION,'1.6','<')){
	$reset = 'index.php?option=com_user&view=reset';
	$remind = 'index.php?option=com_user&view=remind';
}else{
	$reset = 'index.php?option=com_users&view=reset';
	$remind = 'index.php?option=com_users&view=remind';
}
?>
<ul>
	<li>
		<a href="<?php echo JRoute::_( $reset ); ?>">
		<?php echo JText::_('HIKA_FORGOT_YOUR_PASSWORD'); ?></a>
	</li>
	<li>
		<a href="<?php echo JRoute::_( $remind ); ?>">
		<?php echo JText::_('HIKA_FORGOT_YOUR_USERNAME'); ?></a>
	</li>
</ul>