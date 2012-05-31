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
class DiscountViewDiscount extends JView{
	var $type = '';
	var $ctrl= 'discount';
	var $nameListing = 'DISCOUNTS';
	var $nameForm = 'DISCOUNTS';
	var $icon = 'discount';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.discount_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->filter->filter_type = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type','','string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$searchMap = array('a.discount_code','a.discount_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$query = ' FROM '.hikashop_table('discount').' AS a';
		if(!empty($pageInfo->filter->filter_type)){
			switch($pageInfo->filter->filter_type){
				case 'all':
					break;
				default:
					$filters[] = 'a.discount_type = '.$database->Quote($pageInfo->filter->filter_type);
					break;
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
			$rows = hikashop_search($pageInfo->search,$rows,'discount_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		if($pageInfo->elements->page){
			$productIds = array();
			$categoryIds = array();
			$zoneIds = array();
			foreach($rows as $row){
				if(!empty($row->discount_product_id)) $productIds[] = $row->discount_product_id;
				if(!empty($row->discount_category_id)) $categoryIds[] = $row->discount_category_id;
				if(!empty($row->discount_zone_id)) $zoneIds[] = $row->discount_zone_id;
			}
			if(!empty($productIds)){
				$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$productIds).')';
				$database->setQuery($query);
				$products = $database->loadObjectList();
				foreach($rows as $k => $row){
					if(!empty($row->discount_product_id)){
						$found = false;
						foreach($products as $product){
							if($product->product_id==$row->discount_product_id){
								foreach(get_object_vars($product) as $field => $value){
									$rows[$k]->$field = $product->$field;
								}
								$found = true;
							}
						}
						if(!$found){
							$rows[$k]->product_name=JText::_('PRODUCT_NOT_FOUND');
						}
					}
				}
			}
			if(!empty($categoryIds)){
				$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$categoryIds).')';
				$database->setQuery($query);
				$categories = $database->loadObjectList();
				foreach($rows as $k => $row){
					if(!empty($row->discount_category_id)){
						$found = false;
						foreach($categories as $category){
							if($category->category_id==$row->discount_category_id){
								foreach(get_object_vars($category) as $field => $value){
									$rows[$k]->$field = $category->$field;
								}
								$found = true;
							}
						}
						if(!$found){
							$rows[$k]->category_name=JText::_('CATEGORY_NOT_FOUND');
						}
					}
				}
			}
			if(!empty($zoneIds)){
				$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_id IN ('.implode(',',$zoneIds).')';
				$database->setQuery($query);
				$zones = $database->loadObjectList();
				foreach($rows as $k => $row){
					if(!empty($row->discount_zone_id)){
						$found = false;
						foreach($zones as $zone){
							if($zone->zone_id==$row->discount_zone_id){
								foreach(get_object_vars($zone) as $field => $value){
									$rows[$k]->$field = $zone->$field;
								}
								$found = true;
							}
						}
						if(!$found){
							$rows[$k]->zone_name_english=JText::_('ZONE_NOT_FOUND');
						}
					}
				}
			}





		}
		jimport('joomla.html.pagination');
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_discount_manage','all'));
		$this->assignRef('manage',$manage);
		if($manage){
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
		}
		if(hikashop_isAllowed($config->get('acl_discount_delete','all'))) JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$discountType = hikashop_get('type.discount');
		$this->assignRef('filter_type',$discountType);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
	}
	function form(){
		$discount_id = hikashop_getCID('discount_id',false);
		if(!empty($discount_id)){
			$class = hikashop_get('class.discount');
			$element = $class->get($discount_id);
			$task='edit';
		}else{
			$element = JRequest::getVar('fail');
			if(empty($element)){
				$element = null;
				$app =& JFactory::getApplication();
				$type = $app->getUserState( $this->paramBase.".filter_type");
				if(!in_array($type,array('all','nochilds'))){
					$element->discount_type = $type;
				}else{
					$element->discount_type = 'discount';
				}
				$element->discount_published=1;
			}
			$task='add';
		}
		$database =& JFactory::getDBO();
		if(!empty($element->discount_product_id)){
			$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id = '.(int)$element->discount_product_id;
			$database->setQuery($query);
			$product = $database->loadObject();
			if(!empty($product)){
				foreach(get_object_vars($product) as $key => $val){
					$element->$key = $val;
				}
			}
		}
		if(empty($element->product_name)){
			$element->product_name = JText::_('PRODUCT_NOT_FOUND');
		}
		if(!empty($element->discount_category_id)){
			$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_id = '.(int)$element->discount_category_id;
			$database->setQuery($query);
			$category = $database->loadObject();
			if(!empty($category)){
				foreach(get_object_vars($category) as $key => $val){
					$element->$key = $val;
				}
			}
		}
		if(empty($element->category_name)){
			$element->category_name = JText::_('CATEGORY_NOT_FOUND');
		}
		if(!empty($element->discount_zone_id)){
			$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_id = '.(int)$element->discount_zone_id;
			$database->setQuery($query);
			$zone = $database->loadObject();
			if(!empty($zone)){
				foreach(get_object_vars($zone) as $key => $val){
					$element->$key = $val;
				}
			}
		}
		if(empty($element->zone_name_english)){
			$element->zone_name_english = JText::_('ZONE_NOT_FOUND');
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&discount_id='.$discount_id);
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		if(version_compare(JVERSION,'1.7','>=')) JToolBarHelper::save2new();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
		$discountType = hikashop_get('type.discount');
		$this->assignRef('element',$element);
		$this->assignRef('type',$discountType);
		$currencyType = hikashop_get('type.currency');
		$this->assignRef('currency',$currencyType);
		$categoryType = hikashop_get('type.categorysub');
		$categoryType->type='tax';
		$categoryType->field='category_id';
		$this->assignRef('categoryType',$categoryType);
		JHTML::_('behavior.modal');
	}
}