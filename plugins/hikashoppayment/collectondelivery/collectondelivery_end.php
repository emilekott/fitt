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
<div class="hikashop_collectondelivery_end" id="hikashop_collectondelivery_end">
	<span class="hikashop_collectondelivery_end_message" id="hikashop_collectondelivery_end_message">
		<?php echo JText::_('ORDER_IS_COMPLETE').'<br/>'. 
		JText::sprintf('AMOUNT_COLLECTED_ON_DELIVERY',$amount,$order_number).'<br/>'. 
		JText::_('THANK_YOU_FOR_PURCHASE');?>
	</span>
</div>