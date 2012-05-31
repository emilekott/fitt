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
class plgHikashoppaymentEpay extends JPlugin
{
	var $accepted_currencies = array(
		'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD',
		'SEK','DKK','PLN','NOK','HUF','CZK','MXN','BRL','MYR','PHP',
		'TWD','THB','ILS','TRY'
	);
    var $debugData = array();
    function onPaymentDisplay(&$order,&$methods,&$usable_methods){
    	if(!empty($methods)){
    		foreach($methods as $method){
				if($method->payment_type!='epay' || !$method->enabled){
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
					if(!empty($currency) && !in_array(@$currency[$currency_id]->currency_code,$this->accepted_currencies)){
						return true;
					}
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
	function getVars($order,$methods,$method_id)
	{
		$method =& $methods[$method_id];
		$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency=$currencies[$order->order_currency_id];
		$user = hikashop_loadUser(true);
		$lang = &JFactory::getLanguage();
		$locale=strtolower(substr($lang->get('tag'),0,2));
		global $Itemid;
	  	$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$callback_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=epay&tmpl=component&lang='.$locale.$url_itemid;
		$accept_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$url_itemid;
		$decline_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$url_itemid;
		if($method->payment_params->addfee == 1)
		{
			$addfee = 1;
		}
		else
		{
			$addfee = 0;
		}
		$vars = array(
		"merchantnumber" => $method->payment_params->merchantnumber,
		"orderid" => $order->order_id,
		"amount" => intval($order->order_full_price*100), //minor units
		"currency" => $this->get_iso_code($currency->currency_code),
		"windowstate" => $method->payment_params->windowstate,
		"accepturl" => $accept_url,
		"declineurl" => $decline_url,
		"callbackurl" => $callback_url,
		"authsms" => $method->payment_params->authsms,
		"authmail" => $method->payment_params->authemail,
		"instantcapture" => $method->payment_params->instantcapture,
		"splitpayment" => $method->payment_params->splitpayment,
		"group" => $method->payment_params->group,
		"addfee" => $addfee,
		"instantcallback" => 1,
		"cms" => "hikashop"
		);
		if($method->payment_params->md5mode == 3)
		{
			$vars["md5key"] = md5($this->get_iso_code($currency->currency_code) . intval($order->order_full_price*100) . $order->order_id . $method->payment_params->md5key);
		}
		return $vars;
	}
    function onAfterOrderConfirm(&$order,&$methods,$method_id){
    	$method =& $methods[$method_id];
		$tax_total = '';
		$discount_total = '';
		$vars = $this->getVars($order, $methods, $method_id);
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
		$elements = $pluginsClass->getMethods('payment','epay');
		if(empty($elements)) return false;
		$element = reset($elements);
		$orderClass = hikashop_get('class.order');
		$dbOrder = $orderClass->get((int)@$_GET['orderid']);
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".@$_GET['orderid'];
			return false;
		}
		$order = null;
		$order->order_id = $dbOrder->order_id;
		$order->old_status->order_status=$dbOrder->order_status;
		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
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
		if($element->payment_params->debug){
			echo print_r($_GET,true)."\n\n\n";
		}
		if($element->payment_params->md5mode == 2 or $element->payment_params->md5mode == 3)
		{
			$md5key = md5($_GET["amount"] . $_GET["orderid"] . $_GET["tid"] . $element->payment_params->md5key);
			if($md5key != $_GET["eKey"])
			{
				$order->order_status = 'cancelled';
				$order->history->history_reason = JText::_('PAYMENT_MD5_ERROR');
				$order->history->history_notified = 0;
				$order->history->history_payment_id = $element->payment_id;
				$order->history->history_payment_method = $element->payment_type;
				$order->history->history_data = "Payment by ePay - Invalid MD5 - ePay transaction ID: " . $_GET["tid"];
				$order->history->history_type = 'payment';
				$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','ePay').'invalid response');
				$body = JText::sprintf("Hello,\r\n An AlertPay notification was refused because the notification from the ePay server was invalid")."\r\n\r\n".$order_text;
				$mailer->setBody($body);
				$mailer->Send();
				$orderClass->save($order);
				return false;
			}
		}
		$order->order_status = 'confirmed';
		$order->history->history_reason = JText::_('PAYMENT_ORDER_CONFIRMED');
		$order->history->history_notified=0;
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method =$element->payment_type;
		$order->history->history_data = "Payment by ePay - ePay transaction ID: ".$_GET["tid"];
		$order->history->history_type = 'payment';
		$order->mail_status=$statuses[$order->order_status];
	 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','ePay',$order->mail_status).$order_subject);
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','ePay',$order->mail_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->mail_status)."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
	 	$orderClass->save($order);
    	return true;
    }
    function onPaymentConfiguration(&$element){
    		$this->epay = JRequest::getCmd('name','epay');
			if(empty($element)){
				$element = null;
    			$element->payment_name='ePay';
    			$element->payment_description='You can pay by credit card or epay using this payment method';
    			$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
    			$element->payment_type=$this->epay;
				$element->payment_params->windowstate = 2;
    			$element->payment_params=null;
    			$element->payment_params->notification=1;
    			$list=null;
    			$element->payment_params->windowstate=2;
    			$element->payment_params->md5mode=1;
    			$element->payment_params->instantcapture=2;
    			$element->payment_params->splitpayment=2;
    			$element->payment_params->addfee=2;
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
			$bar->appendButton( 'Pophelp','payment-epay-form');
			hikashop_setTitle('ePay','plugin','plugins&plugin_type=payment&task=edit&name='.$this->epay);
			$app =& JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->epay);
			$this->address = hikashop_get('type.address');
			$this->category = hikashop_get('type.categorysub');
			$this->category->type = 'status';
    }
 	function get_iso_code($code) {
	    switch (strtoupper($code)){
	    	case 'ADP': return '020'; break;
			case 'AED': return '784'; break;
			case 'AFA': return '004'; break;
			case 'ALL': return '008'; break;
			case 'AMD': return '051'; break;
			case 'ANG': return '532'; break;
			case 'AOA': return '973'; break;
			case 'ARS': return '032'; break;
			case 'AUD': return '036'; break;
			case 'AWG': return '533'; break;
			case 'AZM': return '031'; break;
			case 'BAM': return '977'; break;
			case 'BBD': return '052'; break;
			case 'BDT': return '050'; break;
			case 'BGL': return '100'; break;
			case 'BGN': return '975'; break;
			case 'BHD': return '048'; break;
			case 'BIF': return '108'; break;
			case 'BMD': return '060'; break;
			case 'BND': return '096'; break;
			case 'BOB': return '068'; break;
			case 'BOV': return '984'; break;
			case 'BRL': return '986'; break;
			case 'BSD': return '044'; break;
			case 'BTN': return '064'; break;
			case 'BWP': return '072'; break;
			case 'BYR': return '974'; break;
			case 'BZD': return '084'; break;
			case 'CAD': return '124'; break;
			case 'CDF': return '976'; break;
			case 'CHF': return '756'; break;
			case 'CLF': return '990'; break;
			case 'CLP': return '152'; break;
			case 'CNY': return '156'; break;
			case 'COP': return '170'; break;
			case 'CRC': return '188'; break;
			case 'CUP': return '192'; break;
			case 'CVE': return '132'; break;
			case 'CYP': return '196'; break;
			case 'CZK': return '203'; break;
			case 'DJF': return '262'; break;
			case 'DKK': return '208'; break;
			case 'DOP': return '214'; break;
			case 'DZD': return '012'; break;
			case 'ECS': return '218'; break;
			case 'ECV': return '983'; break;
			case 'EEK': return '233'; break;
			case 'EGP': return '818'; break;
			case 'ERN': return '232'; break;
			case 'ETB': return '230'; break;
			case 'EUR': return '978'; break;
			case 'FJD': return '242'; break;
			case 'FKP': return '238'; break;
			case 'GBP': return '826'; break;
			case 'GEL': return '981'; break;
			case 'GHC': return '288'; break;
			case 'GIP': return '292'; break;
			case 'GMD': return '270'; break;
			case 'GNF': return '324'; break;
			case 'GTQ': return '320'; break;
			case 'GWP': return '624'; break;
			case 'GYD': return '328'; break;
			case 'HKD': return '344'; break;
			case 'HNL': return '340'; break;
			case 'HRK': return '191'; break;
			case 'HTG': return '332'; break;
			case 'HUF': return '348'; break;
			case 'IDR': return '360'; break;
			case 'ILS': return '376'; break;
			case 'INR': return '356'; break;
			case 'IQD': return '368'; break;
			case 'IRR': return '364'; break;
			case 'ISK': return '352'; break;
			case 'JMD': return '388'; break;
			case 'JOD': return '400'; break;
			case 'JPY': return '392'; break;
			case 'KES': return '404'; break;
			case 'KGS': return '417'; break;
			case 'KHR': return '116'; break;
			case 'KMF': return '174'; break;
			case 'KPW': return '408'; break;
			case 'KRW': return '410'; break;
			case 'KWD': return '414'; break;
			case 'KYD': return '136'; break;
			case 'KZT': return '398'; break;
			case 'LAK': return '418'; break;
			case 'LBP': return '422'; break;
			case 'LKR': return '144'; break;
			case 'LRD': return '430'; break;
			case 'LSL': return '426'; break;
			case 'LTL': return '440'; break;
			case 'LVL': return '428'; break;
			case 'LYD': return '434'; break;
			case 'MAD': return '504'; break;
			case 'MDL': return '498'; break;
			case 'MGF': return '450'; break;
			case 'MKD': return '807'; break;
			case 'MMK': return '104'; break;
			case 'MNT': return '496'; break;
			case 'MOP': return '446'; break;
			case 'MRO': return '478'; break;
			case 'MTL': return '470'; break;
			case 'MUR': return '480'; break;
			case 'MVR': return '462'; break;
			case 'MWK': return '454'; break;
			case 'MXN': return '484'; break;
			case 'MXV': return '979'; break;
			case 'MYR': return '458'; break;
			case 'MZM': return '508'; break;
			case 'NAD': return '516'; break;
			case 'NGN': return '566'; break;
			case 'NIO': return '558'; break;
			case 'NOK': return '578'; break;
			case 'NPR': return '524'; break;
			case 'NZD': return '554'; break;
			case 'OMR': return '512'; break;
			case 'PAB': return '590'; break;
			case 'PEN': return '604'; break;
			case 'PGK': return '598'; break;
			case 'PHP': return '608'; break;
			case 'PKR': return '586'; break;
			case 'PLN': return '985'; break;
			case 'PYG': return '600'; break;
			case 'QAR': return '634'; break;
			case 'ROL': return '642'; break;
			case 'RUB': return '643'; break;
			case 'RUR': return '810'; break;
			case 'RWF': return '646'; break;
			case 'SAR': return '682'; break;
			case 'SBD': return '090'; break;
			case 'SCR': return '690'; break;
			case 'SDD': return '736'; break;
			case 'SEK': return '752'; break;
			case 'SGD': return '702'; break;
			case 'SHP': return '654'; break;
			case 'SIT': return '705'; break;
			case 'SKK': return '703'; break;
			case 'SLL': return '694'; break;
			case 'SOS': return '706'; break;
			case 'SRG': return '740'; break;
			case 'STD': return '678'; break;
			case 'SVC': return '222'; break;
			case 'SYP': return '760'; break;
			case 'SZL': return '748'; break;
			case 'THB': return '764'; break;
			case 'TJS': return '972'; break;
			case 'TMM': return '795'; break;
			case 'TND': return '788'; break;
			case 'TOP': return '776'; break;
			case 'TPE': return '626'; break;
			case 'TRL': return '792'; break;
			case 'TRY': return '949'; break;
			case 'TTD': return '780'; break;
			case 'TWD': return '901'; break;
			case 'TZS': return '834'; break;
			case 'UAH': return '980'; break;
			case 'UGX': return '800'; break;
			case 'USD': return '840'; break;
			case 'UYU': return '858'; break;
			case 'UZS': return '860'; break;
			case 'VEB': return '862'; break;
			case 'VND': return '704'; break;
			case 'VUV': return '548'; break;
			case 'XAF': return '950'; break;
			case 'XCD': return '951'; break;
			case 'XOF': return '952'; break;
			case 'XPF': return '953'; break;
			case 'YER': return '886'; break;
			case 'YUM': return '891'; break;
			case 'ZAR': return '710'; break;
			case 'ZMK': return '894'; break;
			case 'ZWD': return '716'; break;
	    	}
	    return '208';
	  	}
}