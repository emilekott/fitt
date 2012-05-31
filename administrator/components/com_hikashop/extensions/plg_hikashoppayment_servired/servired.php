<?php
defined('_JEXEC') or die('Restricted access');
class plgHikashoppaymentservired extends JPlugin{
	var $accepted_currencies = array(
		'EUR'
	);
    var $debugData = array();
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='servired' || !$method->enabled){
					continue;
				}
				if(!empty($method->payment_zone_namekey)){
					$zoneClass=hikashop::get('class.zone');
	    			$zones = $zoneClass->getOrderZones($order);
					if(!in_array($method->payment_zone_namekey,$zones)){
						return true;
					}
				}
				$currencyClass = hikashop::get('class.currency');
				$null=null;
				$currency_id = intval(@$order->total->prices[0]->price_currency_id);
				$currency = $currencyClass->getCurrencies($currency_id,$null);
				if(!empty($currency) && !in_array(@$currency[$currency_id]->currency_code,$this->accepted_currencies)){
					return true;
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
		$user = hikashop::loadUser(true);
		$amount_total=round($order->cart->full_total->prices[0]->price_value_with_tax*100,(int)$currency->currency_locale['int_frac_digits']);
		$app =& JFactory::getApplication();
		$cart = hikashop::get('class.cart');
		$address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
		$type = 'shipping';
		if(empty($address)){
	    	$address=$app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
			if(!empty($address)){
				$type='billing';
			}
		}
		if(!empty($address)){
			$cart->loadAddress($order->cart,$address,'object',$type);
			$address_type = $type.'_address';
			$vars["pays_liv"]=@$order->cart->$address_type->address_country->zone_code_3;
		}
		$vars["id_client"]=$order->order_user_id;
		$id_pedido=$order->order_id;

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

    	$pluginsClass = hikashop::get('class.plugins');
		$elements = $pluginsClass->getMethods('payment','servired');
		if(empty($elements)){
		return false; }
		$element = reset($elements);
    	$vars = array();
    	$data = array();
    	$filter = & JFilterInput::getInstance();
    	foreach($_POST as $key => $value){
    		$key = $filter->clean($key);
    		$value = JRequest::getString($key);
    		$vars[$key]=$value;

    	}
		$orderClass = hikashop::get('class.order');
		$dbOrder = $orderClass->get((int)@$vars['Ds_Order']);
		$temp_order=(int)@$vars['Ds_Order'];
		$order = null;
		$order->order_id = @$dbOrder->order_id;


		if(!empty($dbOrder)){
			$order->old_status->order_status=$dbOrder->order_status;
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
			$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
			$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		}else{
			echo "Could not load any order for your notification ".@$vars['Ds_Order'];
			return false;
		}
		if($element->payment_params->debug){
			echo print_r($dbOrder)."\n\n\n";
		}
		$mailer =& JFactory::getMailer();
		$config =& hikashop::config();
		$sender = array(
		    $config->get('from_email'),
		    $config->get('from_name') );
		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));

		        $Sig_amount=$vars['Ds_Amount'];
		        $Sig_order=$vars['Ds_Order'];
				$Sig_code=$element->payment_params->merchantId;
				$Sig_currency='978';
				$Sig_transactionType='0';
				$Sig_response=$vars['Ds_Response'];
		   		$Sig_clave=$element->payment_params->encriptionKey;
				$Sig_message = $Sig_amount.$Sig_order.$Sig_code.$Sig_currency.$Sig_response.$Sig_clave;
		        $signature = strtoupper(sha1($Sig_message));
				$Ds_Signature=$vars['Ds_Signature'];


		if($Ds_Signature==$signature){


		$DS1_RESPONSE=(int)@$vars['Ds_Response'];
		if ( $DS1_RESPONSE>=0 &&   $DS1_RESPONSE<100 ) {

		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified=0;
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method =$element->payment_type;
		$order->history->history_data = ob_get_clean();
		$order->history->history_type = 'payment';
	 	$currencyClass = hikashop::get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
		$currency=$currencies[$dbOrder->order_currency_id];
	 	$price_check = round($dbOrder->order_full_price,(int)$currency->currency_locale['int_frac_digits']).$currency->currency_code;
	 	$order->history->history_amount=@$vars['Ds_Amount'].$currency->currency_code;
	 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','servired','Confirmado'));
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','servired','Confirmado')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order->order_status])."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
		$order->order_status = $element->payment_params->verified_status;
	 	$orderClass->save($order);
    	return true;
		}
		else //Failed operation received form pasarela
		{
		$order->order_status = $element->payment_params->invalid_status;
	 	$orderClass->save($order);
		return false;
		}
  	   }
	   else
	   {

		 return false;
	   }
    }
    function onPaymentConfiguration(&$element){
	    	$this->servired = JRequest::getCmd('name','servired');
			if(empty($element)){
				$element = null;
	    		$element->payment_name='Servired';
	    		$element->payment_description='You can pay by credit card or paypal using this payment method';
	    		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
	    		$element->payment_type=$this->servired;
	    		$element->payment_params=null;
	    		$list=null;
	    		$element->payment_params->notification=true;
	    		$element->payment_params->url='https://www.servired.com/in.php';
				$element->payment_params->secure_key='';
	    		$element->payment_params->invalid_status='cancelled';
	    		$element->payment_params->pending_status='created';
	    		$element->payment_params->verified_status='confirmed';
	    		$element = array($element);
	    	}
	    	$lang = &JFactory::getLanguage();
			$locale=strtoupper(substr($lang->get('tag'),0,2));
			$key = key($element);
			$element[$key]->payment_params->status_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=servired&tmpl=component&lang='.strtolower($locale);
	    	$bar = & JToolBar::getInstance('toolbar');
			JToolBarHelper::save();
			JToolBarHelper::apply();
			JToolBarHelper::cancel();
			JToolBarHelper::divider();
			$bar->appendButton( 'Pophelp','payment-servired-form');
			hikashop::setTitle('servired','plugin','plugins&plugin_type=payment&task=edit&name='.$this->servired);
			$app =& JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->servired);
			$this->address = hikashop::get('type.address');
			$this->category = hikashop::get('type.categorysub');
			$this->category->type = 'status';
    }
    function onPaymentConfigurationSave(&$element){
		return true;
    }
}
