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
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
	echo 'This module can not work without the Hikashop Component';
	return;
};
$js ='';
hikashop_initModule();
$config = hikashop_config();
$module_options = $config->get('params_'.$module->id);
if(empty($module_options)){
	$module_options = $config->get('default_params');
}
foreach($module_options as $key => $option){
	if($key !='moduleclass_sfx'){
		$params->set($key,$option);
	}
}
foreach(get_object_vars($module) as $k => $v){
	if(!is_object($v)){
		$params->set($k,$v);
	}
}
$html = trim(hikashop_getLayout('product','cart',$params,$js));
if(!empty($html)){
?>
<div class="hikashop_cart_module" id="hikashop_cart_module">
<?php echo $html; ?>
</div>
<?php }