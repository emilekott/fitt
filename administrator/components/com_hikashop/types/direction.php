<?php
defined('_JEXEC') or die('Restricted access');
?>
<?php
class hikashopDirectionType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'horizontal',JText::_('HORIZONTAL'));
		$this->values[] = JHTML::_('select.option', 'vertical',JText::_('VERTICAL'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}