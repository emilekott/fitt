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
					<?php echo JText::sprintf('HI_CUSTOMER',@$data->customer->name);?>
					<br/>
					<br/>
					<?php
					$url = $data->order_number;
					$config =& hikashop_config();
					if($config->get('simplified_registration',0)!=2){
						$url .= ' ( '.$data->order_url.' )';
					}
					echo JText::sprintf('ORDER_CREATION_SUCCESS_ON_WEBSITE_AT_DATE',$url,HIKASHOP_LIVE, hikashop_getDate(time(),'%d %B %Y'), hikashop_getDate(time(),'%H:%M'));?>
				</td>
			</tr>
			<tr>
				<td height="10">
				</td>
			</tr>
			<tr>
				<td>
					<h1 style="background-color:#DDDDDD;font-size:14px;width:100%;padding:5px;"><?php echo JText::_('SUMMARY_OF_YOUR_ORDER');?></h1>
					<br/>
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
								echo '<tr><td colspan="4" style="text-align:right">'.JText::_('HIKASHOP_COUPON').' : '.$currencyHelper->format($data->order_discount_price*-1,$data->order_currency_id).'</td></tr>';
							}
							if(bccomp($data->order_shipping_price,0,5)){
								echo '<tr><td colspan="4" style="text-align:right">'.JText::_('HIKASHOP_SHIPPING_METHOD').' : '.$currencyHelper->format($data->order_shipping_price,$data->order_currency_id).'</td></tr>';
							}
							if($data->cart->full_total->prices[0]->price_value!=$data->cart->full_total->prices[0]->price_value_with_tax) echo '<tr><td colspan="4" style="text-align:right">'.JText::sprintf('TOTAL_WITHOUT_VAT',$currencyHelper->format($data->cart->full_total->prices[0]->price_value,$data->order_currency_id)).'</td></tr>';
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
						echo '<tr><td style="font-weight:bold;background-color:#DDDDDD">'.JText::_('HIKASHOP_BILLING_ADDRESS').'</td></tr><tr><td>';
						echo str_replace(array("\r\n","\r","\n"),'<br/>',preg_replace('#{(?:(?!}).)*}#i','',$billing)).'<br/></td></tr>';
					}
					if(!empty($data->cart->has_shipping) && !empty($data->cart->shipping_address)){
						$shipping = $template;
						foreach($data->order_addresses_fields as $field){
							$fieldname = $field->field_namekey;
							$address =& $data->order_addresses[$data->cart->shipping_address->address_id];
							$shipping=str_replace('{'.$fieldname.'}',$fieldsClass->show($field,$address->$fieldname),$shipping);
						}
						echo '<tr><td style="font-weight:bold;background-color:#DDDDDD">'.JText::_('HIKASHOP_SHIPPING_ADDRESS').'</td></tr><tr><td>';
						echo str_replace(array("\r\n","\r","\n"),'<br/>',preg_replace('#{(?:(?!}).)*}#i','',$shipping)).'<br/></td></tr>';
					}?>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<?php
					$fields = $fieldsClass->getFields('frontcomp',$data,'order','');
					foreach($fields as $fieldName => $oneExtraField) {
						echo "<br/>".$fieldsClass->trans($oneExtraField->field_realname).' : '.$fieldsClass->show($oneExtraField,$data->$fieldName);
					} ?>
				</td>
			</tr>
			<tr>
				<td height="10">
				</td>
			</tr>
			<tr>
				<td>
					<?php
					if(!$app->isAdmin()){
						echo JText::_('ORDER_VALID_AFTER_PAYMENT'); ?>
						<br/>
						<br/>
						<?php echo JText::sprintf('THANK_YOU_FOR_YOUR_ORDER',HIKASHOP_LIVE);
					}?>
					<br/>
					<br/>
					<?php echo JText::sprintf('BEST_REGARDS_CUSTOMER',$mail->from_name);?>
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