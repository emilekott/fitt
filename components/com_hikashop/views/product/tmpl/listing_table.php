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
if(!empty($this->rows)){
	$pagination = $this->config->get('pagination','bottom');
	if(in_array($pagination,array('top','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total >$this->pageInfo->limit->value){ $this->pagination->form = '_top'; ?>
	<form action="<?php echo hikashop_completeLink(JRequest::getWord('ctrl').'&task='.JRequest::getWord('task').$this->itemid.'&cid='.reset($this->pageInfo->filter->cid)); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_top">
		<div class="hikashop_products_pagination">
		<?php echo $this->pagination->getListFooter(); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	<?php } ?>
	<div class="hikashop_products">
	<?php
		$columns = 1; ?>
		<table class="hikashop_products_table adminlist" cellpadding="1">
			<thead>
				<tr>
					<?php if($this->config->get('thumbnail')){ $columns++; ?>
						<th class="hikashop_product_image title" align="center">
							<?php echo JText::_( 'HIKA_IMAGE' );?>
						</th>
					<?php } ?>
					<th class="hikashop_product_name title" align="center">
						<?php echo JText::_( 'PRODUCT' );?>
					</th>
					<?php if($this->params->get('show_price')){ $columns++; ?>
						<th class="hikashop_product_price title" align="center">
							<?php echo JText::_('PRICE'); ?>
						</th>
					<?php } ?>
					<?php if($this->params->get('add_to_cart')){ $columns++; ?>
						<th class="hikashop_product_add_to_cart title" align="center">
						</th>
					<?php } ?>
					<?php if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) { $columns++; ?>
						<th class="hikashop_product_compare title" align="center">
						</th>
					<?php } ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $columns; ?>">
						<?php if(in_array($pagination,array('bottom','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total > $this->pageInfo->limit->value){ $this->pagination->form = '_bottom'; ?>
						<form action="<?php echo hikashop_completeLink(JRequest::getWord('ctrl').'&task='.JRequest::getWord('task').$this->itemid.'&cid='.reset($this->pageInfo->filter->cid)); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_bottom">
							<div class="hikashop_products_pagination">
							<?php echo $this->pagination->getListFooter(); ?>
							<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
							</div>
							<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
							<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
							<?php echo JHTML::_( 'form.token' ); ?>
						</form>
						<?php } ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach($this->rows as $row){
				$this->row =& $row;
				$link = hikashop_completeLink('product&task=show&cid='.$this->row->product_id.'&name='.$this->row->alias.$this->itemid.$this->category_pathway); ?>
				<tr>
					<?php if($this->config->get('thumbnail')){ ?>
						<td class="hikashop_product_image_row">
							<div style="height:<?php echo $this->image->main_thumbnail_y;?>px;text-align:center;clear:both;" class="hikashop_product_image">
								<?php if($this->params->get('link_to_product_page',1)){ ?>
									<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->product_name); ?>">
								<?php }
									echo $this->image->display(@$this->row->file_path,false,$this->escape($this->row->file_name));
								if($this->params->get('link_to_product_page',1)){ ?>
									</a>
								<?php } ?>
							</div>
						</td>
					<?php } ?>
					<td class="hikashop_product_name_row">
						<span class="hikashop_product_name">
							<?php if($this->params->get('link_to_product_page',1)){ ?>
								<a href="<?php echo $link;?>">
							<?php }
								echo $this->row->product_name;
							if($this->params->get('link_to_product_page',1)){ ?>
								</a>
							<?php } ?>
						</span>
					</td>
					<?php if($this->params->get('show_price')){ ?>
						<td class="hikashop_product_price_row">
						<?php
							$this->setLayout('listing_price');
							echo $this->loadTemplate();
						?>
						</td>
					<?php } ?>
					<?php if($this->params->get('add_to_cart')){ ?>
						<td class="hikashop_product_add_to_cart_row">
							<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->row->product_id.'_'.$this->params->get('main_div_name'); ?>"><?php
								$this->params->set('show_quantity_field', 2);
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
						</td>
					<?php } ?>
					<?php
					if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) {
						if( $this->params->get('show_compare') == 1 ) {
					?>
						<td class="hikashop_product_compare_row">
							<a class="hikashop_cart_button" href="<?php echo $link;?>" onclick="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this); return false;"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></a>
						</td>
					<?php } else { ?>
						<td class="hikashop_product_compare_row">
							<input type="checkbox" class="hikashop_compare_checkbox" id="hikashop_listing_chk_<?php echo $this->row->product_id;?>" onchange="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this);"><label for="hikashop_listing_chk_<?php echo $this->row->product_id;?>"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
						</td>
					<?php }
					} ?>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
<?php } ?>