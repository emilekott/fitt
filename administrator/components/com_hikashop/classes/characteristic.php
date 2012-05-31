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
class hikashopCharacteristicClass extends hikashopClass{
	var $tables = array('characteristic','characteristic');
	var $pkeys = array('characteristic_parent_id','characteristic_id');
	var $deleteToggle = array('variant'=>array('variant_characteristic_id','variant_product_id'));
	function saveForm(){
		$element = null;
		$element->characteristic_id = hikashop_getCID('characteristic_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		foreach($formData['characteristic'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = $safeHtmlFilter->clean($value, 'string');
		}
		$element->values = JRequest::getVar( 'characteristic', array(), '', 'array' );
		JArrayHelper::toInteger($element->values);
		$status = $this->save($element);
		if(!$status){
			JRequest::setVar( 'fail', $element  );
		}elseif(@$element->characteristic_parent_id==0){
			$this->updateValues($element,$status);
		}
		return $status;
	}
	function save(&$element){
		$class = hikashop_get('helper.translation');
		$class->getTranslations($element);
		$status = parent::save($element);
		if($status){
			$class->handleTranslations('characteristic',$status,$element);
		}
		return $status;
	}
	function updateValues(&$element,$status){
		$filter='';
		if(count($element->values)){
			$filter = ' AND characteristic_id NOT IN ('.implode(',',$element->values).')';
		}
		$query = 'DELETE FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = '.$status.$filter;
		$this->database->setQuery($query);
		$this->database->query();
		if(count($element->values)){
			$query = 'UPDATE '.hikashop_table('characteristic').' SET characteristic_parent_id='.$status.' WHERE characteristic_id IN ('.implode(',',$element->values).') AND characteristic_parent_id<1';
			$this->database->setQuery($query);
			$this->database->query();
		}
	}
	function loadConversionTables(&$obj){
		$obj->characteristics = array();
		$obj->characteristicsConversionTable = array();
		$query = 'SELECT * FROM '.hikashop_table('characteristic'). ' ORDER BY characteristic_parent_id ASC';
		$this->database->setQuery($query);
		$obj->characteristics = $this->database->loadObjectList('characteristic_id');
		if(!empty($obj->characteristics)){
			foreach($obj->characteristics as $characteristic){
				$key = '';
				if(!empty($characteristic->characteristic_parent_id) && !empty($obj->characteristics[$characteristic->characteristic_parent_id])){
					if(function_exists('mb_strtolower')){
						$key = mb_strtolower(trim($obj->characteristics[$characteristic->characteristic_parent_id]->characteristic_value)).'_';
					}else{
						$key = strtolower(trim($obj->characteristics[$characteristic->characteristic_parent_id]->characteristic_value)).'_';
					}
				}
				if(function_exists('mb_strtolower')){
					$key2 = mb_strtolower(trim($characteristic->characteristic_value,'" '));
				}else{
					$key2 = strtolower(trim($characteristic->characteristic_value,'" '));
				}
				$key .= $key2;
				$obj->characteristicsConversionTable[$key]=$characteristic->characteristic_id;
				$obj->characteristicsConversionTable[$key2]=$characteristic->characteristic_id;
			}
		}
	}
}
