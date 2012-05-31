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
<?php $link = hikashop_completeLink('category&task=listing&cid='.$this->row->category_id.'&name='.$this->row->alias.$this->menu_id);?>
<table>
	<tr>
		<!-- CATEGORY IMG -->
		<?php if($this->config->get('thumbnail')){ ?>
		<td width="<?php echo $this->image->main_thumbnail_x+30;?>px">
			<div class="hikashop_category_left_part" style="text-align:center;">
				<div style="height:<?php echo $this->image->main_thumbnail_y;?>px;width:<?php echo $this->image->main_thumbnail_x;?>px;text-align:center;margin:auto" class="hikashop_product_image">
					<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->category_name); ?>">
						<?php
						echo $this->image->display(@$this->row->file_path,false,$this->escape($this->row->file_name), '' , '' , $this->image->main_thumbnail_x,  $this->image->main_thumbnail_y);
						?>
					</a>
				</div>
			</div>
		</td>
		<?php } ?>
		<!--EO CATEGORY IMG -->
		<td valign="top">
			<div class="hikashop_category_right_part">
				<h2>
					<!-- CATEGORY NAME -->
					<span class="hikashop_category_name">
							<a href="<?php echo $link;?>">
								<?php
								echo $this->row->category_name;
								?>
							</a>
					</span>
					<!-- EO CATEGORY NAME -->
				</h2>
				<!-- CATEGORY DESC -->
				<span class="hikashop_category_desc" style="text-align:<?php echo $this->align; ?>;">
					<?php
					echo preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->row->category_description);
					?>
				</span>
				<!-- EO CATEGORY DESC -->
			</div>
		</td>
	</tr>
</table>