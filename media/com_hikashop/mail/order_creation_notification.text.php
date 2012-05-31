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
<?php echo JText::sprintf('HI_CUSTOMER',@$data->customer->name);?>
<?php
$url = $data->order_number;
$config =& hikashop_config();
if($config->get('simplified_registration',0)!=2){
	$url .= ' ( '.$data->order_url.' )';
}
echo JText::sprintf('ORDER_CREATION_SUCCESS_ON_WEBSITE_AT_DATE',$url,HIKASHOP_LIVE, hikashop_getDate(time(),'%d %B %Y'), hikashop_getDate(time(),'%H:%M'));?>
--------------------------------------
 <?php echo JText::_('SUMMARY_OF_YOUR_ORDER');?>
--------------------------------------
<?php echo JText::_('CART_PRODUCT_NAME')."\t".JText::_('CART_PRODUCT_UNIT_PRICE')."\t".JText::_('CART_PRODUCT_QUANTITY')."\t".JText::_('HIKASHOP_TOTAL');?>
<?php
foreach($data->cart->products as $item){
	$price = $item->order_product_price*$item->order_product_quantity;
	echo strip_tags($item->order_product_name) . "\t" . $currencyHelper->format($item->order_product_price,$data->order_currency_id)."\t".$item->order_product_quantity."\t".$currencyHelper->format($price,$data->order_currency_id)."\n";
}
if(bccomp($data->order_discount_price,0,5)){
	echo JText::_('HIKASHOP_COUPON').' : '.$currencyHelper->format($data->order_discount_price*-1,$data->order_currency_id)."\n";
}
if(bccomp($data->order_shipping_price,0,5)){
	echo JText::_('HIKASHOP_SHIPPING_METHOD').' : '.$currencyHelper->format($data->order_shipping_price,$data->order_currency_id)."\n";
}
echo JText::sprintf('TOTAL_WITHOUT_VAT',$currencyHelper->format($data->cart->full_total->prices[0]->price_value,$data->order_currency_id))."\n";
echo JText::sprintf('TOTAL_WITH_VAT',$currencyHelper->format($data->cart->full_total->prices[0]->price_value_with_tax,$data->order_currency_id))."\n\n";
$params = null;
$js = '';
$fieldsClass = hikashop_get('class.field');
$app =& JFactory::getApplication();
if($app->isAdmin()){
	$view = 'order';
}else{
	$view = 'address';
}
$template = trim(hikashop_getLayout($view,'address_template',$params,$js));
if(!empty($data->cart->billing_address)){
	$billing = $template;
	foreach($data->order_addresses_fields as $field){
		$fieldname = $field->field_namekey;
		$address =& $data->order_addresses[$data->cart->billing_address->address_id];
		$billing=str_replace('{'.$fieldname.'}',$fieldsClass->show($field,$address->$fieldname),$billing);
	}
	echo JText::_('HIKASHOP_BILLING_ADDRESS')."\n";
	echo preg_replace('#{(?:(?!}).)*}#i','',$billing)."\n\n";
}
if(!empty($data->cart->has_shipping) && !empty($data->cart->shipping_address)){
	$shipping = $template;
	foreach($data->order_addresses_fields as $field){
		$fieldname = $field->field_namekey;
		$address =& $data->order_addresses[$data->cart->shipping_address->address_id];
		$shipping=str_replace('{'.$fieldname.'}',$fieldsClass->show($field,$address->$fieldname),$shipping);
	}
	echo JText::_('HIKASHOP_SHIPPING_ADDRESS')."\n";
	echo preg_replace('#{(?:(?!}).)*}#i','',$shipping)."\n\n";
}
$fields = $fieldsClass->getFields('frontcomp',$data,'order','');
foreach($fields as $fieldName => $oneExtraField) {
	echo $fieldsClass->trans($oneExtraField->field_realname).' : '.$fieldsClass->show($oneExtraField,$data->$fieldName)."\r\n";
}
if(!$app->isAdmin()){
	echo JText::_('ORDER_VALID_AFTER_PAYMENT')."\n\n";
	echo JText::sprintf('THANK_YOU_FOR_YOUR_ORDER',HIKASHOP_LIVE)."\n\n";
}
echo str_replace('<br/>',"\n",JText::sprintf('BEST_REGARDS_CUSTOMER',$mail->from_name));?>