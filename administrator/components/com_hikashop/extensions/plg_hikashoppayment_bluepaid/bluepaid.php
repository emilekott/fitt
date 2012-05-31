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
class plgHikashoppaymentBluepaid extends JPlugin
{
	var $accepted_currencies = array(
		'EUR','CHF','USD','GBP','JPY','CAD','AUD'
	);
    var $debugData = array();
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='bluepaid' || !$method->enabled){
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
		$tax_total = '';
		$discount_total = '';
		$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency=$currencies[$order->order_currency_id];
		$user = hikashop_loadUser(true);
		$vars = Array(
		"devise" => $currency->currency_code,
		"montant" => round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$currency->currency_locale['int_frac_digits']),
		);
		$vars["email_client"]=$user->user_email;
		$vars["id_boutique"]=$method->payment_params->shop_id;
		$lang = &JFactory::getLanguage();
		$locale=strtoupper(substr($lang->get('tag'),0,2));
		if(!in_array($locale,array('EN', 'DE', 'ES', 'FR', 'IT', 'NL', 'PT'))) $locale = 'EN';
		$vars["langue"]=$locale;
		$app =& JFactory::getApplication();
		$cart = hikashop_get('class.cart');
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
		$vars["divers"]=$order->order_id;

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
    	$this->removeCart = true;
    	return true;
    }
    function onPaymentNotification(&$statuses){
    	$pluginsClass = hikashop_get('class.plugins');
		$elements = $pluginsClass->getMethods('payment','bluepaid');
		if(empty($elements)) return false;
		$element = reset($elements);
		if(!$element->payment_params->notification){
			return false;
		}
    	$vars = array();
    	$data = array();
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
		$dbOrder = $orderClass->get((int)@$vars['divers']);
		$order = null;
		$order->order_id = @$dbOrder->order_id;
		if(!empty($dbOrder)){
			$order->old_status->order_status=$dbOrder->order_status;
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
			$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
			$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		}else{
			echo "Could not load any order for your notification ".@$vars['divers'];
			return false;
		}
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
				$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Bluepaid').' '.JText::sprintf('IP_NOT_VALID',$dbOrder->order_number));
				$body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Bluepaid',$ip,implode("\r\n",$element->payment_params->ips)))."\r\n\r\n".$order_text;
				$mailer->setBody($body);
				$mailer->Send();
	    		JError::raiseError( 403, JText::_( 'Access Forbidden' ));
	    		return false;
    		}
    	}
	    if ($vars['secure_key']!=@$element->payment_params->secure_key) {
	    	$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Bluepaid').'invalid response');
			$body = JText::sprintf("Hello,\r\n A Bluepaid notification was refused because the response from the Bluepaid server was invalid")."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();
			if($element->payment_params->debug){
				echo 'invalid response'."\n\n\n";
			}
    		return false;
	    }
	    $vars['status']=strtolower(@$vars['etat']);
		if(!in_array($vars['status'],array("attente","ok"))) {
			if($vars['status']=="annu"){
				$vars['payment_status']='Cancelled';
			}elseif($vars['status']=="ko"){
				$vars['payment_status']='Failed';
			}else{
				$vars['payment_status']='Unknown';
			}
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Bluepaid',$vars['payment_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Bluepaid',$vars['payment_status']));
			$mailer->setBody($body);
			$mailer->Send();
			if($element->payment_params->debug){
				echo 'payment with code '.@$vars['status'].(!empty($vars['failed_reason_code'])?' : '.@$vars['failed_reason_code']:'')."\n\n\n";
			}
			return false;
		 }
		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified=0;
		$order->history->history_amount=@$vars['montant'].@$vars['devise'];
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method =$element->payment_type;
		$order->history->history_data = ob_get_clean();
		$order->history->history_type = 'payment';
	 	$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
		$currency=$currencies[$dbOrder->order_currency_id];
	 	$price_check = round($dbOrder->order_full_price,(int)$currency->currency_locale['int_frac_digits']).$currency->currency_code;
	 	if($price_check != @$vars['montant'].@$vars['devise']){
	 		$order->order_status = $element->payment_params->invalid_status;
	 		$orderClass->save($order);
	 		$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Bluepaid').JText::_('INVALID_AMOUNT'));
			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','Bluepaid',$order->history->history_amount,$price_check))."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();
	 		return false;
	 	}
	 	if($vars['status']=="ok"){
	 		$order->order_status = $element->payment_params->verified_status;
	 		$vars['payment_status']='Accepted';
	 		$order->history->history_notified=1;
	 	}else{
	 		$order->order_status = $element->payment_params->pending_status;
	 		$order_text ="Payment is pending\r\n\r\n".$order_text;
	 		$vars['payment_status']='Pending';
	 	}
	 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Bluepaid',$vars['payment_status']));
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Bluepaid',$vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order->order_status])."\r\n\r\n".$order_text;
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
	    	$this->bluepaid = JRequest::getCmd('name','bluepaid');
			if(empty($element)){
				$element = null;
	    		$element->payment_name='Bluepaid';
	    		$element->payment_description='Vous pouvez payer par carte bleue avec ce système de paiement';
	    		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
	    		$element->payment_type=$this->bluepaid;
	    		$element->payment_params=null;
	    		$list=null;
	    		$element->payment_params->notification=true;
	    		$element->payment_params->ips=$this->_getIPList($list);
	    		$element->payment_params->url='https://www.bluepaid.com/in.php';
				$element->payment_params->secure_key=md5(time().rand());
	    		$element->payment_params->invalid_status='cancelled';
	    		$element->payment_params->pending_status='created';
	    		$element->payment_params->verified_status='confirmed';
	    		$element = array($element);
	    	}
	    	$lang = &JFactory::getLanguage();
			$locale=strtoupper(substr($lang->get('tag'),0,2));
			$key = key($element);
			$element[$key]->payment_params->status_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=bluepaid&tmpl=component&lang='.strtolower($locale);
	    	$bar = & JToolBar::getInstance('toolbar');
			JToolBarHelper::save();
			JToolBarHelper::apply();
			JToolBarHelper::cancel();
			JToolBarHelper::divider();
			$bar->appendButton( 'Pophelp','payment-bluepaid-form');
			hikashop_setTitle('Bluepaid','plugin','plugins&plugin_type=payment&task=edit&name='.$this->bluepaid);
			$app =& JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->bluepaid);
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
    	$ipList = array_merge(gethostbynamel('securepayment.bluepaid.com'),gethostbynamel('securepayment1.bluepaid.com'),gethostbynamel('securepayment2.bluepaid.com'),gethostbynamel('securepayment3.bluepaid.com'),gethostbynamel('securepayment4.bluepaid.com'),gethostbynamel('securepayment5.bluepaid.com'),gethostbynamel('securepayment6.bluepaid.com'));
    	if(!empty($ipList)){
    		$newList = array('193.33.47.34','193.33.47.35');
	    	foreach($ipList as $k => $ip){
	    		$ipParts = explode('.',$ip);
	    		if(!in_array($ip,$newList)){
	    			$newList[]=$ip;
	    		}
	    	}
	    	$ipList = $newList;
    	}
    	return $ipList;
    }
}
