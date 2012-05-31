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
		$price = JRequest::getVar( 'price', 0 );
		$currency = hikashop_get('class.currency');
		echo JText::_('PRICE_WITH_OPTIONS').': '.$currency->format($price, hikashop_getCurrency());
		exit;