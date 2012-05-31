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
$height=$this->newSizes->height;
$width=$this->newSizes->width;
$link = hikashop_completeLink('product&task=show&cid='.$this->row->product_id.'&name='.$this->row->alias.$this->itemid.$this->category_pathway);?>
<table>
	<tr>
		<td valign="top">
			<div class="hikashop_product_item_left_part">
				<!-- PRODUCT IMG -->
				<?php if($this->config->get('thumbnail')){
				 ?>
				<div style="height:<?php echo $this->image->main_thumbnail_y;?>px;width:<?php echo $this->image->main_thumbnail_x;?>px;text-align:center;margin:auto" class="hikashop_product_image">
					<?php if($this->params->get('link_to_product_page',1)){ ?>
						<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->product_name); ?>">
					<?php }
						echo $this->image->display(@$this->row->file_path,false,$this->escape($this->row->file_name), '' , '' , $this->image->main_thumbnail_x,  $this->image->main_thumbnail_y);
					if($this->params->get('link_to_product_page',1)){ ?>
						</a>
					<?php } ?>
				</div>
				<!-- EO PRODUCT IMG -->
				<!-- PRODUCT PRICE -->
				<?php
				}
				if($this->params->get('show_price')){
					$this->setLayout('listing_price');
					echo $this->loadTemplate();
				}
				?>
				<!-- EO PRODUCT PRICE -->
				<?php if($this->params->get('add_to_cart')){
					?>
					<!-- ADD TO CART BUTTON -->
					<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->row->product_id.'_'.$this->params->get('main_div_name'); ?>"><?php
						$this->ajax='';
						if(!$this->config->get('ajax_add_to_cart',0)){
							$this->ajax = 'return hikashopModifyQuantity(\''.$this->row->product_id.'\',field,1,\'hikashop_product_form_'.$this->row->product_id.'_'.$this->params->get('main_div_name').'\');';
						}
						$this->setLayout('quantity');
						echo $this->loadTemplate();
						if(!empty($this->ajax) && $this->config->get('redirect_url_after_add_cart','stay_if_cart')=='ask_user'){ ?>
							<input type="hidden" name="popup" value="1"/>
						<?php } ?>
						<input type="hidden" name="product_id" value="<?php echo $this->row->product_id; ?>" />
						<input type="hidden" name="add" value="1"/>
						<input type="hidden" name="ctrl" value="product"/>
						<input type="hidden" name="task" value="updatecart"/>
						<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
					</form>
					<!-- EO ADD TO CART BUTTON --><?php
				}
				if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) {
					if( $this->params->get('show_compare') == 1 ) {
					?>
						<a class="hikashop_cart_button" href="<?php echo $link;?>" onclick="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this); return false;"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></a>
					<?php } else { ?>
						<input type="checkbox" class="hikashop_compare_checkbox" id="hikashop_listing_chk_<?php echo $this->row->product_id;?>" onchange="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this);"><label for="hikashop_listing_chk_<?php echo $this->row->product_id;?>"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
					<?php }
				} ?>
			</div>
		</td>
		<td valign="top">
			<div class="hikashop_product_item_right_part">
				<h2>
				<!-- PRODUCT NAME -->
					<span class="hikashop_product_name">
						<?php if($this->params->get('link_to_product_page',1)){ ?>
							<a href="<?php echo $link;?>">
								<?php
								echo $this->row->product_name;
								?>
							</a>
						<?php } ?>
					</span>
				<!-- EO PRODUCT NAME -->
				</h2>
				<!-- PRODUCT DESCRIPTION -->
				<div class="hikashop_product_desc" style="text-align:<?php echo $this->align; ?>">
					<?php
					echo preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->row->product_description);
					?>
				</div>
				<!-- EO PRODUCT DESCRIPTION -->
			</div>
		</td>
	</tr>
</table>