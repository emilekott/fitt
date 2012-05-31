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
<div class="hikashop_currency_module" id="hikashop_currency_module">
	<form action="<?php echo hikashop_completeLink('currency&task=update'); ?>" method="post" name="hikashop_currency_form">
		<input type="hidden" name="return_url" value="<?php echo urlencode($redirectUrl); ?>" />
		<?php echo $currency->display('hikashopcurrency',hikashop_getCurrency(),'onchange="document.hikashop_currency_form.submit();"'); ?>
	</form>
</div>