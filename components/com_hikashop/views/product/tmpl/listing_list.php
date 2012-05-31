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
	if(in_array($pagination,array('top','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total > $this->pageInfo->limit->value){ $this->pagination->form = '_top'; ?>
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
		<ul class="hikashop_product_list<?php echo $this->params->get('ul_class_name'); ?>">
		<?php
			$columns = $this->params->get('columns');
			if(empty($columns)) $columns = 1;
			$width = (int)(100/$columns)-2;
			$current_column = 1;
			if(empty($width)){
				$width='style="float:left;"';
			}else{
				$width='style="float:left;width:'.$width.'%;"';
			}
			foreach($this->rows as $row){
				$link = hikashop_completeLink('product&task=show&cid='.$row->product_id.'&name='.$row->alias.$this->itemid.$this->category_pathway);
				?>
				<li class="hikashop_product_list_item" <?php echo $width; ?>>
					<?php if($this->params->get('link_to_product_page',0)){ ?>
						<a href="<?php echo $link; ?>" class="hikashop_product_name_in_list">
					<?php }
						echo $row->product_name;
						if($this->params->get('show_price')){
							$this->row =& $row;
							$this->setLayout('listing_price');
							echo '&nbsp;'.$this->loadTemplate();
						}
					if($this->params->get('link_to_product_page',1)){ ?>
						</a>
					<?php
					}
					if($this->params->get('add_to_cart') ){
						?><form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->row->product_id.'_'.$this->params->get('main_div_name'); ?>"><?php
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
						</form><?php
					}
					if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) {
						if( $this->params->get('show_compare') == 1 ) {
					?>
						<a class="hikashop_cart_button" href="<?php echo $link;?>" onclick="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this); return false;"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></a>
					<?php } else { ?>
						<input type="checkbox" class="hikashop_compare_checkbox" id="hikashop_listing_chk_<?php echo $this->row->product_id;?>" onchange="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this);"><label for="hikashop_listing_chk_<?php echo $this->row->product_id;?>"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
					<?php }
					} ?>
				</li>
				<?php if($current_column>=$columns){ ?>
				<?php
					$current_column=0;
				}
				$current_column++;
			}
		?>
		</ul>
	</div>
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
	<?php }
} ?>