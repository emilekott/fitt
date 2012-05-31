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
class plgHikashoppaymentAuthorize extends JPlugin
{
    var $debugData = array();
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='authorize' || !$method->enabled){
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
				if(!empty($currency) && @$currency[$currency_id]->currency_code != 'USD'){
					return true;
				}
				$this->needCC($method);
				$usable_methods[$method->ordering]=$method;
    		}
    	}
    	return true;
    }
	function needCC(&$method) {
		if(@$method->payment_params->api=='aim'){
			$method->ask_cc=true;
			if($method->payment_params->ask_ccv){
				$method->ask_ccv = true;
			}
			return true;
		}
		return false;
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
    function onBeforeOrderCreate(&$order,&$do){
    	$app =& JFactory::getApplication();
    	if($app->isAdmin()){
    		return true;
    	}
    	if($order->order_payment_method!='authorize'){
    		return true;
    	}
    	$db =& JFactory::getDBO();
    	$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($order->order_payment_method);
		$db->setQuery($query);
		$paymentData = $db->loadObjectList('payment_id');
		$pluginsClass = hikashop_get('class.plugins');
		$pluginsClass->params($paymentData,'payment');
		$method =& $paymentData[$order->order_payment_id];
		if(@$method->payment_params->api!='aim'){
			return true;
		}
		if(!function_exists('curl_init')){
			$app->enqueueMessage('The Authorize.net payment plugin in AIM mode needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		$vars = $this->_loadStandardVars($order,$method);
		$vars["x_delim_data"]= "TRUE";
		$vars["x_delim_char"] = "|";
		$this->cc_number = $app->getUserState( HIKASHOP_COMPONENT.'.cc_number');
		if(!empty($this->cc_number)){
			$this->cc_number = base64_decode($this->cc_number);
		}
		$this->cc_month = $app->getUserState( HIKASHOP_COMPONENT.'.cc_month');
		if(!empty($this->cc_month)){
			$this->cc_month = base64_decode($this->cc_month);
		}
		$this->cc_year = $app->getUserState( HIKASHOP_COMPONENT.'.cc_year');
		if(!empty($this->cc_year)){
			$this->cc_year = base64_decode($this->cc_year);
		}
		$vars["x_card_num"] = $this->cc_number;
		if($method->payment_params->ask_ccv){
			$this->cc_CCV = $app->getUserState( HIKASHOP_COMPONENT.'.cc_CCV');
			if(!empty($this->cc_CCV)){
				$this->cc_CCV = base64_decode($this->cc_CCV);
			}
			$vars["x_card_code"] = $this->cc_CCV;
		}
		$vars["x_exp_date"] = $this->cc_month.$this->cc_year;
		$vars["x_tran_key"] = $method->payment_params->transaction_key;
		$post_string = "";
		foreach( $vars as $key => $value ){
			if(is_array($value)){
				foreach($value as $v){
					$post_string .= "$key=" . urlencode( $v ) . "&";
				}
			}else{
				$post_string .= "$key=" . urlencode( $value ) . "&";
			}
		}
		$post_string = rtrim( $post_string, "& " );
		$request = curl_init($method->payment_params->url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
		$post_response = curl_exec($request);
		curl_close ($request);
		if(empty($post_response)){
			$app->enqueueMessage('The connection to the payment plateform did not succeed. It is often caused by the hosting company blocking external connections so you should contact him for further guidance.','error');
			return false;
		}
		$response_array = explode("|",$post_response);
		$response_code        = (int)@$response_array[0];
		$response_subcode     = @$response_array[1];
		$response_reason_code = @$response_array[2];
		$response_reason_text = @$response_array[3];
		$this->response_code = $response_code;
		switch($response_code) {
			case 2:
				$app->enqueueMessage(JText::_('TRANSACTION_DECLINED_WRONG_CARD'));
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_number','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_month','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_year','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_owner','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_valid',0);
				$do = false;
				break;
			case 3:
			default:
				$app->enqueueMessage(JText::sprintf('TRANSACTION_PROCESSING_ERROR',$response_reason_code.' '.$response_reason_text));
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_number','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_month','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_year','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_owner','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_valid',0);
				$do = false;
				break;
    		case 1:
    			$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
				$order->history->history_notified=0;
				$order->history->history_amount= round($order->cart->full_total->prices[0]->price_value_with_tax,2).'USD';
				$order->history->history_payment_id = $order->order_payment_id;
				$order->history->history_payment_method =$order->order_payment_method;
				$order->history->history_data = '';
				$order->history->history_type = 'payment';
				$order->order_status = $method->payment_params->verified_status;
				break;
    		case 4:
				$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
				$order->history->history_notified=0;
				$order->history->history_amount= round($order->cart->full_total->prices[0]->price_value_with_tax,2).'USD';
				$order->history->history_payment_id = $order->order_payment_id;
				$order->history->history_payment_method =$order->order_payment_method;
				$order->history->history_data = '';
				$order->history->history_type = 'payment';
				$order->order_status = $element->payment_params->pending_status;
				break;
		}
		return true;
    }
    function onAfterOrderCreate(&$order,&$send_email){
    	$app =& JFactory::getApplication();
    	if($app->isAdmin()){
    		return true;
    	}
    	if($order->order_payment_method!='authorize'){
    		return true;
    	}
    	$db =& JFactory::getDBO();
    	$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($order->order_payment_method);
		$db->setQuery($query);
		$paymentData = $db->loadObjectList('payment_id');
		$pluginsClass = hikashop_get('class.plugins');
		$pluginsClass->params($paymentData,'payment');
		$method =& $paymentData[$order->order_payment_id];
		if(@$method->payment_params->api!='aim'){
			return true;
		}
		if(!empty($this->response_code)){
			switch($this->response_code){
				case 1:
					$mailer =& JFactory::getMailer();
					$config =& hikashop_config();
					$sender = array(
					    $config->get('from_email'),
					    $config->get('from_name') );
					$mailer->setSender($sender);
					$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));
					$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
					$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$order->order_number,HIKASHOP_LIVE);
					$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
					$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Authorize.net','Accepted'));
					$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Authorize.net','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;
					$mailer->setBody($body);
					$mailer->Send();
					break;
				case 4:
					$mailer =& JFactory::getMailer();
					$config =& hikashop_config();
					$sender = array(
					    $config->get('from_email'),
					    $config->get('from_name') );
					$mailer->setSender($sender);
					$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));
					$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
					$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$order->order_number,HIKASHOP_LIVE);
					$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
					$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Authorize.net','Pending'));
					$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Authorize.net','Pending')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;
					$mailer->setBody($body);
					$mailer->Send();
					break;
			}
		}
    }
    function _loadStandardVars(&$order,&$method){
		$tax_total = '';
		$discount_total = '';
		$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency=$currencies[$order->order_currency_id];
		$user = hikashop_loadUser(true);
		$debug = $method->payment_params->debug;
		$vars = Array(
		"x_amount" => round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$currency->currency_locale['int_frac_digits']),
		"x_version" => '3.1',
		"x_test_request" => $debug,
		);
		$vars["x_relay_response"] = 'FALSE';
		$vars["x_customer_ip"] = $order->order_ip;
		$vars["x_type"] = 'AUTH_CAPTURE'; // AUTH_ONLY would be able to just ask for the availability of money and it would be necessayr to make another request in CURL after the order is ready to ship with the same transaction id
		$vars["x_login"] = $method->payment_params->login_id;
		if(!empty($order->order_id)){
			$vars["x_invoice_num"] = $order->order_id;
			$vars["x_po_num"] = $vars["x_invoice_num"];
		}
		$vars["x_email"]=$user->user_email;
		$app =& JFactory::getApplication();
		$cart = hikashop_get('class.cart');
		$address=$app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		if(!empty($address)){
			$cart->loadAddress($order->cart,$address,'object','billing');
			$vars["x_first_name"]=substr(@$order->cart->billing_address->address_firstname,0,50);
			$vars["x_last_name"]=substr(@$order->cart->billing_address->address_lastname,0,50);
			$vars["x_address"]=substr(@$order->cart->billing_address->address_street,0,60);
			$vars["x_company"]=substr(@$order->cart->billing_address->address_company,0,50);
			$vars["x_country"]=substr(@$order->cart->billing_address->address_country->zone_name_english,0,60);
			$vars["x_zip"]=substr(@$order->cart->billing_address->address_post_code,0,20);
			$vars["x_city"]=substr(@$order->cart->billing_address->address_city,0,40);
			$vars["x_state"]=substr(@$order->cart->billing_address->address_state->zone_name_english,0,40);
			$vars["x_phone"]=substr(@$order->cart->billing_address->address_telephone,0,25);
		}
    	$address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
		if(!empty($address)){
			$cart->loadAddress($order->cart,$address,'object','shipping');
			$vars["x_ship_to_first_name"]=substr(@$order->cart->shipping_address->address_firstname,0,50);
			$vars["x_ship_to_last_name"]=substr(@$order->cart->shipping_address->address_lastname,0,50);
			$vars["x_ship_to_address"]=substr(@$order->cart->shipping_address->address_street,0,60);
			$vars["x_ship_to_company"]=substr(@$order->cart->shipping_address->address_company,0,50);
			$vars["x_ship_to_country"]=substr(@$order->cart->shipping_address->address_country->zone_name_english,0,60);
			$vars["x_ship_to_zip"]=substr(@$order->cart->shipping_address->address_post_code,0,20);
			$vars["x_ship_to_city"]=substr(@$order->cart->shipping_address->address_city,0,40);
			$vars["x_ship_to_state"]=substr(@$order->cart->shipping_address->address_state->zone_name_english,0,40);
		}
		$i = 1;
		$tax = 0;
		$vars["x_line_item"]=array();
		foreach($order->cart->products as $product){
			if(bccomp($product->order_product_tax,0,5)){
				$tax+=$product->order_product_quantity*round($product->order_product_tax,(int)$currency->currency_locale['int_frac_digits']);
				$has_tax = 'TRUE';
			}else{
				$has_tax = 'FALSE';
			}
			$vars["x_line_item"][]=substr($product->order_product_code,0,30).'<|>'.substr(strip_tags($product->order_product_name),0,30).'<|><|>'.$product->order_product_quantity.'<|>'.round($product->order_product_price,(int)$currency->currency_locale['int_frac_digits']).'<|>'.$has_tax;
		}
		if(!empty($order->cart->coupon) && @$order->cart->coupon->discount_value>0){
			$vars["x_line_item"][]='coupon<|>'.JText::_('HIKASHOP_COUPON').'<|><|>1<|>'.round($order->cart->coupon->discount_value,(int)$currency->currency_locale['int_frac_digits']).'<|>N';
		}
		if(bccomp($tax,0,5)){
			$vars['x_tax']=$tax;
			$vars['x_tax_exempt']='FALSE';
		}else{
			$vars['x_tax_exempt']='TRUE';
		}
		if(!empty($order->order_shipping_price)){
			$vars["x_freight"]=round($order->order_shipping_price,(int)$currency->currency_locale['int_frac_digits']);
		}
		return $vars;
    }
    function onAfterOrderConfirm(&$order,&$methods,$method_id){
    	$method =& $methods[$method_id];
    	if(@$method->payment_params->api=='aim'){
    		$viewType='_thankyou';
			global $Itemid;
			$url_itemid='';
			if(!empty($Itemid)){
				$url_itemid='&Itemid='.$Itemid;
			}
			$return_url =  HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$url_itemid;
			$this->removeCart = true;
    	}else{
			$vars = $this->_loadStandardVars($order,$method);
			$viewType = '_end';
			$vars["x_show_form"] = 'PAYMENT_FORM';
			if(@$method->payment_params->notification){
				$vars["x_relay_response"] = 'TRUE';
				$lang = &JFactory::getLanguage();
				$locale=strtolower(substr($lang->get('tag'),0,2));
				global $Itemid;
				$url_itemid='';
				if(!empty($Itemid)){
					$url_itemid='&Itemid='.$Itemid;
				}
				$vars["x_relay_url"] = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=authorize&tmpl=component&lang='.$locale.$url_itemid;
			}
			$vars["x_fp_sequence"] = $vars["x_invoice_num"];
			$vars["x_fp_timestamp"] = time();
			$vars["x_fp_hash"] = hash_hmac("md5", $vars["x_login"] . "^" . $vars["x_fp_sequence"] . "^" . $vars["x_fp_timestamp"] . "^" . $vars["x_amount"] . "^", $method->payment_params->transaction_key);
			if(!empty($method->payment_params->x_logo_url)){
				$vars['x_logo_url']=$method->payment_params->x_logo_url;
			}
    	}
		JHTML::_('behavior.mootools');
		$app =& JFactory::getApplication();
		$name = $method->payment_type.$viewType.'.php';
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
		$elements = $pluginsClass->getMethods('payment','authorize');
		if(empty($elements)) return JText::_('ORDER_VALID_AFTER_PAYMENT');
		$element = reset($elements);
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$return_url =  HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$url_itemid;
    	$vars = array();
    	$data = array();
    	$filter = & JFilterInput::getInstance();
    	foreach($_POST as $key => $value){
    		$key = $filter->clean($key);
    		$value = JRequest::getString($key);
    		$vars[$key]=$value;
    	}
		$app =& JFactory::getApplication();
		$name = $element->payment_type.'_thankyou.php';
    	$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashoppayment'.DS.$name;
    	if(!file_exists($path)){
    		if(version_compare(JVERSION,'1.6','<')){
    			$path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$name;
    		}else{
    			$path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$element->payment_type.DS.$name;
    		}
    		if(!file_exists($path)){
    		}
    	}
    	ob_start();
    	require($path);
    	$msg = ob_get_clean();
		if(!$element->payment_params->notification){
			echo 'Notification not activated for authorize.net';
			return $msg;
		}
    	$vars['x_MD5_Hash_calculated']=$this->md5Hash(@$element->payment_params->md5_hash,@$element->payment_params->login_id,@$vars['x_trans_id'],@$vars['x_amount']);
		if($element->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
		}
		$orderClass = hikashop_get('class.order');
		$dbOrder = $orderClass->get((int)@$vars['x_po_num']);
		$order = null;
		$order->order_id = @$dbOrder->order_id;
		if(!empty($dbOrder)){
			$order->old_status->order_status=$dbOrder->order_status;
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
			$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
			$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		}else{
			echo "Could not load any order for your notification ".@$vars['x_po_num'];
			return $msg;
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
	    if (@$vars['x_MD5_Hash']!=$vars['x_MD5_Hash_calculated']) {
	    	$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Authorize.net').'invalid response');
			$body = JText::sprintf("Hello,\r\n An Authorize.net notification was refused because the response from the Authorize.net server was invalid")."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();
			if($element->payment_params->debug){
				echo 'invalid md5'."\n\n\n";
			}
    		return $msg;
	    }
	    $vars['x_response_code']=(int)@$vars['x_response_code'];
		if(!in_array($vars['x_response_code'],array(1,4))) {
			if($vars['x_response_code']==2){
				$vars['payment_status']='Declined';
			}else{
				$vars['payment_status']='Error';
			}
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Authorize.net',$vars['payment_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Authorize.net',$vars['payment_status']));
			$mailer->setBody($body);
			$mailer->Send();
			if($element->payment_params->debug){
				echo 'payment with code '.@$vars['x_response_code'].' : '.@$vars['x_response_reason_text']."\n\n\n";
			}
			return $msg;
		 }
		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified=0;
		$order->history->history_amount=@$vars['x_amount'].'USD';
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method =$element->payment_type;
		$order->history->history_data = ob_get_clean();
		$order->history->history_type = 'payment';
	 	$price_check = round($dbOrder->order_full_price, 2 );
	 	if($price_check != @$vars['x_amount']){
	 		$order->order_status = $element->payment_params->invalid_status;
	 		$orderClass->save($order);
	 		$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Authorize.net').JText::_('INVALID_AMOUNT'));
			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','Authorize.net',$order->history->history_amount,$price_check.'USD'))."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();
	 		return $msg;
	 	}
	 	if($vars['x_response_code']==1){
	 		$order->order_status = $element->payment_params->verified_status;
	 		$vars['payment_status']='Accepted';
	 		$order->history->history_notified=1;
	 	}else{
	 		$order->order_status = $element->payment_params->pending_status;
	 		$order_text =@$vars['x_response_reason_text']."\r\n\r\n".$order_text;
	 		$vars['payment_status']='Pending';
	 	}
	 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Authorize.net',$vars['payment_status']));
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Authorize.net',$vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order->order_status])."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
	 	$orderClass->save($order);
    	return $msg;
    }
    function onPaymentConfiguration(&$element){
    	$subtask = JRequest::getCmd('subtask','');
    	$this->authorize = JRequest::getCmd('name','authorize');
		if(empty($element)){
			$element = null;
    		$element->payment_name='Authorize';
    		$element->payment_description='You can pay by credit card using this payment method';
    		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
    		$element->payment_type=$this->authorize;
    		$element->payment_params=null;
    		$element->payment_params->url='https://secure.authorize.net/gateway/transact.dll';
    		$element->payment_params->api='sim';
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
		$bar->appendButton( 'Pophelp','payment-authorize-form');
		hikashop_setTitle('Authorize','plugin','plugins&plugin_type=payment&task=edit&name='.$this->authorize);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->authorize);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
    }
    function onPaymentConfigurationSave(&$element){
		return true;
    }





	function md5Hash($md5Hash, $login_id, $trans_id, $amount) {
		if ($amount == '' || $amount == '0'){
			$amount = '0.00';
		}
		return strtoupper(md5($md5Hash.$login_id.$trans_id.$amount));
	}
}
if(!function_exists('hash_hmac')){
	function hash_hmac($algo, $data, $key, $raw_output = false){
		$algo = strtolower($algo);
		$pack = 'H'.strlen($algo('test'));
		$size = 64;
		$opad = str_repeat(chr(0x5C), $size);
		$ipad = str_repeat(chr(0x36), $size);
		if (strlen($key) > $size) {
			$key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
		} else {
			$key = str_pad($key, $size, chr(0x00));
		}
		for ($i = 0; $i < strlen($key) - 1; $i++) {
			$opad[$i] = $opad[$i] ^ $key[$i];
			$ipad[$i] = $ipad[$i] ^ $key[$i];
		}
		$output = $algo($opad.pack($pack, $algo($ipad.$data)));
		return ($raw_output) ? pack($pack, $output) : $output;
	}
}