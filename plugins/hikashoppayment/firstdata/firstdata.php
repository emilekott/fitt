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
class plgHikashoppaymentFirstdata extends JPlugin
{
	var $accepted_currencies = array( 'USD' );
	function onPaymentDisplay(&$order,&$methods,&$usable_methods) {
		if(!empty($methods)){
			foreach($methods as $method){
				if($method->payment_type!='firstdata' || !$method->enabled){
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
		if($order->order_payment_method != 'firstdata') {
			return true;
		}
		if(!function_exists('curl_init')){
			$app->enqueueMessage('The First Data payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
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
		$amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','');
		$vars = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' . "\r\n" . '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Header /><SOAP-ENV:Body>';
		$vars .= '<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1="http://secure.linkpt.net/fdggwsapi/schemas_us/v1"  xmlns:fdggwsapi="http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi">';
		$vars .= '<v1:Transaction><v1:CreditCardTxType><v1:Type>sale</v1:Type></v1:CreditCardTxType><v1:CreditCardData><v1:CardNumber>';
		$vars .= $this->cc_number;
		$vars .= '</v1:CardNumber><v1:ExpMonth>'. $this->cc_month .'</v1:ExpMonth>';
		$vars .= '<v1:ExpYear>' . substr($this->cc_year, -2) . '</v1:ExpYear>';
		if( $method->payment_params->ask_ccv ) {
			$vars .= '<v1:CardCodeValue>' . $this->cc_CCV . '</v1:CardCodeValue>';
		}
		$vars .= '</v1:CreditCardData><v1:Payment><v1:ChargeTotal>' . $amount . '</v1:ChargeTotal></v1:Payment>';
		$vars .= '<v1:TransactionDetails><v1:UserID>'. $user->user_id .'</v1:UserID></v1:TransactionDetails>';
		$vars .= '<v1:Billing><v1:Name>'. $this->cc_owner .'</v1:Name><v1:Address1>'.
			@$order->cart->$address_type->address_street .'</v1:Address1><v1:City>'.
			@$order->cart->$address_type->address_city.'</v1:City><v1:State>'.
			@$order->cart->$address_type->address_state->zone_name.'</v1:State><v1:Zip>'.
			@$order->cart->$address_type->address_post_code.'</v1:Zip><v1:Country>'.
			@$order->cart->$address_type->address_country->zone_name.'</v1:Country></v1:Billing>';
		$vars .= '</v1:Transaction></fdggwsapi:FDGGWSApiOrderRequest>';
		$vars .= '</SOAP-ENV:Body></SOAP-ENV:Envelope>';
		$credentials = 'WS'.$method->payment_params->login . '._.1:' . $method->payment_params->password;
		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$domain = rtrim($method->payment_params->domain, '/'); // ws.firstdataglobalgateway.com
		$url = '/fdggwsapi/services/order.wsdl';
		$session = curl_init('https://' . $domain . $url);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_VERBOSE, 1);
		curl_setopt($session, CURLOPT_POST, 1);
		curl_setopt($session, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
		curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($session, CURLOPT_USERPWD, $credentials);
		curl_setopt($session, CURLOPT_POSTFIELDS, $vars);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_SSLCERT, $method->payment_params->pem_file);
		curl_setopt($session, CURLOPT_SSLKEY, $method->payment_params->key_file);
		curl_setopt($session, CURLOPT_SSLKEYPASSWD, $method->payment_params->key_passwd);
		$ret = curl_exec($session);
		$error = curl_errno($session);
		$err_msg = curl_error($session);;
		curl_close($session);
		if( !empty($ret) ) {
			if( $method->payment_params->debug ) {
				echo print_r($ret, true) . "\n\n\n";
			}
			$result = 0;
			if( strpos($ret, '<fdggwsapi:FDGGWSApiOrderResponse') !== false ) {
				$result = 1;
				if( preg_match('#<fdggwsapi:TransactionResult>(.*)</fdggwsapi:TransactionResult>#iU', $ret, $res) ) {
					$resultMsg = strtoupper(trim($res[1]));
					if($resultMsg == 'APPROVED') {
						$result = 2;
					}
				}
				if( $result ) {
					if( preg_match('#<fdggwsapi:TransactionID>(.*)</fdggwsapi:TransactionID>#iU', $ret, $res) ) {
						$transactionId = trim($res[1]);
					}
					if( preg_match('#<fdggwsapi:ApprovalCode>(.*)</fdggwsapi:ApprovalCode>#iU', $ret, $res) ) {
						$approvalCode = trim($res[1]);
					}
				}
				if( preg_match('#<fdggwsapi:ErrorMessage>(.*)</fdggwsapi:ErrorMessage>#iU', $ret, $res) ) {
					$errorMsg = trim($res[1]);
				}
				if( preg_match('#<fdggwsapi:AuthenticationResponseCode>(.*)</fdggwsapi:AuthenticationResponseCode>#iU', $ret, $res) ) {
					$responseMsg = trim($res[1]);
				}
			}
			if( $result > 0 ) {
				if( $result == 2 ) {
					$do = true;
					$dbg .= ob_get_clean();
					if( !empty($dbg) ) $dbg .= "\r\n";
					ob_start();
					$order->history->history_reason = JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
					$order->history->history_notified = 0;
					$order->history->history_amount = $amount . $this->accepted_currencies[0];
					$order->history->history_payment_id = $method->payment_id;
					$order->history->history_payment_method = $method->payment_type;
					$order->history->history_data = $dbg . 'Authorization Code: ' . @$approvalCode . "\r\n" . 'Transaction ID: ' . @$transactionId;
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
					$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','First Data','Accepted'));
					$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','First Data','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;
					$mailer->setBody($body);
					$mailer->Send();
				} else {
					if( isset($responseMsg) ) {
						$app->enqueueMessage($responseMsg);
					} else {
						$app->enqueueMessage('Error');
					}
					if( isset($errorMsg) ) {
						$app->enqueueMessage($errorMsg);
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
			$app->enqueueMessage('There was an error during the connection with the First Data payment gateway');
			if( $method->payment_params->debug ) {
				$app->enqueueMessage('Curl Err [' . $error . '] : ' . $err_msg );
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
		$this->firstdata = JRequest::getCmd('name','firstdata');
		if(empty($element)){
			$element = null;
			$element->payment_name='FirstData';
			$element->payment_description='You can pay by credit card using this payment method';
			$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
			$element->payment_type=$this->firstdata;
			$element->payment_params=null;
			$element->payment_params->login='';
			$element->payment_params->password='';
			$element->payment_params->ask_ccv = true;
			$element->payment_params->cert = false;
			$element->payment_params->pending_status='created';
			$element->payment_params->verified_status='confirmed';
			$element = array($element);
		}
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','payment-firstdata-form');
		hikashop_setTitle('FIRSTDATA','plugin','plugins&plugin_type=payment&task=edit&name='.$this->firstdata);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->firstdata);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
	}
	function onPaymentConfigurationSave(&$element){
		return true;
	}
}
