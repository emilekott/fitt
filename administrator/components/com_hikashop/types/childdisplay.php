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
class hikashopChilddisplayType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 0,JText::_('DIRECT_SUB_ELEMENTS') );
		$this->values[] = JHTML::_('select.option', 1,JText::_('ALL_SUB_ELEMENTS'));
	}
	function display($map,$value,$form=true){
		$this->load();
		$options = 'class="inputbox" size="1" ';
		if(!$form){
			$options .= 'onchange="this.form.submit();"';
		}
		return JHTML::_('select.genericlist',   $this->values, $map, $options, 'value', 'text', (int)$value );
	}
}