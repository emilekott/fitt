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
class PluginsController extends hikashopController{
	var $type='plugins';
	var $listing = true;
	function __construct(){
		parent::__construct();
		$this->display[]='selectimages';
		$this->modify_views[]='edit_translation';
		$this->modify[]='save_translation';
		$this->modify[]='trigger';
	}
	function trigger(){
		$cid= JRequest::getInt('cid');
		$function = JRequest::getString('function');
		if(empty($cid) || empty($function)){
			return false;
		}
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->get($cid);
		if(empty($plugin)){
			return false;
		}
		$plugin = hikashop_import($plugin->folder,$plugin->element);
		return $plugin->$function();
		return true;
	}
	function edit_translation(){
		JRequest::setVar( 'layout', 'edit_translation'  );
		return parent::display();
	}
	function save_translation(){
		$cid= JRequest::getInt('cid');
		$type = JRequest::getString('type');
		$id_field = $type.'_id';
		$pluginClass = hikashop_get('class.'.$type);
		$element = $pluginClass->get($cid);
		if(!empty($element->$id_field)){
			$class = hikashop_get('helper.translation');
			$class->getTranslations($element);
			$class->handleTranslations($type,$element->$id_field,$element);
		}
	}
	function orderdown(){
		$this->setOptions();
		return parent::orderdown();
	}
	function orderup(){
		$this->setOptions();
		return parent::orderup();
	}
	function saveorder(){
		$this->setOptions();
		return parent::saveorder();
	}
	function cancel(){
		$type = JRequest::getVar( 'plugin_type','shipping').'_edit';
		if(JRequest::getVar( 'subtask','')==$type){
			JRequest::setVar( 'subtask', ''  );
			return $this->edit();
		}
		return $this->listing();
	}
	function listing(){
		if($this->listing){
			JRequest::setVar( 'layout', 'listing'  );
		}else{
			JRequest::setVar( 'layout', 'form'  );
			$app =& JFactory::getApplication();
			$type = $app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.shipping_plugin_type','shipping_plugin_type','manual');
			JRequest::setVar( 'name', $type  );
		}
		return parent::display();
	}
	function selectimages(){
		JRequest::setVar( 'layout', 'selectimages'  );
		return parent::display();
	}
	function setOptions(){
		$app =& JFactory::getApplication();
		$this->listing = false;
		$this->groupVal = $app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.shipping_plugin_type','shipping_plugin_type','manual' );
		$this->type=$app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.plugin_type','plugin_type','shipping' );
		$this->pkey = $this->type.'_id';
		$this->table = $this->type;
		$this->groupMap = $this->type.'_type';
		$this->orderingMap = $this->type.'_ordering';
	}
	function save(){
		$status = $this->store();
		$subtask=JRequest::getVar('subtask');
		if(!empty($subtask)){
			JRequest::setVar('subtask','');
			return $this->edit();
		}
		return $this->listing();
	}
	function store(){
		$this->plugin = JRequest::getCmd('name','manual');
		$this->plugin_type = JRequest::getCmd('plugin_type','shipping');
		if(!in_array($this->plugin_type,array('shipping','payment'))){
			return false;
		}
		$data = hikashop_import('hikashop'.$this->plugin_type,$this->plugin);
		$element = null;
		$id = hikashop_getCID($this->plugin_type.'_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		$params_name = $this->plugin_type.'_params';
		if(!empty($formData[$this->plugin_type])){
			$plugin_id = $this->plugin_type.'_id';
			$element->$plugin_id = $id;
			foreach($formData[$this->plugin_type] as $column => $value){
				hikashop_secureField($column);
				if(is_array($value)){
					if($column == $params_name){
						$element->$params_name = null;
						foreach($formData[$this->plugin_type][$column] as $key=>$val){
							hikashop_secureField($key);
							$element->$params_name->$key = strip_tags($val);
						}
					}elseif($column=='payment_shipping_methods' OR $column=='payment_currency'){
						$element->$column = array();
						foreach($formData[$this->plugin_type][$column] as $key=>$val){
							$element->{$column}[(int)$key] = strip_tags($val);
						}
					}
				}else{
					$element->$column = strip_tags($value);
				}
			}
			if($this->plugin_type=='payment'){
				if(!isset($element->payment_shipping_methods)) $element->payment_shipping_methods=array();
				if(!isset($element->payment_currency)) $element->payment_currency=array();
			}
			$plugin_description = $this->plugin_type.'_description';
			$plugin_description_data = JRequest::getVar($plugin_description,'','','string',JREQUEST_ALLOWRAW);
			$element->$plugin_description = $plugin_description_data;
			$class = hikashop_get('helper.translation');
			$class->getTranslations($element);
		}
		$function = 'on'.ucfirst($this->plugin_type).'ConfigurationSave';
		if(method_exists($data,$function)){
			$data->$function($element);
		}
		if(!empty($element)){
			$pluginClass = hikashop_get('class.'.$this->plugin_type);
			if(isset($element->$params_name)){
				$element->$params_name=serialize($element->$params_name);
			}
			$status = $pluginClass->save($element);
			if(!$status){
				JRequest::setVar( 'fail', $element  );
			}else{
				$class->handleTranslations('payment',$status,$element);
				$app =& JFactory::getApplication();
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'message');
				if(empty($id)){
					JRequest::setVar($this->plugin_type.'_id',$status);
				}
			}
		}
	}
}