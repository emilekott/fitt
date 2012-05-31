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
if(!empty($this->orders)){
	$maxProd = 0;
	$productFields = null;
	foreach($this->orders as $order){
		$nbProd = count($order->products);
		if($maxProd<$nbProd){
			$maxProd = $nbProd;
			if(empty($productFields)){
				$productFields = array_keys(get_object_vars(reset($order->products)));
			}
		}
	}
	$config =& hikashop_config();
	$separator = $config->get('csv_separator',";");
	$first = reset($this->orders);
	$firstCol = true;
	foreach($first as $key => $val){
		if(!is_array($val)){
			if($firstCol){
				$firstCol = false;
			}else{
				echo $separator;
			}
			echo '"'.str_replace('"','""',$key).'"';
		}
	}
	if($maxProd && !empty($productFields)){
		for($i=1;$i<=$maxProd;$i++){
			foreach($productFields as $field){
				echo $separator.'"item'.$i.'_'.str_replace('"','""',$field).'"';
			}
		}
	}
	echo $eol;
	foreach($this->orders as $row){
		$firstCol = true;
		foreach($row as $key => $val){
			if(!is_array($val)){
				if($firstCol){
					$firstCol = false;
				}else{
					echo $separator;
				}
				echo '"'.str_replace('"','""',$val).'"';
			}
		}
		if($maxProd && !empty($productFields)){
			for($i=0;$i<$maxProd;$i++){
				$prod =& $row->products[$i];
				foreach($productFields as $field){
					echo $separator.'"'.str_replace('"','""',@$prod->$field).'"';
				}
			}
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