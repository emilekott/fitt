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
class checkoutController extends hikashopController{
	var $cart_update=false;
	var $modify_views = array();
	var $add = array();
	var $modify = array();
	var $delete = array();
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		$this->display=array('step','notice','state','deleteaddress','notify','after_end','activate_page','activate','resetcart','threedsecure','printcart');
		if(!$skip){
			$this->registerDefaultTask('step');
		}
		$conf =& hikashop_config();
		$this->checkout_workflow = trim($conf->get('checkout','login_address_shipping_payment_coupon_cart_status_confirm,end'));
		$this->steps=explode(',',$this->checkout_workflow);
		$this->redirect_url = $conf->get('redirect_url_when_cart_is_empty');
		if(empty($this->redirect_url)){
			global $Itemid;
			$url = '';
			if(!empty($Itemid)){
				$url='&Itemid='.$Itemid;
			}
			$this->redirect_url=hikashop_completeLink('product&task=listing'.$url,false,true);
		}else{
			if(!preg_match('#^https?://#',$this->redirect_url)) $this->redirect_url = JURI::base().ltrim($this->redirect_url,'/');
			$this->redirect_url = JRoute::_($this->redirect_url,false);
		}
	}
	function authorize($task){
		if($this->isIn($task,array('display'))){
			return true;
		}
		return false;
	}
	function printcart(){
		JRequest::setVar( 'layout', 'printcart' );
		return parent::display();
	}
	function notice(){
		JRequest::setVar( 'layout', 'notice' );
		return parent::display();
	}
	function resetcart(){
		$cart = hikashop_get('class.cart');
		$cart->resetCart();
		$app =& JFactory::getApplication();
		$app->redirect( $this->redirect_url );
	}
	function activate(){
		$app =& JFactory::getApplication();
		$db			=& JFactory::getDBO();
		$user 		=& JFactory::getUser();
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$userActivation			= $usersConfig->get('useractivation');
		$allowUserRegistration	= $usersConfig->get('allowUserRegistration');
		if ($user->get('id')) {
			$app->redirect( hikashop_completeLink('checkout',false,true) );
		}
		if ($allowUserRegistration == '0' || $userActivation == '0') {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}
		$lang =& JFactory::getLanguage();
		$lang->load('com_user',JPATH_SITE);
		jimport('joomla.user.helper');
		$activation = $db->getEscaped(JRequest::getVar('activation', '', '', 'alnum' ));
		if (empty( $activation )){
			$app->enqueueMessage(JText::_( 'HIKA_REG_ACTIVATE_NOT_FOUND' ));
			return;
		}
		if(version_compare(JVERSION,'1.6','<')){
			$result = JUserHelper::activateUser($activation);
		}else{
			JModel::addIncludePath(HIKASHOP_ROOT . DS . 'components' . DS . 'com_users' . DS . 'models');
			$model = $this->getModel('Registration', 'UsersModel');
			if($model) $result = $model->activate($activation);
		}
		if(!$result){
			$app->enqueueMessage(JText::_( 'HIKA_REG_ACTIVATE_NOT_FOUND' ));
			return;
		}else{
			$app->enqueueMessage(JText::_( 'HIKA_REG_ACTIVATE_COMPLETE' ));
			$id = JRequest::getInt('id',0);
			$class = hikashop_get('class.user');
			$user = $class->get($id);
			if($id && file_exists(JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'comprofiler.php') && $userActivation<2){
				 $db->setQuery('REPLACE #__comprofiler (cbactivation,id,user_id) VALUES (\'\','.$user->user_cms_id.','.$user->user_cms_id.')');
				 $db->query();
			}
			$infos = JRequest::getVar('infos','');
			global $Itemid;
			$url = '';
			if(!empty($Itemid)){
				$url='&Itemid='.$Itemid;
			}
			if(!empty($infos)){
				$infos = unserialize(base64_decode($infos));
				JPluginHelper::importPlugin('user');
				if($userActivation<2 && !empty($infos['passwd']) && !empty($infos['username']) && $this->_doLogin($infos['username'],$infos['passwd'],false)){
					$page = JRequest::getString('page','checkout');
					if($page=='checkout'){
						$this->before_address();
						$app->redirect( hikashop_completeLink('checkout'.$url,false,true) );
					}else{
						return true;
					}
				}
			}
			if(version_compare(JVERSION,'1.6','<')){
				$url = 'index.php?option=com_user&view=login'.$url;
			}else{
				$url = 'index.php?option=com_users&view=login'.$url;
			}
			$app->redirect( JRoute::_($url,false) );
		}
	}
	function activate_page(){
	}
	function state(){
		JRequest::setVar( 'layout', 'state' );
		return parent::display();
	}

	function deleteaddress(){
		$addressdelete = JRequest::getInt('address_id',0);
		if($addressdelete){
			JRequest::checkToken('request') or jexit( 'Invalid Token' );
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($addressdelete);
			if(!empty($oldData)){
				$user_id = hikashop_loadUser();
				if($user_id==$oldData->address_user_id){
					$addressClass->delete($addressdelete);
					$app=&JFactory::getApplication();
					$oldShip = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
					$oldBill = $app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
					if($oldShip==$addressdelete){
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',0);
					}
					if($oldBill==$addressdelete){
						$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',0);
					}
				}
			}
		}
		$this->step();
	}
	function step(){
		$class = hikashop_get('class.cart');
		$class->get();
		if(empty($class->cart->cart_id)){
			$this->setRedirect( $this->redirect_url, JText::_('CART_EMPTY'));
			return true;
		}
		$config =& hikashop_config();
		global $Itemid;
		$redirect = false;
		$ssl = false;
		$new_item_id = $Itemid;
		$itemid_for_checkout = $config->get('checkout_itemid','0');
		if(!empty($itemid_for_checkout)){
			if($new_item_id!=$itemid_for_checkout && empty($_SESSION['hikashop_new_itemid'])){
				$new_item_id=$itemid_for_checkout;
				$_SESSION['hikashop_new_itemid']=$new_item_id;
				$redirect = true;
			}else{
				$_SESSION['hikashop_new_itemid']='';
			}
		}
		$app =& JFactory::getApplication();
		if($config->get('force_ssl',0) && $app->getUserState('com_hikashop.ssl_redirect')!=1){
			if (!hikashop_isSSL()){
				$ssl = 1;
				$redirect = true;
				$app->setUserState('com_hikashop.ssl_redirect',1);
     		}
		}
		if($redirect){
			$url = '';
			if(!empty($new_item_id)){
				$url='&Itemid='.$new_item_id;
			}
			$this->setRedirect( JRoute::_('index.php?option='.HIKASHOP_COMPONENT.'&ctrl=checkout'.$url,false,$ssl));
			return true;
		}
		$go_back=false;
		$this->previous = JRequest::getInt('previous',0);
		$this->current = JRequest::getInt('step',0);
		if(isset($_REQUEST['previous'])){
			if(!isset($this->steps[$this->previous])){
				$this->previous = 0;
			}
			$this->controllers = trim($this->steps[$this->previous]);
			$this->controllers=explode('_',$this->controllers);
			$newArray = array();
			$found = false;
			$cart = false;
			$coupon = false;
			foreach($this->controllers as $v){
				if($v=='confirm'){
					$found = true;
				}elseif($v=='cart'){
					$cart = true;
				}elseif($v=='coupon'){
					$coupon = true;
				}else{
					$newArray[]=$v;
				}
			}
			if($cart){
				array_unshift($newArray,'cart');
			}
			if($coupon){
				array_unshift($newArray,'coupon');
			}
			if($found){
				$newArray[]='confirm';
			}
			$this->controllers=$newArray;
			$this->beforeControllers=$newArray;
			foreach($this->controllers as $controller){
				$controller = 'after_'.trim($controller);
				if(method_exists($this,$controller)){
					if(!$this->$controller(!$go_back)){
						$go_back=true;
					}
				}
			}
		}
		if($go_back){
			JRequest::setVar('step',$this->previous);
		}else{
			$this->controllers = trim($this->steps[$this->current]);
			$this->controllers=explode('_',$this->controllers);
			foreach($this->controllers as $controller){
				$controller = 'before_'.trim($controller);
				if(method_exists($this,$controller)){
					if(!$this->$controller()){
						$go_back=true;
					}
				}
			}
			if($go_back && isset($this->previous)){
				JRequest::setVar('step',$this->previous);
			}
		}
		JRequest::setVar( 'layout', 'step' );
		return $this->display();
	}
	function before_coupon(){
		return true;
	}
	function after_coupon($success){
		$coupon = JRequest::getString('coupon','');
		$qty = 1;
		if(empty($coupon)){
			$coupon = JRequest::getInt('removecoupon',0);
			$qty = 0;
		}
		if(!empty($coupon)){
			$class = hikashop_get('class.cart');
			if($class->update($coupon,$qty,0,'coupon')){
				if(strpos($this->checkout_workflow,'shipping')!==false){
					$this->before_shipping(true);
				}
				$this->initCart(true);
				$this->cart_update = true;
				return false;
			}
		}
		return true;
	}
	function check_coupon(){
		return true;
	}
	function before_terms(){
		return true;
	}
	function after_terms(){
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.checkout_terms', JRequest::getInt('hikashop_checkout_terms',0) );
		return true;
	}
	function check_terms(){
		$app =& JFactory::getApplication();
		$status = (bool)$app->getUserState( HIKASHOP_COMPONENT.'.checkout_terms',0 );
		if(!$status){
			$app->enqueueMessage(JText::_('PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER'));
		}
		return $status;
	}
	function before_fields(){
		return true;
	}
	function after_fields(){
		if(hikashop_level(2)){
			$app =& JFactory::getApplication();
			$old = $app->getUserState( HIKASHOP_COMPONENT.'.checkout_fields_ok',0);
			$oldData = $app->getUserState( HIKASHOP_COMPONENT.'.checkout_fields');
			$fieldClass = hikashop_get('class.field');
			$orderData = $fieldClass->getInput('order',$oldData,!$this->cart_update);
			if($orderData!==false){
				$app->setUserState( HIKASHOP_COMPONENT.'.checkout_fields_ok',1);
				$app->setUserState( HIKASHOP_COMPONENT.'.checkout_fields',$orderData);
				$changed = false;
			}
			if($orderData===false || (!empty($orderData) && $changed && $this->_getStep('confirm',(int)$this->previous)===(int)$this->previous)){
				return false;
			}
		}
		return true;
	}
	function check_fields(){
		if(hikashop_level(2)){
			$app =& JFactory::getApplication();
			$status = (bool)$app->getUserState( HIKASHOP_COMPONENT.'.checkout_fields_ok',0 );
			if(!$status){
				$app->enqueueMessage(JText::_('PLEASE_FILL_ADDITIONAL_INFO'));
			}
		}else{
			$status = true;
		}
		return $status;
	}
	function before_cart(){
		$config =& hikashop_config();
		$auto_select_default = $config->get('auto_select_default',2);
		if($auto_select_default){
			$this->before_shipping(true);
			$this->before_payment(true);
		}
		return true;
	}
	function after_cart($success){
		$modified = false;
		$formData = JRequest::getVar( 'item', array(), '', 'array' );
		$class = hikashop_get('class.cart');
		if(!empty($formData)){
			$modified = $class->update($formData,0,0,'item');
		}else{
			$formData = JRequest::getVar( 'data', array(), '', 'array' );
			if(!empty($formData)){
				$modified = $class->update($formData,0,0);
			}
		}
		if($modified){
			$class->get();
			if(strpos($this->checkout_workflow,'shipping')!==false){
				$this->before_shipping(true);
			}
			if(empty($class->cart->cart_id)){
				$this->setRedirect( $this->redirect_url,JText::_('CART_EMPTY'));
				$this->redirect();
			}
			$this->initCart(true);
			$this->cart_update = true;
			return false;
		}
		return true;
	}
	function check_cart(){
		return true;
	}
	function before_login(){
		if(count($this->controllers)==1){
			$user =& JFactory::getUser();
			$app =& JFactory::getApplication();
			$user_id=$app->getUserState( HIKASHOP_COMPONENT.'.user_id' );
			if(!$user->guest || $user_id){
				JRequest::setVar('step',$this->current+1);
				JRequest::setVar('previous',$this->current);
				$this->step();
			}
		}
		return true;
	}
	function after_login($success){
		$user =& JFactory::getUser();
		$status = true;
		$app =& JFactory::getApplication();
		$user_id=$app->getUserState( HIKASHOP_COMPONENT.'.user_id' );
		if($user->guest && empty($user_id)){
			JPluginHelper::importPlugin('user');
			$register=JRequest::getString('register','');
			$action = JRequest::getString('login_view_action','');
			if($action=='register' || !empty($register)){
				$status = $this->_doRegister();
			}else{
				$login=JRequest::getString('login','');
				if($action=='login' || !empty($login)){
					$status = $this->_doLogin();
				}else{
					$name = @$_REQUEST['data']['register']['email'];
					$username = JRequest::getVar('username', '', 'request', 'username');
					if(!empty($name)){
						$status = $this->_doRegister();
					}elseif(!empty($username)){
						$status = $this->_doLogin();
					}
				}
			}
			if($status){
				if($this->_getStep('address',$this->previous)!==false){
					$status = false;
				}
				if(!$this->before_address()){
					$status = false;
				}
			}
		}
		return $status;
	}
	function _doRegister(){
		$class = hikashop_get('class.user');
		$status = $class->register($this);
		$app =& JFactory::getApplication();
		if($status){
			$this->cart_update=true;
			$app->setUserState( HIKASHOP_COMPONENT.'.user_id',$class->user_id );
			$config =& hikashop_config();
			$simplified = $config->get('simplified_registration',0);
			if($simplified!=2){
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',$class->address_id );
				$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',$class->address_id );
				$usersConfig = &JComponentHelper::getParams( 'com_users' );
				$useractivation = $usersConfig->get( 'useractivation' );
				if ( $useractivation != 1 ) {
					$this->_doLogin($class->registerData->username,$class->registerData->password);
				}
			}else{
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',0 );
				$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',0 );
			}
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method','');
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',0);
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',null);
		}
		return $status;
	}
	function _doLogin($user='',$pass='',$checkToken=true){
		$options = array();
		$options['remember'] = JRequest::getBool('remember', false);
		$options['return'] = false;
		$credentials = array();
		if(empty($user)){
			$credentials['username'] = JRequest::getVar('username', '', 'request', 'username');
		}else{
			$credentials['username'] = $user;
		}
		if(empty($pass)){
			$credentials['password'] = JRequest::getString('passwd', '', 'request', JREQUEST_ALLOWRAW);
		}else{
			$credentials['password'] = $pass;
		}

		$mainframe =& JFactory::getApplication();
		$error = $mainframe->login($credentials, $options);
		$user =& JFactory::getUser();
		if(JError::isError($error) || $user->guest){
			return false;
		}
		$this->cart_update=true;
		$class = hikashop_get('class.user');
		$user_id = $class->getID($user->get('id'));
		if($user_id){
			$app =& JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.user_id',$user_id );
		}
		return true;
	}
	function check_login(){
		$logged=(bool)hikashop_loadUser();
		if(!$logged){
			$app =& JFactory::getApplication();
			$app->enqueueMessage( JText::_('LOGIN_OR_REGISTER_ACCOUNT') );
		}
		return $logged;
	}
	function before_address(){
		$status = $this->_checkLogin();
		if($status){
			$user_id = hikashop_loadUser();
			if($user_id){
				$app =& JFactory::getApplication();
				$shipping = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_address',0 );
				$billing = $app->getUserState( HIKASHOP_COMPONENT.'.billing_address',0 );
				if(empty($shipping) || empty($billing)){
					$db =& JFactory::getDBO();
					$db->setQuery('SELECT address_id FROM '.hikashop_table('address').' WHERE address_published=1 AND address_user_id='.$user_id);
					$address_id = $db->loadResult();
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',$address_id );
					$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',$address_id );
					if(strpos($this->checkout_workflow,'shipping')!==false){
						if(!$this->before_shipping(true) && $this->_getStep('shipping',$this->previous)!==false){
							$status = false;
						}
					}
					if(strpos($this->checkout_workflow,'payment')!==false){
						if(!$this->before_payment(true) && $this->_getStep('payment',$this->previous)!==false){
							$status = false;
						}
					}
					$this->initCart(true);
					$this->cart_update = true;
					$this->initCart();
				}
			}
		}
		return $status;
	}
	function after_address($success){
		if($this->cart_update){
			return true;
		}
		$billing = JRequest::getInt('hikashop_address_billing',0);
		if(empty($billing)){
			if(!$this->cart_update){
				$app =& JFactory::getApplication();
				$app->enqueueMessage( JText::_('CREATE_OR_SELECT_ADDRESS') );
			}
			return false;
		}
		$shipping = JRequest::getInt('hikashop_address_shipping',0);
		if(JRequest::getString('same_address','')=='yes'||empty($shipping)){
			$shipping = $billing;
		}
		$app =& JFactory::getApplication();
		$oldShippingAddress=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address' );
		$oldBillingAddress=$app->getUserState( HIKASHOP_COMPONENT.'.billing_address' );
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',$shipping );
		$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',$billing );
		$shipping_address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address' );
		$billing_address=$app->getUserState( HIKASHOP_COMPONENT.'.billing_address' );
		if($shipping!=$oldShippingAddress){
			$cart = $this->initCart();
			if($cart->has_shipping){
				$this->cart_update=true;
				if(strpos($this->checkout_workflow,'shipping')!==false){
					$this->before_shipping(true);
				}
				if(strpos($this->checkout_workflow,'payment')!==false){
					$this->before_payment(true);
				}
				return false;
			}
		}
		if($billing!=$oldBillingAddress){
			return false;
		}
		return true;
	}
	function check_address(){
		$app =& JFactory::getApplication();
		$shipping_address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address' );
		if(empty($shipping_address)){
			$app->enqueueMessage( JText::_('CREATE_OR_SELECT_ADDRESS') );
			return false;
		}
		return true;
	}
	function before_shipping($directCall=false){
		$ok = true;
		if(!$directCall){
			$ok = $this->_checkLogin();
			if(!$ok){
				return $ok;
			}
		}
		$app =& JFactory::getApplication();
		$shipping_address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
		if(empty($shipping_address) && !$directCall){
			$found = $this->_getStep('address');
			if($found!==false && $found!=$this->current){
				JRequest::setVar('step',$found);
				JRequest::setVar('previous',0);
				$this->step();
				return false;
			}
		}
		$app =& JFactory::getApplication();
		$method = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_method','');
		$id = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_id','');
		if(empty($method) || $this->cart_update){
			$cart = $this->initCart();
			if($cart->has_shipping){
				$class = hikashop_get('class.shipping');
				$methods =& $class->getShippings($cart);
				if(!empty($methods)){
					$reset_shipping=true;
					if($this->cart_update){
						$found = false;
						foreach($methods as $m){
							if($m->shipping_id==$id&&$m->shipping_type==$method){
								$found=true;
							}
						}
						$reset_shipping = !$found;
					}
					if($reset_shipping){
						$config =& hikashop_config();
						$auto_select_default = $config->get('auto_select_default',2);
						if($auto_select_default==1 && count($methods)>1) $auto_select_default=0;
						$ok = false;
						if($auto_select_default){
							$method = reset($methods);
							$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',$method->shipping_type);
							$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',$method->shipping_id);
							$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',$method);
							$this->initCart(true);
						}else{
							$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method','');
							$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id','');
							$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data','');
							if(($method=='' && $id=='') || $directCall || isset($this->beforeControllers) && count($this->beforeControllers)==1){
								$ok = true;
							}
						}
					}
				}
			}
		}
		return $ok;
	}
	function after_shipping($success){
		if($this->cart_update){
			return true;
		}
		$shipping = JRequest::getString('hikashop_shipping','');
		$cart = $this->initCart();
		if(!$cart->has_shipping){
			return true;
		}
		if(!empty($shipping)){
			$shipping = explode('_',$shipping);
			if(count($shipping)>1){
				$shipping_id = array_pop($shipping);
				$shipping = implode('_',$shipping);
				if(!empty($shipping)){
					$shippingClass = hikashop_get('class.shipping');
					$methods = $shippingClass->getMethods($cart);
					$data = hikashop_import('hikashopshipping',$shipping);
					$shippingData = $data->onShippingSave($cart,$methods,$shipping_id);
					$app =& JFactory::getApplication();
					if($shippingData===false){
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method','');
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id','');
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data','');
						return false;
					}
					$old_shipping_method = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_method');
					$old_shipping_id = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_id');
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',$shipping);
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',$shipping_id);
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',$shippingData);
					if(($old_shipping_id!=$shipping_id || $old_shipping_method!=$shipping) && strpos($this->checkout_workflow,'payment')!==false){
						$this->initCart(true);
						$this->before_payment(true);
					}
					if(($old_shipping_id!=$shipping_id || $old_shipping_method!=$shipping) && $this->_getStep('cart',(int)$this->previous)===(int)$this->previous){
						return false;
					}
					return true;
				}
			}
		}
		return false;
	}
	function check_shipping(){
		$app =& JFactory::getApplication();
		$shipping_done=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_method');
		if(empty($shipping_done)){
			$shipping_done=false;
		}else{
			$shipping_done=true;
		}
		if(!$shipping_done){
			$cart = $this->initCart();
			if(!$cart->has_shipping){
				return true;
			}
			$app->enqueueMessage( JText::_('SELECT_SHIPPING') );
		}
		return $shipping_done;
	}
	function initCart($reset=false){
		static $done = false;
		if($reset){
			$done=false;
			return true;
		}
		if(!$done){
			$cartClass = hikashop_get('class.cart');
			$done = $cartClass->loadFullCart(true);
			if(empty($done->products)){
				$app =& JFactory::getApplication();
				$app->redirect( $this->redirect_url, JText::_('CART_EMPTY'));
			}
			$shippingClass = hikashop_get('class.shipping');
			$usable_rates =& $shippingClass->getShippings($done);
			if(empty($usable_rates) && empty($shippingClass->errors)){
				$shipping = false;
			}else{
				$shipping = true;
			}
			$config =& hikashop_config();
			$done->has_shipping = $shipping || $config->get('force_shipping');
		}
		return $done;
	}
	function before_payment($directCall=false){
		$ok = true;
		if(!$directCall){
			$ok = $this->_checkLogin();
			if(!$ok){
				return $ok;
			}
		}
		$app =& JFactory::getApplication();
		$payment_method = $app->getUserState( HIKASHOP_COMPONENT.'.payment_method','');
		$payment_id = $app->getUserState( HIKASHOP_COMPONENT.'.payment_id','');
		$cart = $this->initCart();
		if(empty($cart->full_total->prices[0]->price_value_with_tax) || bccomp($cart->full_total->prices[0]->price_value_with_tax,0,5)==0){
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_method','');
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',0);
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_data','');
			return true;
		}
		if(empty($payment_method) || $this->cart_update){
				$class = hikashop_get('class.payment');
				$methods = $class->getPayments($cart);
				if(!empty($methods)){
					$reset_payment=true;
					if($this->cart_update){
						$found = false;
						foreach($methods as $m){
							if($m->payment_id==$payment_id&&$m->payment_type==$payment_method){
								$found=true;
							}
						}
						$reset_payment = !$found;
					}
					if($reset_payment){
						$config =& hikashop_config();
						$auto_select_default = $config->get('auto_select_default',2);
						if($auto_select_default==1 && count($methods)>1) $auto_select_default=0;
						$ok = false;
						if($auto_select_default){
							$method = reset($methods);
							$app->setUserState( HIKASHOP_COMPONENT.'.payment_method',$method->payment_type);
							$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',$method->payment_id);
							$app->setUserState( HIKASHOP_COMPONENT.'.payment_data',$method);
						}else{
							$app->setUserState( HIKASHOP_COMPONENT.'.payment_method','');
							$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',0);
							$app->setUserState( HIKASHOP_COMPONENT.'.payment_data','');
							if(($payment_method=='' && $payment_id=='') || $directCall || !empty($this->beforeControllers) && count($this->beforeControllers)==1){
								$ok = true;
							}
						}
					}
				}
		}
		return $ok;
	}
	function after_payment($success){
		if($this->cart_update){
			return true;
		}
		$cart = $this->initCart();
		$app =& JFactory::getApplication();
		if(empty($cart->full_total->prices[0]->price_value_with_tax) || bccomp($cart->full_total->prices[0]->price_value_with_tax,0,5)==0){
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_method','');
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',0);
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_data','');
			return true;
		}
		$payment = JRequest::getString('hikashop_payment','');
		if(empty($payment)){
			return false;
		}
		$payment = explode('_',$payment);
		if(count($payment)>1){
			$payment_id = array_pop($payment);
			$payment = implode('_',$payment);
			if(empty($payment)){
				return false;
			}
			$cart = $this->initCart();
			$pluginsClass = hikashop_get('class.plugins');
			$rates = $pluginsClass->getMethods('payment');
			$data = hikashop_import('hikashoppayment',$payment);
			$paymentData = $data->onPaymentSave($cart,$rates,$payment_id);
			if($paymentData===false){
				return false;
			}
			$old_payment_method = $app->getUserState(HIKASHOP_COMPONENT.'.payment_method');
			$old_payment_id = $app->getUserState(HIKASHOP_COMPONENT.'.payment_id');
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_method',$payment);
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',$payment_id);
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_data',$paymentData);
			if(!empty($paymentData->ask_cc)){
				$paymentClass = hikashop_get('class.payment');
				$paymentClass->readCC();
			}
			if(($old_payment_id!=$payment_id || $old_payment_method!=$payment) && $this->_getStep('confirm',(int)$this->previous)===(int)$this->previous){
				return false;
			}
			return true;
		}else{
			return false;
		}
	}
	function check_payment(){
		$cart = $this->initCart();
		$app =& JFactory::getApplication();
		if(empty($cart->full_total->prices[0]->price_value_with_tax) || bccomp($cart->full_total->prices[0]->price_value_with_tax,0,5)==0){
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_method','');
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',0);
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_data','');
			return true;
		}
		$payment=$app->getUserState( HIKASHOP_COMPONENT.'.payment_method');
		if(empty($payment)){
			$payment_done=false;
		}else{
			$payment_done=true;
		}
		if(!$payment_done){
			$app =& JFactory::getApplication();
			$app->enqueueMessage( JText::_('SELECT_PAYMENT') );
		}else{
			$paymentData = $app->getUserState( HIKASHOP_COMPONENT.'.payment_data');
			if(!empty($paymentData->ask_cc)){
				$cc_number=$app->getUserState( HIKASHOP_COMPONENT.'.cc_number');
				$cc_month=$app->getUserState( HIKASHOP_COMPONENT.'.cc_month');
				$cc_year=$app->getUserState( HIKASHOP_COMPONENT.'.cc_year');
				$cc_CCV=$app->getUserState( HIKASHOP_COMPONENT.'.cc_CCV');
				$cc_owner=$app->getUserState( HIKASHOP_COMPONENT.'.cc_owner');
				if(empty($cc_number) || empty($cc_month) || empty($cc_year) || (empty($cc_CCV)&&!empty($paymentData->ask_ccv)) || (empty($cc_owner)&&!empty($paymentData->ask_owner))){
					$app =& JFactory::getApplication();
					$app->enqueueMessage( JText::_('FILL_CREDIT_CARD_INFO') );
					$payment_done=false;
				}
			}
		}
		return $payment_done;
	}
	function _checkToken(){
		static $done = false;
		if(!$done){
			$done = true;
			JRequest::checkToken('request') or jexit( 'Invalid Token' );
		}
	}
	function notify(){
		ob_start();
		$payment = JRequest::getCmd('notif_payment');
		$data = hikashop_import('hikashoppayment',$payment);
		if(!empty($data)){
			$trans = hikashop_get('helper.translation');
			$cleaned_statuses = $trans->getStatusTrans();
			$data = $data->onPaymentNotification($cleaned_statuses);
		}
		$dbg=ob_get_clean();
		if(!empty($dbg)){
			$config =& hikashop_config();
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
		if(is_string($data) && !empty($data)){
			echo $data;
		}
	}
	function threedsecure(){
		ob_start();
		$payment = JRequest::getCmd('3dsecure_payment');
		$data = hikashop_import('hikashoppayment',$payment);
		if(!empty($data)){
			$trans = hikashop_get('helper.translation');
			$cleaned_statuses = $trans->getStatusTrans();
			$data = $data->onThreeDSecure($cleaned_statuses);
		}
		$dbg=ob_get_clean();
		if(!empty($dbg)){
			$config =& hikashop_config();
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
		if(is_string($data) && !empty($data)){
			echo $data;
		}
	}
	function before_confirm(){
		foreach($this->steps as $i => $step){
			if(intval($i)!=intval($this->current)){
				$this->_checkStep(trim($step),$i);
			}
		}
		return true;
	}
	function _checkStep($step,$i){
		$controllers = explode('_',$step);
		$ok = true;
		foreach($controllers as $controller){
			$fct = 'check_'.trim($controller);
			if(method_exists($this,$fct)){
				if(!$this->$fct()){
					$ok = false;
				}
			}
		}
		if(!$ok){
			$this->setRedirect( hikashop_completeLink('checkout&task=step&step='.$i,false,true));
			$this->redirect();
		}
	}
	function after_confirm($success){
		if(!$success){
			return false;
		}
		if(!JRequest::getVar('hikashop_validate',1)){
			return false;
		}
		if($this->current==$this->previous){
			return true;
		}
		foreach($this->steps as $i => $step){
			$this->_checkStep(trim($step),$i);
		}
		$app =& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$config =& hikashop_config();
		$pluginsClass = hikashop_get('class.plugins');
		$cart = $this->initCart();
		$shipping=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_method');
		$shipping_id=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_id');
		$payment=$app->getUserState( HIKASHOP_COMPONENT.'.payment_method');
		$payment_id=$app->getUserState( HIKASHOP_COMPONENT.'.payment_id');
		$ids = array();
		foreach($cart->products as $product){
			if($product->cart_product_quantity>0 && $product->product_type=='variant'){
				$ids[$product->product_id]=$product->product_id;
			}
		}
		if(!empty($ids)){
			$database =& JFactory::getDBO();
			$query = 'SELECT a.variant_product_id as product_id,b.characteristic_id as value_id,b.characteristic_value as value,c.characteristic_id as name_id,c.characteristic_value as name FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id LEFT JOIN '.hikashop_table('characteristic').' AS c ON b.characteristic_parent_id = c.characteristic_id WHERE a.variant_product_id IN ('.implode(',',$ids).')';
			$database->setQuery($query);
			$characteristics = $database->loadObjectList();
			if(!empty($characteristics)){
				foreach($characteristics as $characteristic){
					foreach($cart->products as $k => $product){
						if($product->product_id == $characteristic->product_id){
							if(empty($product->characteristics)){
								$product->characteristics = array($characteristic->name => $characteristic->value);
							}else{
								$product->characteristics[$characteristic->name] = $characteristic->value;
							}
						}
					}
				}
			}
		}
		if(hikashop_level(2)){
			$element=null;
			$fieldsClass = hikashop_get('class.field');
			$itemFields = $fieldsClass->getFields('frontcomp',$element,'item');
		}
		$products = array();
		foreach($cart->products as $product){
			if($product->cart_product_quantity>0){
				$orderProduct = null;
				$orderProduct->product_id = $product->product_id;
				$orderProduct->order_product_quantity = $product->cart_product_quantity;
				$orderProduct->order_product_name = $product->product_name;
				$orderProduct->cart_product_id = $product->cart_product_id;
				$orderProduct->cart_product_option_parent_id = $product->cart_product_option_parent_id;
				$orderProduct->order_product_code = $product->product_code;
				$orderProduct->order_product_price = @$product->prices[0]->unit_price->price_value;
				$tax = 0;
				if(!empty($product->prices[0]->unit_price->price_value_with_tax) && bccomp($product->prices[0]->unit_price->price_value_with_tax,0,5)){
					$tax = $product->prices[0]->unit_price->price_value_with_tax-$product->prices[0]->unit_price->price_value;
				}
				$orderProduct->order_product_tax = $tax;
				$characteristics = '';
				if(!empty($product->characteristics)){
					$characteristics = serialize($product->characteristics);
				}
				$orderProduct->order_product_options = $characteristics;
				if(!empty($product->discount)){
					$orderProduct->discount = $product->discount;
				}
				if(!empty($itemFields)){
					foreach($itemFields as $field){
						$namekey = $field->field_namekey;
						if(isset($product->$namekey)){
							$orderProduct->$namekey = $product->$namekey;
						}
					}
				}
				$products[]=$orderProduct;
			}
		}
		$cart->products = &$products;
		$shipping_address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
		$billing_address=$app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		$main_currency = (int)$config->get('main_currency',1);
		$currency_id = (int)$app->getUserState( HIKASHOP_COMPONENT.'.currency_id', $main_currency);
		$order = null;
		$order->order_user_id = @hikashop_loadUser();
		$order->order_status = $config->get('order_created_status');
		$order->order_shipping_address_id = $shipping_address;
		$order->order_billing_address_id = $billing_address;
		$order->order_discount_code = @$cart->coupon->discount_code;
		$order->order_currency_id = $cart->full_total->prices[0]->price_currency_id;
		$order->order_type='sale';
		$order->order_full_price = $cart->full_total->prices[0]->price_value_with_tax;
		$order->order_shipping_price = @$cart->shipping->shipping_price_with_tax;
		if(!empty($cart->shipping->shipping_price_with_tax)&&!empty($cart->shipping->shipping_price)){
			$order->order_shipping_tax = $cart->shipping->shipping_price_with_tax-$cart->shipping->shipping_price;
		}else{
			$order->order_shipping_tax = 0;
		}
		$discount_price = 0;
		$discount_tax=0;
		if(!empty($cart->coupon)&& !empty($cart->coupon->total->prices[0]->price_value_without_discount_with_tax)){
			$discount_price=@$cart->coupon->total->prices[0]->price_value_without_discount_with_tax-@$cart->coupon->total->prices[0]->price_value_with_tax;
			if(!empty($cart->coupon->total->prices[0]->price_value_with_tax)&&!empty($cart->coupon->total->prices[0]->price_value)){
				$discount_tax = $cart->coupon->total->prices[0]->price_value_with_tax-$cart->coupon->total->prices[0]->price_value;
			}
		}
		$order->order_discount_tax = $discount_tax;
		$order->order_discount_price = $discount_price;
		$order->order_shipping_id = $shipping_id;
		$order->order_shipping_method = $shipping;
		$order->order_payment_id = $payment_id;
		$order->order_payment_method = $payment;
		$order->cart =& $cart;
		$order->history->history_reason=JText::_('ORDER_CREATED');
		$order->history->history_notified=0;
		$order->history->history_type = 'creation';
		$app =& JFactory::getApplication();
		if(hikashop_level(2)){
			$orderData = $app->getUserState( HIKASHOP_COMPONENT.'.checkout_fields');
			if(!empty($orderData)){
				foreach(get_object_vars($orderData) as $key => $val){
					$order->$key = $val;
				}
			}
			$app->setUserState( HIKASHOP_COMPONENT.'.checkout_fields_ok',0);
		}
		$orderClass = hikashop_get('class.order');
		$order->order_id = $orderClass->save($order);
		$removeCart = false;
		if(!empty($order->order_id)){
			$app =& JFactory::getApplication();
			$entriesData = $app->getUserState( HIKASHOP_COMPONENT.'.entries_fields');
			if(!empty($entriesData)){
				$entryClass = hikashop_get('class.entry');
				foreach($entriesData as $entryData){
					$entryData->order_id = $order->order_id;
					$entryClass->save($entryData);
				}
				$app->setUserState( HIKASHOP_COMPONENT.'.entries_fields',null);
			}
			if(!empty($payment)){
				$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($payment);
				$db->setQuery($query);
				$paymentData = $db->loadObjectList('payment_id');
				$pluginsClass->params($paymentData,'payment');
			}else{
				$paymentData = null;
			}
			if(!empty($shipping)){
				$query = 'SELECT * FROM '.hikashop_table('shipping').' WHERE shipping_type='.$db->Quote($shipping);
				$db->setQuery($query);
				$shippingData = $db->loadObjectList('shipping_id');
				$pluginsClass->params($shippingData,'shipping');
			}else{
				$shippingData = null;
			}
			ob_start();
			if(!empty($shippingData)){
				$data = hikashop_import('hikashopshipping',$shipping);
				$data->onAfterOrderConfirm($order,$shippingData,$shipping_id);
				if(!empty($data->removeCart)){
					$removeCart = true;
				}
			}
			if(!empty($paymentData)){
				$data = hikashop_import('hikashoppayment',$payment);
				$data->onAfterOrderConfirm($order,$paymentData,$payment_id);
				if(!empty($data->removeCart)){
					$removeCart = true;
				}
			}
			JRequest::setVar('hikashop_plugins_html',ob_get_clean());
		}else{
			return false;
		}
		$app->setUserState( HIKASHOP_COMPONENT.'.order_id',$order->order_id);
		if($config->get('clean_cart','order_created')=='order_created' || $removeCart){
			$cart_id = $app->getUserState( HIKASHOP_COMPONENT.'.cart_id');
			if($cart_id){
				$class = hikashop_get('class.cart');
				$class->delete($cart_id);
				$app->setUserState( HIKASHOP_COMPONENT.'.cart_id',0);
			}
			$app->setUserState( HIKASHOP_COMPONENT.'.coupon_code','' );
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_number','');
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_month','');
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_year','');
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV','');
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_owner','');
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_valid',0);
			$app->setUserState( HIKASHOP_COMPONENT.'.checkout_terms', 0 );
			$user =& JFactory::getUser();
			if($user->guest){
				$app->setUserState( HIKASHOP_COMPONENT.'.user_id',0 );
			}
		}
		return true;
	}
	function before_status(){
		return true;
	}
	function after_status(){
		return true;
	}
	function check_status(){
		return true;
	}
	function before_end(){
		$app =& JFactory::getApplication();
		$order = $app->getUserState( HIKASHOP_COMPONENT.'.order_id',0);
		if(empty($order)){
			return false;
		}
		return true;
	}
	function after_end(){
		if(!isset($this->current)){
			$config =& hikashop_config();
			$app =& JFactory::getApplication();
			$cart_id = $app->getUserState( HIKASHOP_COMPONENT.'.cart_id');
			if($cart_id){
				$class = hikashop_get('class.cart');
				$class->delete($cart_id);
			}
			$user =& JFactory::getUser();
			if($user->guest){
				$app->setUserState( HIKASHOP_COMPONENT.'.user_id',0 );
			}
			$app->setUserState( HIKASHOP_COMPONENT.'.cart_id',0);
			$app->setUserState( HIKASHOP_COMPONENT.'.coupon_code','' );
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_number','');
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_month','');
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_year','');
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV','');
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_owner','');
			$app->setUserState( HIKASHOP_COMPONENT.'.cc_valid',0);
			$app->setUserState( HIKASHOP_COMPONENT.'.checkout_terms', 0 );
			$app->setUserState(HIKASHOP_COMPONENT.'.display_ga',1);
			$order_id = $app->getUserState( HIKASHOP_COMPONENT.'.order_id');
			if($order_id){
				$class = hikashop_get('class.order');
				$order = $class->get($order_id);
				$db =& JFactory::getDBO();
				$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($order->order_payment_method).' AND payment_id='.$db->Quote($order->order_payment_id);
				$db->setQuery($query);
				$paymentData = $db->loadObjectList();
				$pluginsClass = hikashop_get('class.plugins');
				$pluginsClass->params($paymentData,'payment');
				$paymentOptions=reset($paymentData);
				if(!empty($paymentOptions->payment_params->return_url)){
					$app->redirect($paymentOptions->payment_params->return_url);
				}
			}
			JRequest::setVar( 'layout', 'after_end' );
			return $this->display();
		}
		return true;
	}
	function _checkLogin(){
		if(count($this->controllers)==1){
			$user =& JFactory::getUser();
			$app =& JFactory::getApplication();
			$user_id=$app->getUserState( HIKASHOP_COMPONENT.'.user_id' );
			if($user->guest && empty($user_id)){
				$found = $this->_getStep('login');
				if($found!==false){
					JRequest::setVar('step',$found);
					JRequest::setVar('previous',0);
					$this->step();
					return false;
				}else{
					$userData = null;
					$userData->user_created_ip = hikashop_getIP();
					$class=hikashop_get('class.user');
					$userData->user_id = $class->save($userData);
					$app->setUserState( HIKASHOP_COMPONENT.'.user_id',$userData->user_id );
				}
			}
		}
		return true;
	}
	function _getStep($search,$onStep=null){
		$found = false;
		foreach($this->steps as $k => $step){
			if(isset($onStep) && $onStep!=$k) continue;
			if(strpos($step,$search)!==false){
				$found = $k;
				break;
			}
		}
		return $found;
	}
	function display(){
		static $done = false;
		$result = true;
		if(!$done){
			$done = true;
			$result = parent::display();
		}
		return $result;
	}
}