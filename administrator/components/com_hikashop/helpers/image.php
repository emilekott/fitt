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
class hikashopImageHelper{
	function hikashopImageHelper(){
		$config =& hikashop_config();
		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$this->uploadFolder_url = str_replace(DS,'/',$uploadFolder);
		$this->uploadFolder = JPATH_ROOT.DS.$uploadFolder;
		$app =& JFactory::getApplication();
		if($app->isAdmin()){
			$this->uploadFolder_url = '../'.$this->uploadFolder_url;
		}else{
			$this->uploadFolder_url = JURI::base().$this->uploadFolder_url;
		}
		$this->thumbnail = $config->get('thumbnail',1);
		$this->thumbnail_x=$config->get('thumbnail_x',100);
		$this->thumbnail_y=$config->get('thumbnail_y',100);
		$this->main_thumbnail_x=$this->thumbnail_x;
		$this->main_thumbnail_y=$this->thumbnail_y;
		$this->main_uploadFolder_url = $this->uploadFolder_url;
		$this->main_uploadFolder = $this->uploadFolder;
		static $done = false;
		static $override = false;
		if(!$done){
			$done = true;
			$app =& JFactory::getApplication();
			$chromePath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'hikashop_image.php';
			if (file_exists($chromePath)){
				require_once ($chromePath);
				$override = true;
			}
		}
		$this->override = $override;
	}
	function display($path,$addpopup=true,$alt="",$options='',$optionslink='', $width=0, $height=0){
		$html = '';
		if(!$this->_checkImage($this->uploadFolder.$path)){
			$config =& hikashop_config();
			$path = $config->get('default_image');
			if($path == 'barcode.png'){
				$this->uploadFolder_url=HIKASHOP_IMAGES;
				$this->uploadFolder=HIKASHOP_MEDIA.'images'.DS;
			}
			if(!$this->_checkImage($this->uploadFolder.$path)){
				$this->uploadFolder_url = $this->main_uploadFolder_url;
				$this->uploadFolder = $this->main_uploadFolder;
				return $html;
			}
		}
		if($width!=0 && $height!=0){
			$this->width=$width;
			$this->height=$height;
			$module[0]=$height;
			$module[1]=$width;
			$this->main_thumbnail_x=$width;
			$this->main_thumbnail_y=$height;
			$html = $this->displayThumbnail($path,$alt,is_string($addpopup),$options, $module);
		}else{
			list($this->width, $this->height) = getimagesize($this->uploadFolder.$path);
			$html = $this->displayThumbnail($path,$alt,is_string($addpopup),$options);
		}
		if($addpopup){
			$config =& hikashop_config();
			$popup_x=$config->get('max_x_popup',760);
			$popup_y=$config->get('max_y_popup',480);
			$this->width+=20;
			$this->height+=30;
			if($this->width>$popup_x) $this->width = $popup_x;
			if($this->height>$popup_y) $this->height = $popup_y;
			if(is_string($addpopup)){
				static $first=true;
				if($first){
					if($this->override && function_exists('hikashop_image_toggle_js')){
						$js = hikashop_image_toggle_js($this);
					}else{
					$js = '
					function hikashopChangeImage(id,url,x,y,obj){
						image=document.getElementById(id);
						if(image){
							image.src=url;
							if(x) image.width=x;
							if(y) image.height=y;
						}
						image_link = document.getElementById(id+\'_link\');
						if(image_link){
							image_link.href=obj.href;
							image_link.rel=obj.rel;
						}
						var myEls = getElementsByClass(\'hikashop_child_image\');
						for ( i=0;i<myEls.length;i++ ) {
							myEls[i].style.border=\'0px\';
						}
						obj.childNodes[0].style.border=\'1px solid\';
						return false;
					}
					function getElementsByClass(searchClass,node,tag) {
						var classElements = new Array();
						if ( node == null )
							node = document;
						if ( tag == null )
							tag = \'*\';
						var els = node.getElementsByTagName(tag);
						var elsLen = els.length;
						var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
						for (i = 0, j = 0; i < elsLen; i++) {
							if ( pattern.test(els[i].className) ) {
								classElements[j] = els[i];
								j++;
							}
						}
						return classElements;
					}
					window.addEvent(\'domready\', function() {
						image_link = document.getElementById(\'hikashop_image_small_link_first\');
						if(image_link){
							image_link.childNodes[0].style.border=\'1px solid\';
						}
					});
					';
					}
					$doc =& JFactory::getDocument();
					$doc->addScriptDeclaration("<!--\n".$js."\n//-->");
					$first=false;
					$optionslink.=' id="hikashop_image_small_link_first" ';
					JHTML::_('behavior.modal');
				}
				if(!empty($this->no_size_override)){
					$this->thumbnail_x = '';
					$this->thumbnail_y = '';
					$this->uploadFolder_url_thumb = $this->uploadFolder_url.$path;
				}
				if($this->override && function_exists('hikashop_small_image_link_render')){
					$html = hikashop_small_image_link_render($this,$path,$addpopup,$optionslink,$html);
				}else{
					$html = '<a title="'.$alt.'" class="hikashop_image_small_link" rel="{handler: \'image\'}" href="'.$this->uploadFolder_url.$path.'" onclick="SqueezeBox.fromElement(this,{parse: \'rel\'});return false;" target="_blank" onmouseover="return hikashopChangeImage(\''.$addpopup.'\',\''.$this->uploadFolder_url_thumb.'\',\''.$this->thumbnail_x.'\',\''.$this->thumbnail_y.'\',this);" '.$optionslink.'>'.$html.'</a>';
				}
			}else{
				JHTML::_('behavior.modal');
				if($this->override && function_exists('hikashop_image_link_render')){
					$html = hikashop_image_link_render($this,$path,$addpopup,$optionslink,$html);
				}else{
					$html = '<a title="'.$alt.'" rel="{handler: \'image\'}" target="_blank" href="'.$this->uploadFolder_url.$path.'" onclick="SqueezeBox.fromElement(this,{parse: \'rel\'});return false;" '.$optionslink.'>'.$html.'</a>';
				}
			}
		}
		$this->uploadFolder_url = $this->main_uploadFolder_url;
		$this->uploadFolder = $this->main_uploadFolder;
		return $html;
	}
	function _checkImage($path){
		if(!empty($path)){
			jimport('joomla.filesystem.file');
			if(JFile::exists($path)){
				return true;
			}
		}
		return false;
	}




	function getPath($file_path,$url=true){
		if($url){
			return $this->uploadFolder_url.$file_path;
		}
		return $this->uploadFolder.$file_path;
	}
	function displayThumbnail($path,$alt='',$reduceSize=false,$options='',$module=false){
		$new = $this->scaleImage($this->width, $this->height,$this->main_thumbnail_x,$this->main_thumbnail_y);
		if($new!==false){
			$this->thumbnail_x=$new[0];
			$this->thumbnail_y=$new[1];
		}else{
			$this->thumbnail_x = $this->width;
			$this->thumbnail_y = $this->height;
		}
		if(!$reduceSize && !$module){
			$options.=' height="'.$this->thumbnail_y.'" width="'.$this->thumbnail_x.'" ';
		}
		if($this->thumbnail){
			jimport('joomla.filesystem.file');
			$ok = true;
		if(!JFile::exists($this->uploadFolder.'thumbnail_'.$this->thumbnail_y.'x'.$this->thumbnail_x.DS.$path)){
			if($module){
				$ok = $this->generateThumbnail($path, $module);
			}
			else{
				$ok = $this->generateThumbnail($path);
			}
		}
			if($ok){
				$this->uploadFolder_url_thumb=$this->uploadFolder_url.'thumbnail_'.$this->thumbnail_y.'x'.$this->thumbnail_x.'/'.$path;
				return '<img src="'.$this->uploadFolder_url_thumb.'" alt="'.$alt.'" '.$options.' />';
			}
		}
		$this->uploadFolder_url_thumb=$this->uploadFolder_url.$path;
		return '<img src="'.$this->uploadFolder_url_thumb.'" alt="'.$alt.'" '.$options.' />';
	}
	function generateThumbnail($file_path, $module=false){
		$ok = true;
		if($this->thumbnail){
			$ok = false;
			$gd_ok = false;
			if (function_exists('gd_info')) {
				$gd = gd_info();
				if (isset ($gd["GD Version"])) {
					$gd_ok = true;
					list($this->width, $this->height) = getimagesize($this->uploadFolder.$file_path);
					$config =& hikashop_config();
					if($module){
						$thumbnail_x=$module[1];
						$thumbnail_y=$module[0];
					}
					else{
						$thumbnail_x=$config->get('thumbnail_x',100);
						$thumbnail_y=$config->get('thumbnail_y',100);
					}
					if(!$thumbnail_x && !$thumbnail_y){
						return true;
					};
					$new = $this->scaleImage($this->width, $this->height,$thumbnail_x,$thumbnail_y);
					if($new!==false){
						$ok = $this->_resizeImage($file_path,$new[0],$new[1]);
					}
				}
			}
			if(!$gd_ok){
				$app =& JFactory::getApplication();
				if($app->isAdmin()){
					$app->enqueueMessage('The PHP GD extension could not be found. Thus, it is impossible to generate thumbnails in PHP from your images. If you want HikaShop to generate thumbnails you need to install GD or ask your hosting company to do so. Otherwise, you can deactivate thumbnails creation in the configuration of HikaShop and this message won\'t be displayed');
				}
			}
		}
		return $ok;
	}
	function resizeImage($file_path){
		$config =& hikashop_config();
		$image_x=$config->get('image_x',0);
		$image_y=$config->get('image_y',0);
		$watermark_name = $config->get('watermark','');
		$ok = true;
		if(($image_x || $image_y) || !empty($watermark_name)){
			$ok = false;
			$gd_ok = false;
			if (function_exists('gd_info')) {
				$gd = gd_info();
				if (isset ($gd["GD Version"])) {
					$gd_ok = true;
					$new = getimagesize($this->uploadFolder.$file_path);
					$this->width=$new[0];
					$this->height=$new[1];
					if(!$image_x && !$image_y && empty($watermark_name)){
						return true;
					}
					if($image_x || $image_y){
						$new = $this->scaleImage($this->width, $this->height,$image_x,$image_y);
						if($new===false){
							$new = array($this->width,$this->height);
						}
					}
					$ok = $this->_resizeImage($file_path,$new[0],$new[1],$this->uploadFolder,'image');
				}
			}
			if(!$gd_ok){
				$app =& JFactory::getApplication();
				if($app->isAdmin()){
					$app->enqueueMessage('The PHP GD extension could not be found. Thus, it is impossible to process your images in PHP. If you want HikaShop to process your images, you need to install GD or ask your hosting company to do so. Otherwise, you can deactivate thumbnails creation, remove your watermark image if any, and clear the image max width and height in the configuration of HikaShop and this message won\'t be displayed');
				}
			}
		}
		return $ok;
	}
	function _resizeImage($file_path,$newWidth,$newHeight,$dstFolder='',$type='thumbnail'){
		$image = $this->uploadFolder.$file_path;
		if(empty($dstFolder)){
			$dstFolder = $this->uploadFolder.'thumbnail_'.$this->thumbnail_y.'x'.$this->thumbnail_x.DS;
		}
		$watermark_path = '';
		if($type=='image'){
			if(hikashop_level(2)){
				$config =& hikashop_config();
				$watermark_name = $config->get('watermark','');
				if(!empty($watermark_name)){
					$watermark_path = $this->main_uploadFolder.$watermark_name;
					if(!$this->_checkImage($watermark_path)){
						$watermark_path = '';
					}else{
						$wm_extension = strtolower(substr($watermark_path,strrpos($watermark_path,'.')+1));
						$watermark = $this->_getImage($watermark_path,$wm_extension);
					}
				}
			}
		}
		$extension = strtolower(substr($file_path,strrpos($file_path,'.')+1));
		$img = $this->_getImage($image,$extension);
		if($newWidth!=$this->width || $newHeight!=$this->height){
			$thumb = ImageCreateTrueColor($newWidth, $newHeight);
			if(in_array($extension,array('gif','png'))){
				$trnprt_indx = imagecolortransparent($img);
				if ($trnprt_indx >= 0) {
					$trnprt_color = imagecolorsforindex($img, $trnprt_indx);
					$trnprt_indx = imagecolorallocate($thumb, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
					imagefill($thumb, 0, 0, $trnprt_indx);
					imagecolortransparent($thumb, $trnprt_indx);
				}elseif($extension=='png'){
					imagealphablending($thumb, false);
					$color = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
					imagefill($thumb, 0, 0, $color);
					imagesavealpha($thumb,true);
				}
			}
			if(function_exists("imageAntiAlias")) {
				imageAntiAlias($thumb,true);
			}
			if(function_exists("imagecopyresampled")){
				ImageCopyResampled($thumb, $img, 0, 0, 0, 0, $newWidth, $newHeight,$this->width, $this->height);
			}else{
				ImageCopyResized($thumb, $img, 0, 0, 0, 0, $newWidth, $newHeight,$this->width, $this->height);
			}
		}else{
			$thumb =& $img;
		}
		if(!empty($watermark_path)){
			list($wm_width,$wm_height) = getimagesize($watermark_path);
			$padding = 3;
			$dest_x = $newWidth - $wm_width - $padding;
			$dest_y = $newHeight - $wm_height - $padding;
			$this->imagecopymerge_alpha($thumb, $watermark, $dest_x, $dest_y, 0, 0, $wm_width, $wm_height, (int)$config->get('opacity',0));
			imagedestroy($watermark);
		}
		$dest = $dstFolder.$file_path;
		ob_start();
		switch($extension){
			case 'gif':
				$status = imagegif($thumb);
				break;
			case 'jpg':
			case 'jpeg':
				$status = imagejpeg($thumb,null,100);
				break;
			case 'png':
				$status = imagepng($thumb,null,0);
				break;
		}
		$imageContent = ob_get_clean();
		$status = $status && JFile::write($dest,$imageContent);



		imagedestroy($img);
		@imagedestroy($thumb);
		return $status;
	}
	function _getImage($image,$extension){
		switch($extension){
			case 'gif':
				return ImageCreateFromGIF($image);
				break;
			case 'jpg':
			case 'jpeg':
				return ImageCreateFromJPEG($image);
				break;
			case 'png':
				return ImageCreateFromPNG($image);
				break;
		}
	}
	function scaleImage($x,$y,$cx,$cy) {
		if(empty($cx)){
			$cx = 9999;
		}
		if(empty($cy)){
			$cy = 9999;
		}
	    if ($x>=$cx || $y>=$cy) {
	        if ($x>0) $rx=$cx/$x;
	        if ($y>0) $ry=$cy/$y;
	        if ($rx>$ry) {
	            $r=$ry;
	        } else {
	            $r=$rx;
	        }
	        $x=intval($x*$r);
	        $y=intval($y*$r);
	        return array($x,$y);
	    }
	    return false;
	}
	function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
	    if(!isset($pct)){
	        return false;
	    }
	    $pct /= 100;
	    $w = imagesx( $src_im );
	    $h = imagesy( $src_im );
	    imagealphablending( $src_im, false );
	    $minalpha = 127;
	    for( $x = 0; $x < $w; $x++ )
	    for( $y = 0; $y < $h; $y++ ){
	        $alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF;
	        if( $alpha < $minalpha ){
	            $minalpha = $alpha;
	        }
	    }
	    for( $x = 0; $x < $w; $x++ ){
	        for( $y = 0; $y < $h; $y++ ){
	            $colorxy = imagecolorat( $src_im, $x, $y );
	            $alpha = ( $colorxy >> 24 ) & 0xFF;
	            if( $minalpha !== 127 ){
	                $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
	            } else {
	                $alpha += 127 * $pct;
	            }
	            $alphacolorxy = imagecolorallocatealpha( $src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
	            if( !imagesetpixel( $src_im, $x, $y, $alphacolorxy ) ){
	                return false;
	            }
	        }
	    }
	    imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
	}
}
