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
class hikashopQuantityType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 1,JText::_('AJAX_INPUT'));
		$this->values[] = JHTML::_('select.option', -1,JText::_('NORMAL_INPUT'));
		$this->values[] = JHTML::_('select.option', 0,JText::_('NO_DISPLAY'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', (int)$value );
	}
}