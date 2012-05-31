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
class plgHikashoppaymentEway extends JPlugin
{
    var $debugData = array();
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='eway' || !$method->enabled){
					continue;
				}
				if(!empty($method->payment_zone_namekey)){
					$zoneClass=hikashop_get('class.zone');
	    			$zones = $zoneClass->getOrderZones($order);
					if(!in_array($method->payment_zone_namekey,$zones)){
						return true;
					}
				}
				$this->needCC($method);
				$usable_methods[$method->ordering]=$method;
    		}
    	}
    	return true;
    }
	function needCC(&$method) {
		$method->ask_cc=true;
		$method->ask_ccv = true;
		$method->ask_owner = true;
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
    function onBeforeOrderCreate(&$order,&$do){
    	$app =& JFactory::getApplication();
    	if($app->isAdmin()){
    		return true;
    	}
    	if($order->order_payment_method!='eway'){
    		return true;
    	}
    	$db =& JFactory::getDBO();
    	$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($order->order_payment_method);
		$db->setQuery($query);
		$paymentData = $db->loadObjectList('payment_id');
		$pluginsClass = hikashop_get('class.plugins');
		$pluginsClass->params($paymentData,'payment');
		$method =& $paymentData[$order->order_payment_id];
		if(!function_exists('curl_init')){
			$app->enqueueMessage('The eWay payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		if( $method->payment_params->debug) {
			$eway = new EwayPayment( '87654321', "https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp" );
		} else {
			$eway = new EwayPayment( $method->payment_params->cust_id, 'https://www.eway.com.au/gateway_cvn/xmlpayment.asp'  );
		}
		$eway->setCustomerInvoiceRef( uniqid( "order_" ) );
		$eway->setTrxnNumber( uniqid( "eway_" ) );
		$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency=$currencies[$order->order_currency_id];
		$user = hikashop_loadUser(true);
		$eway->setTotalAmount(round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$currency->currency_locale['int_frac_digits'])*100);
		$eway->setCustomerEmail( $user->user_email );
		$app =& JFactory::getApplication();
		$cart = hikashop_get('class.cart');
		$shipping_address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
		$billing_address=$app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		if(!empty($shipping_address)){
			$cart->loadAddress($order->cart,$shipping_address,'object','shipping');
			$eway->setCustomerAddress( @$order->cart->shipping_address->address_street.', '.@$order->cart->shipping_address->address_city.', '.@$order->cart->shipping_address->address_state->zone_name_english );
			$eway->setCustomerPostcode( @$order->cart->shipping_address->address_post_code );
			$eway->setCustomerFirstname( @$order->cart->shipping_address->address_firstname );
		}elseif(!empty($billing_address)){
			$cart->loadAddress($order->cart,$billing_address,'object','billing');
			$eway->setCustomerAddress( @$order->cart->billing_address->address_street.', '.@$order->cart->billing_address->address_city.', '.@$order->cart->billing_address->address_state->zone_name_english );
			$eway->setCustomerPostcode( @$order->cart->billing_address->address_post_code );
			$eway->setCustomerFirstname( @$order->cart->billing_address->address_firstname );
		}
		$order_item_name = Array();
		foreach($order->cart->products as $product){
            $order_item_name[] = $product->order_product_name;
        }
        $order_items = implode(' - ', $order_item_name);
		$eway->setCustomerInvoiceDescription( $order_items );
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
		$this->cc_CCV = $app->getUserState( HIKASHOP_COMPONENT.'.cc_CCV');
		if(!empty($this->cc_CCV)){
			$this->cc_CCV = base64_decode($this->cc_CCV);
		}
    	$this->cc_owner = $app->getUserState( HIKASHOP_COMPONENT.'.cc_owner');
		if(!empty($this->cc_owner)){
			$this->cc_owner = base64_decode($this->cc_owner);
		}
		$eway->setCardHoldersName( $this->cc_owner );
		$eway->setCardNumber( $this->cc_number);
		$eway->setCardExpiryMonth( $this->cc_month );
		$eway->setCardExpiryYear( $this->cc_year );
		$eway->setCardCVN( $this->cc_CCV );
		switch($eway->doPayment()) {
			case EWAY_TRANSACTION_FAILED:
				$app->enqueueMessage('Your transaction was declined. Please reenter your credit card or another credit card information.');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_number','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_month','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_year','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_owner','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_valid',0);
				$do = false;
				break;
			case EWAY_TRANSACTION_UNKNOWN:
			default:
				$app->enqueueMessage('There was an error while processing your transaction: '.$eway->getErrorMessage());
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_number','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_month','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_year','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_owner','');
				$app->setUserState( HIKASHOP_COMPONENT.'.cc_valid',0);
				$do = false;
				break;
    		case EWAY_TRANSACTION_OK:
    			$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
				$order->history->history_notified=0;
				$order->history->history_amount= round($order->cart->full_total->prices[0]->price_value_with_tax,2).'USD';
				$order->history->history_payment_id = $order->order_payment_id;
				$order->history->history_payment_method =$order->order_payment_method;
				$order->history->history_data = '';
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
				$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
				$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
				$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','eWAY','Accepted'));
				$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','eWAY','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;
				$mailer->setBody($body);
				$mailer->Send();
				break;
		}
		return true;
    }
    function onAfterOrderConfirm(&$order,&$methods,$method_id){
		$method =& $methods[$method_id];
		$viewType = '_end';
		$this->removeCart = true;
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
    function onPaymentConfiguration(&$element){
    	$subtask = JRequest::getCmd('subtask','');
    	$this->eway = JRequest::getCmd('name','eway');
		if(empty($element)){
			$element = null;
    		$element->payment_name='eWAY';
    		$element->payment_description='You can pay by credit card using this payment method';
    		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
    		$element->payment_type=$this->eway;
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
		$bar->appendButton( 'Pophelp','payment-eway-form');
		hikashop_setTitle('eWay','plugin','plugins&plugin_type=payment&task=edit&name='.$this->eway);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->eway);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
    }
    function onPaymentConfigurationSave(&$element){
		return true;
    }
}
define( 'EWAY_CURL_ERROR_OFFSET', 1000 );
define( 'EWAY_XML_ERROR_OFFSET',  2000 );
define( 'EWAY_TRANSACTION_OK',       0 );
define( 'EWAY_TRANSACTION_FAILED',   1 );
define( 'EWAY_TRANSACTION_UNKNOWN',  2 );
class EwayPayment {
    var $parser;
    var $xmlData;
    var $currentTag;
    var $myGatewayURL;
    var $myCustomerID;
    var $myTotalAmount;
    var $myCustomerFirstname;
    var $myCustomerLastname;
    var $myCustomerEmail;
    var $myCustomerAddress;
    var $myCustomerPostcode;
    var $myCustomerInvoiceDescription;
    var $myCustomerInvoiceRef;
    var $myCardHoldersName;
    var $myCardNumber;
    var $myCardExpiryMonth;
    var $myCardExpiryYear;
    var $myCardCVN;
    var $myTrxnNumber;
    var $myOption1;
    var $myOption2;
    var $myOption3;
    var $myResultTrxnStatus;
    var $myResultTrxnNumber;
    var $myResultTrxnOption1;
    var $myResultTrxnOption2;
    var $myResultTrxnOption3;
    var $myResultTrxnReference;
    var $myResultTrxnError;
    var $myResultAuthCode;
    var $myResultReturnAmount;
	var $myCardName;
    var $myError;
    var $myErrorMessage;
    function EwayPayment( $customerID = EWAY_DEFAULT_CUSTOMER_ID, $gatewayURL = EWAY_DEFAULT_GATEWAY_URL ) {
        $this->myCustomerID = $customerID;
        $this->myGatewayURL = $gatewayURL;
    }
    function epXmlElementStart ($parser, $tag, $attributes) {
        $this->currentTag = $tag;
    }
    function epXmlElementEnd ($parser, $tag) {
        $this->currentTag = "";
    }
    function epXmlData ($parser, $cdata) {
        $this->xmlData[$this->currentTag] = $cdata;
    }
    function setCustomerID( $customerID ) {
        $this->myCustomerID = $customerID;
    }
    function setTotalAmount( $totalAmount ) {
        $this->myTotalAmount = $totalAmount;
    }
    function setCustomerFirstname( $customerFirstname ) {
        $this->myCustomerFirstname = $customerFirstname;
    }
    function setCustomerLastname( $customerLastname ) {
        $this->myCustomerLastname = $customerLastname;
    }
    function setCustomerEmail( $customerEmail ) {
        $this->myCustomerEmail = $customerEmail;
    }
    function setCustomerAddress( $customerAddress ) {
        $this->myCustomerAddress = $customerAddress;
    }
    function setCustomerPostcode( $customerPostcode ) {
        $this->myCustomerPostcode = $customerPostcode;
    }
    function setCustomerInvoiceDescription( $customerInvoiceDescription ) {
        $this->myCustomerInvoiceDescription = $customerInvoiceDescription;
    }
    function setCustomerInvoiceRef( $customerInvoiceRef ) {
        $this->myCustomerInvoiceRef = $customerInvoiceRef;
    }
    function setCardHoldersName( $cardHoldersName ) {
        $this->myCardHoldersName = $cardHoldersName;
    }
    function setCardNumber( $cardNumber ) {
        $this->myCardNumber = $cardNumber;
    }
    function setCardExpiryMonth( $cardExpiryMonth ) {
        $this->myCardExpiryMonth = $cardExpiryMonth;
    }
    function setCardExpiryYear( $cardExpiryYear ) {
        $this->myCardExpiryYear = $cardExpiryYear;
    }
    function setCardCVN( $cardCVN ) {
        $this->myCardCVN = $cardCVN;
    }
    function setTrxnNumber( $trxnNumber ) {
        $this->myTrxnNumber = $trxnNumber;
    }
    function setOption1( $option1 ) {
        $this->myOption1 = $option1;
    }
    function setOption2( $option2 ) {
        $this->myOption2 = $option2;
    }
    function setOption3( $option3 ) {
        $this->myOption3 = $option3;
    }
    function getTrxnStatus() {
        return $this->myResultTrxnStatus;
    }
    function getTrxnNumber() {
        return $this->myResultTrxnNumber;
    }
    function getTrxnOption1() {
        return $this->myResultTrxnOption1;
    }
    function getTrxnOption2() {
        return $this->myResultTrxnOption2;
    }
    function getTrxnOption3() {
        return $this->myResultTrxnOption3;
    }
    function getTrxnReference() {
        return $this->myResultTrxnReference;
    }
    function getTrxnError() {
        return $this->myResultTrxnError;
    }
    function getAuthCode() {
        return $this->myResultAuthCode;
    }
    function getReturnAmount() {
        return $this->myResultReturnAmount;
    }
    function getError()
    {
        if( $this->myError != 0 ) {
            return $this->myError;
        } else {
            if( $this->getTrxnStatus() == 'True' ) {
                return EWAY_TRANSACTION_OK;
            } elseif( $this->getTrxnStatus() == 'False' ) {
                return EWAY_TRANSACTION_FAILED;
            } else {
                return EWAY_TRANSACTION_UNKNOWN;
            }
        }
    }
    function getErrorMessage()
    {
        if( $this->myError != 0 ) {
            return $this->myErrorMessage;
        } else {
            return $this->getTrxnError();
        }
    }
    function doPayment() {
        $xmlRequest = "<ewaygateway>".
                "<ewayCustomerID>".htmlentities( $this->myCustomerID )."</ewayCustomerID>".
                "<ewayTotalAmount>".htmlentities( $this->myTotalAmount)."</ewayTotalAmount>".
                "<ewayCustomerFirstName>".htmlspecialchars( $this->myCustomerFirstname , ENT_QUOTES, 'UTF-8')."</ewayCustomerFirstName>".
                "<ewayCustomerLastName>".htmlspecialchars( $this->myCustomerLastname, ENT_QUOTES, 'UTF-8' )."</ewayCustomerLastName>".
                "<ewayCustomerEmail>".htmlspecialchars( $this->myCustomerEmail, ENT_QUOTES, 'UTF-8' )."</ewayCustomerEmail>".
                "<ewayCustomerAddress>".htmlspecialchars( $this->myCustomerAddress, ENT_QUOTES, 'UTF-8' )."</ewayCustomerAddress>".
                "<ewayCustomerPostcode>".htmlspecialchars( $this->myCustomerPostcode , ENT_QUOTES, 'UTF-8')."</ewayCustomerPostcode>".
                "<ewayCustomerInvoiceDescription>".htmlspecialchars( $this->myCustomerInvoiceDescription, ENT_QUOTES, 'UTF-8' )."</ewayCustomerInvoiceDescription>".
                "<ewayCustomerInvoiceRef>".htmlentities( $this->myCustomerInvoiceRef )."</ewayCustomerInvoiceRef>".
                "<ewayCardHoldersName>".htmlspecialchars( $this->myCardHoldersName, ENT_QUOTES, 'UTF-8' )."</ewayCardHoldersName>".
                "<ewayCardNumber>".htmlentities( $this->myCardNumber )."</ewayCardNumber>".
                "<ewayCardExpiryMonth>".htmlentities( $this->myCardExpiryMonth )."</ewayCardExpiryMonth>".
                "<ewayCardExpiryYear>".htmlentities( $this->myCardExpiryYear )."</ewayCardExpiryYear>".
                "<ewayTrxnNumber>".htmlentities( $this->myTrxnNumber )."</ewayTrxnNumber>".
                "<ewayOption1>".htmlentities( $this->myOption1 )."</ewayOption1>".
                "<ewayOption2>".htmlentities( $this->myOption2 )."</ewayOption2>".
                "<ewayOption3>".htmlentities( $this->myOption3 )."</ewayOption3>".
                "<ewayCVN>".htmlentities( $this->myCardCVN )."</ewayCVN>".
                "</ewaygateway>";
        $ch = curl_init( $this->myGatewayURL );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $xmlRequest );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 240 );
        $xmlResponse = curl_exec( $ch );
        if( curl_errno( $ch ) == CURLE_OK ) {
            $this->parser = xml_parser_create();
            xml_parser_set_option ($this->parser, XML_OPTION_CASE_FOLDING, FALSE);
            xml_set_object($this->parser, $this);
            xml_set_element_handler ($this->parser, "epXmlElementStart", "epXmlElementEnd");
            xml_set_character_data_handler ($this->parser, "epXmlData");
            xml_parse($this->parser, $xmlResponse, TRUE);
            if( xml_get_error_code( $this->parser ) == XML_ERROR_NONE ) {
                $this->myResultTrxnStatus = @$this->xmlData['ewayTrxnStatus'];
                $this->myResultTrxnNumber = @$this->xmlData['ewayTrxnNumber'];
                $this->myResultTrxnOption1 = @$this->xmlData['ewayTrxnOption1'];
                $this->myResultTrxnOption2 = @$this->xmlData['ewayTrxnOption2'];
                $this->myResultTrxnOption3 = @$this->xmlData['ewayTrxnOption3'];
                $this->myResultTrxnReference = @$this->xmlData['ewayTrxnReference'];
                $this->myResultAuthCode = @$this->xmlData['ewayAuthCode'];
                $this->myResultReturnAmount = @$this->xmlData['ewayReturnAmount'];
                $this->myResultTrxnError = @$this->xmlData['ewayTrxnError'];
                $this->myError = 0;
                $this->myErrorMessage = '';
            } else {
                $this->myError = xml_get_error_code( $this->parser ) + EWAY_XML_ERROR_OFFSET;
                $this->myErrorMessage = xml_error_string( $this->parser  );
            }
            xml_parser_free( $this->parser );
        } else {
            $this->myError = curl_errno( $ch ) + EWAY_CURL_ERROR_OFFSET;
            $this->myErrorMessage = curl_error( $ch );
        }
        curl_close( $ch );
        return $this->getError();
    }
}