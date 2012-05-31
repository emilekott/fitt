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
<table class="admintable" cellspacing="1">
	<tr>
		<td class="key" >
			<?php echo JText::_('UPLOAD_FILE'); ?>
		</td>
		<td>
			<input type="file" size="50" name="importfile" />
			<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('CHARSET_FILE'); ?>
		</td>
		<td>
			<?php $charsetType = hikashop_get('type.charset'); array_unshift($charsetType->values,JHTML::_('select.option', JText::_('UNKNOWN'),'')); echo $charsetType->display('charsetconvert',JRequest::getString('charsetconvert','')); ?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('UPDATE_PRODUCTS'); ?>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'file_update_products','',JRequest::getInt('file_update_products','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('CREATE_CATEGORIES'); ?>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'file_create_categories','',JRequest::getInt('file_create_categories','1'));?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('FORCE_PUBLISH'); ?>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'file_force_publish','',JRequest::getInt('file_force_publish','1'));?>
		</td>
	</tr>
</table>
