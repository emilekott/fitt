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
class hikashopPaymentType{
	function load($form){
		$this->values = array();
		$pluginsClass = hikashop_get('class.plugins');
		$methods = $pluginsClass->getMethods('payment');
		if(!$form){
			$this->values[] = JHTML::_('select.option', '', JText::_('ALL_PAYMENT_METHODS') );
		}
		if(!empty($methods)){
			foreach($methods as $method){
				$this->values[] = JHTML::_('select.option', $method->payment_type, $method->payment_name );
			}
		}
	}
	function display($map,$value,$form=true){
		$this->load($form);
		if(!$form){
			$attribute = ' onchange="document.adminForm.submit();"';
		}else{
			$attribute = '';
		}
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1"'.$attribute, 'value', 'text', $value );
	}
}