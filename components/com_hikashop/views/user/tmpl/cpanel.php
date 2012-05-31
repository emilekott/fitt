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
<div class="hikashop_cpanel_main" id="hikashop_cpanel_main">
	<div class="hikashop_cpanel_title" id="hikashop_cpanel_title">
		<fieldset>
			<div class="header hikashop_header_title"><h1><?php echo JText::_('CUSTOMER_ACCOUNT');?></h1></div>
		</fieldset>
	</div>
	<div class="hikashopcpanel" id="hikashopcpanel">
		<?php
			foreach($this->buttons as $oneButton){
				echo $oneButton;
			}
			?>
	</div>
</div>
<div class="clear_both"></div>