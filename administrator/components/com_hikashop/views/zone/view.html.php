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
class ZoneViewZone extends JView
{
	var $type = '';
	var $ctrl= 'zone';
	var $nameListing = 'ZONES';
	var $nameForm = 'ZONES';
	var $icon = 'langmanager';
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
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.zone_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		$selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type','','string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(JRequest::getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$database	=& JFactory::getDBO();
		$searchMap = array('a.zone_code_3','a.zone_code_2','a.zone_name_english','a.zone_name','a.zone_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$query = ' FROM '.hikashop_table('zone').' AS a';
		if(!empty($selectedType)){
			$filters[] = 'a.zone_type = '.$database->Quote($selectedType);
			if($selectedType=='state'){
				$selectedCountry = $app->getUserStateFromRequest( $this->paramBase.".filter_country",'filter_country',0,'int');
				if($selectedCountry){
					$query = ' FROM '.hikashop_table('zone').' AS c LEFT JOIN '.hikashop_table('zone_link') .' AS b ON c.zone_namekey=b.zone_parent_namekey LEFT JOIN '.hikashop_table('zone').' AS a ON b.zone_child_namekey=a.zone_namekey';
					$filters[] = 'c.zone_id = '.$database->Quote($selectedCountry);
				}
			}
		}
		if(!empty($filters)){
			$query.= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'zone_id');
		}
		$database->setQuery('SELECT count(*)'.$query);
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_zone_manage','all'));
		$this->assignRef('manage',$manage);
		if($manage){
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::divider();
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
		}
		if(hikashop_isAllowed($config->get('acl_zone_delete','all'))) 	JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		$config =& hikashop_config();
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$filters = null;
		$zoneType = hikashop_get('type.zone');
		$filters->type = $zoneType->display('filter_type',$selectedType);
		if($selectedType=='state'){
			$countryType = hikashop_get('type.country');
			$filters->country = $countryType->display('filter_country',$selectedCountry);
		}else{
			$filters->country = '';
		}
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('filters',$filters);
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
	}
	function selectchildlisting(){
		$this->paramBase .= '_child';
		$this->listing();
		$control=JRequest::getWord('type');
		$this->assignRef('type',$control);
		$subcontrol=JRequest::getVar('subtype');
		$this->assignRef('subtype',$subcontrol);
		$map=JRequest::getVar('map');
		$this->assignRef('map',$map);
	}
	function form(){
		$zone_id = hikashop_getCID('zone_id',false);
		if(!empty($zone_id)){
			$class = hikashop_get('class.zone');
			$element = $class->get($zone_id);
			$task='edit';
		}else{
			$element = JRequest::getVar('fail');
			if(empty($element)){
				$element = null;
				$app =& JFactory::getApplication();
				$element->zone_type = $app->getUserState( $this->paramBase.".filter_type");
			}
			$task='add';
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&zone_id='.$zone_id);
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		if(version_compare(JVERSION,'1.7','>=')) JToolBarHelper::save2new();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
		$zoneType = hikashop_get('type.zone');
		$this->assignRef('element',$element);
		$this->assignRef('type',$zoneType);
		$control=JRequest::getWord('type');
		$this->assignRef('control',$control);
		$this->_childZones($zone_id,@$element->zone_namekey);
	}
	function newchildform(){
		$element = null;
		$app =& JFactory::getApplication();
		$this->paramBase .= '_child';
		$main_id = JRequest::getInt('main_id');
		if(!empty($main_id)){
			$zoneClass = hikashop_get('class.zone');
			$parent = $zoneClass->get($main_id);
			if($parent->zone_type=='country'){
				$element->zone_type='state';
			}else{
				$element->zone_type='country';
			}
		}else{
			$element->zone_type = $app->getUserState( $this->paramBase.".filter_type");
		}
		$element->zone_published = 1;
		$zoneType = hikashop_get('type.zone');
		$this->assignRef('element',$element);
		$this->assignRef('type',$zoneType);
	}
	function savechild(){
		$document=& JFactory::getDocument();
		$database =& JFactory::getDBO();
		$id = JRequest::getInt( 'cid' );
		if(!empty($id)){
			$query = 'SELECT a.* FROM '.hikashop_table('zone').' AS a WHERE a.zone_id='.$id;
			$database->setQuery($query);
			$rows =  $database->loadObjectList();
		}else{
			$rows = array();
		}
		$this->assignRef('list',$rows);
		$this->assignRef('main_namekey',JRequest::getCmd('main_namekey'));
		$this->assignRef('toggleClass',hikashop_get('helper.toggle'));
		$document=& JFactory::getDocument();
		$js = "window.addEvent('domready', function() {
				var dstTable = window.top.document.getElementById('list_0_data');
				var srcTable = document.getElementById('result');
				for (var c = 0,m=srcTable.rows.length;c<m;c++){
					var rowData = srcTable.rows[c].cloneNode(true);
					dstTable.appendChild(rowData);
				}
				try{	window.top.document.getElementById('sbox-window').close(); }catch(err){ window.top.SqueezeBox.close(); }
		});";
		$document->addScriptDeclaration($js);
		$this->setLayout('newchild');
	}
	function newchild(){
		$document=& JFactory::getDocument();
		$database =& JFactory::getDBO();
		$childNamekeys = JRequest::getVar( 'cid', array(), '', 'array' );
		if(!empty($childNamekeys)){
			$query = 'SELECT a.* FROM '.hikashop_table('zone').' AS a WHERE a.zone_namekey  IN (';
			foreach($childNamekeys as $namekey){
				$query.=$database->Quote($namekey).',';
			}
			$query=rtrim($query,',').');';
			$database->setQuery($query);
			$rows =  $database->loadObjectList();
		}else{
			$rows = array();
		}
		$this->assignRef('list',$rows);
		$this->assignRef('main_namekey',JRequest::getCmd('main_namekey'));
		$this->assignRef('toggleClass',hikashop_get('helper.toggle'));
		$document=& JFactory::getDocument();
		$js = "window.addEvent('domready', function() {
				var dstTable = window.top.document.getElementById('list_0_data');
				var srcTable = document.getElementById('result');
				for (var c = 0,m=srcTable.rows.length;c<m;c++){
					var rowData = srcTable.rows[c].cloneNode(true);
					dstTable.appendChild(rowData);
				}
				try{	window.top.document.getElementById('sbox-window').close(); }catch(err){ window.top.SqueezeBox.close(); }
		});";
		$document->addScriptDeclaration($js);
	}
	function addchild(){
		$document=& JFactory::getDocument();
		$database =& JFactory::getDBO();
		$zone_id = hikashop_getCID( 'zone_id');
		if(!empty($zone_id)){
			$query = 'SELECT a.* FROM '.hikashop_table('zone').' AS a WHERE a.zone_id  ='.$zone_id;
			$database->setQuery($query);
			$element =  $database->loadObject();
		}else{
			$element = null;
		}
		if(empty($element->zone_name_english)){
			if(!empty($element->zone_name)){
				$element->zone_name_english = $element->zone_name;
			}else{
				$element->zone_name_english=JText::_('ZONE_NOT_FOUND');
			}
		}
		$subtype=JRequest::getVar('subtype');
		if(empty($subtype)){
			$subtype='zone_id';
		}
		$document=& JFactory::getDocument();
		$js = "window.addEvent('domready', function() {
					window.top.document.getElementById('".$subtype."').innerHTML = document.getElementById('result').innerHTML;
					try{	window.top.document.getElementById('sbox-window').close(); }catch(err){ window.top.SqueezeBox.close(); }
			});";
		$document->addScriptDeclaration($js);
		$this->assignRef('element',$element);
	}
	function _childZones($zone_id,$zone_namekey){
		$toggleClass = hikashop_get('helper.toggle');
		if(!empty($zone_id)){
			$database =& JFactory::getDBO();
			$query = 'SELECT a.* FROM '.hikashop_table('zone_link').' AS b LEFT JOIN '.hikashop_table('zone').' AS a ON b.zone_child_namekey=a.zone_namekey WHERE b.zone_parent_namekey  = '.$database->Quote($zone_namekey).' ORDER BY a.zone_id';
			$database->setQuery($query);
			$rows =  $database->loadObjectList();
			$this->assignRef('list',$rows);
			$this->assignRef('main_id',$zone_id);
			$this->assignRef('main_namekey',$zone_namekey);
			$this->assignRef('toggleClass',$toggleClass);
			JHTML::_('behavior.modal');
		}
		$toggleClass->addDeleteJS();
	}
}
