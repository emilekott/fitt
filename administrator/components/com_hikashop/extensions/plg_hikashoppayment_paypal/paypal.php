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
class plgHikashoppaymentPaypal extends JPlugin
{
	var $accepted_currencies = array(
		'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD',
		'SEK','DKK','PLN','NOK','HUF','CZK','MXN','BRL','MYR','PHP',
		'TWD','THB','ILS','TRY'
	);
    var $debugData = array();
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='paypal' || !$method->enabled){
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
		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=paypal&tmpl=component&lang='.$locale.$url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$url_itemid;
		$debug = $method->payment_params->debug;
		if(!isset($method->payment_params->no_shipping)) $method->payment_params->no_shipping = 1;
		if(!empty($method->payment_params->rm)) $method->payment_params->rm = 2;
		$vars = array(
		"cmd" => "_ext-enter",
		"redirect_cmd" => "_cart",
		"upload" => "1",
		"business" => $method->payment_params->email,
		"receiver_email" => $method->payment_params->email,
		"invoice" => $order->order_id,
		"currency_code" => $currency->currency_code,
		"return" => $return_url,
		"notify_url" => $notify_url,
		"cancel_return" => $cancel_url,
		"undefined_quantity" => "0",
		"test_ipn" => $debug,
		"no_shipping" => $method->payment_params->no_shipping,
		"no_note" => "1",
		"charset" => "utf-8",
		"rm"=>(int)@$method->payment_params->rm,
		"bn"=> "HikariSoftware_Cart_WPS"
		);
		if(!empty($method->payment_params->address_type)){
			$address_type = $method->payment_params->address_type.'_address';
			$app =& JFactory::getApplication();
			$address=$app->getUserState( HIKASHOP_COMPONENT.'.'.$address_type);
			if(!empty($address)){
				if(!isset($method->payment_params->address_override)){
					$method->payment_params->address_override = '1';
				}
				$vars["address_override"]=$method->payment_params->address_override;
				$cart = hikashop_get('class.cart');
				$cart->loadAddress($order->cart,$address,'object',$method->payment_params->address_type);
				$vars["first_name"]=@$order->cart->$address_type->address_firstname;
				$vars["last_name"]=@$order->cart->$address_type->address_lastname;
				$address1 = '';
				$address2 = '';
				if(!empty($order->cart->$address_type->address_street2)){
					$address2 = substr($order->cart->$address_type->address_street2,0,99);
				}
				if(!empty($order->cart->$address_type->address_street)){
					if(strlen($order->cart->$address_type->address_street)>100){
						$address1 = substr($order->cart->$address_type->address_street,0,99);
						if(empty($address2)) $address2 = substr($order->cart->$address_type->address_street,99,199);
					}else{
						$address1 = $order->cart->$address_type->address_street;
					}
				}
				$vars["address1"]=$address1;
				$vars["address2"]=$address2;
				$vars["zip"]=@$order->cart->$address_type->address_post_code;
				$vars["city"]=@$order->cart->$address_type->address_city;
				$vars["state"]=@$order->cart->$address_type->address_state->zone_code_2;
				$vars["country"]=@$order->cart->$address_type->address_country->zone_code_2;
				$vars["email"]=$user->user_email;
				$vars["night_phone_b"]=@$order->cart->$address_type->address_telephone;
			}elseif(!empty($order->cart->billing_address->address_country->zone_code_2)){
				$vars["lc"]=$order->cart->billing_address->address_country->zone_code_2;
			}
		}elseif(!empty($order->cart->billing_address->address_country->zone_code_2)){
			$vars["lc"]=$order->cart->billing_address->address_country->zone_code_2;
		}
		if(!empty($method->payment_params->cpp_header_image)){
			$vars['cpp_header_image']=$method->payment_params->cpp_header_image;
		}
		if(empty($method->payment_params->details)){
			$vars["amount_1"]=round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$currency->currency_locale['int_frac_digits']);
			$vars["item_name_1"]=JText::_('CART_PRODUCT_TOTAL_PRICE');
		}else{
			$i = 1;
			$tax = 0;
			foreach($order->cart->products as $product){
				$vars["item_name_".$i]=substr(strip_tags($product->order_product_name),0,127);
				$vars["item_number_".$i]=$product->order_product_code;
				$vars["amount_".$i]=round($product->order_product_price,(int)$currency->currency_locale['int_frac_digits']);
				$vars["quantity_".$i]=$product->order_product_quantity;
				$tax+=round($product->order_product_tax,(int)$currency->currency_locale['int_frac_digits'])*$product->order_product_quantity;
				$i++;
			}
			if(bccomp($tax,0,5)){
				$vars['tax_cart']=$tax;
			}
			if(!empty($order->order_shipping_price) && bccomp($order->order_shipping_price,0,5)){
				$vars["item_name_".$i]=JText::_('HIKASHOP_SHIPPING');
				$vars["amount_".$i]=round($order->order_shipping_price,(int)$currency->currency_locale['int_frac_digits']);
				$vars["quantity_".$i]=1;
				$i++;
			}
			if(!empty($order->cart->coupon)){
				$vars["discount_amount_cart"]=round($order->order_discount_price,(int)$currency->currency_locale['int_frac_digits']);
			}
		}

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
		$elements = $pluginsClass->getMethods('payment','paypal');
		if(empty($elements)) return false;
		$element = reset($elements);
		if(!$element->payment_params->notification){
			return false;
		}
    	$vars = array();
    	$data = array();
    	$filter = & JFilterInput::getInstance();
    	foreach($_REQUEST as $key => $value){
    		$key = $filter->clean($key);
    		if(preg_match("#^[0-9a-z_-]{1,30}$#i",$key)&&!preg_match("#^cmd$#i",$key)){
    			$value = JRequest::getString($key);
	    		$vars[$key]=$value;
	    		$data[]=$key.'='.urlencode($value);
    		}
    	}
    	$data = implode('&',$data).'&cmd=_notify-validate';
		if($element->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
		}
		$orderClass = hikashop_get('class.order');
		$dbOrder = $orderClass->get((int)@$vars['invoice']);
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".@$vars['invoice'];
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
    	if(!empty($element->payment_params->ips)){
    		$ip = hikashop_getIP();
    		$ips = str_replace(array('.','*',','),array('\.','[0-9]+','|'),$element->payment_params->ips);
    		if(!preg_match('#('.implode('|',$ips).')#',$ip)){
				$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').' '.JText::sprintf('IP_NOT_VALID',$dbOrder->order_number));
				$body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Paypal',$ip,implode("\r\n",$element->payment_params->ips)))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#ip').$order_text;
				$mailer->setBody($body);
				$mailer->Send();
	    		JError::raiseError( 403, JText::_( 'Access Forbidden' ));
	    		return false;
    		}
    	}
    	$url = parse_url($element->payment_params->url);
    	if(!isset($url['query'])){
			$url['query'] = '';
    	}
    	if(!isset($url['port'])){
    		if(!empty($url['scheme'])&&in_array($url['scheme'],array('https','ssl'))){
    			$url['port'] = 443;
    		}else{
				$url['port'] = 80;
    		}
    	}
    	if(!empty($url['scheme'])&&in_array($url['scheme'],array('https','ssl'))){
    		$url['host_socket'] = 'ssl://'.$url['host'];
    	}else{
    		$url['host_socket'] = $url['host'];
    	}
    	if($element->payment_params->debug){
			echo print_r($url,true)."\n\n\n";
		}
	    $fp = fsockopen ( $url['host_socket'], $url['port'], $errno, $errstr, 30);
	    if (!$fp) {
			$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').' '.JText::sprintf('PAYPAL_CONNECTION_FAILED',$dbOrder->order_number));
			$body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_NO_CONNECTION','Paypal'))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#connection').$order_text;
			$mailer->setBody($body);
			$mailer->Send();
    		JError::raiseError( 403, JText::_( 'Access Forbidden' ));
    		return false;
	    }
	    $uri = $url['path'].($url['query']!='' ? '?' . $url['query'] : '');
    	$header = "POST $uri HTTP/1.0\r\n".
    	"User-Agent: PHP/".phpversion()."\r\n".
	    "Referer: ".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].@$_SERVER['QUERY_STRING']."\r\n".
	    "Server: ".$_SERVER['SERVER_SOFTWARE']."\r\n".
	    "Host: ".$url['host'].":".$url['port']."\r\n".
	    "Content-Type: application/x-www-form-urlencoded\r\n".
	    "Content-Length: ".strlen($data)."\r\n".
	    "Accept: */"."*\r\n\r\n";
	    fwrite($fp, $header . $data);
		$response = '';
		while (!feof($fp)) {
			$response .= fgets ($fp, 1024);
		}
		fclose ($fp);
		if($element->payment_params->debug){
			echo print_r($header,true)."\n\n\n";
			echo print_r($data,true)."\n\n\n";
			echo print_r($response,true)."\n\n\n";
		}
		$response = substr($response, strpos($response, "\r\n\r\n") + strlen("\r\n\r\n"));
		$verified = preg_match( "#VERIFIED#i", $response);
		if(!$verified){
			if(preg_match("#INVALID#i", $response)){
				$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').'invalid transaction');
				$body = JText::sprintf("Hello,\r\n A paypal notification was refused because it could not be verified by the paypal server")."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#invalidtnx').$order_text;
				$mailer->setBody($body);
				$mailer->Send();
				if($element->payment_params->debug){
					echo 'invalid transaction'."\n\n\n";
				}
			}else{
				$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').'invalid response');
				$body = JText::sprintf("Hello,\r\n A paypal notification was refused because the response from the paypal server was invalid")."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#invalidresponse').$order_text;
				$mailer->setBody($body);
				$mailer->Send();
				if($element->payment_params->debug){
					echo 'invalid response'."\n\n\n";
				}
			}
			return false;
		}
		$completed = preg_match("#Completed#i", $vars['payment_status']);
		$pending = preg_match("#Pending#i", $vars['payment_status']);
		 if (!$completed && !$pending) {
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Paypal',$vars['payment_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#status').$order_text;
		 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Paypal',$vars['payment_status']));
			$mailer->setBody($body);
			$mailer->Send();
			if($element->payment_params->debug){
				echo 'payment '.$vars['payment_status']."\n\n\n";
			}
			return false;
		 }
		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified=0;
		$order->history->history_amount=@$vars['mc_gross'].@$vars['mc_currency'];
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method =$element->payment_type;
		$order->history->history_data = ob_get_clean();
		$order->history->history_type = 'payment';
	 	$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
		$currency=$currencies[$dbOrder->order_currency_id];
	 	$price_check = round($dbOrder->order_full_price, (int)$currency->currency_locale['int_frac_digits'] );
	 	if($price_check != @$vars['mc_gross'] || $currency->currency_code != @$vars['mc_currency']){
	 		$order->order_status = $element->payment_params->invalid_status;
	 		$orderClass->save($order);
	 		$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').JText::_('INVALID_AMOUNT'));
			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','Paypal',$order->history->history_amount,$price_check.$currency->currency_code))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#amount').$order_text;
			$mailer->setBody($body);
			$mailer->Send();
	 		return false;
	 	}
	 	if($completed){
	 		$order->order_status = $element->payment_params->verified_status;
	 		$order->history->history_notified=1;
	 	}else{
	 		$order->order_status = $element->payment_params->pending_status;
	 		$order_text = JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#pending')."\r\n\r\n".$order_text;
	 	}
	 	if($dbOrder->order_status == $order->order_status) return true;
	 	$order->mail_status=$statuses[$order->order_status];
	 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Paypal',$vars['payment_status']));
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Paypal',$vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->mail_status)."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
	 	$orderClass->save($order);
    	return true;
    }
    function onPaymentConfiguration(&$element){
    	$subtask = JRequest::getCmd('subtask','');
    	if($subtask=='ips'){
    		$ips = null;
			echo implode(',',$this->_getIPList($ips));
			exit;
    	}else{
    		$this->paypal = JRequest::getCmd('name','paypal');
			if(empty($element)){
				$element = null;
    			$element->payment_name='PayPal';
    			$element->payment_description='You can pay by credit card or paypal using this payment method';
    			$element->payment_images='MasterCard,VISA,Credit_card,PayPal';
    			$element->payment_type=$this->paypal;
    			$element->payment_params=null;
    			$element->payment_params->url='https://www.paypal.com/cgi-bin/webscr';
    			$element->payment_params->notification=1;
    			$list=null;
    			$element->payment_params->ips=$this->_getIPList($list);
    			$element->payment_params->details=0;
    			$element->payment_params->invalid_status='cancelled';
    			$element->payment_params->pending_status='created';
    			$element->payment_params->verified_status='confirmed';
    			$element->payment_params->address_override=1;
    			$element = array($element);
    		}
    		$obj = reset($element);
			if(empty($obj->payment_params->email)){
				$app =& JFactory::getApplication();
				$lang = &JFactory::getLanguage();
				$locale=strtolower(substr($lang->get('tag'),0,2));
				$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED','PayPal',JText::_('HIKA_EMAIL'),'PayPal','https://www.paypal.com/'.$locale.'/mrb/pal=SXL9FKNKGAEM8'));
			}
	    	$bar = & JToolBar::getInstance('toolbar');
			JToolBarHelper::save();
			JToolBarHelper::apply();
			JToolBarHelper::cancel();
			JToolBarHelper::divider();
			$bar->appendButton( 'Pophelp','payment-paypal-form');
			hikashop_setTitle('Paypal','plugin','plugins&plugin_type=payment&task=edit&name='.$this->paypal);
			$app =& JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->paypal);
			$this->address = hikashop_get('type.address');
			$this->category = hikashop_get('type.categorysub');
			$this->category->type = 'status';
    	}
    }
    function onPaymentConfigurationSave(&$element){
    	if(!empty($element->payment_params->ips)){
    		$element->payment_params->ips=explode(',',$element->payment_params->ips);
    	}
		return true;
    }
    function _getIPList(&$ipList){
    	$paypal1 = gethostbynamel('www.paypal.com');
    	$paypal2 = gethostbynamel('notify.paypal.com');
    	$paypal3 = gethostbynamel('ipn.sandbox.paypal.com');
    	$ipList = array();
    	if(!empty($paypal1)){
    		$ipList = $paypal1;
    	}
    	if(!empty($paypal2)){
    		$ipList = array_merge($ipList,$paypal2);
    	}
    	if(!empty($paypal3)){
    		$ipList = array_merge($ipList,$paypal3);
    	}
    	if(!empty($ipList)){
    		$newList = array();
	    	foreach($ipList as $k => $ip){
	    		$ipParts = explode('.',$ip);
	    		if(count($ipParts)==4){
	    			array_pop($ipParts);
	    			$ip = implode('.',$ipParts).'.*';
	    		}
	    		if(!in_array($ip,$newList)){
	    			$newList[]=$ip;
	    		}
	    	}
	    	$ipList = $newList;
    	}
    	return $ipList;
    }
}