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
	<span class="hikashop_product_price_full">
	<?php
	if(empty($this->row->prices)){
		echo JText::_('FREE_PRICE');
	}else{
		$first=true;
		echo JText::_('PRICE_BEGINNING');
		$i=0;
		foreach($this->row->prices as $price){
			if($first)$first=false;
			else echo JText::_('PRICE_SEPARATOR');
			if(isset($price->price_min_quantity) && empty($this->cart_product_price) && $price->price_min_quantity>1){
				echo '<span class="hikashop_product_price_with_min_qty hikashop_product_price_for_at_least_'.$price->price_min_quantity.'">';
			}
			echo '<span class="hikashop_product_price hikashop_product_price_'.$i.'">';
			if($this->params->get('price_with_tax')){
				echo $this->currencyHelper->format($price->price_value_with_tax,$price->price_currency_id);
			}
			if($this->params->get('price_with_tax')==2){
				echo JText::_('PRICE_BEFORE_TAX');
			}
			if($this->params->get('price_with_tax')==2||!$this->params->get('price_with_tax')){
				echo $this->currencyHelper->format($price->price_value,$price->price_currency_id);
			}
			if($this->params->get('price_with_tax')==2){
				echo JText::_('PRICE_AFTER_TAX');
			}
			if($this->params->get('show_original_price') && !empty($price->price_orig_value)){
				echo JText::_('PRICE_BEFORE_ORIG');
				if($this->params->get('price_with_tax')){
					echo $this->currencyHelper->format($price->price_orig_value_with_tax,$price->price_orig_currency_id);
				}
				if($this->params->get('price_with_tax')==2){
					echo JText::_('PRICE_BEFORE_TAX');
				}
				if($this->params->get('price_with_tax')==2||!$this->params->get('price_with_tax')){
					echo $this->currencyHelper->format($price->price_orig_value,$price->price_orig_currency_id);
				}
				if($this->params->get('price_with_tax')==2){
					echo JText::_('PRICE_AFTER_TAX');
				}
				echo JText::_('PRICE_AFTER_ORIG');
			}
			echo '</span> ';
			if(!empty($this->row->discount)){
				if($this->params->get('show_discount')==1){
					echo '<span class="hikashop_product_discount">'.JText::_('PRICE_DISCOUNT_START');
					if(bccomp($this->row->discount->discount_flat_amount,0,5)!==0){
						echo $this->currencyHelper->format(-1*$this->row->discount->discount_flat_amount,$price->price_currency_id);
					}elseif(bccomp($this->row->discount->discount_percent_amount,0,5)!==0){
						echo -1*$this->row->discount->discount_percent_amount.'%';
					}
					echo JText::_('PRICE_DISCOUNT_END').'</span>';
				}elseif($this->params->get('show_discount')==2){
					echo '<span class="hikashop_product_price_before_discount">'.JText::_('PRICE_DISCOUNT_START');
					if($this->params->get('price_with_tax')){
						echo $this->currencyHelper->format($price->price_value_without_discount_with_tax,$price->price_currency_id);
					}
					if($this->params->get('price_with_tax')==2){
						echo JText::_('PRICE_BEFORE_TAX');
					}
					if($this->params->get('price_with_tax')==2||!$this->params->get('price_with_tax')){
						echo $this->currencyHelper->format($price->price_value_without_discount,$price->price_currency_id);
					}
					if($this->params->get('price_with_tax')==2){
						echo JText::_('PRICE_AFTER_TAX');
					}
					if($this->params->get('show_original_price') && !empty($price->price_orig_value_without_discount_with_tax)){
						echo JText::_('PRICE_BEFORE_ORIG');
						if($this->params->get('price_with_tax')){
							echo $this->currencyHelper->format($price->price_orig_value_without_discount_with_tax,$price->price_orig_currency_id);
						}
						if($this->params->get('price_with_tax')==2){
							echo JText::_('PRICE_BEFORE_TAX');
						}
						if($this->params->get('price_with_tax')==2||!$this->params->get('price_with_tax') && !empty($price->price_orig_value_without_discount)){
							echo $this->currencyHelper->format($price->price_orig_value_without_discount,$price->price_orig_currency_id);
						}
						if($this->params->get('price_with_tax')==2){
							echo JText::_('PRICE_AFTER_TAX');
						}
						echo JText::_('PRICE_AFTER_ORIG');
					}
					echo JText::_('PRICE_DISCOUNT_END').'</span>';
				}
			}
			if(isset($price->price_min_quantity) && empty($this->cart_product_price)){
				if($price->price_min_quantity>1){
					echo JText::sprintf('PER_UNIT_AT_LEAST_X_BOUGHT',$price->price_min_quantity);
				}else{
					echo JText::_('PER_UNIT');
				}
			}
			if($this->params->get('show_price_weight')){
				if(!empty($this->element->product_id) && isset($this->row->product_weight) && bccomp($this->row->product_weight,0,3)){
					echo JText::_('PRICE_SEPARATOR').'<span class="hikashop_product_price_per_weight_unit">';
					if($this->params->get('price_with_tax')){
						$weight_price = $price->price_value_with_tax / $this->row->product_weight;
						echo $this->currencyHelper->format($weight_price,$price->price_currency_id).' / '.JText::_($this->row->product_weight_unit);
					}
					if($this->params->get('price_with_tax')==2){
						echo JText::_('PRICE_BEFORE_TAX');
					}
					if($this->params->get('price_with_tax')==2||!$this->params->get('price_with_tax')){
						$weight_price = $price->price_value / $this->row->product_weight;
						echo $this->currencyHelper->format($weight_price,$price->price_currency_id).' / '.JText::_($this->row->product_weight_unit);
					}
					if($this->params->get('price_with_tax')==2){
						echo JText::_('PRICE_AFTER_TAX');
					}
					echo '</span>';
				}
			}
			if(isset($price->price_min_quantity) && empty($this->cart_product_price) && $price->price_min_quantity>1){
				echo '</span>';
			}
			$i++;
		}
		echo JText::_('PRICE_END');
	}
	?></span>