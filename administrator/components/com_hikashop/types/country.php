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
class hikashopCountryType{
	var $type = 'country';
	var $published = false;
	var $allName = 'HIKA_ALL';
	var $country_name = '';
	function load(){
		$filters = array();
		$leftjoin='';
		$from='';
		$db =& JFactory::getDBO();
		if(is_array($this->type)){
			$filters[]='a.zone_type IN (\''.implode('\',\'',$this->type).'\')';
		}else{
			$filters[]= 'a.zone_type=\''.$this->type.'\'';
			if($this->type=='state'&&!empty($this->country_name)){
				$filters[]= 'b.zone_parent_namekey='.$db->Quote($this->country_name);
				$from=hikashop_table('zone_link').' AS b LEFT JOIN ';
				$leftjoin=' ON b.zone_child_namekey=a.zone_namekey';
			}
		}
		if($this->published){
			$filters[]='a.zone_published=1';
		}
		$query = 'SELECT a.* FROM '.$from.hikashop_table('zone').' AS a'.$leftjoin;//.' AS a LEFT JOIN '.hikashop_table('zone_link').' AS b ON a.zone_namekey=b.zone_child_namekey';
		$query .= ' WHERE '.implode(' AND ',$filters).' ORDER BY a.zone_name_english ASC';
		$db->setQuery($query);
		return $db->loadObjectList('zone_namekey');
	}
	function display($map,$value, $form=true,$options='class="inputbox" size="1"'){
		$zones = $this->load();
		$this->values = array();
		if($form){
			$this->values[] = JHTML::_('select.option', '0', JText::_($this->allName) );
			$options .= ' onchange="document.adminForm.submit( );"';
		}
		foreach($zones as $oneZone){
			$this->values[] = JHTML::_('select.option', $oneZone->zone_id, $oneZone->zone_name_english.' ( '.$oneZone->zone_name.' )' );
		}
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', (int)$value );
	}
}