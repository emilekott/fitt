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
class CategoryViewCategory extends JView{
	var $type = 'product';
	var $ctrl= 'category';
	var $nameListing = 'HIKA_CATEGORIES';
	var $nameForm = 'HIKA_CATEGORIES';
	var $icon = 'category';
	var $module=false;
	function display($tpl = null,$params=array()){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		$this->params = $params;
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$module = hikashop_get('helper.module');
		$module->initialize($this);
		$this->paramBase.='_'.$this->params->get('main_div_name');
		$filters = array();
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$database	=& JFactory::getDBO();
		$content_type = $this->params->get('content_type');
		if($content_type=='manufacturer'){
			$category_type = 'manufacturer';
			$id = JRequest::getInt("cid");
			$class = hikashop_get('class.category');
			$new_id = 'manufacturer';
			$class->getMainElement($new_id);
			$this->params->set('selectparentlisting',$new_id);
		}else{
			$category_type = 'product';
		}
		if($this->params->get('content_synchronize')){
			if(JRequest::getString('option','')==HIKASHOP_COMPONENT){
				if(JRequest::getString('ctrl','category')=='product'){
					$product_id = hikashop_getCID('product_id');
					if(!empty($product_id)){
						$query = 'SELECT category_id FROM '.hikashop_table('product_category').' WHERE product_id='.$product_id;
						$database->setQuery($query);
						$pageInfo->filter->cid = $database->loadResultArray();
					}else{
						$pageInfo->filter->cid = $this->params->get('selectparentlisting');
					}
				}elseif(JRequest::getString('ctrl','category')=='category'){
					$pageInfo->filter->cid = JRequest::getInt("cid",$this->params->get('selectparentlisting'));
				}else{
					$pageInfo->filter->cid = $this->params->get('selectparentlisting');
				}
			}else{
				$pageInfo->filter->cid = $this->params->get('selectparentlisting');
			}
		}else{
			if(empty($this->module)){
				$pageInfo->filter->cid = JRequest::getInt("cid",$this->params->get('selectparentlisting'));
			}else{
				$pageInfo->filter->cid = $this->params->get('selectparentlisting');
			}
		}
		if(!empty($pageInfo->filter->cid)){
			$acl_filters = array();
			hikashop_addACLFilters($acl_filters,'category_access');
			if(!empty($acl_filters)){
				if(!is_array($pageInfo->filter->cid)){
					$pageInfo->filter->cid = array($pageInfo->filter->cid);
				}
				$acl_filters[]='category_type=\''.$category_type.'\'';
				$acl_filters[]='category_id IN ('.implode(',',$pageInfo->filter->cid).')';
				$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE '.implode(' AND ',$acl_filters);
				$database->setQuery($query);
				$pageInfo->filter->cid = $database->loadResultArray();
			}
		}
		if(empty($pageInfo->filter->cid)){
			$pageInfo->filter->cid = 'product';
		}
		$category_selected='';
		if(!is_array($pageInfo->filter->cid)){
			$category_selected = '_'.$pageInfo->filter->cid;
			$this->paramBase.=$category_selected;
		}
		$this->assignRef('category_selected',$category_selected);
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order_'.$this->params->get('main_div_name').$category_selected,	'a.'.$this->params->get('category_order'),'cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir_'.$this->params->get('main_div_name').$category_selected,	$this->params->get('order_dir'),	'word' );
		$oldValue = $app->getUserState($this->paramBase.'.list_limit');
		if(empty($oldValue)){
			$oldValue = $this->params->get('limit');
		}
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit_'.$this->params->get('main_div_name').$category_selected, $this->params->get('limit'), 'int' );
		if($oldValue!=$pageInfo->limit->value){
			JRequest::setVar('limitstart_'.$this->params->get('main_div_name').$category_selected,0);
		}
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart_'.$this->params->get('main_div_name').$category_selected, 0, 'int' );
		if(empty($this->module)){
				if($config->get('hikarss_format') != 'none'){
					$doc_title = $config->get('hikarss_name','');
					if(empty($doc_title)){
						$category = hikashop_get('class.category');
						if(is_array($pageInfo->filter->cid)){
							$cat = reset($pageInfo->filter->cid);
						}else{
							$cat = $pageInfo->filter->cid;
						}
						$catData = $category->get($cat);
						if($catData) $doc_title = $catData->category_name;
					}
					$doc =& JFactory::getDocument();
					if($config->get('hikarss_format') != 'both'){
						$link	= '&format=feed&limitstart=';
						$attribs = array('type' => 'application/rss+xml', 'title' => $doc_title.' RSS 2.0');
						$doc->addHeadLink(JRoute::_($link.'&type='.$config->get('hikarss_format')), 'alternate', 'rel', $attribs);
					}else{
						$link	= '&format=feed&limitstart=';
						$attribs = array('type' => 'application/rss+xml', 'title' => $doc_title.' RSS 2.0');
						$doc->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
						$attribs = array('type' => 'application/atom+xml', 'title' => $doc_title.' Atom 1.0');
						$doc->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
					}
				}
			$cid = JRequest::getInt("cid",0);
			if(empty($cid)){
				JRequest::setVar("no_cid",1);
			}
			if(is_array($pageInfo->filter->cid)){
				JRequest::setVar("cid",reset($pageInfo->filter->cid));
			}else{
				JRequest::setVar("cid",$pageInfo->filter->cid);
			}
			JRequest::setVar('menu_main_category',$this->params->get('selectparentlisting'));
		}
		$searchMap = array('a.category_name','a.category_description','a.category_id');
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$class = hikashop_get('class.category');
		$class->parentObject =& $this;
		$rows = $class->getChilds($pageInfo->filter->cid,$this->params->get('filter_type'),$filters,$order,$pageInfo->limit->start,$pageInfo->limit->value,true);
		if(!empty($class->query)){
			$database->setQuery('SELECT COUNT(*) '.$class->query);
			$pageInfo->elements->total = $database->loadResult();
			$pageInfo->elements->page = count($rows);
		}else{
			$pageInfo->elements->total = 0;
			$pageInfo->elements->page = 0;
		}
		if($pageInfo->elements->page){
			$ids = array();
			foreach($rows as $key => $row){
				$ids[]=$row->category_id;
				if(method_exists($app,'stringURLSafe')){
					$rows[$key]->alias = $app->stringURLSafe($row->category_name);
				}else{
					$rows[$key]->alias = JFilterOutput::stringURLSafe($row->category_name);
				}
			}
			if($this->params->get('child_display_type')!='nochild'){
				$childs = $class->getChilds($ids,false,array(),' ORDER BY category_ordering',0,0,false);
				if(!empty($childs)){
					foreach($rows as $k => $row){
						foreach($childs as $child){
							if($child->category_parent_id==$row->category_id){
								if(method_exists($app,'stringURLSafe')){
									$child->alias = $app->stringURLSafe($child->category_name);
								}else{
									$child->alias = JFilterOutput::stringURLSafe($child->category_name);
								}
								$rows[$k]->childs[]=$child;
								$limit = $this->params->get('child_limit');
								if(!empty($limit) && count($rows[$k]->childs)>=$limit){
									break;
								}
							}
						}
					}
				}
			}
		}
		$this->assignRef('rows',$rows);
		$this->assignRef('modules',$this->modules);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$this->assignRef('category_image',$category_image);
		$menu_id = '';
		if(empty($this->module)){
			if(is_array($pageInfo->filter->cid)){
				$pageInfo->filter->cid = reset($pageInfo->filter->cid);
			}
			$element = $class->get($pageInfo->filter->cid,true);
			$fieldsClass = hikashop_get('class.field');
			$fields = $fieldsClass->getFields('frontcomp',$element,'category','checkout&task=state');
			$this->assignRef('fieldsClass',$fieldsClass);
			$this->assignRef('fields',$fields);
			$this->assignRef('element',$element);
			$use_module = $this->params->get('use_module_name');
			$title = $this->params->get('page_title');
			if(empty($title)){
				$title = $this->params->get('title');
			}
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
			$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
			$pagination->hikaSuffix = '_'.$this->params->get('main_div_name').$category_selected;
			$this->assignRef('pagination',$pagination);
			$this->params->set('show_limit',1);
			$pathway =& $app->getPathway();
			$categories = $class->getParents($cid,$this->params->get('selectparentlisting'));
			global $Itemid;
			if(!empty($Itemid)){
				$menu_id = '&Itemid='.$Itemid;
			}
			$one = true;
			foreach($categories as $category){
				if($one){
					$one = false;
				}
				else{
					if(method_exists($app,'stringURLSafe')){
						$alias = $app->stringURLSafe($category->category_name);
					}else{
						$alias = JFilterOutput::stringURLSafe($category->category_name);
					}
					$pathway->addItem($category->category_name,hikashop_completeLink('category&task=listing&cid='.(int)$category->category_id.'&name='.$alias.$menu_id));
				}
			}
		}else{
			$menu_id = $this->params->get('itemid',0);
			if(!empty($menu_id)){
				$menu_id = '&Itemid='.$menu_id;
			}else{
				$menu_id = '';
			}
		}
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('menu_id',$menu_id);
		$this->assignRef('params',$this->params);
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
}