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
	<div style="background-color: #ffffff; font-family: Verdana, Arial, Helvetica, sans-serif;font-size:12px; color: #000000; width: 100%;">
	<table style="margin: auto;font-family: Verdana, Arial, Helvetica, sans-serif;font-size:12px;" border="0" cellspacing="0" cellpadding="0">
		<tbody>
			<tr>
				<td height="10">
				</td>
			</tr>
			<tr>
				<td>
					<br/>
					<br/>
					<?php
					$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$data->order_id;
					echo JText::sprintf('ORDER_STATUS_CHANGED',$data->mail_status)."<br/><br/>".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$data->order_number,HIKASHOP_LIVE);
					$currency = hikashop_get('class.currency');
					$url = '<a href="'.$url.'" target="_blank">'.$url.'</a>';
					echo "<br/>".JText::sprintf('ACCESS_ORDER_WITH_LINK',$url);
					if($data->order_payment_method=='creditcard' && !empty($data->credit_card_info->cc_number)){
						echo "<br/>".JText::_('CUSTOMER_PAID_WITH_CREDIT_CARD');
						if(!empty($data->credit_card_info->cc_owner)){
							echo "<br/>".JText::_('CREDIT_CARD_OWNER').' : '.$data->credit_card_info->cc_owner;
						}
						echo "<br/>".JText::_('END_OF_CREDIT_CARD_NUMBER').' : '.substr($data->credit_card_info->cc_number,8);
						if(!empty($data->credit_card_info->cc_CCV)){
							echo "<br/>".JText::_('CARD_VALIDATION_CODE').' : '.$data->credit_card_info->cc_CCV;
						}
						echo "<br/>".JText::_('CREDITCARD_WARNING');
					}
					$fieldsClass = hikashop_get('class.field');
					$fields = $fieldsClass->getFields('frontcomp',$data,'order','');
					foreach($fields as $fieldName => $oneExtraField) {
						if(!empty($data->$fieldName)) echo "<br/>".$fieldsClass->trans($oneExtraField->field_realname).' : '.$fieldsClass->show($oneExtraField,$data->$fieldName);
					}
					$class = hikashop_get('class.order');
					$url = $data->order_number;
					$config =& hikashop_config();
					if($config->get('simplified_registration',0)!=2){
						$url .= ' ( '.$data->order_url.' )';
					}
   					$data->cart = $class->loadFullOrder($data->order_id,true,false);
					$data->cart->coupon = null;
					$price = null;
					$tax = $data->cart->order_subtotal - $data->cart->order_subtotal_no_vat + $data->order_discount_tax + $data->order_shipping_tax;
					$price->price_value = $data->order_full_price-$tax;
					$price->price_value_with_tax = $data->order_full_price;
					$data->cart->full_total = null;
					$data->cart->full_total->prices = array($price);
					$data->cart->coupon->discount_value =& $data->order_discount_price;
		?>
				</td>
			</tr>
			<tr>
				<td height="10">
				</td>
			</tr>
			<tr>
				<td>
					<table width="100%" style="font-family: Verdana, Arial, Helvetica, sans-serif;font-size:12px;">
						<tr>
							<td style="font-weight:bold;">
								<?php echo JText::_('CART_PRODUCT_NAME'); ?>
							</td>
							<td style="font-weight:bold;">
								<?php echo JText::_('CART_PRODUCT_UNIT_PRICE'); ?>
							</td>
							<td style="font-weight:bold;">
								<?php echo JText::_('CART_PRODUCT_QUANTITY'); ?>
							</td>
							<td style="font-weight:bold;text-align:right;">
								<?php echo JText::_('HIKASHOP_TOTAL'); ?>
							</td>
						</tr>
						<?php
							if(hikashop_level(2)){
								$fieldsClass = hikashop_get('class.field');
								$null = null;
								$itemFields = $fieldsClass->getFields('frontcomp',$null,'item');
							}
							foreach($data->cart->products as $item){
								?>
								<tr>
									<td>
										<p><?php echo $item->order_product_name; ?></p><?php
										if(!empty($itemFields)){
											foreach($itemFields as $field){
												$namekey = $field->field_namekey;
												if(empty($item->$namekey)) continue;
												echo '<p>'.$fieldsClass->getFieldName($field).': '.$fieldsClass->show($field,$item->$namekey).'</p>';
											}
										} ?>
									</td>
									<td>
										<?php echo $currencyHelper->format($item->order_product_price+$item->order_product_tax,$data->order_currency_id); ?>
									</td>
									<td>
										<?php echo $item->order_product_quantity; ?>
									</td>
									<td style="text-align:right">
										<?php echo $currencyHelper->format($item->order_product_total_price,$data->order_currency_id); ?>
									</td>
								</tr>
								<?php
							}
							if(bccomp($data->order_discount_price,0,5)){
								echo '<tr><td colspan="4" style="text-align:right">'.JText::_('HIKASHOP_COUPON').' : '.$currencyHelper->format($data->order_discount_price,$data->order_currency_id).'</td></tr>';
							}
							if(bccomp($data->order_shipping_price,0,5)){
								echo '<tr><td colspan="4" style="text-align:right">'.JText::_('HIKASHOP_SHIPPING_METHOD').' : '.$currencyHelper->format($data->order_shipping_price,$data->order_currency_id).'</td></tr>';
							}
							echo '<tr><td colspan="4" style="text-align:right">'.JText::sprintf('TOTAL_WITHOUT_VAT',$currencyHelper->format($data->cart->full_total->prices[0]->price_value,$data->order_currency_id)).'</td></tr>';
							echo '<tr><td colspan="4" style="text-align:right;font-weight:bold;">'.JText::sprintf('TOTAL_WITH_VAT',$currencyHelper->format($data->cart->full_total->prices[0]->price_value_with_tax,$data->order_currency_id)).'</td></tr>';
							?>
					</table>
				</td>
			</tr>
			<tr>
				<td height="10">
				</td>
			</tr>
			<tr>
				<td>
					<table width="100%" style="border: 1px solid #DDDDDD;font-family: Verdana, Arial, Helvetica, sans-serif;font-size:12px;">
					<?php
					$params = null;
					$js = '';
					$fieldsClass = hikashop_get('class.field');
					$app=&JFactory::getApplication();
					$template = trim(hikashop_getLayout($app->isAdmin() ? 'order' : 'address','address_template',$params,$js));
					if(!empty($data->cart->billing_address)){
						$billing = $template;
						foreach($data->cart->fields as $field){
							$fieldname = $field->field_namekey;
							$billing=str_replace('{'.$fieldname.'}',$fieldsClass->show($field,$data->cart->billing_address->$fieldname),$billing);
						}
						echo '<tr><td style="font-weight:bold;background-color:#DDDDDD">'.JText::_('HIKASHOP_BILLING_ADDRESS').'</td></tr><tr><td>';
						echo str_replace(array("\r\n","\r","\n"),'<br/>',preg_replace('#{(?:(?!}).)*}#i','',$billing)).'<br/></td></tr>';
					}
					if(!empty($data->cart->order_shipping_id) && !empty($data->cart->shipping_address)){
						$shipping = $template;
						foreach($data->cart->fields as $field){
							$fieldname = $field->field_namekey;
							$shipping=str_replace('{'.$fieldname.'}',$fieldsClass->show($field,$data->cart->shipping_address->$fieldname),$shipping);
						}
						echo '<tr><td style="font-weight:bold;background-color:#DDDDDD">'.JText::_('HIKASHOP_SHIPPING_ADDRESS').'</td></tr><tr><td>';
						echo str_replace(array("\r\n","\r","\n"),'<br/>',preg_replace('#{(?:(?!}).)*}#i','',$shipping)).'<br/></td></tr>';
					}?>
					</table>
				</td>
			</tr>
			<tr>
				<td height="10">
				</td>
			</tr>
			<tr>
				<td height="10">
				</td>
			</tr>
			<tr>
				<td height="10">
				</td>
			</tr>
		</tbody>
	</table>
</div>