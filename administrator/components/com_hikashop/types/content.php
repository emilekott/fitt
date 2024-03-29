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
class hikashopContentType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'product',JText::_('PRODUCT') );
		$this->values[] = JHTML::_('select.option', 'category',JText::_('CATEGORY'));
		$this->values[] = JHTML::_('select.option', 'manufacturer',JText::_('MANUFACTURER'));
	}
	function display($map,$value,&$js,$update=true){
		$this->load();
		$options='';
		if($update){
			if(empty($value)){
				$value = 'product';
			}
			$options = 'var options = [\'product\', \'category\'];';
			$js .=$options.'switchPanel(\''.$value.'\',options,\'content\');';
			$options='onchange="'.$options.'return switchPanel(this.value,options,\'content\');"';
		}
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" '.$options, 'value', 'text', $value, 'content_select' );
	}
}