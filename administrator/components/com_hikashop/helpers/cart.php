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
class hikashopCartHelper{
	function hikashopCartHelper(){
		static $done = false;
		static $override = false;
		if(!$done){
			$done = true;
			$app =& JFactory::getApplication();
			$chromePath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'hikashop_button.php';
			if (file_exists($chromePath)){
				require_once ($chromePath);
				$override = true;
			}
		}
		$this->override = $override;
	}
	function displayButton($name,$map,&$params,$url='',$ajax="",$options="",$max_quantity=0,$min_quantity=1,$classname=''){
		$config =& hikashop_config();
		$button = $config->get('button_style','normal');
		static $i=0;
		$i++;
		if(!empty($ajax)){
			$ajax = 'onclick="var field=document.getElementById(\'hikashop_product_quantity_field_'.$i.'\');'.$ajax.'" ';
		}
		if($this->override && function_exists('hikashop_button_render')){
			$html = hikashop_button_render($map,$name,$ajax,$options,$url,$classname);
		}else{
			switch($button){
				case 'rounded': //deprecated
					$params->set('main_div_name', 'hikashop_button_'.$i);
					$moduleHelper = hikashop_get('helper.module');
					$moduleHelper->setCSS($params);
					$url = 'href="'.$url.'" ';
					$html='
					<div id="'.$params->get('main_div_name').'">
					<div class="hikashop_container">
					<div class="hikashop_subcontainer">
					<a class="hikashop_cart_rounded_button'.$classname.'" '.$url.$ajax.$options.'>'.$name.'</a>
					</div>
					</div>
					</div>
					';
					break;
				case 'css':
					$url = 'href="'.$url.'" ';
					$html= '<a class="hikashop_cart_button'.$classname.'" '.$options.' '.$url.$ajax.'>'.$name.'</a>';
					break;
				case 'normal':
				default:
					$html= '<input type="submit" class="button hikashop_cart_input_button'.$classname.'" name="'.$map.'" value="'.$name.'" '.$ajax.$options.'/>';
					break;
			}
		}
		if($map=='add'){
			if($params->get('show_quantity_field',0)==1){
				$max_quantity=(int)$max_quantity;
				$min_quantity=(int)$min_quantity;
				static $first = false;
				if(!$first){
					$first=true;
					$js = '
					function hikashopQuantityChange(field,plus,max,min){
						var fieldEl=document.getElementById(field);
						var current = fieldEl.value;
						current = parseInt(current);
						if(plus){
							if(max==0 || current<max){
								fieldEl.value=parseInt(fieldEl.value)+1;
							}else if(max && current==max){
								alert(\''.JText::_('NOT_ENOUGH_STOCK',true).'\');
							}
						}else{
							if(current>1 && current>min){
								fieldEl.value=current-1;
							}
						}
						return false;
					}
					function hikashopCheckQuantityChange(field,max,min){
						var fieldEl=document.getElementById(field);
						var current = fieldEl.value;
						current = parseInt(current);
						if(max && current>max){
							fieldEl.value=max;
							alert(\''.JText::_('NOT_ENOUGH_STOCK',true).'\');
						}else if(current<min){
							fieldEl.value=min;
						}
						return false;
					}
					';
					$doc =& JFactory::getDocument();
					$doc->addScriptDeclaration("<!--\n".$js."\n//-->");
				}
				if($this->override && function_exists('hikashop_quantity_render')){
					$html = hikashop_quantity_render($html,$i,$max_quantity,$min_quantity);
				}else{
					$html ='
					<table>
						<tr>
							<td rowspan="2">
								<input id="hikashop_product_quantity_field_'.$i.'" type="text" value="'.JRequest::getInt('quantity',$min_quantity).'" class="hikashop_product_quantity_field" name="quantity" onchange="hikashopCheckQuantityChange(\'hikashop_product_quantity_field_'.$i.'\','.$max_quantity.','.$min_quantity.');" />
							</td>
							<td>
								<a id="hikashop_product_quantity_field_change_plus_'.$i.'" class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange(\'hikashop_product_quantity_field_'.$i.'\',1,'.$max_quantity.','.$min_quantity.');">+</a>
							</td>
							<td rowspan="2">
								'.$html.'
							</td>
						</tr>
						<tr>
							<td>
								<a id="hikashop_product_quantity_field_change_minus_'.$i.'" class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange(\'hikashop_product_quantity_field_'.$i.'\',0,'.$max_quantity.','.$min_quantity.');">-</a>
							</td>
						</tr>
					</table>
					';
				}
			}elseif($params->get('show_quantity_field',0)!=-1){
				$html.='<input id="hikashop_product_quantity_field_'.$i.'" type="hidden" value="'.$min_quantity.'" class="hikashop_product_quantity_field" name="quantity" />';
			}else{
				static $second = false;
				if(!$second){
					$second=true;
					$js = '
					function hikashopQuantityChange(field,plus,max,min){
						var fieldEl=document.getElementById(field);
						var current = fieldEl.value;
						current = parseInt(current);
						if(plus){
							if(max==0 || current<max){
								fieldEl.value=parseInt(fieldEl.value)+1;
							}else if(max && current==max){
								alert(\''.JText::_('NOT_ENOUGH_STOCK',true).'\');
							}
						}else{
							if(current>1 && current>min){
								fieldEl.value=current-1;
							}
						}
						return false;
					}
					';
					$doc =& JFactory::getDocument();
					$doc->addScriptDeclaration("<!--\n".$js."\n//-->");
				}
				$html = '<input id="hikashop_product_quantity_field_'.$i.'" type="text" value="'.JRequest::getInt('quantity',$min_quantity).'" class="hikashop_product_quantity_field" name="quantity" onchange="hikashopCheckQuantityChange(\'hikashop_product_quantity_field_'.$i.'\','.$max_quantity.','.$min_quantity.');" />'.$html;
			}
		}
		return $html;
	}
	function cartCount($add=false){
		static $carts = 0;
		if($add){
			$carts=$carts+1;
		}
		return $carts;
	}
	function getJS($url,$needNotice=true){
				static $first = true;
				if($first){
					$config =& hikashop_config();
					$redirect = $config->get('redirect_url_after_add_cart','stay_if_cart');
					global $Itemid;
					$url_itemid='';
					if(!empty($Itemid)){
						$url_itemid='&Itemid='.$Itemid;
					}
					$baseUrl = hikashop_completeLink('product&task=updatecart',true,true);
					if(strpos($baseUrl,'?')!==false){
						$baseUrl.='&';
					}else{
						$baseUrl.='?';
					}
					if($redirect=='ask_user'){
						JHTML::_('behavior.modal');
						if($needNotice && JRequest::getVar('tmpl','')!='component'){
							if($this->override && function_exists('hikashop_popup_render')){
								echo hikashop_popup_render();
							}else{
								echo '<div style="display:none;"><a rel="{handler: \'iframe\',size: {x: 480, y: 140}}" id="hikashop_notice_box_trigger_link" href="'.hikashop_completeLink('checkout&task=notice'.$url_itemid,true).'"></a></div>';
							}
						}
						if($this->override && function_exists('hikashop_popup_js_render')){
								$js = hikashop_popup_js_render($url);
						}else{
							$js = '
							function hikashopModifyQuantity(id,obj,add,form){
								if(add){
									add=\'&add=1\';
								}else{
									add=\'\';
								}
								var qty=1;
								if(obj){
									qty=parseInt(obj.value);
								}
								if(form){
									var varform = eval(\'document.\'+form);
									if(varform){
										varform.submit();
									}
								}else{
									if(qty){
										SqueezeBox.fromElement(\'hikashop_notice_box_trigger_link\',{parse: \'rel\'});
									}
									try{
										new Ajax(\''.$baseUrl.'product_id=\'+id+\'&quantity=\'+qty+add+\''.$url_itemid.'&return_url='.urlencode(base64_encode(urldecode($url))).'\',  { method: \'get\', onComplete: function(result) { var hikaModule = window.document.getElementById(\'hikashop_cart_module\'); if(hikaModule) hikaModule.innerHTML = result;}}).request();
									}catch(err){
										new Request({url:\''.$baseUrl.'product_id=\'+id+\'&quantity=\'+qty+add+\''.$url_itemid.'&return_url='.urlencode(base64_encode(urldecode($url))).'\', method: \'get\', onComplete: function(result) { var hikaModule = window.document.getElementById(\'hikashop_cart_module\'); if(hikaModule) hikaModule.innerHTML = result;}}).send();
									}
								}
								return false;
							}
							';
						}
					}else{
						if($this->override && function_exists('hikashop_cart_js_render')){
							$js = hikashop_cart_js_render($url);
						}else{
							$js='';
							if($this->cartCount()!=1 && !empty($url)){
								$js = 'window.location = \''.urldecode($url).'\';';
							}
							$js = '
							function hikashopModifyQuantity(id,obj,add,form){
								if(add){
									add=\'&add=1\';
								}else{
									add=\'\';
								}
								var qty=1;
								if(obj){
									qty=parseInt(obj.value);
								}
								if(form){
									var varform = eval(\'document.\'+form);
									if(varform){
										varform.submit();
									}
								}else{
									try{
										new Ajax(\''.$baseUrl.'product_id=\'+id+\'&quantity=\'+qty+add+\''.$url_itemid.'&return_url='.urlencode(base64_encode(urldecode($url))).'\',  { method: \'get\', onComplete: function(result) { var hikaModule = window.document.getElementById(\'hikashop_cart_module\'); if(hikaModule){ hikaModule.innerHTML = result;} '.$js.'}}).request();
									}catch(err){
										new Request({url:\''.$baseUrl.'product_id=\'+id+\'&quantity=\'+qty+add+\''.$url_itemid.'&return_url='.urlencode(base64_encode(urldecode($url))).'\', method: \'get\', onComplete: function(result) { var hikaModule = window.document.getElementById(\'hikashop_cart_module\'); if(hikaModule){ hikaModule.innerHTML = result;} '.$js.'}}).send();
									}
								}
								return false;
							}
							';
						}
						JHTML::_('behavior.mootools');
					}
					$doc =& JFactory::getDocument();
					$doc->addScriptDeclaration("<!--\n".$js."\n//-->");
					$first = !$needNotice;
				}
	}
}