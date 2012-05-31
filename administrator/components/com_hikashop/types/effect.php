<?php
defined('_JEXEC') or die('Restricted access');
?>
<?php
class hikashopEffectType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'slide',JText::_('SLIDE'));
		$this->values[] = JHTML::_('select.option', 'fade',JText::_('FADE'));
	}
	function display($map,$value, $options=''){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" '.$options, 'value', 'text', $value );
	}
}