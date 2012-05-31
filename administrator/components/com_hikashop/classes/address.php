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
class hikashopAddressClass extends hikashopClass{
	var $tables = array('address');
	var $pkeys = array('address_id');
	function getByUser($user_id){
		$query = 'SELECT a.* FROM '.hikashop_table('address').' AS a WHERE a.address_user_id='.(int)$user_id.' and a.address_published=1 ORDER BY a.address_id DESC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList('address_id');
	}
	function loadZone(&$addresses,$type='name',$display='frontcomp'){
		$fieldClass = hikashop_get('class.field');
		$fields = $fieldClass->getData($display,'address');
		$this->fields =& $fields;
		if(!empty($fields)){
			$namekeys = array();
			foreach($fields as $field){
				if($field->field_type=='zone'){
					$namekeys[$field->field_namekey] = $field->field_namekey;
				}
			}
			if(!empty($namekeys)){
				$zones=array();
				foreach($addresses as $address){
					foreach($namekeys as $namekey){
						if(!empty($address->$namekey)){
							$zones[$address->$namekey]=$address->$namekey;
						}
					}
				}
				if(!empty($zones)){
					if(in_array($type,array('name','object'))){
						$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey IN (\''.implode('\',\'',$zones).'\');';
						$this->database->setQuery($query);
						$zones = $this->database->loadObjectList('zone_namekey');
						if(!empty($zones)){
							foreach($addresses as $k => $address){
								foreach($namekeys as $namekey){
									if(!empty($address->$namekey) && !empty($zones[$address->$namekey])){
										if($type=='name'){
											$addresses[$k]->$namekey=$zones[$address->$namekey]->zone_name_english;
										}else{
											$addresses[$k]->$namekey=$zones[$address->$namekey];
										}
									}
								}
							}
						}
					}else{
						$this->_getParents($zones,$addresses,$namekeys);
					}
				}
			}
		}
	}
	function loadUserAddresses($user_id){
		static $addresses = null;
		if(is_null($addresses)){
			$query = 'SELECT a.* FROM '.hikashop_table('address').' AS a WHERE a.address_user_id='.(int)$user_id.' and a.address_published=1 ORDER BY a.address_id DESC';
			$this->database->setQuery($query);
			$addresses = $this->database->loadObjectList('address_id');
		}
		return $addresses;
	}
	function _getParents(&$zones,&$addresses,&$fields){
		$namekeys = array();
		foreach($zones as $zone){
			$namekeys[]=$this->database->Quote($zone);
		}
		$query = 'SELECT a.* FROM '.hikashop_table('zone_link').' AS a WHERE a.zone_child_namekey IN ('.implode(',',$namekeys).');';
		$this->database->setQuery($query);
		$parents = $this->database->loadObjectList();
		if(!empty($parents)){
			$childs = array();
			foreach($parents as $parent){
				foreach($addresses as $k => $address){
					foreach($fields as $field){
						if(!is_array($addresses[$k]->$field)){
							$addresses[$k]->$field = array($addresses[$k]->$field);
						}
						foreach($addresses[$k]->$field as $value){
							if($value == $parent->zone_child_namekey && !in_array($parent->zone_parent_namekey,$addresses[$k]->$field)){
								$values =& $addresses[$k]->$field;
								$values[]=$parent->zone_parent_namekey;
								$childs[$parent->zone_parent_namekey]=$parent->zone_parent_namekey;
							}
						}
					}
				}
			}
			if(!empty($childs)){
				$this->_getParents($childs,$addresses,$fields);
			}
		}
	}
	function save(&$addressData,$order_id=0,$type='shipping'){
		$new = true;
		if(!empty($addressData->address_id)){
			$new = false;
			$oldData = $this->get($addressData->address_id);
			if(!empty($addressData->address_vat) && $oldData->address_vat != $addressData->address_vat){
				if(!$this->_checkVat($addressData)){
					return false;
				}
			}
			$app =& JFactory::getApplication();
			if(!$app->isAdmin()){
				$user_id = hikashop_loadUser();
				if($user_id!=$oldData->address_user_id || !$oldData->address_published){
					unset($addressData->address_id);
					$new = true;
				}
			}
			$orderClass = hikashop_get('class.order');
			if(!empty($addressData->address_id) && ($oldData->address_published!=0||$order_id) && $orderClass->addressUsed($addressData->address_id,$order_id,$type)){
				unset($addressData->address_id);
				$new = true;
				$oldData->address_published=0;
				parent::save($oldData);
			}
		}elseif(!empty($addressData->address_vat)){
			if(!$this->_checkVat($addressData)){
				return false;
			}
		}
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher =& JDispatcher::getInstance();
		$do = true;
		if($new){
			$dispatcher->trigger( 'onBeforeAddressCreate', array( & $addressData, & $do) );
		}else{
			$dispatcher->trigger( 'onBeforeAddressUpdate', array( & $addressData, & $do) );
		}
		if(!$do){
			return false;
		}
		$status = parent::save($addressData);
		if(!$status){
			return false;
		}
		if($new){
			$dispatcher->trigger( 'onAfterAddressCreate', array( & $addressData ) );
		}else{
			$dispatcher->trigger( 'onAfterAddressUpdate', array( & $addressData ) );
		}
		return $status;
	}
	function delete($elements,$order=false){
		$elements = (int)$elements;
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher =& JDispatcher::getInstance();
		$do=true;
		$dispatcher->trigger( 'onBeforeAddressDelete', array( & $elements, & $do) );
		if(!$do){
			return false;
		}
		$orderClass = hikashop_get('class.order');
		$status = true;
		if($orderClass->addressUsed($elements)){
			if(!$order){
				$address=null;
				$address->address_id = $elements;
				$address->address_published=0;
				$status = parent::save($address);
			}
		}else{
			$data = $this->get($elements);
			if(!$order || !$data->address_published){
				$status = parent::delete($elements);
			}
		}
		if($status){
			$dispatcher->trigger( 'onAfterAddressDelete', array( & $elements ) );
		}
		return $status;
	}
	function _checkVat(&$vatData){
		$vat = hikashop_get('helper.vat');
		if(!$vat->isValid($vatData)){
			return false;
		}
		return true;
	}
}