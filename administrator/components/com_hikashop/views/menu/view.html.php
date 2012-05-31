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
class MenuViewMenu extends JView{
	function display($tpl = null,$title){
		$this->assignRef('title',$title);
		$doc =& JFactory::getDocument();
		$doc->addScript(HIKASHOP_JS.'menu.js');
		$doc->addStyleSheet(HIKASHOP_CSS.'menu.css');
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		$menus = array();
		$config = null;
		$config->name = JText::_('HIKA_CONFIGURATION');
		$config->check='ctrl=config';
		$config->acl='config';
		$config->task='manage';
		$config->url = hikashop_completeLink('config');
		$zones = null;
		$zones->name = JText::_('ZONES');
		$zones->check='ctrl=zone';
		$zones->acl='zone';
		$zones->url = hikashop_completeLink('zone');
		$payments = null;
		$payments->name = JText::_('PAYMENT_METHODS');
		$payments->check='ctrl=plugins&plugin_type=payment';
		$payments->acl='plugins';
		$payments->url = hikashop_completeLink('plugins&plugin_type=payment');
		$shippings = null;
		$shippings->name = JText::_('SHIPPING_METHODS');
		$shippings->check='ctrl=plugins&plugin_type=shipping';
		$shippings->acl='plugins';
		$shippings->url = hikashop_completeLink('plugins&plugin_type=shipping');
		$taxes = null;
		$taxes->name = JText::_('TAXES');
		$taxes->check='ctrl=taxation';
		$taxes->acl='taxation';
		$taxes->url = hikashop_completeLink('taxation');
		$currencies = null;
		$currencies->name = JText::_('CURRENCIES');
		$currencies->check='ctrl=currency';
		$currencies->acl='currency';
		$currencies->url = hikashop_completeLink('currency');
		$discounts = null;
		$discounts->name = JText::_('DISCOUNTS');
		$discounts->check='ctrl=discount';
		$discounts->acl='discount';
		$discounts->url = hikashop_completeLink('discount');
		$status = null;
		$status->name = JText::_('ORDER_STATUSES');
		$status->check='ctrl=category&filter_id=status';
		$status->acl='config';
		$status->url = hikashop_completeLink('category&filter_id=status');
		$import = null;
		$import->name = JText::_('IMPORT');
		$import->check='ctrl=import';
		$import->acl='import';
		$import->url = hikashop_completeLink('import&task=show');
		$system = null;
		$system->name = JText::_('SYSTEM');
		$system->check='ctrl=config';
		$system->acl='config';
		$system->task='manage';
		$system->url = hikashop_completeLink('config');
		$system->children = array(
			$config,
			$zones,
			$payments,
			$shippings,
			$taxes,
			$currencies,
			$discounts,
			$status
		);
		if(hikashop_level(2)){
			$emails = null;
			$emails->name = JText::_('EMAILS');
			$emails->check='ctrl=email';
			$emails->acl='email';
			$emails->url = hikashop_completeLink('email');
			$system->children[]=$emails;
		}
		$menus[]=$system;
		$product = null;
		$product->name = JText::_('ADD_PRODUCT');
		$product->check='ctrl=product&task=add';
		$product->acl='product';
		$product->task='manage';
		$product->url = hikashop_completeLink('product&task=add');
		$categories = null;
		$categories->name = JText::_('HIKA_CATEGORIES');
		$categories->check='ctrl=category&filter_id=product';
		$categories->acl='category';
		$categories->url = hikashop_completeLink('category&filter_id=product');
		$characteristics = null;
		$characteristics->name = JText::_('CHARACTERISTICS');
		$characteristics->check='ctrl=characteristic';
		$characteristics->acl='characteristic';
		$characteristics->url = hikashop_completeLink('characteristic');
		$manufacturers = null;
		$manufacturers->name = JText::_('MANUFACTURERS');
		$manufacturers->check='ctrl=category&filter_id=manufacturer';
		$categories->acl='category';
		$manufacturers->url = hikashop_completeLink('category&filter_id=manufacturer');
		$products = null;
		$products->name = JText::_('PRODUCTS');
		$products->check='ctrl=product';
		$products->acl='product';
		$products->url = hikashop_completeLink('product');
		$products->children = array(
			$product,
			&$products,
			$categories,
			$characteristics,
			$manufacturers,
			$import
		);
		$menus[]=$products;



		$users = null;
		$users->name = JText::_('CUSTOMERS');
		$users->check='ctrl=user&filter_partner=0';
		$users->acl='user';
		$users->url = hikashop_completeLink('user&filter_partner=0');
		$users->children = array(
			$users,
		);
		$menus[]=$users;

		$sales = null;
		$sales->name = JText::_('SALES');
		$sales->check='ctrl=order&order_type=sale&filter_partner=0';
		$sales->acl='order';
		$sales->url = hikashop_completeLink('order&order_type=sale&filter_partner=0');
		$sales->children = array(
			$sales,
		);
		if(hikashop_level(2)){
			$entries = null;
			$entries->name = JText::_('HIKASHOP_ENTRIES');
			$entries->check='ctrl=entry';
			$entries->acl='entry';
			$entries->url = hikashop_completeLink('entry');
			$sales->children[]=$entries;
		}
		$menus[]=$sales;
		$plugin =& JPluginHelper::getPlugin('system', 'hikashopaffiliate');
		if(!empty($plugin) && hikashop_level(2)){
			$banners = null;
			$banners->name = JText::_('HIKA_BANNERS');
			$banners->check='ctrl=banner';
			$banners->acl='banner';
			$banners->url = hikashop_completeLink('banner');
			$affiliates_sales = null;
			$affiliates_sales->name = JText::_('AFFILIATES_SALES');
			$affiliates_sales->check='ctrl=order&order_type=sale&filter_partner=1';
			$affiliates_sales->acl='order';
			$affiliates_sales->url = hikashop_completeLink('order&order_type=sale&filter_partner=1');
			$clicks=null;
			$clicks->name = JText::_('CLICKS');
			$clicks->check='ctrl=user&task=clicks';
			$clicks->acl='order';
			$clicks->url = hikashop_completeLink('user&task=clicks');
			$affiliates = null;
			$affiliates->name = JText::_('AFFILIATES');
			$affiliates->check='ctrl=user&filter_partner=1';
			$affiliates->acl='user';
			$affiliates->url = hikashop_completeLink('user&filter_partner=1');
			$partners = null;
			$partners->name = JText::_('PARTNERS');
			$partners->check='ctrl=user&filter_partner=1';
			$partners->acl='user';
			$partners->url = hikashop_completeLink('user&filter_partner=1');
			$affiliates->children = array(
				$partners,
				$banners,
				$clicks,
				$affiliates_sales
			);
			$menus[]=$affiliates;
		}
		$view = null;
		$view->name = JText::_('VIEWS');
		$view->check='ctrl=view';
		$view->acl='view';
		$view->url = hikashop_completeLink('view');
		$menu = null;
		$menu->name = JText::_('CONTENT_MENUS');
		$menu->check='ctrl=menus';
		$menu->acl='menus';
		$menu->url = hikashop_completeLink('menus');
		$modules = null;
		$modules->name = JText::_('CONTENT_MODULES');
		$modules->check='ctrl=modules';
		$modules->acl='modules';
		$modules->url = hikashop_completeLink('modules');
		$fields = null;
		$fields->name = JText::_('FIELDS');
		$fields->check='ctrl=field';
		$fields->acl='field';
		$fields->url = hikashop_completeLink('field');
		$display = null;
		$display->name = JText::_('DISPLAY');
		$display->check='ctrl=view';
		$display->url = hikashop_completeLink('view');
		$display->acl='view';
		$display->children = array(
			$view,
			$menu,
			$modules,
			$fields
		);
		if(hikashop_level(2)){
			$filters = null;
			$filters->name = JText::_('FILTERS');
			$filters->check='ctrl=filter';
			$filters->acl='filter';
			$filters->url = hikashop_completeLink('filter');
			$display->children[]=$filters;
		}
		$menus[]=$display;
		$documentation = null;
		$documentation->name = JText::_('DOCUMENTATION');
		$documentation->check='ctrl=documentation';
		$documentation->url = hikashop_completeLink('documentation');
		$update = null;
		$update->name = JText::_('UPDATE_ABOUT');
		$update->check='ctrl=update';
		$update->url = hikashop_completeLink('update');
		$forum = null;
		$forum->name = JText::_('FORUM');
		$forum->check='support/forum.html';
		$forum->options='target="_blank"';
		$forum->url = HIKASHOP_URL.'support/forum.html';
		$help = null;
		$help->name = JText::_('HIKA_HELP');
		$help->check='ctrl=documentation';
		$help->url = hikashop_completeLink('documentation');
		$help->children = array(
			$documentation,
			$update,
			$forum
		);
		$menus[]=$help;
		$this->_checkActive($menus);
		$this->assignRef('menus',$menus);
		parent::display($tpl);
	}
	function _checkActive(&$menus,$level=0){
		if($level<2){
			foreach($menus as $k => $menu){
				if(strpos($_SERVER['QUERY_STRING'],$menu->check)!==false){
					if(strpos($_SERVER['QUERY_STRING'],'&task=')===false || strpos($menu->check,'&task=')!==false){
						$menus[$k]->active = true;
					}
				}
				if(!empty($menu->children)){
					$this->_checkActive($menus[$k]->children,$level+1);
				}
			}
		}
	}
}