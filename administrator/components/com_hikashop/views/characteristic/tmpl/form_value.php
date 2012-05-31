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
	<a class="modal" rel="{handler: 'iframe', size: {x: 420, y: 240}}" href="<?php echo hikashop_completeLink("characteristic&task=editpopup&characteristic_parent_id=".@$this->element->characteristic_id,true ); ?>">
		<button type="button" onclick="return false">
			<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
		</button>
	</a>
</div>
<br/>
<table class="adminlist" cellpadding="1" width="100%">
	<thead>
		<tr>
			<th class="title titletoggle">
				<?php echo JText::_('HIKA_EDIT'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('VALUE'); ?>
			</th>
			<th class="title titletoggle">
				<?php echo JText::_('HIKA_DELETE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ID'); ?>
			</th>
		</tr>
	</thead>
	<tbody id="characteristic_listing">
		<?php
			if(!empty($this->element->values)){
				$k = 0;
				for($i = 0,$a = count($this->element->values);$i<$a;$i++){
					$row =& $this->element->values[$i];
					$id=rand();
					?>
					<tr id="characteristic_<?php echo $row->characteristic_id.'_'.$id;?>">
						<td>
							<a class="modal" rel="{handler: 'iframe', size: {x: 420, y: 240}}" href="<?php echo hikashop_completeLink("characteristic&task=editpopup&cid=".$row->characteristic_id.'&characteristic_parent_id='.$this->element->characteristic_id.'&id='.$id,true ); ?>" onclick="SqueezeBox.fromElement(this,{parse: 'rel'});return false;">
								<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
							</a>
						</td>
						<td>
							<?php echo $row->characteristic_value; ?>
						</td>
						<td align="center">
							<a href="#" onclick="return deleteRow('characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>','characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]','characteristic_<?php echo $row->characteristic_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
						</td>
						<td width="1%" align="center">
							<?php echo $row->characteristic_id; ?>
							<div id="characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>">
								<input type="hidden" name="characteristic[<?php echo $row->characteristic_id;?>]" id="characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]" value="<?php echo $row->characteristic_id;?>"/>
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
