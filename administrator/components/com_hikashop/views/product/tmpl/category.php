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
	<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectcategory",true ); ?>">
		<button type="button" onclick="return false">
			<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
		</button>
	</a>
</div>
<br/>
<table class="adminlist" cellpadding="1" width="100%">
	<thead>
		<tr>
			<th class="title">
				<?php echo JText::_('HIKA_NAME'); ?>
			</th>
			<th class="title titletoggle">
				<?php echo JText::_('HIKA_DELETE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ID'); ?>
			</th>
		</tr>
	</thead>
	<tbody id="category_listing">
		<?php
			if(!empty($this->element->categories)){
				$k = 0;
				for($i = 0,$a = count($this->element->categories);$i<$a;$i++){
					$row =& $this->element->categories[$i];
					if(!empty($row->category_id)){
					?>
						<tr id="category_<?php echo $row->category_id;?>">
							<td>
								<a href="<?php echo hikashop_completeLink('category&task=edit&cid='.$row->category_id); ?>"><?php echo $row->category_name; ?></a>
							</td>
							<td align="center">
								<a href="#" onclick="return deleteRow('category_div_<?php echo $row->category_id;?>','category[<?php echo $row->category_id;?>]','category_<?php echo $row->category_id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
							</td>
							<td width="1%" align="center">
								<?php echo $row->category_id; ?>
								<div id="category_div_<?php echo $row->category_id;?>">
									<input type="hidden" name="category[<?php echo $row->category_id;?>]" id="category[<?php echo $row->category_id;?>]" value="<?php echo $row->category_id;?>"/>
								</div>
							</td>
						</tr>
					<?php
					}
					$k = 1-$k; 
				}	
			}
		?>
	</tbody>
</table>
