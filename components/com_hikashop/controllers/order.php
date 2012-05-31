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
class orderController extends hikashopController{
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		$this->display[]='cancel';
		$this->display[]='invoice';
		$this->display[]='download';
		$this->display[]='pay';
		$this->display[]='cancel_order';
	}
	function authorize($task){
		if($this->isIn($task,array('display'))){
			return true;
		}
		return false;
	}
	function listing(){
		$user_id = hikashop_loadUser();
		if(empty($user_id)){
			$app=&JFactory::getApplication();
			$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
			global $Itemid;
			$url = '';
			if(!empty($Itemid)){
				$url='&Itemid='.$Itemid;
			}
			if(version_compare(JVERSION,'1.6','<')){
				$url = 'index.php?option=com_user&view=login'.$url;
			}else{
				$url = 'index.php?option=com_users&view=login'.$url;
			}
			$app->redirect(JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl())),false));
			return true;
		}
		return parent::listing();
	}
	function show(){
		if($this->_check()){
			return parent::show();
		}
		return true;
	}
	function cancel_order(){
		$app =& JFactory::getApplication();
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			$order_id = $app->getUserState( HIKASHOP_COMPONENT.'.order_id');
		}
		$class = hikashop_get('class.order');
		$order = $class->get($order_id);
		$config =& hikashop_config();
		$checkout = explode(',',$config->get('checkout'));
		$step = max(count($checkout)-2,0);
		$itemid_for_checkout = $config->get('checkout_itemid','0');
		$item ='';
		if(!empty($itemid_for_checkout)){
			$item='&Itemid='.(int)$itemid_for_checkout;
		}
		$cancel_url =  hikashop_completeLink('checkout&step='.$step.$item,false,true);
		if(!empty($order)){
			$user_id = hikashop_loadUser();
			if($order->order_user_id==$user_id){
				$status = $config->get('cancelled_order_status');
				$created_status = $config->get('order_created_status');
				$cancellable_statuses = explode(',',$config->get('cancellable_order_status'));
				if( $order->order_status == $created_status || in_array($order->order_status, $cancellable_statuses) ) {
					if(!empty($status)){
						$statuses = explode(',',$status);
						$newOrder = null;
						$newOrder->order_status = reset($statuses);
						$newOrder->order_id = $order_id;
						$class->save($newOrder);
						if( JRequest::getVar('email',false) ) {
							$mailClass = hikashop_get('class.mail');
							$infos = null;
							$infos =& $order;
							$mail = $mailClass->get('order_cancel',$infos);
							if( !empty($mail) ) {
								$mail->subject = JText::sprintf($mail->subject,HIKASHOP_LIVE);
								$config =& hikashop_config();
								if(!empty($infos->email)){
									$mail->dst_email = $infos->email;
								}else{
									$mail->dst_email = $config->get('from_email');
								}
								if(!empty($infos->name)){
									$mail->dst_name = $infos->name;
								}else{
									$mail->dst_name = $config->get('from_name');
								}
								$mailClass->sendMail($mail);
							}
						}
					}
				}
			}
			$db =& JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($order->order_payment_method).' AND payment_id='.$db->Quote($order->order_payment_id);
			$db->setQuery($query);
			$paymentData = $db->loadObjectList();
			$pluginsClass = hikashop_get('class.plugins');
			$pluginsClass->params($paymentData,'payment');
			$paymentOptions=reset($paymentData);
			if(!empty($paymentOptions->payment_params->cancel_url)){
				$cancel_url = $paymentOptions->payment_params->cancel_url;
			}
		}
		$redirect_url = JRequest::getVar('redirect_url');
		if( !empty($redirect_url) )
			$cancel_url = $redirect_url;
		$app->redirect($cancel_url);
		return true;
	}
	function invoice(){
		if($this->_check()){
			JRequest::setVar( 'layout', 'invoice'  );
			return parent::display();
		}
		return true;
	}
	function pay(){
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			parent::listing();
			return false;
		}
		$class = hikashop_get('class.order');
		$order = $class->loadFullOrder($order_id,true);
		if(empty($order)){
			$app =& JFactory::getApplication();
			$app->enqueueMessage('The order '.$order_id.' could not be found');
			parent::listing();
			return false;
		}
		$new_payment_method = JRequest::getVar('new_payment_method','');
		$config =& hikashop_config();
		if($config->get('allow_payment_change',1) && !empty($new_payment_method)){
			$new_payment_method = explode('_',$new_payment_method);
			$payment_id = array_pop($new_payment_method);
			$payment_method = implode('_',$new_payment_method);
			if($payment_id!=$order->order_payment_id || $payment_method!=$order->order_payment_method){
				$updateOrder=null;
				$updateOrder->order_id=$order->order_id;
				$updateOrder->order_payment_id = $payment_id;
				$updateOrder->order_payment_method = $payment_method;
				$updateOrder->history = null;
				$updateOrder->history->history_payment_id = $payment_id;
				$updateOrder->history->history_payment_method = $payment_method;
				$class->save($updateOrder);
				$order->order_payment_id = $payment_id;
				$order->order_payment_method = $payment_method;
			}
		}
		$userClass = hikashop_get('class.user');
		$order->customer = $userClass->get($order->order_user_id);
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($order->order_payment_method);
		$db->setQuery($query);
		$paymentData = $db->loadObjectList('payment_id');
		$pluginsClass = hikashop_get('class.plugins');
		$pluginsClass->params($paymentData,'payment');
		if(empty($paymentData)){
			$app =& JFactory::getApplication();
			$app->enqueueMessage('The payment method '.$order->order_payment_method.' could not be found');
			parent::listing();
			return false;
		}
		$order->cart =& $order;
		$order->cart->coupon = null;
		$price = null;
		$price->price_value_with_tax = $order->order_full_price;
		$order->cart->full_total = null;
		$order->cart->full_total->prices = array($price);
		$price2 = null;
		$total = 0;
		$class = hikashop_get('class.currency');
		$order->cart->total = null;
		$price2 = $class->calculateTotal($order->products,$order->cart->total,$order->order_currency_id);
		$order->cart->coupon->discount_value =& $order->order_discount_price;
		$shippingClass = hikashop_get('class.shipping');
		$methods = $shippingClass->getMethods($order->cart);
		$data = hikashop_import('hikashopshipping',$order->order_shipping_method);
		if(!empty($data)) $order->cart->shipping = $data->onShippingSave($order->cart,$methods,$order->order_shipping_id);
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',$order->order_shipping_address_id);
		$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',$order->order_billing_address_id);
		ob_start();
		$data = hikashop_import('hikashoppayment',$order->order_payment_method);
		if(!empty($data)){
			$needCC = false;
			if( method_exists($data, 'needCC') ) {
				$method =& $paymentData[$order->order_payment_id];
				$needCC = $data->needCC($method);
			}
			if( !$needCC ) {
				$data->onAfterOrderConfirm($order,$paymentData,$order->order_payment_id);
			} else {
				$paymentClass = hikashop_get('class.payment');
				$do = false;
				$app->setUserState( HIKASHOP_COMPONENT.'.payment_method',$order->order_payment_method);
				$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',$order->order_payment_id);
				$app->setUserState( HIKASHOP_COMPONENT.'.payment_data',$method);
				if( $paymentClass->readCC() ) {
					$do = true;
					$data->onBeforeOrderCreate($order, $do);
				}
				if( !$do ) {
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_number','');
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_month','');
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_year','');
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV','');
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_type','');
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_owner','');
					$params = '';
					$js = '';
					echo hikashop_getLayout('checkout','ccinfo',$params,$js);
				} else {
					$order->history->history_notified = 1;
					$class = hikashop_get('class.order');
					$updateOrder=null;
					$updateOrder->order_id=$order->order_id;
					$updateOrder->order_status=$order->order_status;
					$updateOrder->order_payment_id = $payment_id;
					$updateOrder->order_payment_method = $payment_method;
					$updateOrder->history =& $order->history;
					$class->save($updateOrder);
					$app->redirect( hikashop_completeLink('checkout&task=after_end', false, true) );
				}
			}
		}
		$html = ob_get_clean();
		if(empty($html)){
			$app =& JFactory::getApplication();
			$app->enqueueMessage('The payment method '.$order->order_payment_method.' does not handle payments after the order has been created');
			parent::listing();
			return false;
		}
		echo $html;
		return true;
	}
	function download(){
		$file_id = JRequest::getInt('file_id');
		if(empty($file_id)){
			$field_table = JRequest::getWord('field_table');
			$field_namekey = base64_decode(urldecode(JRequest::getString('field_namekey')));
			$name = base64_decode(urldecode(JRequest::getString('name')));
			if(empty($field_table)||empty($field_namekey)||empty($name)){
				$app=&JFactory::getApplication();
				$app->enqueueMessage(JText::_('FILE_NOT_FOUND'));
				return false;
			}else{
				$fileClass = hikashop_get('class.file');
				$fileClass->downloadFieldFile($name,$field_table,$field_namekey);
			}
		}
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			parent::listing();
			return false;
		}
		$fileClass = hikashop_get('class.file');
		if(!$fileClass->download($file_id,$order_id)){
			switch($fileClass->error_type){
				case 'login':
					$this->_check();
					break;
				case 'no_order';
					parent::listing();
					break;
				default:
					parent::show();
					break;
			}
		}
		return true;
	}
	function _check(){
		$user_id = hikashop_loadUser();
		if(empty($user_id)){
			$app=&JFactory::getApplication();
			$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
			global $Itemid;
			$url = '';
			if(!empty($Itemid)){
				$url='&Itemid='.$Itemid;
			}
			if(version_compare(JVERSION,'1.6','<')){
				$url = 'index.php?option=com_user&view=login'.$url;
			}else{
				$url = 'index.php?option=com_users&view=login'.$url;
			}
			$app->redirect(JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl())),false));
			return false;
		}
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			parent::listing();
			return false;
		}
		return true;
	}
	function cancel(){
		$cancel_redirect = JRequest::getString('cancel_redirect');
		if(empty($cancel_redirect)){
			$cancel_url = JRequest::getString('cancel_url');
			if(!empty($cancel_url)){
				$this->setRedirect(base64_decode(urldecode($cancel_redirect)));
			}else{
				$order_id = hikashop_getCID('order_id');
				if(empty($order_id)){
					global $Itemid;
					$url = '';
					if(!empty($Itemid)){
						$url='&Itemid='.$Itemid;
					}
					$this->setRedirect(hikashop_completeLink('user'.$url,false,true));
				}else{
					return $this->listing();
				}
			}
		}else{
			$this->setRedirect(urldecode($cancel_redirect));
		}
	}
}