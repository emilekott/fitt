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
	<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=menus" method="post" name="adminForm">
		<table>
			<tr>
				<td width="100%">
					<?php echo JText::_( 'FILTER' ); ?>:
					<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
					<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
				</td>
			</tr>
		</table>
		<table class="adminlist" cellpadding="1">
			<thead>
				<tr>
					<th class="title titlenum">
						<?php echo JText::_( 'HIKA_NUM' );?>
					</th>
					<th class="title titlebox">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows); ?>);" />
					</th>
					<th class="title">
						<?php if(version_compare(JVERSION,'1.6','<')){
							echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
						}else{
							echo JHTML::_('grid.sort', JText::_('HIKA_TITLE'), 'title', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
						}?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('HIKA_ALIAS'), 'alias', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					</th>
					<th class="title">
						<?php echo JText::_('HIKA_TYPE'); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('LINK'), 'link', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titletoggle">
						<?php echo JText::_('HIKA_ENABLED'); ?>
					</th>
					<th class="title titleid">
						<?php echo JHTML::_('grid.sort', JText::_( 'ID' ), 'id', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$k = 0;
					$i = 0;
					foreach($this->rows as $row){
						$publishedid = 'published-'.$row->id;
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
						<?php echo $i+1 ?>
						</td>
						<td align="center">
							<?php echo JHTML::_('grid.id', $i, $row->id ); ?>
						</td>
						<td>
							<?php if($this->manage){ ?>
								<a href="<?php echo hikashop_completeLink('menus&task=edit&cid[]='.$row->id);?>">
							<?php } ?>
							<?php if(version_compare(JVERSION,'1.6','<')){
								echo $row->name;
							}else{
								echo $row->title;
							} ?>
							<?php if($this->manage){ ?>
							</a>
							<?php } ?>
						</td>
						<td>
							<?php echo $row->alias; ?>
						</td>
						<td>
							<?php echo $row->content_type; ?>
						</td>
						<td>
							<?php echo $row->link; ?>
						</td>
						<td align="center">
							<?php if($this->manage){ ?>
								<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid,$row->published,'menus') ?></span>
							<?php }else{ echo $this->toggleClass->display('activate',$row->published); } ?>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
						</td>
					</tr>
				<?php
						$k = 1-$k;
						$i++;
					}
				?>
			</tbody>
		</table>
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>