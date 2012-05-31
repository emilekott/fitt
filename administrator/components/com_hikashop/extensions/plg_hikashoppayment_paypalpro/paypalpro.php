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
class plgHikashoppaymentPaypalpro extends JPlugin
{
	var $accepted_currencies = array( 'USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD' );
	function onPaymentDisplay(&$order,&$methods,&$usable_methods) {
		if(!empty($methods)){
			foreach($methods as $method){
				if($method->payment_type!='paypalpro' || !$method->enabled){
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
					if(!empty($currency) && !in_array(@$currency[$currency_id]->currency_code,$this->accepted_currencies)) {
						return true;
					}
				}
				$this->needCC($method);
				$usable_methods[$method->ordering] = $method;
			}
		}
		return true;
	}
	function needCC(&$method) {
		$method->ask_cc = true;
		if( $method->payment_params->ask_ccv ) {
			$method->ask_ccv = true;
		}
		return true;
	}
	function onPaymentSave(&$cart,&$rates,&$payment_id) {
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
	function onBeforeOrderCreate(&$order, &$do) {
		$app =& JFactory::getApplication();
		if($app->isAdmin()) {
			return true;
		}
		if($order->order_payment_method != 'paypalpro') {
			return true;
		}
		if(!function_exists('curl_init')){
			$app->enqueueMessage('The PayPal Pro payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($order->order_payment_method);
		$db->setQuery($query);
		$paymentData = $db->loadObjectList('payment_id');
		$pluginsClass = hikashop_get('class.plugins');
		$pluginsClass->params($paymentData,'payment');
		$method =& $paymentData[$order->order_payment_id];
		$currencyClass = hikashop_get('class.currency');
		$currencies = null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency = $currencies[$order->order_currency_id];
		$user = hikashop_loadUser(true);
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
		if( $method->payment_params->ask_ccv ) {
			$this->cc_CCV = $app->getUserState( HIKASHOP_COMPONENT.'.cc_CCV');
			if(!empty($this->cc_CCV)){
				$this->cc_CCV = base64_decode($this->cc_CCV);
			}
		} else {
			$this->cc_CCV = '';
		}
		$billing_address = $app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		$shipping_address = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
		$cart = hikashop_get('class.cart');
		$cart->loadAddress($order->cart,$billing_address,'object','billing');
		$cart->loadAddress($order->cart,$shipping_address,'object','shipping');
		$amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','');
		$vars = array(
			'USER' => $method->payment_params->login,
			'PWD' => $method->payment_params->password,
			'SIGNATURE' => $method->payment_params->signature,
			'VERSION' => '51.0',
			'METHOD' => 'DoDirectPayment',
			'PAYMENTACTION' => $method->payment_params->instant_capture?'Sale':'Authorization',
			'AMT' => $amount,
			'ACCT' => $this->cc_number,
			'EXPDATE' => $this->cc_month.'20'.$this->cc_year,
			'FIRSTNAME' => $order->cart->billing_address->address_firstname,
			'LASTNAME' => $order->cart->billing_address->address_lastname,
			'CURRENCYCODE' => $currency->currency_code,
			'EMAIL' => $user->user_email,
			'STREET' => @$order->cart->billing_address->address_street,
			'STREET2' => @$order->cart->billing_address->address_street2,
			'CITY' => @$order->cart->billing_address->address_city,
			'STATE' => @$order->cart->billing_address->address_state->zone_name,
			'COUNTRYCODE' => @$order->cart->billing_address->address_country->zone_code_2,
			'ZIP' => @$order->cart->billing_address->address_post_code,
			'BN' => 'HikariSoftware_Cart_DP'
		);
		if(!empty($order->cart->billing_address->address_street2)){
			$vars['STREET2'] = substr($order->cart->billing_address->address_street2,0,99);
		}
		if(!empty($order->cart->shipping_address)){
			$vars['SHIPTONAME'] = @$order->cart->shipping_address->address_firstname.' '.@$order->cart->shipping_address->address_lastname;
			$vars['SHIPTOSTREET'] = @$order->cart->shipping_address->address_street;
			$vars['SHIPTOSTREET2'] = @$order->cart->shipping_address->address_street2;
			$vars['SHIPTOCITY'] = @$order->cart->shipping_address->address_city;
			$vars['SHIPTOSTATE'] = @$order->cart->shipping_address->address_state->zone_name;
			$vars['SHIPTOCOUNTRY'] = @$order->cart->shipping_address->address_country->zone_code_2;
			$vars['SHIPTOZIP'] = @$order->cart->shipping_address->address_post_code;
			$vars['SHIPTOPHONENUM'] = @$order->cart->shipping_address->address_phone;
		}
		$i = 1;
		$tax = 0;
		foreach($order->cart->products as $product){
			$vars["L_NAME".$i]=substr(strip_tags($product->order_product_name),0,127);
			$vars["L_NUMBER".$i]=$product->order_product_code;
			$vars["L_AMT".$i]=round($product->order_product_price,(int)$currency->currency_locale['int_frac_digits']);
			$vars["L_QTY".$i]=$product->order_product_quantity;
			$vars["L_TAXAMT".$i]=round($product->order_product_tax,(int)$currency->currency_locale['int_frac_digits']);
			$tax+=round($product->order_product_tax,(int)$currency->currency_locale['int_frac_digits'])*$product->order_product_quantity;
			$i++;
		}
		if(bccomp($tax,0,5)){
			$vars['TAXAMT']=$tax+$order->order_shipping_tax-$order->order_discount_tax;
		}
		if(!empty($order->order_shipping_price) && bccomp($order->order_shipping_price,0,5)){
			$vars['SHIPPINGAMT']=round($order->order_shipping_price,(int)$currency->currency_locale['int_frac_digits']);
		}
		$vars['ITEMAMT']=$vars['AMT']-(@$vars['TAXAMT']+@$vars['SHIPPINGAMT']);
		if( $method->payment_params->ask_ccv ) {
			$vars['CVV2'] = $this->cc_CCV;
		}
		if( $method->payment_params->debug ) {
			echo print_r($vars, true) . "\n\n\n";
		}
		$session = curl_init();
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($session, CURLOPT_POST,           1);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_VERBOSE,        1);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($session, CURLOPT_FAILONERROR,    true);
		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$url = 'api-3t.paypal.com/nvp';
		if( $method->payment_params->environnement != 'production' ) {
			$url = 'api-3t.'.$method->payment_params->environnement.'.paypal.com/nvp';
		}
		if( $method->payment_params->debug ) {
			echo print_r($url, true) . "\n\n\n";
		}
		$tmp = array();
		foreach($vars as $k => $v) {
			$tmp[] = $k . '=' . urlencode(trim($v));
		}
		$tmp = implode('&', $tmp);
		curl_setopt($session, CURLOPT_URL, 'https://' . $url);
		curl_setopt($session, CURLOPT_REFERER, $httpsHikashop);
		curl_setopt($session, CURLOPT_POSTFIELDS, $tmp);
		$ret = curl_exec($session);
		$error = curl_errno($session);
		curl_close($session);
		if( !$error ) {
			$params = explode('&', $ret);
			$ret = array();
			foreach($params as $p) {
				$t = explode('=', $p);
				$ret[strtoupper($t[0])] = $t[1];
			}
			if( $method->payment_params->debug ) {
				$app->enqueueMessage(nl2br(print_r($ret, true)));
			}
			$responseCode = null;
			if( isset($ret['ACK']) ) {
				$responseCode = strtoupper($ret['ACK']);
			}
			if( isset($responseCode) ) {
				if( $responseCode == 'SUCCESS' || $responseCode == 'SUCCESSWITHWARNING' ) {
					$order->history->history_reason = JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
					$order->history->history_notified = 0;
					$order->history->history_amount = $amount . $this->accepted_currencies[0];
					$order->history->history_payment_id = $method->payment_id;
					$order->history->history_payment_method = $method->payment_type;
					$order->history->history_data = ob_get_clean();
					$order->history->history_type = 'payment';
					$order->order_status = $method->payment_params->verified_status;
					$mailer =& JFactory::getMailer();
					$config =& hikashop_config();
					$sender = array(
						$config->get('from_email'),
						$config->get('from_name') );
					$mailer->setSender($sender);
					$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));
					$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
					$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE','',HIKASHOP_LIVE);
					$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
					$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Paypal Pro','Accepted'));
					$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Paypal Pro','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;
					$mailer->setBody($body);
					$mailer->Send();
				} else {
					$message = 'Error';
					if(!empty($ret['ERRORCODE'])){
						$message.=' '.$ret['ERRORCODE'];
					}elseif(!empty($ret['L_ERRORCODE0'])){
						$message.=' '.$ret['L_ERRORCODE0'];
					}
					if(!empty($ret['LONGMESSAGE'])){
						$message.=': '.urldecode($ret['LONGMESSAGE']);
					}elseif(!empty($ret['L_LONGMESSAGE0'])){
						$message.=': '.urldecode($ret['L_LONGMESSAGE0']);
					}
					$app->enqueueMessage($message);
					$do = false;
				}
			} else {
				$app->enqueueMessage('An error occurred. No response code in PayPal Pro server\'s response');
				$do = false;
			}
		} else {
			$app->enqueueMessage('An error occurred. The connection to the PayPal Pro server could not be established');
			$do = false;
		}
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_number','');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_month','');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_year','');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV','');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_valid',0);
		return true;
	}
	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		$method =& $methods[$method_id];
		$this->removeCart = true;
		JHTML::_('behavior.mootools');
		$app =& JFactory::getApplication();
		$name = $method->payment_type.'_thanks.php';
		if(!empty($method->payment_params->return_url)){
			$return_url = $method->payment_params->return_url;
		}
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
	function onPaymentConfiguration(&$element){
		$this->paypalpro = JRequest::getCmd('name','paypalpro');
		if(empty($element)){
			$element = null;
			$element->payment_name='PayPal Pro';
			$element->payment_description='You can pay by credit card using this payment method';
			$element->payment_images='MasterCard,VISA,Credit_card';
			$element->payment_type=$this->paypalpro;
			$element->payment_params=null;
			$element->payment_params->login='';
			$element->payment_params->password='';
			$element->payment_params->ask_ccv = true;
			$element->payment_params->pending_status='created';
			$element->payment_params->verified_status='confirmed';
			$element = array($element);
		}
		$obj = reset($element);
		$field = '';
		if(empty($obj->payment_params->login)){
			$field = JText::_( 'USERNAME' );
		}elseif(empty($obj->payment_params->password)){
			$field = JText::_( 'PASSWORD' );
		}elseif(empty($obj->payment_params->signature)){
			$field = JText::_( 'SIGNATURE' );
		}
		if(!empty($field)){
			$app =& JFactory::getApplication();
			$lang = &JFactory::getLanguage();
			$locale=strtolower(substr($lang->get('tag'),0,2));
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED','PayPal Pro',$field,'PayPal Pro','https://www.paypal.com/'.$locale.'/mrb/pal=SXL9FKNKGAEM8'));
		}
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','payment-paypalpro-form');
		hikashop_setTitle('PAYPALPRO','plugin','plugins&plugin_type=payment&task=edit&name='.$this->paypalpro);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->paypalpro);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
	}
	function onPaymentConfigurationSave(&$element){
		return true;
	}
}
