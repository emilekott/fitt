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
class hikashopOrder_statusType{
	function load(){
		$this->values=array();
		$class = hikashop_get('class.category');
		$rows = $class->loadAllWithTrans('status');
		foreach($rows as $row){
			if(!empty($row->translation)){
				$this->values[] = JHTML::_('select.option', $row->category_name,$row->translation);
			}else{
				$this->values[] = JHTML::_('select.option', $row->category_name,$row->category_name);
			}
		}
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}