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
$desc = $this->params->get('msg');
if(empty($desc)){
	$this->params->set('msg',JText::_('CART_EMPTY'));
}
if(!headers_sent()){
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' );
}
if(empty($this->rows)){
	$desc = trim($this->params->get('msg'));
	if(!empty($desc)){
		echo $this->notice_html;  ?>
		<div id="hikashop_cart" class="hikashop_cart">
			<?php echo $desc; ?>
		</div>
		<div class="clear_both"></div>
<?php
	}
}else{ ?>
<div id="hikashop_cart" class="hikashop_cart">
	<?php
	echo $this->notice_html;
	$row_count = 1;
	global $Itemid;
	$url_itemid='';
	if(!empty($Itemid)){
		$url_itemid='&Itemid='.$Itemid;
	}
	if($this->params->get('small_cart')){
		$this->row=$this->total;
		if($this->params->get('show_cart_quantity',1)){
			$qty = 0;
			foreach($this->rows as $i => $row){
				if(empty($row->cart_product_quantity)) continue;
				$qty+=$row->cart_product_quantity;
			}
			$text = JText::sprintf('X_ITEMS_FOR_X',$qty,$this->loadTemplate());
		}else{
			$text = JText::sprintf('TOTAL_IN_CART_X',$this->loadTemplate());
		} ?>
		<a class="hikashop_small_cart_checkout_link" href="<?php echo hikashop_completeLink('checkout'.$url_itemid); ?>">
			<span class="hikashop_small_cart_total_title"><?php echo $text; ?></span>
		</a><?php
		if($this->params->get('show_cart_delete',1)){
			$delete = hikashop_completeLink('product&task=cleancart');
			if(strpos($delete,'?')){
				$delete.='&amp;';
			}else{
				$delete.='?';
			} ?>
		<a class="hikashop_small_cart_clean_link" href="<?php echo $delete.'return_url='. urlencode(base64_encode(hikashop_currentURL('url'))); ?>" >
			<img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" border="0" alt="clean cart" />
		</a><?php
		}
	}else{
	?>
	<form action="<?php echo hikashop_completeLink('product&task=updatecart'.$url_itemid,false,true); ?>" method="post" name="hikashop_cart_form">
		<table width="100%">
			<thead>
				<tr>
					<th class="hikashop_cart_module_product_name_title hikashop_cart_title">
						<?php echo JText::_('CART_PRODUCT_NAME'); ?>
					</th>
					<?php if($this->params->get('show_cart_quantity',1)){
						$row_count++; ?>
						<th class="hikashop_cart_module_product_quantity_title hikashop_cart_title">
							<?php echo JText::_('CART_PRODUCT_QUANTITY'); ?>
						</th>
					<?php }
					if($this->params->get('show_cart_price',1)){
						$row_count++; ?>
					<th class="hikashop_cart_module_product_price_title hikashop_cart_title">
						<?php echo JText::_('CART_PRODUCT_PRICE'); ?>
					</th>
					<?php }
					if($this->params->get('show_cart_delete',1)){
						$row_count++; ?>
					<th class="hikashop_cart_title">
					</th>
					<?php }
					if($row_count<2){ ?>
					<th></th>
					<?php }?>
				</tr>
			</thead>
			<?php if($this->params->get('show_cart_price',1)){ ?>
			<tfoot>
				<tr>
					<td colspan="<?php echo $row_count;?>">
						<hr></hr>
					</td>
				</tr>
				<tr>
					<?php if($this->params->get('show_cart_quantity',1)){ ?>
						<td>
						</td>
					<?php }?>
					<td class="hikashop_cart_module_product_total_title">
						<?php echo JText::_('HIKASHOP_TOTAL'); ?>
					</td>
					<td class="hikashop_cart_module_product_total_value">
					<?php
						$this->row=$this->total;
						echo $this->loadTemplate();
					?>
					</td>
					<?php if($this->params->get('show_cart_delete',1)){ ?>
						<td>
						</td>
					<?php }?>
				</tr>
			</tfoot>
			<?php } ?>
			<tbody>
				<?php
					$k = 0;
					$this->cart_product_price = true;
					$group = $this->config->get('group_options',0);
					foreach($this->rows as $i => $row){
						if(empty($row->cart_product_quantity)) continue;
						if($group && $row->cart_product_option_parent_id) continue;
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="hikashop_cart_module_product_name_value hikashop_cart_value">
								<a href="<?php echo hikashop_completeLink('product&task=show&cid='.$row->product_id.$url_itemid);?>" ><?php echo $row->product_name; ?></a>
								<p class="hikashop_cart_product_custom_item_fields">
									<?php
									if(hikashop_level(2) && !empty($this->itemFields)){
										foreach($this->itemFields as $field){
											$namekey = $field->field_namekey;
											if(!empty($row->$namekey)){
												echo '<p class="hikashop_cart_item_'.$namekey.'">'.$this->fieldsClass->getFieldName($field).': '.$this->fieldsClass->show($field,$row->$namekey).'</p>';
											}
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
													echo $optionElement->product_name;
												?>
											</p>
									<?php
									$input .='document.getElementById(\'cart_product_option_'.$optionElement->cart_product_id.'\').value=qty_field.value;';
									echo '<input type="hidden" id="cart_product_option_'.$optionElement->cart_product_id.'" name="item['.$optionElement->cart_product_id.'][cart_product_quantity]" value="'.$row->cart_product_quantity.'"/>';
									}
								}
									?>
								</p>
							</td>
							<?php if($this->params->get('show_cart_quantity',1)){ ?>
							<td class="hikashop_cart_module_product_quantity_value hikashop_cart_value">
								<input id="hikashop_cart_quantity_<?php echo $row->cart_product_id;?>" type="text" name="item[<?php echo $row->cart_product_id;?>][cart_product_quantity]" class="hikashop_product_quantity_field" value="<?php echo $row->cart_product_quantity; ?>" onchange="var qty_field = document.getElementById('hikashop_cart_quantity_<?php echo $row->cart_product_id;?>'); if (qty_field){<?php echo $input; ?> } document.hikashop_cart_form.submit(); return false;" />
							</td>
							<?php }
							if($this->params->get('show_cart_price',1)){ ?>
							<td class="hikashop_cart_module_product_price_value hikashop_cart_value">
								<?php
								$this->row=&$row;
								echo $this->loadTemplate();
								?>
							</td>
							<?php }
							if($this->params->get('show_cart_delete',1)){ ?>
							<td class="hikashop_cart_module_product_delete_value hikashop_cart_value">
								<a href="<?php echo hikashop_completeLink('product&task=updatecart&cart_product_id='.$row->cart_product_id.'&quantity=0&return_url='.urlencode(base64_encode(urldecode($this->params->get('url'))))); ?>" onclick="var qty_field = document.getElementById('hikashop_cart_quantity_<?php echo $row->cart_product_id;?>'); if(qty_field){qty_field.value=0;<?php echo $input; ?> document.hikashop_cart_form.submit(); return false;}else{ return true;}"  title="<?php echo JText::_('HIKA_DELETE'); ?>"><img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" border="0" alt="<?php echo JText::_('HIKA_DELETE'); ?>" /></a>
							</td>
							<?php }
							if($row_count<2){ ?>
							<td></td>
							<?php }?>
						</tr>
						<?php
						$k = 1-$k;
					}
					$this->cart_product_price=false;
				?>
			</tbody>
		</table>
			<?php
			if($this->params->get('show_cart_quantity',1)){ ?>
				<noscript>
					<input type="submit" class="button" name="refresh" value="<?php echo JText::_('REFRESH_CART');?>"/>
				</noscript>
			<?php }
		if($this->params->get('show_cart_proceed',1)){
			echo $this->cart->displayButton(JText::_('PROCEED_TO_CHECKOUT'),'checkout',$this->params,hikashop_completeLink('checkout'.$url_itemid),'');
		} ?>
		<input type="hidden" name="url" value="<?php echo $this->params->get('url');?>"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="task" value="updatecart"/>
	</form>
	<?php } ?>
</div>
<div class="clear_both"></div>
<?php } ?>
<?php
if(JRequest::getWord('tmpl','')=='component'){
	exit;
}