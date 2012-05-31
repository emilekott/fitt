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
class hikashopCurrencyType{
	var $displayType = 'auto';
	var $currencies = array();
	function load($value){
		$config =& hikashop_config();
		$forced=array();
		$this->values = array();
		$forced[] = (int)$config->get('main_currency');
		$filters = array('currency_published=1');
		if($this->displayType=='auto'){
			$app = JFactory::getApplication();
			if($app->isAdmin()){
				if(is_array($value)){
					$forced = array_merge($forced,$value);
				}else{
					$forced[]=(int)$value;
				}
			}else{
				$filters[]='currency_displayed = 1';
			}
			$filters[]='currency_id IN (\''.implode('\',\'',$forced).'\')';
		}elseif($this->displayType=='all'){
			$filters[]='currency_displayed = 1';
			$this->values[] = JHTML::_('select.option', 0,JText::_('HIKA_NONE'));
		}
		if(empty($this->currencies)){
			$query = 'SELECT * FROM '.hikashop_table('currency').' WHERE  '.implode(' OR ',$filters);
			$db =& JFactory::getDBO();
			$db->setQuery($query);
			$this->currencies = $db->loadObjectList('currency_id');
		}
		if(!empty($this->currencies)){
			foreach($this->currencies as $currency){
				$this->values[] = JHTML::_('select.option', (int)$currency->currency_id, $currency->currency_symbol.' '.$currency->currency_code );
			}
		}
	}
	function display($map,$value,$options='size="1"'){
		if(empty($this->values)){
			$this->load($value);
		}
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" '.$options, 'value', 'text', $value );
	}
}