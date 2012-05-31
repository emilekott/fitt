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
class plgHikashoppaymentCMCIC extends JPlugin
{
	var $accepted_currencies = array(
		'EUR',
		'USD',
		'GBP',
		'CHF'
	);
	function onPaymentDisplay(&$order,&$methods,&$usable_methods) {
		if(!empty($methods)){
			foreach($methods as $method){
				if($method->payment_type!='cmcic' || !$method->enabled){
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
		$tax_total = '';
		$discount_total = '';
		$encoding = hikashop_get('helper.encoding');
		$currencyClass = hikashop_get('class.currency');
		$currencies = null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency = $currencies[$order->order_currency_id];
		hikashop_loadUser(true,true); //reset user data in case the emails were changed in the email code
		$user = hikashop_loadUser(true);
		$lang = &JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));
		$httpsHikashop = HIKASHOP_LIVE; //str_replace('http://','https://', HIKASHOP_LIVE);
		$notify_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=cmcic&tmpl=component&orderId='.$order->order_id.'&lang='.$locale;
		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=cmcic&tmpl=component&cmcic_return=1&orderId='.$order->order_id.'&lang='.$locale;
		$localeCM = 'FR';
		if( in_array($locale, array('fr','en','de','it','es','nl','pt')) ) {
			$localCM = strtoupper($locale);
		}
		if( @$method->payment_params->debug ) {
			$urls = array(
				'cm' => 'https://paiement.creditmutuel.fr/test/paiement.cgi',
				'cic' => 'https://ssl.paiement.cic-banques.fr/test/paiement.cgi',
				'obc' => 'https://ssl.paiement.banque-obc.fr/test/paiement.cgi'
			);
		} else {
			$urls = array(
				'cm' => 'https://paiement.creditmutuel.fr/paiement.cgi',
				'cic' => 'https://ssl.paiement.cic-banques.fr/paiement.cgi',
				'obc' => 'https://ssl.paiement.banque-obc.fr/paiement.cgi'
			);
		}
		if(@$method->payment_params->bank && !in_array($method->payment_params->bank, $urls) ) {
			$method->payment_params->bank = 'cm';
		}
		$url = @$urls[@$method->payment_params->bank];
		$vars = Array(
			'TPE' => @$method->payment_params->tpe,
			'date' => date('d/m/Y:H:i:s'),
			'montant' => number_format($order->cart->full_total->prices[0]->price_value_with_tax, 2, '.', '') . $currency->currency_code,
			'reference' => $order->order_number,
			'texte-libre' => '',
			'version' => '3.0',
			'lgue' => $localeCM,
			'societe' => @$method->payment_params->societe,
			'mail' => $user->user_email,
		);
		$vars['MAC'] = $this->generateHash($vars, @$method->payment_params->key, 19);
		if( @$method->payment_params->debug ) {
			echo 'Sent Data<pre>';
			echo var_export($vars, true);
			echo '</pre>';
		}
		$vars['url_retour'] = HIKASHOP_LIVE . 'index.php?option=com_hikashop';
		$vars['url_retour_ok'] = $return_url;
		$vars['url_retour_err'] = $return_url;
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
		$elements = $pluginsClass->getMethods('payment','cmcic');
		if(empty($elements)) return false;
		$element = reset($elements);
		$finalReturn = isset($_GET['cmcic_return']);
		if( $finalReturn ) {
			$vars = array(
				'reference' => @$_GET['orderId']
			);
		} else {
			$vars = array(
				'TPE' => $element->payment_params->tpe,
				'date' => @$_POST['date'],
				'montant' => @$_POST['montant'],
				'reference' => @$_POST['reference'],
				'texte-libre' => @$_POST['texte-libre'],
				'version' => '3.0',
				'code-retour' => @$_POST['code-retour'],
				'cvx' => @$_POST['cvx'],
				'vld' => @$_POST['vld'],
				'brand' => @$_POST['brand'],
				'status3ds' => @$_POST['status3ds'],
				'numauto' => @$_POST['numauto'],
				'motifrefus' => @$_POST['motifrefus'],
				'originecb' => @$_POST['originecb'],
				'bincb' => @$_POST['bincb'],
				'hpancb' => @$_POST['hpancb'],
				'ipclient' => @$_POST['ipclient'],
				'originetr' => @$_POST['originetr']
			);
		}
		if($element->payment_params->debug){
			echo print_r($vars,true)."\r\n\r\n";
		}
		if( !$finalReturn && empty($_POST['MAC']) ) {
			$msg = ob_get_clean();
			echo "version=2\ncdr=1\n";
			$msg .= "\r\n".'POST[MAC] not present';
			if( $element->payment_params->debug )
				$this->writeToLog($msg);
			exit;
		}
		if( !$finalReturn && ( $_POST['TPE'] != $element->payment_params->tpe ) ) {
			$msg = ob_get_clean();
			echo "version=2\ncdr=1\n";
			ob_start();
			$msg .= "\r\n".'POST[TPE] invalid ("'.$_POST['TPE'].'" != "'.$element->payment_params->tpe.'")';
			if( $element->payment_params->debug )
				$this->writeToLog($msg);
			exit;
		}
		if( !$finalReturn && ( strtolower($_POST['MAC']) != $this->generateHash($vars, $element->payment_params->key, 21) ) ) {
			$msg = ob_get_clean();
			echo "version=2\ncdr=1\n";
			$msg .= "\r\n".'POST[MAC] invalid ("'.$_POST['MAC'].'" != "'.$this->generateHash($vars, $element->payment_params->key, 21).'")';
			if( $element->payment_params->debug )
				$this->writeToLog($msg);
			exit;
		}
		if( !$finalReturn ) {
			$db =& JFactory::getDBO();
			$db->setQuery("SELECT order_id FROM ".hikashop_table('order')." WHERE order_number=".$db->Quote($vars['reference']).";");
			$order_id = $db->loadObjectList();
			if( isset($order_id[0]) && isset($order_id[0]->order_id) ) {
				$order_id = (int)$order_id[0]->order_id;
			} else {
				$order_id = 0;
			}
		} else {
			$order_id = (int)$vars['reference'];
		}
		$orderClass = hikashop_get('class.order');
		$dbOrder = $orderClass->get((int)$order_id);
		if(empty($dbOrder)){
			if( $finalReturn ) {
				$msg = ob_get_clean();
				echo "Could not load any order for your notification ".$vars['reference'];
				return false;
			}
			$msg = ob_get_clean();
			echo "version=2\ncdr=1\n";
			$msg .= "\r\n".'POST[reference] invalid ("'.$vars['reference'].'")';
			if( $element->payment_params->debug )
				$this->writeToLog($msg);
			exit;
		}
		$order = null;
		$order->order_id = $dbOrder->order_id;
		$order->old_status->order_status = $dbOrder->order_status;
		$httpsHikashop = HIKASHOP_LIVE; //str_replace('http://','https://', HIKASHOP_LIVE);
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$url_itemid;
		$cancel_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$url_itemid;
		if($finalReturn) {
			$app =& JFactory::getApplication();
			if( $dbOrder->order_status != $element->payment_params->verified_status ) {
				$app->enqueueMessage('Transaction declined.');
				$app->redirect($cancel_url);
			}
			$db =& JFactory::getDBO();
			$db->setQuery("SELECT * FROM ". hikashop_table('history') ." WHERE history_order_id=". $dbOrder->order_id." AND history_new_status=".$db->Quote($element->payment_params->verified_status)." ORDER BY history_created DESC;");
			$histories = $db->loadObjectList();
			foreach( $histories as $history ) {
				$data = $history->history_data;
				if( strpos($data, "\n--\n") !== false ) {
					$data = trim(substr($data, 0, strpos($data, "\n--\n")));
					$app->enqueueMessage($data);
					break;
				}
			}
			$app->redirect($return_url);
		}
		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id.$url_itemid;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified = 0;
		$order->history->history_amount = $vars['montant'];
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method =$element->payment_type;
		$order->history->history_data = $vars['numauto'] . "\r\n" . ob_get_clean();
		$order->history->history_type = 'payment';
		$mailer =& JFactory::getMailer();
		$config =& hikashop_config();
		$sender = array(
			$config->get('from_email'),
			$config->get('from_name') );
		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));
		$currencyClass = hikashop_get('class.currency');
		$currencies = null;
		$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
		$currency = $currencies[$dbOrder->order_currency_id];
		if( $element->payment_params->debug ) {
			$completed = ($vars['code-retour'] == 'payetest');
		} else {
			$completed = ($vars['code-retour'] == 'paiement');
		}
		if($completed) {
			$order->order_status = $element->payment_params->verified_status;
			$order->history->history_notified = 1;
			$payment_status = 'confirmed';
		} else {
			$order->order_status = $element->payment_params->invalid_status;
			$payment_status = 'cancelled';
			$order_text = $vars['motifrefus']."\r\n\r\n".$order_text;
		}
		$order->mail_status = $statuses[$order->order_status];
		$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION','CMCIC',$payment_status));
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','CMCIC',$payment_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->mail_status)."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
		$orderClass->save($order);
		$msg = ob_get_clean();
		echo "version=2\ncdr=0\n";
		if( $element->payment_params->debug )
			$this->writeToLog($msg);
		exit;
	}
	function writeToLog($data) {
		if( $data === null ) {
			$dbg .= ob_get_clean();
		} else {
			$dbg = $data;
		}
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
		if( $data === null ) {
			ob_start();
		}
	}
	function onPaymentConfiguration(&$element){
		$this->cmcic = JRequest::getCmd('name','cmcic');
		if(empty($element)){
			$element = null;
			$element->payment_name='CMCIC';
			$element->payment_description='You can pay by credit card using this payment method';
			$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
			$element->payment_type=$this->cmcic;
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
		$bar->appendButton( 'Pophelp','payment-cmcic-form');
		hikashop_setTitle('CMCIC','plugin','plugins&plugin_type=payment&task=edit&name='.$this->cmcic);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->cmcic);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
	}
	function onPaymentConfigurationSave(&$element){
		return true;
	}
	function generateHash($vars, $key, $nb) {
		$str = implode('*',$vars);
		$l = $nb - count($vars);
		$str .= str_pad('', $l, '*');
		$hexStrKey = substr($key, 0, 38);
		$hexFinal = "" . substr($key, 38, 2) . "00";
		$cca0 = ord($hexFinal);
		if ($cca0>70 && $cca0<97) {
			$hexStrKey .= chr($cca0-23) . substr($hexFinal, 1, 1);
		} else {
			if (substr($hexFinal, 1, 1)=="M")  {
				$hexStrKey .= substr($hexFinal, 0, 1) . "0";
			} else {
				$hexStrKey .= substr($hexFinal, 0, 2);
			}
		}
		$hKey = pack("H*", $hexStrKey);
		return strtolower($this->hmacsha1($str, $hKey));
	}
	function hmacsha1($data,$key) {
		if( function_exists('hash_hmac') ) {
			return hash_hmac('sha1', $data, $key);
		}
		if( !function_exists('sha1') ) {
			die('SHA1 function is not present');
		}
		if (strlen($key) > 64) {
			$key = pack('H*',sha1($key));
		}
		$key  = str_pad($key, 64, chr(0x00));
		$ipad = str_pad('', 64, chr(0x36));
		$opad = str_pad('', 64, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;
		return sha1($k_opad.pack('H*',sha1($k_ipad.$data)));
	}
}
