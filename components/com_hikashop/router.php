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
function HikashopBuildRoute( &$query )
{
	$segments = array();
	if(function_exists('hikashop_config') || include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
		$config =& hikashop_config();
		if($config->get('activate_sef',1)){
			$categorySef=$config->get('category_sef_name','category');
			$productSef=$config->get('product_sef_name','product');
			if(empty($categorySef)){
				$categorySef='';
			}
			if(empty($productSef)){
				$productSef='';
			}
			if(isset($query['ctrl']) && isset($query['task'])){
				if($query['ctrl']=='category' && $query['task']=='listing'){
					$segments[] = $categorySef;
					unset( $query['ctrl'] );
					unset( $query['task'] );
				}
				else if($query['ctrl']=='product' && $query['task']=='show'){
					$segments[] = $productSef;
					unset( $query['ctrl'] );
					unset( $query['task'] );
				}
			}
			else if(isset($query['view']) && isset($query['layout'])){
				if($query['view']=='category' && $query['layout']=='listing'){
					$segments[] = $categorySef;
					unset( $query['layout'] );
					unset( $query['view'] );
				}
				else if($query['view']=='product' && $query['layout']=='show'){
					$segments[] = $productSef;
					unset( $query['layout'] );
					unset( $query['view'] );
				}
			}
		}
	}
	if (isset($query['ctrl'])) {
		$segments[] = $query['ctrl'];
		unset( $query['ctrl'] );
		if (isset($query['task'])) {
			$segments[] = $query['task'];
			unset( $query['task'] );
		}
	}elseif(isset($query['view'])){
		$segments[] = $query['view'];
		unset( $query['view'] );
		if(isset($query['layout'])){
			$segments[] = $query['layout'];
			unset( $query['layout'] );
		}
	}
	if(isset($query['category_pathway'])&& empty($query['category_pathway'])){
		unset( $query['category_pathway'] );
	}
	if(isset($query['product_id'])){
		$query['cid'] = $query['product_id'];
		unset($query['product_id']);
	}
	if(isset($query['cid']) && isset($query['name'])){
		if(is_numeric($query['name'])){
			$query['name']=$query['name'].'-';
		}
		$segments[] = $query['cid'].':'.$query['name'];
		unset($query['cid']);
		unset($query['name']);
	}
	if(!empty($query)){
		foreach($query as $name => $value){
			if(!in_array($name,array('option','Itemid','start','format','limitstart'))){
					$segments[] = $name.':'.$value;
				unset($query[$name]);
			}
		}
	}
	return $segments;
}
function HikashopParseRoute( $segments )
{
	$vars = array();
	$check=false;
	if(!empty($segments)){
		if(function_exists('hikashop_config') || include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			$config =& hikashop_config();
			if($config->get('activate_sef',1)){
				$categorySef=$config->get('category_sef_name','category');
				$productSef=$config->get('product_sef_name','product');
				$skip=false;
				if(isset($segments[0])){
					$file = HIKASHOP_CONTROLLER.$segments[0].'.php';
					if(file_exists($file) && isset($segments[1])){
						if(!($segments[0]=='product'&&$segments[1]=='show' || $segments[0]=='category'&&$segments[1]=='listing')){
							$controller = hikashop_get('controller.'.$segments[0],array(),true);
							if($controller->isIn($segments[1],array('display','modify_views','add','modify','delete'))){
								$skip = true;
							}
						}
					}
				}
				if(!$skip){
					$i = 0;
					foreach($segments as $name){
						if(strpos($name,':')){
							if(empty($productSef) && !$check){
								$vars['ctrl']='product';
								$vars['task']='show';
							}
							list($arg,$val) = explode(':',$name,2);
							if(is_numeric($arg) && !is_numeric($val)){
								$vars['cid'] = $arg;
								$vars['name'] = $val;
							}elseif(is_numeric($arg)) $vars['Itemid'] = $arg;
							else $vars[$arg] = $val;
						}else if($name==$productSef){
							$vars['ctrl']='product';
							$vars['task']='show';
						}else if($name==$categorySef){
							$vars['ctrl']='category';
							$vars['task']='listing';
							$check=true;
						}else{
							$i++;
							if($i == 1) $vars['ctrl'] = $name;
							elseif($i == 2) $vars['task'] = $name;
							$check=true;
						}
					}
					return $vars;
				}
			}
		}
		$i = 0;
		foreach($segments as $name){
			if(strpos($name,':')){
				list($arg,$val) = explode(':',$name,2);
				if(is_numeric($arg) && !is_numeric($val)){
					$vars['cid'] = $arg;
					$vars['name'] = $val;
				}elseif(is_numeric($arg)) $vars['Itemid'] = $arg;
				else $vars[$arg] = $val;
			}else{
				$i++;
				if($i == 1) $vars['ctrl'] = $name;
				elseif($i == 2) $vars['task'] = $name;
			}
		}
	}
	return $vars;
}