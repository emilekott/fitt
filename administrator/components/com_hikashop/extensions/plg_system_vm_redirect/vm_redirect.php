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
class plgSystemVm_redirect extends JPlugin {
	function plgSystemVm_redirect(&$subject, $config) {
		parent::__construct($subject, $config);
	}
	function onAfterRoute() {
		$app =& JFactory::getApplication();
		if( JRequest::getString('option') != 'com_virtuemart' || $app->isAdmin() )  return true;
		$vmProdId = (int)JRequest::getVar('product_id');
		$vmCatId = (int)JRequest::getVar('category_id');
		$vmOrderId= JRequest::getInt('order_id');
		$url = null; //HIKASHOP_LIVE;
		$db =& JFactory::getDBO();
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
		$query='SHOW TABLES LIKE '.$db->Quote($db->getPrefix().substr(hikashop_table('vm_prod'),3));
		$db->setQuery($query);
		$table = $db->loadResult();
		if(empty($table)) return true;
		if( !empty($vmProdId) && $vmProdId > 0 ) {
			$query = "SELECT a.hk_id, b.product_name as 'name' FROM `#__hikashop_vm_prod` a INNER JOIN `#__hikashop_product` b ON a.hk_id = b.product_id WHERE a.vm_id = " . $vmProdId . ";";
			$baseUrl = 'product&task=show';
		} else if( !empty($vmCatId)  && $vmCatId > 0 ) {
			$id = 'vm-fallback';
			$alias = 'hikashop-menu-for-module-'.$id;
			$db->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$alias.'\'');
			$itemId = $db->loadResult();
			if(empty($itemId)) {
				$options = null;
				$config =& hikashop_config();
				$options->hikashop_params = $config->get('default_params',null);
				$classMenu = hikashop_get('class.menus');
				$classMenu->loadParams($options);
				$options->hikashop_params['content_type'] = 'category';
				$options->hikashop_params['layout_type']='div';
				$options->hikashop_params['content_synchronize']='1';
				if($options->hikashop_params['columns']==1){
					$options->hikashop_params['columns']=3;
				}
				$classMenu->createMenu($options->hikashop_params, $id);
				$itemId = $options->hikashop_params['itemid'];
			}
			$query = "SELECT a.hk_id, b.category_name as 'name' FROM `#__hikashop_vm_cat` a INNER JOIN `#__hikashop_category` b ON a.hk_id = b.category_id WHERE a.vm_id = " . $vmCatId . ";";
			$baseUrl = 'category&task=listing&ItemId='.$itemId;
		}elseif(!empty($vmOrderId)){
			$db->setQuery('SELECT order_id FROM '.hikashop_table('order').' WHERE order_vm_id='.$vmOrderId);
			$hikaOrderId = $db->loadResult();
			if(!empty($hikaOrderId)){
				$url = hikashop_completeLink('order&task=show&cid='.$hikaOrderId, false, true);
				$app->redirect($url);
				return true;
			}
		}
		if( !empty($query) && !empty($baseUrl) ) {
			$db->setQuery($query);
			$link = $db->loadObject();
			if( $link ) {
				if(method_exists($app,'stringURLSafe')) {
					$name = $app->stringURLSafe(strip_tags($link->name));
				} else {
					$name = JFilterOutput::stringURLSafe(strip_tags($link->name));
				}
				$url = hikashop_completeLink($baseUrl.'&cid='.$link->hk_id.'&name='.$name, false, true);
			}
		}
		if( $url )
			$app->redirect($url,'','message',true);
	}
}