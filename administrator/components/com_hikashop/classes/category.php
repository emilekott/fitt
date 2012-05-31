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
class hikashopCategoryClass extends hikashopClass{
	var $tables = array('taxation','product_category','category');
	var $pkeys = array('','category_id','category_id');
	var $namekeys = array('category_namekey','','');
	var $parent = 'category_parent_id';
	var $toggle = array('category_published'=>'category_id');
	var $type = 'product';
	var $query = '';
	var $parentObject = '';
	function setType($type){
		$this->type = $type;
	}
	function get($element,$withimage=false){
		if(in_array($element,array('product','status','tax','manufacturer'))){
			$this->getMainElement($element);
		}
		if($withimage){
			$query = 'SELECT a.*,b.* FROM '.hikashop_table(end($this->tables)).' AS a LEFT JOIN '.hikashop_table('file').' AS b ON a.category_id=b.file_ref_id AND b.file_type=\'category\' WHERE a.category_id = '.(int)$element.' LIMIT 1';
			$this->database->setQuery($query);
			return $this->database->loadObject();
		}
		return parent::get($element);
	}
	function saveForm(){
		$category_id = hikashop_getCID('category_id');
		if($category_id){
			$oldCategory = $this->get($category_id);
		}
		$fieldsClass = hikashop_get('class.field');
		$element = $fieldsClass->getInput('category',$oldCategory);
		if(empty($element)){
			return false;
		}
		$main = JRequest::getVar( 'main_category', 0, '', 'int' );
		if($main){
			$element->category_parent_id = 0;
		}else{
			$element->category_type='';
		}
		$category_description = JRequest::getVar('category_description','','','string',JREQUEST_ALLOWRAW);
		$element->category_description = $category_description;
		$class = hikashop_get('helper.translation');
		$class->getTranslations($element);
		$status = $this->save($element);
		if(!$status){
			JRequest::setVar( 'fail', $element  );
		}else{
			$class->handleTranslations('category',$status,$element);
			$class = hikashop_get('class.file');
			$class->storeFiles('category',$status);
		}
		return $status;
	}
	function save(&$element,$ordering=true){
		$pkey = end($this->pkeys);
		$table = hikashop_table(end($this->tables));
		$recalculate = false;
		$new = true;
		if(!empty($element->$pkey)){
			$new = false;
			$old = $this->get($element->$pkey);
			if(isset($element->category_parent_id)){
				$newParentElement = $this->get($element->category_parent_id);
				if( $old->category_parent_id != $element->category_parent_id){
					if($element->category_parent_id == $element->$pkey){
						return false;
					}
					if(($newParentElement->category_left > $old->category_left) && ($newParentElement->category_right < $old->category_right)){
						return false;
					}
					$recalculate = true;
				}
				if(!empty($newParentElement->category_type)&&$newParentElement->category_type!='root'){
					$element->category_type = $newParentElement->category_type;
				}
			}
			if(empty($element->category_type)){
				if(!empty($old->category_type)){
					if($old->category_type=='root'){
						$element->category_type = $this->type;
					}else{
						$element->category_type = $old->category_type;
					}
				}else{
					$element->category_type = $this->type;
				}
			}
			$element->category_modified=time();
		}else{
			if(empty($element->category_parent_id)){
				$query='SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'root\' LIMIT 1;';
				$this->database->setQuery($query);
				$element->category_parent_id=$this->database->loadResult();
				$element->category_namekey=$element->category_type;
				$element->category_depth = 1;
			}
			$newParentElement = $this->get($element->category_parent_id);
			if(empty($element->category_type) && $newParentElement->category_type!='root'){
				$element->category_type = $newParentElement->category_type;
			}
			if(empty($element->category_type)){
				$element->category_type = $this->type;
			}
			$element->category_created=$element->category_modified=time();
			if(empty($element->category_namekey)){
				$element->category_namekey=$newParentElement->category_type.'_'.$element->category_created.'_'.rand();
			}
			if(!isset($element->category_published)){
				$element->category_published = 1;
			}
		}
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher =& JDispatcher::getInstance();
		$do = true;
		if($new){
			$dispatcher->trigger( 'onBeforeCategoryCreate', array( & $element, & $do) );
		}else{
			$dispatcher->trigger( 'onBeforeCategoryUpdate', array( & $element, & $do) );
		}
		if(!$do){
			return false;
		}
		$status = parent::save($element);
		if(!$status){
			return false;
		}
		if($new){
			$dispatcher->trigger( 'onAfterCategoryCreate', array( & $element ) );
		}else{
			$dispatcher->trigger( 'onAfterCategoryUpdate', array( & $element, ) );
		}
		if(empty($element->$pkey)){
			$element->$pkey = $status;
			if($ordering){
				$orderClass = hikashop_get('helper.order');
				$orderClass->pkey = 'category_id';
				$orderClass->table = 'category';
				$orderClass->groupMap = 'category_parent_id';
				$orderClass->groupVal = $element->category_parent_id;
				$orderClass->orderingMap = 'category_ordering';
				$orderClass->reOrder();
			}
		}
		$filter = '';
		if($new){
			$query = 'UPDATE '.$table.' SET category_right = category_right + 2 WHERE category_right >= '.$newParentElement->category_right.$filter;
			$this->database->setQuery($query);
			$this->database->query();
			$query = 'UPDATE '.$table.' SET category_left = category_left + 2 WHERE category_left >= '.$newParentElement->category_right.$filter;
			$this->database->setQuery($query);
			$this->database->query();
			$query = 'UPDATE '.$table.' SET category_left = '.$newParentElement->category_right.', category_right = '.($newParentElement->category_right+1).', category_depth = '.($newParentElement->category_depth+1).' WHERE '.$pkey.' = '.$status.' LIMIT 1';
			$this->database->setQuery($query);
			$this->database->query();
		}elseif($recalculate){
			$query = 'SELECT category_left,category_right,category_depth,'.$pkey.',category_parent_id FROM '.$table.$filter.' ORDER BY category_left ASC';
			$this->database->setQuery($query);
			$categories = $this->database->loadObjectList();
			$root = null;
			$this->categories = array();
			foreach($categories as $cat){
				$this->categories[$cat->category_parent_id][]=$cat;
				if(empty($cat->category_parent_id)){
					$root = $cat;
				}
			}
			$this->rebuildTree($root,0,1);
		}
		return $status;
	}
	function delete($elements){
		if(!is_array($elements)){
			$elements = array($elements);
		}
		JArrayHelper::toInteger($elements);
		$status = true;
		$pkey = end($this->pkeys);
		$table = hikashop_table(end($this->tables));
		$parent = $this->parent;
		$parentIds = array();
		$ids = array();
		$products=array();
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher =& JDispatcher::getInstance();
		$do=true;
		$dispatcher->trigger( 'onBeforeCategoryDelete', array( & $elements, & $do) );
		if(!$do){
			return false;
		}
		foreach($elements as $element){
			if(!$status) continue;
			$data = $this->get($element);
			if(empty($data)) continue;
			if(in_array($data->category_namekey,array('root','product','tax','status','created','confirmed','cancelled','refunded','shipped','manufacturer'))){
				$app =& JFactory::getApplication();
				$app->enqueueMessage(JText::_('DEFAULT_CATEGORIES_DELETE_ERROR'),'error');
				$status=false;
				continue;
			}
			$ids[]=$element;
			$parentIds[$data->category_parent_id]=$data->category_parent_id;
			if($data->category_type=='product'){
				$query = 'SELECT product_id FROM '.hikashop_table('product_category').' WHERE category_id='.$element;
				$this->database->setQuery($query);
				$products = array_merge($products,$this->database->loadResultArray());
			}
			if(!empty($data->category_type)){
				$this->type = $data->category_type;
			}
			$filter = '';
			if($data->category_right - $data->category_left != 1 ){
				$query = 'UPDATE '.$table.' SET '.$parent.' = '.$data->$parent.' WHERE '.$parent.' = '.$element;
				$this->database->setQuery($query);
				$status = $status && $this->database->query();
				$query = 'UPDATE '.$table.' SET category_depth = category_depth-1, category_left=category_left-1, category_right=category_right-1 WHERE category_left > '.$data->category_left.' AND category_right < '.$data->category_right.$filter;
				$this->database->setQuery($query);
				$status = $status && $this->database->query();
			}//endif
			$query = 'UPDATE '.$table.' SET category_right=category_right-2 WHERE category_right > '.$data->category_right.$filter;
			$this->database->setQuery($query);
			$status = $status && $this->database->query();
			$query = 'UPDATE '.$table.' SET category_left=category_left-2 WHERE category_left > '.$data->category_right.$filter;
			$this->database->setQuery($query);
			$status = $status && $this->database->query();
			$status = $status && parent::delete($element);
		}
		if($status){
			$dispatcher->trigger( 'onAfterCategoryDelete', array( & $elements ) );
			if(!empty($parentIds)){
				$orderClass = hikashop_get('helper.order');
				$orderClass->pkey = 'category_id';
				$orderClass->table = 'category';
				$orderClass->groupMap = 'category_parent_id';
				$orderClass->orderingMap = 'category_ordering';
				foreach($parentIds as $parentId){
					$orderClass->groupVal = $parentId;
					$orderClass->reOrder();
				}
			}
			if(!empty($products)){
				$query='SELECT * FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$products).')';
				$this->database->setQuery($query);
				$entries=$this->database->loadObjectList();
				foreach($entries as $entry){
					if(in_array($entry->product_id,$products)){
						$key = array_search($entry->product_id,$products);
						unset($products[$key]);
					}
				}
				if(!empty($products)){
					$root = 'product';
					$this->getMainElement($root);
					$insert = array();
					foreach($products as $new){
						$insert[]='('.(int)$root.','.$new.')';
					}
					$query = 'INSERT IGNORE INTO '.hikashop_table('product_category').' (category_id,product_id) VALUES '.implode(',',$insert).';';
					$this->database->setQuery($query);
					$this->database->query();
					$orderClass = hikashop_get('helper.order');
					$orderClass->pkey = 'product_category_id';
					$orderClass->table = 'product_category';
					$orderClass->groupMap = 'category_id';
					$orderClass->orderingMap = 'ordering';
					$orderClass->groupVal = $root;
					$orderClass->reOrder();
				}
			}
			$class = hikashop_get('class.file');
			$class->deleteFiles('category',$elements);
			$class = hikashop_get('helper.translation');
			$class->deleteTranslations('category',$elements);
		}
		return $status;
	}
	function getRoot(){
		static $id = 0;
		if(empty($id)){
			$this->database->setQuery('SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'root\' LIMIT 1');
			$id = $this->database->loadResult();
		}
		return $id;
	}
	function getMainElement(&$element){
		$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_parent_id='.$this->getRoot().' AND category_type='.$this->database->Quote($element).' LIMIT 1';
		$this->database->setQuery($query);
		$element = (int)$this->database->loadResult();
	}
	function getChilds($element, $all=false,$additionalFilters=array(),$order='',$start=0,$value=20,$category_image=false,$select = 'a.*'){
		$filters = array();
		$this->category_used = null;
		if(empty($element)){
			$element=$this->getRoot();
		}
		if(is_array($element)){
			if(count($element)>1){
				foreach($element as $k => $v){
					$element[$k]=(int)$v;
				}
				$filters[] = 'a.category_parent_id IN ('.implode(',',$element).')';
				$this->type=0;
			}else{
				$element = (int) array_pop($element);
			}
		}elseif(!is_numeric($element)){
			$this->getMainElement($element);
		}
		if(is_numeric($element)){
			if($all){
				$data = $this->get($element);
				if(!empty($data)){
					if ( ($data->category_left +1) == $data->category_right ) return array();
					$filters[] = 'a.category_left > '.$data->category_left;
					$filters[] = 'a.category_right < '.$data->category_right;
					if(!empty($data->category_type) AND $data->category_type!="root"){
						$this->type = $data->category_type;
					}
				}
			}else{
				$filters[] = 'a.category_parent_id = '.$element;
				$this->type=0;
			}
		}elseif(!is_array($element)){
			$this->type = $element;
		}
		if(is_numeric($element)){
			$this->category_used = $element;
		}elseif(is_array($element)){
			$this->category_used = (int) array_pop($element);
		}
		if(!empty($this->type)){
			$filters[] = 'a.category_type = '.$this->database->Quote($this->type);
		}
		if(!empty($additionalFilters)){
			$filters = array_merge($filters,$additionalFilters);
		}
		$leftjoin = '';
		$app =& JFactory::getApplication();
		if(!$app->isAdmin()){
			$filters[]='a.category_published=1';
			hikashop_addACLFilters($filters,'category_access','a');
		}
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeCategoryListingLoad', array( & $filters, &$order, &$this->parentObject) );
		if(!empty($filters)){
			$filters = ' WHERE '.implode(' AND ',$filters);
		}else{
			$filters = '';
		}
		$this->query = ' FROM '.hikashop_table(end($this->tables)).' AS a'.$filters;
		$this->database->setQuery('SELECT '.$select.' FROM '.hikashop_table(end($this->tables)).' AS a'.$leftjoin.$filters.$order,(int)$start,(int)$value);
		$rows = $this->database->loadObjectList();
		if($category_image && !empty($rows)){
			$ids = array();
			foreach($rows as $row){
				$ids[]=$row->category_id;
			}
			$this->database->setQuery('SELECT * FROM '.hikashop_table('file').' WHERE file_type=\'category\' AND file_ref_id IN ('.implode(',',$ids).')');
			$images = $this->database->loadObjectList();
			foreach($rows as $k => $cat){
				if(!empty($images)){
					foreach($images as $img){
						if($img->file_ref_id==$cat->category_id){
							foreach(get_object_vars($img) as $key => $val){
								$rows[$k]->$key = $val;
							}
							break;
						}
					}
				}
				if(!isset($rows[$k]->file_name)){
					$rows[$k]->file_name = $row->category_name;
				}
			}
		}
		return $rows;
	}
	function loadAllWithTrans($type='',$all=false,$filters=array(),$order=' ORDER BY category_ordering ASC',$start=0,$value=500,$category_image=false){
		static $data = array();
		static $queries = array();
		$key = $type.'_'.(int)$all.'_'.$order.'_'.implode('_',$filters).'_'.$start.'_'.$value.'_'.(int)$category_image;
		if(!isset($data[$key])){
			$rows = $this->getChilds($type,$all,$filters,$order,$start,$value,$category_image);
			$queries[$key]=$this->query;
			if(!empty($rows)){
				$ids = array();
				foreach($rows as $id => $oneRow){
					$ids[]=$oneRow->category_id;
				}
				$translationHelper = hikashop_get('helper.translation');
				if($translationHelper->isMulti()){
					$user =& JFactory::getUser();
					$locale = $user->getParam('language');
					if(empty($locale)){
						$config =& JFactory::getConfig();
						$locale = $config->getValue('config.language');
					}
					$lgid = $translationHelper->getId($locale);
					$query = 'SELECT * FROM '.hikashop_table('jf_content',false).' AS b WHERE b.reference_id IN ('.implode(',',$ids).') AND b.reference_table=\'hikashop_category\' AND b.reference_field=\'category_name\' AND b.published=1 AND b.language_id='.$lgid;
					$this->database->setQuery($query);
					$translations = $this->database->loadObjectList();
					if(!empty($translations)){
						foreach($translations as $translation){
							foreach($rows as $k => $row){
								if($row->category_id==$translation->reference_id){
									$rows[$k]->translation = $translation->value;
									break;
								}
							}
						}
					}
				}
				foreach($rows as $k => $category){
					if(!isset($category->translation)){
						$val = str_replace(' ','_',strtoupper($category->category_name));
						$rows[$k]->translation = JText::_($val);
						if($val==$rows[$k]->translation){
							$rows[$k]->translation = $category->category_name;
						}
					}
				}
			}
			$data[$key] =& $rows;
		}else{
			$this->query=$queries[$key];
		}
		return $data[$key];
	}
	function getParents( $element ,$exclude=0){
		if(empty($element)) return array();
		$where='';
		if($exclude){
			$el = $this->get($exclude);
			if($el){
				$where=' AND b.category_left>='.$el->category_left.' AND b.category_right<='.$el->category_right;
			}else{
				$where=' AND b.category_id!='.(int)$exclude;
			}
		}
		if(is_array($element)){
			$and='AND a.category_id IN (';
			foreach($element as $cat){
				if(is_object($cat)){
					$and.=(int)$cat->category_id.',';
				}else{
					$and.=(int)$cat.',';
				}
			}
			$and=substr($and,0,-1);
			$and.=')';
		}else{
			$and='AND a.category_id='.(int)$element;
		}
		$query = 'SELECT DISTINCT b.* FROM '.hikashop_table(end($this->tables)).' AS a LEFT JOIN '.
		hikashop_table(end($this->tables)).' AS b ON a.category_left >= b.category_left WHERE '.
		'b.category_right >= a.category_right '.$and.$where.' ORDER BY b.category_left';
		$this->database->setQuery($query);
		return $this->database->loadObjectList();
  	}
  	function getNamekey($element){
  		return $element->category_parent_id.'_'.preg_replace('#[^a-z0-9]#i','',$element->category_name).'_'.rand();
  	}
	function rebuildTree($element,$depth,$left){
		$currentLeft = $left;
		$currentDepth = $depth;
		$pkey = end($this->pkeys);
		if(!empty($this->categories[$element->$pkey])){
			$depth++;
			foreach($this->categories[$element->$pkey] as $child){
				$left++;
				list($depth,$left) = $this->rebuildTree($child,$depth,$left);
			}
			$depth--;
		}
		$left++;
		if($currentLeft != $element->category_right OR $currentLeft != $element->category_left OR $currentDepth!=$element->category_depth){
			$query = 'UPDATE '.hikashop_table(end($this->tables)). ' SET category_left='.$currentLeft.', category_right='.$left.', category_depth='.$currentDepth.' WHERE '.$pkey.' = '.$element->$pkey.' LIMIT 1';
			$this->database->setQuery($query);
			$this->database->query();
		}
		return array($depth,$left);
	}
}