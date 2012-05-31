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
class hikashopShippingClass extends hikashopClass{
	var $tables = array('shipping');
	var $pkeys = array('shipping_id');
	var $deleteToggle = array('shipping'=>array('shipping_type','shipping_id'));
	var $toggle = array('shipping_published'=>'shipping_id');
	function save($element,$reorder=true){
		$status = parent::save($element);
		if($status && empty($element->shipping_id)){
			$element->shipping_id = $status;
			if($reorder){
				$orderClass = hikashop_get('helper.order');
				$orderClass->pkey = 'shipping_id';
				$orderClass->table = 'shipping';
				$orderClass->groupMap = 'shipping_type';
				$orderClass->groupVal = $element->shipping_type;
				$orderClass->orderingMap = 'shipping_ordering';
				$orderClass->reOrder();
			}
		}
		return $status;
	}
	function delete($elements){
		$status = parent::delete($elements);
		if($status){
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = 'shipping_id';
			$orderClass->table = 'shipping';
			$orderClass->groupMap = 'shipping_type';
			$orderClass->orderingMap = 'shipping_ordering';
			$app =& JFactory::getApplication();
			$orderClass->groupVal = $app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.shipping_plugin_type','shipping_plugin_type','manual');
			$orderClass->reOrder();
		}
		return $status;
	}
	function getMethods(&$order){
		$pluginClass = hikashop_get('class.plugins');
		$rates = $pluginClass->getMethods('shipping');
    	if(bccomp($order->total->prices[0]->price_value,0,5) && !empty($rates)){
			$currencyClass = hikashop_get('class.currency');
			$currencyClass->convertShippings($rates);
    	}
    	return $rates;
	}
	function & getShippings(&$order,$reset=false){
		static $usable_methods = null;
		static $errors = array();
		if($reset){
			$usable_methods = null;
			$errors = array();
		}
		if(is_null($usable_methods)){
			JPluginHelper::importPlugin( 'hikashopshipping' );
			$dispatcher =& JDispatcher::getInstance();
			$rates = $this->getMethods($order);
	    	$usable_methods = array();
			$dispatcher->trigger( 'onShippingDisplay', array( & $order,&$rates, &$usable_methods,&$errors ) );
		}
		$this->errors = $errors;
		return $usable_methods;
	}
}