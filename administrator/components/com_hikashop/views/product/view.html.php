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
class ProductViewProduct extends JView
{
	var $type = 'main';
	var $ctrl= 'product';
	var $nameListing = 'PRODUCTS';
	var $nameForm = 'PRODUCTS';
	var $icon = 'generic';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('backend_listing','product',false);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.ordering','cmd' );
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
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type',0,'int');
		$pageInfo->filter->filter_id = $app->getUserStateFromRequest( $this->paramBase.".filter_id",'filter_id',0,'string');
		$pageInfo->filter->filter_product_type = $app->getUserStateFromRequest( $this->paramBase.".filter_product_type",'filter_product_type','main','word');
		$pageInfo->filter->filter_published = $app->getUserStateFromRequest( $this->paramBase.".filter_published",'filter_published',0,'int');
		$database	=& JFactory::getDBO();
		$filters = array();
		$searchMap = array('b.product_name','b.product_description','b.product_id','b.product_code');
		foreach($fields as $field){
			$searchMap[]='b.'.$field->field_namekey;
		}
		if($pageInfo->filter->filter_published==2){
			$filters[]='b.product_published=1';
		}elseif($pageInfo->filter->filter_published==1){
			$filters[]='b.product_published=0';
		}
		if(empty($pageInfo->filter->filter_id)|| !is_numeric($pageInfo->filter->filter_id)){
			$pageInfo->filter->filter_id='product';
			$class = hikashop_get('class.category');
			$class->getMainElement($pageInfo->filter->filter_id);
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!$selectedType){
			$filters[]='a.category_id='.(int)$pageInfo->filter->filter_id;
			$select='SELECT a.ordering, b.*';
		}else{
			$categoryClass = hikashop_get('class.category');
			$categoryClass->parentObject =& $this;
			$childs = $categoryClass->getChilds((int)$pageInfo->filter->filter_id,true,array(),'',0,0);
			$filter = 'a.category_id IN (';
			foreach($childs as $child){
				$filter .= $child->category_id.',';
			}
			$filters[]=$filter.(int)$pageInfo->filter->filter_id.')';
			$select='SELECT DISTINCT b.*';
		}
		if($pageInfo->filter->filter_product_type=='all'){
			if(!empty($pageInfo->filter->order->value)){
				$select.=','.$pageInfo->filter->order->value.' as sorting_column';
				$order = ' ORDER BY sorting_column '.$pageInfo->filter->order->dir;
			}
		}else{
			if(!empty($pageInfo->filter->order->value)){
				$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
			}
		}
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeProductListingLoad', array( & $filters, & $order, &$this) );
		if($pageInfo->filter->filter_product_type=='all'){
			$query = '( '.$select.' FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_id=b.product_id WHERE '.implode(' AND ',$filters).' AND b.product_id IS NOT NULL )
			UNION
					  ( '.$select.' FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_id=b.product_parent_id WHERE '.implode(' AND ',$filters).' AND b.product_parent_id IS NOT NULL ) ';
			$database->setQuery($query.$order,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		}else{
			$filters[]='b.product_type = '.$database->Quote($pageInfo->filter->filter_product_type);
			if($pageInfo->filter->filter_product_type!='variant'){
				$lf = 'a.product_id=b.product_id';
			}else{
				$lf = 'a.product_id=b.product_parent_id';
			}
			$query = ' FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON '.$lf.' WHERE '.implode(' AND ',$filters);
			$database->setQuery($select.$query.$order,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		}
		$rows = $database->loadObjectList();
		$fieldsClass->handleZoneListing($fields,$rows);
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'product_id');
		}
		if($pageInfo->filter->filter_product_type=='all'){
			$database->setQuery('SELECT COUNT(*) FROM ('.$query.') as u');
		}else{
			$database->setQuery('SELECT COUNT(DISTINCT(b.product_id))'.$query);
		}
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		if($pageInfo->elements->page){
			$this->_loadPrices($rows);
		}
		jimport('joomla.html.pagination');
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_product_manage','all'));
		$this->assignRef('manage',$manage);
		if(hikashop_level(1) && hikashop_isAllowed($config->get('acl_limit_view','all'))){
			$bar->appendButton( 'Link', 'limit', JText::_('LIMIT'), hikashop_completeLink('limit') );
		}
		$bar->appendButton( 'Standard', 'archive',JText::_('HIKA_EXPORT'), 'export', false, false );
		if($manage){
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			if(hikashop_level(2)){
				$bar->appendButton( 'Standard', 'copy',JText::_('HIKA_COPY'), 'copy', true, false );
			}
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
		}
		if(hikashop_isAllowed($config->get('acl_product_delete','all'))) JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		$config =& hikashop_config();
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$childClass = hikashop_get('type.childdisplay');
		$this->assignRef('childDisplay',$childClass->display('filter_type',$selectedType,false));
		$publishDisplay = hikashop_get('type.published');
		$this->assignRef('publishDisplay',$publishDisplay->display('filter_published',$pageInfo->filter->filter_published));
		$productClass = hikashop_get('type.product');
		$this->assignRef('productType',$productClass);
		$breadcrumbClass = hikashop_get('type.breadcrumb');
		$this->assignRef('breadCrumb',$breadcrumbClass->display('filter_id',$pageInfo->filter->filter_id,'product'));
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$doOrdering = !$selectedType;
		if($doOrdering && !(empty($pageInfo->filter->filter_product_type) || $pageInfo->filter->filter_product_type=='main')){
			$doOrdering=false;
		}
		$this->assignRef('doOrdering',$doOrdering);
		if($doOrdering){
			$order = null;
			$order->ordering = false;
			$order->orderUp = 'orderup';
			$order->orderDown = 'orderdown';
			$order->reverse = false;
			if($pageInfo->filter->order->value == 'a.ordering'){
				$order->ordering = true;
				if($pageInfo->filter->order->dir == 'desc'){
					$order->orderUp = 'orderdown';
					$order->orderDown = 'orderup';
					$order->reverse = true;
				}
			}
			$this->assignRef('order',$order);
		}
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$config =& hikashop_config();
		$this->assignRef('config',$config);
	}
	function form(){
		$product_id = hikashop_getCID('product_id');
		$class = hikashop_get('class.product');
		if(!empty($product_id)){
			$element = $class->get($product_id,true);
			$task='edit';
			if($element){
				$database	=& JFactory::getDBO();
				$query = 'SELECT b.* FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('category').' AS b ON a.category_id=b.category_id WHERE a.product_id = '.$product_id.' ORDER BY a.product_category_id';
				$database->setQuery($query);
				$element->categories = $database->loadObjectList();
				$query = 'SELECT a.*,b.* FROM '.hikashop_table('product_related').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_related_id=b.product_id WHERE a.product_related_type=\'related\' AND a.product_id = '.$product_id;
				$database->setQuery($query);
				$element->related = $database->loadObjectList();
				$query = 'SELECT a.*,b.* FROM '.hikashop_table('product_related').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_related_id=b.product_id WHERE a.product_related_type=\'options\' AND a.product_id = '.$product_id;
				$database->setQuery($query);
				$element->options = $database->loadObjectList();
				$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id = '.$product_id.' AND file_type=\'product\'';
				$database->setQuery($query);
				$element->images = $database->loadObjectList();
				$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id = '.$product_id.' AND file_type=\'file\'';
				$database->setQuery($query);
				$element->files = $database->loadObjectList('file_id');
				if(!empty($element->files)){
					$query = 'SELECT SUM(download_number) AS download_number,file_id FROM '.hikashop_table('download').' WHERE file_id IN ( '.implode(',',array_keys($element->files)).' ) GROUP BY file_id';
					$database->setQuery($query);
					$downloads = $database->loadObjectList('file_id');
					if(!empty($downloads)){
						foreach($downloads as $download){
							$element->files[$download->file_id]->download_number = $download->download_number;
						}
					}
				}
				if($element->product_type=='variant'){
					$query = 'SELECT b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE variant_product_id = '.$product_id;
					$database->setQuery($query);
					$characteristics = $database->loadObjectList('characteristic_parent_id');
				}else{
					$element->characteristics = $this->_getCharacteristics($product_id);
				}
				$ids = array($product_id);
				if(!empty($element->related)){
					foreach($element->related as $related){
						$ids[]=(int)@$related->product_id;
					}
				}
				if(!empty($element->options)){
					foreach($element->options as $optionElement){
						$ids[]=(int)@$optionElement->product_id;
					}
				}
				$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$ids).')';
				$database->setQuery($query);
				$prices = $database->loadObjectList();
				if(!empty($prices)){
					foreach($prices as $price){
						if($price->price_product_id==$product_id){
							$element->prices[]=$price;
						}
						if(!empty($element->related)){
							foreach($element->related as $k => $related){
								if($price->price_product_id==$related->product_id){
									$element->related[$k]->prices[]=$price;
									break;
								}
							}
						}
						if(!empty($element->options)){
							foreach($element->options as $k => $optionElement){
								if($price->price_product_id==$optionElement->product_id){
									$element->options[$k]->prices[]=$price;
									break;
								}
							}
						}
					}
				}
			}
		}else{
			$element = JRequest::getVar('fail');
			if(empty($element)){
				$element = null;
				$element->product_published=1;
				if(JRequest::getBool('variant')){
					$element->product_type = 'variant';
					$element->product_parent_id = JRequest::getInt('parent_id');
				}else{
					$element->product_type = 'main';
				}
				$element->product_quantity=-1;
				$categoryClass = hikashop_get('class.category');
				$mainTaxCategory = 'tax';
				$categoryClass->getMainElement($mainTaxCategory);
				$db = JFactory::getDBO();
				$db->setQuery('SELECT category_id FROM '. hikashop_table('category'). ' WHERE category_type=\'tax\' && category_parent_id='.(int)$mainTaxCategory.' ORDER BY category_id DESC');
				$element->product_tax_id = $db->loadResult();
			}
			$task='add';
		}
		$config =& hikashop_config();
		$main_currency = $config->get('main_currency',1);
		$currency = hikashop_get('type.currency');
		$this->assignRef('currency',$currency);
		$this->assignRef('config',$config);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		if($element->product_type=='variant'){
			$element->characteristics = $this->_getCharacteristics(@$element->product_parent_id);
			foreach($element->characteristics as $key => $characteristic){
				if(isset($characteristics[$characteristic->characteristic_id])){
					$element->characteristics[$key]->default_id=$characteristics[$characteristic->characteristic_id]->characteristic_id;
				}
			}
			$parentdata = $class->get($element->product_parent_id);
			$element->product_tax_id=$parentdata->product_tax_id;
		}
		if(!empty($element->product_tax_id)){
			$main_tax_zone = explode(',',$config->get('main_tax_zone',''));
			if(count($main_tax_zone)){
				$main_tax_zone = array_shift($main_tax_zone);
			}
		}
		if(!empty($element->prices)){
			$unset = array();
			foreach($element->prices as $key => $price){
				if(empty($price->price_value)){
					$unset[]=$key;
				}
			}
			foreach($unset as $u){
				unset($element->prices[$u]);
			}
			if(!empty($element->product_tax_id)){
				foreach($element->prices as $key => $price){
					$element->prices[$key]->price_value_with_tax = $currencyClass->getTaxedPrice($price->price_value,$main_tax_zone,$element->product_tax_id);
				}
			}else{
				foreach($element->prices as $key => $price){
					$element->prices[$key]->price_value_with_tax = $price->price_value;
				}
			}
		}
		if(empty($element->prices)){
			$obj = null;
			$obj->price_value=0;
			$obj->price_value_with_tax=0;
			$obj->price_currency_id = $main_currency;
			$element->prices = array($obj);
		}
		if($element->product_quantity==-1){
			$element->product_quantity=JText::_('UNLIMITED');
		}
		if(empty($element->product_max_per_order)){
			$element->product_max_per_order=JText::_('UNLIMITED');
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&product_id='.$product_id);
		$bar = & JToolBar::getInstance('toolbar');
		if(!empty($product_id)){
			if(version_compare(JVERSION,'1.6','<')){
				$bar->appendButton( 'Popup', 'upload', JText::_('ADD_TO_CART_HTML_CODE'), hikashop_completeLink('product&task=updatecart&cid='.$product_id,true), 320, 140);
			}else{
				$bar->appendButton( 'Popup', 'upload', JText::_('ADD_TO_CART_HTML_CODE'), 'index.php?option=com_hikashop&ctrl=product&task=updatecart&tmpl=component&cid='.$product_id, 320, 140);
			}
		}
		JToolBarHelper::divider();
		JToolBarHelper::save();
		if(version_compare(JVERSION,'1.7','>=')) JToolBarHelper::save2new();
		JToolBarHelper::apply();
		if(JRequest::getInt('variant')){
			$variant =1;
			$this->assignRef('variant',$variant);
			$bar->appendButton( 'Link', 'cancel', JText::_('HIKA_CANCEL'), hikashop_completeLink('product&task=variant&cid='.$element->product_parent_id) );
		}else{
			JToolBarHelper::cancel();
		}
		if($element->product_type=='variant'){
			$bar->appendButton( 'Link', 'forward', JText::_('GO_TO_MAIN_PRODUCT'), hikashop_completeLink('product&task=edit&cid='.$element->product_parent_id) );
		}
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
		$this->assignRef('element',$element);
		JHTML::_('behavior.modal');
		$type = 'tabs';
		if($config->get('multilang_display','tabs')!='popups'){
			$type = $config->get('multilang_display','tabs');
		}
		jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance($type);
		$this->assignRef('tabs',$tabs);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_product',@$element->product_id,$element);
			$this->assignRef('transHelper',$transHelper);
		}
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$js = '
		function deleteRow(divName,inputName,rowName,div1,input1,div2,input2){
			var d = document.getElementById(divName);
			var olddiv = document.getElementById(inputName);
			if(d && olddiv){
				d.removeChild(olddiv);
				document.getElementById(rowName).style.display=\'none\';
			}
			if(div1 && input1){
				deleteRow(div1,input1,rowName);
			}
			if(div2 && input2){
				deleteRow(div2,input2,rowName);
			}
			return false;
		}
		function updatePrice(divId,price,tax_id,conversion){
			try{
				new Ajax(\'index.php?option=com_hikashop&tmpl=component&ctrl='.$this->ctrl.'&task=getprice&price=\'+price+\'&tax_id=\'+tax_id+\'&conversion=\'+conversion, { method: \'get\', onComplete: function(result) {window.document.getElementById(divId).value = result;}}).request();
			}catch(err){
				new Request({url:\'index.php?option=com_hikashop&tmpl=component&ctrl='.$this->ctrl.'&task=getprice&price=\'+price+\'&tax_id=\'+tax_id+\'&conversion=\'+conversion,method: \'get\', onComplete: function(result) {window.document.getElementById(divId).value = result;}}).send();
			}
		}';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		$this->assignRef('translation',$translation);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'product_description';
		$editor->content = @$element->product_description;
		$editor->height=300;
		$this->assignRef('editor',$editor);
		$categoryType = hikashop_get('type.categorysub');
		$categoryType->type='tax';
		$categoryType->field='category_id';
		$this->assignRef('categoryType',$categoryType);
		$manufacturerType = hikashop_get('type.categorysub');
		$manufacturerType->type='manufacturer';
		$manufacturerType->field='category_id';
		$this->assignRef('manufacturerType',$manufacturerType);
		$weightType = hikashop_get('type.weight');
		$this->assignRef('weight',$weightType);
		$volumeType = hikashop_get('type.volume');
		$this->assignRef('volume',$volumeType);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$characteristicHelper = hikashop_get('type.characteristic');
		$this->assignRef('characteristicHelper',$characteristicHelper);
		$this->_addCustom($element);
	}
	function edit_translation(){
		$language_id = JRequest::getInt('language_id',0);
		$product_id = hikashop_getCID('product_id');
		$class = hikashop_get('class.product');
		$element = $class->get($product_id);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_product',@$element->product_id,$element,$language_id);
			$this->assignRef('transHelper',$transHelper);
		}
		$editor = hikashop_get('helper.editor');
		$editor->name = 'product_description';
		$editor->content = @$element->product_description;
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
		$fields = $fieldsClass->getFields('',$element,'product','field&task=state');
		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($fields,$element,0);
		$this->assignRef('fieldsClass',$fieldsClass);
		$this->assignRef('fields',$fields);
	}
	function _getCharacteristics($product_id){
		$database =& JFactory::getDBO();
		$query = 'SELECT a.ordering,b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id = '.(int)$product_id.' ORDER BY a.ordering';
		$database->setQuery($query);
		$characteristics = $database->loadObjectList();
		if(!empty($characteristics)){
			$unsetList = array();
			$ids = array();
			foreach($characteristics as $key => $characteristic){
				if(!empty($characteristic->characteristic_parent_id)){
					$unsetList[]=$key;
					foreach($characteristics as $key2 => $characteristic2){
						if($characteristic->characteristic_parent_id==$characteristic2->characteristic_id){
							$characteristics[$key2]->default_id=$characteristic->characteristic_id;
							break;
						}
					}
				}else{
					$ids[] = (int)$characteristic->characteristic_id;
				}
			}
			if(!empty($unsetList)){
				foreach($unsetList as $item){
					unset($characteristics[$item]);
				}
				$characteristics=array_values($characteristics);
			}
			if(!empty($ids)){
				$config =& hikashop_config();
				if($config->get('characteristics_values_sorting')=='old'){
					$order = 'characteristic_id ASC';
				}else{
					$order = 'characteristic_value ASC';
				}
				$query = 'SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id IN ('.implode(',',$ids).') ORDER BY '.$order;
				$database->setQuery($query);
				$values = $database->loadObjectList();
				if(!empty($values)){
					foreach($values as $value){
						foreach($characteristics as $key => $characteristic){
							if($value->characteristic_parent_id==$characteristic->characteristic_id){
								if(!isset($characteristics[$key]->values)){
									$characteristics[$key]->values=array();
								}
								$characteristics[$key]->values[$value->characteristic_id]=$value->characteristic_value;
								break;
							}
						}
					}
				}
			}
		}
		return $characteristics;
	}
	function _loadPrices(&$rows){
		$ids = array();
		foreach($rows as $row){
			$ids[]=(int)$row->product_id;
		}
		$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$ids).')';
		$database =& JFactory::getDBO();
		$database->setQuery($query);
		$prices = $database->loadObjectList();
		if(!empty($prices)){
			foreach($prices as $price){
				foreach($rows as $k => $row){
					if($price->price_product_id==$row->product_id){
						if(!isset($row->prices)) $row->prices=array();
						$rows[$k]->prices[]=$price;
						break;
					}
				}
			}
		}
	}
	function variant(){
		$app =& JFactory::getApplication();
		$database	=& JFactory::getDBO();
		$filters = array();
		$product_id = JRequest::getInt('parent_id');
		if(empty($product_id)){
			$product_id = hikashop_getCID('product_id');
		}
		$characteristics = false;
		$filters[]='a.variant_product_id = '.$product_id;
		$query = 'SELECT a.* FROM '.hikashop_table('variant').' AS a WHERE '.implode(' AND ',$filters);
		$database->setQuery($query);
		$variants = $database->loadObjectList();
		$bar = & JToolBar::getInstance('toolbar');
		if(count($variants)){
			$filters = array();
			$filters[]='a.product_parent_id = '.$product_id;
			$query = 'SELECT a.* FROM '.hikashop_table('product').' AS a WHERE '.implode(' AND ',$filters);
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			$characteristics = $this->_getCharacteristics($product_id);
			if(count($rows)){
				$this->_loadPrices($rows);
				$ids = array();
				foreach($rows as $row){
					$ids[]=$row->product_id;
				}
				$query = 'SELECT a.variant_product_id,b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE variant_product_id IN ('.implode(',',$ids).')';
				$database->setQuery($query);
				$variants = $database->loadObjectList();
				if(!empty($variants)){
					foreach($variants as $variant){
						foreach($rows as $k => $row){
							if($variant->variant_product_id==$row->product_id){
								$name = false;
								foreach($characteristics as $characteristic){
									if($characteristic->characteristic_id==$variant->characteristic_parent_id){
										$name = $characteristic->characteristic_value;
										break;
									}
								}
								if($name!==false){
									$rows[$k]->characteristics[$name]=$variant->characteristic_value;
								}
								break;
							}
						}
					}
				}
			}
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
			JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
			$this->assignRef('rows',$rows);
			$this->assignRef('characteristics',$characteristics);
		}else{
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_('CHARACTERISTICS_FIRST'));
		}
		hikashop_setTitle(JText::_('VARIANTS'),$this->icon,'product&task=variant&cid='.$product_id);
		$bar->appendButton( 'Link', 'cancel', JText::_('HIKA_CANCEL'), hikashop_completeLink('product&task=edit&cid='.$product_id) );
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','variant-listing');
		$config =& hikashop_config();
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$this->assignRef('product_id', $product_id);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);
	}
	function selectcategory(){
		$this->paramBase .= '_category';
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.category_ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type',0,'int');
		$pageInfo->filter->filter_id = $app->getUserStateFromRequest( $this->paramBase.".filter_id",'filter_id','product','string');
		$database	=& JFactory::getDBO();
		$searchMap = array('a.category_name','a.category_description','a.category_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$class = hikashop_get('class.category');
		$class->parentObject =& $this;
		$rows = $class->getChilds($pageInfo->filter->filter_id,$selectedType,$filters,$order,$pageInfo->limit->start,$pageInfo->limit->value,false);
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'category_id');
		}
		$database->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		$config =& hikashop_config();
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$childClass = hikashop_get('type.childdisplay');
		$this->assignRef('childDisplay',$childClass->display('filter_type',$selectedType,false));
		$breadcrumbClass = hikashop_get('type.breadcrumb');
		$this->assignRef('breadCrumb',$breadcrumbClass->display('filter_id',$pageInfo->filter->filter_id,'product'));
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
		$config =& hikashop_config();
		$this->assignRef('config',$config);
	}
	function addcategory(){
		$categories = JRequest::getVar( 'cid', array(), '', 'array' );
		$rows = array();
		if(!empty($categories)){
			JArrayHelper::toInteger($categories);
			$database	=& JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$categories).')';
			$database->setQuery($query);
			$rows = $database->loadObjectList();
		}
		$this->assignRef('rows',$rows);
		$document=& JFactory::getDocument();
		$js = "window.addEvent('domready', function() {
				var dstTable = window.top.document.getElementById('category_listing');
				var srcTable = document.getElementById('result');
				for (var c = 0,m=srcTable.rows.length;c<m;c++){
					var rowData = srcTable.rows[c].cloneNode(true);
					dstTable.appendChild(rowData);
				}
				try{
					window.top.document.getElementById('sbox-window').close();
				}catch(err){
					window.top.SqueezeBox.close();
				}
		});";
		$document->addScriptDeclaration($js);
	}
	function selectrelated(){
		$type = JRequest::getWord('select_type');
		$this->paramBase .= '_related_'.$type;
		$this->listing();
		$this->assignRef('type',$type);
	}
	function addrelated(){
		$elements = JRequest::getVar( 'cid', array(), '', 'array' );
		$type = JRequest::getWord('select_type');
		$this->assignRef('type',$type);
		$rows = array();
		if(!empty($elements)){
			JArrayHelper::toInteger($elements);
			$database	=& JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$elements).')';
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			if(!empty($rows)){
				$this->_loadPrices($rows);
			}
		}
		$this->assignRef('rows',$rows);
		$document=& JFactory::getDocument();
		switch($type){
			case 'discount':
			case 'limit':
			case 'field':
				$js = "window.addEvent('domready', function() {
						window.top.document.getElementById('product_id').innerHTML = document.getElementById('result').innerHTML;
						try{
							window.top.document.getElementById('sbox-window').close();
						}catch(err){
							window.top.SqueezeBox.close();
						}
				});";
				$document->addScriptDeclaration($js);
				$this->setLayout($type);
				break;
			case 'import':
				$js = "window.addEvent('domready', function() {
						window.top.document.getElementById('template_product').innerHTML = document.getElementById('result').innerHTML;
						try{
							window.top.document.getElementById('sbox-window').close();
						}catch(err){
							window.top.SqueezeBox.close();
						}
				});";
				$document->addScriptDeclaration($js);
				$this->setLayout('import');
				break;
			default:
				$js = "window.addEvent('domready', function() {
						var dstTable = window.top.document.getElementById('".$type."_listing');
						var srcTable = document.getElementById('result');
						for (var c = 0,m=srcTable.rows.length;c<m;c++){
							var rowData = srcTable.rows[c].cloneNode(true);
							dstTable.appendChild(rowData);
						}
						try{
							window.top.document.getElementById('sbox-window').close();
						}catch(err){
							window.top.SqueezeBox.close();
						}
				});";
				$document->addScriptDeclaration($js);
				$currencyClass = hikashop_get('class.currency');
				$this->assignRef('currencyHelper',$currencyClass);
				break;
		}
	}
	function selectimage(){
		$id = (int)hikashop_getCID( 'file_id');
		if(!empty($id)){
			$class = hikashop_get('class.file');
			$element = $class->get($id);
		}else{
			$element = null;
		}
		$this->assignRef('cid',$id);
		$this->assignRef('element',$element);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'file_description';
		$editor->content = @$element->file_description;
		$editor->height=200;
		$this->assignRef('editor',$editor);
	}
	function addimage(){
		$element = JRequest::getInt( 'cid');
		$rows = array();
		if(!empty($element)){
			$database	=& JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_id ='.$element;
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			$document=& JFactory::getDocument();
			$id = JRequest::getInt('id');
			$js = "window.addEvent('domready', function() {
					window.top.deleteRow('image_div_".$rows[0]->file_id.'_'.$id."','image[".$rows[0]->file_id."][".$id."]','image_".$rows[0]->file_id.'_'.$id."');
					var dstTable = window.top.document.getElementById('image_listing');
					var srcTable = document.getElementById('result');
					for (var c = 0,m=srcTable.rows.length;c<m;c++){
						var rowData = srcTable.rows[c].cloneNode(true);
						dstTable.appendChild(rowData);
					}
					try{
						window.top.document.getElementById('sbox-window').close();
					}catch(err){
						window.top.SqueezeBox.close();
					}
			});";
			$document->addScriptDeclaration($js);
		}
		$this->assignRef('rows',$rows);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
	}
	function selectfile(){
		$id = (int)hikashop_getCID( 'file_id');
		if(!empty($id)){
			$class = hikashop_get('class.file');
			$element = $class->get($id);
		}else{
			$element = null;
		}
		$this->assignRef('cid',$id);
		$this->assignRef('element',$element);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'file_description';
		$editor->content = @$element->file_description;
		$editor->height=200;
		$this->assignRef('editor',$editor);
	}
	function addfile(){
		$element = JRequest::getInt( 'cid');
		$rows = array();
		if(!empty($element)){
			$database	=& JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_id ='.$element;
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			$document=& JFactory::getDocument();
			$id = JRequest::getInt('id');
			$js = "window.addEvent('domready', function() {
					window.top.deleteRow('file_div_".$rows[0]->file_id.'_'.$id."','file[".$rows[0]->file_id."][".$id."]','file_".$rows[0]->file_id.'_'.$id."');
					var dstTable = window.top.document.getElementById('file_listing');
					var srcTable = document.getElementById('result');
					for (var c = 0,m=srcTable.rows.length;c<m;c++){
						var rowData = srcTable.rows[c].cloneNode(true);
						dstTable.appendChild(rowData);
					}
					try{
						window.top.document.getElementById('sbox-window').close();
					}catch(err){
						window.top.SqueezeBox.close();
					}
			});";
			$document->addScriptDeclaration($js);
		}
		$this->assignRef('rows',$rows);
	}
	function priceaccess(){
		$js = "
		function hikashopSetACL() {
			acl = document.getElementById('hidden_price_access');
			price = window.top.document.getElementById('price_access_".JRequest::getInt('id')."');
			if(acl && price){
				price.value = acl.value;
			}
			try{
				window.top.document.getElementById('sbox-window').close();
			}catch(err){
				window.top.SqueezeBox.close();
			}
		}";
		$document =& JFactory::getDocument();
		$document->addScriptDeclaration($js);
		$access = JRequest::getVar('access','');
		$this->assignRef('access',$access);
	}
	function export(){
		$product = hikashop_get('class.product');
		$products = JRequest::getVar( 'cid', array(), '', 'array' );
		$product->getProducts($products,'object');
		$products =& $product->all_products;
		if(!empty($products)){
			$currencies = array();
			foreach($products as $product){
				if(!empty($product->prices)){
					foreach($product->prices as $price){
						$currencies[$price->price_currency_id]=$price->price_currency_id;
					}
				}
			}
			if(!empty($currencies)){
				$currency = hikashop_get('class.currency');
				$null=null;
				$currencies = $currency->getCurrencies($currencies,$null);
			}
			$this->assignRef('currencies',$currencies);
		}
		$db =& JFactory::getDBO();
		$db->setQuery('SELECT * FROM '.hikashop_table('category').' AS a WHERE a.category_type=\'product\' ORDER BY a.category_left ASC');
		$categories = $db->loadObjectList('category_id');
		$db->setQuery('SELECT * FROM '.hikashop_table('file').' AS a WHERE a.file_type=\'category\' AND a.file_ref_id IN ('.implode(',',array_keys($categories)).')');
		$files = $db->loadObjectList('file_ref_id');
		foreach($categories as $id => $cat){
			if(isset($files[$id])){
				$categories[$id]->file_path=$files[$id]->file_path;
			}
		}
		$this->assignRef('categories',$categories);
		$this->assignRef('products',$products);
	}
}