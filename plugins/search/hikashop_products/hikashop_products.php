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
class plgSearchHikashop_products extends JPlugin{
	function plgSearchHikashop_products(&$subject, $config){
		$this->loadLanguage('plg_search_hikashop_products');
		$this->loadLanguage('plg_search_hikashop_products_override');
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('search', 'hikashop_products');
			jimport('joomla.html.parameter');
			$this->params = new JParameter( $plugin->params );
		}
    }
	function onContentSearchAreas(){
    	return $this->onSearchAreas();
    }
	function onContentSearch( $text, $phrase='', $ordering='', $areas=null ){
    	return $this->onSearch( $text, $phrase, $ordering, $areas );
    }
	function &onSearchAreas(){
		$areas = array(
			'products' => JText::_('PRODUCTS')
		);
		return $areas;
	}
	function onSearch( $text, $phrase='', $ordering='', $areas=null ){
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return array();
		$db		=& JFactory::getDBO();
		if (is_array( $areas )) {
			if (!array_intersect( $areas, array_keys( $this->onSearchAreas() ) )) {
				return array();
			}
		}
		$limit = $this->params->def( 'search_limit', 50 );
		$text = trim( $text );
		if ( $text == '' ) {
			return array();
		}
		switch($ordering){
			case 'alpha':
				$order = 'a.product_name ASC';
				break;
			case 'newest':
				$order = 'a.product_modified DESC';
				break;
			case 'oldest':
				$order = 'a.product_created ASC';
				break;
			case 'popular':
				$order = 'a.product_hit DESC';
				break;
			case 'category':
			default:
				$order = 'a.product_name DESC';
				break;
		}
		$trans=hikashop_get('helper.translation');
		$multi=$trans->isMulti();
		$rows = array();
		$filters = array('a.product_published=1','a.product_type=\'main\'');
		$out_of_stock = (int)$this->params->get('out_of_stock_display','1');
		if(!$out_of_stock){
			$filters[]='a.product_quantity!=0';
		}
		hikashop_addACLFilters($filters,'product_access','a');
		$leftjoin='';
		if(hikashop_level(2)){
			$catFilters = array('category_published=1','category_type=\'product\'');
			hikashop_addACLFilters($catFilters,'category_access');
			$db->setQuery('SELECT category_id FROM '.hikashop_table('category').' WHERE '.implode(' AND ',$catFilters));
			$cats = $db->loadResultArray();
			if(!empty($cats)){
				$filters[]='b.category_id IN ('.implode(',',$cats).')';
			}
		}
		$leftjoin=' INNER JOIN '.hikashop_table('product_category').' AS b ON a.product_id=b.product_id';
		$filters2 = array();
		if($multi){
			$registry =& JFactory::getConfig();
			$lg = $trans->getId($registry->getValue("config.jflang"));
			$filters2[] = "b.reference_table='hikashop_product'";
			$filters2[] = "b.published=1";
			$filters2[] = 'b.language_id='.$lg;
		}
		$fields = $this->params->get('fields','');
		if(empty($fields)){
			$fields = array('product_name','product_description');
		}else{
			$fields = explode(',',$fields);
		}
		switch($phrase){
			case 'exact':
				$text		= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
				foreach($fields as $f){
					$filters[] = "a.".$f." LIKE ".$text;
				}
				if($multi){
					$filters2[] = "b.value LIKE ".$text;
				}
				break;
			case 'all':
			case 'any':
			default:
				$words = explode( ' ', $text );
				$wordFilters = array();
				$subWordFiltersX = array();
				$wordFilters2 = array();
				foreach ($words as $word) {
					$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
					foreach($fields as $i => $f){
						$subWordFiltersX[$i][] = "a.".$f." LIKE ".$word;
					}
					if($multi){
						$wordFilters2[] = "b.value LIKE ".$word;
					}
				}
				foreach($subWordFiltersX as $i => $subWordFilters){
					$wordFilters[$i]= '((' .implode( ($phrase == 'all' ? ') AND (' : ') OR ('),$subWordFilters). '))';
				}
				$filters[] = '((' . implode( ') OR (', $wordFilters ) . '))';
				if($multi){
					$filters2[] = '((' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wordFilters2 ) . '))';
				}
				break;
		}
		$new_page = (int)$this->params->get('new_page','1');
		$select = ' a.product_id AS id, a.product_name AS title, a.product_created AS created , a.product_description AS text, "'.$new_page.'" AS browsernav';
		$count = 0;
		if($multi && !empty($lg)){
			$filters2[]=implode(' AND ',$filters);
			$query = ' SELECT DISTINCT '.$select.' FROM '.hikashop_table('jf_content',false) . ' AS b LEFT JOIN '.hikashop_table('product').' AS a ON b.reference_id=a.product_id WHERE '.implode(' AND ',$filters2).' ORDER BY '.$order;
			$db->setQuery($query, 0, $limit);
			$rows = $db->loadObjectList("id");
			$count = count($rows);
			if($count){
				$limit = $limit-$count;
				$filters[]='a.product_id NOT IN ('.implode(',',array_keys($rows)).')';
			}
		}
		if($limit){
			if(!empty($leftjoin)){
				$select.=', b.category_id as category_id';
			}
			$query = ' SELECT '.$select.' FROM '.hikashop_table('product') . ' AS a '.$leftjoin.' WHERE '.implode(' AND ',$filters).' ORDER BY '.$order;
			$db->setQuery( $query, 0, $limit );
			$mainRows = $db->loadObjectList("id");
			if(!empty($mainRows)){
				foreach($mainRows as $k => $main){
					$rows[$k]=$main;
				}
				$count = count( $rows );
			}
		}
		if($count){
			if($multi && !empty($lg)){
				$query = ' SELECT * FROM '.hikashop_table('jf_content',false) . ' WHERE reference_table=\'hikashop_product\' AND language_id=\''.$lg.'\' AND published=1 AND reference_id IN ('.implode(',',array_keys($rows)).')';
				$db->setQuery($query);
				$trans = $db->loadObjectList();
				foreach($trans as $item){
					foreach($rows as $key => $row){
						if($row->id==$item->reference_id){
							if($item->reference_field=='product_name'){
								$row->title=$item->value;
							}elseif($item->reference_field=='product_description'){
								$row->text=$item->value;
							}else{
								$row->title=$item->value;
							}
							break;
						}
					}
				}
			}
			$parent = '';
			$item_id = $this->params->get('item_id','');
			$menuClass = hikashop_get('class.menus');
			$menus=array();
			if(!empty($item_id)){
				$Itemid='&Itemid='.$item_id;
				if($this->params->get('full_path',1)){
					$menuData = $menus[$item_id] = $menuClass->get($item_id);
					if(!empty($menuData->hikashop_params['selectparentlisting'])){
						$parent = '&category_pathway='.(int)$menuData->hikashop_params['selectparentlisting'];
					}
				}
			}
			$itemids=array();
			$app=& JFactory::getApplication();
			foreach ( $rows as $k => $row ) {
				if(empty($item_id) && !empty($row->category_id)){
					if(!isset($itemids[$row->category_id])) $itemids[$row->category_id] = $menuClass->getItemidFromCategory($row->category_id);
					$item_id = $itemids[$row->category_id];
					if(!empty($item_id)){
						$Itemid='&Itemid='.$item_id;
						if($this->params->get('full_path',1)){
							$parent = '&category_pathway='.(int)$row->category_id;
						}
						$item_id = '';
					}
				}
				if(method_exists($app,'stringURLSafe')){
					$alias = $app->stringURLSafe(strip_tags($row->title));
				}else{
					$alias = JFilterOutput::stringURLSafe(strip_tags($row->title));
				}
				$rows[$k]->href = hikashop_completeLink('product&task=show&name='.$alias.'&cid='.$row->id.$Itemid.$parent);
				$rows[$k]->section 	= JText::_( 'PRODUCT' );
			}
		}
		return $rows;
	}
}