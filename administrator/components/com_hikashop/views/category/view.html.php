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
class CategoryViewCategory extends JView
{
	var $type = '';
	var $ctrl= 'category';
	var $nameListing = 'HIKA_CATEGORIES';
	var $nameForm = 'HIKA_CATEGORIES';
	var $icon = 'categories';
	function display($tpl = null)
	{
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('backend_listing','category',false);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.category_ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		if(JRequest::getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type',0,'int');
		$pageInfo->filter->filter_id = $app->getUserStateFromRequest( $this->paramBase.".filter_id",'filter_id',0,'string');
		$database	=& JFactory::getDBO();
		$searchMap = array('a.category_name','a.category_description','a.category_id');
		foreach($fields as $field){
			$searchMap[]='a.'.$field->field_namekey;
		}
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$category_image = $config->get('category_image',1);
		$class = hikashop_get('class.category');
		$type='';
		if(is_numeric($pageInfo->filter->filter_id)){
			$cat=$class->get($pageInfo->filter->filter_id);
			if(@$cat->category_type!='root'){
				$type=@$cat->category_type;
			}
		}else{
			$type=$pageInfo->filter->filter_id;
		}
		if($type=='tax'||$type=='status'){
			$category_image = false;
		}
		$this->assignRef('type',$type);
		$rows = $class->loadAllWithTrans($pageInfo->filter->filter_id,$pageInfo->selectedType,$filters,$order,$pageInfo->limit->start,$pageInfo->limit->value,$category_image);
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'category_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$class->query);
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		if($category_image){
			$image=hikashop_get('helper.image');
			$this->assignRef('image',$image);
		}
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$this->addHeader();
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$childClass = hikashop_get('type.childdisplay');
		$this->assignRef('childDisplay',$childClass->display('filter_type',$pageInfo->selectedType,false));
		$breadcrumbClass = hikashop_get('type.breadcrumb');
		$this->assignRef('breadCrumb',$breadcrumbClass->display('filter_id',$pageInfo->filter->filter_id,$type));
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$order = null;
		$order->ordering = false;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.category_ordering'){
			$order->ordering = true;
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}
		$this->assignRef('order',$order);
		$this->assignRef('category_image',$category_image);
	}
	function addHeader(){
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_category_manage','all'));
		$this->assignRef('manage',$manage);
		if($manage){
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
		}
		if(hikashop_isAllowed($config->get('acl_category_delete','all'))){
			JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		}
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
	}
	function selectstatus(){
		$class = hikashop_get('class.category');
		$rows = $class->loadAllWithTrans('status');
		$selected = JRequest::getVar('values','','','string');
		$selectedvalues = explode(',',$selected);
		$translated=false;
		if(!empty($rows)){
			foreach($rows as $id => $oneRow){
				if(in_array($oneRow->category_name,$selectedvalues)){
					$rows[$id]->selected = true;
				}
				if(isset($oneRow->translation)){
					$translated = true;
				}
			}
		}
		$this->assignRef('translated',$translated);
		$this->assignRef('rows',$rows);
		$controlName = JRequest::getString('control','');
		$this->assignRef('controlName',$controlName);
	}
	function selectparentlisting(){
		$this->paramBase .='_parent';
		$control = JRequest::getCmd('control');
		$id = JRequest::getCmd('id');
		$name = JRequest::getCmd('name');
		if(empty($id)){	$id='changeParent';	}
		if(!empty($control)){
			$js ='
			function changeParent(id,name){
				parent.document.getElementById("'.$id.'").innerHTML= id+" "+name;
				parent.document.getElementById("'.$control.'selectparentlisting").value=id;
			}';
		}else{
			$js ='
			function changeParent(id,name){
				parent.document.getElementById("'.$id.'").innerHTML= id+" "+name;
				var el = document.createElement("input");
				el.type = "hidden";
				el.name = "data[category][category_parent_id]";
				el.value = id;
				parent.document.getElementById("'.$id.'").appendChild(el);
			}';
		}
		$this->assignRef('control',$control);
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		$this->listing();
	}
	function form(){
		$category_id = hikashop_getCID('category_id');
		$class = hikashop_get('class.category');
		if(!empty($category_id)){
			$element = $class->get($category_id,true);
			$task='edit';
		}else{
			$element = JRequest::getVar('fail');
			if(empty($element)){
				$element = null;
				$element->category_published=1;
				$app =& JFactory::getApplication();
				$filter_id = $app->getUserStateFromRequest( $this->paramBase.".filter_id",'filter_id','','string');
				if(!is_numeric($filter_id)){
					$class->getMainElement($filter_id);
				}
				$element->category_parent_id=(int)$filter_id;
			}
			$task='add';
		}
		if($element->category_parent_id){
			$parentData = $class->get($element->category_parent_id);
			$element->category_parent_name = $parentData->category_name;
			if(empty($element->category_type)&&$parentData->category_type!='root'){
				$element->category_type=$parentData->category_type;
			}
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&category_id='.$category_id);
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		if(version_compare(JVERSION,'1.7','>=')) JToolBarHelper::save2new();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
		$this->_addCustom($element);
		$this->assignRef('element',$element);
		$categoryType = hikashop_get('type.category');
		$this->assignRef('categoryType',$categoryType);
		$mainCategory = $element->category_parent_id?0:1;
		$this->assignRef('mainCategory',$mainCategory);


		JHTML::_('behavior.modal');
		$config =& hikashop_config();
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_category',@$element->category_id,$element);
			$this->assignRef('transHelper',$transHelper);
		}
		jimport('joomla.html.pane');
		$multilang_display = $config->get('multilang_display','tabs');
		if($multilang_display=='popups') $multilang_display = 'tabs';
		$tabs	=& JPane::getInstance($multilang_display);
		$this->assignRef('tabs',$tabs);
		$this->assignRef('config',$config);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$this->assignRef('translation',$translation);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'category_description';
		$editor->content = @$element->category_description;
		$this->assignRef('editor',$editor);
		$category_image = $config->get('category_image',1);
		if($category_image){
			$image=hikashop_get('helper.image');
			$this->assignRef('image',$image);
		}
		if($element->category_type=='tax'||$element->category_type=='status'){
			$category_image = false;
		}
		$this->assignRef('category_image',$category_image);
	}
	function edit_translation(){
		$language_id = JRequest::getInt('language_id',0);
		$category_id = hikashop_getCID('category_id');
		$class = hikashop_get('class.category');
		$element = $class->get($category_id);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_category',@$element->category_id,$element,$language_id);
			$this->assignRef('transHelper',$transHelper);
		}
		$editor = hikashop_get('helper.editor');
		$editor->name = 'category_description';
		$editor->content = @$element->category_description;
		$editor->height=300;
		$this->assignRef('editor',$editor);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$this->assignRef('element',$element);
		jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance('tabs');
		$this->assignRef('tabs',$tabs);
	}
	function _addCustom(&$element){
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getFields('',$element,'category','field&task=state');
		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($fields,$element,0);
		$this->assignRef('fieldsClass',$fieldsClass);
		$this->assignRef('fields',$fields);
	}








}
