<?php
defined('_JEXEC') or die('Restricted access');
?>
<fieldset>
  <div class="toolbar" id="toolbar" style="float: right;">
		<button type="button" onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_( 'PLEASE_SELECT_SOMETHING',true ); ?>');}else{submitbutton('product_add');}"><img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('OK'); ?></button>
  </div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<table width="100%">
	<tr>
		<?php if($this->config->get('category_explorer')){?>
			<td style="vertical-align:top;border:1px solid #CCC;background-color: #F3F3F3" width="200px">
				<?php echo hikashop_setExplorer('order&task=product_select',$this->pageInfo->filter->filter_id,true,'product'); ?>
			</td>
		<?php } ?>
		<td style="vertical-align:top;">
			<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=order" method="post" name="adminForm">
				<table>
					<tr>
						<td width="100%">
							<a href="<?php echo hikashop_completeLink('order&task=product_select&filter_id=0',true); ?>"><?php echo JText::_( 'ROOT' ); ?>/</a>
							<?php echo $this->breadCrumb.' '.JText::_( 'FILTER' ); ?>:
							<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
							<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
							<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
						</td>
						<td nowrap="nowrap">
							<?php echo $this->productType->display('filter_product_type',$this->pageInfo->filter->filter_product_type); ?>
							<?php echo $this->childDisplay; ?>
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
								<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'b.product_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort', JText::_('PRODUCT_CODE'), 'b.product_code', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
							</th>
							<th class="title">
								<?php echo JText::_('PRODUCT_PRICE'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('PRODUCT_QUANTITY'); ?>
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'b.product_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="7">
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
              ?>
							<tr class="<?php echo "row$k"; ?>">
								<td align="center">
								<?php echo $this->pagination->getRowOffset($i); ?>
								</td>
								<td align="center">
									<?php echo JHTML::_('grid.id', $i, $row->product_id ); ?>
								</td>
								<td>
									<?php echo $row->product_name; ?>
								</td>
								<td>
									<?php echo $row->product_code; ?>
								</td>
								<td>
									<?php echo $this->currencyHelper->displayPrices(@$row->prices); ?>
								</td>
				                <td>
				                  <input name="quantity[<?php echo $row->product_id ?>]" type="text" size="4" value="1"/>
				                </td>
								<td width="1%" align="center">
									<?php echo $row->product_id; ?>
								</td>
							</tr>
						<?php
								$k = 1-$k;
							}
						?>
					</tbody>
				</table>
        		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
				<input type="hidden" name="task" value="<?php echo JRequest::getCmd('task'); ?>" />
				<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="tmpl" value="component" />
				<input type="hidden" id="filter_id" name="filter_id" value="<?php echo $this->pageInfo->filter->filter_id; ?>" />
				<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
       			<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
		</td>
	</tr>
</table>
