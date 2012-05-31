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
class hikashopListType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'nochild',JText::_('NO_CHILD') );
		$this->values[] = JHTML::_('select.option', 'allchilds',JText::_('ALL_CHILDS'));
		$this->values[] = JHTML::_('select.option', 'allchildsexpand',JText::_('ALL_CHILDS_EXPANDED'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}