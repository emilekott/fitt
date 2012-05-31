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
class CharacteristicViewCharacteristic extends JView{
	var $ctrl= 'characteristic';
	var $nameListing = 'CHARACTERISTICS';
	var $nameForm = 'CHARACTERISTICS';
	var $icon = 'characteristic';
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
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.characteristic_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$searchMap = array('a.characteristic_value','a.characteristic_id');
		$filters = array('a.characteristic_parent_id=0');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$query = ' FROM '.hikashop_table('characteristic').' AS a';
		if(!empty($filters)){
			$query.= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'characteristic_id');
		}
		$database->setQuery('SELECT count(*)'.$query);
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_characteristic_manage','all'));
		$this->assignRef('manage',$manage);
		if($this->manage){
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
		}
		if(hikashop_isAllowed($config->get('acl_characteristic_view','all'))) JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
	}
	function form(){
		$characteristic_id = $this->editpopup();
		if(!empty($characteristic_id)){
			$task='edit';
		}else{
			$task='add';
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&characteristic_id='.$characteristic_id);
		$bar = & JToolBar::getInstance('toolbar');
		JHTML::_('behavior.modal');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
	}
	function editpopup(){
		$characteristic_id = hikashop_getCID('characteristic_id');
		$class = hikashop_get('class.characteristic');
		if(!empty($characteristic_id)){
			$element = $class->get($characteristic_id,true);
			if($element && empty($element->characteristic_parent_id)){
				$database	=& JFactory::getDBO();
				$config =& hikashop_config();
				if($config->get('characteristics_values_sorting')=='old'){
					$order = 'characteristic_id ASC';
				}else{
					$order = 'characteristic_value ASC';
				}
				$query = 'SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = '.$characteristic_id.' ORDER BY '.$order;
				$database->setQuery($query);
				$element->values = $database->loadObjectList();
			}
		}else{
			$element = JRequest::getVar('fail');
			if(empty($element)){
				$element = null;
			}
		}
		$this->assignRef('element',$element);
		jimport('joomla.html.pane');
		$config =& hikashop_config();
		$multilang_display=$config->get('multilang_display','tabs');
		if($multilang_display=='popups') $multilang_display = 'tabs';
		$tabs	=& JPane::getInstance($multilang_display);
		$this->assignRef('tabs',$tabs);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_characteristic',@$element->characteristic_id,$element);
			$this->assignRef('transHelper',$transHelper);
		}
		$js = '
		function deleteRow(divName,inputName,rowName){
			var d = document.getElementById(divName);
			var olddiv = document.getElementById(inputName);
			if(d && olddiv){
				d.removeChild(olddiv);
				document.getElementById(rowName).style.display=\'none\';
			}
			return false;
		}
		';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		$this->assignRef('cid',$characteristic_id);
		$this->assignRef('translation',$translation);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		return $characteristic_id;
	}
	function addcharacteristic(){
		$element = JRequest::getInt( 'cid');
		$rows = array();
		if(!empty($element)){
			$database	=& JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_id ='.$element;
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			$document=& JFactory::getDocument();
			$id = JRequest::getInt('id');
			$js = "window.addEvent('domready', function() {
					window.top.deleteRow('characteristic_div_".$rows[0]->characteristic_id.'_'.$id."','characteristic[".$rows[0]->characteristic_id."][".$id."]','characteristic_".$rows[0]->characteristic_id.'_'.$id."');
					var dstTable = window.top.document.getElementById('characteristic_listing');
					var srcTable = document.getElementById('result');
					for (var c = 0,m=srcTable.rows.length;c<m;c++){
						var rowData = srcTable.rows[c].cloneNode(true);
						dstTable.appendChild(rowData);
					}
					try{	window.top.document.getElementById('sbox-window').close(); }catch(err){ window.top.SqueezeBox.close(); }
			});";
			$document->addScriptDeclaration($js);
		}
		$this->assignRef('rows',$rows);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
	}
	function selectcharacteristic(){
		$this->listing();
	}
	function usecharacteristic(){
		$characteristics = JRequest::getVar( 'cid', array(), '', 'array' );
		$rows = array();
		$js="try{	window.top.document.getElementById('sbox-window').close(); }catch(err){ window.top.SqueezeBox.close(); }";
		if(!empty($characteristics)){
			JArrayHelper::toInteger($characteristics);
			$database	=& JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_id IN ('.implode(',',$characteristics).') OR characteristic_parent_id IN ('.implode(',',$characteristics).') ORDER BY characteristic_value ASC';
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			if(!empty($rows)){
				$unsetList = array();
				foreach($rows as $key => $characteristic){
					if(!empty($characteristic->characteristic_parent_id)){
						$unsetList[]=$key;
						foreach($rows as $key2 => $characteristic2){
							if($characteristic->characteristic_parent_id==$characteristic2->characteristic_id){
								$rows[$key2]->values[$characteristic->characteristic_id]=$characteristic->characteristic_value;
								break;
							}
						}
					}
				}
				if(!empty($unsetList)){
					foreach($unsetList as $item){
						unset($rows[$item]);
					}
					$rows = array_values($rows);
				}
			}
			$js="
				var dstTable = window.top.document.getElementById('characteristic_listing');
				var srcTable = document.getElementById('result');
				for (var c = 0,m=srcTable.rows.length;c<m;c++){
					var rowData = srcTable.rows[c].cloneNode(true);
					dstTable.appendChild(rowData);
				}
				".$js;
		}
		$this->assignRef('rows',$rows);
		$document=& JFactory::getDocument();
		$js = "window.addEvent('domready', function() {".$js."});";
		$document->addScriptDeclaration($js);
		$characteristicHelper = hikashop_get('type.characteristic');
		$this->assignRef('characteristicHelper',$characteristicHelper);
	}
}