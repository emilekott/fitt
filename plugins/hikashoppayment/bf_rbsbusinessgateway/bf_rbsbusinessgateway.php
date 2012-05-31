<?php
/**
 * @package		 HikaShop for Joomla!
 * @subpackage Payment Plug-in for RBS Worldpay Business Gateway.
 * @version		 0.0.1
 * @author		 brainforge.co.uk
 * @copyright	 (C) 2011 Brainforge derive from Paypal plug-in by HIKARI SOFTWARE. All rights reserved.
 * @license		 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * In order to configure and use this plug-in you must have a RBS Worldpay Business Gateway account.
 * RBS Worldpay Business Gateway is sometimes refered to as 'Select Junior'.
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgHikashoppaymentbf_rbsbusinessgateway extends JPlugin
{
	var $accepted_currencies = array(
		'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD',
		'SEK','DKK','PLN','NOK','HUF','CZK','MXN','BRL','MYR','PHP',
		'TWD','THB','ILS'
	);
    var $debugData = array();
    function isShippingValid($shipping) {
      return true;
    }
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
      if (!$this->isShippingValid(@$order->shipping)) return false;
			if (empty($order->total)) return false;
    	if (empty($methods)) return false;
		  $user = hikashop::loadUser(true);
      if (!$user) return false;
      $found = false;
  		foreach($methods as $method){
  		if($method->payment_type!='bf_rbsbusinessgateway') continue;
        if ($method->payment_params->testMode) {
          if (isset($user->user_tester)) {
            if (@$user->user_tester != 'Y') continue;
          }
        }
        else if (@$user->user_tester == 'Y') continue;
        if (!$method->enabled) continue;
				if(!empty($method->payment_zone_namekey)){
					$zoneClass=hikashop::get('class.zone');
    			$zones = $zoneClass->getOrderZones($order);
					if(!in_array($method->payment_zone_namekey,$zones)) continue;
				}
				$currencyClass = hikashop::get('class.currency');
				$null=null;
				$currency_id = intval(@$order->total->prices[0]->price_currency_id);
				$currency = $currencyClass->getCurrencies($currency_id,$null);
				if(!empty($currency) && !in_array(@$currency[$currency_id]->currency_code,$this->accepted_currencies)){
					continue;
				}
				$usable_methods[$method->ordering]=$method;
        $found = true;
  		}
    	return $found;
    }
    function onBeforeOrderCreate(&$order,&$do) {
			if($order->order_payment_method!='bf_rbsbusinessgateway') return;
      if (!$this->isShippingValid(@$order->cart->shipping)) {
        $do = false;
        JError::raiseWarning(100, 'Error - This payment method is not available with the selected shipping method.' );
      }
    }
    function onPaymentSave(&$cart,&$rates,&$payment_id){
    	$usable = array();
    	if ($this->onPaymentDisplay($cart,$rates,$usable)) {
      	$payment_id = (int) $payment_id;
      	foreach($usable as $usable_method){
      		if($usable_method->payment_id==$payment_id){
      			return $usable_method;
      		}
      	}
      }
    	return false;
    }
    function addAddress($user, $order, $address_type, &$vars, $prefix=null) {
			$app =& JFactory::getApplication();
			$address=$app->getUserState( HIKASHOP_COMPONENT.'.'.$address_type);
			if(!empty($address)) {
				$cart = hikashop::get('class.cart');
				$cart->loadAddress($order->cart,$address,'object',$address_type);
				$vars[$prefix.'name'] = trim(@$order->cart->$address_type->address_lastname . ', ' . @$order->cart->$address_type->address_firstname, ', ');
				$vars[$prefix.'address']=trim($order->cart->$address_type->address_street . ",\n" . @$order->cart->$address_type->address_city, ",\n ");
				$vars[$prefix.'postcode']=@$order->cart->$address_type->address_post_code;
				$vars[$prefix.'country']=@$order->cart->$address_type->address_country->zone_code_2;
        if (empty($vars[$prefix.'country']) && $vars[$prefix.'currency'] == 'GBP') {
          $vars[$prefix.'country'] = 'GB';
        }
				if (empty($prefix)) {
          $vars[$prefix.'email']=$user->user_email;
  				$vars[$prefix.'tel']=@$order->cart->$address_type->address_telephone;
        }
		  }
    }
    function onAfterOrderConfirm(&$order,&$methods,$method_id) {
      $method =& $methods[$method_id];
  		$currencyClass = hikashop::get('class.currency');
  		$currencies=null;
  		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
  		$currency=$currencies[$order->order_currency_id];
  		$user = hikashop::loadUser(true);
  		$lang = &JFactory::getLanguage();
  		$locale=strtolower(substr($lang->get('tag'),0,2));
      $x = isset($order->cart->products);
      $y = isset($order->products);
  		$vars = Array(
          'instId'   => $method->payment_params->instid,
          'cartId'   => $order->order_id,
      		'amount'   => $order->order_full_price,
      		'currency' => $currency->currency_code,
                   );
      if (!empty($method->payment_params->descProductName) &&
          count($order->cart->products) == 1) {
    		foreach($order->cart->products as $product) {
    		  $vars['desc'] = substr($product->order_product_name, 0, 255);
        }
      }
      else $vars['desc'] = substr($method->payment_params->desc,0,255);
      if (!empty($method->payment_params->notification)) {
        $vars['MC_callback'] = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=' . $method->payment_type . '&tmpl=component&lang='.$locale;
      }
      if (!empty($method->payment_params->fixContact)) $vars['fixContact'] = null;
      if (!empty($method->payment_params->hideContact)) $vars['hideContact'] = null;
  		if(!empty($method->payment_params->address_type)) {
  		  switch ($method->payment_params->address_type) {
  		    case 'billing';
            $this->addAddress($user, $order, 'billing_address', $vars);
            break;
  		    case 'shipping';
            $this->addAddress($user, $order, 'shipping_address', $vars);
            break;
  		    case 'both';
            $this->addAddress($user, $order, 'billing_address', $vars);
            $vars['withDelivery'] = 'true';
            $this->addAddress($user, $order, 'shipping_address', $vars, 'delv');
            break;
        }
      }
      if (!empty($method->payment_params->testMode)) $vars['testMode'] = '100';
      if (empty($vars['name'])) $vars['name'] = $user->username;
  		$i = 1;
  		$tax_cart = 0;
  		foreach($order->cart->products as $product){
  			$vars["C_item_name_".$i]=substr($product->order_product_name,0,127);
  			$vars["C_item_number_".$i]=$product->order_product_code;
  			$vars["C_quantity_".$i]=$product->order_product_quantity;
  			$amount_item=round($product->order_product_price,(int)$currency->currency_locale['int_frac_digits']);
  			$tax_item =round($product->order_product_tax,(int)$currency->currency_locale['int_frac_digits']);
        if ($method->payment_params->show_tax_amount) $tax_cart+=($tax_item*$product->order_product_quantity);
        else $amount_item+=$tax_item;
  			$vars["C_amount_".$i]=$amount_item;
  			$i++;
  		}
  		if(!empty($order->order_shipping_price) || !empty($order->cart->shipping->shipping_name)){
  			$vars["C_item_name_".$i]=JText::_('HIKASHOP_SHIPPING');
    		if(!empty($order->order_shipping_price)){
          if ($method->payment_params->show_tax_amount && !empty($order->cart->shipping->shipping_price)) {
            $amount_item=round($order->cart->shipping->shipping_price,(int)$currency->currency_locale['int_frac_digits']);
            $tax_item=round($order->cart->shipping->shipping_price_with_tax,(int)$currency->currency_locale['int_frac_digits'])-$amount_item;
            $tax_cart+=$tax_item;
            $vars["C_amount_".$i]=$amount_item;
          }
          else $vars["C_amount_".$i]=round($order->order_shipping_price,(int)$currency->currency_locale['int_frac_digits']);
        }
        else $vars["C_amount_".$i] = 0;
  			$vars["C_quantity_".$i]=1;
  			if (empty($order->cart->shipping->shipping_name)) $vars["item_number_".$i]= $order->order_shipping_method;
        else $vars["C_item_number_".$i]= ucwords($order->cart->shipping->shipping_name);
  			$i++;
  		}
  		if(bccomp($tax_cart,0,5)){
  			$vars['C_tax_cart']=$tax_cart;
  		}
  		if(!empty($order->cart->coupon)){
  			$vars["C_discount_amount_cart"]=round($order->cart->coupon->discount_value,(int)$currency->currency_locale['int_frac_digits']);
  		}
  		JHTML::_('behavior.mootools');
  		$app =& JFactory::getApplication();
  		$name = $method->payment_type.'_end.php';
    	$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashoppayment'.DS.$name;
    	if(!file_exists($path)){
    		if(version_compare(JVERSION,'1.6','<')) $path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$name;
        else $path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$method->payment_type.DS.$name;
    		if(!file_exists($path)) return true;
    	}
    	require($path);
    	return true;
    }
    function onPaymentNotification(&$statuses){
    	$pluginsClass = hikashop::get('class.plugins');
  		$elements = $pluginsClass->getMethods('payment','bf_rbsbusinessgateway');
  		if(empty($elements)) return false;
  		$element = reset($elements);
  		if(!$element->payment_params->notification) return false;
    	$vars = array();
    	$data = array();
    	$filter = & JFilterInput::getInstance();
    	foreach($_POST as $key => $value){
    		$key = $filter->clean($key);
    		if(preg_match("#^[0-9a-z_-]{1,30}$#i",$key)&&!preg_match("#^cmd$#i",$key)){
    			$value = JRequest::getString($key);
	    		$vars[$key] = $value;
	    		$data[] = $key . '=' . urlencode($value);
    		}
    	}
      if (@$vars['instId'] != $element->payment_params->instid) return false;
    	$data = implode('&',$data).'&cmd=_notify-validate';
  		if($element->payment_params->debug) echo print_r($vars,true)."\n\n\n";
  		$orderClass = hikashop::get('class.order');
  		$dbOrder = $orderClass->get((int)@$vars['cartId']);
  		if(empty($dbOrder)){
  			echo "Could not load any order for your notification ".@$vars['cartId'];
  			return false;
  		}
  		$order = null;
  		$order->order_id = $dbOrder->order_id;
  		$order->old_status->order_status = $dbOrder->order_status;
  		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
  		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',hikashop::encode($dbOrder),HIKASHOP_LIVE);
  		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
  		if($element->payment_params->debug) echo print_r($dbOrder,true)."\n\n\n";
  		$mailer =& JFactory::getMailer();
  		$config =& hikashop::config();
  		$sender = array(
  		    $config->get('from_email'),
  		    $config->get('from_name')
                     );
  		$mailer->setSender($sender);
  		$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));
      $hostError = -1;
      $ip = hikashop::getIP();
    	if(!empty($element->payment_params->hostname)){ // \.outbound\.wp3\.rbsworldpay\.com
        $hostname = gethostbyaddr($ip);
        if (preg_match('#' . $element->payment_params->hostname . '#i', $hostname)) $hostError = 0;
        else $hostError = 1;
      }
      if ($hostError < 0) {
    		$ips = str_replace(array('.','*',','),array('\.','[0-9]+','|'),$element->payment_params->ips);
        if (!empty($ips)) {
      		if (preg_match('#('.implode('|',$ips).')#',$ip)) $hostError = 0;
          else $hostError = 1;
        }
      }
      if ($hostError > 0) {
				$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','RBS Worldpay Business Gateway').' '.JText::sprintf('IP_NOT_VALID',hikashop::encode($dbOrder)));
				$body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','RBS Worldpay Business Gateway',$ip,'See Hostname / IPs defined in configuration'))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-rbsworldpay-error#ip').$order_text;
				$mailer->setBody($body);
				$mailer->Send();
    		JError::raiseError( 403, JText::_( 'Access Forbidden' ));
    		return false;
    	}
  		switch ($vars['transStatus']) {
  		  case 'Y':
          break;
        default:
          $body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','RBS Worldpay Business Gateway',$vars['payment_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-rbsworldpay-error#status').$order_text;
          $mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','RBS Worldpay Business Gateway',$vars['transStatus']));
          $mailer->setBody($body);
          $mailer->Send();
          if($element->payment_params->debug) echo 'payment '.$vars['transStatus']."\n\n\n";
          return false;
		  }
  		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
  		$order->history->history_notified=0;
  		$order->history->history_amount=@$vars['amount'].@$vars['currency'];
  		$order->history->history_payment_id = $element->payment_id;
  		$order->history->history_payment_method =$element->payment_type;
  		$order->history->history_data = ob_get_clean();
  		$order->history->history_type = 'payment';
  	 	$currencyClass = hikashop::get('class.currency');
  		$currencies=null;
  		$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
  		$currency=$currencies[$dbOrder->order_currency_id];
  	 	$price_check = round($dbOrder->order_full_price, (int)$currency->currency_locale['int_frac_digits'] );
  	 	if($price_check != @$vars['amount'] || $currency->currency_code != @$vars['currency']){
  	 		$order->order_status = $element->payment_params->invalid_status;
  	 		$orderClass->save($order);
  	 		$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','RBS Worldpay Business Gateway').JText::_('INVALID_AMOUNT'));
  			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','RBS Worldpay Business Gateway',$order->history->history_amount,$price_check.$currency->currency_code))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-rbsworldpay-error#amount').$order_text;
  			$mailer->setBody($body);
  			$mailer->Send();
  	 		return false;
  	 	}
  		switch ($vars['transStatus']) {
  		  case 'Y':
          $payment_status = 'Authenticated';
    	 		$order->order_status = $element->payment_params->verified_status;
    	 		$order->history->history_notified = 1;
          break;
        default:
          $payment_status = 'Unknown';
    	 		$order->order_status = $element->payment_params->invalid_status;
    	 		$order_text = JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-rbsworldpay-error#pending')."\r\n\r\n".$order_text;
  	 	}
  	 	$order->mail_status=$statuses[$order->order_status];
  	 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','RBS Worldpay Business Gateway',$payment_status));
  		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','RBS Worldpay Business Gateway',$vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->mail_status)."\r\n\r\n".$order_text;
  		$mailer->setBody($body);
  		$mailer->Send();
  	 	$orderClass->save($order);
      echo '[OK]';
  		if($element->payment_params->debug) {
    		$dbg = ob_get_clean();
        echo '[OK]';
    		ob_start();
        echo $dbg;
  		}
    	return true;
    }
    function onPaymentConfiguration(&$element){
    	$subtask = JRequest::getCmd('subtask','');
    	if($subtask=='ips'){
    		$ips = null;
			echo implode(',',$this->_getIPList($ips));
			exit;
    	}else{
    		$this->bf_rbsbusinessgateway = JRequest::getCmd('name','bf_rbsbusinessgateway');
  			if(empty($element)){
   				  $element = null;
      			$element->payment_name='RBS Worldpay Business Gateway';
      			$element->payment_description='You can pay by debit or credit card using this payment method';
      			$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
      			$element->payment_type=$this->bf_rbsbusinessgateway;
      			$element->payment_params=null;
      			$element->payment_params->url='https://secure-test.wp3.rbsworldpay.com/wcc/dispatcher';
      			$element->payment_params->notification=1;
            $element->payment_params->hostname = '\.outbound\.wp3\.rbsworldpay\.com';
            $element->payment_params->ips = '155.136.66.*,155.136.16.*';
      			$element->payment_params->invalid_status='cancelled';
      			$element->payment_params->verified_status='confirmed';
      			$element->payment_params->confirmed_status='confirmed';
      			$element->payment_params->redirect_button='style="background: url(\'https://secure-test.wp3.rbsworldpay.com/images/rbswp/brand.gif\') top left no-repeat;' .
                                                   'width:139px;height:33px;border:solid 1px #7C98B7;cursor:pointer;margin:10px 100px;"';
      			$element = array($element);
      		}
  	    	$bar = & JToolBar::getInstance('toolbar');
  			JToolBarHelper::save();
  			JToolBarHelper::apply();
  			JToolBarHelper::cancel();
  			JToolBarHelper::divider();
  			$bar->appendButton( 'Pophelp','payment-bf_rbsbusinessgateway-form');
  			hikashop::setTitle('RBS Worldpay Business Gateway','plugin','plugins&plugin_type=payment&task=edit&name='.$this->bf_rbsbusinessgateway);
  			$app =& JFactory::getApplication();
  			$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->bf_rbsbusinessgateway);
  			$this->address = hikashop::get('type.address');
  			$this->category = hikashop::get('type.categorysub');
  			$this->category->type = 'status';
    	}
    }
    function onPaymentConfigurationSave(&$element){
    	if(!empty($element->payment_params->ips)){
    		$element->payment_params->ips=explode(',',$element->payment_params->ips);
    	}
  		return true;
    }
}
?>