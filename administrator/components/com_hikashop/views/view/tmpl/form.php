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
<form action="<?php echo hikashop_completeLink('view');?>" method="post" name="adminForm">
	<?php if($this->ftp){ ?>
	<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
		<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>
		<?php echo JText::_('DESCFTP'); ?>
		<?php if(JError::isError($this->ftp)){ ?>
			<p><?php echo JText::_($this->ftp->message); ?></p>
		<?php } ?>
		<table class="adminform nospace">
		<tbody>
		<tr>
			<td width="120">
				<label for="username"><?php echo JText::_('HIKA_USERNAME'); ?>:</label>
			</td>
			<td>
				<input type="text" id="username" name="username" class="input_box" size="70" value="" />
			</td>
		</tr>
		<tr>
			<td width="120">
				<label for="password"><?php echo JText::_('HIKA_PASSWORD'); ?>:</label>
			</td>
			<td>
				<input type="password" id="password" name="password" class="input_box" size="70" value="" />
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>
	<?php } ?>
	<table class="adminform">
	<tr>
		<th>
			<?php echo $this->element->view .' / '. $this->element->filename;?>
		</th>
	</tr>
	<tr>
		<td>
			<?php echo $this->editor->displayCode('filecontent',$this->element->content); ?>
		</td>
	</tr>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="id" value="<?php echo $this->element->id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getString('ctrl');?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>