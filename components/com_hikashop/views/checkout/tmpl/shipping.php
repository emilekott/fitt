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
<?php if(!empty($this->rates)){ ?>
<div class="hikashop_shipping_methods" id="hikashop_shipping_methods">
	<fieldset>
		<legend><?php echo JText::_('HIKASHOP_SHIPPING_METHOD');?></legend>
		<table>
			<?php
			$this->setLayout('listing_price');
			$this->params->set('show_quantity_field', 0);
			$auto_select_default = $this->config->get('auto_select_default',2);
			if($auto_select_default==1 && count($this->rates)>1) $auto_select_default=0;
			$done=false;
			$k = 0;
			foreach($this->rates as $rate){
				$checked = '';
				if(($this->shipping_method==$rate->shipping_type && $this->shipping_id==$rate->shipping_id)|| ($auto_select_default && empty($this->shipping_id)&&!$done)){
					$done = true;
					$checked = 'checked="checked"';
				}
				if($this->config->get('auto_submit_methods',1) && empty($checked)){
					$checked.=' onclick="this.form.submit(); return false;"';
				}
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<input class="hikashop_checkout_shipping_radio" type="radio" name="hikashop_shipping" id="radio_<?php echo $rate->shipping_type.'_'.$rate->shipping_id;?>" value="<?php echo $rate->shipping_type.'_'.$rate->shipping_id;?>" <?php echo $checked; ?> />
				</td>
				<td><label for="radio_<?php echo $rate->shipping_type.'_'.$rate->shipping_id;?>" style="cursor:pointer;">
					<span class="hikashop_checkout_shipping_image">
					<?php
						if(!empty($rate->shipping_images)){
							$images = explode(',',$rate->shipping_images);
							if(!empty($images)){
								foreach($images as $image){
									if(!empty($this->images_shipping[$image])){
										?>
										<img src="<?php echo HIKASHOP_IMAGES .'shipping/'.  $this->images_shipping[$image];?>" alt=""/>
										<?php
									}
								}
							}
						}
					?>
					</span>
					</label>
				</td>
				<td><label for="radio_<?php echo $rate->shipping_type.'_'.$rate->shipping_id;?>" style="cursor:pointer;">
					<span class="hikashop_checkout_shipping_name"><?php echo $rate->shipping_name;?></span>
					<span class="hikashop_checkout_shipping_price_full">
						<?php
						if(empty($rate->shipping_price_with_tax)){
							$rate->shipping_price_with_tax = $rate->shipping_price;
						}
						if(empty($rate->shipping_price)){
							$rate->shipping_price = $rate->shipping_price_with_tax;
						}
						$taxes = round($rate->shipping_price_with_tax-$rate->shipping_price,$this->currencyHelper->getRounding($rate->shipping_currency_id));
						$prices_taxes = 1;
						if(bccomp($taxes,0,5)==0){
							$prices_taxes = 0;
						}
						if(bccomp($rate->shipping_price,0,5)===0){
							echo JText::_('FREE_SHIPPING');
						}else{
							echo JText::_('PRICE_BEGINNING');
							echo '<span class="hikashop_checkout_shipping_price">';
							if($prices_taxes){
								echo $this->currencyHelper->format($rate->shipping_price_with_tax,$rate->shipping_currency_id);
								echo JText::_('PRICE_BEFORE_TAX');
								echo $this->currencyHelper->format($rate->shipping_price,$rate->shipping_currency_id);
								echo JText::_('PRICE_AFTER_TAX');
							}else{
								echo $this->currencyHelper->format($rate->shipping_price,$rate->shipping_currency_id);
							}
							if($this->params->get('show_original_price') && isset($rate->shipping_price_orig) && bccomp($rate->shipping_price_orig,0,5)){
								echo JText::_('PRICE_BEFORE_ORIG');
								if($prices_taxes){
									echo $this->currencyHelper->format($rate->shipping_price_orig_with_tax,$rate->shipping_currency_id_orig);
								}else{
									echo $this->currencyHelper->format($rate->shipping_price_orig,$rate->shipping_currency_id_orig);
								}
								echo JText::_('PRICE_AFTER_ORIG');
							}
							echo '</span> ';
							echo JText::_('PRICE_END');
						}
						?>
					</span>
					</label>
					<br/>
					<div class="hikashop_checkout_shipping_description"><?php echo $rate->shipping_description;?></div>
				</td>
			</tr>
			<?php $k = 1-$k;
			} ?>
		</table>
	</fieldset>
</div>
<?php } ?>