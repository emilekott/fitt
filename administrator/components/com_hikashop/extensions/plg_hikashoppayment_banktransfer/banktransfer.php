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
class plgHikashoppaymentBanktransfer extends JPlugin
{
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='banktransfer' || !$method->enabled){
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
    	$this->banktransfer = JRequest::getCmd('name','banktransfer');
    	if(empty($element)){
    		$element = null;
    		$element->payment_name='Bank transfer';
    		$element->payment_description='You can pay by sending us a bank transfer.';
    		$element->payment_images='Bank_transfer';
    		$element->payment_type=$this->banktransfer;
    		$element->payment_params=null;
    		$element->payment_params->information='Account owner: XXXXX<br/>
<br/>
Owner address:<br/>
<br/>
XX XXXX XXXXXX<br/>
<br/>
XXXXX XXXXXXXX<br/>
<br/>
IBAN International Bank Account Number:<br/>
<br/>
XXXX XXXX XXXX XXXX XXXX XXXX XXX<br/>
<br/>
BIC swift Bank Identification Code:<br/>
<br/>
XXXXXXXXXXXXXX<br/>
<br/>
Bank name: XXXXXXXXXXX<br/>
<br/>
Bank address:<br/>
<br/>
XX XXXX XXXXXX<br/>
<br/>
XXXXX XXXXXXXX';
    		$element->payment_params->order_status='created';
    		$element = array($element);
    	}
    	$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','payment-banktransfer-form');
		hikashop_setTitle(JText::_('BANK_TRANSFER'),'plugin','plugins&plugin_type=payment&task=edit&name='.$this->banktransfer);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->banktransfer);
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
		$this->editor = hikashop_get('helper.editor');
    }
    function onPaymentConfigurationSave(&$element){
    	$element->payment_params->information = JRequest::getVar('bank_account_information','','','string',JREQUEST_ALLOWRAW);
    	return true;
    }
    function onAfterOrderConfirm(&$order,&$methods,$method_id){
    	$method =& $methods[$method_id];
    	$orderObj = null;
    	$orderObj->order_status = $method->payment_params->order_status;
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
    	$information = $method->payment_params->information;
    	if(preg_match('#^[a-z0-9_]*$#i',$information)){
    		$information = JText::_($information);
    	}
    	$currencyClass = hikashop_get('class.currency');
    	$amount = $currencyClass->format($order->order_full_price,$order->order_currency_id);
    	$order_number = $order->order_number;
    	require($path);
		return true;
    }
}