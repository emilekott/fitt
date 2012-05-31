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
class MenusViewMenus extends JView{
	var $ctrl= 'menus';
	var $nameListing = 'MENUS';
	var $nameForm = 'MENU';
	var $icon = 'menu';
	function display($tpl = null,$params=null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function($params);
		parent::display($tpl);
	}
	function _loadCategory(&$element){
		if(empty($element)) $element = null;
		if(!isset($element->hikashop_params)) $element->hikashop_params = array();
		if(empty($element->hikashop_params['selectparentlisting'])){
			$db 	=& JFactory::getDBO();
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'root\' AND category_parent_id=0 LIMIT 1';
			$db->setQuery($query);
			$root = $db->loadResult();
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'product\' AND category_parent_id='.$root.' LIMIT 1';
			$db->setQuery($query);
			$element->hikashop_params['selectparentlisting'] = $db->loadResult();
		}
		if(!empty($element->hikashop_params['selectparentlisting'])){
			$class=hikashop_get('class.category');
			$element->category = $class->get($element->hikashop_params['selectparentlisting']);
		}
	}
	function _assignTypes(){
		$js = "
		var old_value_layout = '';
		var old_value_content = '';
		function switchPanel(name,options,type){
			var len = options.length;
			if(type=='layout'){
				if(name=='table'){
					el4 = document.getElementById('content_select');
					if(el4 && (el4.value=='category' || el4.value=='manufacturer')){
						el5 = document.getElementById('layout_select');
						el5.value = old_value_layout;
						alert('".JText::_('CATEGORY_CONTENT_DOES_NOT_SUPPORT_TABLE_LAYOUT',true)."');
						return;
					}
				}
				el3 = document.getElementById('number_of_columns');
				if(el3){
					if(name=='table'){
						el3.style.display='none';
					}else{
						el3.style.display='';
					}
				}
			}else if(type=='content'){
				if(name=='manufacturer'){
					name = 'category';
				}
				if(name=='category'){
					el4 = document.getElementById('layout_select');
					if(el4 && el4.value=='table'){
						el5 = document.getElementById('content_select');
						el5.value = old_value_content;
						alert('".JText::_('CATEGORY_CONTENT_DOES_NOT_SUPPORT_TABLE_LAYOUT',true)."');
						return;
					}
				}
			}
			for (var i = 0; i < len; i++){
				var el = document.getElementById(type+'_'+options[i]);
				if(el) el.style.display='none';
			}
			if(type=='layout'){
				old_value_layout = name;
			}else{
				old_value_content = name;
			}
			var el2 = document.getElementById(type+'_'+name);
			if(el2) el2.style.display='block';
		}
		function switchDisplay(value,name,activevalue){
			var el = document.getElementById(name);
			if(el){
				if(value==activevalue){
					el.style.display='';
				}else{
					el.style.display='none';
				}
			}
		}
		";
		$document=& JFactory::getDocument();
		$document->addScriptDeclaration($js);
		JHTML::_('behavior.modal');
		$colorType = hikashop_get('type.color');
		$this->assignRef('colorType',$colorType);
		$listType = hikashop_get('type.list');
		$this->assignRef('listType',$listType);
		$contentType = hikashop_get('type.content');
		$this->assignRef('contentType',$contentType);
		$layoutType = hikashop_get('type.layout');
		$this->assignRef('layoutType',$layoutType);
		$orderdirType = hikashop_get('type.orderdir');
		$this->assignRef('orderdirType',$orderdirType);
		$orderType = hikashop_get('type.order');
		$this->assignRef('orderType',$orderType);
		$itemType = hikashop_get('type.item');
		$this->assignRef('itemType',$itemType);
		$childdisplayType = hikashop_get('type.childdisplay');
		$this->assignRef('childdisplayType',$childdisplayType);
		$pricetaxType = hikashop_get('type.pricetax');
		$this->assignRef('pricetaxType',$pricetaxType);
		$priceDisplayType = hikashop_get('type.pricedisplay');
		$this->assignRef('priceDisplayType',$priceDisplayType);
		$discountDisplayType = hikashop_get('type.discount_display');
		$this->assignRef('discountDisplayType',$discountDisplayType);
		$transition_effectType = hikashop::get('type.transition_effect');
		$this->assignRef('transition_effectType',$transition_effectType);
		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
	}
	function form(){
		$cid = hikashop_getCID('id');
		if(empty($cid)){
			$element = null;
			$config = hikashop_config();
			$element->hikashop_params = $config->get('default_params');
			$task='add';
			$control = 'config[menu_0]';
			$element->hikashop_params['link_to_product_page'] = '1';
			$element->hikashop_params['border_visible']= true;
		}else{
			$modulesClass = hikashop_get('class.menus');
			$element = $modulesClass->get($cid);
			$task='edit';
			$config = hikashop_config();
			$control = 'config[menu_'.$cid.']';
			if(strpos($element->link,'view=product')!==false){
				$element->hikashop_params['content_type'] = 'product';
			}elseif(empty($element->hikashop_params['content_type']) || !in_array($element->hikashop_params['content_type'],array('manufacturer','category'))){
				$element->hikashop_params['content_type'] = 'category';
			}
			$element->content_type = $element->hikashop_params['content_type'];
			if(!isset($element->hikashop_params['link_to_product_page'])){
				$element->hikashop_params['link_to_product_page'] = '1';
			}
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&cid[]='.$cid);
		$this->_loadCategory($element);
		$bar = & JToolBar::getInstance('toolbar');
		if(!empty($cid)){
			if(version_compare(JVERSION,'1.6','<')){
				$bar->appendButton( 'Link', 'upload', JText::_('JOOMLA_MENU_OPTIONS'), JRoute::_('index.php?option=com_menus&task=edit&cid[]='.$element->id) );
			}else{
				$bar->appendButton( 'Link', 'upload', JText::_('JOOMLA_MENU_OPTIONS'), JRoute::_('index.php?option=com_menus&task=item.edit&id='.$element->id) );
			}
		}
		$js="
		function setVisibleLayoutEffect(value){
			if(value==\"slider_vertical\" || value==\"slider_horizontal\"){
				document.getElementById('product_effect').style.display = '';
				document.getElementById('product_effect_duration').style.display = '';
			}else if(value==\"fade\"){
				document.getElementById('product_effect').style.display = 'none';
				document.getElementById('product_effect_duration').style.display = '';
			}else if(value==\"img_pane\"){
				document.getElementById('product_effect').style.display = 'none';
				document.getElementById('product_effect_duration').style.display = 'none';
			}else{
				document.getElementById('product_effect').style.display = 'none';
				document.getElementById('product_effect_duration').style.display = 'none';
			}
		}";
		$doc =& JFactory::getDocument();
	 	$doc->addScriptDeclaration($js);
		$this->assignRef('element',$element);
		$this->assignRef('control',$control);
		$this->_assignTypes();
	}
	function listing(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$database	=& JFactory::getDBO();
		if(version_compare(JVERSION,'1.6','<')){
			$query = 'SELECT id FROM '.hikashop_table('components',false).' WHERE link=\'option='.HIKASHOP_COMPONENT.'\' LIMIT 1';
			$database->setQuery($query);
			$filters = array('(componentid='.$database->loadResult().' OR (componentid=0 AND link LIKE \'%option='.HIKASHOP_COMPONENT.'%\'))','type=\'component\'');
			$searchMap = array('alias','link','name');
		}else{
			$query = 'SELECT extension_id FROM '.hikashop_table('extensions',false).' WHERE type=\'component\' AND element=\''.HIKASHOP_COMPONENT.'\' LIMIT 1';
			$database->setQuery($query);
			$filters = array('(component_id='.$database->loadResult().' OR (component_id=0 AND link LIKE \'%option='.HIKASHOP_COMPONENT.'%\'))','type=\'component\'','client_id=0');
			$searchMap = array('alias','link','title');
		}
		$filters[] = 'published>-2';
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped(JString::strtolower( $pageInfo->search ),true).'%\'';
			$filters[] =  implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE '. implode(' AND ',$filters);
		}else{
			$filters = '';
		}
		$query = ' FROM '.hikashop_table('menu',false).' '.$filters.$order;
		$database->setQuery('SELECT *'.$query);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$config =& hikashop_config();
		$unset=array();
		foreach($rows as $k => $row){
			if(strpos($row->link,'view=product')!==false  && strpos($row->link,'layout=show')===false){
				$rows[$k]->hikashop_params = $config->get('menu_'.$row->id);
				$rows[$k]->hikashop_params['content_type'] = 'product';
			}elseif(strpos($row->link,'view=category')!==false || strpos($row->link,'view=')===false){
				$rows[$k]->hikashop_params = $config->get('menu_'.$row->id);
				$rows[$k]->hikashop_params['content_type'] = 'category';
			}else{
				$unset[]=$k;
				continue;
			}
			if(empty($rows[$k]->hikashop_params)){
				$rows[$k]->hikashop_params = $config->get('default_params');
			}
			$rows[$k]->content_type = $rows[$k]->hikashop_params['content_type'];
		}
		foreach($unset as $u){
			unset($rows[$u]);
		}
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_menus_manage','all'));
		$this->assignRef('manage',$manage);
		if($manage){
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
		}
		if(hikashop_isAllowed($config->get('acl_menus_delete','all'))) JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
	}
}