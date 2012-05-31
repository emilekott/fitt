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
class plgHikashoppaymentPayJunction extends JPlugin
{
	var $accepted_currencies = array( 'USD' );
	var $error_msg = array(
		'00' => 'Transaction was approved.',
		'85' => 'Transaction was approved.',
		'FE' => 'There was a format error with your Trinity Gateway Service (API) request.',
		'AE' => 'Address verification failed because address did not match.',
		'ZE' => 'Address verification failed because zip did not match.',
		'XE' => 'Address verification failed because zip and address did not match.',
		'YE' => 'Address verification failed because zip and address did not match.',
		'OE' => 'Address verification failed because address or zip did not match..',
		'UE' => 'Address verification failed because cardholder address unavailable.',
		'RE' => 'Address verification failed because address verification system is not working',
		'SE' => 'Address verification failed because address verification system is unavailable',
		'EE' => 'Address verification failed because transaction is not a mail or phone order.',
		'GE' => 'Address verification failed because international support is unavailable.',
		'CE' => 'Declined because CVV2/CVC2 code did not match.',
		'NL' => 'Aborted because of a system error, please try again later.',
		'AB' => 'Aborted because of an upstream system error, please try again later.',
		'04' => 'Declined. Pick up card.',
		'07' => 'Declined. Pick up card (Special Condition).',
		'41' => 'Declined. Pick up card (Lost).',
		'43' => 'Declined. Pick up card (Stolen).',
		'13' => 'Declined because of the amount is invalid.',
		'14' => 'Declined because the card number is invalid.',
		'80' => 'Declined because of an invalid date.',
		'05' => 'Declined. Do not honor.',
		'51' => 'Declined because of insufficient funds.',
		'N4' => 'Declined because the amount exceeds issuer withdrawal limit.',
		'61' => 'Declined because the amount exceeds withdrawal limit.',
		'62' => 'Declined because of an invalid service code (restricted).',
		'65' => 'Declined because the card activity limit exceeded.',
		'93' => 'Declined because there a violation (the transaction could not be completed).',
		'06' => 'Declined because address verification failed.',
		'54' => 'Declined because the card has expired.',
		'15' => 'Declined because there is no such issuer.',
		'96' => 'Declined because of a system error.',
		'N7' => 'Declined because of a CVV2/CVC2 mismatch.',
		'M4' => 'Declined.',
		'DT' => 'Duplicate Transaction'
	);
	function onPaymentDisplay(&$order,&$methods,&$usable_methods) {
		if(!empty($methods)){
			foreach($methods as $method){
				if($method->payment_type!='payjunction' || !$method->enabled){
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
		$method->ask_owner = true;
		if( $method->payment_params->ask_ccv || ($method->payment_params->security && $method->payment_params->security_cvv) ) {
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
		if($order->order_payment_method != 'payjunction') {
			return true;
		}
		if(!function_exists('curl_init')){
			$app->enqueueMessage('The PayJunction payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
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
		$this->cc_owner = $app->getUserState( HIKASHOP_COMPONENT.'.cc_owner');
		if(!empty($this->cc_owner)){
			$this->cc_owner = base64_decode($this->cc_owner);
		}
		if( $method->payment_params->ask_ccv ) {
			$this->cc_CCV = $app->getUserState( HIKASHOP_COMPONENT.'.cc_CCV');
			if(!empty($this->cc_CCV)){
				$this->cc_CCV = base64_decode($this->cc_CCV);
			}
		} else {
			$this->cc_CCV = '';
		}
		ob_start();
		$dbg = '';
		$address = $app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		$address_type = 'billing_address';
		$cart = hikashop_get('class.cart');
		$cart->loadAddress($order->cart,$address,'object','billing');
		$uuid = uniqid('');
		$amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','');
		$vars = array (
			"dc_logon" => $method->payment_params->login
			,"dc_password" => $method->payment_params->password
			,"dc_version" => "1.2"
			,"dc_transaction_type" => "AUTHORIZATION_CAPTURE"
			,"dc_transaction_amount" => $amount
			,"dc_address" => @$order->cart->$address_type->address_street
			,"dc_city" => @$order->cart->$address_type->address_city
			,"dc_state" => @$order->cart->$address_type->address_state->zone_name
			,"dc_zipcode" => @$order->cart->$address_type->address_post_code
			,"dc_name" => $this->cc_owner
			,"dc_number" => $this->cc_number
			,"dc_expiration_month" => $this->cc_month
			,"dc_expiration_year" => $this->cc_year
			,"dc_verification_number" => $this->cc_CCV
			,"dc_schedule_create" => ''
			,"dc_schedule_limit" => ''
			,"dc_schedule_periodic_number" => ''
			,"dc_schedule_periodic_type" => ''
			,"dc_schedule_start" => ''
			,"dc_transaction_id" => ''
		);
		if( $method->payment_params->security ) {
			$vars['dc_security'] = $method->payment_params->security_avs . '|' .
				($method->payment_params->security_cvv?'M':'I') . '|' .
				($method->payment_params->security_preauth?'true':'false') . '|' .
				($method->payment_params->security_avsforce?'true':'false') . '|' .
				($method->payment_params->security_cvvforce?'true':'false') ;
		}
		$tmp = array();
		foreach($vars as $k => $v) {
			$tmp[] = $k . '=' . urlencode(trim($v));
		}
		$vars = implode('&', $tmp);
		$session = curl_init();
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($session, CURLOPT_POST,           1);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$domain = $method->payment_params->domain;
		$url = '/quick_link';
		curl_setopt($session, CURLOPT_URL, 'https://' . $domain . $url);
		curl_setopt($session, CURLOPT_POSTFIELDS, $vars);
		$ret = curl_exec($session);
		$error = curl_errno($session);
		$err_msg = curl_error($session);;
		curl_close($session);
		if( !empty($ret) ) {
			$ret = explode(chr(28), $ret);
			$result = array();
			if( is_array($ret) ) {
				foreach ($ret as $kv) {
					list ($k, $v) = explode("=", $kv);
					$result[$k] = $v;
				}
			}
			if( $method->payment_params->debug ) {
				echo print_r($result, true) . "\n\n\n";
			}
			if( isset($result['dc_response_code']) ) {
				$rc = $result['dc_response_code'];
				if( $rc == '00' || $rc == '85' ) {
					$do = true;
					$dbg .= ob_get_clean();
					if( !empty($dbg) ) $dbg .= "\r\n";
					ob_start();
					$order->history->history_reason = JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
					$order->history->history_notified = 0;
					$order->history->history_amount = $amount . $this->accepted_currencies[0];
					$order->history->history_payment_id = $method->payment_id;
					$order->history->history_payment_method = $method->payment_type;
					$order->history->history_data = $dbg . 'Authorization Code: ' . @$result['dc_approval_code'] . "\r\n" . 'Transaction ID: ' . @$result['dc_transaction_id'];
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
					$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','PayJunction','Accepted'));
					$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','PayJunction','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;
					$mailer->setBody($body);
					$mailer->Send();
				} else {
					if( isset($this->error_msg[$rc]) ) {
						$app->enqueueMessage($this->error_msg[$rc]);
					} else {
						$app->enqueueMessage('Error');
					}
					if( isset($result['dc_response_message']) ) {
						$app->enqueueMessage( $result['dc_response_message'] );
					}
					$do = false;
				}
			} else {
				$app->enqueueMessage('An error occurred.');
				$do = false;
			}
		} else {
			$do = false;
		}
		if( $error != 0 ) {
			$app->enqueueMessage('There was an error during the connection with the PayJunction payment gateway');
			if( $method->payment_params->debug ) {
				echo 'Curl Err [' . $error . '] : ' . $err_msg . "\n\n\n";
			}
		}
		$dbg .= ob_get_clean();
		if(!empty($dbg)){
			$dbg = '-- ' . date('m.d.y H:i:s') . ' --' . "\r\n" . $dbg;
			$config =& hikashop::config();
			jimport('joomla.filesystem.file');
			$file = $config->get('payment_log_file','');
			$file = rtrim(JPath::clean(html_entity_decode($file)),DS.' ');
			if(!preg_match('#^([A-Z]:)?/.*#',$file)){
				if(!$file[0]=='/' || !file_exists($file)){
					$file = JPath::clean(HIKASHOP_ROOT.DS.trim($file,DS.' '));
				}
			}
			if(!empty($file) && defined('FILE_APPEND')){
				if (!file_exists(dirname($file))) {
					jimport('joomla.filesystem.folder');
					JFolder::create(dirname($file));
				}
				file_put_contents($file,$dbg,FILE_APPEND);
			}
		}
		if( $error != 0 ) {
			return true;
		}
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_number','');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_month','');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_year','');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV','');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_owner','');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_valid',0);
		return true;
	}
	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		$method =& $methods[$method_id];
		JHTML::_('behavior.mootools');
		$app =& JFactory::getApplication();
		$this->removeCart = true;
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
		$this->payjunction = JRequest::getCmd('name','payjunction');
		if(empty($element)){
			$element = null;
			$element->payment_name='PayJunction';
			$element->payment_description='You can pay by credit card using this payment method';
			$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
			$element->payment_type=$this->payjunction;
			$element->payment_params=null;
			$element->payment_params->login='';
			$element->payment_params->password='';
			$element->payment_params->ask_ccv = true;
			$element->payment_params->security = false;
			$element->payment_params->domain='www.payjunction.com';
			$element->payment_params->pending_status='created';
			$element->payment_params->verified_status='confirmed';
			$element = array($element);
		}
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','payment-payjunction-form');
		hikashop_setTitle('PAYJUNCTION','plugin','plugins&plugin_type=payment&task=edit&name='.$this->payjunction);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->payjunction);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
	}
	function onPaymentConfigurationSave(&$element){
		if( isset($element->payment_params->security) && $element->payment_params->security && isset($element->payment_params->security_cvv) && $element->payment_params->security_cvv ) {
			$element->payment_params->ask_ccv = true;
		}
		return true;
	}
}
