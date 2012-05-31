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
		<button type="button" onclick="submitbutton('addfile');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=product" method="post" name="adminForm" enctype="multipart/form-data">
	<table width="100%">
		<tr>
			<td class="key">
				<label for="file_name">
					<?php echo JText::_( 'HIKA_NAME' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[file][file_name]" value="<?php echo $this->escape(@$this->element->file_name); ?>"/>
			</td>
		</tr>
		<tr>
			<?php
				if(empty($this->element->file_path)){
			?>
					<td class="key">
						<label for="files">
							<?php echo JText::_( 'HIKA_FILE' ); ?>
						</label>
					</td>
					<td>
						<input type="file" name="files[]" size="30" />
						<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
					</td>
			<?php
				}else{
			?>
					<td class="key">
						<label for="files">
							<?php echo JText::_( 'FILENAME' ); ?>
						</label>
					</td>
					<td>
						<?php echo $this->element->file_path;?>
					</td>
			<?php
				}
			?>
		</tr>
		<tr>
			<td class="key">
				<label for="file_free_download">
					<?php echo JText::_( 'FREE_DOWNLOAD' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "data[file][file_free_download]" , '',@$this->element->file_free_download	); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="file_description">
					<?php echo JText::_( 'HIKA_DESCRIPTION' ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->editor->display();?>
			</td>
		</tr>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="data[file][file_type]" value="file" />
	<input type="hidden" name="data[file][file_ref_id]" value="<?php echo JRequest::getInt('product_id'); ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->cid; ?>" />
	<input type="hidden" name="id" value="<?php echo JRequest::getInt('id');?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="selectfile" />
	<input type="hidden" name="ctrl" value="product" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>