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
jimport( 'joomla.plugin.plugin' );
class plgContentHikashopsocial extends JPlugin
{
		var $meta='';
	function plgContentHikashopsocial( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		if ( (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ||
     		(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') ) {
			$this->https = 's';
		} else {
			$this->https = '';
		}
	}
	function onAfterRender(){
		$app =& Jfactory::getApplication();
		if(!$app->isAdmin() && JRequest::getVar('option')=='com_hikashop' && JRequest::getVar('ctrl')=='product' && JRequest::getVar('task')=='show'){
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('content','hikashopsocial');
			$body = JResponse::getBody();
			if(strpos($body,'{hikashop_social}')){
				if(!isset($plugin->params['position'])){
					$plugin->params['position'] = 0;
					$plugin->params['display_twitter'] = 1;
					$plugin->params['display_fb'] = 1;
					$plugin->params['display_google'] = 1;
					$plugin->params['fb_style'] = 0;
					$plugin->params['fb_faces'] = 1;
					$plugin->params['fb_verb'] = 0;
					$plugin->params['fb_theme'] = 0;
					$plugin->params['fb_font'] = 0;
					$plugin->params['fb_type'] = 0;
					$plugin->params['twitter_count'] = 0;
					$plugin->params['google_size']=2;
					$plugin->params['google_count']=1;
				}
				if($plugin->params['position']==0) $html='<div id="hikashop_social" style="text-align:left;">';
				else if($plugin->params['position']==1 && $plugin->params['width']!=0) $html='<div id="hikashop_social" style="text-align:right; width:'.$plugin->params['width'].'px">';
				else{ $html='<div id="hikashop_social" style="text-align:right; width:100%">'; }
				if(@$plugin->params['display_google']) $html.=$this->_addGoogleButton( $plugin);
				if($plugin->params['display_twitter']) $html.=$this->_addTwitterButton( $plugin);
				if($plugin->params['display_fb']) $html.=$this->_addFacebookButton( $plugin);
				$html.='</div>';
				$body = str_replace('{hikashop_social}',$html,$body);
				if(@$plugin->params['display_google']){
					$body=str_replace('</head>', '<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script></head>', $body);
				}
				if($plugin->params['display_fb']){
					$body=str_replace('<html ', '<html xmlns:fb="https://www.facebook.com/2008/fbml" xmlns:og="http://ogp.me/ns#" 	', $body);
				}
				$body=str_replace('</head>', $this->meta.'</head>', $body);
				JResponse::setBody($body);
			}
		}
	}
	function _addGoogleButton(&$plugin){
		if($plugin->params['google_size']==0){ $size='size="standard"'; }
		if($plugin->params['google_size']==1){ $size='size="small"'; }
		if($plugin->params['google_size']==2){ $size='size="medium"'; }
		if($plugin->params['google_size']==3){ $size='size="tall"'; }
		if($plugin->params['google_count']==1){ $count='count="true"'; }
		else{ $count='count="false"'; }
		$div='<span>';
		if($plugin->params['fb_style']==0 && $plugin->params['position']==1 && $plugin->params['twitter_count']==2){ $div="<span class='hikashop_social_google_right_tw_none_fb_standart'>";	}
		if($plugin->params['fb_style']==1 && $plugin->params['position']==1 && $plugin->params['twitter_count']==2){ $div="<span class='hikashop_social_google_right_tw_vertical'>";	}
		if($plugin->params['fb_style']==2 && $plugin->params['position']==1 && $plugin->params['twitter_count']==2){ $div="<span class='hikashop_social_google_right_fb_box'>";	}
		if($plugin->params['fb_style']==0 && $plugin->params['position']==1 && $plugin->params['twitter_count']==0){ $div="<span class='hikashop_social_google_right_tw_horizontal_fb_standart'>";	}
		if($plugin->params['fb_style']==1 && $plugin->params['position']==1 && $plugin->params['twitter_count']==0){ $div="<span class='hikashop_social_google_right_tw_vertical'>";	}
		if($plugin->params['fb_style']==2 && $plugin->params['position']==1 && $plugin->params['twitter_count']==0){ $div="<span class='hikashop_social_google_right_fb_box_tw_vert'>";	}
		if($plugin->params['fb_style']==0 && $plugin->params['position']==1 && $plugin->params['twitter_count']==1){ $div="<span class='hikashop_social_google_right_tw_none_fb_standart'>";	}
		if($plugin->params['fb_style']==1 && $plugin->params['position']==1 && $plugin->params['twitter_count']==1){ $div="<span class='hikashop_social_google_right_tw_none'>";	}
		if($plugin->params['fb_style']==2 && $plugin->params['position']==1 && $plugin->params['twitter_count']==1){ $div="<span class='hikashop_social_google_right_fb_box'>";	}
		if($plugin->params['fb_style']==0 && $plugin->params['position']==0 && $plugin->params['twitter_count']==0){ $div="<span class='hikashop_social_google_left_tw_none_fb_standart'>";	}
		if($plugin->params['fb_style']==2 && $plugin->params['position']==0 && $plugin->params['twitter_count']==1 && $plugin->params['google_size']==3){ $div="<span class='hikashop_social_google_left_tw_fb_box'>";	}
		$html=$div.'<g:plusone '.$size.' '.$count.'></g:plusone></span>';
		return $html;
	}
	function _addTwitterButton(&$plugin){
		if($plugin->params['display_fb']){
			if($plugin->params['fb_style']==0 && $plugin->params['position']==1 && $plugin->params['twitter_count']==2){ $div="<span class='hikashop_social_right_tw_none_fb_standart'>";	}
			if($plugin->params['fb_style']==1 && $plugin->params['position']==1 && $plugin->params['twitter_count']==2){ $div="<span class='hikashop_social_right_tw_none'>";	}
			if($plugin->params['fb_style']==2 && $plugin->params['position']==1 && $plugin->params['twitter_count']==2){ $div="<span class='hikashop_social_right_tw_none'>";	}
			if($plugin->params['fb_style']==0 && $plugin->params['position']==1 && $plugin->params['twitter_count']==0){ $div="<span class='hikashop_social_right_tw_horizontal_fb_standart'>";	}
			if($plugin->params['fb_style']==1 && $plugin->params['position']==1 && $plugin->params['twitter_count']==0){ $div="<span class='hikashop_social_right_tw_horizontal'>";	}
			if($plugin->params['fb_style']==2 && $plugin->params['position']==1 && $plugin->params['twitter_count']==0){ $div="<span class='hikashop_social_right_tw_none_fb_box'>";	}
			if($plugin->params['fb_style']==0 && $plugin->params['position']==1 && $plugin->params['twitter_count']==1){ $div="<span class='hikashop_social_right_tw_vertical_fb_standart'>";	}
			if($plugin->params['fb_style']==1 && $plugin->params['position']==1 && $plugin->params['twitter_count']==1){ $div="<span class='hikashop_social_right_tw_horizontal'>";	}
			if($plugin->params['fb_style']==2 && $plugin->params['position']==1 && $plugin->params['twitter_count']==1){ $div="<span class='hikashop_social_right_tw_horizontal'>";	}
			if($plugin->params['fb_style']==0 && $plugin->params['position']==0 && $plugin->params['twitter_count']==2){ $div="<span class='hikashop_social_left_tw_2'>"; }
			if($plugin->params['fb_style']==1 && $plugin->params['position']==0 && $plugin->params['twitter_count']==2){ $div="<span class='hikashop_social_left_tw'>";	}
			if($plugin->params['fb_style']==2 && $plugin->params['position']==0 && $plugin->params['twitter_count']==2){ $div="<span class='hikashop_social_left_tw'>"; }
			if($plugin->params['fb_style']==0 && $plugin->params['position']==0 && $plugin->params['twitter_count']==0 && !$plugin->params['display_google']){ $div="<span class='hikashop_social_left_tw_2'>"; }
			if($plugin->params['fb_style']==0 && $plugin->params['position']==0 && $plugin->params['twitter_count']==0 && $plugin->params['display_google']){ $div="<span class='hikashop_social_left_tw_2_google'>"; }
			if($plugin->params['fb_style']==1 && $plugin->params['position']==0 && $plugin->params['twitter_count']==0){ $div="<span class='hikashop_social_left_tw'>";	}
			if($plugin->params['fb_style']==2 && $plugin->params['position']==0 && $plugin->params['twitter_count']==0){ $div="<span class='hikashop_social_left_tw'>";	}
			if($plugin->params['fb_style']==0 && $plugin->params['position']==0 && $plugin->params['twitter_count']==1){ $div="<span class='hikashop_social_left_tw'>";	}
			if($plugin->params['fb_style']==1 && $plugin->params['position']==0 && $plugin->params['twitter_count']==1){ $div="<span class='hikashop_social_left_tw'>";	}
			if($plugin->params['fb_style']==2 && $plugin->params['position']==0 && $plugin->params['twitter_count']==1){ $div="<span class='hikashop_social_left_tw_box'>";	}
		}
		if($plugin->params['twitter_count']==0) $count='horizontal';
		if($plugin->params['twitter_count']==1) $count='vertical';
		if($plugin->params['twitter_count']==2) $count='none';
		$message='';
		if(!empty($plugin->params['twitter_text'])){
			$message='data-text="'.$plugin->params['twitter_text'].'"';
		}
		$mention='';
		if(!empty($plugin->params['twitter_mention'])){
			$mention='data-via="'.$plugin->params['twitter_mention'].'"';
		}
		$mainLang = &JFactory::getLanguage();
		$locale=strtolower(substr($mainLang->get('tag'),0,2));
		if($locale=='en') $lang='';
		else if($locale=='fr') $lang='data-lang="fr"';
		else if($locale=='de') $lang='data-lang="de"';
		else if($locale=='es') $lang='data-lang="es"';
		else if($locale=='it') $lang='data-lang="it"';
		else if($locale=='ja') $lang='data-lang="ja"';
		else if($locale=='ru') $lang='data-lang="ru"';
		else if($locale=='tr') $lang='data-lang="tr"';
		else if($locale=='ko') $lang='data-lang="ko"';
		else $lang='';
		if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
			$this->meta.='<script type="text/javascript">
				function twitterPop(str) {
					mywindow = window.open(\'http://twitter.com/share?url=\'+str,"Tweet_widow","channelmode=no,directories=no,location=no,menubar=no,scrollbars=no,toolbar=no,status=no,width=500,height=375,left=300,top=200");
					mywindow.focus();
				}
				</script>';
	 		$html=$div;
			$html.='<a href="javascript:twitterPop(\''.hikashop_currentURL().'\')"><img src="'.HIKASHOP_IMAGES.'icons/tweet_button.jpg"></a></span>';
			return $html;
		}
		if(!isset($div)) $div='<span>';
		$html=$div;
		$html.='<a href="http'.$this->https.'://twitter.com/share" class="twitter-share-button" '.$message.' data-count="'.$count.'" '.$mention.' '.$lang.'>Tweet</a>
				<script type="text/javascript" src="http'.$this->https.'://platform.twitter.com/widgets.js"></script></span>';
		return $html;
	}
	function _addFacebookButton( &$plugin){
		$options='';
		if($plugin->params['fb_style']==0){ $options='layout=standard&amp;'; $options.='width=400&amp;'; }
		if($plugin->params['fb_style']==1){ $options='layout=button_count&amp;'; $options.='width=115&amp;'; }
		if($plugin->params['fb_style']==2){ $options='layout=box_count&amp;'; $options.='width=115&amp;'; }
		if($plugin->params['fb_faces']==0) $options.='show_faces=false&amp;';
		else $options.='show_faces=true&amp;';
		if($plugin->params['fb_verb']==0) $options.='action=like&amp;';
		else $options.='action=recommend&amp;';
		if($plugin->params['fb_theme']==0) $options.='colorscheme=light&amp;';
		else $options.='colorscheme=dark&amp;';
		if($plugin->params['fb_font']==0) $options.='font=arial&amp;';
		if($plugin->params['fb_font']==1) $options.='font=lucida%2Bgrande&amp;';
		if($plugin->params['fb_font']==2) $options.='font=segoe%2Bui&amp;';
		if($plugin->params['fb_font']==3) $options.='font=tahoma&amp;';
		if($plugin->params['fb_font']==4) $options.='font=trebuchet%2Bms&amp;';
		if($plugin->params['fb_font']==5) $options.='font=verdana&amp;';
		$url=urlencode(hikashop_currentURL());
		if($plugin->params['fb_style']==0){	$sizes='class="hikashop_social_fb_standard"';	}
		if($plugin->params['fb_style']==1){	$sizes='class="hikashop_social_fb_button_count"';	}
		if($plugin->params['fb_style']==2){	$sizes='class="hikashop_social_fb_box_count"';	}
		if($plugin->params['display_twitter']){
			if($plugin->params['twitter_count']==0 && $plugin->params['position']==0){	$div='<span class="hikashop_social_left_fb_tw_horizontal" >';	}
			if($plugin->params['twitter_count']==1 && $plugin->params['position']==0){	$div='<span class="hikashop_social_left_fb_tw_vertical" >';	}
			if($plugin->params['twitter_count']==2 && $plugin->params['position']==0){	$div='<span class="hikashop_social_left_fb_tw_none" >';	}
			if($plugin->params['position']==0 && $plugin->params['fb_style']==0 && !$plugin->params['display_google']){ $div='<span class="hikashop_social_left_fb__standart_tw_none" >'; }
			if($plugin->params['position']==0 && $plugin->params['fb_style']==0 && $plugin->params['display_google']){ $div='<span class="hikashop_social_left_fb__standart_tw_none_google" >'; }
		}
		if(isset($div)){ $html=$div; }
		else{ $html='';}
		$html.='<iframe
					src="http'.$this->https.'://www.facebook.com/plugins/like.php?href='.$url.'&amp;send=false&amp;'.$options.'height=30"
					scrolling="no"
					frameborder="0"
					style="border:none; overflow:hidden;" '.$sizes.'
					allowTransparency="true">
				</iframe>';
		if(isset($div)) $html.='</span>';
		$db = &JFactory::getDBO();
		$product_id = (int)hikashop_getCID('product_id');
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
		$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_access=\'all\' AND product_published=1 AND product_type=\'main\' AND product_id='.$product_id;
		$db->setQuery($query);
		$product = $db->loadObject();
		$meta='<meta property="og:title" content="'.$product->product_name.'"/> ';
		if($plugin->params['fb_type']==0) $meta.='<meta property="og:type" content="product"/> ';
		if($plugin->params['fb_type']==1) $meta.='<meta property="og:type" content="album"/> ';
		if($plugin->params['fb_type']==2) $meta.='<meta property="og:type" content="book"/> ';
		if($plugin->params['fb_type']==3) $meta.='<meta property="og:type" content="company"/> ';
		if($plugin->params['fb_type']==4) $meta.='<meta property="og:type" content="drink"/> ';
		if($plugin->params['fb_type']==5) $meta.='<meta property="og:type" content="game"/> ';
		if($plugin->params['fb_type']==6) $meta.='<meta property="og:type" content="movie"/> ';
		if($plugin->params['fb_type']==7) $meta.='<meta property="og:type" content="song"/> ';
		$config =& hikashop_config();
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
		$queryImage = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id='.$product_id.'  AND file_type=\'product\' ORDER BY file_id ASC';
	    $db->setQuery($queryImage);
	    $image = $db->loadObject();
		if(!empty($image)){
			$meta.='<meta property="og:image" content="'.JURI::base().$this->main_uploadFolder_url.$image->file_path.'" /> ';
	    }
		$meta.='<meta property="og:url" content="'.hikashop_currentURL().'" />';
		$conf	=& JFactory::getConfig();
		$siteName=$conf->getValue('config.sitename');
		$meta.='<meta property="og:site_name" content="'.$siteName.'"/> ';
		if(!empty($plugin->params['admin'])){ $meta.='<meta property="fb:admins" content="'.$plugin->params['admin'].'" />'; }
		else{
			return $html;
		}
		$meta.='<meta property="og:description" content="'.strip_tags($product->product_description).'"/> ';
		$this->meta.=$meta;
		return $html;
	}
}