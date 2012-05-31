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
<?php
?>
<?php
$height=$this->newSizes->height;
$width=$this->newSizes->width;
$duration=$this->params->get('product_effect_duration');
if(empty($duration)){ $duration=400; }
$pane_percent_height=$this->params->get('pane_height');
$link = hikashop_completeLink('category&task=listing&cid='.$this->row->category_id.'&name='.$this->row->alias.$this->menu_id);
$htmlLink="";
$cursor="";
if($this->params->get('link_to_product_page',1)){
	$htmlLink='onclick = "window.location.href = \''.$link.'\'';
	$cursor="cursor:pointer;";
}
?>
 <div id="window_<?php echo $this->row->category_id;  ?>" style="margin: auto; <?php echo $cursor; ?> height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; overflow:hidden; position:relative" <?php echo $htmlLink; ?>" >
 	<div id="product_<?php echo $this->row->category_id;  ?>" style="height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; " >
		<!-- CATEGORY IMG -->
			<div style="height:<?php echo $this->image->main_thumbnail_y;?>px;width:<?php echo $this->image->main_thumbnail_x;?>px;text-align:center;margin:auto" class="hikashop_product_image">
				<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->category_name); ?>">
					<?php
					echo $this->image->display(@$this->row->file_path,false,$this->escape($this->row->file_name), '' , '' , $this->image->main_thumbnail_x,  $this->image->main_thumbnail_y);
					?>
				</a>
			</div>
		<!-- EO CATEGORY IMG -->
		<?php
			$paneHeight='';
			if(!empty($pane_percent_height)){
				 $paneHeight='height:'.$pane_percent_height.'px;';
			}
		?>
		<div class="hikashop_img_pane_panel" style="width:<?php echo $width; ?>px; <?php echo $paneHeight; ?>">
			<!-- CATEGORY NAME -->
				<span class="hikashop_category_name">
					<a href="<?php echo $link;?>">
						<?php
						echo $this->row->category_name;
						?>
					</a>
				</span>
			<!-- EO CATEGORY NAME -->
		</div>
	</div>
</div>