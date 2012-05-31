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
<div id="print" style="float:right">
	<a href="#" onclick="document.getElementById('print').style.visibility='hidden';window.focus();window.print();return false;">
		<img src="<?php echo HIKASHOP_IMAGES; ?>print.png"/>
	</a>
</div>
<br/>
<table width="100%">
	<tr>
		<td>
			<h1 style="text-align:center">
			<?php
			if($this->invoice_type=='full'){
				echo JText::_('INVOICE');
			}else{
				echo JText::_('SHIPPING_INVOICE');
			}
			?>
			</h1>
			<br/>
			<br/>
		</td>
	</tr>
	<tr>
		<td>
			<div style="float:right;width:100px;padding-top:20px">
			<?php echo JText::_('DATE').': '.hikashop_getDate($this->element->order_created,'%d %B %Y ');?><br/>
			<?php echo JText::_('INVOICE').': '.@$this->element->order_number;?>
			</div>
			<p>
			<?php echo $this->store_address;?>
			</p>
		</td>
	</tr>
	<tr>
		<td>
			<br/>
			<br/>
			<table width="100%">
				<tr>
					<?php if($this->invoice_type=='full' && !empty($this->element->billing_address)){?>
					<td>
						<fieldset class="adminform" id="htmlfieldset_billing">
						<legend style="background-color: #FFFFFF;"><?php echo JText::_('HIKASHOP_BILLING_ADDRESS'); ?></legend>
							<?php
								$this->setLayout('address_template');
								$html = $this->loadTemplate();
								foreach($this->element->fields as $field){
									$fieldname = $field->field_namekey;
									$html=str_replace('{'.$fieldname.'}',$this->fieldsClass->show($field,$this->element->billing_address->$fieldname),$html);
								}
								echo str_replace("\n","<br/>\n",str_replace("\n\n","\n",preg_replace('#{(?:(?!}).)*}#i','',$html)));
							?>
						</fieldset>
					</td>
					<?php }?>
					<td>
					<?php
						if(!empty($this->element->order_shipping_id) && !empty($this->element->shipping_address)){
							?>
							<fieldset class="adminform" id="htmlfieldset_shipping">
								<legend style="background-color: #FFFFFF;"><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS'); ?></legend>
								<?php
									$this->setLayout('address_template');
									$html = $this->loadTemplate();
									foreach($this->element->fields as $field){
										$fieldname = $field->field_namekey;
										$html=str_replace('{'.$fieldname.'}',$this->fieldsClass->show($field,$this->element->shipping_address->$fieldname),$html);
									}
									echo str_replace("\n","<br/>\n",str_replace("\n\n","\n",preg_replace('#{(?:(?!}).)*}#i','',$html)));
								?>
							</fieldset>
							<?php
						}
					?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<br/>
			<fieldset class="adminform" id="htmlfieldset_products">
				<legend style="background-color: #FFFFFF;"><?php echo JText::_('PRODUCT_LIST'); ?></legend>
				<table class="adminlist" cellpadding="1" width="100%">
					<thead>
						<tr>
							<th class="title" width="60%">
								<?php echo JText::_('PRODUCT'); ?>
							</th>
							<?php if($this->invoice_type=='full'){?>
							<th class="title">
								<?php echo JText::_('UNIT_PRICE'); ?>
							</th>
							<?php } ?>
							<th class="title titletoggle">
								<?php echo JText::_('PRODUCT_QUANTITY'); ?>
							</th>
							<?php if($this->invoice_type=='full'){?>
							<th class="title titletoggle">
								<?php echo JText::_('PRICE'); ?>
							</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
					<?php
						$k=0;
						$group = $this->config->get('group_options',0);
						foreach($this->order->products as $product){
							if($group && $product->order_product_option_parent_id) continue;
							?>
							<tr class="row<?php echo $k;?>">
								<td>
									<?php echo $product->order_product_name.' '.$product->order_product_code;?>
									<p class="hikashop_order_product_custom_item_fields">
									<?php
									if(hikashop_level(2) && !empty($this->fields['item'])){
										foreach($this->fields['item'] as $field){
											$namekey = $field->field_namekey;
											if(empty($product->$namekey)){
												continue;
											}
											echo '<p class="hikashop_order_item_'.$namekey.'">'.$this->fieldsClass->getFieldName($field).': '.$this->fieldsClass->show($field,$product->$namekey).'</p>';
										}
									}
									if($group){
											foreach($this->order->products as $j => $optionElement){
												if($optionElement->order_product_option_parent_id != $product->order_product_id) continue;
												$product->order_product_price +=$optionElement->order_product_price;
												$product->order_product_total_price_no_vat+=$optionElement->order_product_total_price_no_vat;
												 ?>
													<p class="hikashop_order_option_name">
														<?php
															echo $optionElement->order_product_name . ($this->invoice_type=='full' ? ' ( + '.$this->currencyHelper->format($optionElement->order_product_price,$this->order->order_currency_id).' )' : '');
														?>
													</p>
											<?php
											}
									} ?>
									</p>
								</td>
								<?php if($this->invoice_type=='full'){?>
								<td>
								<?php echo $this->currencyHelper->format($product->order_product_price,$this->order->order_currency_id);?>
								</td>
								<?php } ?>
								<td align="center">
									<?php echo $product->order_product_quantity;?>
								</td>
								<?php if($this->invoice_type=='full'){?>
								<td>
									<?php echo $this->currencyHelper->format($product->order_product_total_price_no_vat,$this->order->order_currency_id);?>
								</td>
								<?php } ?>
							</tr>
							<?php
							$k=1-$k;
						}
					?>
						<?php if($this->invoice_type=='full'){?>
						<tr>
							<td style="border-top:2px solid #B8B8B8;" colspan="2">
							</td>
							<td style="border-top:2px solid #B8B8B8;" class="key">
								<label>
									<?php echo JText::_( 'SUBTOTAL' ); ?>
								</label>
							</td>
							<td style="border-top:2px solid #B8B8B8;">
								<?php echo $this->currencyHelper->format($this->order->order_subtotal_no_vat,$this->order->order_currency_id); ?>
							</td>
						</tr>
						<?php $taxes = $this->order->order_subtotal-$this->order->order_subtotal_no_vat;
							if($taxes > 0){ ?>
						<tr>
							<td colspan="2">
							</td>
							<td class="key">
								<label>
									<?php echo JText::_( 'VAT' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->currencyHelper->format($taxes,$this->order->order_currency_id); ?>
							</td>
						</tr>
						<?php }
							if(!empty($this->order->order_discount_code)){ ?>
						<tr>
							<td colspan="2">
							</td>
							<td class="key">
								<label>
									<?php echo JText::_( 'HIKASHOP_COUPON' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->currencyHelper->format($this->order->order_discount_price*-1.0,$this->order->order_currency_id); ?>
							</td>
						</tr>
						<?php }
							if(!empty($this->order->order_shipping_method)){ ?>
						<tr>
							<td colspan="2">
							</td>
							<td class="key">
								<label>
									<?php echo JText::_( 'SHIPPING' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->currencyHelper->format($this->order->order_shipping_price,$this->order->order_currency_id); ?>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td colspan="2">
							</td>
							<td class="key">
								<label>
									<?php echo JText::_( 'HIKASHOP_TOTAL' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->currencyHelper->format($this->order->order_full_price,$this->order->order_currency_id); ?>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</fieldset>
		</td>
	</tr>
	<?php if($this->invoice_type=='full'){ ?>
	<tr>
		<td>
		<?php if(!empty($this->shipping)){
			echo JText::_('HIKASHOP_SHIPPING_METHOD').' : '.$this->shipping->getName($this->order->order_shipping_method,$this->order->order_shipping_id).'<br/>';
		}?>
		<?php if(!empty($this->payment)){
			echo JText::_('HIKASHOP_PAYMENT_METHOD').' : '.$this->payment->getName($this->order->order_payment_method,$this->order->order_payment_id);
		}?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td>
		</td>
	</tr>
</table>
<div style="page-break-after:always"></div>