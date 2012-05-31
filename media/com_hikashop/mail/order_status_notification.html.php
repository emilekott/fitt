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
<div style="background-color: #ffffff; font-size: 100%; font-family: Tahoma,Geneva,Kalimati,sans-serif; color: #8a8a8a; width: 100%;">
	<table style="margin: auto; width: 560px;" border="0" cellspacing="0" cellpadding="0">
		<tbody>
			<tr>
				<td height="10">
				</td>
			</tr>
			<tr>
				<td height="30" style="margin: auto; font-size: 10px; background-color: #ffffff; color: #000000; text-align: center" colspan="3">
					<?php echo JText::sprintf('DATE_ON_WEBSITE',hikashop_getDate(time(),'%Y-%m-%d %H:%M'),HIKASHOP_LIVE);?>
				</td>
			</tr>
			<tr>
				<td height="10" style="background-color: #ffffff;">
				</td>
			</tr>
			<tr>
				<td style="background-color: #ffffff;">
					<?php echo JText::sprintf('HI_CUSTOMER',@$data->customer->name);?>
					<br/>
					<br/>
					<?php
					$order_url = $data->order_url;
					$mail_status = $data->mail_status;
					$customer = $data->customer;
					$order_status = $data->order_status;
					$class = hikashop_get('class.order');
					$data = $class->get($data->order_id);
					$url = $data->order_number;
					$data->order_url = $order_url;
					$data->order_status = $order_status;
					$data->mail_status = $mail_status;
					$config =& hikashop_config();
					if($config->get('simplified_registration',0)!=2){
						$url = '<a href="'.$order_url.'">'. $url.'</a>';
					}
					echo JText::sprintf('ORDER_STATUS_CHANGED_TO',$url,$data->mail_status);
                    $data->cart = $class->loadFullOrder($data->order_id,true,false);
					$data->cart->coupon = null;
					$price = null;
					$tax = $data->cart->order_subtotal - $data->cart->order_subtotal_no_vat + $data->order_discount_tax + $data->order_shipping_tax;
					$price->price_value = $data->order_full_price-$tax;
					$price->price_value_with_tax = $data->order_full_price;
					$data->cart->full_total = null;
					$data->cart->full_total->prices = array($price);
					$data->cart->coupon->discount_value =& $data->order_discount_price;
					$app=&JFactory::getApplication();
					if($app->isAdmin()){
						$view = 'order';
					}else{
						$view = 'address';
					}
					?>
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
										}
										$statusDownload = explode(',',$config->get('order_status_for_download','confirmed,shipped'));
										if(!empty($item->files) && in_array($data->order_status,$statusDownload)){
											echo '<p>';
											foreach($item->files as $file){
												$fileName = empty($file->file_name) ? $file->file_path : $file->file_name;
												echo '<a href="'.hikashop_frontendLink('index.php?option=com_hikashop&ctrl=order&task=download&file_id='.$file->file_id.'&order_id='.$data->order_id).'">'.$fileName.'</a><br/>';
											}
											echo '</p>';
										}
										?>
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
					$template = trim(hikashop_getLayout($view,'address_template',$params,$js));
					if(!empty($data->cart->billing_address)){
						$billing = $template;
						foreach($data->cart->fields as $field){
							$fieldname = $field->field_namekey;
							$billing=str_replace('{'.$fieldname.'}',$fieldsClass->show($field,$data->cart->billing_address->$fieldname),$billing);
						}
						echo '<tr><td style="font-weight:bold;background-color:#DDDDDD">'.JText::_('HIKASHOP_BILLING_ADDRESS').'</td></tr><tr><td>';
						echo str_replace(array("\r\n","\r","\n"),'<br/>',preg_replace('#{(?:(?!}).)*}#i','',$billing)).'<br/></td></tr>';
					}
					if(!empty($data->order_shipping_id) && !empty($data->cart->shipping_address)){
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
					<?php echo JText::sprintf('THANK_YOU_FOR_YOUR_ORDER',HIKASHOP_LIVE);?>
					<br/>
					<br/>
					<?php echo JText::sprintf('BEST_REGARDS_CUSTOMER',$mail->from_name);?>
				</td>
			</tr>
			<tr>
				<td height="10" style="background-color: #ffffff;">
				</td>
			</tr>
			<tr>
				<td height="10">
				</td>
			</tr>
		</tbody>
	</table>
</div>
<?php $data->customer = $customer;?>