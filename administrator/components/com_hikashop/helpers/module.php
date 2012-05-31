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
class hikashopModuleHelper{
	function initialize(&$obj){
		$this->_getParams($obj);
		$this->setCSS($obj->params,@$obj->module);
		$obj->modules = $this->setModuleData($obj->params->get('modules'));
	}
	function setCSS(&$params,$name=''){
		$css ='';
		$main_div_name = $params->get('main_div_name');
		if(empty($main_div_name)){
			$main_div_name ='hikashop_category_information_'.($name?'module_':'menu_').$params->get('id');
			$params->set('main_div_name',$main_div_name);
		}
		$background_color = $params->get('background_color');
		if(!empty($background_color)){
			$css='
#'.$main_div_name.' div.hikashop_subcontainer {
	background:'.$background_color.';
}
#'.$main_div_name.' .hikashop_rtop *,#'.$main_div_name.' .hikashop_rbottom *{
	background:'.$background_color.';
}
			';
		}
		$center = $params->get('text_center');
		if(!empty($center)){
		$css.='
#'.$main_div_name.' div.hikashop_subcontainer,#'.$main_div_name.' div.hikashop_subcontainer span {
	text-align:center;
}
#'.$main_div_name.' div.hikashop_container {
	text-align:center;
}
			';
		}else{
			$css.='
#'.$main_div_name.' div.hikashop_subcontainer,#'.$main_div_name.' div.hikashop_subcontainer span {
	text-align:left;
}
#'.$main_div_name.' div.hikashop_container {
	text-align:left;
}
			';
		}
		$margin = $params->get('margin',0);
			$css.='
#'.$main_div_name.' div.hikashop_container {
	margin:'.$margin.'px '.$margin.'px;
}
#'.$main_div_name.' div.hikashop_category,#'.$main_div_name.' div.hikashop_product{
	float:left;
	width:100%;
}
			';
		$rounded_corners = $params->get('rounded_corners',0);
		if($rounded_corners){
			$css.= '
#'.$main_div_name.' .hikashop_subcontainer {
     -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    -khtml-border-radius: 5px;
    border-radius: 5px;
}
			';
		}else{
			$css.= '
';
		}
		$doc =& JFactory::getDocument();
		$doc->addStyleDeclaration($css);
	}
	function setModuleData($modules){
		if(!empty($modules)){
			if(!is_array($modules)){
				$modules = explode(',',$modules);
			}
			JArrayHelper::toInteger($modules);
			$modules = implode(',',$modules);
			$database =& JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('modules',false).' WHERE id IN ('.$modules.');';
			$database->setQuery($query);
			$modulesData = $database->loadObjectList('id');
			$unset = array();
			$modules = explode(',',$modules);
			foreach($modules as $k => $v){
				if(isset($modulesData[$v])){
					$file = $modulesData[$v]->module;
					$custom = substr( $file, 0, 4 ) == 'mod_' ?  0 : 1;
					$modulesData[$v]->user = $custom;
					$modulesData[$v]->name = $custom ? $modulesData[$v]->title : substr( $file, 4 );
					$modulesData[$v]->style	= null;
					$modulesData[$v]->position = strtolower($modulesData[$v]->position);
					$modules[$k] = $modulesData[$v];
				}else{
					$unset[]=$k;
				}
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($modules[$u]);
				}
			}
		}else{
			$modules=array();
		}
		return $modules;
	}
	function _getParams(&$obj){
		if(empty($obj->params)){
			global $Itemid;
			$menus	= &JSite::getMenu();
			$menu	= $menus->getActive();
			if(!empty($Itemid) && !empty($menu) && !empty($menuData->link) && strpos($menu->link,'option='.HIKASHOP_COMPONENT)!==false && (strpos($menu->link,'view=category')!==false || strpos($menu->link,'view=')===false)){
				$app =& JFactory::getApplication();
				$app->setUserState(HIKASHOP_COMPONENT.'.category_item_id',$Itemid);
			}
			if(empty($menu)){
				if(!empty($Itemid)){
					$menus->setActive($Itemid);
					$menu	= $menus->getItem($Itemid);
				}else{
					$app =& JFactory::getApplication();
					$item_id = $app->getUserState(HIKASHOP_COMPONENT.'.category_item_id');
					if(!empty($item_id)){
						$menus->setActive($item_id);
						$menu	= $menus->getItem($item_id);
					}
				}
			}
			jimport('joomla.html.parameter');
			if (is_object( $menu )) {
				$obj->params = new JParameter( $menu->params );
				$obj->params->set('id',$menu->id);
				if(version_compare(JVERSION,'1.6','<')){
					$obj->params->set('title',$menu->name);
				}else{
					$obj->params->set('title',$menu->title);
				}
			}else{
				$params ='';
				$obj->params = new JParameter($params);
			}
			$config =& hikashop_config();
			$menuClass = hikashop_get('class.menus');
			$menuData = $menuClass->get(@$menu->id);
			if($config->get('auto_init_options',1) && !empty($menuData->link) && strpos($menuData->link,'view=product')===false){
				$options = $config->get('menu_'.@$menu->id,null);
				if(empty($options) || empty($options['modules'])){
					$menuClass->createMenuOption($menuData,$options);
				}
			}
			if(!empty($menuData->hikashop_params)){
				foreach($menuData->hikashop_params as $key => $item){
					$obj->params->set($key,$item);
				}
			}
			if(!empty($menuData->params)){
				foreach($menuData->params as $key => $item){
					if(!is_object($item)){
						$obj->params->set($key,$item);
					}
				}
			}
		}else{
			$obj->module=true;
		}
	}
}