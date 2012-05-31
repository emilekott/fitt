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
class plgHikashoppaymentCollectondelivery extends JPlugin
{
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='collectondelivery' || !$method->enabled){
					continue;
				}
				if(!empty($method->payment_zone_namekey)){
					$zoneClass=hikashop_get('class.zone');
	    			$zones = $zoneClass->getOrderZones($order);
					if(!in_array($method->payment_zone_namekey,$zones)){
						return true;
					}
				}

				$usable_methods[$method->ordering]=$method;
    		}
    	}
    	return true;
    }
    function onPaymentSave(&$cart,&$rates,&$payment_id){
    	$usable = array();
    	$this->onPaymentDisplay($cart,$rates,$usable);
    	$payment_id = (int) $payment_id;
    	foreach($usable as $usable_method){
    		if($usable_method->payment_id==$payment_id){
    			return $usable_method;
    		}
    	}
    	return false;
    }
    function onPaymentConfiguration(&$element){
    	$this->collectondelivery = JRequest::getCmd('name','collectondelivery');
    	if(empty($element)){
    		$element = null;
    		$element->payment_name='Collect on delivery';
    		$element->payment_description='You can pay when your package is delivered by using this payment method.';
    		$element->payment_images='Collect_on_delivery';
    		$element->payment_type=$this->collectondelivery;
    		$element->payment_params=null;
    		$element->payment_params->order_status='created';
    		$element = array($element);
    	}
    	$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','payment-collectondelivery-form');
		hikashop_setTitle(JText::_('COLLECT_ON_DELIVERY'),'plugin','plugins&plugin_type=payment&task=edit&name='.$this->collectondelivery);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->collectondelivery);
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
    }
	function onPaymentConfigurationSave(&$element){
    	return true;
    }
    function onAfterOrderConfirm(&$order,&$methods,$method_id){
    	$method =& $methods[$method_id];
    	$orderObj = null;
    	$orderObj->order_status = $method->payment_params->order_status;
    	$orderObj->history->history_notified = @$method->payment_params->status_notif_email;
    	$orderObj->order_id = $order->order_id;
    	$orderClass = hikashop_get('class.order');
    	$orderClass->save($orderObj);
    	$app =& JFactory::getApplication();
    	$this->removeCart = true;
		$name = $method->payment_type.'_end.php';
    	$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashoppayment'.DS.$name;
    	if(!file_exists($path)){
    		if(version_compare(JVERSION,'1.6','<')){
    			$path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$name;
    		}else{
    			$path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$method->payment_type.DS.$name;
    		}
    		if(!file_exists($path)){
    			return true;
    		}
    	}
    	$currencyClass = hikashop_get('class.currency');
    	$amount = $currencyClass->format($order->order_full_price,$order->order_currency_id);
    	$order_number = $order->order_number;
    	require($path);
		return true;
    }
}