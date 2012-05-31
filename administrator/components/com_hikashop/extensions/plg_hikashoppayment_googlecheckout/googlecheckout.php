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
class plgHikashoppaymentGOOGLECHECKOUT extends JPlugin
{
	var $accepted_currencies = array( 'USD', 'GBP' );
	var $error_msg = array();
	function onPaymentDisplay(&$order,&$methods,&$usable_methods) {
		if(!empty($methods)){
			foreach($methods as $method){
				if($method->payment_type!='googlecheckout' || !$method->enabled){
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
				if(!empty($order->total)){
					$currency_id = intval(@$order->total->prices[0]->price_currency_id);
					$currency = $currencyClass->getCurrencies($currency_id,$null);
					if(!empty($currency) && @$currency[$currency_id]->currency_code !== $method->payment_params->currency ) {
						return true;
					}
				}
				$usable_methods[$method->ordering] = $method;
			}
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
	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		$method =& $methods[$method_id];
		$currencyClass = hikashop::get('class.currency');
		$currencies = null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency = $currencies[$order->order_currency_id];
		hikashop::loadUser(true,true); //reset user data in case the emails were changed in the email code
		$user = hikashop::loadUser(true);
		$lang = &JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));
		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$notify_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=googlecheckout&tmpl=component&lang='.$locale;
		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=googlecheckout&tmpl=component&user_return=1&lang='.$locale;
		$app =& JFactory::getApplication();
		$address = $app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		$price = round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$currency->currency_locale['int_frac_digits']);
		$data = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
		$data .= '<checkout-shopping-cart xmlns="http://checkout.google.com/schema/2"><shopping-cart><items>';
		$data .= '<item><item-name>'.JText::_('CART_PRODUCT_TOTAL_PRICE').' #'.$order->order_id.'</item-name><item-description></item-description><unit-price currency="'.$currency->currency_code.'">'.$price.'</unit-price><quantity>1</quantity></item>';
		$data .= '</items></shopping-cart><checkout-flow-support><merchant-checkout-flow-support/></checkout-flow-support></checkout-shopping-cart>';
		if( $method->payment_params->debug ) { echo 'XML Sent to Google<pre>'.htmlentities($data).'</pre>'; }
		if( $method->payment_params->server_to_server == true ) {
			$ret =& $this->webCall('checkout', $data, $method->payment_params);
			if( $ret !== false ) {
				if( preg_match('#<redirect-url>(.*)</redirect-url>#iU', $ret, $redirect) ) {
					$redirect = html_entity_decode(trim($redirect[1]));
					$app =& JFactory::getApplication();
					$app->redirect($redirect);
				}
				if( $method->payment_params->debug ) { echo 'Google call return<pre>'.htmlentities($ret).'</pre>'; }
			} 
			$url = '';
			$vars = '';
			$app =& JFactory::getApplication();
			$app->enqueueMessage('Google Checkout error. Please log-in to the backend of Google Checkout to see the log.');
		} else {
			$vars = array(
				'signature' => base64_encode($this->signature($data, $method->payment_params->merchant_key)),
				'cart' => base64_encode($data)
			);
			if( $method->payment_params->sandbox ) {
				$url = 'https://sandbox.google.com/checkout/api/checkout/v2/checkout/Merchant/';
			} else {
				$url = 'https://checkout.google.com/api/checkout/v2/checkout/Merchant/';
			}
			$url .= $method->payment_params->merchant_id;
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
		$pluginsClass = hikashop::get('class.plugins');
		$elements = $pluginsClass->getMethods('payment','googlecheckout');
		if(empty($elements)) return false;
		$element = reset($elements);
		$compare_mer_id = '';
		$compare_mer_key = '';
		if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$compare_mer_id = $_SERVER['PHP_AUTH_USER'];
			$compare_mer_key = $_SERVER['PHP_AUTH_PW'];
		}
		if( $compare_mer_id != $element->payment_params->merchant_id || $compare_mer_key != $element->payment_params->merchant_key ) {
			header('HTTP/1.1 401 Unauthorized');
			return false;
		}
		$orderId = 0;
		$response = isset($HTTP_RAW_POST_DATA)?$HTTP_RAW_POST_DATA:file_get_contents('php://input');
		if (get_magic_quotes_gpc()) { $response = stripslashes($response); }
		$vars =& $this->parseResponse($response);
		$orderId = (int)$vars['order-num'];
		if( in_array($vars['state'], array('REVIEWING','CHARGING')) || empty($vars['state']) || in_array($vars['type'], array('risk-information-notification','charge-amount-notification')) ) {
			$this->sendAck($vars);
			exit;
		}
		$orderClass = hikashop::get('class.order');
		$dbOrder = $orderClass->get($orderId);
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".$orderId;
			header('HTTP/1.0 400 Bad Request');
			exit;
		}
		if($vars['state'] == 'CHARGEABLE') {
			if( $vars['type'] != 'authorization-amount-notification' ) {
				$this->sendAck($vars);
				exit;
			}
			$data = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
			if( $element->payment_params->charge_and_ship ) {
				$data .= '<charge-and-ship-order xmlns="http://checkout.google.com/schema/2" google-order-number="'.$vars['google-order'].'">';
			} else {
				$data .= '<charge-order xmlns="http://checkout.google.com/schema/2" google-order-number="'.$vars['google-order'].'">';
			}
			if( $vars['currency'] != '' ) {
				$data .= '<amount currency="'.$vars['currency'].'">'.$vars['amount'].'</amount>';
			}
			if( $element->payment_params->charge_and_ship ) {
				$data .= '</charge-and-ship-order>';
			} else {
				$data .= '</charge-order>';
			}
			$serial = $vars['serial'];
			$ret =& $this->webCall('request', $data, $element->payment_params);
			$vars =& $this->parseResponse($ret);
			$vars['serial'] = $serial;
			if( $vars['type'] == 'request-received' ) {
				$this->sendAck($vars);
				exit;
			}
		}
		$order = null;
		$order->order_id = $dbOrder->order_id;
		$order->old_status->order_status=$dbOrder->order_status;
		if( $dbOrder->order_status == $element->payment_params->verified_status || $dbOrder->order_status == $element->payment_params->invalid_status ) {
			$this->sendAck($vars);
			exit;
		}
		$app =& JFactory::getApplication();
		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified = 0;
		$order->history->history_amount = $vars['amount']. $vars['currency'];
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method = $element->payment_type;
		$order->history->history_data = var_export($vars, true) . "\r\n" . ob_get_clean();
		$order->history->history_type = 'payment';
		$mailer =& JFactory::getMailer();
		$config =& hikashop::config();
		$sender = array(
		    $config->get('from_email'),
		    $config->get('from_name') );
		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));
		if($vars['state'] == 'CHARGED') {
			$order->order_status = $element->payment_params->verified_status;
			$order->history->history_notified = 1;
			$payment_status = 'confirmed';
		} else {
			$order->order_status = $element->payment_params->invalid_status;
			$payment_status = 'cancelled';
			$order_text = 'Google Checkout State: ' . $vars['state'] ."\r\n". $order_text;
		}
	 	$order->mail_status = $statuses[$order->order_status];
	 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','GOOGLECHECKOUT',$payment_status));
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','GOOGLECHECKOUT',$payment_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->mail_status)."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
		$orderClass->save($order);
		$this->sendAck($vars);
		exit;
	}
	function onPaymentConfiguration(&$element){
		$this->googlecheckout = JRequest::getCmd('name','googlecheckout');
		if(empty($element)){
			$element = null;
			$element->payment_name='Google Checkout';
			$element->payment_description='You can pay by credit card using this payment method';
			$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
			$element->payment_type=$this->googlecheckout;
			$element->payment_params=null;
			$element->payment_params->login='';
			$element->payment_params->password='';
			$element->payment_params->currency = $this->accepted_currencies[0];
			$element->payment_params->ask_ccv = true;
			$element->payment_params->security = false;
			$element->payment_params->pending_status='created';
			$element->payment_params->verified_status='confirmed';
			$element = array($element);
		}
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','payment-googlecheckout-form');
		hikashop::setTitle('GOOGLECHECKOUT','plugin','plugins&plugin_type=payment&task=edit&name='.$this->googlecheckout);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->googlecheckout);
		$this->address = hikashop::get('type.address');
		$this->category = hikashop::get('type.categorysub');
		$this->category->type = 'status';
	}
	function onPaymentConfigurationSave(&$element){
		if( isset($element->payment_params->security) && $element->payment_params->security && isset($element->payment_params->security_cvv) && $element->payment_params->security_cvv ) {
			$element->payment_params->ask_ccv = true;
		}
		if( empty($element->payment_params->currency) ) {
			$element->payment_params->currency = $this->accepted_currencies[0];
		}
		return true;
	}
	function webCall($type, &$data, $params) {
		if( $type == 'request' ) {
			$called_action = 'request';
		} else if( $type == 'checkout' ) {
			if( $params->server_to_server ) {
				$called_action = 'merchantCheckout';
			} else {
				$called_action = 'checkout';
			}
		}
		if( $params->sandbox ) {
			$url = 'https://sandbox.google.com/checkout/api/checkout/v2/'.$called_action.'/Merchant/';
		} else {
			$url = 'https://checkout.google.com/api/checkout/v2/'.$called_action.'/Merchant/';
		}
		$url .= $params->merchant_id;
		$headers = array(
			'Authorization: Basic '.base64_encode($params->merchant_id.':'.$params->merchant_key),
			'Content-Type: application/xml; charset=UTF-8',
			'Accept: application/xml; charset=UTF-8',
			'User-Agent: HikaShop Google Checkout Plugin'
		);
		$session = curl_init($url);
		curl_setopt($session, CURLOPT_POST, true);
		curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($session, CURLOPT_POSTFIELDS, $data);
		curl_setopt($session, CURLOPT_HEADER, true);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
		$ret = curl_exec($session);
		curl_close($session);
		return $ret;
	}
	function parseResponse(&$xml) {
		$vars = array(
			'currency' => '',
			'amount' => 0,
			'serial' => '',
			'order-num' => '',
			'state' => ''
		);
		if( preg_match('#<(.*) xmlns="http://checkout.google.com/schema/2" serial-number=#iU', $xml, $ggreg) ) {
			$vars['type'] = trim($ggreg[1]);
		}
		if( preg_match('#serial-number="(.*)"#iU', $xml, $ggreg) ) {
			$vars['serial'] = $ggreg[1];
		}
		if( preg_match('/<item-name>.* #(.*)<\/item-name>/iU', $xml, $ggreg) ) {
			$vars['order-num'] = trim($ggreg[1]);
		}
		if( preg_match('#<google-order-number>(.*)</google-order-number>#iU', $xml, $ggreg) ) {
			$vars['google-order'] = trim($ggreg[1]);
		}
		if( preg_match('#<order-total currency="(.*)">(.*)</order-total>#iU', $xml, $ggreg) ) {
			$vars['currency'] = $ggreg[1];
			$vars['amount'] = (int)$ggreg[2];
		}
		if( preg_match('#<new-financial-order-state>(.*)</new-financial-order-state>#iU', $xml, $ggreg) ) {
			$vars['state'] = trim($ggreg[1]);
		} else if( preg_match('#<financial-order-state>(.*)</financial-order-state>#iU', $xml, $ggreg) ) {
			$vars['state'] = trim($ggreg[1]);
		}
		return $vars;
	}
	function sendAck(&$vars) {
		$acknowledgment = '<notification-acknowledgment xmlns="http://checkout.google.com/schema/2"';
		if(!empty($vars['serial'])) {
			$acknowledgment .= ' serial-number="'.$vars['serial'].'"';
		}
		$acknowledgment .= ' />';
		$msg = ob_get_clean();
		echo $acknowledgment;
		ob_start();
		echo $msg;
	}
	function signature($data, $key) {
		$blocksize = 64;
		if (strlen($key) > $blocksize) {
			$key = pack('H*', sha1($key));
		}
		$key = str_pad($key, $blocksize, chr(0x00));
		$ipad = str_repeat(chr(0x36), $blocksize);
		$opad = str_repeat(chr(0x5c), $blocksize);
		$hmac = pack(
			'H*', sha1(
				($key^$opad).pack(
					'H*', sha1(
						($key^$ipad).$data
					)
				)
			)
		);
		return $hmac;
	}
}
