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
<?php if($this->config->get('thumbnail')){ ?>
<div style="height:<?php echo $this->image->main_thumbnail_y;?>px;text-align:center;clear:both;" class="hikashop_category_image">
	<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->category_name); ?>">
		<?php
		echo $this->image->display(@$this->row->file_path,false,$this->escape($this->row->file_name), '' , '' , $this->image->main_thumbnail_x,  $this->image->main_thumbnail_y);
		?>
	</a>
</div>
<?php } ?>
<br/>
<span class="hikashop_category_name">
	<a href="<?php echo $link;?>">
		<?php
		echo $this->row->category_name;
		?>
	</a>
</span>