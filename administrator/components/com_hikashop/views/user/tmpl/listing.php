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
<form action="<?php echo hikashop_completeLink('user'); ?>" method="post" name="adminForm">
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
				}?>
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
					<?php echo JText::_('HIKA_EDIT'); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'b.name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_USERNAME'), 'b.username', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_EMAIL'), 'a.user_email', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php
				if(!empty($this->fields)){
					foreach($this->fields as $field){
						echo '<th class="title">'.JHTML::_('grid.sort', $this->fieldsClass->trans($field->field_realname), 'a.'.$field->field_namekey, $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ).'</th>';
					}
				}
				if($this->pageInfo->filter->filter_partner==1){?>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('TOTAL_UNPAID_AMOUNT'), 'a.user_unpaid_amount', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php }?>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.user_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php
				$count = 7+count($this->fields);
				if($this->pageInfo->filter->filter_partner==1){
					$count++;
				}
				echo $count;
				?>">
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
					<?php echo $this->pagination->getRowOffset($i);
					?>
					</td>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->user_id ); ?>
					</td>
					<td align="center">
						<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='.$row->user_id); ?>">
								<img src="<?php echo HIKASHOP_IMAGES;?>edit.png" alt="edit"/>
							</a>
						<?php } ?>
					</td>
					<td>
						<?php echo @$row->name; ?>
					</td>
					<td>
						<?php echo @$row->username; ?>
					</td>
					<td>
						<?php echo $row->user_email; ?>
					</td>
					<?php
					if(!empty($this->fields)){
						foreach($this->fields as $field){
							$namekey = $field->field_namekey;
							echo '<td>'.$this->fieldsClass->show($field,$row->$namekey).'</td>';
						}
					}
					if($this->pageInfo->filter->filter_partner==1){?>
					<td align="center">
						<?php
						if(bccomp($row->user_unpaid_amount,0,5)){
							$config =& hikashop_config();
							if(!$config->get('allow_currency_selection',0) || empty($row->user_currency_id)){
								$row->user_currency_id =  $config->get('partner_currency',1);
							}
							echo $this->currencyHelper->format($row->user_unpaid_amount,$row->user_currency_id);
						}
						?>
					</td>
					<?php }?>
					<td width="1%" align="center">
						<?php echo $row->user_id; ?>
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