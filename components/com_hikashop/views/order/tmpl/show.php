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
$colspan = 2;
if($this->invoice_type=='order'){
?>
<div id="hikashop_order_main">
<fieldset>
	<div class="header hikashop_header_title"><h1><?php echo JText::_('HIKASHOP_ORDER').':'.$this->element->order_number;?></h1></div>
	<div class="toolbar hikashop_header_buttons" id="toolbar">
		<table>
			<tr>
				<?php if(hikashop_level(1) && $this->config->get('print_invoice_frontend')){ ?>
				<td>
					<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=invoice&order_id='.$this->element->order_id,true); ?>">
						<span class="icon-32-print" title="<?php echo JText::_('PRINT_INVOICE'); ?>">
						</span>
						<?php echo JText::_('HIKA_PRINT'); ?>
					</a>
				</td>
				<?php } ?>
				<td>
					<a onclick="submitbutton('cancel'); return false;" href="#" >
						<span class="icon-32-back" title="<?php echo JText::_('HIKA_BACK'); ?>">
						</span>
						<?php echo JText::_('HIKA_BACK'); ?>
					</a>
				</td>
			</tr>
		</table>
	</div>
</fieldset>
<?php }?>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order'); ?>" method="post" name="adminForm">
	<table width="100%">
<?php if($this->invoice_type!='order'){?>
		<tr>
			<td>
				<span id="hikashop_order_title" class="hikashop_order_title"><?php echo JText::_('INVOICE');?></span>
				<br/>
				<br/>
			</td>
		</tr>
<?php }?>
		<tr>
			<td>
							<div id="hikashop_order_right_part" class="hikashop_order_right_part">
							<?php echo JText::_('DATE').': '.hikashop_getDate($this->element->order_created,'%d %B %Y ');?><br/>
							<?php if($this->invoice_type=='order'){
									echo JText::_('HIKASHOP_ORDER');
								}else{
									echo JText::_(strtoupper($this->invoice_type));
								}
								echo ': '.@$this->element->order_number;
							?>
							</div>
							<div id="hikashop_order_left_part" class="hikashop_order_left_part">
							<?php echo $this->store_address;?>
							</div>
			</td>
		</tr>
		<tr>
			<td>
				<br/>
				<br/>
				<table width="100%">
					<tr>
						<?php
							$params = null;
							$js = '';
						?>
						<td>
							<?php if(!empty($this->element->billing_address)){ ?>
							<fieldset class="adminform" id="htmlfieldset_billing">
							<legend style="background-color: #FFFFFF;"><?php echo JText::_('HIKASHOP_BILLING_ADDRESS'); ?></legend>
								<?php
									$html = hikashop_getLayout('address','address_template',$params,$js);
									if(!empty($this->element->fields)){
										foreach($this->element->fields as $field){
											$fieldname = $field->field_namekey;
											$html=str_replace('{'.$fieldname.'}',$this->fieldsClass->show($field,$this->element->billing_address->$fieldname),$html);
										}
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
										$html = hikashop_getLayout('address','address_template',$params,$js);
										if(!empty($this->element->fields)){
											foreach($this->element->fields as $field){
												$fieldname = $field->field_namekey;
												$html=str_replace('{'.$fieldname.'}',$this->fieldsClass->show($field,$this->element->shipping_address->$fieldname),$html);
											}
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
					<table cellpadding="1" width="100%">
						<thead>
							<tr>
								<th class="hikashop_order_item_name_title title">
									<?php echo JText::_('PRODUCT'); ?>
								</th>
								<?php
								$files = false;
								foreach($this->order->products as $product){
									if(!empty($product->files)) $files = true;
								}
								if($this->invoice_type=='order' && $files){ $colspan = 3; ?>
								<th class="hikashop_order_item_files_title title">
									<?php echo JText::_('HIKA_FILES'); ?>
								</th>
								<?php } ?>
								<th class="hikashop_order_item_price_title title">
									<?php echo JText::_('UNIT_PRICE'); ?>
								</th>
								<th class="hikashop_order_item_quantity_title title titletoggle">
									<?php echo JText::_('PRODUCT_QUANTITY'); ?>
								</th>
								<th class="hikashop_order_item_total_title title titletoggle">
									<?php echo JText::_('PRICE'); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$k=0;
							global $Itemid;
							$url = '';
							if(!empty($Itemid)){
								$url='&Itemid='.$Itemid;
							}
							$group = $this->config->get('group_options',0);
							foreach($this->order->products as $product){
								if($group && $product->order_product_option_parent_id) continue;
								?>
								<tr class="row<?php echo $k;?>">
									<td class="hikashop_order_item_name_value">
										<?php if($this->invoice_type=='order' && !empty($product->product_id)){ ?>
											<a class="hikashop_order_product_link" href="<?php echo hikashop_completeLink('product&task=show&cid='.$product->product_id.$url); ?>">
										<?php } ?>
										<p class="hikashop_order_product_name"><?php echo $product->order_product_name; ?></p>
										<p class="hikashop_order_product_code"><?php echo $product->order_product_code; ?></p>
										<?php if($this->invoice_type=='order' && !empty($product->product_id)){ ?>
											</a>
										<?php } ?>
										<p class="hikashop_order_product_custom_item_fields">
										<?php
										if(hikashop_level(2) && !empty($this->fields['item'])){
											foreach($this->fields['item'] as $field){
												$namekey = $field->field_namekey;
												if(!empty($product->$namekey)){
													echo '<p class="hikashop_order_item_'.$namekey.'">'.$this->fieldsClass->getFieldName($field).': '.$this->fieldsClass->show($field,$product->$namekey).'</p>';
												}
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
															echo $optionElement->order_product_name . ' ( + '.$this->currencyHelper->format($optionElement->order_product_price,$this->order->order_currency_id).' )';
														?>
													</p>
											<?php
											}
										}?>
										</p>
									</td>
									<?php if($this->invoice_type=='order' && !empty($product->files)){ ?>
									<td class="hikashop_order_item_files_value">
										<?php
										if($this->order_status_download_ok || bccomp($product->order_product_price,0,5)==0){
											$html = array();
											foreach($product->files as $file){
												if(empty($file->file_name)){
													$file->file_name = $file->file_path;
												}
												$fileHtml = '';
												if(!empty($this->download_time_limit) && ($this->download_time_limit+$this->order->order_created)<time()){
													$fileHtml = JText::_('TOO_LATE_NO_DOWNLOAD');
												}
												if(!empty($this->download_number_limit) && $this->download_number_limit<=$file->download_number){
													$fileHtml = JText::_('MAX_REACHED_NO_DOWNLOAD');
												}
												if(empty($fileHtml)){
														$fileHtml = '<a href="'.hikashop_completeLink('order&task=download&file_id='.$file->file_id.'&order_id='.$this->order->order_id).'">'.$file->file_name.'</a>';
														if(!empty($this->download_time_limit))$fileHtml .= ' / '.JText::sprintf('UNTIL_THE_DATE',hikashop_getDate($this->order->order_created+$this->download_time_limit));
														if(!empty($this->download_number_limit))$fileHtml .= ' / '.JText::sprintf('X_DOWNLOADS_LEFT',$this->download_number_limit-$file->download_number);
												}else{
													$fileHtml = $file->file_name .' '.$fileHtml;
												}
												$html[]=$fileHtml;
											}
											echo implode('<br/>',$html);
										}
										?>
									</td>
									<?php } ?>
									<td class="hikashop_order_item_price_value">
									<?php echo $this->currencyHelper->format($product->order_product_price,$this->order->order_currency_id);?>
									</td>
									<td class="hikashop_order_item_quantity_value">
										<?php echo $product->order_product_quantity;?>
									</td>
									<td class="hikashop_order_item_total_value">
										<?php echo $this->currencyHelper->format($product->order_product_total_price_no_vat,$this->order->order_currency_id);?>
									</td>
								</tr>
								<?php
								$k=1-$k;
							}
						?>
							<tr>
								<td style="border-top:2px solid #B8B8B8;" colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_subtotal_title" style="border-top:2px solid #B8B8B8;" class="key">
									<label>
										<?php echo JText::_( 'SUBTOTAL' ); ?>
									</label>
								</td>
								<td class="hikashop_order_subtotal_value" style="border-top:2px solid #B8B8B8;">
									<?php echo $this->currencyHelper->format($this->order->order_subtotal_no_vat,$this->order->order_currency_id); ?>
								</td>
							</tr>
							<?php $taxes = $this->order->order_subtotal-$this->order->order_subtotal_no_vat;
							if($taxes > 0){ ?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_tax_title key">
									<label>
										<?php echo JText::_( 'VAT' ); ?>
									</label>
								</td>
								<td class="hikashop_order_tax_value">
									<?php echo $this->currencyHelper->format($taxes,$this->order->order_currency_id); ?>
								</td>
							</tr>
							<?php }
							if(!empty($this->order->order_discount_code)){ ?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_coupon_title key">
									<label>
										<?php echo JText::_( 'HIKASHOP_COUPON' ); ?>
									</label>
								</td>
								<td class="hikashop_order_coupon_value" >
									<?php echo $this->currencyHelper->format($this->order->order_discount_price*-1.0,$this->order->order_currency_id); ?>
								</td>
							</tr>
							<?php }
							if(!empty($this->order->order_shipping_method)){ ?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_shipping_title key">
									<label>
										<?php echo JText::_( 'SHIPPING' ); ?>
									</label>
								</td>
								<td class="hikashop_order_shipping_value" >
									<?php echo $this->currencyHelper->format($this->order->order_shipping_price,$this->order->order_currency_id); ?>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_total_title key">
									<label>
										<?php echo JText::_( 'HIKASHOP_TOTAL' ); ?>
									</label>
								</td>
								<td class="hikashop_order_total_value" >
									<?php echo $this->currencyHelper->format($this->order->order_full_price,$this->order->order_currency_id); ?>
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
		<?php if(hikashop_level(2) && !empty($this->fields['order'])){?>
		<tr>
			<td>
				<fieldset class="hikashop_order_custom_fields_fieldset">
					<legend><?php echo JText::_('ADDITIONAL_INFORMATION'); ?></legend>
					<table class="hikashop_order_custom_fields_table adminlist" cellpadding="1" width="100%">
						<?php foreach($this->fields['order'] as $fieldName => $oneExtraField) {
							if(!@$oneExtraField->field_frontcomp || empty($this->order->$fieldName)) continue;
						?>
							<tr class="hikashop_order_custom_field_<?php echo $fieldName;?>_line">
								<td class="key">
									<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
								</td>
								<td>
									<?php echo $this->fieldsClass->show($oneExtraField,$this->order->$fieldName); ?>
								</td>
							</tr>
						<?php }	?>
					</table>
				</fieldset>
			</td>
		</tr>
		<?php } ?>
		<?php if(hikashop_level(2) && !empty($this->order->entries)){?>
		<tr>
			<td>
				<fieldset class="htmlfieldset_entries">
					<legend><?php echo JText::_('HIKASHOP_ENTRIES'); ?></legend>
					<table class="hikashop_entries_table adminlist" cellpadding="1" width="100%">
						<thead>
							<tr>
								<th class="title titlenum">
									<?php echo JText::_( 'HIKA_NUM' );?>
								</th>
							<?php
								if(!empty($this->fields['entry'])){
									foreach($this->fields['entry'] as $field){
										echo '<th class="title">'.$this->fieldsClass->trans($field->field_realname).'</th>';
									}
								}
							?>
							</tr>
						</thead>
						<tbody>
						<?php
							$k=0;
							$i=1;
							foreach($this->order->entries as $entry){
								?>
								<tr class="row<?php echo $k;?>">
									<td>
										<?php echo $i;?>
									</td>
									<?php
									if(!empty($this->fields['entry'])){
										foreach($this->fields['entry'] as $field){
											$namekey = $field->field_namekey;
											echo '<td>'.$this->fieldsClass->show($field,$entry->$namekey).'</td>';
										}
									}
									?>
								</tr>
								<?php
								$k=1-$k;
								$i++;
							}
						?>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
		<?php } ?>
	</table>
	<input type="hidden" name="cid[]" value="<?php echo $this->element->order_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="cancel_redirect" value="<?php echo JRequest::getString('cancel_redirect'); ?>" />
	<input type="hidden" name="cancel_url" value="<?php echo JRequest::getString('cancel_url'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
<div style="page-break-after:always"></div>
