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
if(!$this->identified){
	$this->nextButton = false;
	?><h1><?php echo JText::_('LOGIN_OR_REGISTER_ACCOUNT');?></h1>
	<div id="hikashop_checkout_login" class="hikashop_checkout_login"><?php
		if($this->config->get('display_login',1)){ ?>
		<div id="hikashop_checkout_login_left_part" class="hikashop_checkout_login_left_part">
			<fieldset class="input">
			<h2><?php echo JText::_('HIKA_LOGIN');?></h2><?php
			echo $this->loadTemplate('form');
			?>
			</fieldset>
		</div>
		<?php } ?>
		<div  id="hikashop_checkout_login_right_part" class="hikashop_checkout_login_right_part">
			<fieldset class="input">
				<h2><?php echo JText::_('HIKA_REGISTRATION');?></h2><?php
				$usersConfig = &JComponentHelper::getParams( 'com_users' );
				$allowRegistration = $usersConfig->get('allowUserRegistration');
				if ($allowRegistration || $this->simplified_registration == 2){
					$params = null; $js = null;
					echo hikashop_getLayout('user','registration',$params,$js);
				}else{
					echo JText::_('REGISTRATION_NOT_ALLOWED');
				}
			?></fieldset>
		</div>
	</div>
	<input type="hidden" id="login_view_action" name="login_view_action" value="" />
	<div style="clear:both"></div><br/><?php
}
