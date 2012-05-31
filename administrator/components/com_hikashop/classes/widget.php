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
class hikashopWidgetClass extends hikashopClass{
	var $pkeys=array('widget_id');
	var $tables=array('widget');
	function get($cid=0){
		if(!empty($cid)){
			$widget = parent::get($cid);
			if(!empty($widget->widget_params)){
				$widget->widget_params = unserialize($widget->widget_params);
				if(!empty($widget->widget_params->status)){
					$widget->widget_params->status = explode(',',$widget->widget_params->status);
				}
			}
			return $widget;
		}
		$query = 'SELECT * FROM '.hikashop_table('widget');
		$this->database->setQuery($query);
		$widgets = $this->database->loadObjectList();
		if(!empty($widgets)){
			foreach($widgets as $k => $widget){
				if(!empty($widget->widget_params)){
					$widgets[$k]->widget_params = unserialize($widget->widget_params);
					if(!empty($widgets[$k]->widget_params->status)){
						$widgets[$k]->widget_params->status = explode(',',$widgets[$k]->widget_params->status);
					}
				}
			}
		}
		return $widgets;
	}
	function save(&$element){
		if(!empty($element->widget_params) && !is_string($element->widget_params)){
			if(is_array($element->widget_params->status)){
				$element->widget_params->status = implode(',',$element->widget_params->status);
			}
			$element->widget_params = serialize($element->widget_params);
		}
		return parent::save($element);
	}
	function saveForm(){
		$widget = null;
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		if(!empty($formData)){
			foreach($formData['widget'] as $column => $value){
				hikashop_secureField($column);
				if(is_array($value)){
					$widget->$column=null;
					foreach($value as $k2 => $v2){
						hikashop_secureField($k2);
						if(is_array($v2)){
							$v2 = implode(',',$v2);
						}
						$widget->{$column}->$k2 = strip_tags($v2);
					}
				}else{
					$widget->$column = strip_tags($value);
				}
			}
		}
		if(!empty($widget->widget_params->start)){
			$widget->widget_params->start = hikashop_getTime($widget->widget_params->start);
		}
		if(!empty($widget->widget_params->end)){
			$widget->widget_params->end = hikashop_getTime($widget->widget_params->end);
		}
		return $this->save($widget);
	}
}