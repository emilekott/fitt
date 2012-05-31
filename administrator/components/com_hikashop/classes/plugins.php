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
class hikashopPluginsClass extends hikashopClass{
	function  __construct( $config = array() ){
		if(version_compare(JVERSION,'1.6','<')){
			$this->toggle = array('published'=>'id');
			$this->pkeys = array('id');
		}else{
			$this->toggle = array('enabled'=>'extension_id');
			$this->pkeys = array('extension_id');
		}
		return parent::__construct($config);
	}
	function getTable(){
		if(version_compare(JVERSION,'1.6','<')){
			return hikashop_table('plugins',false);
		}else{
			return hikashop_table('extensions',false);
		}
	}
	function getMethods($type='shipping',$name='',$shipping='',$currency=''){
		$where = array();
		$lf='';
		$select='*';
		if(!empty($name)){
			$where[] = $type.'_type='.$this->database->Quote($name);
		}
		if(!empty($shipping)){
			$where[] = '(payment_shipping_methods IN (\'\',\'_\') OR payment_shipping_methods LIKE \'%'.$shipping.'%\')';
		}
		if(!empty($currency)){
			$where[] = "(payment_currency IN ('','_','all') OR payment_currency LIKE '%,".intval($currency).",%')";
		}
		$app =& JFactory::getApplication();
		if(!$app->isAdmin()){
			$access = $type.'_access';
			hikashop_addACLFilters($where,$access);
		}
		if(!empty($where)){
			$where = ' WHERE '.implode(' AND ',$where);
		}else{
			$where = '';
		}
		if($type=='shipping'){
			$where .= ' ORDER BY shipping_ordering ASC';
		}
		$query = 'SELECT '.$select.' FROM '.hikashop_table($type).' '.$lf.$where;
		$this->database->setQuery($query);
		$methods = $this->database->loadObjectList($type.'_id');
		$this->params($methods,$type);
		if(empty($methods)){
			$methods = array();
		}else{
			if($type=='payment'){
				$types = array();
				foreach($methods as $method){
					$types[$method->payment_type]=$this->database->Quote($method->payment_type);
				}
				$types = implode(',',$types);
				if(version_compare(JVERSION,'1.6','<')){
					$query='SELECT *,published as enabled FROM '.hikashop_table('plugins',false).' WHERE element IN ('.$types.') AND folder=\'hikashoppayment\' ORDER BY ordering ASC';
				}else{
					$query='SELECT * FROM '.hikashop_table('extensions',false).' WHERE element IN ('.$types.') AND folder=\'hikashoppayment\' AND type=\'plugin\' ORDER BY ordering ASC';
				}
				$this->database->setQuery($query);
				$plugins = $this->database->loadObjectList();
				foreach($methods as $k => $method){
					foreach($plugins as $plugin){
						if($plugin->element == $method->payment_type){
							foreach(get_object_vars($plugin) as $key => $val){
								$methods[$k]->$key = $val;
							}
							break;
						}
					}
				}
			}
		}
    	return $methods;
	}
	function params(&$methods,$type){
		if(!empty($methods)){
			$params = $type.'_params';
	    	foreach($methods as $k => $el){
	    		if(!empty($el->$params)){
	    			$methods[$k]->$params = @unserialize($el->$params);
	    		}
	    	}
    	}
	}
	function get($id){
		$result = parent::get($id);
		$this->_loadParams($result);
		return $result;
	}
	function getByName($type,$name){
		if(version_compare(JVERSION,'1.6','<')){
			$query = 'SELECT * FROM '.hikashop_table('plugins',false).' WHERE folder='.$this->database->Quote($type).' AND element='.$this->database->Quote($name);
		}else{
			$query = 'SELECT * FROM '.hikashop_table('extensions',false).' WHERE folder='.$this->database->Quote($type).' AND element='.$this->database->Quote($name).' AND type=\'plugin\'';
		}
		$this->database->setQuery($query);
		$result = $this->database->loadObject();
		$this->_loadParams($result);
		return $result;
	}
	function _loadParams(&$result){
		if(!empty($result->params)){
			if(version_compare(JVERSION,'1.6','<')){
				$lines = explode("\n",$result->params);
				$result->params = array();
				foreach($lines as $line){
					$param = explode('=',$line,2);
					if(count($param)==2){
						$result->params[$param[0]]=$param[1];
					}
				}
			}else{
				$registry = new JRegistry;
				$registry->loadJSON($result->params);
				$result->params = $registry->toArray();
			}
		}
	}
	function save(&$element){
		if(!empty($element->params)){
			if(version_compare(JVERSION,'1.6','<')){
				$params = '';
				foreach($element->params as $key => $val){
					$params.=$key.'='.$val."\n";
				}
				$element->params = rtrim($params);
			}else{
				$handler = JRegistryFormat::getInstance('JSON');
				$element->params = $handler->objectToString($element->params);
			}
		}
		return parent::save($element);
	}
}