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
class hikashopFileClass extends hikashopClass{
	var $tables = array('file');
	var $pkeys = array('file_id');
	var $namekeys = array();
	var $deleteToggle = array('file'=>array('file_type','file_ref_id'));
	var $error_type='';
	function saveFile($var_name='files',$type = 'image'){
		$file = JRequest::getVar( $var_name, array(), 'files', 'array' );
		if(empty($file['name'])){
			return false;
		}
		$app =& JFactory::getApplication();
		$config =& hikashop_config();
		if($type=='file'){
			$allowed = explode(',',strtolower($config->get('allowedfiles')));
		}else{
			$allowed = explode(',',strtolower($config->get('allowedimages')));
		}
		$uploadPath = $this->getPath($type);
		$tempData = array();
		if(empty($file['name'])) return false;
		$file_path = strtolower(JFile::makeSafe($file['name']));
		$extension = strtolower(substr($file_path,strrpos($file_path,'.')+1));
		if(!in_array($extension,$allowed)){
			$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',$extension,implode(',',$allowed)), 'notice');
			return false;
		}
		if(JFile::exists($uploadPath . $file_path)){
			$pos = strrpos($file_path,'.');
			$file_path = substr($file_path,0,$pos).'_'.rand().'.'.substr($file_path,$pos+1);
		}
		if(!JFile::upload($file['tmp_name'], $uploadPath . $file_path)){
			if ( !move_uploaded_file($file['tmp_name'], $uploadPath . $file_path)) {
				$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$file['tmp_name'],$uploadPath . $file_path), 'error');
				return false;
			}
		}
		return $file_path;
	}
	function storeFiles($type,$pkey,$var_name = 'files'){
		$ids = array();
		$files = JRequest::getVar( $var_name, array(), 'files', 'array' );
		if(!empty($files['name'][0]) OR !empty($files['name'][1])){
			$app =& JFactory::getApplication();
			$config =& hikashop_config();
			if($type=='file'){
				$allowed = explode(',',strtolower($config->get('allowedfiles')));
			}else{
				$allowed = explode(',',strtolower($config->get('allowedimages')));
				$imageHelper = hikashop_get('helper.image');
			}
			$uploadPath = $this->getPath($type);
			$tempData = array();
			foreach($files['name'] as $id => $filename){
				if(empty($filename)) continue;
				$file_path = strtolower(JFile::makeSafe($filename));
				$extension = strtolower(substr($file_path,strrpos($file_path,'.')+1));
				if(!in_array($extension,$allowed)){
					$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',$extension,implode(',',$allowed)), 'notice');
					continue;
				}
				$tempData[$id]= $file_path;
			}
			if(!empty($tempData)){
				switch($type){
					case 'category':
						$query = 'SELECT * FROM '.hikashop_table(end($this->tables)).' WHERE file_ref_id = '.$pkey.' AND file_type=\'category\'';
						$this->database->setQuery($query);
						$oldEntries = $this->database->loadObjectList();
						foreach($oldEntries as $old){
							if(JFile::exists( $uploadPath . $old->file_path)) JFile::delete( $uploadPath . $old->file_path );
						}
						break;
				}
				foreach( $tempData as $id => $file_path){
					if(JFile::exists($uploadPath . $file_path)){
						$pos = strrpos($file_path,'.');
						$file_path = substr($file_path,0,$pos).'_'.rand().'.'.substr($file_path,$pos+1);
					}
					if(!JFile::upload($files['tmp_name'][$id], $uploadPath . $file_path)){
						if ( !move_uploaded_file($files['tmp_name'][$id], $uploadPath . $file_path)) {
							$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$files['tmp_name'][$id],$uploadPath . $file_path), 'error');
							continue;
						}
					}
					if(!in_array($type,array('file','watermark'))){
						$imageHelper->resizeImage($file_path);
						$imageHelper->generateThumbnail($file_path);
					}
					$element = null;
					$element->file_path = $file_path;
					$element->file_type = $type;
					$element->file_ref_id = $pkey;
					$status = $this->save($element);
					if($status){
						$ids[$id] = $status;
					}
				}
			}
		}elseif(JRequest::getVar('ctrl')=='product'){
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_( 'ADD_FILE_VIA_BROWSE_BUTTON'),'error');
		}
		if(!empty($ids)){
			switch($type){
				case 'category':
					$query = 'DELETE FROM '.hikashop_table(end($this->tables)).' WHERE file_id NOT IN ('.implode(',',$ids).') AND file_ref_id = '.$pkey.' AND file_type=\'category\'';
					$this->database->setQuery($query);
					$this->database->query();
					break;
			}
		}
		return $ids;
	}
	function deleteFiles($type,$pkeys){
		if(!is_array($pkeys)) $pkeys = array($pkeys);
		$uploadPath = $this->getPath($type);
		$query = 'SELECT * FROM '.hikashop_table(end($this->tables)).' WHERE file_ref_id IN ('.implode(',',$pkeys).') AND file_type=\''.$type.'\'';
		$this->database->setQuery($query);
		$oldEntries = $this->database->loadObjectList();
		if(!empty($oldEntries)){
			$paths = array();
			$ids = array();
			foreach($oldEntries as $old){
				$paths[] = $this->database->Quote($old->file_path);
				$ids[] = $old->file_id;
			}
			$query = 'SELECT file_path FROM '.hikashop_table(end($this->tables)).' WHERE file_path IN ('.implode(',',$paths).') AND file_id NOT IN ('.implode(',',$ids).')';
			$this->database->setQuery($query);
			$stillUsed = $this->database->loadResultArray();
			foreach($oldEntries as $old){
				if((empty($stillUsed) || !in_array($old->file_path,$stillUsed))&&JFile::exists( $uploadPath . $old->file_path)){
					JFile::delete( $uploadPath . $old->file_path );
					if(!in_array($type,array('file','watermark')) && JFile::exists(  $uploadPath .'thumbnail'.DS. $old->file_path)){
						JFile::delete( $uploadPath .'thumbnail'.DS. $old->file_path );
					}
				}
			}
			$query = 'DELETE FROM '.hikashop_table(end($this->tables)).' WHERE file_ref_id IN ('.implode(',',$pkeys).') AND file_type=\''.$type.'\'';
			$this->database->setQuery($query);
			$this->database->query();
			$elements = array();
			foreach($oldEntries as $old){
				$elements[]=$old->file_id;
			}
			$class = hikashop_get('helper.translation');
			$class->deleteTranslations('file',$elements);
		}
	}
	function resetdownload($file_id,$order_id=0){
		$query = 'UPDATE '.hikashop_table('download').' SET download_number=0 WHERE file_id='.(int)$file_id;
		if(!empty($order_id)){
			$query .= ' AND order_id='.(int)$order_id;
		}
		$this->database->setQuery($query);
		return $this->database->query();
	}
	function download($file_id,$order_id=0){
		$app =& JFactory::getApplication();
		$file = $this->get($file_id);
		if(!$app->isAdmin() && empty($file->file_free_download)){
			$user_id = hikashop_loadUser();
			if(empty($user_id)){
				$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
				$this->error_type = 'login';
				return false;
			}
			$orderClass = hikashop_get('class.order');
			$order = $orderClass->get($order_id);
			if(empty($order) || $order->order_user_id != $user_id){
				$app->enqueueMessage(JText::_('ORDER_NOT_FOUND'));
				$this->error_type = 'no_order';
				return false;
			}
			$config =& hikashop_config();
			$order_status_for_download = $config->get('order_status_for_download','confirmed,shipped');
			if(!in_array($order->order_status,explode(',',$order_status_for_download))){
				$app->enqueueMessage(JText::_('BECAUSE_STATUS_NO_DOWNLOAD'));
				$this->error_type = 'status';
				return false;
			}
			$download_time_limit = $config->get('download_time_limit',0);
			if(!empty($download_time_limit) && ($download_time_limit+$order->order_created)<time()){
				$app->enqueueMessage(JText::_('TOO_LATE_NO_DOWNLOAD'));
				$this->error_type = 'date';
				return false;
			}
			$query = 'SELECT a.* FROM '.hikashop_table('order_product').' AS a WHERE a.order_id = '.$order_id;
			$this->database->setQuery($query);
			$order->products = $this->database->loadObjectList();
			$product_ids = array();
			foreach($order->products as $product){
				$products_ids[]=$product->product_id;
			}
			$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$products_ids).') AND product_type=\'variant\'';
			$this->database->setQuery($query);
			$products = $this->database->loadObjectList();
			if(!empty($products)){
				foreach($products as $product){
					foreach($order->products as $item){
						if($product->product_id == $item->product_id && !empty($product->product_parent_id)){
							$item->product_parent_id = $product->product_parent_id;
							$products_ids[]=$product->product_parent_id;
						}
					}
				}
			}
			$filters = array('a.file_ref_id IN ('.implode(',',$products_ids).')','a.file_type=\'file\'','a.file_id='.$file_id);
			$query = 'SELECT a.*,b.* FROM '.hikashop_table('file').' AS a LEFT JOIN '.hikashop_table('download').' AS b ON b.order_id='.$order->order_id.' AND a.file_id = b.file_id WHERE '.implode(' AND ',$filters);
			$this->database->setQuery($query);
			$fileData = $this->database->loadObject();
			if(!empty($fileData)){
				$download_number_limit = $config->get('download_number_limit',0);
				if(!empty($download_number_limit) && $download_number_limit<=$fileData->download_number){
					$app->enqueueMessage(JText::_('MAX_REACHED_NO_DOWNLOAD'));
					$this->error_type = 'limit';
					return false;
				}
			}else{
				$app->enqueueMessage(JText::_('FILE_NOT_FOUND'));
				$this->error_type = 'no_file';
				return false;
			}
		}
		if(!empty($file)){
			$path = $this->getPath('file');
			if(file_exists( $path . $file->file_path)){
				if(!$app->isAdmin() && empty($file->file_free_download)){
					$query = 'SELECT * FROM '.hikashop_table('download').' WHERE file_id='.$file->file_id.' AND order_id='.$order_id;
					$this->database->setQuery($query);
					$download = $this->database->loadObject();
					if(empty($download)){
						$query = 'INSERT INTO '.hikashop_table('download').'(file_id,order_id,download_number) VALUES('.$file->file_id.','.$order_id.',1);';
					}else{
						$query = 'UPDATE '.hikashop_table('download').' SET download_number=download_number+1 WHERE file_id='.$file->file_id.' AND order_id='.$order_id;
					}
					$this->database->setQuery($query);
					$this->database->query();
				}
				$this->sendFile($path.$file->file_path);
			}
		}
		$app->enqueueMessage(JText::_('FILE_NOT_FOUND'));
		return true;
	}




