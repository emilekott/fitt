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
class hikashopProductsyncType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 2,JText::_('RELATED_PRODUCTS'));
		$this->values[] = JHTML::_('select.option', 1,JText::_('IN_SAME_CATEGORIES'));
		$this->values[] = JHTML::_('select.option', 3,JText::_('FROM_SAME_MANUFACTURER'));
		$this->values[] = JHTML::_('select.option', 0,JText::_('IN_MODULE_PARENT_CATEGORY'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', (int)$value );
	}
}