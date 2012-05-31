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
class plgHikashoppaymentMoneybookers extends JPlugin
{
	var $accepted_currencies = array(
		'EUR','USD','GBP','HKD','SGD','JPY','CAD','AUD','CHF','DKK',
		'SEK','NOK','ILS','MYR','NZD','TRY','AED','MAD','QAR','SAR',
		'TWD','THB','CZK','HUF','SKK','EEK','BGN','PLN','ISK','INR',
		'LVL','KRW','ZAR','RON','HRK','LTL','JOD','OMR','RSD','TND',
	);
    var $debugData = array();
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='moneybookers' || !$method->enabled){
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
		$price = round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$currency->currency_locale['int_frac_digits']);
		if(strpos($price,'.')){
			$price =rtrim(rtrim($price, '0'), '.');
		}
		$vars = Array(
		"currency" => $currency->currency_code,
		"amount" => $price,
		);
		$lang = &JFactory::getLanguage();
		$locale=strtoupper(substr($lang->get('tag'),0,2));
		global $Itemid;
	  	$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$vars["status_url"] = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=moneybookers&tmpl=component&lang='.strtolower($locale).$url_itemid;
		$vars["transaction_id"] = $order->order_id;
		$vars["pay_from_email"]=$user->user_email;
		$vars["pay_to_email"]=$method->payment_params->email;
		$app =& JFactory::getApplication();
		$vars["recipient_description"] = $app->getCfg( 'sitename' );
		if(!in_array($locale,array('EN', 'DE', 'ES', 'FR', 'IT', 'PL', 'GR', 'RO', 'RU', 'TR', 'CN', 'CZ', 'NL', 'DA', 'SV', 'FI'))) $locale = 'EN';
		$vars["language"]=$locale;
		$vars["return_url"]=HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$url_itemid;
		$vars["return_url_text"]=JText::_('RETURN_TO_THE_STORE');
			$cancel_url =  HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$url_itemid;
		$vars["cancel_url"]=$cancel_url;
		$app =& JFactory::getApplication();
		$cart = hikashop_get('class.cart');
		$address=$app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		$type = 'billing';
		if(empty($address)){
	    	$address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
			if(!empty($address)){
				$type='shipping';
			}
		}
		if(!empty($address)){
			$cart->loadAddress($order->cart,$address,'object',$type);
			$address_type = $type.'_address';
			$vars["title"]=substr(@$order->cart->$address_type->address_title,0,3);
			$vars["firstname"]=substr(@$order->cart->$address_type->address_firstname,0,20);
			$vars["lastname"]=substr(@$order->cart->$address_type->address_lastname,0,50);
			$address1 = '';
			$address2 = '';
			if(!empty($order->cart->$address_type->address_street)){
				if(strlen($order->cart->$address_type->address_street)>100){
					$address1 = substr($order->cart->$address_type->address_street,0,100);
					$address2 = substr($order->cart->$address_type->address_street,100,200);
				}else{
					$address1 = $order->cart->$address_type->address_street;
				}
			}
			$vars["address"]=$address1;
			$vars["address2"]=$address2;
			$vars["country"]=@$order->cart->$address_type->address_country->zone_code_3;
			$vars["postal_code"]=substr(@$order->cart->$address_type->address_post_code,0,9);
			$vars["city"]=substr(@$order->cart->$address_type->address_city,0,50);
			$vars["state"]=substr(@$order->cart->$address_type->address_state->zone_name_english,0,50);
			$vars["phone_number"]=substr(@$order->cart->$address_type->address_telephone,0,20);
		}
		if(!empty($method->payment_params->logo_url)){
			$vars['logo_url']=$method->payment_params->logo_url;
		}
		$vars["detail1_description"]=JText::_('ORDER_NUMBER').' :';
		$vars["detail1_text"]=$order->order_number;

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
		$elements = $pluginsClass->getMethods('payment','moneybookers');
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
    	$vars['calculated_md5sig']=strtoupper(md5(@$element->payment_params->merchant_id.@$vars['transaction_id'].strtoupper(md5($element->payment_params->secret_word)).@$vars['mb_amount'].@$vars['mb_currency'].@$vars['status']));
		if($element->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
		}
		$orderClass = hikashop_get('class.order');
		$dbOrder = $orderClass->get((int)@$vars['transaction_id']);
		$order = null;
		$order->order_id = @$dbOrder->order_id;
		if(!empty($dbOrder)){
			$order->old_status->order_status=$dbOrder->order_status;
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
			$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
			$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		}else{
			echo "Could not load any order for your notification ".@$vars['transaction_id'];
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
				$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Moneybookers').' '.JText::sprintf('IP_NOT_VALID',$dbOrder->order_number));
				$body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Moneybookers',$ip,implode("\r\n",$element->payment_params->ips)))."\r\n\r\n".$order_text;
				$mailer->setBody($body);
				$mailer->Send();
	    		JError::raiseError( 403, JText::_( 'Access Forbidden' ));
	    		return false;
    		}
    	}
	    if (@$vars['md5sig']!=$vars['calculated_md5sig']) {
	    	$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Moneybookers').'invalid response');
			$body = JText::sprintf("Hello,\r\n A Moneybookers notification was refused because the response from the Moneybookers server was invalid")."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();
			if($element->payment_params->debug){
				echo 'invalid response'."\n\n\n";
			}
    		return false;
	    }
	    $vars['status']=(int)@$vars['status'];
		if(!in_array($vars['status'],array(0,2))) {
			if($vars['status']==-1){
				$vars['payment_status']='Cancelled';
			}elseif($vars['status']==-2){
				$vars['payment_status']='Failed';
			}elseif($vars['status']==-3){
				$vars['payment_status']='Chargeback';
			}else{
				$vars['payment_status']='Unknown';
			}
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Moneybookers',$vars['payment_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Moneybookers',$vars['payment_status']));
			$mailer->setBody($body);
			$mailer->Send();
			if($element->payment_params->debug){
				echo 'payment with code '.@$vars['status'].(!empty($vars['failed_reason_code'])?' : '.@$vars['failed_reason_code']:'')."\n\n\n";
			}
			return false;
		 }
		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified=0;
		$order->history->history_amount=@$vars['mb_amount'].@$vars['mb_currency'];
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method =$element->payment_type;
		$order->history->history_data = ob_get_clean();
		$order->history->history_type = 'payment';
	 	$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
		$currency=$currencies[$dbOrder->order_currency_id];
		$price_check = round($dbOrder->order_full_price,(int)$currency->currency_locale['int_frac_digits']);
		if(strpos($price_check,'.')){
			$price_check =rtrim(rtrim($price_check, '0'), '.');
		}
	 	$price_check.=$currency->currency_code;
	 	if($price_check != @$vars['mb_amount'].@$vars['mb_currency']){
	 		$order->order_status = $element->payment_params->invalid_status;
	 		$orderClass->save($order);
	 		$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Moneybookers').JText::_('INVALID_AMOUNT'));
			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','Moneybookers',$order->history->history_amount,$price_check))."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();
	 		return false;
	 	}
	 	if($vars['status']==2){
	 		$order->order_status = $element->payment_params->verified_status;
	 		$vars['payment_status']='Accepted';
	 		$order->history->history_notified=1;
	 	}else{
	 		$order->order_status = $element->payment_params->pending_status;
	 		$order_text ="Payment is pending\r\n\r\n".$order_text;
	 		$vars['payment_status']='Pending';
	 	}
	 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Moneybookers',$vars['payment_status']));
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Moneybookers',$vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order->order_status])."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
	 	$orderClass->save($order);
    	return true;
    }
    function onPaymentConfiguration(&$element){

	    	$this->moneybookers = JRequest::getCmd('name','moneybookers');
			if(empty($element)){
				$element = null;
	    		$element->payment_name='Moneybookers';
	    		$element->payment_description='You can pay by credit card, bank transfer, check, etc using this payment method';
	    		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
	    		$element->payment_type=$this->moneybookers;
	    		$element->payment_params=null;
	    		$element->payment_params->ips=array('91.208.28.*','72.52.0.65','83.220.158.*','91.208.28.*','213.129.65.223','213.129.65.21','91.208.28.*');
	    		$element->payment_params->url='https://www.moneybookers.com/app/payment.pl';
	    		$element->payment_params->invalid_status='cancelled';
	    		$element->payment_params->pending_status='created';
	    		$element->payment_params->verified_status='confirmed';
	    		$element = array($element);
	    	}
	    	$bar = & JToolBar::getInstance('toolbar');
			JToolBarHelper::save();
			JToolBarHelper::apply();
			JToolBarHelper::cancel();
			JToolBarHelper::divider();
			$bar->appendButton( 'Pophelp','payment-moneybookers-form');
			hikashop_setTitle('Moneybookers','plugin','plugins&plugin_type=payment&task=edit&name='.$this->moneybookers);
			$app =& JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->moneybookers);
			$this->address = hikashop_get('type.address');
			$this->category = hikashop_get('type.categorysub');
			$this->category->type = 'status';
    }
    function onPaymentConfigurationSave(&$element){
		return true;
    }





}
