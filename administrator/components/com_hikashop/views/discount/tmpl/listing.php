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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=discount" method="post" name="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php echo $this->filter_type->display('filter_type',$this->pageInfo->filter->filter_type); ?>
			</td>
		</tr>
	</table>
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows); ?>);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('DISCOUNT_CODE'), 'a.discount_code', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('DISCOUNT_TYPE'), 'a.discount_type', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('DISCOUNT_START_DATE'), 'a.discount_start', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('DISCOUNT_END_DATE'), 'a.discount_end', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JText::_('DISCOUNT_VALUE'); ?>
				</th>
				<?php if(hikashop_level(1)){ ?>
					<th class="title">
						<?php echo JText::_('DISCOUNT_QUOTA'); ?>
					</th>
					<th class="title">
						<?php echo JText::_('RESTRICTIONS'); ?>
					</th>
				<?php } ?>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.discount_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.discount_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
					$publishedid = 'discount_published-'.$row->discount_id;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->discount_id ); ?>
					</td>
					<td>
						<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('discount&task=edit&cid[]='.$row->discount_id); ?>">
						<?php } ?>
								<?php echo $row->discount_code; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php } ?>
					</td>
					<td>
						<?php echo $row->discount_type; ?>
					</td>
					<td align="center">
						<?php echo hikashop_getDate($row->discount_start); ?>
					</td>
					<td align="center">
						<?php echo hikashop_getDate($row->discount_end); ?>
					</td>
					<td align="center">
						<?php
							if(isset($row->discount_flat_amount) && $row->discount_flat_amount > 0){
								echo $this->currencyHelper->displayPrices(array($row),'discount_flat_amount','discount_currency_id');
							}
							elseif(isset($row->discount_percent_amount) && $row->discount_percent_amount > 0){
								echo $row->discount_percent_amount. '%';
							}
						?>
					</td>
					<?php if(hikashop_level(1)){ ?>
						<td align="center">
							<?php
								if(empty($row->discount_quota)){
									echo JText::_('UNLIMITED');
								}else{
									echo $row->discount_quota. ' ('.JText::sprintf('X_LEFT',$row->discount_quota-$row->discount_used_times).')';
								}
							?>
						</td>
						<td>
							<?php
								$restrictions=array();
								if(!empty($row->discount_minimum_order)){
									$restrictions[]=JText::_('MINIMUM_ORDER_VALUE').':'.$this->currencyHelper->displayPrices(array($row),'discount_minimum_order','discount_currency_id');
								}
								if(!empty($row->product_name)){
									$restrictions[]=JText::_('PRODUCT').':'.$row->product_name;
								}
								if(!empty($row->category_name)){
									$restriction=JText::_('CATEGORY').':'.$row->category_name;
									if($row->discount_category_childs){
										$restriction.=' '.JText::_('INCLUDING_SUB_CATEGORIES');
									}
									$restrictions[]=$restriction;
								}
								if(!empty($row->zone_name_english)){
									$restrictions[]=JText::_('ZONE').':'.$row->zone_name_english;
								}
								if(!empty($row->username)){
									$restrictions[]=JText::_('HIKA_USER').':'.$row->username;
								}
                if ($row->discount_type == 'coupon') {
  								if (!empty($row->discount_coupon_product_only)) {
 									  $restrictions[]='Percentage for product only';
								  }
  								switch($row->discount_coupon_nodoubling) {
  								  case 1:
  									  $restrictions[]='Ignore discounted products';
                      break;
  								  case 2:
  									  $restrictions[]='Override discounted products';
                      break;
                    default:
                      break;
  								}
                }
								echo implode('<br/>',$restrictions);
							?>
						</td>
					<?php } ?>
					<td align="center">
						<?php if($this->manage){ ?>
							<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->discount_published,'discount') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->discount_published); } ?>
					</td>
					<td width="1%" align="center">
						<?php echo $row->discount_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>