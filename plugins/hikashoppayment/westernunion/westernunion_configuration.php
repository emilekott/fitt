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
<tr>
	<td class="key">
		<label for="data[payment][payment_params][order_status]">
			<?php echo JText::_( 'ORDER_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][order_status]",@$this->element->payment_params->order_status); ?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<table class="adminform" style="min-width:360px">
			<tbody>
				<tr>
					<th>
						<label for="bank_account_information">
							<?php echo JText::_( 'BANK_ACCOUNT_INFORMATION' ); ?>
						</label>
					</th>
				</tr>
				<tr>
					<td>
						<textarea rows="20" style="width:100%" name="bank_account_information"><?php echo @$this->element->payment_params->information;?></textarea>						
					</td>
				</tr>
			</tbody>
		</table>
	</td>
</tr>