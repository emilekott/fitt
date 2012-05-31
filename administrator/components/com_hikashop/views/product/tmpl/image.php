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
	<a class="modal" rel="{handler: 'iframe', size: {x: 560, y: 180}}" href="<?php echo hikashop_completeLink("product&task=selectimage&product_id=".@$this->element->product_id,true ); ?>">
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
								<?php echo JText::_('HIKA_IMAGE'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('HIKA_NAME'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('HIKA_DELETE'); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'ID' ); ?>
							</th>
						</tr>
					</thead>
					<tbody id="image_listing">
						<?php
							if(!empty($this->element->images)){
								$k = 0;
								for($i = 0,$a = count($this->element->images);$i<$a;$i++){
									$row =& $this->element->images[$i];
									$id=rand();
							?>
								<tr class="<?php echo "row$k"; ?>" id="image_<?php echo $row->file_id.'_'.$id;?>">
									<td>
										<a class="modal" rel="{handler: 'iframe', size: {x: 560, y: 180}}" href="<?php echo hikashop_completeLink("product&task=selectimage&cid=".$row->file_id."&product_id=".@$this->element->product_id.'&id='.$id,true ); ?>">
											<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
										</a>
									</td>
									<td>
										<?php echo $this->image->display($row->file_path,true,"",'','', 100, 100); ?>
									</td>
									<td>
										<?php echo $row->file_name; ?>
									</td>
									<td width="1%" align="center">
										<a href="#" onclick="return deleteRow('image_div_<?php echo $row->file_id.'_'.$id;?>','image[<?php echo $row->file_id;?>][<?php echo $id;?>]','image_<?php echo $row->file_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
									</td>
									<td width="1%" align="center">
										<?php echo $row->file_id; ?>
										<div id="image_div_<?php echo $row->file_id.'_'.$id;?>">
											<input type="hidden" name="image[<?php echo $row->file_id;?>]" id="image[<?php echo $row->file_id;?>][<?php echo $id;?>]" value="<?php echo $row->file_id;?>"/>
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