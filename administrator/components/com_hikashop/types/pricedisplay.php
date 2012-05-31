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
class hikashopPricedisplayType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'cheapest',JText::_('CHEAPEST_PRICE') );
		$this->values[] = JHTML::_('select.option', 'unit',JText::_('UNIT_PRICE_ONLY'));
		$this->values[] = JHTML::_('select.option', 'range',JText::_('PRICE_RANGE'));
		$this->values[] = JHTML::_('select.option', 'all',JText::_('HIKA_ALL'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}