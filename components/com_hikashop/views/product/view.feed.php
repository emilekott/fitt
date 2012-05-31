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
jimport( 'joomla.application.component.view');
class ProductViewProduct  extends JView
{
	function display($tpl = null)
    {
		global $mainframe;
		global $Itemid;
		$db			=& JFactory::getDBO();
		$app =& JFactory::getApplication();
		$this->params = $app->getParams();
		$doc	=& JFactory::getDocument();
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();
		$config =& hikashop_config();
		if(empty($menu) AND !empty($Itemid)){
			$menus->setActive($Itemid);
			$menu	= $menus->getItem($Itemid);
		}
		$myItem = empty($Itemid) ? '' : '&Itemid='.$Itemid;
		if (is_object( $menu )) {
			jimport('joomla.html.parameter');
			$menuparams = new JParameter( $menu->params );
		}
		$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_access=\'all\' AND product_published=1 AND product_type=\'main\' ';
		if(!$config->get('show_out_of_stock',1)){
			$query.=' AND product_quantity!=0 ';
		}
	    $query .= ' ORDER BY '.$config->get('hikarss_order','product_id').' DESC';
		$query .= ' LIMIT '.$config->get('hikarss_element','10');
	    $db->setQuery($query);
	    $products = $db->loadObjectList();
	    if(!empty($products)){
	        $ids = array();
	        foreach($products as $key => $row){
	            $ids[]=$row->product_id;
	            $products[$key]->alias = JFilterOutput::stringURLSafe($row->product_name);
	        }
	        $queryCategoryId='SELECT * FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$ids).')';
	        $db->setQuery($queryCategoryId);
	        $categoriesId = $db->loadObjectList();
	        foreach($products as $k=>$row){
	            foreach($categoriesId as $catId){
	                if($row->product_id==$catId->product_id){
	                    $products[$k]->categories_id[0]=$catId->category_id;
	                }
	            }
	        }
	        $queryImage = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\'product\' ORDER BY file_id ASC';
	        $db->setQuery($queryImage);
	        $images = $db->loadObjectList();
	        foreach($products as $k=>$row){
	            foreach($images as $image){
	                if($row->product_id==$image->file_ref_id){
	                    foreach(get_object_vars($image) as $key => $name){
	                        $products[$k]->images[0]->$key = $name;
	                    }
	                }
	            }
	        }
	        $db->setQuery('SELECT * FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',',$ids).')');
	        $variants = $db->loadObjectList();
	        if(!empty($variants)){
	            foreach($products as $k => $product){
	                foreach($variants as $variant){
	                    if($product->product_id==$variant->variant_product_id){
	                          $products[$k]->has_options = true;
	                          break;
	                    }
	                  }
	            }
	        }
	    }
	    else{
	        return true;
	    }
	    $zone_id=hikashop_getZone();
	    $currencyClass = hikashop_get('class.currency');
	    $config =& hikashop_config();
	    $main_currency = (int)$config->get('main_currency',1);
	    $currencyClass->getListingPrices($products,$zone_id,$main_currency,'cheapest');
	    $uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS);
	    $uploadFolder = rtrim($uploadFolder,DS).DS;
	    $this->uploadFolder_url = str_replace(DS,'/',$uploadFolder);
	    $this->uploadFolder = JPATH_ROOT.DS.$uploadFolder;
	    $app =& JFactory::getApplication();
	    $this->thumbnail = $config->get('thumbnail',1);
	    $this->thumbnail_x=$config->get('thumbnail_x',100);
	    $this->thumbnail_y=$config->get('thumbnail_y',100);
	    $this->main_thumbnail_x=$this->thumbnail_x;
	    $this->main_thumbnail_y=$this->thumbnail_y;
	    $this->main_uploadFolder_url = $this->uploadFolder_url;
	    $this->main_uploadFolder = $this->uploadFolder;
		$doc_description = $config->get('hikarss_description','');
		$doc_title = $config->get('hikarss_name','');
		if(!empty($doc_title)){
			$doc->title = $doc_title;
		}
		if(!empty($doc_description)){
			$doc->description = $doc_description;
		}
		$imageHelper = hikashop_get('helper.image');
		foreach ( $products as $product )
		{
			$title = $this->escape( $product->product_name );
			$title = html_entity_decode( $title );
			$link = JURI::base().'index.php?option=com_hikashop&amp;ctrl=product&amp;task=show&amp;cid='.$product->product_id.'&amp;name='.$product->alias.'&amp;Itemid='.$Itemid.'&amp;category_pathway='.$product->category_id;
    		if($product->prices['0']->price_value_with_tax != 0 ){
				$desc = $product->product_description.JText::_('CART_PRODUCT_PRICE').' : '.$currencyClass->format($product->prices[0]->price_value_with_tax,$product->prices[0]->price_currency_id);
    		}
    		else{
    			$desc= $product->product_description.JText::_('FREE_PRICE');
    		}
    		$desc = preg_replace('#<hr *id="system-readmore" */>#i','',$desc);
    		$description = '<table><tr><td>'.$imageHelper->display(@$product->images[0]->file_path,false,$title, '' , '' , $imageHelper->main_thumbnail_x, $imageHelper->main_thumbnail_y).'</td><td>'.$desc.'</td></tr></table>';
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $product->product_created;
			$item->category   	= $product->category_id;
			$doc->addItem( $item );
		}
	}
}
