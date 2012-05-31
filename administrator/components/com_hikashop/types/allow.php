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
class hikashopAllowType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '',JText::_('ALL_DATES') );
		$this->values[] = JHTML::_('select.option', 'past',JText::_('ONLY_PAST_DATES'));
		$this->values[] = JHTML::_('select.option', 'future',JText::_('ONLY_FUTURE_DATES'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}