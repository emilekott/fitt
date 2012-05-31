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
<div id="page-files">
	<table width="100%">
		<tr>
			<td valign="top">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'HIKA_FILES' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key" >
								<?php echo JText::_('ALLOWED_FILES'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[allowedfiles]" size="50" value="<?php echo strtolower(str_replace(' ','',$this->config->get('allowedfiles'))); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('UPLOAD_SECURE_FOLDER'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[uploadsecurefolder]" size="50" value="<?php echo $this->config->get('uploadsecurefolder'); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('UPLOAD_FOLDER'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[uploadfolder]" size="50" value="<?php echo $this->config->get('uploadfolder'); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('PAYMENT_LOG_FILE'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[payment_log_file]" size="50" value="<?php echo $this->config->get('payment_log_file'); ?>" />
								<a class="modal" href="<?php echo hikashop_completeLink('config&task=seepaymentreport',true); ?>" rel="{handler: 'iframe', size: {x: 800, y: 500}}"><button onclick="return false"><?php echo JText::_('REPORT_SEE'); ?></button></a>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ORDER_STATUS_FOR_DOWNLOAD'); ?>
							</td>
							<td>
								<input id="order_status_for_download" name="config[order_status_for_download]" value="<?php echo @$this->config->get('order_status_for_download'); ?>" />
								<a id="link_order_status_for_download" class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("category&task=selectstatus&control=order_status_for_download&values=".$this->config->get('order_status_for_download'),true ); ?>">
									<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
								</a>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('DOWNLOAD_TIME_LIMIT'); ?>
							</td>
							<td>
								<?php echo $this->delayTypeDownloads->display('config[download_time_limit]',$this->config->get('download_time_limit',0),3); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('DOWNLOAD_NUMBER_LIMIT'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[download_number_limit]" value="<?php echo $this->config->get('download_number_limit'); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('CSV_SEPARATOR'); ?>
							</td>
							<td>
								<?php echo $this->csvType->display('config[csv_separator]',$this->config->get('csv_separator',';')); ?>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
			<td valign="top">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'HIKA_IMAGES' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key" >
								<?php echo JText::_('ALLOWED_IMAGES'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[allowedimages]" size="50" value="<?php echo strtolower(str_replace(' ','',$this->config->get('allowedimages'))); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('DEFAULT_IMAGE'); ?>
							</td>
							<td>
								<span id="default_image">
									<?php $default_image = $this->config->get('default_image',''); echo $this->image->display($default_image); ?>
								<span class="spanloading"><?php //if(!empty($default_image)) echo $this->toggle->delete("default_image",'config_namekey-default_image','config',true); ?></span><br/></span>
								<input type="file" name="files[]" size="30" /><br/>
								<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
							</td>
						</tr>
						<tr>
							<td class="key" >
							<?php echo JText::_('THUMBNAIL'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', "config[thumbnail]" , '',$this->config->get('thumbnail') );?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('THUMBNAIL_X'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[thumbnail_x]" value="<?php echo $this->config->get('thumbnail_x'); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('THUMBNAIL_Y'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[thumbnail_y]" value="<?php echo $this->config->get('thumbnail_y'); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('IMAGE_X'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[image_x]" value="<?php echo $this->config->get('image_x'); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('IMAGE_Y'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[image_y]" value="<?php echo $this->config->get('image_y'); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('WATERMARK_ON_IMAGES'); ?>
							</td>
							<td>
								<?php if(hikashop_level(2)){ ?>
									<span id="watermark">
										<?php $watermark_image = $this->config->get('watermark',''); if(!empty($watermark_image))echo $this->image->display($watermark_image); ?>
									<span class="spanloading"><?php if(!empty($watermark_image)) echo $this->toggle->delete("watermark",'config_namekey-watermark','config',true); ?></span><br/></span>
									<input type="file" name="watermark[]" size="30" /><br/>
									<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize'));
									}else{
										echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
									}?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('WATERMARK_OPACITY'); ?>
							</td>
							<td>
								<?php if(hikashop_level(2)){ ?>
									<input class="inputbox" type="text" name="config[opacity]" value="<?php echo $this->config->get('opacity',0); ?>" size="3" />%
								<?php  }else{
									echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
								}?>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
	</table>
</div>