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
class plgHikashoppaymentSAGEPAY extends JPlugin
{
	var $accepted_currencies = array(
		'GBP','USD','EUR'
	);
	function onPaymentDisplay(&$order,&$methods,&$usable_methods) {
		if(!empty($methods)){
			foreach($methods as $method){
				if($method->payment_type!='sagepay' || !$method->enabled){
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
					if(!empty($currency) && !in_array(@$currency[$currency_id]->currency_code, $this->accepted_currencies)) {
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
		$viewType='_end';
		$currencyClass = hikashop::get('class.currency');
		$currencies = null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency = $currencies[$order->order_currency_id];
		hikashop::loadUser(true,true);
		$user = hikashop::loadUser(true);
		$lang = &JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));
		$server_url = HIKASHOP_LIVE.'index.php';
		global $Itemid;
	  	$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$return_url_p = 'option=com_hikashop&ctrl=checkout&task=notify&notif_payment=sagepay&tmpl=component&lang='.$locale.$url_itemid;
		$app =& JFactory::getApplication();
		$cart = hikashop::get('class.cart');
		$address_type = 'billing_address';
		$address = $app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		$cart->loadAddress($order->cart,$address,'object','billing');
		$address = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
		$cart->loadAddress($order->cart,$address,'object','shipping');
		$address1 = ''; $address2 = '';
		$address1 = @$order->cart->billing_address->address_street;
		if( strlen($address1) > 100 ) {
			$address2 = substr($address1, 100, 100);
			$address1 = substr($address1, 0, 100);
		}
		$ship_address1 = ''; $ship_address2 = '';
		$ship_address1 = @$order->cart->shipping_address->address_street;
		if( empty($ship_address1) ) { $ship_address1 = $address1; }
		if( strlen($ship_address1) > 100 ) {
			$ship_address2 = substr($ship_address1, 100, 100);
			$ship_address1 = substr($ship_address1, 0, 100);
		}
		$sendEmail = 0;
		$postData = array(
			'VendorTxCode' => $order->order_id,
			'Amount' => $order->cart->full_total->prices[0]->price_value_with_tax,
			'Currency' => $currency->currency_code,
			'Description' => $order->order_number,
			'SuccessURL' => $server_url . '?' . $return_url_p,
			'FailureURL' => $server_url . '?' . $return_url_p,
			'CustomerName' => @$order->cart->billing_address->address_firstname . ' ' . @$order->cart->billing_address->address_lastname,
			'SendEMail' => $sendEmail,
			'BillingFirstnames' => @$order->cart->billing_address->address_firstname,
			'BillingSurname' => @$order->cart->billing_address->address_lastname,
			'BillingAddress1' => $address1,
			'BillingAddress2' => $address2,
			'BillingCity' => @$order->cart->billing_address->address_city,
			'BillingPostCode' => @$order->cart->billing_address->address_post_code,
			'BillingCountry' => @$order->cart->billing_address->address_country->zone_code_2,
			'DeliveryFirstnames' => @$order->cart->shipping_address->address_firstname,
			'DeliverySurname' => @$order->cart->shipping_address->address_lastname,
			'DeliveryAddress1' => $ship_address1,
			'DeliveryAddress2' => $ship_address2,
			'DeliveryCity' => @$order->cart->shipping_address->address_city,
			'DeliveryPostCode' => @$order->cart->shipping_address->address_post_code,
			'DeliveryCountry' => @$order->cart->shipping_address->address_country->zone_code_2,
			'AllowGiftAid' => 0,
			'ApplyAVSCV2' => 0,
			'Apply3DSecure' => 0,
		);
		$t = array();
		foreach($postData as $k => $v) {
			$t[] = $k . '=' . $v;
		}
		$postData = implode('&',$t);
		unset($t);
		$vars = array(
			'navigate' => '',
			'VPSProtocol' => '2.23',
			'TxType' => 'PAYMENT',
			'Vendor' => $method->payment_params->vendor_name,
			'Crypt' => $this->encryptAndEncode($postData, $method->payment_params->password, '' ),
		);
		switch( $method->payment_params->mode ) {
			case 'LIVE':
				$url = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
				break;
			case 'TEST':
				$url = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
				break;
			case 'SIMU':
			default:
				$url = 'https://test.sagepay.com/Simulator/VSPFormGateway.asp';
				break;
		}
		JHTML::_('behavior.mootools');
		$name = $method->payment_type.$viewType.'.php';
		$app =& JFactory::getApplication();
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
		$elements = $pluginsClass->getMethods('payment','sagepay');
		if(empty($elements)) return false;
		$element = reset($elements);
		$data = $this->decodeAndDecrypt($_REQUEST['crypt'], $element->payment_params->password);
		$httpsHikashop = HIKASHOP_LIVE;
		if( $element->payment_params->debug ) {
			$httpsHikashop = str_replace('https://','http://', HIKASHOP_LIVE);
		}
		global $Itemid;
	  	$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$cancel_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order&task=cancel_order'.$url_itemid;
		$app =& JFactory::getApplication();
		if( strpos($data, 'Status') === false ) {
			$app->enqueueMessage('Error while processing encrypted data');
			$app->redirect($cancel_url);
			return false;
		}
		$vars = array();
		parse_str($data, $vars);
		$vars['OrderID'] = (int)$vars['VendorTxCode'];
		if($element->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
		}
		$orderClass = hikashop::get('class.order');
		$dbOrder = $orderClass->get((int)$vars['OrderID']);
		if(empty($dbOrder)){
			$app->enqueueMessage('Could not load any order for your notification '.$vars['OrderID']);
			$app->redirect($cancel_url);
			return false;
		}
		$cancel_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$dbOrder->order_id.$url_itemid;
		if($element->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
		}
		$order = null;
		$order->order_id = $dbOrder->order_id;
		$order->old_status->order_status=$dbOrder->order_status;
		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$url_itemid;
		$mailer =& JFactory::getMailer();
		$config =& hikashop::config();
		$sender = array(
			$config->get('from_email'),
			$config->get('from_name') );
		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));
		$currencyClass = hikashop::get('class.currency');
		$currencies = null;
		$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
		$currency = $currencies[$dbOrder->order_currency_id];
		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified = 0;
		$order->history->history_amount = $vars['Amount'] . $currency->currency_code;
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method =$element->payment_type;
		$order->history->history_data = $vars['Status'] . ': ' . $vars['StatusDetail'] . "\n--\n" . 'Sage Pay ID: ' . $vars['VPSTxId'] . "\n" . 'Authorisation Code:' . $vars['TxAuthNo'] . "\n" . ob_get_clean();
		$order->history->history_type = 'payment';
		$completed = ($vars['Status'] == 'OK');
		if( !$completed ) {
			$order->order_status = $element->payment_params->invalid_status;
			$order->history->history_data .= "\n\n" . 'payment with code '.$vars['Status'].' - '.$vars['StatusDetail'];
			$orderClass->save($order);
			$order_text = $vars['Status'] . ' - ' . $vars['StatusDetail']."\r\n\r\n".$order_text;
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','SagePay',$vars['Status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','SagePay',$vars['Status']));
			$mailer->setBody($body);
			$mailer->Send();
			$app->enqueueMessage('Transaction Failed: '.$vars['StatusDetail']);
			$app->redirect($cancel_url);
			return false;
		}
		$order->order_status = $element->payment_params->verified_status;
		$vars['payment_status']='Accepted';
		$order->history->history_notified = 1;
		$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','SagePay',$vars['payment_status']));
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','SagePay',$vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order->order_status])."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
		$orderClass->save($order);
		$app->redirect($return_url);
		return true;
	}
	function onPaymentConfiguration(&$element){
		$this->sagepay = JRequest::getCmd('name','sagepay');
		if(empty($element)){
			$element = null;
			$element->payment_name='SagePay';
			$element->payment_description='You can pay by credit card using this payment method';
			$element->payment_images='VISA,Maestro,MasterCard';
			$element->payment_type=$this->sagepay;
			$element->payment_params=null;
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
		$bar->appendButton( 'Pophelp','payment-sagepay-form');
		hikashop::setTitle('SagePay','plugin','plugins&plugin_type=payment&task=edit&name='.$this->sagepay);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->sagepay);
		$this->address = hikashop::get('type.address');
		$this->category = hikashop::get('type.categorysub');
		$this->category->type = 'status';
	}
	function simpleXor($in, $k) {
		$lst = array();
		$output = '';
		for($i = 0; $i < strlen($k); $i++) {
			$lst[$i] = ord(substr($k, $i, 1));
		}
		for($i = 0; $i < strlen($in); $i++) {
			$output .= chr(ord(substr($in, $i, 1)) ^ ($lst[$i % strlen($k)]));
		}
		return $output;
	}
	function encryptAndEncode($in, $password, $type) {
		if($type == 'XOR') {
			return base64_encode($this->simpleXor($in, $password));
		} else {
			$this->addPKCS5Padding($in);
			$iv = $password;
			$strCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $password, $in, MCRYPT_MODE_CBC, $iv);
			return "@" . bin2hex($strCrypt);
		}
	}
	function decodeAndDecrypt($in, $password) {
		if( substr($in,0,1) == '@') {
			$iv = $password;
			$in = substr($in,1);
			$in = pack('H*', $in);
			return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $password, $in, MCRYPT_MODE_CBC, $iv);
		} else {
			return $this->simpleXor(base64_decode(str_replace(' ','+',$in)), $password);
		}
	}
	function addPKCS5Padding(&$input) {
		$blocksize = 16;
		$padding = '';
		$padlength = $blocksize - (strlen($input) % $blocksize);
		for($i = 1; $i <= $padlength; $i++) {
			$padding .= chr($padlength);
		}
		$input .= $padding;
	}
}
