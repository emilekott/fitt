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
class hikashopMenusClass extends hikashopClass{
	var $pkeys=array('id');
	var $toggle = array('published'=>'id');
	function getTable(){
		return hikashop_table('menu',false);
	}
	function get($id){
		$obj = parent::get($id);
		$config =& hikashop_config();
		if(!empty($obj->id)){
			$obj->hikashop_params = $config->get('menu_'.$obj->id,null);
		}
		if(empty($obj->hikashop_params)){
			$obj->hikashop_params = $config->get('default_params',null);
		}
		$this->loadParams($obj);
		return $obj;
	}
	function loadParams(&$result){
		if(!empty($result->params)){
			$lines = explode("\n",$result->params);
			$result->params = array();
			foreach($lines as $line){
				$param = explode('=',$line,2);
				if(count($param)==2){
					$result->params[$param[0]]=$param[1];
				}
			}
		}
	}
	function saveForm(){
		$module = null;
		$formData = JRequest::getVar( 'menu', array(), '', 'array' );
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
			if(in_array($module->content_type,array('category','manufacturer'))){
				$module->link='index.php?option=com_hikashop&view=category&layout=listing';
			}else{
				$module->link='index.php?option=com_hikashop&view=product&layout=listing';
			}
			$content_type = $module->content_type;
			unset($module->content_type);
		}
		$new = false;
		if(empty($module->id)){
			$new = true;
			if(empty($module->alias)){
				if(version_compare(JVERSION,'1.6','<')){
					$module->alias = $module->name;
				}else{
					$module->alias = $module->title;
				}
				$module->alias = preg_replace('#[^a-z_0-9-]#i','',$module->alias);
			}
		}
		$result = $this->save($module);
		if($result){
			$element = array();
			$formData = JRequest::getVar( 'config', array(), '', 'array' );
			$params_name = 'menu_'.(int)$module->id;
			if($new){
				$post_name = 'menu_0';
			}else{
				$post_name = $params_name;
			}
			if(!empty($formData[$post_name])){
				foreach($formData[$post_name] as $column => $value){
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
			$element['content_type']=$content_type;
			if(in_array($element['content_type'],array('category','manufacturer')) && empty($element['modules'])){
				$this->displayErrors((int)$module->id);
			}
			$configClass =& hikashop_config();
			$config=null;
			$config->$params_name = $element;
			if($configClass->save($config)){
				$configClass->set($params_name,$element);
			}
		}
		return $result;
	}
	function displayErrors($id){
		static $displayed = false;
		if(!$displayed){
			$displayed = true;
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_('MENU_WITHOUT_ASSOCIATED_MODULE'));
			$app->enqueueMessage(JText::_('ASSOCIATED_MODULE_NEEDED'));
			$app->enqueueMessage(JText::sprintf('ADD_MODULE_AUTO',hikashop_completeLink('menus&task=add_module&cid='.$id.'&'.JUtility::getToken().'=1')));
		}
	}
	function save(&$element){
		if(version_compare(JVERSION,'1.6','<')){
			$query="SELECT a.id FROM ".hikashop_table('components',false).' AS a WHERE a.option=\''.HIKASHOP_COMPONENT.'\'';
			$this->database->setQuery($query);
			$element->componentid = $this->database->loadResult();
		}else{
			$query="SELECT a.extension_id FROM ".hikashop_table('extensions',false).' AS a WHERE a.type=\'component\' AND a.element=\''.HIKASHOP_COMPONENT.'\'';
			$this->database->setQuery($query);
			$element->component_id = $this->database->loadResult();
		}
		if(empty($element->id)){
			$element->params['show_page_title']=1;
		}
		if(!empty($element->params)&&is_array($element->params)){
			$params = '';
			foreach($element->params as $k => $v){
				$params.=$k.'='.$v."\n";
			}
			$element->params = rtrim($params,"\n");
		}
		$element->id = parent::save($element);
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
					$ids[]=$this->database->Quote('menu_'.(int)$id);
				}
				$query = 'DELETE FROM '.hikashop_table('config').' WHERE config_namekey IN ('.implode(',',$ids).');';
				$this->database->setQuery($query);
				return $this->database->query();
			}
		}
		return $result;
	}
	function attachAssocModule($id){
		$menu = $this->get($id);
		if(!empty($menu->link) && strpos($menu->link,'view=product')===false){
			if($menu->hikashop_params['content_type']!='manufacturer'){
				$menu->hikashop_params['content_type']='category';
			}
		}else{
			$menu->hikashop_params['content_type']='category';
		}
		$params =& $menu->hikashop_params;
		$module_id = $this->createAssocModule($params,$id);
		if(!empty($module_id)){
			$configData=null;
			$params['modules']=$module_id;
			$name = 'menu_'.$id;
			$configData->$name = $params;
			$config =& hikashop_config();
			if($config->save($configData)){
				$config->set($name,$params);
				$app =& JFactory::getApplication();
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
			}
		}
		return true;
	}
	function createMenu(&$moduleOtpions,$id){
		$alias = 'hikashop-menu-for-module-'.$id;
		$this->database->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$alias.'\'');
		$moduleOtpions['itemid'] = $this->database->loadResult();
		if(empty($moduleOtpions['itemid'])){
			$this->database->setQuery('SELECT menutype FROM '.hikashop_table('menu_types',false).' WHERE menutype=\'hikashop_hidden\'');
			$mainMenu = $this->database->loadResult();
			if(empty($mainMenu)){
				$this->database->setQuery('INSERT INTO '.hikashop_table('menu_types',false).' ( `menutype`,`title`,`description` ) VALUES ( \'hikashop_hidden\',\'HikaShop hidden menus\',\'This menu is used by HikaShop to store menus configurations\' )');
				$this->database->query();
			}
			if(version_compare(JVERSION,'1.6','<')){
				$element = null;
				$element->menutype = 'hikashop_hidden';
				$element->alias = $alias;
				$element->link = 'index.php?option=com_hikashop&view=category&layout=listing';
				$element->type = 'component';
				$element->published = 1;
				$element->name = 'Menu item for category listing module '.$id;
				$this->save($element);
				$this->database->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$element->alias.'\'');
				$moduleOtpions['itemid'] = $this->database->loadResult();
			}else{
				$this->database->setQuery('SELECT rgt FROM '.hikashop_table('menu',false).' WHERE id=1');
				$root = $this->database->loadResult();
				$element = null;
				$element->menutype = 'hikashop_hidden';
				$element->alias = $alias;
				$element->link = 'index.php?option=com_hikashop&view=category&layout=listing';
				$element->type = 'component';
				$element->published = 1;
				$element->client_id = 0;
				$element->language = '*';
				$element->access = 1;
				$element->lft = $root;
				$element->rgt = $root+1;
				$element->level = 1;
				$element->parent_id = 1;
				$element->title = 'Menu item for category listing module '.$id;
				$this->save($element);
				$this->database->setQuery('UPDATE '.hikashop_table('menu',false).' SET rgt='.($root+2).' WHERE id=1');
				$this->database->query();
				$this->database->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$element->alias.'\'');
				$moduleOtpions['itemid'] = $this->database->loadResult();
			}
		}
		if(!empty($moduleOtpions['itemid'])){
			$menuData = null;
			$menuData->id = $moduleOtpions['itemid'];
			$this->createMenuOption($menuData,$moduleOtpions);
		}
	}
	function createMenuOption(&$menuData,$default_params=null){
		$configClass =& hikashop_config();
		if(empty($default_params)){
			$default_params = $configClass->get('default_params');
			$default_params['content_type'] = 'category';
			$default_params['layout_type']='div';
			$default_params['content_synchronize']='1';
			if($default_params['columns']==1){
				$default_params['columns']=3;
			}
		}
		$id = (int)@$menuData->id;
		$default_params['modules']='';
		$default_params['modules']=(int)$this->createAssocModule($default_params,$id);
		$name = 'menu_'.$id;
		$config=null;
		$config->$name = $default_params;
		if($configClass->save($config)){
			$configClass->set($name,$default_params);
		}
		$menuData->hikashop_params = $default_params;
		return true;
	}
	function createAssocModule(&$params,$id){
		if(!empty($params['modules'])){
			$ids = explode(',',$params['modules']);
			$this->database->setQuery('SELECT * FROM '.hikashop_table('modules',false).' WHERE id IN ('.$ids.');');
			$modulesData = $this->database->loadObjectList('id');
			if(!is_array($modulesData) || !count($modulesData)){
				$params['modules']='';
			}
		}
		if(!empty($params['content_type']) && in_array($params['content_type'],array('category','manufacturer'))&&empty($params['modules'])){
			$config =& hikashop_config();
			$default_params = $config->get('default_params');
			$default_params['content_type'] = 'product';
			$default_params['layout_type']='div';
			$default_params['random']=0;
			$default_params['content_synchronize']='1';
			if($default_params['columns']==1){
				$default_params['columns']=3;
			}
			$module = null;
			$module->hikashop_params = $default_params;
			$module->title = 'Associated products listing for '.$params['content_type'].' listing menu '.$id;
			$module->published=0;
			$module->position='left';
			$module->ordering=0;
			$module->module='mod_hikashop';
			$module->client_id=0;
			$module->showtitle=0;
			$class = hikashop_get('class.modules');
			return $class->save($module);
		}
		return false;
	}
	function getItemidFromCategory($category_id) {
		$config = & hikashop_config();
		$values = $config->values;
		foreach ($values as $key => $value) {
			if (preg_match('#menu_([0-9]+)#', $key, $match)  && is_string($value->config_value)) {
				$options = unserialize(base64_decode($value->config_value));
				if ($options['selectparentlisting'] == $category_id) {
					return $match[1];
				}
			}
		}
		return false;
	}
}