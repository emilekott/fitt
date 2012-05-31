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
class addressViewAddress extends JView{
	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$user_id = hikashop_loadUser();
		$addresses = array();
		$fields = null;
		if($user_id){
			$addressClass = hikashop_get('class.address');
			$addresses = $addressClass->getByUser($user_id);
			if(!empty($addresses)){
				$addressClass->loadZone($addresses);
				$fields =& $addressClass->fields;
			}
		}
		$this->assignRef('user_id',$user_id);
		$this->assignRef('fields',$fields);
		$this->assignRef('addresses',$addresses);
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		JHTML::_('behavior.modal');
	}
	function form(){
		$user_id = hikashop_loadUser();
		$this->assignRef('user_id',$user_id);
		$address_id = hikashop_getCID('address_id');
		$address = null;
		if(!empty($address_id)){
			$class=hikashop_get('class.address');
			$address = $class->get($address_id);
			if($address->address_user_id!=$user_id){
				$address = null;
				$address_id = 0;
			}
		}else{
			$userCMS =& JFactory::getUser();
			if(!$userCMS->guest){
				$name = $userCMS->get('name');
				$pos = strpos($name,' ');
				if($pos!==false){
					$address->address_firstname = substr($name,0,$pos);
					$name = substr($name,$pos+1);
				}
				$address->address_lastname = $name;
			}
		}
		$extraFields=array();
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->skipAddressName=true;
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$extraFields['address'] = $fieldsClass->getFields('frontcomp',$address,'address','checkout&task=state'.$url_itemid);
		$this->assignRef('extraFields',$extraFields);
		$this->assignRef('address',$address);
		$module = hikashop_get('helper.module');
		$module->initialize($this);
		$requiredFields = array();
		$validMessages = array();
		$values = array('address'=>$address);
		$fieldsClass->checkFieldsForJS($extraFields,$requiredFields,$validMessages,$values);
		$fieldsClass->addJS($requiredFields,$validMessages,array('address'));
		$cart=hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		JHTML::_('behavior.mootools');
	}
}