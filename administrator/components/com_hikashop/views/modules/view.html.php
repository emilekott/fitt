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
class ModulesViewModules extends JView{
	var $include_module = false;
	var $ctrl= 'modules';
	var $nameListing = 'MODULES';
	var $nameForm = 'MODULE';
	var $icon = 'module';
	function display($tpl = null,$params=null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function($params);
		parent::display($tpl);
	}
	function form(){
		$cid = hikashop_getCID('id');
		if(empty($cid)){
			$element = null;
			$config = hikashop_config();
			$element->hikashop_params = $config->get('default_params');
			$element->position 	= 'left';
			$element->showtitle = true;
			$element->published = 1;
			$element->module 	= 'mod_hikashop';
			$element->hikashop_params['link_to_product_page'] = '1';
			$element->hikashop_params['transition_effect'] = 'quad';
			$element->hikashop_params['carousel_effect_duration'] = 800;
			$element->hikashop_params['one_by_one'] = true;
			$element->hikashop_params['auto_slide'] = true;
			$element->hikashop_params['auto_slide_duration'] = 1800;
			$element->hikashop_params['pagination_type'] = 'dot';
			$element->hikashop_params['pagination_position'] = 'bottom';
			$element->hikashop_params['display_button'] = true;
			$element->hikashop_params['border_visible'] = true;
			$control = 'config[params_0]';
			$task='add';
		}else{
			$modulesClass = hikashop_get('class.modules');
			$element = $modulesClass->get($cid);
			$control = 'config[params_'.$cid.']';
			$task='edit';
			if(!isset($element->hikashop_params['link_to_product_page'])){
				$element->hikashop_params['link_to_product_page'] = '1';
			}
		}
		$this->_loadCategory($element);
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&cid[]='.$cid);
		$bar = & JToolBar::getInstance('toolbar');
		if(!empty($cid)){
			if(version_compare(JVERSION,'1.6','<')){
				$bar->appendButton( 'Link', 'upload', JText::_('JOOMLA_MODULE_OPTIONS'), JRoute::_('index.php?option=com_modules&client=0&task=edit&cid[]='.$element->id) );
			}else{
				$bar->appendButton( 'Link', 'upload', JText::_('JOOMLA_MODULE_OPTIONS'), JRoute::_('index.php?option=com_modules&task=module.edit&id='.$element->id) );
			}
		}
		$this->assignRef('element',$element);
		$this->assignRef('control',$control);
		$js = null;
		$jsHide="
		function setVisible(value){
			value=parseInt(value);
			if(value==1){
				document.getElementById('carousel_type').style.display = '';
				document.getElementById('slide_direction').style.display = '';
				document.getElementById('transition_effect').style.display = '';
				document.getElementById('carousel_effect_duration').style.display = '';
				document.getElementById('product_by_slide').style.display = '';
				document.getElementById('slide_one_by_one').style.display = '';
				document.getElementById('auto_slide').style.display = '';
				document.getElementById('auto_slide_duration').style.display = '';
				document.getElementById('slide_pagination').style.display = '';
				document.getElementById('pagination_width').style.display = '';
				document.getElementById('pagination_height').style.display = '';
				document.getElementById('pagination_position').style.display = '';
				document.getElementById('display_button').style.display = '';
			}
			else{
				document.getElementById('carousel_type').style.display = 'none';
				document.getElementById('slide_direction').style.display = 'none';
				document.getElementById('transition_effect').style.display = 'none';
				document.getElementById('carousel_effect_duration').style.display = 'none';
				document.getElementById('product_by_slide').style.display = 'none';
				document.getElementById('slide_one_by_one').style.display = 'none';
				document.getElementById('auto_slide').style.display = 'none';
				document.getElementById('auto_slide_duration').style.display = 'none';
				document.getElementById('slide_pagination').style.display = 'none';
				document.getElementById('pagination_width').style.display = 'none';
				document.getElementById('pagination_height').style.display = 'none';
				document.getElementById('pagination_position').style.display = 'none';
				document.getElementById('display_button').style.display = 'none';
			}
		}
		function setVisibleAutoSlide(value){
			value=parseInt(value);
			if(value==1){
				document.getElementById('auto_slide_duration').style.display = '';
			}else{
				document.getElementById('auto_slide_duration').style.display = 'none';
			}
		}
		function setVisiblePagination(value){
			if(value==\"no_pagination\"){
				document.getElementById('pagination_width').style.display = 'none';
				document.getElementById('pagination_height').style.display = 'none';
				document.getElementById('pagination_position').style.display = 'none';
			}else if(value==\"thumbnails\"){
				document.getElementById('pagination_width').style.display = '';
				document.getElementById('pagination_height').style.display = '';
				document.getElementById('pagination_position').style.display = '';
			}else{
				document.getElementById('pagination_width').style.display = 'none';
				document.getElementById('pagination_height').style.display = 'none';
				document.getElementById('pagination_position').style.display = '';
			}
		}
		function setVisibleEffect(value){
			if(value==\"fade\"){
				document.getElementById('transition_effect').style.display = 'none';
				document.getElementById('slide_one_by_one').style.display = 'none';
			}else{
				document.getElementById('transition_effect').style.display = '';
				document.getElementById('slide_one_by_one').style.display = '';
			}
		}
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
		}
		";
	 	$doc =& JFactory::getDocument();
	 	$doc->addScriptDeclaration($jsHide);
		$this->assignRef('js',$js);
		$this->_assignTypes();
	}
	function _loadCategory(&$element){
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
		function hikashopToggleCart(minicart){
			if(minicart>0){
			 displayStatus ='none';
			}else{
			 displayStatus = '';
			}
			var el = document.getElementById('cart_price');
			if(el){ el.style.display=displayStatus; }
			var el = document.getElementById('cart_proceed');
			if(el){ el.style.display=displayStatus; }
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
		$effectType = hikashop::get('type.effect');
		$this->assignRef('effectType',$effectType);
		$directionType = hikashop::get('type.direction');
		$this->assignRef('directionType',$directionType);
		$transition_effectType = hikashop::get('type.transition_effect');
		$this->assignRef('transition_effectType',$transition_effectType);
		$slide_paginationType = hikashop::get('type.slide_pagination');
		$this->assignRef('slide_paginationType',$slide_paginationType);
		$positionType = hikashop::get('type.position');
		$this->assignRef('positionType',$positionType);
		$childdisplayType = hikashop_get('type.childdisplay');
		$this->assignRef('childdisplayType',$childdisplayType);
		$pricetaxType = hikashop_get('type.pricetax');
		$this->assignRef('pricetaxType',$pricetaxType);
		$priceDisplayType = hikashop_get('type.pricedisplay');
		$this->assignRef('priceDisplayType',$priceDisplayType);
		$productSyncType = hikashop_get('type.productsync');
		$this->assignRef('productSyncType',$productSyncType);
		$discountDisplayType = hikashop_get('type.discount_display');
		$this->assignRef('discountDisplayType',$discountDisplayType);
		if(version_compare(JVERSION,'1.6','<')){
			$query = 'SELECT a.name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype ORDER BY b.title ASC,a.ordering ASC';
		}else{
			$query = 'SELECT a.title as name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.client_id=0 ORDER BY b.title ASC,a.ordering ASC';
		}
		$db 	=& JFactory::getDBO();
		$db->setQuery($query);
		$joomMenus = $db->loadObjectList();
		$menuvalues = array();
		$menuvalues[] = JHTML::_('select.option', '0',JText::_('HIKA_NONE'));
		$lastGroup = '';
		foreach($joomMenus as $oneMenu){
			if($oneMenu->title != $lastGroup){
				if(!empty($lastGroup)) $menuvalues[] = JHTML::_('select.option', '</OPTGROUP>');
				$menuvalues[] = JHTML::_('select.option', '<OPTGROUP>',$oneMenu->title);
				$lastGroup = $oneMenu->title;
			}
			$menuvalues[] = JHTML::_('select.option', $oneMenu->itemid,$oneMenu->name);
		}
		$this->assignRef('hikashop_menu',$menuvalues);
		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
	}





	function listing(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$filters = array('(module = \'mod_hikashop\' OR module = \'mod_hikashop_cart\')');
		$searchMap = array('module','title');
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
		$query = ' FROM '.hikashop_table('modules',false).' '.$filters.$order;
		$database->setQuery('SELECT *'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$config =& hikashop_config();
		foreach($rows as $k => $row){
			$rows[$k]->hikashop_params = $config->get('params_'.$row->id);
			if(empty($rows[$k]->hikashop_params)){
				$rows[$k]->hikashop_params = $config->get('default_params');
			}
		}
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_modules_manage','all'));
		$this->assignRef('manage',$manage);
		if($manage){
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
		}
		if(hikashop_isAllowed($config->get('acl_modules_delete','all'))) JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
	}
	function selectmodules(){
		$this->modules = JRequest::getString('modules','');
		$query='SELECT * FROM '.hikashop_table('modules',false). ' WHERE module IN (\'mod_hikashop\')';
		$this->database =& JFactory::getDBO();
		$this->database->setQuery($query);
		$rows = $this->database->loadObjectList();
		if(!empty($this->modules)){
			$this->modules=explode(',',$this->modules);
			JArrayHelper::toInteger($this->modules);
			foreach($this->modules as $i=>$id){
				foreach($rows as $k => $row){
					if($row->id==$id){
						$rows[$k]->module_ordering = $i+1;
						$rows[$k]->module_used = 1;
						break;
					}
				}
			}
		}
		foreach(get_object_vars($this) as $key => $var){
			$this->assignRef($key,$this->$key);
		}
		$this->assignRef('rows',$rows);
	}
	function savemodules(){
		$modules = array();
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['module']['used'] as $id => $used){
			if((bool)$used){
				$modules[$formData['module']['ordering'][$id]]=$id;
			}
		}
		if(!empty($modules)){
			ksort($modules);
			$modules = array_values($modules);
		}
		$this->assignRef('modules',$modules);
		$control = JRequest::getString('control','');
		$name = JRequest::getString('name','');
		if(empty($control) || empty($name)){
			$id = 'modules_display';
		}else{
			$id = $control.$name;
		}
		$document=& JFactory::getDocument();
		$js = "window.addEvent('domready', function() {
				window.top.document.getElementById('".$id."').value = document.getElementById('result').innerHTML;
				try{	window.top.document.getElementById('sbox-window').close(); }catch(err){ window.top.SqueezeBox.close(); }
		});";
		$document->addScriptDeclaration($js);
	}
}