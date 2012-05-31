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
		<table class="adminlist" cellpadding="1">
			<thead>
				<tr>
					<th class="title titlenum">
						<?php echo JText::_( 'HIKA_NUM' );?>
					</th>
					<th class="title">
						<?php echo JText::_('HIKA_NAME'); ?>
					</th>
					<th class="title titletoggle">
						<?php echo JText::_('HIKA_ENABLED'); ?>
					</th>
					<th class="title titleid">
						<?php echo JText::_( 'ID' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$k = 0;
					for($i = 0,$a = count($this->plugins);$i<$a;$i++){
						$row =& $this->plugins[$i];
						if(version_compare(JVERSION,'1.6','<')){
							$publishedid = 'published-'.$row->id;
						}else{
							$publishedid = 'enabled-'.$row->id;
						}
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
						<?php echo $i+1 ?>
						</td>
						<td>
							<?php if($this->manage){ ?>
								<a href="<?php echo hikashop_completeLink('plugins&task=edit&name='.$row->element.'&plugin_type='.$this->plugin_type);?>">
							<?php } ?>
									<?php echo $row->name; ?>
							<?php if($this->manage){ ?>
								</a>
							<?php } ?>
						</td>
						<td align="center">
							<span id="<?php echo $publishedid ?>" class="loading">
								<?php if($this->manage){ ?>
									<?php echo $this->toggleClass->toggle($publishedid,$row->published,'plugins'); ?>
								<?php }else{ echo $this->toggleClass->display('activate',$row->published); } ?>
							</span>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
						</td>
					</tr>
				<?php
						$k = 1-$k;
					}
				?>
			</tbody>
		</table>