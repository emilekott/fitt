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
<div style="float:right">
	<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectfile&product_id=".@$this->element->product_id,true ); ?>">
		<button type="button" onclick="return false">
			<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
		</button>
	</a>
</div>
<br/>
				<table class="adminlist" cellpadding="1">
					<thead>
						<tr>
							<th class="title">
								<?php echo JText::_('HIKA_EDIT'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('FILENAME'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('HIKA_NAME'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('DOWNLOADS'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('FREE_DOWNLOAD'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('HIKA_DELETE'); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'ID' ); ?>
							</th>
						</tr>
					</thead>
					<tbody id="file_listing">
						<?php
							if(!empty($this->element->files)){
								$k = 0;
								foreach($this->element->files as $row){
									$id=rand();
							?>
								<tr class="<?php echo "row$k"; ?>" id="file_<?php echo $row->file_id.'_'.$id;?>">
									<td>
										<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectfile&cid=".$row->file_id."&product_id=".@$this->element->product_id.'&id='.$id,true ); ?>">
											<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
										</a>
									</td>
									<td>
										<?php echo $row->file_path; ?>
									</td>
									<td>
										<?php echo $row->file_name; ?>
									</td>
									<td>
										<?php
											echo (int)@$row->download_number;
											if(@$row->download_number){
												echo ' <a href="'.hikashop_completeLink('file&task=resetdownload&file_id='.$row->file_id.'&'.JUtility::getToken().'=1&return='.urlencode(base64_encode(hikashop_completeLink('product&task=edit&cid='.@$this->element->product_id,false,true)))).'"><img src="'.HIKASHOP_IMAGES.'delete.png" alt="'.JText::_('HIKA_DELETE').'" /></a>';
											}
										?>
									</td>
									<td>
					      				<input type="checkbox" disabled="disabled" <?php echo !empty($row->file_free_download) ? 'checked="checked"' : ''; ?> />
									</td>
									<td width="1%" align="center">
										<a href="#" onclick="return deleteRow('file_div_<?php echo $row->file_id.'_'.$id;?>','file[<?php echo $row->file_id;?>][<?php echo $id;?>]','file_<?php echo $row->file_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
									</td>
									<td width="1%" align="center">
										<?php echo $row->file_id; ?>
										<div id="file_div_<?php echo $row->file_id.'_'.$id;?>">
											<input type="hidden" name="file[<?php echo $row->file_id;?>]" id="file[<?php echo $row->file_id;?>][<?php echo $id;?>]" value="<?php echo $row->file_id;?>"/>
										</div>
									</td>
								</tr>
							<?php
									$k = 1-$k;
								}
							}
						?>
					</tbody>
				</table>