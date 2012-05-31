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
jimport('joomla.plugin.plugin');
class plgSystemHikashopuser extends JPlugin{
	function plgSystemHikashopuser(&$subject, $config){
		parent::__construct($subject, $config);
		$app =& JFactory::getApplication();
		$this->cart = $app->getUserState('com_hikashop.cart_id');
		$this->currency = $app->getUserState('com_hikashop.currency_id');
		$this->entries = $app->getUserState('com_hikashop.entries_fields');
    }
    function onUserBeforeSave($user, $isnew, $new){
    	return $this->onBeforeStoreUser($user, $isnew);
	}
	function onUserAfterSave($user, $isnew, $success, $msg){
		return $this->onAfterStoreUser($user, $isnew, $success, $msg);
	}
	function onUserAfterDelete($user, $success, $msg){
		return $this->onAfterDeleteUser($user, $success, $msg);
	}
	function onUserLogin($user, $options){
		return $this->onLoginUser($user, $options);
	}
	function onBeforeStoreUser($user, $isnew){
		$this->oldUser = $user;
		return true;
	}
	function onAfterStoreUser($user, $isnew, $success, $msg){
		if($success===false || !is_array($user)) return false;
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
		$userClass = hikashop_get('class.user');
		$hikaUser = null;
		$hikaUser->user_email = trim(strip_tags($user['email']));
		$hikaUser->user_cms_id = (int)$user['id'];
		if(!empty($hikaUser->user_cms_id)){
			$hikaUser->user_id = $userClass->getID($hikaUser->user_cms_id,'cms');
		}
		if(empty($hikaUser->user_id) && !empty($hikaUser->user_email)){
			$hikaUser->user_id = $userClass->getID($hikaUser->user_email,'email');
		}
		if(!empty($hikaUser->user_id)){
			$userClass->save($hikaUser,true);
		}
		return true;
	}
	function onAfterDeleteUser($user, $success, $msg){
		if($success===false || !is_array($user)) return false;
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
		$userClass = hikashop_get('class.user');
		$user_id = $userClass->getID($user['email'],'email');
		if(!empty($user_id)){
			$userClass->delete($user_id,true);
		}
		return true;
	}
	function onLoginUser($user, $options){
		$app =& JFactory::getApplication();
		$cart = $app->getUserState('com_hikashop.cart_id');
		if(empty($cart) && !empty($this->cart)){
			$app->setUserState('com_hikashop.cart_id',$this->cart);
		}
		$entries = $app->getUserState('com_hikashop.entries_fields');
		if(empty($entries) && !empty($this->entries)){
			$app->setUserState('com_hikashop.entries_fields',$this->entries);
		}
		$currency = $app->getUserState('com_hikashop.currency_id');
		if(empty($currency) && !empty($this->currency)){
			$app->setUserState('com_hikashop.currency_id',$this->currency);
		}
		if(empty($user['id'])){
			jimport('joomla.user.helper');
			$instance = new JUser();
			if($id = intval(JUserHelper::getUserId($user['username'])))  {
				$instance->load($id);
			}
			if ($instance->get('block') == 0) {
				$user_id=$instance->id;
			}
		}else{
			$user_id = $user['id'];
		}
		if(!empty($user_id)){
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
			$userClass = hikashop_get('class.user');
			$hika_user_id = $userClass->getID($user_id,'cms');
			if(!empty($hika_user_id)){
				$addressClass = hikashop_get('class.address');
				$addresses = $addressClass->getByUser($hika_user_id);
				if(!empty($addresses) && count($addresses)){
					$address = reset($addresses);
					$field = 'address_country';
					if(!empty($address->address_state)){
						$field = 'address_state';
					}
					$zoneClass = hikashop_get('class.zone');
					$zone = $zoneClass->get($address->$field);
					if(!empty($zone)){
						$zone_id = $zone->zone_id;
						$app->setUserState( HIKASHOP_COMPONENT.'.zone_id', $zone->zone_id );
					}
				}
			}
		}
	}
	function onAfterRender(){
		$user=null;
		return $this->onLogoutUser($user);
	}
	function onUserLogout($user){
		return $this->onLogoutUser($user);
	}
	function onLogoutUser($user){
		$options=null;
		return $this->onLoginUser($user, $options);
	}
}