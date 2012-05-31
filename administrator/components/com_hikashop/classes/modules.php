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
class hikashopModulesClass extends hikashopClass{
	var $pkeys=array('id');
	var $toggle = array('published'=>'id');
	function getTable(){
		return hikashop_table('modules',false);
	}
	function get($id){
		$obj = parent::get($id);
		$config =& hikashop_config();
		if(!empty($obj->id)){
			$obj->hikashop_params = $config->get('params_'.$obj->id,null);
		}
		if(empty($obj->hikashop_params)){
			$obj->hikashop_params = $config->get('default_params',null);
		}
		$this->_loadParams($obj);
		return $obj;
	}
	function _loadParams(&$result){
		if(!empty($result->params)){
			if(version_compare(JVERSION,'1.6','<')){
				$lines = explode("\n",$result->params);
				$result->params = array();
				foreach($lines as $line){
					$param = explode('=',$line,2);
					if(count($param)==2){
						$result->params[$param[0]]=$param[1];
					}
				}
			}else{
				$registry = new JRegistry;
				$registry->loadJSON($result->params);
				$result->params = $registry->toArray();
			}
		}
	}
	function saveForm(){
		$module = null;
		$formData = JRequest::getVar( 'module', array(), '', 'array' );
		if(!empty($formData)){
			foreach($formData as $column => $value){
				hikashop_secureField($column);
				if(is_array($value)){
					$module->$column=array();
					foreach($value as $k2 => $v2){
						hikashop_secureField($k2);
						$module->{$column}[$k2] = strip_tags($v2);
					}
				}else{
					$module->$column = strip_tags($value);
				}
			}
		}
		$element = array();
		$formData = JRequest::getVar( 'config', array(), '', 'array' );
		$params_name = 'params_'.(int)$module->id;
		if(!empty($formData[$params_name])){
			foreach($formData[$params_name] as $column => $value){
				hikashop_secureField($column);
				$element[$column] = strip_tags($value);
			}
			if(empty($element['selectparentlisting'])){
				$cat = hikashop_get('class.category');
				$mainProductCategory = 'product';
				$cat->getMainElement($mainProductCategory);
				$element['selectparentlisting']=$mainProductCategory;
			}
		}
		$module->hikashop_params =& $element;
		$result = $this->save($module);
		return $result;
	}
	function save(&$element){
		if(!empty($element->params)&&is_array($element->params)){
			if(version_compare(JVERSION,'1.6','<')){
				$params = '';
				foreach($element->params as $k => $v){
					$params.=$k.'='.$v."\n";
				}
				$element->params = rtrim($params,"\n");
			}else{
				$handler = JRegistryFormat::getInstance('JSON');
				$element->params = $handler->objectToString($element->params);
			}
		}
		$element->id = parent::save($element);
		if($element->id && !empty($element->hikashop_params)){
			$configClass =& hikashop_config();
			$config=null;
			$params_name = 'params_'.$element->id;
			$config->$params_name = $element->hikashop_params;
			if($configClass->save($config)){
				$configClass->set($params_name,$element->hikashop_params);
			}
		}
		return $element->id;
	}
	function delete(&$elements){
		$result = parent::delete($elements);
		if($result){
			if(!is_array($elements)){
				$elements=array($elements);
			}
			if(!empty($elements)){
				$ids = array();
				foreach($elements as $id){
					$ids[]=$this->database->Quote('params_'.(int)$id);
				}
				$query = 'DELETE FROM '.hikashop_table('config').' WHERE config_namekey IN ('.implode(',',$ids).');';
				$this->database->setQuery($query);
				return $this->database->query();
			}
		}
		return $result;
	}
}