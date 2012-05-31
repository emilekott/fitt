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
class plgHikashoppaymentIveri extends JPlugin
{
	var $accepted_currencies = array( 'ZAR' );
	function onPaymentDisplay(&$order,&$methods,&$usable_methods) {
		if(!empty($methods)){
			foreach($methods as $method){
				if($method->payment_type!='iveri' || !$method->enabled){
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
		if($order->order_payment_method != 'iveri') {
			return true;
		}
		if(!function_exists('curl_init')){
			$app->enqueueMessage('The iVeri payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
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
		}
		$address = $app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		$address_type = 'billing_address';
		$address1 = ''; $address2 = ''; $address3 = '';
		$cart = hikashop_get('class.cart');
		$cart->loadAddress($order->cart,$address,'object','billing');
		if(!empty($order->cart->$address_type->address_street)) {
			if(strlen($order->cart->$address_type->address_street)>20) {
				$address1 = substr($order->cart->$address_type->address_street,0,20);
				$address2 = @substr($order->cart->$address_type->address_street,20,20);
				$address3 = @substr($order->cart->$address_type->address_street,40,20);
			}else{
				$address1 = $order->cart->$address_type->address_street;
			}
		}
		$country_code_2 = @$order->cart->$address_type->address_country->zone_code_3;
		if( isset($order->order_id) )
			$uuid = $order->order_id;
		else
			$uuid = uniqid('');
		$appId = '{' . trim($method->payment_params->applicationid, " {}\t\r\n\0") . '}';
		$prefix = empty($method->payment_params->invoice_prefix)?'inv':$method->payment_params->invoice_prefix;
		$amount = (int)round($order->cart->full_total->prices[0]->price_value_with_tax * 100);
		$vars = array (
			'Lite_Version' => '2.0',
			'Lite_Merchant_ApplicationId' => $appId,
			'Lite_Order_Amount' => $amount,
			'Lite_Order_Terminal' => 'web',
			'Lite_Website_Successful_Url' => 'http://127.0.0.1/success',
			'Lite_Website_Fail_Url' => 'http://127.0.0.1/fail',
			'Lite_Website_TryLater_Url' => 'http://127.0.0.1/trylater',
			'Lite_Website_Error_Url' => 'http://127.0.0.1/error',
			'Lite_Order_LineItems_Product_1' => 'Your order',
			'Lite_Order_LineItems_Amount_1' => $amount,
			'Lite_Order_LineItems_Quantity_1' => 1,
			'Lite_ConsumerOrderID_PreFix' => $prefix,
			'Lite_Authorisation' => 'false',
			'Ecom_TransactionComplete' => 'false',
			'Ecom_SchemaVersion' => '',
			'Ecom_Payment_Card_Protocols' => 'iVeri',
			'Ecom_Payment_Card_StartDate_Day' => '00',
			'Ecom_Payment_Card_StartDate_Month' => '04',
			'Ecom_Payment_Card_StartDate_Year' => '2000',
			'Ecom_Payment_Card_ExpDate_Day' => '00',
			'Ecom_BillTo_Postal_Name_First' => substr( @$order->cart->$address_type->address_firstname, 0, 20),
			'Ecom_BillTo_Postal_Name_Last' => substr( @$order->cart->$address_type->address_lastname, 0, 20),
			'Ecom_BillTo_Postal_Street_Line1' => $address1,
			'Ecom_BillTo_Postal_Street_Line2' => $address2,
			'Ecom_BillTo_Postal_Street_Line3' => $address3,
			'Ecom_BillTo_Postal_City' => substr( @$order->cart->$address_type->address_city, 0, 22),
			'Ecom_BillTo_Postal_PostalCode' => substr( @$order->cart->$address_type->address_post_code, 0, 20),
			'Ecom_BillTo_Postal_CountryCode' => @$order->cart->$address_type->address_country->zone_code_2,
			'Ecom_BillTo_Online_Email' => substr($user->user_email, 0, 40),
			'Ecom_ShipTo_Postal_Name_First' => substr( @$order->cart->$address_type->address_firstname, 0, 20),
			'Ecom_ShipTo_Postal_Name_Last' => substr( @$order->cart->$address_type->address_firstname, 0, 20),
			'Ecom_ShipTo_Postal_Street_Line1' => $address1,
			'Ecom_ShipTo_Postal_Street_Line2' => $address2,
			'Ecom_ShipTo_Postal_Street_Line3' => $address3,
			'Ecom_ShipTo_Postal_City' => substr( @$order->cart->$address_type->address_city, 0, 22),
			'Ecom_ShipTo_Postal_PostalCode' => substr( @$order->cart->$address_type->address_post_code, 0, 14),
			'Ecom_ShipTo_Postal_CountryCode' => @$order->cart->$address_type->address_country->zone_code_2,
			'Ecom_Payment_Card_Name' => $this->cc_owner,
			'Ecom_Payment_Card_Number' => $this->cc_number,
			'Ecom_Payment_Card_Verification' => @$this->cc_CCV,
			'Ecom_Payment_Card_ExpDate_Month' => $this->cc_month,
			'Ecom_Payment_Card_ExpDate_Year' => $this->cc_year,
			'Ecom_ConsumerOrderID' => $uuid,
		);
		$session = curl_init();
		curl_setopt($session, CURLOPT_FRESH_CONNECT,  true);
		curl_setopt($session, CURLOPT_HEADER,         0);
		curl_setopt($session, CURLOPT_POST,           1);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($session, CURLOPT_FAILONERROR,    true);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_COOKIEFILE,     "");
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$domain = $method->payment_params->domain;
		$url = '/Lite/Transactions/New/Authorise.aspx';
		curl_setopt($session, CURLOPT_URL, 'https://' . $domain . $url);
		curl_setopt($session, CURLOPT_REFERER, $httpsHikashop);
		curl_setopt($session, CURLOPT_POSTFIELDS, $vars);
		$result = curl_exec($session);
		$error = curl_error($session);
		$inputs = $this->getHiddenInputValues($result, true);
		if( !empty($error) || !isset($inputs['__viewstate']) ) {
			$app->enqueueMessage('Error while connecting to the Payment Gateway.');
			$do = false;
		} else {
			$inputs = $this->getHiddenInputValues($result);
			curl_setopt($session, CURLOPT_REFERER, 'https://' . $domain . $url);
			curl_setopt($session, CURLOPT_POSTFIELDS, $inputs);
			$result = curl_exec($session);
			$error = curl_error($session);
			$inputs = $this->getHiddenInputValues($result, true);
			if( empty($error) && isset($inputs['lite_payment_card_status']) ) {
				$err = $inputs['lite_payment_card_status'];
				if( $err == 0 ) {
					$order->history->history_reason = JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
					$order->history->history_notified = 0;
					$order->history->history_amount = $amount . $this->accepted_currencies[0];
					$order->history->history_payment_id = $method->payment_id;
					$order->history->history_payment_method = $method->payment_type;
					$order->history->history_data = ob_get_clean() . "\r\n" . 'Authorization Code: ' . $inputs['lite_order_authorisationcode'];
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
					$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','Iveri','Accepted'));
					$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','iVeri','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;
					$mailer->setBody($body);
					$mailer->Send();
				} else if($err == 1 || $err == 2 || $err == 5 || $err == 9) {
					$app->enqueueMessage('The transaction could not be processed.');
					$do = false;
				} else if($err == 14) {
					$app->enqueueMessage('Invalid card number.');
					$do = false;
				} else if($err == 255) {
					$app->enqueueMessage('The transaction could not be processed due incorrect or missing information.');
					$do = false;
				} else {
					$app->enqueueMessage('The transaction has been declined.');
					$do = false;
				}
			} else {
				$app->enqueueMessage('An error occurred.');
				$do = false;
			}
		}
		curl_close($session);
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
		$this->removeCart = true;
		JHTML::_('behavior.mootools');
		$app =& JFactory::getApplication();
		$name = $method->payment_type.'_end.php';
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
		$this->iveri = JRequest::getCmd('name','iveri');
		if(empty($element)){
			$element = null;
			$element->payment_name='IVERI';
			$element->payment_description='You can pay by credit card using this payment method';
			$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
			$element->payment_type=$this->iveri;
			$element->payment_params=null;
			$element->payment_params->domain='backoffice.iveri.co.za';
			$element->payment_params->payment_params->invoice_prefix='inv';
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
		$bar->appendButton( 'Pophelp','payment-iveri-form');
		hikashop_setTitle('IVERI','plugin','plugins&plugin_type=payment&task=edit&name='.$this->iveri);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->iveri);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
	}
	function onPaymentConfigurationSave(&$element){
		return true;
	}
	function getInputTags(&$content) {
		$results = '';
		preg_match_all('/<input\s.*?>/i', $content, $results);
		return $results;
	}
	function getHiddenInputTags(&$content) {
		$input_tags =& $this->getInputTags($content);
		$results = array();
		foreach($input_tags[0] as $tag) {
			if (preg_match('/type\s*=\s*(\'|")hidden(\'|")/i', $tag) > 0) {
				array_push($results, $tag);
			}
		}
		return $results;
	}
	function getHiddenInputValues(&$content, $lowerK = false, $lowerV = false) {
		$tags =& $this->getHiddenInputTags($content);
		$nameValues = array();
		foreach ($tags as $tag) {
			$name = trim($this->getAttributeValue('name', $tag));
			if ( empty($name) ) continue;
			if( $lowerK ) $name = strtolower($name);
			$value = trim($this->getAttributeValue('value', $tag));
			if( $lowerV ) $value = strtolower($value);
			$nameValues[$name] = $value;
		}
		return $nameValues;
	}
	function getAttributeValue($name, $tag) {
		$regex1 = '/[\s]' . $name . '[\s]*=[\s]*["\'][-\]\\_!@#$%^&*()_+=[|}{;:\/?.,\w\s]*(?=["\'])/i';
		$regex2 = '/[\s]' . $name . '[\s]*=[\s]*["\']/i';
		preg_match_all($regex1, $tag, $matches);
		if (count($matches[0]) != 1) return '';
		return preg_replace($regex2, "", $matches[0][0]);
	}
}
