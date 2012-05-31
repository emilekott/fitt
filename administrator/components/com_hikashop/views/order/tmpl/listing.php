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
<form action="<?php echo hikashop_completeLink('order'); ?>" method="post" name="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php if($this->affiliate_active){
					echo $this->partner->display("filter_partner",$this->pageInfo->filter->filter_partner,false);
				}
				echo $this->payment->display("filter_payment",$this->pageInfo->filter->filter_payment,false);
				echo $this->category->display("filter_status",$this->pageInfo->filter->filter_status,false); ?>
			</td>
		</tr>
	</table>
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="hikashop_order_num_title title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="hikashop_order_select_title title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows); ?>);" />
				</th>
				<th class="hikashop_order_number_title title">
					<?php echo JHTML::_('grid.sort', JText::_('ORDER_NUMBER'), 'b.order_number', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_customer_title title">
					<?php echo JHTML::_('grid.sort', JText::_('CUSTOMER'), 'c.name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_payment_title title">
					<?php echo JHTML::_('grid.sort', JText::_('PAYMENT_METHOD'), 'b.order_payment_method', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_date_title title">
					<?php echo JHTML::_('grid.sort', JText::_('DATE'), 'b.order_created', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_modified_title title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_LAST_MODIFIED'), 'b.order_modified', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_status_title title">
					<?php echo JHTML::_('grid.sort',   JText::_('ORDER_STATUS'), 'b.order_status', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_total_title title">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKASHOP_TOTAL'), 'b.order_full_price', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<?php $count_fields=0;
				if(hikashop_level(2) && !empty($this->fields)){
					foreach($this->fields as $field){
						$count_fields++;
						echo '<th class="hikashop_order_'.$field->field_namekey.'_title title">'.JHTML::_('grid.sort', $this->fieldsClass->trans($field->field_realname), 'b.'.$field->field_namekey, $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ).'</th>';
					}
				}
					if($this->affiliate_active){?>
				<th class="hikashop_order_partner_title title">
					<?php echo JText::_('PARTNER'); ?>
				</th>
				<?php }?>
				<th class="hikashop_order_id_title title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'b.order_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php if($this->affiliate_active){echo 11+$count_fields;}else{echo 10+$count_fields;}?>">
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
					<td class="hikashop_order_num_value">
					<?php echo $this->pagination->getRowOffset($i);
					?>
					</td>
					<td class="hikashop_order_select_value">
						<?php echo JHTML::_('grid.id', $i, $row->order_id ); ?>
					</td>
					<td class="hikashop_order_number_value">
						<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('order&task=edit&cid[]='.$row->order_id.'&cancel_redirect='.urlencode(base64_encode(hikashop_completeLink('order')))); ?>">
						<?php } ?>
								<?php echo $row->order_number; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php } ?>
					</td>
					<td class="hikashop_order_customer_value">
						<?php
						 if(!empty($row->username)){
						 	echo $row->name.' ( '.$row->username.' )</a><br/>';
						 }
						 $url = hikashop_completeLink('user&task=edit&cid[]='.$row->user_id);
						 $config =& hikashop_config();
						 if(hikashop_isAllowed($config->get('acl_user_manage','all'))) echo $row->user_email.'<a href="'.$url.'"><img src="'.HIKASHOP_IMAGES.'edit2.png" alt="edit"/></a>';
						 ?>
					</td>
					<td class="hikashop_order_payment_value">
						<?php if(!empty($row->order_payment_method)){
							if(!empty($this->payments[$row->order_payment_method])){
								echo $this->payments[$row->order_payment_method]->payment_name;
							}else{
								echo $row->order_payment_method;
							}
						} ?>
					</td>
					<td class="hikashop_order_date_value">
						<?php echo hikashop_getDate($row->order_created,'%Y-%m-%d %H:%M');?>
					</td>
					<td class="hikashop_order_modified_value">
						<?php echo hikashop_getDate($row->order_modified,'%Y-%m-%d %H:%M');?>
					</td>
					<td class="hikashop_order_status_value">
						<?php if($this->manage){ ?>
							<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('order&task=changestatus&order_id='.$row->order_id,true);?>" id="status_change_link_<?php echo $row->order_id;?>"></a>
							<?php
								$onchange = ' onfocus="this.oldvalue = this.value;" onchange="var link = document.getElementById(\'status_change_link_'.$row->order_id.'\');link.href = link.href+\'&status=\' +this.value; this.value=this.oldvalue; SqueezeBox.fromElement(link,{parse: \'rel\'});"';
							}
							echo $this->category->display("filter_status_".$row->order_id,$row->order_status,$onchange);
						?>
					</td>
					<td class="hikashop_order_total_value">
						<?php echo $this->currencyHelper->format($row->order_full_price,$row->order_currency_id);?>
					</td>
					<?php
					if(hikashop_level(2) && !empty($this->fields)){
						foreach($this->fields as $field){
							$namekey = $field->field_namekey;
							echo '<td class="hikashop_order_'.$namekey.'_value">'.$this->fieldsClass->show($field,$row->$namekey).'</td>';
						}
					}
					if($this->affiliate_active){?>
					<td class="hikashop_order_partner_value">
						<?php
						if(bccomp($row->order_partner_price,0,5)){
							echo $this->currencyHelper->format($row->order_partner_price,$row->order_partner_currency_id);
							if(empty($row->order_partner_paid)){
								echo JText::_('NOT_PAID').'<img src="'.HIKASHOP_IMAGES.'delete2.png" />';
							}else{
								echo JText::_('PAID').'<img src="'.HIKASHOP_IMAGES.'ok.png" />';
							}
						}
						?>
					</td>
					<?php }?>
					<td class="hikashop_order_id_value">
						<?php echo $row->order_id; ?>
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