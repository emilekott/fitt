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
class hikashopConfigClass extends hikashopClass{
	var $toggle = array('config_value'=>'config_namekey');
	function load(){
		$query = 'SELECT * FROM '.hikashop_table('config');
		$this->database->setQuery($query);
		$this->values = $this->database->loadObjectList('config_namekey');
		if(!empty($this->values['default_params']->config_value)){
			$this->values['default_params']->config_value = unserialize(base64_decode($this->values['default_params']->config_value));
		}
	}
	function set($namekey,$value){
		$this->values[$namekey]->config_value=$value;
		$this->values[$namekey]->config_namekey=$namekey;
		return true;
	}
	function get($namekey,$default = ''){
		if(isset($this->values[$namekey])){
			if(preg_match('#^(menu_|params_)[0-9]+$#',$namekey) && !empty($this->values[$namekey]->config_value) && is_string($this->values[$namekey]->config_value)){
				$this->values[$namekey]->config_value = unserialize(base64_decode($this->values[$namekey]->config_value));
			}
			return $this->values[$namekey]->config_value;
		}
		return $default;
	}
	function save($configObject,$default=false){
		if(empty($this->values)){
			$this->load();
		}
		$query = 'REPLACE INTO '.hikashop_table('config').' (config_namekey,config_value'.($default?',config_default':'').') VALUES ';
		$params = array();
		if(is_object($configObject)){
			$configObject = get_object_vars($configObject);
		}
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		foreach($configObject as $namekey => $value){
			if($namekey=='default_params' || preg_match('#^(menu_|params_)[0-9]+$#',$namekey)){
				$value=base64_encode(serialize($value));
			}elseif($namekey=='main_currency'){
				if(!empty($this->values[$namekey]->config_value)){
					$currencyClass = hikashop_get('class.currency');
					$currencyClass->updateRatesWithNewMainCurrency($this->values[$namekey]->config_value,$value);
				}
			}
			$this->values[$namekey]->config_value = $value;
			if(!isset($this->values[$namekey]->config_default)){
				$this->values[$namekey]->config_default = $this->values[$namekey]->config_value;
			}
			$params[] = '('.$this->database->Quote(strip_tags($namekey)).','.$this->database->Quote($safeHtmlFilter->clean($value, 'string')).($default?','.$this->database->Quote($this->values[$namekey]->config_default):'').')';
		}
		$query .= implode(',',$params);
		$this->database->setQuery($query);
		return $this->database->query();
	}
	function reset(){
		$query = 'UPDATE '.hikashop_table('config').' SET config_value = config_default';
		$this->database->setQuery($query);
		$this->values = $this->database->query();
	}
}