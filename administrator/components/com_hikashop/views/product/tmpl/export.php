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
ob_clean();
ob_start();
$eol= "\r\n";
$characteristic = hikashop_get('class.characteristic');
$characteristic->loadConversionTables($this);
$config =& hikashop_config();
$separator = $config->get('csv_separator',";");
$db =& JFactory::getDBO();
$columnsTable = $db->getTableFields(hikashop_table('product'));
$columnsArray = reset($columnsTable);
$columns = $products_columns = array_keys($columnsArray);
$product_table_count = count($columns);
$columns['parent_category']='parent_category';
$columns['categories_image']='categories_image';
$columns['categories']='categories';
$columns['price_value']='price_value';
$columns['price_currency_id']='price_currency_id';
$columns['price_min_quantity']='price_min_quantity';
$columns['price_access']='price_access';
$columns['files']='files';
$columns['images']='images';
$columns['related']='related';
$columns['options']='options';
$characteristicsColumns = array();
if(!empty($this->characteristics)){
	foreach($this->characteristics as $characteristic){
		if(empty($characteristic->characteristic_parent_id)){
			$columns[$characteristic->characteristic_value]=$characteristic->characteristic_value;
			$characteristicsColumns[]=$characteristic->characteristic_value;
		}
	}
}
$after_category_çcount = count($columns)-($product_table_count+3);
$output='';
foreach($columns as $column){
	if(!empty($column)){
		$output.= '"'.str_replace('"','\\'.'"',$column).'"';
	}
	$output.= $separator;
}
echo rtrim($output,$separator).$eol;
$output='';
if(!empty($this->categories)){
	foreach($this->categories as $category){
		echo '"'.str_repeat('"'.$separator.'"',$product_table_count-1).'"'.$separator;
		if(!empty($category->category_parent_id) && isset($this->categories[$category->category_parent_id])){
			echo '"'.str_replace('"','""',$this->categories[$category->category_parent_id]->category_name).'"'.$separator;
		}else{
			echo '""'.$separator;
		}
		if(!empty($category->file_path)){
			echo '"'.str_replace('"','""',$category->file_path).'"'.$separator;
		}else{
			echo '""'.$separator;
		}
		echo '"'.str_replace('"','""',$category->category_name).'"'.$separator.'"'.str_repeat('"'.$separator.'"',$after_category_çcount-1).'"'.$eol;
	}
}
if(!empty($this->products)){
	foreach($this->products as $k => $product){
		if($product->product_type=="variant"){
			$this->products[$k]->product_parent_id = str_replace('"','""',$this->products[$product->product_parent_id]->product_code);
		}
	}
	foreach($this->products as $product){
		foreach($products_columns as $column){
			echo '"'.str_replace('"','""',$product->$column).'"'.$separator;
		}
		$categories = array();
		if(!empty($product->categories)){
			foreach($product->categories as $category){
				if(!empty($this->categories[$category])){
					$categories[]=str_replace(array('"',',',';'),array('""','\\,','\\;'),$this->categories[$category]->category_name);
				}
			}
		}
		echo '""'.$separator.'""'.$separator;
		if(!empty($categories)){
			echo '"'.implode($separator,$categories).'"'.$separator;
		}else{
			echo '""'.$separator;
		}
		$values = array();
		$codes = array();
		$qtys = array();
		$accesses = array();
		if(!empty($product->prices)){
			foreach($product->prices as $price){
				$values[]=$price->price_value;
				$codes[]=$this->currencies[$price->price_currency_id]->currency_code;
				$qtys[]=$price->price_min_quantity;
				$accesses[]=$price->price_access;
			}
		}
		if(empty($values)){
			echo '""'.$separator.'""'.$separator.'""'.$separator.'""'.$separator;
		}else{
			echo '"'.implode('|',$values).'"'.$separator.'"'.implode('|',$codes).'"'.$separator.'"'.implode('|',$qtys).'"'.$separator.'"'.implode('|',$accesses).'"'.$separator;
		}
		$files = array();
		if(!empty($product->files)){
			foreach($product->files as $file){
				$files[]=str_replace(array('"',',',';'),array('""','\\,','\\;'),$file->file_path);
			}
		}
		if(empty($files)){
			echo '""'.$separator;
		}else{
			echo '"'.implode($separator,$files).'"'.$separator;
		}
		$images = array();
		if(!empty($product->images)){
			foreach($product->images as $image){
				$images[]=str_replace(array('"',',',';'),array('""','\\,','\\;'),$image->file_path);
			}
		}
		if(empty($images)){
			echo '""'.$separator;
		}else{
			echo '"'.implode($separator,$images).'"'.$separator;
		}
		$related = array();
		if(!empty($product->related)){
			foreach($product->related as $rel){
				$related[]=str_replace(array('"',',',';'),array('""','\\,','\\;'),@$this->products[$rel]->product_code);
			}
		}
		if(empty($related)){
			echo '""'.$separator;
		}else{
			echo '"'.implode($separator,$related).'"'.$separator;
		}
		$options = array();
		if(!empty($product->options)){
			foreach($product->options as $rel){
				$options[]=str_replace(array('"',',',';'),array('""','\\,','\\;'),@$this->products[$rel]->product_code);
			}
		}
		if(empty($options)){
			echo '""';
		}else{
			echo '"'.implode($separator,$options).'"';
		}
		if(!empty($product->variant_links)){
			echo $separator;
			$characteristics=array();
			if(!empty($characteristicsColumns)){
				foreach($product->variant_links as $char_id){
					if(!empty($this->characteristics[$char_id])){
						$char = $this->characteristics[$char_id];
						if(!empty($this->characteristics[$char->characteristic_parent_id])){
							$key = $this->characteristics[$char->characteristic_parent_id]->characteristic_value;
							$characteristics[$key] = str_replace('"','""',$char->characteristic_value);
						}
					}
				}
				foreach($characteristicsColumns as $characteristic){
					echo '"'.@$characteristics[$characteristic].'"'.$separator;
				}
			}
		}elseif(!empty($characteristicsColumns)){
			echo $separator;
			echo str_repeat('""'.$separator,count($characteristicsColumns)-1).'""';
		}
		echo $eol;
	}
}
$data = ob_get_clean();
header("Pragma: public");
header("Expires: 0"); // set expiration time
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment; filename=hikashopexport.csv;");
header("Content-Transfer-Encoding: binary");
header('Content-Length: '.strlen($data));
echo $data;
exit;