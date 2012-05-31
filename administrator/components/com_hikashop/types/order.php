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
class hikashopOrderType{
	function load($type){
		$filter=false;
		if($type=='product_filter'){
			$type='product';
			$filter=true;
		}
		$query = 'SELECT * FROM '.hikashop_table($type).' LIMIT 1';
		$database =& JFactory::getDBO();
		$database->setQuery($query);
		$object = $database->loadObject();
		$this->values = array();
		if($type=='product'){
			if(!$filter){
				$this->values[] = JHTML::_('select.option', 'ordering','ordering');
			}else{
				$this->values[] = JHTML::_('select.option', 'all','all');
			}
		}
		if(!empty($object)){
			foreach(get_object_vars($object) as $key => $val){
				$this->values[] = JHTML::_('select.option', $key,$key);
			}
		}
	}
	function display($map,$value,$type,$options='class="inputbox" size="1"'){
		$this->load($type);
		return JHTML::_('select.genericlist',   $this->values, $map, $options, 'value', 'text', $value );
	}
}