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
$this->setLayout('listing_price');
$this->params->set('show_quantity_field', 0);
$comp_description = $this->params->get('comp_description');
if(empty($comp_description)){
	$this->params->set('comp_description',JText::_('CART_EMPTY'));
}
?>
<div id="hikashop_checkout_cart" class="hikashop_checkout_cart">
	<?php
	if(empty($this->rows)){
		echo $this->params->get('comp_description');
	}else{
		if($this->config->get('print_cart',0)&&JRequest::getVar('tmpl','')!='component'){ ?>
			<div class="hikashop_checkout_cart_print_link">
				<a title="<?php echo JText::_('HIKA_PRINT');?>" class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('checkout&task=printcart',true); ?>">
					<img src="<?php echo HIKASHOP_IMAGES; ?>print.png" alt="<?php echo JText::_('HIKA_PRINT');?>"/>
				</a>
			</div>
		<?php }
		foreach($this->rows as $i => $row){
			if(empty($row->cart_product_quantity)) continue;
			if(!empty($row->product_min_per_order)){
				if($row->product_min_per_order>$row->cart_product_quantity){
					$this->nextButton = false;
					$app =& JFactory::getApplication();
					$app->enqueueMessage(JText::sprintf('YOU_NEED_TO_ORDER_AT_LEAST_X_X',$row->product_min_per_order,$row->product_name));
				}
			}
		}
		$row_count = 4;
	?>
	<br/>
		<table width="100%">
			<thead>
				<tr>
					<?php if($this->params->get('show_cart_image')){ $row_count++;?>
						<th id="hikashop_cart_product_image_title" class="hikashop_cart_product_image_title hikashop_cart_title">
							<?php echo JText::_('CART_PRODUCT_IMAGE'); ?>
						</th>
					<?php } ?>
					<th id="hikashop_cart_product_name_title" class="hikashop_cart_product_name_title hikashop_cart_title">
						<?php echo JText::_('CART_PRODUCT_NAME'); ?>
					</th>
					<th id="hikashop_cart_product_price_title" class="hikashop_cart_product_price_title hikashop_cart_title">
						<?php echo JText::_('CART_PRODUCT_UNIT_PRICE'); ?>
					</th>
					<th id="hikashop_cart_product_quantity_title" class="hikashop_cart_product_quantity_title hikashop_cart_title">
						<?php echo JText::_('PRODUCT_QUANTITY'); ?>
					</th>
					<th id="hikashop_cart_product_total_title" class="hikashop_cart_product_total_title hikashop_cart_title">
						<?php echo JText::_('CART_PRODUCT_TOTAL_PRICE'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $row_count; ?>">
						<hr></hr>
					</td>
				</tr>
				<?php if(!empty($this->coupon) || !empty($this->shipping)){
					?>
				<tr>
					<?php if($this->params->get('show_cart_image')) echo '<td></td>'; ?>
					<td>
					</td>
					<td>
					</td>
					<td id="hikashop_checkout_cart_total2_title" class="hikashop_cart_subtotal_title hikashop_cart_title">
						<?php echo JText::_('HIKASHOP_TOTAL'); ?>
					</td>
					<td class="hikashop_cart_subtotal_value">
					<?php
						$this->row=$this->total;
						echo $this->loadTemplate();
					?>
					</td>
				</tr>
				<?php }
				if(!empty($this->coupon)){
					?>
					<tr>
						<?php if($this->params->get('show_cart_image')) echo '<td></td>'; ?>
						<td>
						</td>
						<td>
						</td>
						<td id="hikashop_checkout_cart_coupon_title" class="hikashop_cart_coupon_title hikashop_cart_title">
							<?php echo JText::_('HIKASHOP_COUPON'); ?>
						</td>
						<td class="hikashop_cart_coupon_value" >
							<span class="hikashop_checkout_cart_coupon">
							<?php
								echo $this->currencyHelper->format(@$this->coupon->discount_value*-1,@$this->coupon->discount_currency_id);
							?>
							</span>
						</td>
					</tr>
				<?php
				}
				$taxes = round($this->full_total->prices[0]->price_value_with_tax-$this->full_total->prices[0]->price_value,$this->currencyHelper->getRounding($this->full_total->prices[0]->price_currency_id));
				if(!empty($this->shipping)){
					?>
					<tr>
						<?php if($this->params->get('show_cart_image')) echo '<td></td>'; ?>
						<td>
						</td>
						<td>
						</td>
						<td id="hikashop_checkout_cart_shipping_title" class="hikashop_cart_shipping_title hikashop_cart_title">
							<?php echo JText::_('HIKASHOP_SHIPPING'); ?>
						</td>
						<td class="hikashop_cart_shipping_value" >
							<span class="hikashop_checkout_cart_shipping">
							<?php
								if(bccomp($taxes,0,5)==0){
									echo $this->currencyHelper->format(@$this->shipping->shipping_price,$this->shipping->shipping_currency_id);
								}else{
									echo $this->currencyHelper->format(@$this->shipping->shipping_price_with_tax,$this->shipping->shipping_currency_id);
								}
							?>
							</span>
						</td>
					</tr>
					<?php
				}
				if(bccomp($taxes,0,5)){ ?>
				<tr>
					<?php if($this->params->get('show_cart_image')) echo '<td></td>'; ?>
					<td>
					</td>
					<td>
					</td>
					<td id="hikashop_checkout_cart_tax_title" class="hikashop_cart_tax_title hikashop_cart_title">
						<?php echo JText::_('TAXES'); ?>
					</td>
					<td class="hikashop_cart_tax_value">
						<span class="hikashop_checkout_cart_taxes">
						<?php
							echo $this->currencyHelper->format($taxes,$this->full_total->prices[0]->price_currency_id);
						?>
						</span>
					</td>
				</tr>
				<?php }?>
				<tr>
					<?php if($this->params->get('show_cart_image')) echo '<td></td>'; ?>
					<td>
					</td>
					<td>
					</td>
					<td id="hikashop_checkout_cart_final_total_title" class="hikashop_cart_total_title hikashop_cart_title">
						<?php echo JText::_('HIKASHOP_FINAL_TOTAL'); ?>
					</td>
					<td class="hikashop_cart_total_value">
						<span class="hikashop_checkout_cart_final_total">
						<?php
							echo $this->currencyHelper->format($this->full_total->prices[0]->price_value_with_tax,$this->full_total->prices[0]->price_currency_id);
						?>
						</span>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					$k = 0;
					$group = $this->config->get('group_options',0);
					foreach($this->rows as $i => $row){
						if(empty($row->cart_product_quantity)) continue;
						if($group && $row->cart_product_option_parent_id) continue;
						?>
						<tr class="<?php echo "row$k"; ?>">
							<?php if($this->params->get('show_cart_image')){ ?>
								<td class="hikashop_cart_product_image_value">
									<?php if(!empty($row->images)){
										$image = reset($row->images);
										if(!$this->config->get('thumbnail')){
											echo '<img src="'.$this->image->uploadFolder_url.$image->file_path.'" alt="'.$image->file_name.'" id="hikashop_main_image" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle" />';
										}else{
											$height = $this->config->get('thumbnail_y');  ?>
											<div class="hikashop_cart_product_image_thumb" >
											<?php echo $this->image->display($image->file_path,true,$image->file_name,'style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle"'); ?>
											</div>
									<?php }
									} ?>
								</td>
							<?php } ?>
							<td class="hikashop_cart_product_name_value">
								<p class="hikashop_cart_product_name">
									<a href="<?php echo hikashop_completeLink('product&task=show&cid='.$row->product_id);?>" ><?php echo $row->product_name; ?></a>
									<?php
									if($group){
										$this->row=&$row;
										$this->unit=true;
										echo  ' '.strip_tags($this->loadTemplate());
									}
									?>
								</p>
								<p class="hikashop_cart_product_custom_item_fields">
								<?php
								if(hikashop_level(2) && !empty($this->extraFields['item'])){
									foreach($this->extraFields['item'] as $field){
										$namekey = $field->field_namekey;
										if(empty($row->$namekey)) continue;
										echo '<p class="hikashop_cart_item_'.$namekey.'">'.$this->fieldsClass->getFieldName($field).': '.$this->fieldsClass->show($field,$row->$namekey).'</p>';
									}
								}
								$input='';
								if($group){
									foreach($this->rows as $j => $optionElement){
										if($optionElement->cart_product_option_parent_id != $row->cart_product_id) continue;
										if(!empty($optionElement->prices[0])){
											if(!isset($row->prices[0])) $row->prices[0]->price_value=0;
											foreach(get_object_vars($row->prices[0]) as $key => $value){
												if(is_object($value)){
													foreach(get_object_vars($value) as $key2 => $var2){
														if(strpos($key2,'price_value')!==false) $row->prices[0]->$key->$key2 +=$optionElement->prices[0]->$key->$key2;
													}
												}else{
													if(strpos($key,'price_value')!==false) $row->prices[0]->$key+=@$optionElement->prices[0]->$key;
												}
											}
										}
										 ?>
											<p class="hikashop_cart_option_name">
												<?php
													echo $optionElement->product_name . ' ( + ';
													$this->row=&$optionElement;
													$this->unit=true;
													echo strip_tags($this->loadTemplate()).' )';
												?>
											</p>
									<?php
									$input .='document.getElementById(\'product_option_'.$optionElement->cart_product_id.'\').value=qty_field.value;';
									echo '<input type="hidden" id="product_option_'.$optionElement->cart_product_id.'" name="item['.$optionElement->cart_product_id.']" value="'.$row->cart_product_quantity.'"/>';
									}
								}?>
								</p>
							</td>
							<td class="hikashop_cart_product_price_value">
								<?php
									$this->row=&$row;
									$this->unit=true;
									echo $this->loadTemplate();
								?>
							</td>
							<td class="hikashop_cart_product_quantity_value">
								<input id="hikashop_checkout_quantity_<?php echo $row->cart_product_id;?>" type="text" name="item[<?php echo $row->cart_product_id;?>]" class="hikashop_product_quantity_field" value="<?php echo $row->cart_product_quantity; ?>" onchange="var qty_field = document.getElementById('hikashop_checkout_quantity_<?php echo $row->cart_product_id;?>'); if (qty_field){<?php echo $input; ?>}" />
								<div class="hikashop_cart_product_quantity_refresh">
									<a href="#" onclick="var qty_field = document.getElementById('hikashop_checkout_quantity_<?php echo $row->cart_product_id;?>'); if (qty_field && qty_field.value != '<?php echo $row->cart_product_quantity; ?>'){<?php echo $input; ?> qty_field.form.submit(); } return false;" title="<?php echo JText::_('HIKA_REFRESH'); ?>">
										<img src="<?php echo HIKASHOP_IMAGES . 'refresh.png';?>" border="0" alt="<?php echo JText::_('HIKA_REFRESH'); ?>" />
									</a>
								</div>
								<?php if($this->params->get('show_delete',1)){ ?>
									<div class="hikashop_cart_product_quantity_delete">
										<a href="<?php echo hikashop_completeLink('product&task=updatecart&product_id='.$row->product_id.'&quantity=0&return_url='.urlencode(base64_encode(urldecode($this->params->get('url'))))); ?>" onclick="var qty_field = document.getElementById('hikashop_checkout_quantity_<?php echo $row->cart_product_id;?>'); if(qty_field){qty_field.value=0; <?php echo $input; ?> qty_field.form.submit();} return false;" title="<?php echo JText::_('HIKA_DELETE'); ?>">
											<img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" border="0" alt="<?php echo JText::_('HIKA_DELETE'); ?>" />
										</a>
									</div>
								<?php } ?>
							</td>
							<td class="hikashop_cart_product_total_value">
								<?php
									$this->row=&$row;
									$this->unit=false;
									echo $this->loadTemplate();
								?>
							</td>
						</tr>
						<?php
						$k = 1-$k;
					}
				?>
			</tbody>
		</table>
		<?php
		if($this->params->get('show_quantity')){ ?>
			<noscript>
				<input id="hikashop_checkout_cart_quantity_button" class="button" type="submit" name="refresh" value="<?php echo JText::_('REFRESH_CART');?>"/>
			</noscript>
		<?php }
	} ?>
</div>