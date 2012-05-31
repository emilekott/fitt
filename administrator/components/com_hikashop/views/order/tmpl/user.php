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
<fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button type="button" onclick="submitbutton('saveuser');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order',true); ?>" method="post" name="adminForm">
	<table width="100%" class="admintable">
		<tr>
			<td class="key">
				<label for="data[order][order_user_id]">
					<?php echo JText::_( 'HIKA_USER' ); ?>
				</label>
			</td>
			<td>
				<?php $type = hikashop_get('type.user');
				echo $type->display('data[order][order_user_id]',JRequest::getVar('user_id',0)); ?>
			</td>
		</tr>
		<?php $this->setLayout('notification'); echo $this->loadTemplate();?>
	</table>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>