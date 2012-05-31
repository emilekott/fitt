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
<div class="hikashop_bf_rbsbusinessgateway_thankyou" id="hikashop_bf_rbsbusinessgateway_thankyou">
	<span id="hikashop_bf_rbsbusinessgateway_thankyou_message" class="hikashop_bf_rbsbusinessgateway_thankyou_message">
		<?php echo JText::_('THANK_YOU_FOR_PURCHASE');
		if(!empty($return_url)){
			echo '<br/><a href="'.$return_url.'">'.JText::_('GO_BACK_TO_SHOP').'</a>';
		}?>
	</span>
</div>
