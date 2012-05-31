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
<div>
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('HIKA_NAME'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_USERNAME'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_EMAIL'); ?>
				</th>
				<?php 
				if($this->widget->filter_partner==1){?>
				<th class="title">
					<?php echo JText::_('UNPAID_TOTAL_AMOUNT'); ?>
				</th>
				<?php }?>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				if(!empty($this->widget->elements)){
					for($i = 0,$a = count($this->widget->elements);$i<$a;$i++){
						$row =& $this->widget->elements[$i];
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo @$row->name; ?>
					</td>
					<td>
						<?php echo @$row->username; ?>
					</td>
					<td>
						<?php echo $row->user_email; ?>
						<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='.$row->user_id); ?>">
							<img src="<?php echo HIKASHOP_IMAGES;?>edit2.png" alt="edit"/>
						</a>
					</td>
					<?php 
					if($this->widget->filter_partner==1){?>
					<td align="center">
						<?php 
						if(bccomp($row->user_unpaid_amount,0,5)){
							echo $this->currencyHelper->format($row->user_unpaid_amount,$row->user_currency_id);
						}
						?>
					</td>
					<?php }?>
				</tr>
			<?php
					$k = 1-$k;
				}
			}
			?>
		</tbody>
	</table>
</div>