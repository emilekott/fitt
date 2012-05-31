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
define('MAX_IMPORT_ID', 12); //number of steps of VM import
class hikashopImportHelper{
	var $template = null;
	var $totalInserted = 0;
	var $totalTry = 0;
	var $totalValid = 0;
	var $listSeparators = array(';',',','|',"\t");
	var $perBatch = 50;
	var $codes = array();
	var $characteristics = array();
	var $characteristicsConversionTable = array();
	var $characteristicColumns = array();
	var $countVariant = true;
	var $overwrite = false;
	var $products_already_in_db = array();
	var $new_variants_in_db = array();
	var $columnNamesConversionTable = array();
	var $createCategories = false;
	var $header_errors = true;
	var $force_published = true;
	var $tax_category=0;
	var $default_file = '';
	var $db = null;
	var $options = null;
	var $vmImgDir = null;
	var $token = '';
	var $refreshPage = false;
	function HikashopImportHelper(){
		$this->fields = array('product_weight','product_description','product_meta_description','product_tax_id','product_vendor_id','product_manufacturer_id','product_url','product_keywords','product_weight_unit','product_dimension_unit','product_width','product_length','product_height','product_max_per_order');
		$fieldClass = hikashop_get('class.field');
		$userFields = $fieldClass->getData('','product');
		if(!empty($userFields)){
			foreach($userFields as $k => $v){
				if($v->field_type!='customtext'){
					$this->fields[]=$k;
				}
			}
		}
		$this->all_fields = array_merge($this->fields,array('product_name','product_published','product_code','product_created','product_modified','product_sale_start','product_sale_end','product_type','product_quantity'));
		$this->db =& JFactory::getDBO();
		$columnsProductTable = $this->db->getTableFields(hikashop_table('product'));
		$this->columnsProductTable = array_keys($columnsProductTable[hikashop_table('product')]);
		$characteristic = hikashop_get('class.characteristic');
		$characteristic->loadConversionTables($this);
		$this->volumeHelper =& hikashop_get('helper.volume');
		$this->weightHelper =& hikashop_get('helper.weight');
		$class = hikashop_get('class.category');
		$this->mainProductCategory = 'product';
		$class->getMainElement($this->mainProductCategory);
		$this->mainManufacturerCategory = 'manufacturer';
		$class->getMainElement($this->mainManufacturerCategory);
		$config =& hikashop_config();
		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$this->uploadFolder = JPATH_ROOT.DS.$uploadFolder;
		$this->uploadFolder_url = str_replace(DS,'/',$uploadFolder);
		$this->uploadFolder_url = HIKASHOP_LIVE.$this->uploadFolder_url;
		jimport('joomla.filesystem.file');
	}
	function addTemplate($template_product_id){
		if(hikashop_level(2)){
			if($template_product_id){
				$productClass = hikashop_get('class.product');
				if($productClass->getProducts($template_product_id) && !empty($productClass->products)){
					$key = key($productClass->products);
					$this->template =& $productClass->products[$key];
				}
			}
		}
	}
	function importFromFolder($type,$delete,$uploadFolder){
		$config =& hikashop_config();
		$allowed = explode(',',strtolower($config->get('allowed'.$type)));
		$folder = 'image';
		if($type=='files'){
			$folder = 'file';
		}
		$uploadFolder = rtrim(JPath::clean(html_entity_decode($uploadFolder)),DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$uploadFolder)){
			if(!$uploadFolder[0]=='/' || !is_dir($uploadFolder)){
				$uploadFolder = JPath::clean(HIKASHOP_ROOT.DS.trim($uploadFolder,DS.' ').DS);
			}
		}
		$fileClass = hikashop_get('class.file');
		if(!$fileClass->checkFolder($uploadFolder)){
			return false;
		}
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$app =& JFactory::getApplication();
		$files = JFolder::files($uploadFolder);
		if(!empty($files)){
			$imageHelper = hikashop_get('helper.image');
			$uploadPath = $fileClass->getPath($folder);
			if(!empty($this->template->variants)){
				$this->countVariant = false;
			}
			foreach($files as $file){
				if(in_array($file,array('index.html','.htaccess'))) continue;
				$extension = strtolower(substr($file,strrpos($file,'.')+1));
				if(!in_array($extension,$allowed)){
					$app->enqueueMessage(JText::sprintf('FILE_SKIPPED',$file));
					continue;
				}
				$newProduct = null;
				$newProduct->$type = $file;
				$this->_checkData($newProduct);
				$this->totalTry++;
				if(!empty($newProduct->product_code)){
					$this->totalValid++;
					$products = array($newProduct);
					if(!empty($this->template->variants)){
						foreach($this->template->variants as $variant){
							$copy = (PHP_VERSION < 5) ? $variant : clone($variant);
							unset($copy->product_id);
							$copy->product_parent_id = $newProduct->product_code;
							$copy->product_code = $newProduct->product_code.'_'.$copy->product_code;
							$products[]=$copy;
						}
					}
					$this->_insertProducts($products);
					if($delete){
						JFile::move($uploadFolder.$file,$uploadPath.$file);
					}else{
						JFile::copy($uploadFolder.$file,$uploadPath.$file);
					}
					if($type!='files'){
						$imageHelper->resizeImage($file);
					}
				}
			}
			$this->_deleteUnecessaryVariants();
		}
		$app->enqueueMessage(JText::sprintf('IMPORT_REPORT',$this->totalTry,$this->totalInserted,$this->totalTry - $this->totalValid,$this->totalValid - $this->totalInserted));
	}
	function copyProduct($product_id){
		if(!hikashop_level(2)){
			return false;
		}
		$this->addTemplate($product_id);
		$newProduct = null;
		$newProduct->product_code = $this->template->product_code.'_copy'.rand();
		$this->_checkData($newProduct);
		if(!empty($newProduct->product_code)){
			$products = array($newProduct);
			if(!empty($this->template->variants)){
				foreach($this->template->variants as $variant){
					$copy = (PHP_VERSION < 5) ? $variant : clone($variant);
					$copy->product_parent_id = $newProduct->product_code;
					$copy->product_code = $copy->product_code.'_copy'.rand();
					unset($copy->product_id);
					$products[]=$copy;
				}
			}
			JPluginHelper::importPlugin( 'hikashop' );
			$dispatcher =& JDispatcher::getInstance();
			$do = true;
			$dispatcher->trigger( 'onBeforeProductCopy', array( & $this->template, & $products[0], & $do) );
			if(!$do){
				return false;
			}
			$this->_insertProducts($products);
			$dispatcher->trigger( 'onAfterProductCopy', array( & $this->template, & $products[0]) );
		}
		return true;
	}
	function importFromFile(&$importFile){
		$app =& JFactory::getApplication();
		if(empty($importFile['name'])){
			$app->enqueueMessage(JText::_('BROWSE_FILE'),'notice');
			return false;
		}
		$this->charsetConvert = JRequest::getString('charsetconvert','');
		jimport('joomla.filesystem.file');
		$config =& hikashop_config();
		$allowedFiles = explode(',',strtolower($config->get('allowedfiles')));
		$uploadFolder = JPath::clean(html_entity_decode($config->get('uploadfolder')));
		$uploadFolder = trim($uploadFolder,DS.' ').DS;
		$uploadPath = JPath::clean(HIKASHOP_ROOT.$uploadFolder);
		if(!is_dir($uploadPath)){
			jimport('joomla.filesystem.folder');
			JFolder::create($uploadPath);
			JFile::write($uploadPath.'index.html','<html><body bgcolor="#FFFFFF"></body></html>');
		}
		if(!is_writable($uploadPath)){
			@chmod($uploadPath,'0755');
			if(!is_writable($uploadPath)){
				$app->enqueueMessage(JText::sprintf( 'WRITABLE_FOLDER',$uploadPath), 'notice');
			}
		}
		$attachment = null;
		$attachment->filename = strtolower(JFile::makeSafe($importFile['name']));
		$attachment->size = $importFile['size'];
		$attachment->extension = strtolower(substr($attachment->filename,strrpos($attachment->filename,'.')+1));
		if(!in_array($attachment->extension,$allowedFiles)){
			$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',$attachment->extension,$config->get('allowedfiles')), 'notice');
			return false;
		}
		if ( !move_uploaded_file($importFile['tmp_name'], $uploadPath . $attachment->filename)) {
			if(!JFile::upload($importFile['tmp_name'], $uploadPath . $attachment->filename)){
				$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$importFile['tmp_name'],$uploadPath . $attachment->filename), 'error');
			}
		}
		hikashop_increasePerf();
		$contentFile = file_get_contents($uploadPath . $attachment->filename);
		if(!$contentFile){
			$app->enqueueMessage(JText::sprintf( 'FAIL_OPEN',$uploadPath . $attachment->filename), 'error');
			return false;
		};
		unlink($uploadPath . $attachment->filename);
		return $this->handleContent($contentFile);
	}
	function handleContent(&$contentFile){
		$app =& JFactory::getApplication();
		$contentFile = str_replace(array("\r\n","\r"),"\n",$contentFile);
		$this->importLines = explode("\n", $contentFile);
		$this->i = 0;
		while(empty($this->header)){
			$this->header = trim($this->importLines[$this->i]);
			$this->i++;
		}
		if(!$this->_autoDetectHeader()){
			return false;
		}
		$this->numberColumns = count($this->columns);
		$importProducts = array();
		$encodingHelper = hikashop_get('helper.encoding');
		while ($data = $this->_getProduct()) {
			$this->totalTry++;
			$newProduct = null;
			foreach($data as $num => $value){
				if(!empty($this->columns[$num])){
					$field = $this->columns[$num];
					if( strpos('|',$field) !== false ) { $field = str_replace('|','__tr__',$field); }
					$newProduct->$field = trim($value,'\'" ');
					if(!empty($this->charsetConvert)){
						$newProduct->$field = $encodingHelper->change($newProduct->$field,$this->charsetConvert,'UTF-8');
					}
				}
			}
			$this->_checkData($newProduct,true);
			if(!empty($newProduct->product_code)){
				$importProducts[] = $newProduct;
				if(count($this->currentProductVariants)){
					foreach($this->currentProductVariants as $variant){
						$importProducts[] = $variant;
					}
				}
				$this->totalValid++;
			}
			if( $this->totalValid%$this->perBatch == 0){
				$this->_insertProducts($importProducts);
				$importProducts = array();
			}
		}
		if(!empty($importProducts)){
			$this->_insertProducts($importProducts);
		}
		$this->_deleteUnecessaryVariants();
		$app->enqueueMessage(JText::sprintf('IMPORT_REPORT',$this->totalTry,$this->totalInserted,$this->totalTry - $this->totalValid,$this->totalValid - $this->totalInserted));
		return true;
	}
	function _deleteUnecessaryVariants(){
		if(!empty($this->products_already_in_db)){
			$this->db->setQuery('SELECT product_id FROM '.hikashop_table('product').' WHERE product_parent_id IN ('.implode(',',$this->products_already_in_db).') AND product_id NOT IN ('.implode(',',$this->new_variants_in_db).') AND product_type=\'variant\'');
			$variants_to_be_deleted = $this->db->loadResultArray();
			if(!empty($variants_to_be_deleted)){
				$productClass = hikashop_get('class.product');
				$productClass->delete($variants_to_be_deleted);
			}
		}
	}
	function &_getProduct(){
		$false = false;
		if(!isset($this->importLines[$this->i])){
			return $false;
		}
		if(empty($this->importLines[$this->i])){
			$this->i++;
			return $this->_getProduct();
		}
		$quoted = false;
		$dataPointer=0;
		$data = array('');
		while($data!==false && isset($this->importLines[$this->i]) && (count($data) < $this->numberColumns||$quoted)){
			$k = 0;
			$total = strlen($this->importLines[$this->i]);
			while($k < $total){
				switch($this->importLines[$this->i][$k]){
					case '"':
						if($k && isset($this->importLines[$this->i][$k+1]) && $this->importLines[$this->i][$k+1]=='"'){
							$data[$dataPointer].='"';
							$k++;
						}elseif($quoted){
							$quoted = false;
						}elseif(empty($data[$dataPointer])){
							$quoted = true;
						}else{
							$data[$dataPointer].='"';
						}
						break;
					case $this->separator:
						if(!$quoted){
							$data[]='';
							$dataPointer++;
							break;
						}
					default:
						$data[$dataPointer].=$this->importLines[$this->i][$k];
						break;
				}
				$k++;
			}
			$this->_checkLineData($data);
			if(count($data) < $this->numberColumns||$quoted){
				$data[$dataPointer].="\r\n";
			}
			$this->i++;
		}
		if($data!=false) $this->_checkLineData($data,false);
		return $data;
	}
	function _checkLineData(&$data,$type=true){
		if($type){
			$not_ok = count($data) > $this->numberColumns;
		}else{
			$not_ok = count($data) != $this->numberColumns;
		}
		if($not_ok){
			static $errorcount = 0;
			if(empty($errorcount)){
				$app =& JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('IMPORT_ARGUMENTS',$this->numberColumns),'error');
			}
			$errorcount++;
			if($errorcount<20){
				$app =& JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('IMPORT_ERRORLINE',$this->importLines[$this->i-1]),'notice');
			}elseif($errorcount == 20){
				$app =& JFactory::getApplication();
				$app->enqueueMessage('...','notice');
			}
			$data = $this->_getProduct();
		}
	}
	function _checkData(&$product,$main=false){
		$this->currentProductVariants = array();
		if(empty($product->product_created)){
			$product->product_created = time();
		}elseif(!is_numeric($product->product_created)){
			$product->product_created = strtotime($product->product_created);
		}
		if(empty($product->product_modified)){
			$product->product_modified = time();
		}elseif(!is_numeric($product->product_modified)){
			$product->product_modified = strtotime($product->product_modified);
		}
		if(empty($product->product_sale_start)){
			if(!empty($this->template->product_sale_start)){
				$product->product_sale_start = $this->template->product_sale_start;
			}
		}elseif(!is_numeric($product->product_sale_start)){
			$product->product_sale_start = strtotime($product->product_sale_start);
		}
		if(empty($product->product_sale_end)){
			if(!empty($this->template->product_sale_end)){
				$product->product_sale_end = $this->template->product_sale_end;
			}
		}elseif(!is_numeric($product->product_sale_end)){
			$product->product_sale_end = strtotime($product->product_sale_end);
		}
		if(empty($product->product_type)){
			if(empty($product->product_parent_id)){
				$product->product_type='main';
			}else{
				if(!empty($product->product_parent_id) && !empty($product->product_code) && $product->product_parent_id == $product->product_code){
					$app =& JFactory::getApplication();
					$app->enqueueMessage('The product '.$product->product_code.' has the same value in the product_parent_id and product_code fields which is not possible ( a main product cannot be a variant at the same time ). This product has been considered as a main product by HikaShop and has been imported as such.');
					$product->product_type='main';
					$product->product_parent_id=0;
				}else{
					$product->product_type='variant';
				}
			}
		}else{
			if(!in_array($product->product_type,array('main','variant'))){
				$product->product_type = 'main';
			}
		}
		if($product->product_type=='main'){
			if(!empty($product->product_parent_id)){
				$app =& JFactory::getApplication();
				$app->enqueueMessage('The product '.@$product->product_code.' should have an empty value instead of the value '.$product->product_parent_id.' in the field product_parent_id as it is a main product (not a variant) and thus doesn\'t have any parent.','error');
			}
		}
		if(!isset($product->product_tax_id) || strlen($product->product_tax_id)<1){
			$product->product_tax_id = $this->tax_category;
		}else{
			if(!is_numeric($product->product_tax_id)){
				$id = $this->_getCategory($product->product_tax_id,0,!$this->createCategories,'tax');
				if(empty($id) && $this->createCategories){
					$id = $this->_createCategory($product->product_tax_id,0,'tax');
				}
				$product->product_tax_id = $id;
			}
		}
		if(!empty($product->product_manufacturer_id) && !is_numeric($product->product_manufacturer_id)){
			$id = $this->_getCategory($product->product_manufacturer_id,0,!$this->createCategories,'manufacturer');
			if(empty($id) && $this->createCategories){
				$id = $this->_createCategory($product->product_manufacturer_id,0,'manufacturer');
			}
			$product->product_manufacturer_id = $id;
		}
		if(!isset($product->product_quantity) || strlen($product->product_quantity)<1){
			if(!empty($this->template->product_quantity)){
				$product->product_quantity=$this->template->product_quantity;
			}
		}
		if(isset($product->product_quantity) && !is_numeric($product->product_quantity)){
			$product->product_quantity=-1;
		}
		foreach($this->fields as $field){
			if(empty($product->$field)&&!empty($this->template->$field)){
				$product->$field=$this->template->$field;
			}
		}
		if(empty($product->product_dimension_unit)){
			$product->product_dimension_unit=$this->volumeHelper->getSymbol();
		}else{
			$product->product_dimension_unit= strtolower($product->product_dimension_unit);
		}
		if(empty($product->product_weight_unit)){
			$product->product_weight_unit=$this->weightHelper->getSymbol();
		}else{
			$product->product_weight_unit= strtolower($product->product_weight_unit);
		}
		if(empty($product->product_name)){
			if(!empty($product->files)){
				$this->_separate($product->files);
				$product->product_name=substr($product->files[0],0,strrpos($product->files[0],'.'));
			}elseif(!empty($product->images)){
				$this->_separate($product->images);
				$product->product_name=substr($product->images[0],0,strrpos($product->images[0],'.'));
			}
		}
		if(!empty($product->product_published)){
			$product->product_published=1;
		}
		if(!isset($product->product_published)){
			if(!empty($this->template)){
				$product->product_published = $this->template->product_published;
			}
		}
		if(!empty($product->price_value)){
			$product->prices = array();
			if(strpos($product->price_value,'|')===false){
				$price = null;
				$price->price_value = hikashop_toFloat($product->price_value);
				if(!empty($this->price_fee)){
					$price->price_value += $price->price_value*hikashop_toFloat($this->price_fee)/100;
				}
				$price->price_min_quantity = (int)@$product->price_min_quantity;
				if($price->price_min_quantity==1){
					$price->price_min_quantity=0;
				}
				if(empty($product->price_access)){
					$price->price_access = 'all';
				}else{
					$price->price_access = $product->price_access;
				}
				if(!empty($product->price_currency_id)){
					if(!is_numeric($product->price_currency_id)){
						$product->price_currency_id = $this->_getCurrency($product->price_currency_id);
					}
					$price->price_currency_id = $product->price_currency_id;
				}else{
					$config =& hikashop_config();
					$price->price_currency_id = $config->get('main_currency',1);
				}
				$product->prices[]=$price;
			}else{
				$price_value = explode('|',$product->price_value);
				if(!empty($product->price_min_quantity)){
					$price_min_quantity = explode('|',$product->price_min_quantity);
				}
				if(!empty($product->price_access)){
					$price_access = explode('|',$product->price_access);
				}
				if(!empty($product->price_currency_id)){
					$price_currency_id = explode('|',$product->price_currency_id);
				}
				foreach($price_value as $k => $price_value_one){
					$price = null;
					$price->price_value = hikashop_toFloat($price_value_one);
					if(!empty($this->price_fee)){
						$price->price_value += $price->price_value*hikashop_toFloat($this->price_fee)/100;
					}
					$price->price_min_quantity = (int)@$price_min_quantity[$k];
					if($price->price_min_quantity==1){
						$price->price_min_quantity=0;
					}
					if(empty($price_access[$k])){
						$price->price_access = 'all';
					}else{
						$price->price_access = $price_access[$k];
					}
					if(!empty($price_currency_id[$k])){
						if(!is_numeric($price_currency_id[$k])){
							$price_currency_id[$k] = $this->_getCurrency($price_currency_id[$k]);
						}
						$price->price_currency_id = $price_currency_id[$k];
					}else{
						$config =& hikashop_config();
						$price->price_currency_id = $config->get('main_currency',1);
					}
					$product->prices[]=$price;
				}
			}
		}
		if(!empty($product->files) && !is_array($product->files)){
			$this->_separate($product->files);
			$unset = array();
			foreach($product->files as $k => $file){
				if(substr($file,0,7)=='http://'){
					$parts = explode('/',$file);
					$name = array_pop($parts);
					if(!file_exists($this->uploadFolder.$name)){
						$data = @file_get_contents($file);
						if(empty($data) && !empty($this->default_file)){
							$name = $this->default_file;
						}else{
							JFile::write($this->uploadFolder.$name,$data);
						}
					}
					if(filesize($this->uploadFolder.$name)){
						$product->files[$k] = $name;
					}else{
						$unset[]=$k;
					}
				}
			}
			if(!empty($unset)){
				foreach($unset as $k){
					unset($product->files[$k]);
				}
			}
		}
		if(!empty($product->images) && !is_array($product->images)){
			$this->_separate($product->images);
			$unset = array();
			foreach($product->images as $k => $image){
				if(substr($image,0,7)=='http://'){
					$parts = explode('/',$image);
					$name = array_pop($parts);
					if(!file_exists($this->uploadFolder.$name)){
						JFile::write($this->uploadFolder.$name,file_get_contents($image));
					}
					if(filesize($this->uploadFolder.$name)){
						$product->images[$k] = $name;
					}else{
						$unset[]=$k;
					}
				}
			}
			if(!empty($unset)){
				foreach($unset as $k){
					unset($product->images[$k]);
				}
			}
		}
		if(!empty($product->related) && !is_array($product->related)){
			$this->_separate($product->related);
		}
		if(!empty($product->options) && !is_array($product->options)){
			$this->_separate($product->options);
		}
		if($product->product_type=='variant'){
			$product->categories = null;
		}else{
			if(!empty($product->categories)){
				if(!is_array($product->categories)){
					$this->_separate($product->categories);
				}
				$parent_id=0;
				if($this->createCategories && !empty($product->parent_category)){
					$this->_separate($product->parent_category);
					$parent_id = array();
					foreach($product->parent_category as $k => $parent_category){
						$parent_id[$k] = $this->_getCategory($parent_category,0,false,'product');
						if(empty($parent_id[$k])){
							$parent_id[$k] = $this->_createCategory($parent_category);
						}
					}
				}
				if($this->createCategories && !empty($product->categories_image)){
					$unset = array();
					$this->_separate($product->categories_image);
					foreach($product->categories_image as $k => $image){
						if(substr($image,0,7)=='http://'){
							$parts = explode('/',$image);
							$name = array_pop($parts);
							if(!file_exists($this->uploadFolder.$name)){
								JFile::write($this->uploadFolder.$name,file_get_contents($image));
							}
							if(filesize($this->uploadFolder.$name)){
								$product->categories_image[$k] = $name;
							}else{
								$unset[]=$k;
							}
						}
					}
					if(!empty($unset)){
						foreach($unset as $k){
							unset($product->categories_image[$k]);
						}
					}
				}
				foreach($product->categories as $k => $v){
					if(!is_numeric($v)){
						$pid = 0;
						if(is_array($parent_id)){
							if(!empty($parent_id[$k])){
								$pid = $parent_id[$k];
							}elseif(!empty($parent_id[0])){
								$pid = $parent_id[0];
							}
						}
						$id = $this->_getCategory($v,0,!$this->createCategories,'product',$pid);
						if(empty($id) && $this->createCategories){
							if(!empty($product->categories_image[$k])){
								$id = $this->_createCategory($v,$pid,'product',$product->categories_image[$k]);
							}else{
								$id = $this->_createCategory($v,$pid,'product');
							}
						}
						$product->categories[$k] = $id;
					}
				}
			}
		}
		if(!empty($product->categories_ordering)){
			$this->_separate($product->categories_ordering);
		}
		if(empty($product->product_access)){
			if(!empty($this->template)){
				$product->product_access = @$this->template->product_access;
			}else{
				$product->product_access = 'all';
			}
		}
		if(!isset($product->product_contact) && !empty($this->template)){
			$product->product_contact = @$this->template->product_contact;
		}
		if(!isset($product->product_group_after_purchase) && !empty($this->template)){
			$product->product_group_after_purchase = @$this->template->product_group_after_purchase;
		}
		if(hikashop_level(2) && !empty($product->product_access)){
			if(!is_array($product->product_access)){
				if(!in_array($product->product_access,array('none','all'))){
					if(!is_array($product->product_access)){
						$this->_separate($product->product_access);
					}
				}
			}
			if(is_array($product->product_access)){
				$accesses = array();
				foreach($product->product_access as $access){
					if(empty($access))continue;
					if(!is_numeric($access)){
						$access = $this->_getAccess($access);
						if(empty($access))continue;
					}
					$accesses[] = $access;
				}
				$product->product_access = ','.implode(',',$accesses).',';
			}
		}
		if(!empty($this->characteristicColumns)){
			foreach($this->characteristicColumns as $column){
				if(isset($product->$column) && strlen($product->$column)>0){
					if($product->product_type=='main' && !empty($this->characteristicsConversionTable[$column])){
						if(!isset($product->variant_links)){
							$product->variant_links=array();
						}
						$product->variant_links[]=$this->characteristicsConversionTable[$column];
					}
					if(function_exists('mb_strtolower')){
						$key = mb_strtolower(trim($product->$column,'" '));
					}else{
						$key = strtolower(trim($product->$column,'" '));
					}
					if(!empty($this->characteristicsConversionTable[$column.'_'.$key])){
						$key = $column.'_'.$key;
					}
					if(!empty($this->characteristicsConversionTable[$key])){
						if(!isset($product->variant_links)){
							$product->variant_links=array();
						}
						$product->variant_links[]=$this->characteristicsConversionTable[$key];
					}
				}
			}
		}
		if(empty($product->product_code)&&!empty($product->product_name)){
			$test=preg_replace('#[^a-z0-9_-]#i','',$product->product_name);
			if(empty($test)){
				static $last_pid = null;
				if($last_pid===null){
					$query = 'SELECT MAX(`product_id`) FROM '.hikashop_table('product');
					$this->database->setQuery($query);
					$last_pid = (int)$this->database->loadResult();
				}
				$last_pid++;
				$product->product_code = 'product_'.$last_pid;
			}else{
				$product->product_code = preg_replace('#[^a-z0-9_-]#i','_',$product->product_name);
			}
		}
		if(empty($product->product_name)&&!empty($this->template->product_name)){
			$product->product_name = $this->template->product_name;
		}
		if( !empty($this->translateColumns) ) {
			foreach($this->translateColumns as $k => $v) {
				if( !empty($product->$v) ) {
					list($name,$lng) = explode('__tr__',$v);
					if( $lng == $this->locale ) {
						$product->$name =& $product->$v;
					} else {
						if( isset($this->translateLanguages[$lng]) ) {
							if( !isset($product->translations) ) {
								$product->translations = array();
							}
							$obj = null;
							$obj->language_id = $this->translateLanguages[$lng];
							$obj->reference_table = 'hikashop_product';
							$obj->reference_field = $name;
							$obj->value =& $product->$v;
							$obj->modified_by = 0; //TODO
							$obj->published = 1;
							$product->translations[] = $obj;
						}
					}
				}
			}
		}
		$unset = array();
		foreach(get_object_vars($product) as $column=>$value){
			if(!empty($this->columnNamesConversionTable[$column]) && is_array($this->columnNamesConversionTable[$column])){
				if(!empty($this->columnNamesConversionTable[$column]['append'])){
					$new_column = $this->columnNamesConversionTable[$column]['append'];
					if(in_array($column,array('files','images'))){
						if(is_array($value)){
							$tmp=array();
							foreach($value as $v){
								$tmp[]='<a href="'.$this->uploadFolder_url.$v.'">'.$v.'</a>';
							}
							$value = implode(',',$tmp);
						}else{
							$value='<a href="'.$this->uploadFolder_url.$value.'">'.$value.'</a>';
						}
					}
					$trans_string = 'HIKASHOP_FEED_'.strtoupper($column);
					$trans = JText::_($trans_string);
					if($trans_string==$trans){
						$trans=$column;
					}
					$product->$new_column.='<div id="hikashop_product_'.$column.'">'.$trans.':'.$value.'</div>';
					$unset[]=$column;
				}
				if(!empty($this->columnNamesConversionTable[$column]['copy'])){
					$new_column = $this->columnNamesConversionTable[$column]['copy'];
					$product->$new_column=$value;
				}
			}
		}
		if($product->product_type=='main' && $main && !isset($product->product_parent_id)){
			if(!empty($this->template->variants)){
				foreach($this->template->variants as $variant){
					$copy = (PHP_VERSION < 5) ? $variant : clone($variant);
					unset($copy->product_id);
					$copy->product_parent_id = $product->product_code;
					$copy->product_code = $product->product_code.'_'.$copy->product_code;
					$this->currentProductVariants[]=$copy;
				}
			}
		}
		if(!empty($unset)){
			foreach($unset as $u){
				unset($product->$u);
			}
		}
	}
	function _createCategory($category,$parent_id=0,$type='product',$img=''){
		$obj=null;
		$obj->category_name = $category;
		$obj->category_type = $type;
		if(empty($parent_id)){
			$name = 'main'.ucfirst($type).'Category';
			$parent_id = $this->$name;
		}
		$obj->category_parent_id = $parent_id;
		$class = hikashop_get('class.category');
		$new_id = $class->save($obj,false);
		$this->_getCategory($obj->category_namekey,$new_id,true,$type,$parent_id);
		$this->_getCategory($obj->category_name,$new_id,true,$type,$parent_id);
		if($new_id && !empty($img)){
			$db =& JFactory::getDBO();
			$base = substr($img,0,strrpos($img,'.'));
			$db->setQuery('INSERT IGNORE INTO '.hikashop_table('file').' (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ('.$db->Quote($base).',\'\','.$db->Quote($img).',\'category\','.(int)$new_id.');');
			$db->query();
		}
		return $new_id;
	}
	function _getCategory($code,$newId=0,$error=true,$type='product',$parent_id=0){
		static $data=array();
		$namekey = $code;
		$parent_condition = '';
		if(!empty($parent_id)){
			$namekey.='__'.$parent_id;
			$parent_condition = ' AND category_parent_id='.$parent_id;
		}
		if(!empty($newId)){
			$data[$code] = $newId;
			$data[$namekey] = $newId;
		}
		if(!isset($data[$namekey])){
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_namekey='.$this->db->Quote($code).' AND category_type='.$this->db->Quote($type).$parent_condition;
			$this->db->setQuery($query);
			$data[$namekey] = $this->db->loadResult();
			if(empty($data[$namekey])){
				$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_name='.$this->db->Quote($code).' AND category_type='.$this->db->Quote($type).$parent_condition;
				$this->db->setQuery($query);
				$data[$namekey] = $this->db->loadResult();
				if(empty($data[$namekey])){
					if($error){
						$app =& JFactory::getApplication();
						$app->enqueueMessage('The '.$type.' category "'.$code.'" could not be found in the database. Products imported and using this '.$type.' category will be linked to the main '.$type.' category.');
						$name = 'main'.ucfirst($type).'Category';
						$data[$namekey] = @$this->$name;
					}else{
						$data[$namekey] = 0;
					}
				}
			}
		}
		return $data[$namekey];
	}
	function _getRelated($code){
		static $data=array();
		if(!isset($data[$code])){
			$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_code='.$this->db->Quote($code);
			$this->db->setQuery($query);
			$id = $this->db->loadResult();
			if(empty($id)){
				return $code;
			}else{
				$data[$code] = $id;
			}
		}
		return $data[$code];
	}
	function _getAccess($access){
		static $data=array();
		if(!isset($data[$access])){
			if(version_compare(JVERSION,'1.6','<')){
				$query = 'SELECT id FROM '.hikashop_table('core_acl_aro_groups',false).' WHERE name='.$this->db->Quote($access);
			}else{
				$query = 'SELECT id FROM '.hikashop_table('usergroups',false).' WHERE title='.$this->db->Quote($access);
			}
			$this->db->setQuery($query);
			$data[$access] = (int)$this->db->loadResult();
		}
		return $data[$access];
	}
	function _getCurrency($code){
		static $data=array();
		if(!isset($data[$code])){
			$query = 'SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_code='.$this->db->Quote(strtoupper($code));
			$this->db->setQuery($query);
			$data[$code] = $this->db->loadResult();
		}
		return $data[$code];
	}
	function _insertPrices(&$products){
		$values = array();
		$totalValid=0;
		$insert = 'INSERT IGNORE INTO '.hikashop_table('price').' (`price_value`,`price_currency_id`,`price_min_quantity`,`price_product_id`,`price_access`) VALUES (';
		$ids = array();
		foreach($products as $product){
			if(empty($product->prices) && empty($product->hikashop_update)){
				if(!empty($this->template->prices)){
					foreach($this->template->prices as $price){
						$value = array($this->db->Quote($price->price_value),(int)$price->price_currency_id,(int)$price->price_min_quantity,(int)$product->product_id,$this->db->Quote(@$price->price_access));
						$values[] = implode(',',$value);
						$totalValid++;
						if( $totalValid%$this->perBatch == 0){
							$this->db->setQuery($insert.implode('),(',$values).')');
							$this->db->query();
							$totalValid=0;
							$values=array();
						}
					}
				}
			}elseif(!empty($product->prices)){
				$ids[]=(int)$product->product_id;
				foreach($product->prices as $price){
					$value = array($this->db->Quote($price->price_value),(int)$price->price_currency_id,(int)$price->price_min_quantity,(int)$product->product_id,$this->db->Quote(@$price->price_access));
					$values[] = implode(',',$value);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$ids).')');
							$this->db->query();
							$ids=array();
						}
						$this->db->setQuery($insert.implode('),(',$values).')');
						$this->db->query();
						$totalValid=0;
						$values=array();
					}
				}
			}
		}
		if(!empty($values)){
			if(!empty($ids)){
				$this->db->setQuery('DELETE FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$ids).')');
				$this->db->query();
			}
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}
	function _insertCategories(&$products){
		$values = array();
		$totalValid=0;
		$insert = 'INSERT IGNORE INTO '.hikashop_table('product_category').' (`category_id`,`product_id`,`ordering`) VALUES (';
		$ids = array();
		foreach($products as $product){
			if(empty($product->categories) && empty($product->hikashop_update)){
				if(empty($this->template->categories)){
					$product->categories = array($this->mainProductCategory);
				}else{
					foreach($this->template->categories as $k => $id){
						$value = array((int)$id,$product->product_id,(int)@$this->template->categories_ordering[$k]);
						$values[] = implode(',',$value);
						$totalValid++;
						if( $totalValid%$this->perBatch == 0){
							$this->db->setQuery($insert.implode('),(',$values).')');
							$this->db->query();
							$totalValid=0;
							$values=array();
						}
					}
				}
			}
			if(!empty($product->categories)){
				$ids[] = (int)$product->product_id;
				foreach($product->categories as $k => $id){
					$value = array((int)$id,$product->product_id,(int)@$product->categories_ordering[$k]);
					$values[] = implode(',',$value);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$ids).')');
							$this->db->query();
							$ids=array();
						}
						$this->db->setQuery($insert.implode('),(',$values).')');
						$this->db->query();
						$totalValid=0;
						$values=array();
					}
				}
			}
		}
		if(!empty($values)){
			if(!empty($ids)){
				$this->db->setQuery('DELETE FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$ids).')');
				$this->db->query();
			}
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}
	function _insertRelated(&$products,$type='related'){
		$values = array();
		$totalValid=0;
		$insert = 'INSERT IGNORE INTO '.hikashop_table('product_related').' (`product_related_id`,`product_related_type`,`product_id`) VALUES (';
		$ids=array();
		foreach($products as $product){
			if(empty($product->$type) && empty($product->hikashop_update)){
				if(!empty($this->template->$type)){
					foreach($this->template->$type as $id){
						$value = array((int)$id,$this->db->Quote($type),$product->product_id);
						$values[] = implode(',',$value);
						$totalValid++;
						if( $totalValid%$this->perBatch == 0){
							$this->db->setQuery($insert.implode('),(',$values).')');
							$this->db->query();
							$totalValid=0;
							$values=array();
						}
					}
				}
			}elseif(!empty($product->$type)){
				$ids[] = (int)$product->product_id;
				foreach($product->$type as $k => $id){
					if(!is_numeric($id)){
						$id = $this->_getRelated($id);
						$product->{$type}[$k] = $id;
					}
					$value = array((int)$id,$this->db->Quote($type),$product->product_id);
					$values[] = implode(',',$value);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table('product_related').' WHERE product_id IN ('.implode(',',$ids).')');
							$this->db->query();
							$ids=array();
						}
						$this->db->setQuery($insert.implode('),(',$values).')');
						$this->db->query();
						$totalValid=0;
						$values=array();
					}
				}
			}
		}
		if(!empty($values)){
			if(!empty($ids)){
				$this->db->setQuery('DELETE FROM '.hikashop_table('product_related').' WHERE product_id IN ('.implode(',',$ids).')');
				$this->db->query();
			}
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}
	function _insertVariants(&$products){
		$values = array();
		$totalValid=0;
		$insert = 'INSERT IGNORE INTO '.hikashop_table('variant').' (`variant_characteristic_id`,`variant_product_id`) VALUES (';
		$ids = array();
		foreach($products as $product){
			if(empty($product->variant_links)&&!empty($this->template->variant_links) && empty($product->hikashop_update)){
				$product->variant_links = $this->template->variant_links;
			}
			if(!empty($product->variant_links)){
				$ids[] = (int)$product->product_id;
				foreach($product->variant_links as $link){
					$value = array((int)$link,(int)$product->product_id);
					$values[] = implode(',',$value);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',',$ids).')');
							$this->db->query();
							$ids=array();
						}
						$this->db->setQuery($insert.implode('),(',$values).')');
						$this->db->query();
						$totalValid=0;
						$values=array();
					}
				}
			}
		}
		if(!empty($values)){
			if(!empty($ids)){
				$this->db->setQuery('DELETE FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',',$ids).')');
				$this->db->query();
			}
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}
	function _insertTranslations(&$products){
		$value = array();
		$product_translation = false;
		$translations = array();
		foreach($products as $p) {
			if( !empty($p->translations) ) {
				$product_translation = true;
				$translation = reset($p->translations);
				foreach( get_object_vars($translation) as $key => $field){
					$value[] = $key;
				}
				$value[] = 'reference_id';
				break;
			}
		}
		if(!$product_translation) {
			if(empty($this->template->translations) || !empty($product->hikashop_update)) {
				return true;
			}
			$translations =& $this->template->translations;
			$translation = reset($translations);
			foreach(get_object_vars($translation) as $key => $field){
				$value[] = $key;
			}
		}
		$ids = array();
		$values = array();
		$totalValid=0;
		$translation = reset($this->template->translations);
		$value = array();
		if(isset($translation->id)) unset($translation->id);
		foreach(get_object_vars($translation) as $key => $field){
			$value[] = $key;
		}
		$insert = 'INSERT IGNORE INTO '.hikashop_table('jf_content',false).' ('.implode(',',$value).') VALUES (';
		foreach($products as $product){
			if($product_translation) {
				unset($translations);
				$translations =& $product->translations;
			}
			foreach($translations as $translation){
				$translation->reference_id = $product->product_id;
				if(isset($translation->id)) unset($translation->id);
				$value = array();
				foreach(get_object_vars($translation) as $field){
					$value[] = $this->db->Quote($field);
				}
				$values[] = implode(',',$value);
				$ids[] = 'language_id='.(int)$translation->language_id.' AND reference_id='.(int)$translation->reference_id.' AND reference_table='.$this->db->Quote($translation->reference_table).' AND reference_field='.$this->db->Quote($translation->reference_field);
				$totalValid++;
				if( $totalValid%$this->perBatch == 0){
					if(!empty($ids)){
						$this->db->setQuery('DELETE FROM '.hikashop_table('jf_content',false).' WHERE (' . implode(') OR (', $ids) . ')');
						$this->db->query();
						$ids=array();
					}
					$this->db->setQuery($insert.implode('),(',$values).')');
					$this->db->query();
					$totalValid=0;
					$values=array();
				}
			}
		}
		if(!empty($values)){
			if(!empty($ids)){
				$this->db->setQuery('DELETE FROM '.hikashop_table('jf_content',false).' WHERE (' . implode(') OR (', $ids) . ')');
				$this->db->query();
			}
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}
	function _insertFiles(&$products,$type='files'){
		$db_type = 'product';
		if($type=='files'){
			$db_type='file';
		}
		$values = array();
		$totalValid=0;
		$ids=array();
		$insert = 'INSERT IGNORE INTO '.hikashop_table('file').' (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES (';
		foreach($products as $product){
			if(!isset($product->$type) && empty($product->hikashop_update)){
				if(!empty($this->template->$type)){
					foreach($this->template->$type as $file){
						$value = array($this->db->Quote($file->file_name),$this->db->Quote($file->file_description),$this->db->Quote($file->file_path),$this->db->Quote($db_type),$product->product_id);
						$values[] = implode(',',$value);
						$totalValid++;
						if( $totalValid%$this->perBatch == 0){
							$this->db->setQuery($insert.implode('),(',$values).')');
							$this->db->query();
							$totalValid=0;
							$values=array();
						}
					}
				}
			}elseif(!empty($product->$type)){
				$ids[]=(int)$product->product_id;
				foreach($product->$type as $file){
					if(is_string($file)){
						$value = array($this->db->Quote(str_replace('_',' ',substr($file,0,strrpos($file,'.')))),$this->db->Quote(''),$this->db->Quote($file),$this->db->Quote($db_type),$product->product_id);
					}else{
						$value = array($this->db->Quote($file->file_name),$this->db->Quote($file->file_description),$this->db->Quote($file->file_path),$this->db->Quote($db_type),$product->product_id);
					}
					$values[] = implode(',',$value);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\''.$db_type.'\'');
							$this->db->query();
							$ids = array();
						}
						$this->db->setQuery($insert.implode('),(',$values).')');
						$this->db->query();
						$totalValid=0;
						$values=array();
					}
				}
			}
		}
		if(!empty($values)){
			if(!empty($ids)){
				$this->db->setQuery('DELETE FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\''.$db_type.'\'');
				$this->db->query();
			}
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}
	function _separate(&$files){
		$separator='';
		foreach($this->listSeparators as $sep){
			$pos = strpos($files,$sep);
			if(preg_match('#(?!\\\\)'.$sep.'#',$files)){
				$separator = $sep;
				$files=str_replace('\\'.$separator,'#.#.#.#',$files);
				break;
			}
		}
		if(!empty($separator)){
			$files = explode($separator,$files);
		}else{
			$files = array($files);
		}
		foreach($files as $k => $v){
			$files[$k]=str_replace('#.#.#.#',$separator,$v);
		}
	}
	function _autoDetectHeader(){
		$app =& JFactory::getApplication();
		$this->separator = ',';
		$this->header = str_replace("\xEF\xBB\xBF","",$this->header);
		foreach($this->listSeparators as $sep){
			if(strpos($this->header,$sep) !== false){
				$this->separator = $sep;
				break;
			}
		}
		$this->columns = explode($this->separator,$this->header);
		$this->translateColumns = array();
		$columnsTable = $this->db->getTableFields(hikashop_table('product'));
		$columns = reset($columnsTable);
		$columns['price_value']='price_value';
		$columns['price_currency_id']='price_currency_id';
		$columns['price_min_quantity']='price_min_quantity';
		$columns['price_access']='price_access';
		$columns['files']='files';
		$columns['images']='images';
		$columns['parent_category']='parent_category';
		$columns['categories_image']='categories_image';
		$columns['categories_ordering']='categories_ordering';
		$columns['categories']='categories';
		$columns['related']='related';
		$columns['options']='options';
		if(hikashop_level(2)){
			$columns['product_access']='product_access';
			$columns['product_group_after_purchase']='product_group_after_purchase';
		}
		foreach($this->columns as $i => $oneColumn){
			if(function_exists('mb_strtolower')){
				$this->columns[$i] = mb_strtolower(trim($oneColumn,'" '));
			}else{
				$this->columns[$i] = strtolower(trim($oneColumn,'" '));
			}
			$this->columns[$i] = strtolower(trim($oneColumn,'" '));
			if( strpos($this->columns[$i],'|') !== false ) {
				$this->columns[$i] = str_replace('|','__tr__',$this->columns[$i]);
				$this->translateColumns[] = $this->columns[$i];
				$columns[$this->columns[$i]] = '';
			}
			if(!isset($columns[$this->columns[$i]])){
				if( isset($this->columnNamesConversionTable[$this->columns[$i]]) ){
					if(is_array($this->columnNamesConversionTable[$this->columns[$i]])){
						$this->columnNamesConversionTable[$this->columnNamesConversionTable[$this->columns[$i]]['name']]=$this->columnNamesConversionTable[$this->columns[$i]];
						$this->columns[$i]=$this->columnNamesConversionTable[$this->columns[$i]]['name'];
					}else{
						$this->columns[$i]=$this->columnNamesConversionTable[$this->columns[$i]];
					}
				}else{
					if(isset($this->characteristicsConversionTable[$this->columns[$i]])){
						$this->characteristicColumns[] = $this->columns[$i];
					}else{
						$possibilities = array_diff(array_keys($columns),array('product_id'));
						if(!empty($this->characteristics)){
							foreach($this->characteristics as $char){
								if(empty($char->characteristic_parent_id)){
									if(function_exists('mb_strtolower')){
										$possibilities[]=mb_strtolower(trim($char->characteristic_value,' "'));
									}else{
										$possibilities[]=strtolower(trim($char->characteristic_value,' "'));
									}
								}
							}
						}
						if($this->header_errors){
							$app->enqueueMessage(JText::sprintf('IMPORT_ERROR_FIELD',$this->columns[$i],implode(' | ',$possibilities)),'error');
						}
					}
				}
			}
		}
		$config =& JFactory::getConfig();
		$this->locale = strtolower($config->getValue('config.language'));
		$this->translateLanguages = array();
		$this->db->setQuery('SELECT id, code FROM '.hikashop_table('languages',false));
		$languages = $this->db->loadObjectList();
		if(!empty($languages)){
			foreach($languages as $language) {
				$this->translateLanguages[ strtolower($language->code) ] = $language->id;
			}
		}
		return true;
	}
	function _insertProducts(&$products){
		$this->_insertOneTypeOfProducts($products,'main');
		foreach($products as $k => $variant){
			if($variant->product_type!='main'){
				$parent_code = $variant->product_parent_id;
				if(is_numeric($parent_code)){
					foreach($products as $k2 => $main){
						if($variant->product_parent_id == $main->product_id){
							$parent_code=$main->product_code;
						}
					}
				}
				if(!empty($this->codes[$parent_code])){
					$products[$k]->product_parent_id = @$this->codes[$parent_code]->product_id;
				}
				if(empty($products[$k]->product_parent_id)){
					unset($products[$k]->product_parent_id);
				}
			}
		}
		$this->_insertOneTypeOfProducts($products,'variant');
		$this->_insertVariants($products);
		$this->_insertPrices($products);
		$this->_insertFiles($products,'images');
		$this->_insertFiles($products,'files');
		$this->_insertCategories($products);
		$this->_insertRelated($products);
		$this->_insertRelated($products,'options');
		$this->_insertTranslations($products);
	}
	function _insertOneTypeOfProducts(&$products,$type='main'){
		if(empty($products)) return true;
		$lines = array();
		$totalValid=0;
		$fields = array();
		$all_fields = $this->all_fields;
		if($type!='main'){
			$all_fields[]='product_parent_id';
		}
		$all_fields[]='product_id';
		foreach($this->columnsProductTable as $field){
			if(!in_array($field,$all_fields)){
				$all_fields[]=$field;
			}
		}
		foreach($all_fields as $field){
			$fields[]= '`'.$field.'`';
		}
		$fields = implode(', ',$fields);
		$insert = 'REPLACE INTO '.hikashop_table('product').' ('.$fields.') VALUES (';
		$codes = array();
		foreach($products as $product){
			if($product->product_type!=$type) continue;
			$codes[$product->product_code] = $this->db->Quote($product->product_code);
		}
		if(!empty($codes)){
			$query = 'SELECT * FROM '.hikashop_table('product'). ' WHERE product_code IN ('.implode(',',$codes).')';
			$this->db->setQuery($query);
			$already = $this->db->loadObjectList('product_id');
			if(!empty($already)){
				foreach($already as $code){
					$found = false;
					foreach($products as $k => $product){
						if($product->product_code==$code->product_code){
							$found = $k;
							break;
						}
					}
					if($found!==false){
						if($this->overwrite){
							if(!empty($products[$found]->product_type) && !empty($code->product_type) && $products[$found]->product_type==$code->product_type){
								$products[$found]->product_id = $code->product_id;
								$products[$found]->hikashop_update = true;
							}else{
								unset($products[$found]);
								$app =& JFactory::getApplication();
								$app->enqueueMessage('The product '.$products[$found]->product_code.' is of the type '. $products[$found]->product_type.' but it already exists in the database and is of the type '.$code->product_type.'. In order to avoid any problem the product insertion process has been skipped. Please correct its type before trying to reimport it.','error');
							}
						}else{
							unset($products[$found]);
						}
					}
				}
			}
			$exist=0;
			if(!empty($codes)){
				foreach($products as $product){
					if($product->product_type!=$type || empty($codes[$product->product_code])) continue;
					$line = array();
					foreach($all_fields as $field){
						if(!isset($product->$field) && !empty($product->product_id) && isset($already[$product->product_id])){
							$product->$field = $already[$product->product_id]->$field;
						}
						if($field=='product_id'){
							if(empty($product->$field)|| !is_numeric($product->$field)){
								$line[] = 'NULL';
							}else{
								$exist++;
								$line[] = $this->db->Quote(@$product->$field);
							}
						}else{
							if($field=='product_published' && !isset($product->$field) && $this->force_published){
								$product->product_published=1;
							}
							$line[] = $this->db->Quote(@$product->$field);
						}
					}
					$lines[]=implode(',',$line);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						$this->db->setQuery($insert.implode('),(',$lines).')');
						$this->db->query();
						if($type=='main' || $this->countVariant){
							$this->totalInserted += count($lines);
						}
						$totalValid=0;
						$lines=array();
					}
				}
				if(!empty($lines)){
					$this->db->setQuery($insert.implode('),(',$lines).')');
					$this->db->query();
					if($type=='main' || $this->countVariant){
						$this->totalInserted += count($lines);
					}
				}
			}
			$this->totalInserted=$this->totalInserted-$exist;
			if(!empty($codes)){
				$query = 'SELECT product_code, product_id FROM '.hikashop_table('product'). ' WHERE product_code IN ('.implode(',',$codes).')';
				$this->db->setQuery($query);
				$newCodes = (array)$this->db->loadObjectList('product_code');
				foreach($newCodes as $k => $code){
					$this->codes[$k]=$code;
				}
				foreach($products as $k => $product){
					if($product->product_type==$type && !empty($this->codes[$product->product_code])){
						$products[$k]->product_id = @$this->codes[$product->product_code]->product_id;
						if($type=='variant'){
							$this->products_already_in_db[(int)@$products[$k]->product_parent_id]=(int)@$products[$k]->product_parent_id;
							$this->new_variants_in_db[(int)@$products[$k]->product_id]=(int)@$products[$k]->product_id;
						}
					}
				}
			}
		}
	}
	function importFromVM(){
		@ob_clean();
		echo $this->getHtmlPage();
		$this->token = JUtility::getToken();
		flush();
		if( isset($_GET['import']) && $_GET['import'] == '1' ) {
			$time = microtime(true);
			$this->db =& JFactory::getDBO();
			$processed = $this->doImport();
			if( $processed ) {
				$elasped = microtime(true) - $time;
				echo '<br/>Elasped time: ' . round($elasped * 1000, 2) . 'ms<br/>';
				if( !$this->refreshPage ) {
					echo '<p><a href="'.hikashop::completeLink('import&task=import&importfrom=vm&'.$this->token.'=1&import=1').'">'.JText::_('HIKA_NEXT').'</a></p>';
				}
			} else {
				echo '<a href="'.hikashop::completeLink('import&task=show').'">'.JText::_('HIKA_BACK').'</a>';
			}
		} else {
			echo $this->getStartPage();
		}
		if( $this->refreshPage == true ) {
			echo "<script type=\"text/javascript\">\r\nr = true;\r\n</script>";
		}
		echo '</body></html>';
		exit;
	}
	function doImport() {
		if( $this->db == null )
			return false;
		$this->loadConfiguration();
		$current = $this->options->current;
		$ret = true;
		$next = false;
		switch( $this->options->state ) {
			case 0:
				$next = $this->createTables();
				break;
			case 1:
				$next = $this->importTaxes();
				break;
			case 2:
				$next = $this->importManufacturers();
			case 3:
				$next = $this->importCategories();
				break;
			case 4:
				$next = $this->importProducts();
				break;
			case 5:
				//- Import Product Prices
				$next = $this->importProductPrices();
				break;
			case 6:
				$next = $this->importProductCategory();
				break;
			case 7:
				$next = $this->importUsers();
				break;
			case 8:
				$next = $this->importOrders();
				break;
			case 9:
				$next = $this->importOrderItems();
				break;
			case 10:
				$next = $this->importDownloads();
				break;
			case 11:
				$next = $this->importDiscount();
				break;
			case MAX_IMPORT_ID:
				$next = $this->finishImport();
				$ret = false;
				break;
			case MAX_IMPORT_ID+1:
				$next = false;
				$ret = $this->proposeReImport();
				break;
			default:
				$ret = false;
				break;
		}
		if( $ret && $next ) {
			$sql =  "UPDATE `#__hikashop_config` SET config_value=(config_value+1) WHERE config_namekey = 'vm_import_state'; ";
			$this->db->setQuery($sql);
			$this->db->query();
			$sql = "UPDATE `#__hikashop_config` SET config_value=0 WHERE config_namekey = 'vm_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		} else if( $current != $this->options->current ) {
			$sql =  "UPDATE `#__hikashop_config` SET config_value=".$this->options->current." WHERE config_namekey = 'vm_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		}
		return $ret;
	}
	function loadConfiguration() {
		if( $this->db == null )
			return false;
		$this->vmImgDir = HIKASHOP_ROOT . 'components/com_virtuemart/shop_image/product/';
		$data = array(
			'uploadfolder',
			'uploadsecurefolder',
			'main_currency',
			'vm_import_state',
			'vm_import_current',
			'vm_import_tax_id',
			'vm_import_main_cat_id',
			'vm_import_max_hk_cat',
			'vm_import_max_hk_prod',
			'vm_import_last_vm_cat',
			'vm_import_last_vm_prod',
			'vm_import_last_vm_user',
			'vm_import_last_vm_order',
			'vm_import_last_vm_pfile',
			'vm_import_last_vm_coupon',
			'vm_import_last_vm_taxrate',
			'vm_import_last_vm_manufacturer'
		);
		$this->db->setQuery('SELECT config_namekey, config_value FROM `#__hikashop_config` WHERE config_namekey IN ('."'".implode("','",$data)."'".');');
		$options = $this->db->loadObjectList();
		$this->options = null;
		foreach($options as $o) {
			if( substr($o->config_namekey, 0, 10) == 'vm_import_' ) {
				$nk = substr($o->config_namekey, 10);
			} else {
				$nk = $o->config_namekey;
			}
			$this->options->$nk = $o->config_value;
		}
		$this->options->uploadfolder = rtrim(JPath::clean(html_entity_decode($this->options->uploadfolder)),DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$this->options->uploadfolder)){
			if(!$this->options->uploadfolder[0]=='/' || !is_dir($this->options->uploadfolder)){
				$this->options->uploadfolder = JPath::clean(HIKASHOP_ROOT.DS.trim($this->options->uploadfolder,DS.' ').DS);
			}
		}
		$this->options->uploadsecurefolder = rtrim(JPath::clean(html_entity_decode($this->options->uploadsecurefolder)),DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$this->options->uploadsecurefolder)){
			if(!$this->options->uploadsecurefolder[0]=='/' || !is_dir($this->options->uploadsecurefolder)){
				$this->options->uploadsecurefolder = JPath::clean(HIKASHOP_ROOT.DS.trim($this->options->uploadsecurefolder,DS.' ').DS);
			}
		}
		if( !isset($this->options->state) ) {
			$this->options->state = 0;
			$this->options->current = 0;
			$this->options->tax_id = 0;
			$this->options->last_vm_coupon = 0;
			$this->options->last_vm_pfile = 0;
			$this->options->last_vm_taxrate = 0;
			$this->options->last_vm_manufacturer = 0;
			$element = 'product';
			$categoryClass = hikashop::get('class.category');
			$categoryClass->getMainElement($element);
			$this->options->main_cat_id = $element;
			$this->db->setQuery("SELECT max(category_id) as 'max' FROM `#__hikashop_category`;");
			$data = $this->db->loadObjectList();
			$this->options->max_hk_cat = (int)($data[0]->max);
			$this->db->setQuery("SELECT max(product_id) as 'max' FROM `#__hikashop_product`;");
			$data = $this->db->loadObjectList();
			$this->options->max_hk_prod = (int)($data[0]->max);
			//--
			$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('vm_cat'),3));
			$this->db->setQuery($query);
			$table = $this->db->loadResult();
			if(!empty($table)){
				$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_cat`;");
				$data = $this->db->loadObjectList();
				if( $data ) {
					$this->options->last_vm_cat = (int)($data[0]->max);
				} else {
					$this->options->last_vm_cat = 0;
				}
				$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_prod`;");
				$data = $this->db->loadObjectList();
				if( $data ) {
					$this->options->last_vm_prod = (int)($data[0]->max);
				} else {
					$this->options->last_vm_prod = 0;
				}
				$this->db->setQuery("SELECT max(order_vm_id) as 'max' FROM `#__hikashop_order`;");
				$data = $this->db->loadObjectList();
				$this->options->last_vm_order = (int)($data[0]->max);
			}else{
				$this->options->last_vm_cat = 0;
				$this->options->last_vm_prod = 0;
				$this->options->last_vm_order = 0;
			}

			$this->options->last_vm_user = 0;
			$sql = 'INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('vm_import_state',".$this->options->state.",".$this->options->state.")".
				",('vm_import_current',".$this->options->current.",".$this->options->current.")".
				",('vm_import_tax_id',".$this->options->tax_id.",".$this->options->tax_id.")".
				",('vm_import_main_cat_id',".$this->options->main_cat_id.",".$this->options->main_cat_id.")".
				",('vm_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('vm_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('vm_import_last_vm_cat',".$this->options->last_vm_cat.",".$this->options->last_vm_cat.")".
				",('vm_import_last_vm_prod',".$this->options->last_vm_prod.",".$this->options->last_vm_prod.")".
				",('vm_import_last_vm_user',".$this->options->last_vm_user.",".$this->options->last_vm_user.")".
				",('vm_import_last_vm_order',".$this->options->last_vm_order.",".$this->options->last_vm_order.")".
				",('vm_import_last_vm_pfile',".$this->options->last_vm_pfile.",".$this->options->last_vm_pfile.")".
				",('vm_import_last_vm_coupon',".$this->options->last_vm_coupon.",".$this->options->last_vm_coupon.")".
				",('vm_import_last_vm_taxrate',".$this->options->last_vm_taxrate.",".$this->options->last_vm_taxrate.")".
				",('vm_import_last_vm_manufacturer',".$this->options->last_vm_manufacturer.",".$this->options->last_vm_manufacturer.")".
				';';
			$this->db->setQuery($sql);
			$this->db->query();
		}
	}
	function finishImport() {
		if( $this->db == null )
			return false;
		$this->db->setQuery("SELECT max(category_id) as 'max' FROM `#__hikashop_category`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_cat = (int)($data[0]->max);
		$this->db->setQuery("SELECT max(product_id) as 'max' FROM `#__hikashop_product`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_prod = (int)($data[0]->max);
		$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_cat`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_cat = (int)($data[0]->max);
		$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_prod`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_prod = (int)($data[0]->max);
		$this->db->setQuery("SELECT max(user_id) as 'max' FROM `#__vm_user_info`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_user = (int)($data[0]->max);
		$this->db->setQuery("SELECT max(order_vm_id) as 'max' FROM `#__hikashop_order`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_order = (int)($data[0]->max);
		$this->db->setQuery("SELECT max(file_id) as 'max' FROM `#__vm_product_files`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_pfile = (int)($data[0]->max);
		$this->db->setQuery("SELECT max(coupon_id) as 'max' FROM `#__vm_coupons`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_coupon = (int)($data[0]->max);
		$this->db->setQuery("SELECT max(tax_rate_id) as 'max' FROM `#__vm_tax_rate`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_taxrate = (int)($data[0]->max);
		$this->db->setQuery("SELECT max(manufacturer_id) as 'max' FROM `#__vm_manufacturer`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_manufacturer = (int)($data[0]->max);
		$this->options->state = (MAX_IMPORT_ID+1);
		$query = 'REPLACE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('vm_import_state',".$this->options->state.",".$this->options->state.")".
				",('vm_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('vm_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('vm_import_last_vm_cat',".$this->options->last_vm_cat.",".$this->options->last_vm_cat.")".
				",('vm_import_last_vm_prod',".$this->options->last_vm_prod.",".$this->options->last_vm_prod.")".
				",('vm_import_last_vm_user',".$this->options->last_vm_user.",".$this->options->last_vm_user.")".
				",('vm_import_last_vm_order',".$this->options->last_vm_order.",".$this->options->last_vm_order.")".
				",('vm_import_last_vm_pfile',".$this->options->last_vm_pfile.",".$this->options->last_vm_pfile.")".
				",('vm_import_last_vm_coupon',".$this->options->last_vm_coupon.",".$this->options->last_vm_coupon.")".
				",('vm_import_last_vm_taxrate',".$this->options->last_vm_taxrate.",".$this->options->last_vm_taxrate.")".
				",('vm_import_last_vm_manufacturer',".$this->options->last_vm_manufacturer.",".$this->options->last_vm_manufacturer.")".
				';';
		$this->db->setQuery($query);
		$this->db->query();
		echo '<p>Import finished !</p><br/>';
		$class = hikashop_get('class.plugins');
		$infos = $class->getByName('system','vm_redirect');
		if($infos){
			$pkey = reset($class->pkeys);
			if(!empty($infos->$pkey)){
				if(version_compare(JVERSION,'1.6','<')){
					$url = JRoute::_('index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]='.$infos->$pkey);
				}else{
					$url = JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$infos->$pkey);
				}
				echo '<p>You can publish the <a href="'.$url.'">VirtueMart Fallback Redirect Plugin</a> so that your old VirtueMart links are automatically redirected to HikaShop pages and thus not loose the ranking of your content on search engines.</p>';
			}
		}
	}
	function createTables() {
		if( $this->db == null )
			return false;
		echo '[Initialization Tables]'."\n<br/>\n";
		$create = true;
		$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('vm_cat'),3));
		$this->db->setQuery($query);
		$table = $this->db->loadResult();
		if(!empty($table) ) {
			$create = false;
		}
		if( $create ) {
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_vm_prod` (`vm_id` int(10) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`vm_id`)) ENGINE=MyISAM");
			$this->db->query();
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_vm_cat` (`vm_id` int(10) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`vm_id`)) ENGINE=MyISAM");
			$this->db->query();
			$this->db->setQuery('ALTER IGNORE TABLE `#__hikashop_address` ADD `address_vm_order_info_id` INT(11) NULL');
			$this->db->query();
			$this->db->setQuery('ALTER IGNORE TABLE `#__hikashop_order` ADD `order_vm_id` INT(11) NULL');
			$this->db->query();
			$this->db->setQuery('ALTER IGNORE TABLE `#__hikashop_order` ADD INDEX ( `order_vm_id` )');
			$this->db->query();
			$this->db->setQuery('ALTER IGNORE TABLE `#__hikashop_taxation` ADD `tax_vm_id` INT(11) NULL');
			$this->db->query();
		} else {
			$this->refreshPage = true;
		}
		return true;
	}
	function importTaxes() {
		if( $this->db == null )
			return false;
		$ret = false;
		echo '[Import Taxes]'."\n<br/>\n";
		$data = array(
			'tax_namekey' => "CONCAT('VM_TAX_', vmtr.tax_rate_id)",
			'tax_rate' => 'vmtr.tax_rate'
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_tax` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__vm_tax_rate` vmtr '.
			'WHERE vmtr.tax_rate_id > ' . $this->options->last_vm_taxrate;
		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Import taxes: ' . $total . '<br/>';
		$element = 'tax';
		$categoryClass = hikashop::get('class.category');
		$categoryClass->getMainElement($element);

		$data = array(
			'category_type' => "'tax'",
			'category_name' => "CONCAT('Tax imported (', vmtr.tax_country,')')",
			'category_published' => '1',
			'category_parent_id' => $element,
			'category_namekey' => "CONCAT('VM_TAX_', vmtr.tax_rate_id,'_',hkz.zone_id)",
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__vm_tax_rate` vmtr '.
			"INNER JOIN `#__hikashop_zone` hkz ON vmtr.tax_country = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			'WHERE vmtr.tax_rate_id > ' . $this->options->last_vm_taxrate;
		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Imported Taxes Categories: ' . $total . '<br/>';
		if( $total > 0 ) {
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
			$this->db->query();
			$this->importRebuildTree();
		}

		$data = array(
			'zone_namekey' => 'hkz.zone_namekey',
			'category_namekey' => "CONCAT('VM_TAX_', vmtr.tax_rate_id,'_',hkz.zone_id)",
			'tax_namekey' => "CONCAT('VM_TAX_', vmtr.tax_rate_id)",
			'taxation_published' => '1',
			'taxation_type' => "''",
			'tax_vm_id' => 'vmtr.tax_rate_id'
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_taxation` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__vm_tax_rate` vmtr '.
			"INNER JOIN #__hikashop_zone hkz ON vmtr.tax_country = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			'WHERE vmtr.tax_rate_id > ' . $this->options->last_vm_taxrate;
		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Imported Taxations: ' . $total . '<br/>';
		$ret = true;
		return $ret;
	}
	function importManufacturers() {
		if( $this->db == null )
			return false;
		$ret = false;
		echo '[Import Manufacturers]'."\n<br/>\n";
		$element = 'manufacturer';
		$categoryClass = hikashop::get('class.category');
		$categoryClass->getMainElement($element);

		$data = array(
			'category_type' => "'manufacturer'",
			'category_name' => "vmm.mf_name ",
			'category_published' => '1',
			'category_parent_id' => $element,
			'category_namekey' => "CONCAT('VM_MANUFAC_', vmm.manufacturer_id )",
			'category_description' => 'vmm.mf_desc',
			'category_menu' => 'vmm.manufacturer_id'
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__vm_manufacturer` vmm '.
			'WHERE vmm.manufacturer_id > ' . $this->options->last_vm_manufacturer;
		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Imported Manufacturers: ' . $total . '<br/>';
		if( $total > 0 ) {
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
			$this->db->query();
			$this->importRebuildTree();
		}
		$ret = true;
		return $ret;
	}
	function importCategories() {
		if( $this->db == null )
			return false;
		jimport('joomla.filesystem.file');
		$categoryClass = hikashop::get('class.category');
		$rebuild = false;
		$ret = false;
		$offset = 0;
		$count = 100;
		$statuses = array(
			'created' => 'P',
			'confirmed' => 'C',
			'cancelled' => 'X',
			'refunded' => 'R',
			'shipped' => 'S'
		);
		$this->db->setQuery("SELECT category_keywords, category_parent_id FROM `#__hikashop_category` WHERE category_type = 'status' AND category_name = 'confirmed'");
		$data = $this->db->loadObject();
		$status_category = $data->category_parent_id;
		if( $data->category_keywords != 'C' ) {
			foreach($statuses as $k => $v) {
				$this->db->setQuery("UPDATE `#__hikashop_category` SET category_keywords = '".$v."' WHERE category_type = 'status' AND category_name = '".$k."'; ");
				$this->db->query();
			}
		}
		$this->db->setQuery("SELECT order_status_code, order_status_name, order_status_description FROM `#__vm_order_status` WHERE order_status_code NOT IN ('".implode("','",$statuses)."');");
		$data = $this->db->loadObjectList();
		if( count($data) > 0 ) {
			$sql0 = 'INSERT IGNORE INTO `#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_description`,`category_published`,'.
				'`category_namekey`,`category_access`,`category_menu`,`category_keywords`) VALUES ';
			$id = $this->options->max_hk_cat + 1;
			$sep = '';
			foreach($data as $c) {
				$d = array(
					$id++,
					$status_category,
					"'status'",
					$this->db->quote( strtolower($c->order_status_name) ),
					$this->db->quote( $c->order_status_description ),
					'1',
					$this->db->quote('status_vm_import_'.strtolower(str_replace(' ','_',$c->order_status_name))),
					"'all'",
					'0',
					$this->db->quote( $c->order_status_code )
				);
				$sql0 .= $sep.'('.implode(',',$d).')';
				$sep = ',';
			}
			$this->db->setQuery($sql0);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			if( $total > 0 ) {
				echo '[Order Status Categories]<br/>';
				echo 'Imported Categories: ' . $total . '<br/>';
				$rebuild = true;
				$this->options->max_hk_cat += $total;
				$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
				$this->db->query();
				$sql0 = '';
			}
		}
		$this->db->setQuery('SELECT * FROM `#__vm_category` vmc '.
					'LEFT JOIN `#__vm_category_xref` vmcx ON vmc.category_id = vmcx.category_child_id '.
					'LEFT JOIN `#__hikashop_vm_cat` hkvm ON vmc.category_id = hkvm.vm_id '.
					'ORDER BY category_parent_id ASC, list_order ASC, category_id ASC;');
		$data = $this->db->loadObjectList();
		$total = count($data);
		if( $total == 0 ) {
			echo '[Categories]<br/>Nothing to import'."\n<br/>\n";
			if( $rebuild )
				$this->importRebuildTree();
			return true;
		}
		$sql0 = 'INSERT INTO `#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_description`,`category_published`,'.
			'`category_ordering`,`category_namekey`,`category_created`,`category_modified`,`category_access`,`category_menu`) VALUES ';
		$sql1 = 'INSERT INTO `#__hikashop_vm_cat` (`vm_id`,`hk_id`) VALUES ';
		$sql2 = 'INSERT INTO `#__hikashop_file` (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ';
		$doSql2 = false;
		$i = $this->options->max_hk_cat + 1;
		$ids = array( 0 => $this->options->main_cat_id);
		$cpt = 0;
		$sep = '';
		foreach($data as $c) {
			if( !empty($c->vm_id) ) {
				$ids[$c->category_id] = $c->hk_id;
			} else {
				$ids[$c->category_id] = $i;
				$sql1 .= $sep.'('.$c->category_id.','.$i.')';
				$i++;
				$cpt++;
				$sep = ',';
			}
			if( $cpt >= $count )
				break;
		}
		$sql1 .= ';';
		if( $cpt == 0 ) {
			echo '[Categories]<br/>Nothing to import'."\n<br/>\n";
			if( $rebuild )
				$this->importRebuildTree();
			return true;
		}
		$cpt = 0;
		$sep = '';
		foreach($data as $c) {
			if( empty($c->vm_id) ) {
				$id = $ids[$c->category_id];
				$pid = $ids[$c->category_parent_id];
				$element = null;
				$element->category_id = $id;
				$element->category_parent_id = $pid;
				$element->category_name = $c->category_name;
				$nameKey = $categoryClass->getNameKey($element);
				$d = array(
					$id,
					$pid,
					"'product'",
					$this->db->quote($c->category_name),
					$this->db->quote($c->category_description),
					'1',
					$c->list_order,
					$this->db->quote($nameKey),
					$c->cdate,
					$c->mdate,
					"'all'",
					'0'
				);
				$sql0 .= $sep.'('.implode(',',$d).')';
				if( !empty($c->category_full_image)) {
					$doSql2 = true;
					$sql2 .= $sep."('','','".$c->category_full_image."','category',".$id.')';
					$file_name = str_replace('\\','/',$c->category_full_image);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$this->copyFile($this->vmImgDir,$c->category_full_image, $this->options->uploadfolder.$file_name);
				}
				$sep = ',';
				$cpt++;
				if( $cpt >= $count )
					break;
			}
		}
		$sql0 .= ';';
		$sql2 .= ';';
		echo '[Categories]<br/>';
		$this->db->setQuery($sql0);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Imported Categories: ' . $total . '<br/>';
		if( $total > 0) {
			$rebuild = true;
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
			$this->db->query();
		}
		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Fallback links: ' . $total . '<br/>';
		if( $doSql2 ) {
			$this->db->setQuery($sql2);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo 'Categorie files: ' . $total . '<br/>';
		}
		if( $rebuild )
			$this->importRebuildTree();
		echo "\n<br/>\n";
		if( $cpt < $count ) {
			$ret = true;
		}
		return $ret;
	}
	function importRebuildTree() {
		if( $this->db == null )
			return false;
		$categoryClass = hikashop::get('class.category');
		$query = 'SELECT category_namekey,category_left,category_right,category_depth,category_id,category_parent_id FROM `#__hikashop_category` ORDER BY category_left ASC';
		$this->db->setQuery($query);
		$categories = $this->db->loadObjectList();
		$root = null;
		$categoryClass->categories = array();
		foreach($categories as $cat){
			$categoryClass->categories[$cat->category_parent_id][]=$cat;
			if(empty($cat->category_parent_id)){
				$root = $cat;
			}
		}
		$categoryClass->rebuildTree($root,0,1);
	}
	function importProducts() {
		if( $this->db == null )
			return false;
		jimport('joomla.filesystem.file');
		$categoryClass = hikashop::get('class.category');
		$ret = false;
		$count = 100;
		$offset = $this->options->current;
		$this->db->setQuery('SELECT vmp.product_id, vmp.product_full_image FROM `#__vm_product` vmp '.
					'LEFT JOIN `#__hikashop_vm_prod` hkprod ON vmp.product_id = hkprod.vm_id '.
					"WHERE vmp.product_id > ".$offset." AND hkprod.hk_id IS NULL AND (vmp.product_full_image IS NOT NULL) AND vmp.product_full_image <> '' ".
					'ORDER BY product_id ASC LIMIT '.$count.';');
		$data = $this->db->loadObjectList();
		$max = 0;
		foreach($data as $c) {
			if( !empty($c->product_full_image) ) {
				$file_name = str_replace('\\','/',$c->product_full_image);
				if( strpos($file_name,'/') !== false ) {
					$file_name = substr($file_name, strrpos($file_name,'/'));
				}
				$this->copyFile($this->vmImgDir,$c->product_full_image, $this->options->uploadfolder.$file_name);
				$max = $c->product_id;
			}
		}
		if( $max > 0 ) {
			echo '[Products]<br/>Copying files...<br/>(last proccessed product id: ' . $max . ")\n<br/>\n";
			$this->options->current = $max;
			$this->refreshPage = true;
			return $ret;
		}

		$data = array(
			'product_name' => 'vmp.product_name',
			'product_description' => "CONCAT(vmp.product_s_desc,'<hr id=\"system-readmore\"/>',vmp.product_desc)",
			'product_quantity' => 'case when vmp.product_in_stock IS NULL then 0 else vmp.product_in_stock end',
			'product_code' => 'vmp.product_sku',
			'product_published' => "case when vmp.product_publish = 'Y' then 1 else 0 end",
			'product_hit' => '0',
			'product_created' => 'vmp.cdate',
			'product_modified' => 'vmp.mdate',
			'product_sale_start' => 'vmp.product_available_date',
			'product_tax_id' => 'hkc.category_id',
			'product_type' => "'main'",
			'product_url' => 'vmp.product_url',
			'product_weight' => 'vmp.product_weight',
			'product_weight_unit' => 'vmp.product_weight_uom',
			'product_dimension_unit' => 'vmp.product_lwh_uom',
			'product_sales' => 'vmp.product_sales',
			'product_width' => 'vmp.product_width',
			'product_length' => 'vmp.product_length',
			'product_height' => 'vmp.product_height',
		);
		$sql1 = 'INSERT IGNORE INTO `#__hikashop_product` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__vm_product` AS vmp '.
			'LEFT JOIN `#__hikashop_taxation` hkt ON hkt.tax_vm_id = product_tax_id '.
			'LEFT JOIN `#__hikashop_category` hkc ON hkc.category_namekey = hkt.category_namekey '.
			'LEFT JOIN `#__hikashop_vm_prod` AS hkp ON vmp.product_id = hkp.vm_id '.
			'WHERE hkp.hk_id IS NULL ORDER BY vmp.product_id ASC;';
		$data = array(
			'vm_id' => 'vmp.product_id',
			'hk_id' => 'hkp.product_id'
		);
		$sql2 = 'INSERT IGNORE INTO `#__hikashop_vm_prod` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__vm_product` AS vmp INNER JOIN `#__hikashop_product` AS hkp ON vmp.product_sku = hkp.product_code '.
			'LEFT JOIN `#__hikashop_vm_prod` hkvm ON hkvm.vm_id = vmp.product_id '.
			'WHERE hkvm.hk_id IS NULL;';
		$sql3 = 'UPDATE `#__hikashop_product` AS hkp '.
			'INNER JOIN `#__vm_product` AS vmp ON vmp.product_sku = hkp.product_code '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvm ON vmp.product_parent_id = hkvm.vm_id '.
			'SET hkp.product_parent_id = hkvm.hk_id;';
		$data = array(
			'file_name' => "''",
			'file_description' => "''",
			'file_path' => "SUBSTRING_INDEX(vmp.product_full_image,'/',-1)",
			'file_type' => "'product'",
			'file_ref_id' => 'hkvm.hk_id'
		);
		$sql4 = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__vm_product` AS vmp '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvm ON vmp.product_id = hkvm.vm_id '.
			'WHERE vmp.product_id > '.$this->options->last_vm_prod.' AND (vmp.product_full_image IS NOT NULL) AND (vmp.product_full_image <>'." '');";
		$sql5 = 'UPDATE `#__hikashop_product` AS hkp '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvm ON hkp.product_id = hkvm.hk_id '.
			'INNER JOIN `#__vm_product_mf_xref` AS vmm ON vmm.product_id = hkvm.vm_id '.
			"INNER JOIN `#__hikashop_category` AS hkc ON hkc.category_type = 'manufacturer' AND vmm.manufacturer_id = hkc.category_menu ".
			'SET hkp.product_manufacturer_id = hkc.category_id '.
			'WHERE vmm.manufacturer_id > '.$this->options->last_vm_manufacturer.' OR vmm.product_id > '.$this->options->last_vm_prod.';';
		echo '[Products]<br/>';
		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Inserted products: ' . $total . '<br/>';
		$this->db->setQuery($sql2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Fallback links: ' . $total . '<br/>';
		$this->db->setQuery($sql3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Updating products for parent links: ' . $total . '<br/>';
		$this->db->setQuery($sql4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Inserted products files: ' . $total . '<br/>';
		$this->db->setQuery($sql5);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Updating products manufacturers: ' . $total . '<br/>';
		$ret = true;
		return $ret;
	}
	function importProductPrices() {
		if( $this->db == null )
			return false;
		$ret = false;
		$cpt = 0;
		$this->db->setQuery('INSERT IGNORE INTO #__hikashop_price (`price_product_id`,`price_value`,`price_currency_id`,`price_min_quantity`,`price_access`) '
				.'SELECT hkprod.hk_Id, product_price, hkcur.currency_id, price_quantity_start, \'all\' '
				.'FROM #__vm_product_price vm INNER JOIN #__hikashop_vm_prod hkprod ON vm.product_id = hkprod.vm_id '
				.'INNER JOIN #__hikashop_currency hkcur ON vm.product_currency = hkcur.currency_code '
				.'WHERE product_price_vdate < NOW() AND (product_price_edate = 0 OR product_price_edate > NOW() ) '
				.'AND vm.product_id > ' . $this->options->last_vm_prod
		);
		$ret = $this->db->query();
		$cpt = $this->db->getAffectedRows();
		echo '[Products Prices]<br/>Adding ' . $cpt . " prices\n<br/>\n";
		return $ret;
	}
	function importProductCategory() {
		if( $this->db == null )
			return false;
		$data = array(
			'category_id' => 'vmc.hk_id',
			'product_id' => 'vmp.hk_id',
			'ordering' => '`product_list`',
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_product_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__vm_product_category_xref` vm '.
			'INNER JOIN #__hikashop_vm_cat vmc ON vm.category_id = vmc.vm_id '.
			'INNER JOIN #__hikashop_vm_prod vmp ON vm.product_id = vmp.vm_id '.
			'WHERE vmp.vm_id > ' . $this->options->last_vm_prod . ' OR vmc.vm_id > ' . $this->options->last_vm_cat;
		$this->db->setQuery($sql);
		$this->db->query();
		$nb = $this->db->getAffectedRows();
		echo '[Product Category]<br/>' . $nb . " inserted\n<br/>\n";
		return true;
	}
	function importUsers() {
		if( $this->db == null )
			return false;
		$ret = false;
		echo '[User Addresses]<br/>';
		$sql0 = 'INSERT IGNORE INTO `#__hikashop_user` (`user_cms_id`,`user_email`) '.
				'SELECT vmui.user_id, vmui.user_email FROM `#__vm_user_info` AS vmui LEFT JOIN `#__hikashop_user` AS hkusr ON vmui.user_id = hkusr.user_cms_id WHERE hkusr.user_cms_id IS NULL;';
		$data = array(
			'address_user_id' => 'hku.user_id',
			'address_firstname' => 'vmui.first_name',
			'address_middle_name' => 'vmui.middle_name',
			'address_lastname' => 'vmui.last_name',
			'address_company' => 'vmui.company',
			'address_street' => 'CONCAT(vmui.address_1,\' \',vmui.address_2)',
			'address_post_code' => 'vmui.zip',
			'address_city' => 'vmui.city',
			'address_telephone' => 'vmui.phone_1',
			'address_telephone2' => 'vmui.phone_2',
			'address_fax' => 'vmui.fax',
			'address_state' => 'vmui.state',
			'address_country' => 'vmui.country',
			'address_published' => 4
		);
		$sql1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `#__vm_user_info` AS vmui INNER JOIN `#__hikashop_user` AS hku ON vmui.user_id = hku.user_cms_id WHERE vmui.user_id > '.$this->options->last_vm_user.' ORDER BY vmui.user_id ASC';
		$sql2 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone` AS hkz ON (a.address_country = hkz.zone_code_3 AND hkz.zone_type = "country") '.
				'SET address_country = hkz.zone_namekey, address_published = 3 WHERE address_published = 4;';
		$sql3 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone_link` AS zl ON (a.address_country = zl.zone_parent_namekey) '.
				'JOIN `#__hikashop_zone` AS hks ON (hks.zone_namekey = zl.zone_child_namekey AND hks.zone_type = "state" AND hks.zone_code_3 = a.address_state) '.
				'SET address_state = hks.zone_namekey, address_published = 2 WHERE address_published = 3;';
		$sql4 = "UPDATE `#__hikashop_address` AS a SET a.address_country = '' WHERE address_published > 3;";
		$sql5 = "UPDATE `#__hikashop_address` AS a SET a.address_state = '' WHERE address_published > 2;";
		$sql6 = 'UPDATE `#__hikashop_address` AS a SET a.address_published = 1 WHERE address_published > 1;';
		$this->db->setQuery($sql0);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Imported Users: ' . $total . '<br/>';
		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Imported addresses: ' . $total . '<br/>';
		$this->db->setQuery($sql2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Imported addresses countries: ' . $total . '<br/>';
		$this->db->setQuery($sql3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Imported addresses states: ' . $total . '<br/>';
		$this->db->setQuery($sql4);
		$this->db->query();
		$this->db->setQuery($sql5);
		$this->db->query();
		$this->db->setQuery($sql6);
		$this->db->query();
		$ret = true;
		echo "\n<br/>\n";
		return $ret;
	}
	function importOrders() {
		if( $this->db == null )
			return false;
		$ret = false;
		$offset = $this->options->current;
		$count = 100;
		$total = 0;
		$this->db->setQuery("SELECT name FROM `#__vm_userfield` WHERE type = 'euvatid' AND published = 1");
		$vat_cols = $this->db->loadObjectList();
		if( isset($vat_cols) && $vat_cols !== null && is_array($vat_cols) && count($vat_cols)>0) {
			$vat_cols = 'vmui.' . $vat_cols[0]->name;
		} else {
			$vat_cols = "''";
		}
		echo '[Orders]<br/>';
		$data = array(
			'order_number' => 'vmo.order_id',
			'order_vm_id' => 'vmo.order_id',
			'order_user_id' => 'hkusr.user_id',
			'order_status' => 'hkc.category_name',
			'order_discount_code' => 'vmo.coupon_code',
			'order_discount_price' => 'vmo.coupon_discount',
			'order_created' => 'vmo.cdate',
			'order_ip' => 'vmo.ip_address',
			'order_currency_id' => 'hkcur.currency_id',
			'order_shipping_price' => 'vmo.order_shipping',
			'order_shipping_method' => "'vm import'",
			'order_shipping_id' => '1',
			'order_payment_id' => 0,
			'order_payment_method' => '\'vm import\'',
			'order_full_price' => 'vmo.order_total',
			'order_modified' => 'vmo.mdate',
			'order_partner_id' => 0,
			'order_partner_price' => 0,
			'order_partner_paid' => 0,
			'order_type' => "'sale'",
			'order_partner_currency_id' => 0,
			'order_shipping_tax' => 'vmo.order_shipping_tax',
			'order_discount_tax' => 0
		);
		$sql1 = 'INSERT IGNORE INTO `#__hikashop_order` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__vm_orders` AS vmo '.
			'JOIN `#__hikashop_category` AS hkc ON vmo.order_status = hkc.category_keywords AND hkc.category_type = \'status\' '.
			'JOIN `#__hikashop_currency` AS hkcur ON vmo.order_currency = hkcur.currency_code '.
			'JOIN `#__hikashop_user` AS hkusr ON vmo.user_id = hkusr.user_cms_id '.
			'WHERE vmo.order_id > ' . $this->options->last_vm_order . ' '.
			'GROUP BY vmo.order_id '.
			'ORDER BY vmo.order_id ASC;';
		$data = array(
			'address_user_id' => 'vmui.user_id',
			'address_firstname' => 'vmui.first_name',
			'address_middle_name' => 'vmui.middle_name',
			'address_lastname' => 'vmui.last_name',
			'address_company' => 'vmui.company',
			'address_street' => "CONCAT(vmui.address_1,' ',vmui.address_2)",
			'address_post_code' => 'vmui.zip',
			'address_city' => 'vmui.city',
			'address_telephone' => 'vmui.phone_1',
			'address_telephone2' => 'vmui.phone_2',
			'address_fax' => 'vmui.fax',
			'address_state' => 'vmui.state',
			'address_country' => 'vmui.country',
			'address_published' => "case when vmui.address_type = 'BT' then 7 else 8 end",
			'address_vat' => $vat_cols,
			'address_vm_order_info_id' => 'vmui.order_id'
		);
		$sql2_1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `#__vm_order_user_info` AS vmui WHERE vmui.order_id > '.$this->options->last_vm_order.' ORDER BY vmui.order_info_id ASC';
		$sql2_2 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone` AS hkz ON (a.address_country = hkz.zone_code_3 AND hkz.zone_type = "country") '.
				'SET address_country = hkz.zone_namekey, address_published = 6 WHERE address_published >= 7;';
		$sql2_3 = 'UPDATE `#__hikashop_address` AS a '. // todo
				'JOIN `#__hikashop_zone_link` AS zl ON (a.address_country = zl.zone_parent_namekey) '.
				'JOIN `#__hikashop_zone` AS hks ON (hks.zone_namekey = zl.zone_child_namekey AND hks.zone_type = "state" AND hks.zone_code_3 = a.address_state) '.
				'SET address_state = hks.zone_namekey, address_published = 5 WHERE address_published = 6;';
		$sql2_4 = 'UPDATE `#__hikashop_address` AS a '.
				'SET address_published = 0 WHERE address_published > 4;';
		//--
		$sql3 = 'UPDATE `#__hikashop_order` AS o '.
			'INNER JOIN `#__hikashop_address` AS a ON a.address_vm_order_info_id = o.order_vm_id '.
			'SET o.order_billing_address_id = a.address_id, o.order_shipping_address_id = a.address_id '.
			"WHERE o.order_billing_address_id = 0 AND address_published >= 7 ;";
		//--
		$sql4 = 'UPDATE `#__hikashop_order` AS o '.
			'INNER JOIN `#__hikashop_address` AS a ON a.address_vm_order_info_id = o.order_vm_id '.
			'SET o.order_shipping_address_id = a.address_id '.
			"WHERE o.order_shipping_address_id = 0 AND address_published >= 8 ;";
		//--
		$sql5 = 'UPDATE `#__hikashop_order` AS a '.
				'JOIN `#__vm_order_payment` AS o ON a.order_vm_id = o.order_id '.
				'JOIN `#__vm_payment_method` AS p ON o.payment_method_id = p.payment_method_id '.
				"SET a.order_payment_method = CONCAT('vm import: ', p.payment_method_name) ".
				'WHERE a.order_vm_id > ' . $this->options->last_vm_order;
		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Imported orders: ' . $total . '<br/>';
		$this->db->setQuery($sql2_1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Imported orders\' addresses: ' . $total . '<br/>';
		$this->db->setQuery($sql3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Updating billing addresses: ' . $total . '<br/>';
		$this->db->setQuery($sql4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Updating shipping addresses: ' . $total . '<br/>';
		$this->db->setQuery($sql5);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Updating order payments: ' . $total . '<br/>';
		$this->db->setQuery($sql2_2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Updating orders: ' . $total;
		$this->db->setQuery($sql2_3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '/' . $total;
		$this->db->setQuery($sql2_4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '/' . $total . '<br/>';
		$ret = true;
		return $ret;
	}
	function importOrderItems() {
		if( $this->db == null )
			return false;
		$ret = false;
		$offset = $this->options->current;
		$count = 100;
		$data = array(
			'order_id' => 'hko.order_id',
			'product_id' => 'hkp.hk_id',
			'order_product_quantity' => 'vmp.product_quantity',
			'order_product_name' => 'vmp.order_item_name',
			'order_product_code' => 'vmp.order_item_sku',
			'order_product_price' => 'vmp.product_item_price',
			'order_product_tax' => '(vmp.product_final_price - vmp.product_item_price)',
			'order_product_options' => "''"
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_order_product` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__vm_order_item` AS vmp '.
			'INNER JOIN `#__hikashop_order` AS hko ON vmp.order_id = hko.order_vm_id '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkp ON hkp.vm_id = vmp.product_id '.
			'WHERE vmp.order_id > ' . $this->options->last_vm_order . ';';
		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '[Orders Items]<br/>' . $total ."\n<br/>\n";
		$ret = true;
		return $ret;
	}
	function importDownloads() {
		if( $this->db == null )
			return false;
		jimport('joomla.filesystem.file');
		$categoryClass = hikashop::get('class.category');
		$ret = false;
		$count = 100;
		$offset = $this->options->current;
		if( $offset == 0 ) {
			$offset = $this->options->last_vm_pfile;
		}
		$sql = "SELECT `config_value` FROM `#__hikashop_config` WHERE config_namekey = 'download_number_limit';";
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$dl_limit = $data[0]->config_value;
		$sql = 'SELECT vmf.file_id,vmf.file_name,vmf.file_is_image FROM `#__vm_product_files` AS vmf WHERE vmf.file_id > '.$offset.';';
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$max = 0;
		foreach($data as $c) {
			$file_name = str_replace('\\','/',$c->file_name);
			if( strpos($file_name,'/') !== false ) {
				$file_name = substr($file_name, strrpos($file_name,'/'));
			}
			$dstFolder = $this->options->uploadsecurefolder;
			if($c->file_is_image){
				$dstFolder = $this->options->uploadfolder;
			}
			$this->copyFile($this->vmImgDir,$c->file_name, $dstFolder.$file_name);
			$max = $c->file_id;
		}
		if( $max > 0 ) {
			echo '[Downloads/Files]<br/>Copying files...<br/>(last processed file id: ' . $max . ")\n<br/>\n";
			$this->options->current = $max;
			$this->refreshPage = true;
			return $ret;
		}
		echo '[Downloads/Files]<br/>';

		$data = array(
			'file_name' => 'vmf.file_title',
			'file_description' => 'vmf.file_description',
			'file_path' => "SUBSTRING_INDEX(SUBSTRING_INDEX(vmf.file_name, '/', -1), '\\\\', -1)",
			'file_type' => "case when vmf.file_is_image = 1 then 'product' else 'file' end",
			'file_ref_id' => 'hkp.hk_id'
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__vm_product_files` AS vmf '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkp ON hkp.vm_id = vmf.file_product_id '.
			'WHERE vmf.file_id > '.$this->options->last_vm_pfile.';';
		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Files: ' . $total . '<br/>';
		$data = array(
			'file_id' => 'hkf.file_id',
			'order_id' => 'hko.order_id',
			'download_number' => '(' . $dl_limit . '- vmd.download_max)'
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_download` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__vm_product_download` AS vmd '.
			'INNER JOIN `#__hikashop_order` AS hko ON hko.order_vm_id = vmd.order_id '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkp ON hkp.vm_id = vmd.product_id '.
			'INNER JOIN `#__hikashop_file` AS hkf ON (hkf.file_name = vmd.file_name AND hkp.hk_id = hkf.file_ref_id) '.
			"WHERE hkf.file_type = 'file' AND (vmd.product_id > ".$this->options->last_vm_prod.' OR vmd.order_id > ' . $this->options->last_vm_order . ');';
		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Downloads: ' . $total . '<br/>';
		$ret = true;
		return $ret;
	}
	function importDiscount() {
		if( $this->db == null )
			return false;
		$sql = "SELECT `config_value` FROM `#__hikashop_config` WHERE config_namekey = 'main_currency';";
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$main_currency = $data[0]->config_value;
		echo '[Discounts]<br/>';

		$data = array(
			'discount_type' => "'coupon'", //coupon or discount
			'discount_published' => '1',
			'discount_code' => '`coupon_code`',
			'discount_currency_id' => $main_currency,
			'discount_flat_amount' => "case when percent_or_total = 'total' then coupon_value else 0 end",
			'discount_percent_amount' => "case when percent_or_total = 'percent' then coupon_value else 0 end",
			'discount_quota' => "case when coupon_type = 'gift' then 1 else 0 end"
		);
		$sql = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM #__vm_coupons WHERE coupon_id > ' . $this->options->last_vm_coupon;
		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Discount codes/coupons: ' . $total . '<br/>';

		$data = array(
			'discount_type' => "'discount'", //coupon or discount
			'discount_published' => '1',
			'discount_code' => "CONCAT('discount_', vmp.product_sku)",
			'discount_currency_id' => $main_currency,
			'discount_flat_amount' => "case when vmd.is_percent = 0 then vmd.amount else 0 end",
			'discount_percent_amount' => "case when vmd.is_percent = 1 then vmd.amount else 0 end",
			'discount_quota' => "''",
			'discount_product_id' => 'hkp.hk_id',
			'discount_category_id' => '0',
			'discount_start' => "vmd.start_date",
			'discount_end' => "vmd.end_date"
		);
		$sql = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM #__vm_product vmp '.
			'INNER JOIN `#__vm_product_discount` vmd ON vmp.product_discount_id = vmd.discount_id '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkp ON hkp.vm_id = vmp.product_id '.
			'WHERE vmp.product_id > ' . $this->options->last_vm_prod;
		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo 'Product discounts: ' . $total . '<br/>';
		$ret = true;
		return $ret;
	}
	function copyFile($dir, $fsrc, $dst) {
		$src = $fsrc;
		if( file_exists($dir.$fsrc) ) {
			$src = $dir.$fsrc;
		} else if( file_exists(HIKASHOP_ROOT.$fsrc) ) {
			$src = HIKASHOP_ROOT.$fsrc;
		}
		if( file_exists($src) ) {
			if( !file_exists($dst) ) {
				$ret = JFile::copy($src, $dst);
				if( !$ret ) {
					echo 'The file "' . $src . '" could not be copied to "' . $dst . '"<br/>';
				}
			} else {
				echo 'File already exists "' .$dst . '" ("' . $src . '")<br/>';
			}
		} else {
			echo 'File is not found "' . $src . '"<br/>';
		}
	}
	function getHtmlPage() {
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .
			'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr" id="minwidth" >' .
			'<head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><title>HikaShop - VirtueMart Import</title>' .
			'<script type="text/javascript">' . "\r\n" . 'var r = false; function importVM() { setTimeout( function() { if(r) window.location.reload(); }, 1000 ); }' . "\r\n" . '</script>' .
			'</head><body onload="importVM()">'.
			'<h1>HikaShop: '.JText::_('PRODUCTS_FROM_VM').'</h1>';
	}
	function getStartPage() {
		return 'First, make a backup of your database.<br/>'.
			'When ready, click on <a href="'.hikashop::completeLink('import&task=import&importfrom=vm&'.$this->token.'=1&import=1').'">'.JText::_('HIKA_NEXT').'</a>, otherwise '.
			'<a href="'.hikashop::completeLink('import&task=show').'">'.JText::_('HIKA_BACK').'</a>.';
	}
	function proposeReImport() {
		if( !isset($_GET['reimport']) ) {
			echo '<p>You have already make an import. If you restart it, the import system will just import new elements</p>';
			echo '<p><a href="'.hikashop::completeLink('import&task=import&importfrom=vm&'.$this->token.'=1&import=1&reimport=1').'">Import new elements</a></p>';
			return false;
		}
		$sql =  "UPDATE `#__hikashop_config` SET config_value=1 WHERE config_namekey = 'vm_import_state';";
		$this->db->setQuery($sql);
		$this->db->query();
		$this->refreshPage = true;
		echo '<p>The import will restart and import new elements.</p>';
		return true;
	}
}
