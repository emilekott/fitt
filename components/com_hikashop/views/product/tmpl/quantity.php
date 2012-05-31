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
			if(!empty($this->row->has_options)){
				echo $this->cart->displayButton(JText::_('CHOOSE_OPTIONS'),'choose_options',$this->params,hikashop_completeLink('product&task=show&product_id='.$this->row->product_id.$this->itemid),'window.location = \''.hikashop_completeLink('product&task=show&product_id='.$this->row->product_id.$this->itemid).'\';return false;','');
			}else{
				$url = '';
				if(empty($this->ajax)){
					$this->ajax = 'return hikashopModifyQuantity(\''.$this->row->product_id.'\',field,1,0);';
				}
				if($this->row->product_sale_start || empty($this->element->main)){
					$start_date = $this->row->product_sale_start;
				}else{
					$start_date = $this->element->main->product_sale_start;
				}
				if($this->row->product_sale_end || empty($this->element->main)){
					$end_date = $this->row->product_sale_end;
				}else{
					$end_date = $this->element->main->product_sale_end;
				}
				if($end_date && $end_date<time()){
					?>
					<span class="hikashop_product_sale_end">
						<?php echo JText::_('ITEM_NOT_SOLD_ANYMORE'); ?>
					</span>
					<?php
				}elseif($start_date && $start_date>time()){
					?>
					<span class="hikashop_product_sale_start">
						<?php
						echo JText::sprintf('ITEM_SOLD_ON_DATE',hikashop_getDate($start_date,$this->params->get('date_format','%d %B %Y')));
						?>
					</span>
					<?php
				}elseif(!$this->params->get('catalogue') && ($this->config->get('display_add_to_cart_for_free_products') || !empty($this->row->prices))){
					if($this->row->product_min_per_order<=0){
						$this->row->product_min_per_order=1;
					}
					if($this->row->product_quantity==-1){
					?>
					<div class="hikashop_product_stock">
					<?php
						echo $this->cart->displayButton(JText::_('ADD_TO_CART'),'add',$this->params,$url,$this->ajax,'',$this->row->product_max_per_order,$this->row->product_min_per_order);
					}elseif($this->row->product_quantity>0){
					?>
					<div class="hikashop_product_stock">
					<?php
						echo '<span class="hikashop_product_stock_count">'.JText::sprintf('X_ITEMS_IN_STOCK',$this->row->product_quantity).'</span><br/>';
						$config =& hikashop_config();
						if($config->get('button_style','normal')=='css'){
							echo '<br />';
						}
						echo $this->cart->displayButton(JText::_('ADD_TO_CART'),'add',$this->params,$url,$this->ajax,'',$this->row->product_max_per_order,$this->row->product_min_per_order);
					}else{
						?>
					<div class="hikashop_product_no_stock">
					<?php
						echo JText::_('NO_STOCK');
						$waitlist = $this->config->get('product_waitlist',0); ?>
						</div><div id="hikashop_product_waitlist_main" class="hikashop_product_waitlist_main">
							<?php
							if(hikashop_level(1) && ($waitlist==2 || ($waitlist==1 && !empty($this->element->product_waitlist)))){
								$empty='';
								$params = new JParameter($empty);
								echo $this->cart->displayButton(JText::_('ADD_ME_WAITLIST'),'add_waitlist',$params,hikashop_completeLink('product&task=waitlist&cid='.$this->row->product_id),'window.location=\''.hikashop_completeLink('product&task=waitlist&cid='.$this->row->product_id).'\';return false;');
							} ?>
						</div><?php
					}?>
					</div>
				<?php
				}
			}
				?>