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
<form action="<?php echo hikashop_completeLink('tax');?>" method="post" name="adminForm">
	<center>
	<table class="admintable">
		<tr>
			<td class="key">
				<label for="data[tax][tax_namekey]">
					<?php echo JText::_( 'TAX_NAMEKEY' ); ?>
				</label>
			</td>
			<td>
				<?php if(empty($this->element->tax_namekey)){?>
					<input type="text" name="data[tax][tax_namekey]" value="" />
				<?php }else{
					echo $this->element->tax_namekey;
					?><input type="hidden" name="data[tax][tax_namekey]" value="<?php echo $this->escape($this->element->tax_namekey ); ?>" /><?php 
				}?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[tax][tax_rate]">
					<?php echo JText::_( 'RATE' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[tax][tax_rate]" value="<?php echo $this->escape(@$this->element->tax_rate*100.0 ); ?>" />%
			</td>
		</tr>
	</table>
	</center>
	<div class="clr"></div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT;?>" />
	<input type="hidden" name="return" value="<?php echo $this->return;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getString('ctrl');?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>