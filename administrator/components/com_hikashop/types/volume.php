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
class hikashopVolumeType{
	function display($map,$volume_unit){
		$config =& hikashop_config();
		$symbols = explode(',',$config->get('volume_symbols','m,cm'));
		if(empty($volume_unit)){
			$volume_unit = $symbols[0];
		}
		if(count($symbols)>1){
			$this->values = array();
			foreach($symbols as $symbol){
				$this->values[] = JHTML::_('select.option', $symbol,JText::_($symbol) );
			}
			return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $volume_unit );
		}elseif(count($symbols)){
			return $volume_unit.'<input type="hidden" name="'.$map.'" value="'.$weight_unit.'" />';
		}
		return '';
	}
}