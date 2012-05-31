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
class ProductViewProduct extends JView{
	var $type = 'main';
	var $ctrl= 'product';
	var $nameListing = 'PRODUCTS';
	var $nameForm = 'PRODUCTS';
	var $icon = 'product';
	var $module=false;
	function display($tpl = null,$params=array()){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		$this->params =& $params;
		if($function!='cart' && JRequest::getInt('popup') && JRequest::getVar('tmpl')!='component'){
			$class = hikashop_get('helper.cart');
			$class->getJS($this->init());
			$doc =& JFactory::getDocument();
			$js = '
			window.addEvent(\'domready\', function() {
				SqueezeBox.fromElement(\'hikashop_notice_box_trigger_link\',{parse: \'rel\'});
			});
			';
			$doc->addScriptDeclaration("<!--\n".$js."\n//-->");
		}
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function filter(){
		if(!hikashop_level(2)) return true;
		$filterClass = hikashop_get('class.filter');
		$cart = hikashop_get('helper.cart');
		$displayedFilters = '';
		if(!empty($this->params) && $this->params->get('module') == 'mod_hikashop_filter'){
			$this->params->set('main_div_name','module_'.(int)$this->params->get('id'));
			$showButton=$this->params->get('show_filter_button',1);
			$maxColumn=$this->params->get('filter_column_number',1);
			$maxFilter=$this->params->get('filter_limit');
			$heightConfig=$this->params->get('filter_height',100);
			$displayFieldset=$this->params->get('display_fieldset',0);
			$buttonPosition=$this->params->get('filter_button_position','right');
			$displayedFilters=trim($this->params->get('filters'));
			if(!empty($displayedFilters)){
				$displayedFilters = explode(',',$displayedFilters);
			}
			$cid = 0;
			if(JRequest::getVar('option','')=='com_hikashop'){
				$cid = JRequest::getInt('cid');
				if($cid){
					if(JRequest::getVar('ctrl','product')!='product'){
						if(JRequest::getVar('ctrl','product')!='category' || JRequest::getVar('task','listing')!='listing'){
							$cid = 0;
						}
					}elseif(JRequest::getVar('task','listing')!='listing'){
						$cid = 0;
					}
				}elseif(in_array(JRequest::getVar('ctrl','product'),array('product','category'))&& JRequest::getVar('task','listing')=='listing'){
					global $Itemid;
					$menus	= &JSite::getMenu();
					$menu	= $menus->getActive();
					if(empty($menu)){
						if(!empty($Itemid)){
							$menus->setActive($Itemid);
							$menu	= $menus->getItem($Itemid);
						}
					}
					if(!empty($menu->id)){
						$menuClass = hikashop_get('class.menus');
						$menuData = $menuClass->get($menu->id);
						if(@$menuData->hikashop_params['content_type']=='manufacturer'){
							$new_id = 'manufacturer';
							$class = hikashop_get('class.category');
							$class->getMainElement($new_id);
							$menuData->hikashop_params['selectparentlisting']=$new_id;
						}
						if(!empty($menuData->hikashop_params['selectparentlisting'])){
							$cid = $menuData->hikashop_params['selectparentlisting'];
						}
					}
				}
			}
		}else{
			$cid = reset($this->pageInfo->filter->cid);
			$config =& hikashop_config();
			$showButton=$config->get('show_filter_button',1);
			$maxColumn=$config->get('filter_column_number',2);
			$maxFilter=$config->get('filter_limit');
			$heightConfig=$config->get('filter_height',100);
			$displayFieldset=$config->get('display_fieldset',1);
			$buttonPosition=$config->get('filter_button_position','right');
		}
		$filters=$filterClass->getFilters($cid);
		if(empty($maxFilter)){
			$maxFilter=count($filters)-1;
		}
		$this->assignRef('currentId',$cid);
		$this->assignRef('displayedFilters',$displayedFilters);
		$this->assignRef('cart',$cart);
		$this->assignRef('maxFilter',$maxFilter);
		$this->assignRef('maxColumn',$maxColumn);
		$this->assignRef('filters',$filters);
		$this->assignRef('filterClass',$filterClass);
		$this->assignRef('heightConfig',$heightConfig);
		$this->assignRef('showButton',$showButton);
		$this->assignRef('displayFieldset',$displayFieldset);
		$this->assignRef('buttonPosition',$buttonPosition);
	}
	function listing(){
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$module = hikashop_get('helper.module');
		$module->initialize($this);
		$this->paramBase.='_'.$this->params->get('main_div_name');
		$database	=& JFactory::getDBO();
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$filters = array('b.product_published=1');
		$category_selected='';
		$select='';
		$is_synchronized=false;
		$table='b';
		if($this->params->get('product_order')=='ordering'){
			$table='a';
		}
		if(!empty($this->module)){
			$pageInfo->filter->order->value =$table.'.'.$this->params->get('product_order');
			$pageInfo->search = '';
			$pageInfo->filter->order->dir = $this->params->get('order_dir');
			$synchro = $this->params->get('content_synchronize');
			if($synchro){
				if(JRequest::getString('option','')==HIKASHOP_COMPONENT){
					if(JRequest::getString('ctrl','category')=='product'){
						$ok = false;
						$product_synchronize = (int)$this->params->get('product_synchronize',0);
						if($product_synchronize){
							$product_id = hikashop_getCID('product_id');
							if(!empty($product_id)){
								if($product_synchronize==2){
									$filters[]='a.product_related_type=\'related\'';
									$filters[]='a.product_id='.$product_id;
									$select='SELECT DISTINCT b.*';
									$b = hikashop_table('product_related').' AS a LEFT JOIN ';
									$a = hikashop_table('product').' AS b';
									$on = ' ON a.product_related_id=b.product_id';
									if($this->params->get('product_order')=='ordering'){
										$pageInfo->filter->order->value = 'a.product_related_ordering';
									}
								}elseif($product_synchronize==3){
									$query = "SELECT product_manufacturer_id FROM ".hikashop_table('product').' WHERE product_id='.$product_id;
									$database->setQuery($query);
									$pageInfo->filter->cid = $database->loadResultArray();
								}elseif($product_synchronize==4){
									$filters[]='b.product_parent_id='.$product_id;
									$select='SELECT DISTINCT b.*';
									$b = '';
									$a = hikashop_table('product').' AS b';
									$this->type = 'variant';
								}else{
									$pathway = JRequest::getInt('category_pathway',0);
									if(empty($pathway)){
										$query = "SELECT category_id FROM ".hikashop_table('product_category').' WHERE product_id='.$product_id.' ORDER BY product_category_id ASC';
										$database->setQuery($query);
										$pageInfo->filter->cid = $database->loadResultArray();
									}else{
										$pageInfo->filter->cid = array($pathway);
									}
								}
								$ok = true;
							}
						}
						if(!$ok){
							$pageInfo->filter->cid = $this->params->get('selectparentlisting');
						}
					}elseif(JRequest::getString('ctrl','category')=='category'){
						$pageInfo->filter->cid = JRequest::getInt("cid",$this->params->get('selectparentlisting'));
						$is_synchronized=true;
					}else{
						$pageInfo->filter->cid = $this->params->get('selectparentlisting');
					}
				}else{
					$pageInfo->filter->cid = $this->params->get('selectparentlisting');
				}
			}else{
				$pageInfo->filter->cid = $this->params->get('selectparentlisting');
			}
			if(!empty($pageInfo->filter->cid) && !is_array($pageInfo->filter->cid)){
				$category_selected = '_'.$pageInfo->filter->cid;
				$this->paramBase.=$category_selected;
			}
			$pageInfo->filter->price_display_type = $this->params->get('price_display_type');
			if(JRequest::getVar('hikashop_front_end_main',0)){
				$oldValue = $app->getUserState($this->paramBase.'.list_limit');
				if(empty($oldValue)){
					$oldValue = $this->params->get('limit');
				}
				$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit_'.$this->params->get('main_div_name').$category_selected, $this->params->get('limit'), 'int' );
				if($oldValue!=$pageInfo->limit->value){
					JRequest::setVar('limitstart_'.$this->params->get('main_div_name').$category_selected,0);
				}
			}else{
				$pageInfo->limit->value = $this->params->get('limit');
				$pageInfo->limit->start = 0;
			}
		}else{
			$doc =& JFactory::getDocument();
			$pageInfo->filter->cid = JRequest::getInt("cid",$this->params->get('selectparentlisting'));
			if($config->get('show_feed_link', 1) == 1){
				if($config->get('hikarss_format') != 'none'){
					$doc_title = $config->get('hikarss_name','');
					if(empty($doc_title)){
						$category = hikashop_get('class.category');
						$catData = $category->get($pageInfo->filter->cid);
						if($catData) $doc_title = $catData->category_name;
					}
					if($config->get('hikarss_format') != 'both'){
						$link	= '&format=feed&limitstart=';
						$attribs = array('type' => 'application/rss+xml', 'title' => $doc_title.' RSS 2.0');
						$doc->addHeadLink(JRoute::_($link.'&type='.$config->get('hikarss_format')), 'alternate', 'rel', $attribs);
					}
					else{
						$link	= '&format=feed&limitstart=';
						$attribs = array('type' => 'application/rss+xml', 'title' => $doc_title.' RSS 2.0');
						$doc->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
						$attribs = array('type' => 'application/atom+xml', 'title' => $doc_title.' Atom 1.0');
						$doc->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
					}
				}
			}
			$category_selected = '_'.$pageInfo->filter->cid;
			$this->paramBase.=$category_selected;
			$pageInfo->filter->order->value = $table.'.'.$this->params->get('product_order');
			$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order_'.$this->params->get('main_div_name').$category_selected,	$pageInfo->filter->order->value,'cmd' );
			$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir_'.$this->params->get('main_div_name').$category_selected,	$this->params->get('order_dir'),	'word' );
			$oldValue = $app->getUserState($this->paramBase.'.list_limit');
			if(empty($oldValue)){
				$oldValue = $this->params->get('limit');
			}
			$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit_'.$this->params->get('main_div_name').$category_selected, $this->params->get('limit'), 'int' );
			if($oldValue!=$pageInfo->limit->value){
				JRequest::setVar('limitstart_'.$this->params->get('main_div_name').$category_selected,0);
			}
			$pageInfo->filter->price_display_type = $app->getUserStateFromRequest( $this->paramBase.'.price_display_type', 'price_display_type_'.$this->params->get('main_div_name').$category_selected, $this->params->get('price_display_type'), 'word' );
		}
		$this->assignRef('category_selected',$category_selected);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$pageInfo->currency_id = hikashop_getCurrency();
		$config =& hikashop_config();
		if($config->get('tax_zone_type','shipping')=='billing'){
			$pageInfo->zone_id = hikashop_getZone('billing');
		}else{
			$pageInfo->zone_id = hikashop_getZone('shipping');
		}
		$show_price_weight = (int)$config->get('show_price_weight',0);
		$this->params->set('show_price_weight',$show_price_weight);
		if(hikashop_level(2)){
			$this->params->set('show_compare',(int)$config->get('show_compare',0));
		} else {
			$this->params->set('show_compare',0);
		}
		if(!empty($pageInfo->filter->cid)){
			$acl_filters = array();
			hikashop_addACLFilters($acl_filters,'category_access');
			if(!empty($acl_filters)){
				if(!is_array($pageInfo->filter->cid)){
					$pageInfo->filter->cid = array($pageInfo->filter->cid);
				}
				$acl_filters[]='category_id IN ('.implode(',',$pageInfo->filter->cid).')';
				$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE '.implode(' AND ',$acl_filters);
				$database->setQuery($query);
				$pageInfo->filter->cid = $database->loadResultArray();
			}
		}
		if(empty($pageInfo->filter->cid)){
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'product\' AND category_parent_id=0 LIMIT 1';
			$database->setQuery($query);
			$pageInfo->filter->cid = $database->loadResult();
		}
		$searchMap = array('b.product_name','b.product_description','b.product_id','b.product_code');
		$filters[]='b.product_type = '.$database->Quote($this->type);
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		if(!is_array($pageInfo->filter->cid)){
			$pageInfo->filter->cid=array((int)$pageInfo->filter->cid);
		}
		$this->assignRef('pageInfo',$pageInfo);
		if(hikashop_level(2)) $this->filter();
		$class = hikashop_get('class.category');
		$element = $class->get(reset($pageInfo->filter->cid),true);
		$this->assignRef('element',$element);
		if(empty($select)){
			$parentCategories = implode(',',$pageInfo->filter->cid);
			$catName = 'a.category_id';
			$type = 'product';
			if(!empty($element->category_type) && $element->category_type=='manufacturer'){
				if($pageInfo->filter->order->value=='a.ordering'){
					$pageInfo->filter->order->value='b.product_name';
				}
				$type = 'manufacturer';
				$catName = 'b.product_manufacturer_id';
				$b = '';
				$a = hikashop_table('product').' AS b';
				$on = '';
				$select='SELECT DISTINCT b.*';
			}else{
				$b = hikashop_table('product_category').' AS a LEFT JOIN ';
				$a = hikashop_table('product').' AS b';
				$on = ' ON a.product_id=b.product_id';
				$select='SELECT DISTINCT b.*';
			}
			if(!$this->params->get('filter_type')){
				$filters[]=$catName.' IN ('.$parentCategories.')';
			}else{
				$categoryClass = hikashop_get('class.category');
				$categoryClass->parentObject =& $this;
				$categoryClass->type = $type;
				$childs = $categoryClass->getChilds($pageInfo->filter->cid,true,array(),'',0,0);
				$filter = $catName.' IN (';
				foreach($childs as $child){
					$filter .= $child->category_id.',';
				}
				$filters[]=$filter.$parentCategories.')';
			}
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if($this->params->get('add_to_cart')){
			$cart =& hikashop_get('helper.cart');
			$this->assignRef('cart',$cart);
			$catalogue = (int)$config->get('catalogue',0);
			$this->params->set('catalogue',$catalogue);
			$cart->cartCount(1);
			$cart->cartCount(1);
			$cart->getJS($this->init());
		}
		if(!$config->get('show_out_of_stock',1)){
			$filters[]='b.product_quantity!=0';
		}
		hikashop_addACLFilters($filters,'product_access','b');
		if($this->params->get('random')){
			$order = ' ORDER BY RAND()';






		}
		$select2='';
		if(hikashop_level(2) && JRequest::getVar('hikashop_front_end_main',0)){
			foreach($this->filters as $uniqueFitler){
				$this->filterClass->addFilter($uniqueFitler, $filters,$select,$select2, $a, $b, $on, $order, $this, $this->params->get('main_div_name'));
			}
		}
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeProductListingLoad', array( & $filters, & $order, &$this) );
		$query = $select2.' FROM '.$b.$a.$on.' WHERE '.implode(' AND ',$filters).$order;
		$this->assignRef('listingQuery', $query);
		if(!isset($pageInfo->limit->start)){
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart_'.$this->params->get('main_div_name').$category_selected, 0, 'int' );
		}
		$database->setQuery($select.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($rows)){
			$ids = array();
			foreach($rows as $key => $row){
				$ids[]=$row->product_id;
				if(method_exists($app,'stringURLSafe')){
					$rows[$key]->alias = $app->stringURLSafe(strip_tags($row->product_name));
				}else{
					$rows[$key]->alias = JFilterOutput::stringURLSafe(strip_tags($row->product_name));
				}
			}
			$queryImage = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\'product\' ORDER BY file_id ASC';
			$database->setQuery($queryImage);
			$images = $database->loadObjectList();
			foreach($rows as $k=>$row){
				if(!empty($images)){
					foreach($images as $image){
						if($row->product_id==$image->file_ref_id){
							if(!isset($row->file_ref_id)){
								foreach(get_object_vars($image) as $key => $name){
									$rows[$k]->$key = $name;
								}
							}
							break;
						}
					}
				}
				if(!isset($rows[$k]->file_name)){
					$rows[$k]->file_name = $row->product_name;
				}
			}
			$database->setQuery('SELECT * FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',',$ids).')');
			$variants = $database->loadObjectList();
			if(!empty($variants)){
				foreach($rows as $k => $product){
					foreach($variants as $variant){
						if($product->product_id==$variant->variant_product_id){
							$rows[$k]->has_options = true;
							break;
						}
					}
				}
			}
			$currencyClass->getListingPrices($rows,$pageInfo->zone_id,$pageInfo->currency_id,$pageInfo->filter->price_display_type);
		}
		$database->setQuery('SELECT count( DISTINCT b.product_id)'.$query);
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('modules',$this->modules);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		global $Itemid;
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();
		if(empty($menu)){
			if(!empty($Itemid)){
				$menus->setActive($Itemid);
				$menu	= $menus->getItem($Itemid);
			}
		}
		$url_itemid = '';
		if(!empty($Itemid)){
			$url_itemid = '&Itemid='.(int)$Itemid;
		}
		$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$pagination->hikaSuffix = '_'.$this->params->get('main_div_name').$category_selected;
		$this->assignRef('pagination',$pagination);
		if(empty($this->module)){
			$fieldsClass = hikashop_get('class.field');
			$fields = $fieldsClass->getFields('frontcomp',$element,'category','checkout&task=state');
			$this->assignRef('fieldsClass',$fieldsClass);
			$this->assignRef('fields',$fields);
			$title = $this->params->get('page_title');
			if(empty($title)){
				$title = $this->params->get('title');
			}
			$use_module = $this->params->get('use_module_name');
			if(empty($use_module) && !empty($element->category_name)){
				$title = $element->category_name;
			}
			if (empty($title)) {
				$page_title = $app->getCfg('sitename');
			}
			elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
				$page_title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
			}
			elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
				$page_title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
			}else{
				$page_title = $title;
			}
			$this->params->set('page_title',$title);
			$document	=& JFactory::getDocument();
			if(!empty($element->category_keywords)){
				$document->setMetadata('keywords', $element->category_keywords);
			}
			if(!empty($element->category_meta_description)){
				$document->setMetadata('description', $element->category_meta_description);
			}
			$document->setTitle(strip_tags($page_title));
			if(!$this->params->get('random')){
				$this->params->set('show_limit',1);
			}
			if(empty($menu)){
				$pathway =& $app->getPathway();
				$category_pathway = '&category_pathway='.JRequest::getVar('menu_main_category');
				$categories = $class->getParents(reset($pageInfo->filter->cid));
				$one = true;
				foreach($categories as $category){
					if($one){
						$one = false;
					}else{
						if(method_exists($app,'stringURLSafe')){
							$alias = $app->stringURLSafe(strip_tags($category->category_name));
						}else{
							$alias = JFilterOutput::stringURLSafe(strip_tags($category->category_name));
						}
						$pathway->addItem($category->category_name,hikashop_completeLink('category&task=listing&cid='.(int)$category->category_id.'&name='.$alias));
					}
				}
			}
		}else{
			$main = JRequest::getVar('hikashop_front_end_main',0);
			if($main){
				if(!empty($product_id)){
					$category_pathway = '&category_pathway='.JRequest::getInt('category_pathway',0).'&related_product='.$product_id;
				}
				if( !$this->params->get('random')){
					$this->params->set('show_limit',1);
				}
			}elseif(!$is_synchronized){
				$url_itemid='';
				$module_item_id = $this->params->get('itemid');
				$url_itemid='&Itemid='.(int)$module_item_id;
			}
			if(empty($category_pathway) && !empty($menu) && strpos($menu->link,'option='.HIKASHOP_COMPONENT)!==false && (strpos($menu->link,'view=category')!==false || strpos($menu->link,'view=')===false) && !JRequest::getInt('no_cid',0)){
				$category_pathway = '&category_pathway='.reset($pageInfo->filter->cid);
			}
		}
		if(empty($category_pathway)){
			$category_pathway = '&category_pathway=0';
		}
		$this->assignRef('itemid',$url_itemid);
		$this->assignRef('category_pathway',$category_pathway);
		$url = $this->init(true);
		$this->assignRef('redirect_url',$url);
	}
	function show(){
		$app =& JFactory::getApplication();
		$product_id = (int)hikashop_getCID('product_id');
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		global $Itemid;
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();
		if(empty($menu)){
			if(!empty($Itemid)){
				$menus->setActive($Itemid);
				$menu	= $menus->getItem($Itemid);
			}
		}
		if(empty($product_id)){
			if (is_object( $menu )) {
				jimport('joomla.html.parameter');
				$category_params = new JParameter( $menu->params );
				$product_id = $category_params->get('product_id');
				JRequest::setVar('product_id',$product_id);
			}
		}
		if(empty($product_id)){
			return;
		}
		$filters=array('a.product_id='.$product_id);
		hikashop_addACLFilters($filters,'product_access','a');
		$query = 'SELECT a.*,b.product_category_id, b.category_id, b.ordering FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
		$database =& JFactory::getDBO();
		$database->setQuery($query);
		$element = $database->loadObject();
		if(empty($element)){
			return;
		}
		$this->modules = $config->get('product_show_modules','');
		$module = hikashop_get('helper.module');
		$this->modules=$module->setModuleData($this->modules);
		$currencyClass = hikashop_get('class.currency');
		$default_params = $config->get('default_params');
		$empty='';
		jimport('joomla.html.parameter');
		$params = new JParameter($empty);
		foreach($default_params as $k => $param){
			$params->set($k,$param);
		}
		$main_currency = (int)$config->get('main_currency',1);
		$params->set('main_currency',$main_currency);
		$discount_before_tax = (int)$config->get('discount_before_tax',0);
		$params->set('discount_before_tax',$discount_before_tax);
		$catalogue = (int)$config->get('catalogue',0);
		$params->set('catalogue',$catalogue);
		$show_price_weight = (int)$config->get('show_price_weight',0);
		$params->set('show_price_weight',$show_price_weight);
		$currency_id = hikashop_getCurrency();
		if($config->get('tax_zone_type','shipping')=='billing'){
			$zone_id = hikashop_getZone('billing');
		}else{
			$zone_id = hikashop_getZone('shipping');
		}
		$params->set('characteristic_display',$config->get('characteristic_display','table'));
		$params->set('characteristic_display_text',$config->get('characteristic_display_text',1));
		$params->set('show_quantity_field',$config->get('show_quantity_field',1));
		$this->assignRef('params',$params);
		$cart = hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		$this->selected_variant_id=0;
		if($element->product_type=='variant'){
			$this->selected_variant_id = $product_id;
			$filters=array('a.product_id='.$element->product_parent_id);
			hikashop_addACLFilters($filters,'product_access','a');
			$query = 'SELECT a.*,b.* FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
			$database->setQuery($query);
			$element = $database->loadObject();
			if(empty($element)){
				return;
			}
			$product_id = $element->product_id;
		}
		if(method_exists($app,'stringURLSafe')){
			$element->alias = $app->stringURLSafe(strip_tags($element->product_name));
		}else{
			$element->alias = JFilterOutput::stringURLSafe(strip_tags($element->product_name));
		}
		if(!$element->product_published){
			return;
		}
		$productClass=hikashop_get('class.product');
		$prod = null;
		$prod->product_id = $product_id;
		$prod->product_hit = $element->product_hit+1;
		$prod->product_last_seen_date = time();
		$productClass->save($prod,true);
		$filters=array('a.product_id ='.$product_id,'a.product_related_type=\'options\'','b.product_published=1');
		hikashop_addACLFilters($filters,'product_access','b');
		$query = 'SELECT b.* FROM '.hikashop_table('product_related').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_related_id	= b.product_id WHERE '.implode(' AND ',$filters).' ORDER BY a.product_related_ordering ASC, a.product_related_id ASC';
		$database->setQuery($query);
		$element->options = $database->loadObjectList('product_id');
		$ids = array($product_id);
		if(!empty($element->options)){
			foreach($element->options as $optionElement){
				$ids[]=$optionElement->product_id;
			}
		}
		$filters=array('product_parent_id IN ('.implode(',',$ids).')');
		hikashop_addACLFilters($filters,'product_access');
		$query = 'SELECT * FROM '.hikashop_table('product').' WHERE '.implode(' AND ',$filters);
		$database->setQuery($query);
		$variants = $database->loadObjectList();
		if(!empty($variants)){
			foreach($variants as $variant){
				$ids[]=$variant->product_id;
				if($variant->product_parent_id==$product_id){
					$element->variants[$variant->product_id]=$variant;
				}
				if(!empty($element->options)){
					foreach($element->options as $k => $optionElement){
						if($variant->product_parent_id==$optionElement->product_id){
							$element->options[$k]->variants[$variant->product_id] = $variant;
							break;
						}
					}
				}
			}
		}
		if($config->get('characteristics_values_sorting')=='old'){
			$order = 'characteristic_id ASC';
		}else{
			$order = 'characteristic_value ASC';
		}
		$query = 'SELECT a.*,b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id IN ('.implode(',',$ids).') ORDER BY a.ordering ASC,b.'.$order;
		$database->setQuery($query);
		$characteristics = $database->loadObjectList();
		if(!empty($characteristics)){
			$mainCharacteristics = array();
			foreach($characteristics as $characteristic){
				if($product_id==$characteristic->variant_product_id){
					$mainCharacteristics[$product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id]=$characteristic;
				}
				if(!empty($element->options)){
					foreach($element->options as $k => $optionElement){
						if($optionElement->product_id==$characteristic->variant_product_id){
							$mainCharacteristics[$optionElement->product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id]=$characteristic;
						}
					}
				}
			}
			if(!empty($element->variants)){
				$this->addCharacteristics($element,$mainCharacteristics,$characteristics);
			}
			if(!empty($element->options)){
				foreach($element->options as $k => $optionElement){
					if(!empty($optionElement->variants)){
						$this->addCharacteristics($element->options[$k],$mainCharacteristics,$characteristics);
						if(count($mainCharacteristics[$optionElement->product_id][0])){
							$optionsVariants = array();
							if($config->get('characteristics_values_sorting')=='old'){
								$order = 'characteristic_id';
							}else{
								$order = 'characteristic_value';
							}
							foreach($optionElement->variants as $k2 => $variant){
								$key = '';
								foreach($variant->characteristics as $char){
									$key .= $char->characteristic_value.'_';
								}
								$optionsVariants[$key]=&$element->options[$k]->variants[$k2];
							}
							ksort($optionsVariants);
							$element->options[$k]->variants=$optionsVariants;
						}
					}
				}
			}
		}
		$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type IN (\'product\',\'file\') ORDER BY file_id ASC';
		$database->setQuery($query);
		$product_files = $database->loadObjectList();
		if(!empty($product_files)){
			$productClass->addFiles($element,$product_files);
		}
		$currencyClass->getPrices($element,$ids,$currency_id,$main_currency,$zone_id,$discount_before_tax);
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getFields('frontcomp',$element,'product','checkout&task=state');
		$this->assignRef('fieldsClass',$fieldsClass);
		$this->assignRef('fields',$fields);
		if(hikashop_level(2)){
			$itemFields = $fieldsClass->getFields('frontcomp',$element,'item','checkout&task=state');
			$null=array();
			$fieldsClass->addJS($null,$null,$null);
			$fieldsClass->jsToggle($itemFields,$element,0);
			$this->assignRef('itemFields',$itemFields);
			$extraFields = array('item'=>&$itemFields);
			$requiredFields = array();
			$validMessages = array();
			$values = array('item'=>$element);
			$fieldsClass->checkFieldsForJS($extraFields,$requiredFields,$validMessages,$values);
			$fieldsClass->addJS($requiredFields,$validMessages,array('item'));
		}
		$this->checkVariants($element);
		if(!empty($element->options)){
			foreach($element->options as $k => $optionElement){
				$this->checkVariants($element->options[$k]);
			}
		}
		$this->setDefault($element);
		if(!empty($element->options)){
			foreach($element->options as $k => $optionElement){
				$this->setDefault($element->options[$k]);
			}
		}
		$this->assignRef('element',$element);
		$doc =& JFactory::getDocument();
		$product_name = $this->element->product_name;
		$product_description = $element->product_meta_description;
		$product_keywords = $element->product_keywords;
		if(!empty($this->element->main)){
			$product_name = $this->element->main->product_name;
			$product_description = $this->element->main->product_meta_description;
			$product_keywords = $this->element->main->product_keywords;
		}
		if(!empty($product_keywords)){
			$doc->setMetadata('keywords', $product_keywords);
		}
		if(!empty($product_description)){
			$doc->setMetadata('description', $product_description);
		}
		$parent = 0;
		if(empty($menu) || !(strpos($menu->link,'option='.HIKASHOP_COMPONENT)!==false && strpos($menu->link,'view=product')!==false && strpos($menu->link,'layout=show')!==false)){
			$pathway =& $app->getPathway();
			$category_pathway = JRequest::getInt('category_pathway',0);
			$url_itemid='';
			if(!empty($Itemid)){
				$url_itemid='&Itemid='.$Itemid;
			}
			if($category_pathway){
				$class = hikashop_get('class.category');
				if(!empty($menu->id)){
					$menuClass = hikashop_get('class.menus');
					$menuData = $menuClass->get($menu->id);
					if(@$menuData->hikashop_params['content_type']=='manufacturer'){
						$new_id = 'manufacturer';
						$class->getMainElement($new_id);
						$menuData->hikashop_params['selectparentlisting']=$new_id;
					}
					if(!empty($menuData->hikashop_params['selectparentlisting'])){
						$parent = $menuData->hikashop_params['selectparentlisting'];
					}
				}
				$categories = $class->getParents($category_pathway,$parent);
				$one = true;
				foreach($categories as $category){
					if($one){
						$one = false;
					}else{
						if(method_exists($app,'stringURLSafe')){
							$alias = $app->stringURLSafe(strip_tags($category->category_name));
						}else{
							$alias = JFilterOutput::stringURLSafe(strip_tags($category->category_name));
						}
						$pathway->addItem($category->category_name,hikashop_completeLink('category&task=listing&cid='.(int)$category->category_id.'&name='.$alias.$url_itemid));
					}
				}
			}
			$related = JRequest::getInt('related_product',0);
			if(!empty($related)){
				$class = hikashop_get('class.product');
				$prod = $class->get($related);
				if(!empty($prod)){
					if(method_exists($app,'stringURLSafe')){
						$prod->alias = $app->stringURLSafe(strip_tags($prod->product_name));
					}else{
						$prod->alias = JFilterOutput::stringURLSafe(strip_tags($prod->product_name));
					}
					$pathway->addItem($prod->product_name,hikashop_completeLink('product&task=show&cid='.(int)$prod->product_id.'&name='.$prod->alias.'&category_pathway='.$category_pathway.$url_itemid));
				}
			}
			$pathway->addItem($product_name,hikashop_completeLink('product&task=show&cid='.(int)$element->product_id.'&name='.$element->alias.'&category_pathway='.$category_pathway.$url_itemid));
		}
		$image = hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$this->assignRef('currencyHelper',$currencyClass);
		$characteristic = hikashop_get('type.characteristic');
		$this->assignRef('characteristic',$characteristic);
		$document	=& JFactory::getDocument();
		if (empty($product_name)) {
			$product_name = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$product_name = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $product_name);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$product_name = JText::sprintf('JPAGETITLE', $product_name, $app->getCfg('sitename'));
		}
		$document->setTitle( strip_tags($product_name) );
		$url = $this->init();
		$cart->getJS($url);
		$this->assignRef('redirect_url',$url);
	}
	function compare() {
		if(!hikashop_level(2)) { return; }
		$app =& JFactory::getApplication();
		$cids = JRequest::getVar('cid', array(), '', 'array');
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		global $Itemid;
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();
		if(empty($menu)){
			if(!empty($Itemid)){
				$menus->setActive($Itemid);
				$menu	= $menus->getItem($Itemid);
			}
		}
		if(empty($cids)){
			return;
		}
		$c = array();
		foreach($cids as $cid) {
			if( strpos($cid,',')!==false) {
				$c = array_merge($c,explode(',',$cid));
			} else {
				$c[] = (int)$cid;
			}
		}
		$cids = $c;
		JArrayHelper::toInteger($cids);
		$empty = '';
		$default_params = $config->get('default_params');
		jimport('joomla.html.parameter');
		$params = new JParameter($empty);
		foreach($default_params as $k => $param){
			$params->set($k,$param);
		}
		$main_currency = (int)$config->get('main_currency',1);
		$params->set('main_currency',$main_currency);
		$discount_before_tax = (int)$config->get('discount_before_tax',0);
		$params->set('discount_before_tax',$discount_before_tax);
		$params->set('show_compare',(int)$config->get('show_compare',0));
		$compare_limit = (int)$config->get('compare_limit',5);
		$params->set('compare_limit',$compare_limit);
		$compare_inc_lastseen = (int)$config->get('compare_inc_lastseen',0);
		$params->set('compare_inc_lastseen',$compare_inc_lastseen);
		$params->set('compare_show_name_separator',(int)$config->get('compare_show_name_separator',1));
		$params->set('catalogue',(int)$config->get('catalogue',0));
		$params->set('add_to_cart',(int)1);
		$params->set('show_price_weight',(int)$config->get('show_price_weight',0));
		$params->set('characteristic_display',$config->get('characteristic_display','table'));
		$params->set('characteristic_display_text',$config->get('characteristic_display_text',1));
		$params->set('show_quantity_field',$config->get('show_quantity_field',1));
		$this->assignRef('params',$params);
		if( count($cids) > $compare_limit ) {
			$cids = array_slice($cids, 0, $compare_limit );
		}
		$filters=array('a.product_id IN ('.implode(',',$cids).')');
		hikashop_addACLFilters($filters,'product_access','a');
		$query = 'SELECT a.*,b.product_category_id, b.category_id, b.ordering FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters);
		$database =& JFactory::getDBO();
		$database->setQuery($query);
		$elements = $database->loadObjectList();
		if(empty($elements)){
			return;
		}
		$this->modules = $config->get('product_show_modules','');
		$module = hikashop_get('helper.module');
		$this->modules = $module->setModuleData($this->modules);
		$currencyClass = hikashop_get('class.currency');
		$currency_id = hikashop_getCurrency();
		if($config->get('tax_zone_type','shipping')=='billing'){
			$zone_id = hikashop_getZone('billing');
		}else{
			$zone_id = hikashop_getZone('shipping');
		}
		$cart = hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		$this->selected_variant_id=0;
		$productClass=hikashop_get('class.product');
		$this->assignRef('currencyHelper',$currencyClass);
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fields = array( 0 => array() );
		foreach($elements as &$element) {
			$product_id = $element->product_id;
			if( $element->product_type == 'variant' ) {
				$filters = array('a.product_id='.$element->product_parent_id);
				hikashop_addACLFilters($filter,'product_access','a');
				$query = 'SELECT a.*,b.* FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
				$database->setQuery($query);
				$element = $database->loadObject();
				if(empty($element)){
					return;
				}
				$k = array_search($product_id,$cids);
				if( $k !== false ) {
					$cids[$k] = $element->product_id;
				}
			}
			if(method_exists($app,'stringURLSafe')){
				$element->alias = $app->stringURLSafe(strip_tags($element->product_name));
			}else{
				$element->alias = JFilterOutput::stringURLSafe(strip_tags($element->product_name));
			}
			if(!$element->product_published){
				return;
			}
			if( $compare_inc_lastseen ) {
				$prod = null;
				$prod->product_id = $product_id;
				$prod->product_hit = $element->product_hit+1;
				$prod->product_last_seen_date = time();
				$productClass->save($prod,true);
			}
			$f = $fieldsClass->getFields('frontcomp',$element,'product','checkout&task=state');
			$fields[$element->product_id] =& $f;
			foreach($f as $k => &$v) {
				$fields[0][$k] = $v;
			}
			unset($element);
		}
		$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$cids).') AND file_type IN (\'product\',\'file\') ORDER BY file_ref_id ASC, file_id ASC';
		$database->setQuery($query);
		$product_files = $database->loadObjectList();
		if(!empty($product_files)){
			foreach($elements as &$element) {
				$productClass->addFiles($element,$product_files);
			}
			unset($element);
		}
		$currencyClass->getPrices($elements,$cids,$currency_id,$main_currency,$zone_id,$discount_before_tax);
		$this->assignRef('elements',$elements);
		$image = hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$this->assignRef('fields',$fields);
		$url = $this->init();
		$cart->getJS($url);
		$this->assignRef('redirect_url',$url);
	}
	function addCharacteristics(&$element,&$mainCharacteristics,&$characteristics){
		$element->characteristics = $mainCharacteristics[$element->product_id][0];
		if(!empty($element->characteristics) && is_array($element->characteristics)){
			foreach($element->characteristics as $k => $characteristic){
				if(!empty($mainCharacteristics[$element->product_id][$k])){
					$element->characteristics[$k]->default=end($mainCharacteristics[$element->product_id][$k]);
				}else{
					$app =& JFactory::getApplication();
					$app->enqueueMessage('The default value of one of the characteristics of that product isn\'t available as a variant. Please check the characteristics and variants of that product');
				}
			}
		}
		if(!empty($element->variants)){
			foreach($characteristics as $characteristic){
				foreach($element->variants as $k => $variant){
					if($variant->product_id==$characteristic->variant_product_id){
						$element->variants[$k]->characteristics[$characteristic->characteristic_parent_id]=$characteristic;
						$element->characteristics[$characteristic->characteristic_parent_id]->values[$characteristic->characteristic_id]=$characteristic;
						if($this->selected_variant_id && $variant->product_id==$this->selected_variant_id){
							$element->characteristics[$characteristic->characteristic_parent_id]->default=$characteristic;
						}
					}
				}
			}
			if(isset($_REQUEST['hikashop_product_characteristic'])){
				if(is_array($_REQUEST['hikashop_product_characteristic'])){
					JArrayHelper::toInteger($_REQUEST['hikashop_product_characteristic']);
					$chars = $_REQUEST['hikashop_product_characteristic'];
				}else{
					$chars = JRequest::getCmd('hikashop_product_characteristic','');
					$chars = explode('_',$chars);
				}
				if(!empty($chars)){
					foreach($element->variants as $k => $variant){
						$chars = array();
						foreach($variant->characteristics as $val){
							$i = 0;
							$ordering = @$element->characteristics[$val->characteristic_parent_id]->ordering;
							while(isset($chars[$ordering])&& $i < 30){
								$i++;
								$ordering++;
							}
							$chars[$ordering] = $val;
						}
						ksort($chars);
						$element->variants[$k]->characteristics=$chars;
						$variant->characteristics=$chars;
						$choosed = true;
						foreach($variant->characteristics as $characteristic){
							$ok = false;
							foreach($chars as $k => $char){
								if(!empty($char)){
									if($characteristic->characteristic_id==$char){
										$ok = true;
										break;
									}
								}
							}
							if(!$ok){
								$choosed=false;
							}else{
								$element->characteristics[$characteristic->characteristic_parent_id]->default=$characteristic;
							}
						}
						if($choosed){
							break;
						}
					}
				}
			}
			foreach($element->variants as $k => $variant){
				$temp=array();
				foreach($element->characteristics as $k2 => $characteristic2){
					if(!empty($variant->characteristics)){
						foreach($variant->characteristics as $k3 => $characteristic3){
							if($k2==$k3){
								$temp[$k3]=$characteristic3;
								break;
							}
						}
					}
				}
				$element->variants[$k]->characteristics=$temp;
			}
		}
	}
	function setDefault(&$element){
		if(!empty($element->characteristics)&&!empty($element->variants)){
			$match = false;
			foreach($element->variants as $k => $variant){
				$default = true;
				foreach($element->characteristics as $characteristic){
					$found=false;
					foreach($variant->characteristics as $k => $characteristic2){
						if(!empty($characteristic->default->characteristic_id) && $characteristic2->characteristic_id==$characteristic->default->characteristic_id){
							$found=true;
							break;
						}
					}
					if(!$found){
						$default = false;
						break;
					}
				}
				if($default){
					foreach(get_object_vars($variant) as $field=>$value){
						$element->main->$field=@$element->$field;
						$element->$field = $value;
					}
					$match = true;
					break;
				}
			}
			if(!$match){
				$variant = reset($element->variants);
				foreach(get_object_vars($variant) as $field=>$value){
					$element->main->$field=@$element->$field;
					$element->$field = $value;
				}
			}
		}
	}
	function checkVariants(&$element){
		if(!empty($element->characteristics)){
			$mapping = array();
			foreach($element->characteristics as $characteristic){
				$tempmapping = array();
				if(!empty($characteristic->values) && !empty($characteristic->characteristic_id)){
					foreach($characteristic->values as $k => $value){
						if(empty($mapping)){
							$tempmapping[]=array($characteristic->characteristic_id=>$k);
						}else{
							foreach($mapping as $val){
								$val[$characteristic->characteristic_id]=$k;
								$tempmapping[]=$val;
							}
						}
					}
				}
				$mapping = $tempmapping;
			}
			if(empty($element->variants)){
				$element->variants = array();
			}
			$productClass = hikashop_get('class.product');
			foreach($mapping as $map){
				$found=false;
				foreach($element->variants as $k2 => $variant){
					$ok = true;
					foreach($map as $k => $id){
						if(empty($variant->characteristics[$k]->characteristic_id) || $variant->characteristics[$k]->characteristic_id != $id){
							$ok = false;
							break;
						}
					}
					if($ok){
						$found = true;
						$productClass->checkVariant($element->variants[$k2],$element,$map);
						break;
					}
				}
				if(!$found){
					$new = new stdClass;
					$new->product_published = 0;
					$new->product_quantity = 0;
					$productClass->checkVariant($new,$element,$map);
					$element->variants[$new->map]=$new;
				}
			}
		}
	}
	function _getCheckoutURL(){
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		return hikashop_completeLink('checkout'.$url_itemid,false,true);
	}
	function init($cart=false){
		$config =& hikashop_config();
		$url = $config->get('redirect_url_after_add_cart','stay_if_cart');
		switch($url){
			case 'checkout':
				$url = $this->_getCheckoutURL();
				break;
			case 'stay_if_cart':
				$url='';
				if(!$cart){
					$url = $this->_getCheckoutURL();
					break;
				}
			case 'ask_user':
			case 'stay':
				$url='';
			case '':
			default:
				if(empty($url)){
					$url = hikashop_currentURL('return_url');
				}
				break;
		}
		return urlencode($url);
	}
	function cart(){
		$module = hikashop_get('helper.module');
		$module->initialize($this);
		$app =& JFactory::getApplication();
		$database	=& JFactory::getDBO();
		$config =& hikashop_config();
		$currencyClass = hikashop_get('class.currency');
		$class = hikashop_get('class.cart');
		$productClass = hikashop_get('class.product');
		$main_currency = (int)$config->get('main_currency',1);
		$currency_id = hikashop_getCurrency();
		if($config->get('tax_zone_type','shipping')=='billing'){
			$zone_id = hikashop_getZone('billing');
		}else{
			$zone_id = hikashop_getZone('shipping');
		}
		$discount_before_tax = (int)$config->get('discount_before_tax',0);
		$rows = $class->get();
		$total = null;
		if(!empty($rows)){
			$variants = false;
			$ids = array();
			foreach($rows as $k => $row){
				$ids[]=$row->product_id;
				if($row->product_type=='variant'){
					$variants = true;
					foreach($rows as $k2 => $row2){
						if($row->product_parent_id==$row2->product_id){
							$rows[$k2]->variants[]=&$rows[$k];
						}
					}
				}
			}
			if($variants){
				$this->selected_variant_id = 0;
				$query = 'SELECT a.*,b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id IN ('.implode(',',$ids).') ORDER BY a.ordering,b.characteristic_value';
				$database->setQuery($query);
				$characteristics = $database->loadObjectList();
				if(!empty($characteristics)){
					foreach($rows as $k => $row){
						$element =& $rows[$k];
						$product_id=$row->product_id;
						if($row->product_type=='variant'){
							continue;
						}
						$mainCharacteristics = array();
						foreach($characteristics as $characteristic){
							if($product_id==$characteristic->variant_product_id){
								$mainCharacteristics[$product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id]=$characteristic;
							}
							if(!empty($element->options)){
								foreach($element->options as $k => $optionElement){
									if($optionElement->product_id==$characteristic->variant_product_id){
										$mainCharacteristics[$optionElement->product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id]=$characteristic;
									}
								}
							}
						}
						if(!empty($element->variants)){
							$this->addCharacteristics($element,$mainCharacteristics,$characteristics);
						}
						if(!empty($element->options)){
							foreach($element->options as $k => $optionElement){
								if(!empty($optionElement->variants)){
									$this->addCharacteristics($element->options[$k],$mainCharacteristics,$characteristics);
								}
							}
						}
					}
				}
			}
			$product_quantities = array();
			foreach($rows as $row){
				if(empty($product_quantities[$row->product_id])){
					$product_quantities[$row->product_id] = (int)@$row->cart_product_quantity;
				}else{
					$product_quantities[$row->product_id]+=(int)@$row->cart_product_quantity;
				}
			}
			foreach($rows as $k => $row){
				$rows[$k]->cart_product_total_quantity = $product_quantities[$row->product_id];
			}
			$currencyClass->getPrices($rows,$ids,$currency_id,$main_currency,$zone_id,$discount_before_tax);
			foreach($rows as $k => $row){
				if(!empty($row->variants)){
					foreach($row->variants as $k2 => $variant){
						$productClass->checkVariant($rows[$k]->variants[$k2],$row);
					}
				}
			}
			foreach($rows as $k => $row){
				$currencyClass->calculateProductPriceForQuantity($rows[$k]);
			}
			$total=null;
			$currencyClass->calculateTotal($rows,$total,$currency_id);
		}
		$this->assignRef('total',$total);
		$this->assignRef('rows',$rows);
		$this->assignRef('config',$config);
		$cart=hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		$this->assignRef('currencyHelper',$currencyClass);
		$cart->cartCount(true);
		$url = $this->init(true);
		$this->params->set('url',$url);
		ob_start();
		$cart->getJS($url,false);
		$notice_html = ob_get_clean();
		$this->assignRef('notice_html',$notice_html);
		if(hikashop_level(2)){
			$null=null;
			$fieldsClass=hikashop_get('class.field');
			$itemFields = $fieldsClass->getFields('frontcomp',$null,'item','checkout&task=state');
			$this->assignRef('itemFields',$itemFields);
			$this->assignRef('fieldsClass',$fieldsClass);
		}
	}
	function contact(){
		$user = hikashop_loadUser(true);
		$this->assignRef('element',$user);
		$app =& JFactory::getApplication();
		$product_id = (int)hikashop_getCID('product_id');
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$filters=array('a.product_id='.$product_id);
		hikashop_addACLFilters($filters,'product_access','a');
		$query = 'SELECT a.*,b.product_category_id, b.category_id, b.ordering FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
		$database =& JFactory::getDBO();
		$database->setQuery($query);
		$element = $database->loadObject();
		if(empty($element)){
			return;
		}
		if($element->product_type=='variant'){
			$this->selected_variant_id = $product_id;
			$filters=array('a.product_id='.$element->product_parent_id);
			hikashop_addACLFilters($filters,'product_access','a');
			$query = 'SELECT a.*,b.* FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
			$database->setQuery($query);
			$element = $database->loadObject();
			if(empty($element)){
				return;
			}
			$product_id = $element->product_id;
		}
		if(method_exists($app,'stringURLSafe')){
			$element->alias = $app->stringURLSafe(strip_tags($element->product_name));
		}else{
			$element->alias = JFilterOutput::stringURLSafe(strip_tags($element->product_name));
		}
		if(!$element->product_published){
			return;
		}
		$this->assignRef('product',$element);
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$product_url = hikashop_completeLink('product&task=show&cid='.(int)$element->product_id.'&name='.$element->alias.$url_itemid);
		$this->assignRef('product_url',$product_url);
	}
	function waitlist(){
		$user = hikashop_loadUser(true);
		$this->assignRef('element',$user);
		$app =& JFactory::getApplication();
		$product_id = (int)hikashop_getCID('product_id');
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$filters=array('a.product_id='.$product_id);
		hikashop_addACLFilters($filters,'product_access','a');
		$query = 'SELECT a.*,b.product_category_id, b.category_id, b.ordering FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
		$database =& JFactory::getDBO();
		$database->setQuery($query);
		$element = $database->loadObject();
		if(empty($element)){
			return;
		}
		if($element->product_type=='variant'){
			$this->selected_variant_id = $product_id;
			$filters=array('a.product_id='.$element->product_parent_id);
			hikashop_addACLFilters($filters,'product_access','a');
			$query = 'SELECT a.*,b.* FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
			$database->setQuery($query);
			$element = $database->loadObject();
			if(empty($element)){
				return;
			}
			$product_id = $element->product_id;
		}
		if(method_exists($app,'stringURLSafe')){
			$element->alias = $app->stringURLSafe(strip_tags($element->product_name));
		}else{
			$element->alias = JFilterOutput::stringURLSafe(strip_tags($element->product_name));
		}
		if(!$element->product_published){
			return;
		}
		$this->assignRef('product',$element);
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$product_url = hikashop_completeLink('product&task=show&cid='.(int)$element->product_id.'&name='.$element->alias.$url_itemid);
		$this->assignRef('product_url',$product_url);
	}
	function pagination_display($type, $divName, $id, $currentId, $position, $products){
		if($position=='top' || $position=='bottom'){
			if($type=='numbers'){
				echo '<a id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'" style="cursor:pointer; text-decoration:none">'.($id+1).'</a>';
			}
			if($type=='rounds'){
				echo '<span class="hikashop_slide_dot_basic'.($currentId<$products ? ' hikashop_slide_dot_selected' : '').'" id="slide_number_'.$divName.'_'.$id.'"></span>';
			}
			if($type=='thumbnails'){
				echo '<span class="'.($currentId<$products ? ' hikashop_pagination_images_selected' : 'hikashop_pagination_images').'" id="slide_number_'.$divName.'_'.$id.'">';
			}
			if($type=='names'){
				echo '<span id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'">';
			}
		}
		else{
			if($type=='numbers'){
				echo '<a id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'" style="cursor:pointer; text-decoration:none">'.($id+1).'</a><br/>';
			}
			if($type=='rounds'){
				echo '<span class="hikashop_slide_dot_basic'.($currentId<$products ? ' hikashop_slide_dot_selected' : '').'" id="slide_number_'.$divName.'_'.$id.'"></span><br/>';
			}
			if($type=='thumbnails'){
				echo '<span class="'.($currentId<$products ? ' hikashop_pagination_images_selected' : 'hikashop_pagination_images').'" id="slide_number_'.$divName.'_'.$id.'">';
			}
			if($type=='names'){
				echo '<span id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'">';
			}
		}
	}
	function scaleImage($x, $y, $cx, $cy){
		if(empty($cx)){
			$cx = ($x*$cy)/$y;
		}
		if(empty($cy)){
			$cy = ($y*$cx)/$x;
		}
		return array($cx,$cy);
	}
}