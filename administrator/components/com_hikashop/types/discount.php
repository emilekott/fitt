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
class HikashopDiscountType{
	function load($form){
		$this->values = array();
		if(!$form){
			$this->values[] = JHTML::_('select.option', 'all',JText::_('HIKA_ALL') );
		}
		$this->values[] = JHTML::_('select.option', 'discount',JText::_('DISCOUNTS'));
		$this->values[] = JHTML::_('select.option', 'coupon',JText::_('COUPONS'));
	}
	function display($map,$value,$form=false){
		$this->load($form);
		$attribute='';
		if(!$form){
			$attribute = ' onchange="document.adminForm.submit( );"';
		}else{
			if(empty($value)){
				$value = 'discount';
			}
			$js = '
			function hikashopToggleDiscount(value){
				autoLoad = document.getElementById(\'hikashop_auto_load\');
				tax = document.getElementById(\'hikashop_tax\');
				minOrder = document.getElementById(\'hikashop_min_order\');
				hikashop_quota_per_user = document.getElementById(\'hikashop_quota_per_user\');
				hikashop_min_products = document.getElementById(\'hikashop_min_products\');
				hikashop_discount_coupon_product_only = document.getElementById(\'hikashop_discount_coupon_product_only\');
				hikashop_discount_coupon_nodoubling = document.getElementById(\'hikashop_discount_coupon_nodoubling\');
				if(value==\'discount\'){
					if(autoLoad) autoLoad.style.display = \'none\';
					if(tax) tax.style.display = \'none\';
					if(minOrder) minOrder.style.display = \'none\';
					if(hikashop_quota_per_user) hikashop_quota_per_user.style.display = \'none\';
					if(hikashop_min_products) hikashop_min_products.style.display = \'none\';
					if(hikashop_discount_coupon_product_only) hikashop_discount_coupon_product_only.style.display = \'none\';
					if(hikashop_discount_coupon_nodoubling) hikashop_discount_coupon_nodoubling.style.display = \'none\';
				}else{
					if(autoLoad) autoLoad.style.display = \'\';
					if(tax) tax.style.display = \'\';
					if(minOrder) minOrder.style.display = \'\';
					if(hikashop_quota_per_user) hikashop_quota_per_user.style.display = \'\';
					if(hikashop_min_products) hikashop_min_products.style.display = \'\';
					if(hikashop_discount_coupon_product_only) hikashop_discount_coupon_product_only.style.display = \'\';
					if(hikashop_discount_coupon_nodoubling) hikashop_discount_coupon_nodoubling.style.display = \'\';
				}
			}
			window.addEvent(\'domready\', function(){
				hikashopToggleDiscount(\''.$value.'\');
			});';
			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration($js);
			$attribute = ' onchange="hikashopToggleDiscount(this.value);"';
		}
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"'.$attribute, 'value', 'text', $value );
	}
}