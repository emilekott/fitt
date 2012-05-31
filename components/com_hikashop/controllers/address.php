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
class addressController extends hikashopController{
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		$this->modify_views = array('edit');
		$this->add = array('add');
		$this->modify = array('save');
		$this->delete = array('delete');
	}
	function delete(){
		$addressdelete = JRequest::getInt('address_id',0);
		if($addressdelete){
			JRequest::checkToken('request') or jexit( 'Invalid Token' );
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($addressdelete);
			if(!empty($oldData)){
				$user_id = hikashop_loadUser();
				if($user_id==$oldData->address_user_id){
					$addressClass->delete($addressdelete);
				}
			}
		}
		$this->listing();
	}
	function save(){
		JRequest::checkToken('request') or jexit( 'Invalid Token' );
		$addressClass = hikashop_get('class.address');
		$app =& JFactory::getApplication();
		$oldData = null;
		$already = @$_REQUEST['address']['address_id'];
		if(!empty($already)){
			$oldData = $class->get($already);
		}
		$fieldClass = hikashop_get('class.field');
		$addressData = $fieldClass->getInput('address',$oldData);
		$ok = true;
		if(empty($addressData)){
			$ok=false;
		}else{
			$user_id = hikashop_loadUser();
			$addressData->address_user_id=$user_id;
			$address_id = $addressClass->save($addressData);
		}
		if(!$ok || !$address_id){
			if(version_compare(JVERSION,'1.6','<')){
				$app =& JFactory::getApplication();
				$session =& JFactory::getSession();
				$session->set('application.queue', $app->_messageQueue);
			}
			echo '<html><head><script type="text/javascript">javascript: history.go(-1);</script></head><body></body></html>';
			exit;
		}
		$redirect = JRequest::getWord('redirect','');
		global $Itemid;
		$url = '';
		if(!empty($Itemid)){
			$url='&Itemid='.$Itemid;
		}
		if($redirect=='checkout'){
			$makenew = JRequest::getInt('makenew');
			switch(JRequest::getVar('type')){
				case 'shipping':
					if(JRequest::getVar('action')== 'add' && $makenew){
						$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',$address_id );
					}
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address', $address_id );
					break;
				case 'billing':
					if(JRequest::getVar('action')== 'add' && $makenew){
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',$address_id );
					}
					$app->setUserState( HIKASHOP_COMPONENT.'.billing_address', $address_id );
					break;
				default:
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',$address_id );
					$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',$address_id );
					break;
			}
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method','' );
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',0 );
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_method','' );
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',0 );
			if(!$already){
				$controller = hikashop_get('controller.checkout');
				$cart = $controller->initCart();
				$controller->update_cart = true;
				if($cart->has_shipping){
					$controller->before_shipping(true);
				}
				$controller->before_payment(true);
			}
			$url = hikashop_completeLink('checkout&task=step&step='.JRequest::getInt('step',0).$url,false,true);
		}else{
			$url = hikashop_completeLink('address'.$url,false,true);
		}
		echo '<html><head><script type="text/javascript">parent.window.location.href=\''.$url.'\';</script></head><body></body></html>';
		exit;
	}
}