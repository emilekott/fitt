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
<div class="iframedoc" id="iframedoc"></div>
<table width="100%">
	<tr>
		<td>
			<fieldset class="adminform" id="htmlfieldset_general">
				<legend><?php echo JText::_('MAIN_INFORMATION'); ?></legend>
				<table class="admintable">
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'ORDER_NUMBER' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->order->order_number; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="data[order][order_status]">
								<?php echo JText::_( 'ORDER_STATUS' ); ?>
							</label>
						</td>
						<td>
							<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=changestatus&edit=1&order_id='.$this->order->order_id,true);?>" id="status_change_link"></a>
							<?php
								$onchange = ' onfocus="this.oldvalue = this.value;" onchange="var link = document.getElementById(\'status_change_link\');link.href = link.href+\'&status=\' +this.value; this.value=this.oldvalue; SqueezeBox.fromElement(link,{parse: \'rel\'});"';
								echo $this->category->display("filter_status_".$this->order->order_id,$this->order->order_status,$onchange);
							?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'DATE' ); ?>
							</label>
						</td>
						<td>
							<?php echo hikashop_getDate($this->order->order_created,'%Y-%m-%d %H:%M');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'ID' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->order->order_id; ?>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="adminform" id="htmlfieldset_customer">
				<legend><?php echo JText::_('CUSTOMER'); ?></legend>
				<div style="float:right">
					<a class="modal" title="<?php echo JText::_('EDIT')?>" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=user&order_id='.$this->order->order_id,true);?>">
			            <button type="button" onclick="return false">
			              <img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/><?php echo JText::_('EDIT');?>
			            </button>
					</a>
				</div>
				<table class="admintable">
				<?php if(!empty($this->order->customer)){?>
					<?php if(!empty($this->order->customer->name)){?>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'HIKA_NAME' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->order->customer->name.' ('.$this->order->customer->username.')'; ?>
						</td>
					</tr>
					<?php }?>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'HIKA_EMAIL' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->order->customer->user_email; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'ID' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->order->customer->user_id; ?>
							<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='. $this->order->customer->user_id.'&order_id='.$this->order->order_id); ?>">
								<img src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="go" />
							</a>
						</td>
					</tr>
				<?php } ?>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'IP' ); ?>
							</label>
						</td>
						<td>
							<?php
							echo $this->order->order_ip;
							if(!empty($this->order->geolocation) && $this->order->geolocation->geolocation_country!='Reserved'){
								echo ' ( '.$this->order->geolocation->geolocation_city.' '.$this->order->geolocation->geolocation_state.' '.$this->order->geolocation->geolocation_country.' )';
							}
							?>
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
		<td>
			<fieldset class="adminform" id="htmlfieldset_additional">
				<legend><?php echo JText::_('ORDER_ADD_INFO'); ?></legend>
				<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=changeplugin&order_id='.$this->order->order_id,true);?>" id="plugin_change_link"></a>
				<table class="admintable">
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'SUBTOTAL' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->currencyHelper->format($this->order->order_subtotal,$this->order->order_currency_id); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'HIKASHOP_COUPON' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->currencyHelper->format($this->order->order_discount_price*-1.0,$this->order->order_currency_id); ?>
							<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=discount&order_id='.$this->order->order_id,true);?>">
								<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
							</a>
							<?php echo ' '.$this->order->order_discount_code; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'SHIPPING' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->currencyHelper->format($this->order->order_shipping_price,$this->order->order_currency_id); ?>
							<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=changeplugin&plugin='.$this->order->order_shipping_method.'_'.$this->order->order_shipping_id.'&type=shipping&order_id='.$this->order->order_id,true);?>">
								<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
							</a>
							<?php if(!empty($this->shipping)){
								echo $this->shipping->display('data[order][shipping]',$this->order->order_shipping_method,$this->order->order_shipping_id);
							}?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'HIKASHOP_TOTAL' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->currencyHelper->format($this->order->order_full_price,$this->order->order_currency_id); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'PAYMENT_METHOD' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->payment->display('data[order][payment]',$this->order->order_payment_method,$this->order->order_payment_id); ?>
						</td>
					</tr>
					<?php
						if(!empty($this->fields['order'])){
							foreach($this->fields['order'] as $fieldName => $oneExtraField) {
							?>
								<tr>
									<td class="key">
										<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
									</td>
									<td>
										<?php echo $this->fieldsClass->show($oneExtraField,@$this->order->$fieldName); ?>
									</td>
								</tr>
							<?php
							}?>
							<tr>
							<td colspan="2">
								<div style="float:right">
									<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=fields&order_id='.$this->order->order_id,true);?>">
										<img title="Edit additional information" src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
									</a>
								</div>
							</td>
							</tr>
							<?php
						}
					?>
				</table>
			</fieldset>
			<?php if(!empty($this->order->partner)){ ?>
			<fieldset class="adminform" id="htmlfieldset_partner">
				<legend><?php echo JText::_('PARTNER'); ?></legend>
					<table class="admintable">
						<tr>
							<td class="key">
								<label>
									<?php echo JText::_( 'PARTNER_EMAIL' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->order->partner->user_email;?>
								<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='. $this->order->partner->user_id.'&order_id='.$this->order->order_id); ?>">
									<img src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="go" />
								</a>
								<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=partner&order_id='.$this->order->order_id,true); ?>">
									<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit" />
								</a>
							</td>
						</tr>
						<?php if(!empty($this->order->partner->name)){ ?>
							<tr>
								<td class="key">
									<label>
										<?php echo JText::_( 'PARTNER_NAME' ); ?>
									</label>
								</td>
								<td>
									<?php echo $this->order->partner->name; ?>
								</td>
							</tr>
						<?php } ?>
						<tr>
							<td class="key">
								<label>
									<?php echo JText::_( 'PARTNER_FEE' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->currencyHelper->format($this->order->order_partner_price,$this->order->order_partner_currency_id);?>
								<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}"  href="<?php echo hikashop_completeLink('order&task=partner&order_id='.$this->order->order_id,true); ?>">
									<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit" />
								</a>
								<?php
								if(empty($this->order->order_partner_paid)){
									echo JText::_('NOT_PAID').'<img src="'.HIKASHOP_IMAGES.'delete2.png" />';
								}else{
									echo JText::_('PAID').'<img src="'.HIKASHOP_IMAGES.'ok.png" />';
								}
								?>
							</td>
						</tr>
					</table>
				</fieldset>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td id="hikashop_billing_address">
			<?php $this->type = 'billing'; echo $this->loadTemplate('address');?>
		</td>
		<td id="hikashop_shipping_address">
			<?php $this->type = 'shipping'; echo $this->loadTemplate('address');?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<fieldset class="adminform" id="htmlfieldset_products">
				<legend><?php echo JText::_('PRODUCT_LIST'); ?></legend>
				<div style="float:right">
					<a class="modal" title="<?php echo JText::_('ADD')?>" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=product&order_id='.$this->order->order_id,true);?>">
			            <button type="button" onclick="return false">
			              <img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
			            </button>
					</a>
					<a class="modal" title="<?php echo JText::_('ADD_EXISTING_PRODUCT')?>" rel="{handler: 'iframe', size: {x: 860, y: 560}}" href="<?php echo hikashop_completeLink('order&task=product_select&order_id='.$this->order->order_id,true);?>">
			            <button type="button" onclick="return false">
			              <img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD_EXISTING_PRODUCT');?>
			            </button>
					</a>
				</div>
				<table class="adminlist" cellpadding="1">
					<thead>
						<tr>
							<th class="hikashop_order_item_name_title title">
								<?php echo JText::_('PRODUCT'); ?>
							</th>
							<th class="hikashop_order_item_files_title title">
								<?php echo JText::_('HIKA_FILES'); ?>
							</th>
							<th class="hikashop_order_item_price_title title">
								<?php echo JText::_('UNIT_PRICE'); ?>
							</th>
							<th class="hikashop_order_item_quantity_title title titletoggle">
								<?php echo JText::_('PRODUCT_QUANTITY'); ?>
							</th>
							<th class="hikashop_order_item_total_price_title title titletoggle">
								<?php echo JText::_('PRICE'); ?>
							</th>
							<th class="hikashop_order_item_action_title title titletoggle">
								<?php echo JText::_('ACTIONS'); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php
						foreach($this->order->products as $k => $product){
							?>
							<tr>
								<td class="hikashop_order_item_name_value">
									<p class="hikashop_order_item_name">
										<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.$product->product_id,true); ?>">
											<?php echo $product->order_product_name.' '.$product->order_product_code;?>
										</a>
									</p>
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
									}?>
									</p>
								</td>
								<td class="hikashop_order_item_files_value">
									<?php
										if(!empty($product->files)){
											$html = array();
											foreach($product->files as $file){
												if(empty($file->file_name)){
													$file->file_name = $file->file_path;
												}
												$fileHtml = '';
												if(!empty($this->order_status_for_download) && !in_array($this->order->order_status,explode(',',$this->order_status_for_download))){
													$fileHtml .= ' / <b>'.JText::_('BECAUSE_STATUS_NO_DOWNLOAD').'</b>';
												}
												if(!empty($this->download_time_limit)){
														if(($this->download_time_limit+$this->order->order_created)<time()){
															$fileHtml .= ' / <b>'.JText::_('TOO_LATE_NO_DOWNLOAD').'</b>';
														}else{
															$fileHtml .= ' / '.JText::sprintf('UNTIL_THE_DATE',hikashop_getDate($this->order->order_created+$this->download_time_limit));
														}
												}
												if(!empty($this->download_number_limit)){
													if($this->download_number_limit<=$file->download_number){
														$fileHtml .= ' / <b>'.JText::_('MAX_REACHED_NO_DOWNLOAD').'</b>';
													}else{
														$fileHtml .= ' / '.JText::sprintf('X_DOWNLOADS_LEFT',$this->download_number_limit-$file->download_number);
													}
													if($file->download_number){
														$fileHtml .= '<a href="'.hikashop_completeLink('file&task=resetdownload&file_id='.$file->file_id.'&order_id='.$this->order->order_id.'&'.JUtility::getToken().'=1&return='.urlencode(base64_encode(hikashop_completeLink('order&task=edit&cid='.$this->order->order_id,false,true)))).'"><img src="'.HIKASHOP_IMAGES.'delete.png" alt="'.JText::_('HIKA_DELETE').'" /></a>';
													}
												}
												$fileLink = '<a href="'.hikashop_completeLink('order&task=download&file_id='.$file->file_id.'&order_id='.$this->order->order_id).'">'.$file->file_name.'</a>';
												$html[]=$fileLink.' '.$fileHtml;
											}
											echo implode('<br/>',$html);
										}
									?>
								</td>
								<td class="hikashop_order_item_price_value">
								<?php
									echo $this->currencyHelper->format($product->order_product_price,$this->order->order_currency_id);
									if(bccomp($product->order_product_tax,0,5)){
										echo ' '.JText::sprintf('PLUS_X_OF_VAT',$this->currencyHelper->format($product->order_product_tax,$this->order->order_currency_id));
									}
								?>
								</td>
								<td class="hikashop_order_item_quantity_value">
									<?php echo $product->order_product_quantity;?>
								</td>
								<td class="hikashop_order_item_total_price_value">
									<?php echo $this->currencyHelper->format($product->order_product_total_price,$this->order->order_currency_id);?>
								</td>
								<td class="hikashop_order_item_action_value">
									<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=product&product_id='.$product->order_product_id,true);?>">
										<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
									</a>
									<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=product_delete&product_id='.$product->order_product_id,true);?>">
										<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/>
									</a>
								</td>
							</tr>
							<?php
						}
					?>
					</tbody>
				</table>
			</fieldset>
		</td>
	</tr>
	<?php if(!empty($this->order->history)) { ?>
	<tr>
		<td colspan="2">
			<fieldset class="adminform" id="htmlfieldset_history">
				<legend><?php echo JText::_('HISTORY'); ?></legend>
				<table class="adminlist" cellpadding="1">
					<thead>
						<tr>
							<th class="title">
								<?php echo '#'; ?>
							</th>
							<th class="title">
								<?php echo JText::_('HIKA_TYPE'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('ORDER_STATUS'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('REASON'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('HIKA_USER').' / '.JText::_('IP'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('DATE'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('INFORMATION'); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php
						foreach($this->order->history as $k => $history){
							?>
							<tr>
								<td>
									<?php echo $k; ?>
								</td>
								<td>
									<?php
									$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($history->history_type));
									$trans = JText::_($val);
									if($val!=$trans){
										$history->history_type = $trans;
									}
									echo $history->history_type; ?>
								</td>
								<td>
									<?php echo $this->category->get($history->history_new_status); ?>
								</td>
								<td>
									<?php echo $history->history_reason; ?>
								</td>
								<td>
									<?php
									if(!empty($history->history_user_id)){
										$class = hikashop_get('class.user');
										$user = $class->get($history->history_user_id);
										echo $user->username.' / ';
									}
									echo $history->history_ip; ?>
								</td>
								<td>
									<?php echo hikashop_getDate($history->history_created,'%Y-%m-%d %H:%M');?>
								</td>
								<td><?php echo $history->history_data; ?></td>
							</tr>
							<?php
						}
					?>
					</tbody>
				</table>
			</fieldset>
		</td>
	</tr>
	<?php }?>
	<?php if(hikashop_level(2) && !empty($this->order->entries)) { ?>
	<tr>
		<td colspan="2">
			<fieldset class="adminform" id="htmlfieldset_history">
				<legend><?php echo JText::_('HIKASHOP_ENTRIES'); ?></legend>
				<table class="adminlist" cellpadding="1">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'HIKA_NUM' );?>
							</th>
							<th class="title">
								<?php echo JText::_( 'HIKA_EDIT' );?>
							</th>
						<?php
							if(!empty($this->fields['entry'])){
								foreach($this->fields['entry'] as $field){
									echo '<th class="title">'.$this->fieldsClass->trans($field->field_realname).'</th>';
								}
							}
						?>
							<th class="title titlenum">
								<?php echo JText::_('ID'); ?>
							</th>
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
								<td>
									<a class="modal" rel="{handler: 'iframe', size: {x: 320, y: 480}}" href="<?php echo hikashop_completeLink('entry&task=edit&entry_id='.$entry->entry_id,true);?>">
										<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
									</a>
									<a onclick="return confirm('<?php echo JText::_('VALIDDELETEITEMS',true); ?>');" href="<?php echo hikashop_completeLink('order&task=deleteentry&entry_id='.$entry->entry_id.'&'.JUtility::getToken().'=1');?>">
										<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/>
									</a>
								</td>
								<?php
								if(!empty($this->fields['entry'])){
									foreach($this->fields['entry'] as $field){
										$namekey = $field->field_namekey;
										echo '<td>'.$entry->$namekey.'</td>';
									}
								}
								?>
								<td>
									<?php echo $entry->entry_id; ?>
								</td>
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
	<?php }?>
</table>