function sendFile($file, $is_resume=TRUE){
	JPluginHelper::importPlugin( 'hikashop' );
	$dispatcher =& JDispatcher::getInstance();
	$do = true;
	$dispatcher->trigger( 'onBeforeDownloadFile', array( & $file, & $do) );
	if(!$do) return false;
	clearstatcache();
    $size = filesize($file);
    $fileinfo = pathinfo($file);
    $filename = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ?
                  preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'], '.') - 1) :
                  $fileinfo['basename'];
    if($is_resume && isset($_SERVER['HTTP_RANGE']))
    {
        list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        if ($size_unit == 'bytes')
        {
            list($range, $extra_ranges) = explode(',', $range_orig, 2);
        }
        else
        {
            $range = '';
        }
    }
    else
    {
        $range = '';
    }
    $seek = explode('-', $range, 2);
    $seek_end = (empty($seek[1])) ? ($size - 1) : min(abs(intval($seek[1])),($size - 1));
    $seek_start = (empty($seek[0]) || $seek_end < abs(intval($seek[0]))) ? 0 : max(abs(intval($seek[0])),0);
    if ($is_resume)
    {
        if ($seek_start > 0 || $seek_end < ($size - 1))
        {
            header('HTTP/1.1 206 Partial Content');
        }
        header('Accept-Ranges: bytes');
        header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$size);
    }
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: '.($seek_end - $seek_start + 1));
    header("Cache-Control: maxage=1");
	header("Pragma: public");
	header("Content-Transfer-Encoding: binary");
    $fp = fopen($file, 'rb');
    fseek($fp, $seek_start);
    set_time_limit(0);
    while(!feof($fp))
    {
       print(fread($fp, 8192));
        flush();
        ob_flush();
    }
    fclose($fp);
    exit;
}
	function downloadFieldFile($name,$field_table,$field_namekey){
		$app =& JFactory::getApplication();
		if(!$app->isAdmin()){
			$found = false;
			switch($field_table){
				case 'entry':
					$entriesData = $app->getUserState(HIKASHOP_COMPONENT.'.entries_fields');
					if(!empty($entriesData)){
						foreach($entriesData as $entryData){
							if(@$entryData->$field_namekey==$name){
								$found = true;
							}
						}
					}
					break;
				case 'order':
					$orderData = $app->getUserState( HIKASHOP_COMPONENT.'.checkout_fields');
					if(@$orderData->$field_namekey==$name){
						$found = true;
					}
					break;
				case 'item':
					$class = hikashop_get('class.cart');
					$products = $class->get();
					if(!empty($products)){
						foreach( $products as $product ){
							if(@$product->$field_namekey==$name){
								$found = true;
							}
						}
					}
					break;
				default:
					break;
			}
			if(!$found){
				switch($field_table){
					case 'order':
						$this->database->setQuery('SELECT order_id FROM '.hikashop_table('order').' WHERE order_user_id='.hikashop_loadUser().' AND '.$field_namekey.' = '.$this->database->Quote($name));
						break;
					case 'item':
						$this->database->setQuery('SELECT b.order_product_id FROM '.hikashop_table('order').' AS a LEFT JOIN '.hikashop_table('order_product').' AS b ON a.order_id=b.order_id WHERE a.order_user_id='.hikashop_loadUser(). ' AND b.'.$field_namekey.' = '.$this->database->Quote($name));
						break;
					case 'entry':
						$this->database->setQuery('SELECT b.entry_id FROM '.hikashop_table('order').' AS a LEFT JOIN '.hikashop_table('entry').' AS b ON a.order_id=b.order_id WHERE a.order_user_id='.hikashop_loadUser().' AND b.'.$field_namekey.' = '.$this->database->Quote($name));
						break;
					case 'user':
						$this->database->setQuery('SELECT user_id FROM '.hikashop_table('user').' WHERE user_id='.hikashop_loadUser().' AND '.$field_namekey.' = '.$this->database->Quote($name));
						break;
					case 'address':
						$this->database->setQuery('SELECT address_id FROM '.hikashop_table('address').' WHERE address_user_id='.hikashop_loadUser().' AND '.$field_namekey.' = '.$this->database->Quote($name));
						break;
					case 'product':
						$filters = array($field_namekey.' = '.$this->database->Quote($name),'product_published=1');
						hikashop_addACLFilters($filters,'product_access');
						$this->database->setQuery('SELECT product_id FROM '.hikashop_table('product').' WHERE '.implode(' AND ',$filters));
						break;
					case 'category':
						$filters = array($field_namekey.' = '.$this->database->Quote($name),'category_published=1');
						hikashop_addACLFilters($filters,'category_access');
						$this->database->setQuery('SELECT category_id FROM '.hikashop_table('category').' WHERE '.implode(' AND ',$filters));
						break;
					default:
						return false;
				}
				$result = $this->database->loadResult();
				if($result){
					$found = true;
				}
			}
			if(!$found){
				return false;
			}
		}
		$path = $this->getPath('file');
		if(file_exists( $path . $name)){
			$this->sendFile($path.$name);
		}
		return false;
	}
	function getPath($type){
		$app =& JFactory::getApplication();
		jimport('joomla.filesystem.file');
		$config =& hikashop_config();
		if($type=='file'){
			$uploadFolder=$config->get('uploadsecurefolder');
		}else{
			$uploadFolder=$config->get('uploadfolder');
		}
		$uploadFolder = rtrim(JPath::clean(html_entity_decode($uploadFolder)),DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$uploadFolder)){
			if(!$uploadFolder[0]=='/' || !is_dir($uploadFolder)){
				$uploadFolder = JPath::clean(HIKASHOP_ROOT.DS.trim($uploadFolder,DS.' ').DS);
			}
		}
		$this->checkFolder($uploadFolder);
		if($type!='file'){
			$this->checkFolder($uploadFolder.'thumbnail'.DS);
		}
		return $uploadFolder;
	}
	function checkFolder($uploadPath){
		if(!is_dir($uploadPath)){
			jimport('joomla.filesystem.folder');
			JFolder::create($uploadPath);
		}
		if(!is_writable($uploadPath)){
			@chmod($uploadPath,'0755');
			if(!is_writable($uploadPath)){
				$app =& JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf( 'WRITABLE_FOLDER',$uploadPath), 'notice');
				return false;
			}
		}
		return true;
	}
}