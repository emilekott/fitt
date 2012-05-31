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
class plgHikashoppaymentNbepay extends JPlugin
{
	var $accepted_currencies = array(
		'AUD','BGN','CAD','CHF','CZK','DKK','EEK','EUR','GBP','HKD',
		'HUF','INR','LTL','MYR','MKD','NOK','NZD','PLN','RON','SEK',
		'SGD','USD','ZAR',
	);
    var $debugData = array();
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='alertpay' || !$method->enabled){
					continue;
				}
				if(!empty($method->payment_zone_namekey)){
					$zoneClass=hikashop_get('class.zone');
	    			$zones = $zoneClass->getOrderZones($order);
					if(!in_array($method->payment_zone_namekey,$zones)){
						return true;
					}
				}
				$currencyClass = hikashop_get('class.currency');
				$null=null;
				if(!empty($order->total)){
					$currency_id = intval(@$order->total->prices[0]->price_currency_id);
					$currency = $currencyClass->getCurrencies($currency_id,$null);
					if(!empty($currency) && !in_array(@$currency[$currency_id]->currency_code,$this->accepted_currencies)){
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
    function onAfterOrderConfirm(&$order,&$methods,$method_id){
    	$method =& $methods[$method_id];
		$tax_total = '';
		$discount_total = '';
		$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency=$currencies[$order->order_currency_id];
		hikashop_loadUser(true,true); //reset user data in case the emails were changed in the email code
		$user = hikashop_loadUser(true);
		$lang = &JFactory::getLanguage();
		$locale=strtolower(substr($lang->get('tag'),0,2));
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$return_url =  HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$url_itemid;
		$vars = Array(
		"ap_purchasetype" => "item",
		"ap_merchant" => $method->payment_params->email,
		"apc_1" => $order->order_id,
		"ap_itemname" => $order->order_number,
		"ap_currency" => $currency->currency_code,
		"ap_returnurl" => $return_url,
		"ap_cancelurl" => $cancel_url,
		"ap_amount" => round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$currency->currency_locale['int_frac_digits']),
		);
		if(!empty($method->payment_params->address_type)){
			$address_type = $method->payment_params->address_type.'_address';
			$app =& JFactory::getApplication();
			$address=$app->getUserState( HIKASHOP_COMPONENT.'.'.$address_type);
			if(!empty($address)){
				$cart = hikashop_get('class.cart');
				$cart->loadAddress($order->cart,$address,'object',$method->payment_params->address_type);
				$vars["ap_fname"]=@$order->cart->$address_type->address_firstname;
				$vars["ap_lname"]=@$order->cart->$address_type->address_lastname;
				$address1 = '';
				$address2 = '';
				if(!empty($order->cart->$address_type->address_street)){
					if(strlen($order->cart->$address_type->address_street)>100){
						$address1 = substr($order->cart->$address_type->address_street,0,99);
						$address2 = substr($order->cart->$address_type->address_street,99,199);
					}else{
						$address1 = $order->cart->$address_type->address_street;
					}
				}
				$vars["ap_addressline1"]=$address1;
				$vars["ap_addressline2"]=$address2;
				$vars["ap_zippostalcode"]=@$order->cart->$address_type->address_post_code;
				$vars["ap_city"]=@$order->cart->$address_type->address_city;
				$vars["ap_stateprovince"]=@$order->cart->$address_type->address_state->zone_code_2;
				$vars["ap_country"]=@$order->cart->$address_type->address_country->zone_code_3;
				$vars["ap_contactemail"]=$user->user_email;
				$vars["ap_contactphone"]=@$order->cart->$address_type->address_telephone;
			}
		}
		$method->payment_params->url='https://www.alertpay.com/PayProcess.aspx';


		JHTML::_('behavior.mootools');
		$app =& JFactory::getApplication();
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
    	require($path);
    	return true;
    }
    function onPaymentNotification(&$statuses){
    	$pluginsClass = hikashop_get('class.plugins');
		$elements = $pluginsClass->getMethods('payment','alertpay');
		if(empty($elements)) return false;
		$element = reset($elements);
		if(!$element->payment_params->notification){
			return false;
		}
    	$vars = array();
    	$filter = & JFilterInput::getInstance();
    	foreach($_POST as $key => $value){
    		$key = $filter->clean($key);
    		$value = JRequest::getString($key);
    		$vars[$key]=$value;
    	}
		if($element->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
		}
		$orderClass = hikashop_get('class.order');
		$dbOrder = $orderClass->get((int)@$vars['apc_1']);
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".@$vars['apc_1'];
			return false;
		}
		$order = null;
		$order->order_id = $dbOrder->order_id;
		$order->old_status->order_status=$dbOrder->order_status;
		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		if($element->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
		}
		$mailer =& JFactory::getMailer();
		$config =& hikashop_config();
		$sender = array(
		    $config->get('from_email'),
		    $config->get('from_name') );
		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));
    	if($vars['ap_merchant']!=$element->payment_params->email || $vars['ap_securitycode']!=$element->payment_params->security_code){
			$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','AlertPay').'invalid response');
			$body = JText::sprintf("Hello,\r\n An AlertPay notification was refused because the notification from the AlertPay server was invalid")."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();
			if($element->payment_params->debug){
				echo 'invalid response'."\n\n\n";
			}
			return false;
    	}
		if($vars['ap_status']!='Success'){
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','AlertPay',$vars['ap_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','AlertPay',$vars['ap_status']));
			$mailer->setBody($body);
			$mailer->Send();
			if($element->payment_params->debug){
				echo 'payment '.$vars['payment_status']."\n\n\n";
			}
			return false;
		}
		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified=0;
		$order->history->history_amount=@$vars['ap_totalamount'].@$vars['ap_currency'];
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method =$element->payment_type;
		$order->history->history_data = ob_get_clean();
		$order->history->history_type = 'payment';
	 	$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
		$currency=$currencies[$dbOrder->order_currency_id];
	 	$price_check = round($dbOrder->order_full_price, (int)$currency->currency_locale['int_frac_digits'] );
	 	if($price_check != @$vars['ap_totalamount'] || $currency->currency_code != @$vars['ap_currency']){
	 		$order->order_status = $element->payment_params->invalid_status;
	 		$orderClass->save($order);
	 		$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','AlertPay').JText::_('INVALID_AMOUNT'));
			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','AlertPay',$order->history->history_amount,$price_check.$currency->currency_code))."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();
	 		return false;
	 	}
 		$order->order_status = $element->payment_params->verified_status;
 		$order->history->history_notified=1;
	 	$order->mail_status=$statuses[$order->order_status];
	 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','AlertPay',$vars['ap_status']));
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','AlertPay',$vars['ap_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->mail_status)."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
	 	$orderClass->save($order);
    	return true;
    }
    function onPaymentConfiguration(&$element){
    	$this->alertpay = JRequest::getCmd('name','alertpay');
		if(empty($element)){
			$element = null;
    		$element->payment_name='AlertPay';
    		$element->payment_description='You can pay by credit card using this payment method';
    		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
    		$element->payment_type=$this->alertpay;
    		$element->payment_params=null;
    		$element->payment_params->address_type="billing";
    		$element->payment_params->notification=1;
    		$element->payment_params->invalid_status='cancelled';
    		$element->payment_params->verified_status='confirmed';
    		$element = array($element);
    	}
    	$lang = &JFactory::getLanguage();
		$locale=strtoupper(substr($lang->get('tag'),0,2));
		$key = key($element);
		$element[$key]->payment_params->status_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=alertpay&tmpl=component&lang='.strtolower($locale);
    	$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','payment-alertpay-form');
		hikashop_setTitle('AlertPay','plugin','plugins&plugin_type=payment&task=edit&name='.$this->alertpay);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->alertpay);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
    }
    function onPaymentConfigurationSave(&$element){
		return true;
    }
}