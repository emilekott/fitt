<?php
/**
  * @version   $Id: default_row.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
?>
<?php if ($that->row_entry_number % $that->items_per_row == 0): ?>
    <div class="gallery-row">
<?php endif; ?>
<?php echo RokCommon_Composite::get('com_rokgallery.default')->load('default_file.php', array('that'=>$that)); ?>
<?php if ($that->row_entry_number % $that->items_per_row == (($that->items_per_row-1)%$that->items_per_row) || ($that->item_number == $that->items_to_be_rendered)): ?>
    <div class="clr"></div>
		</div>
<?php endif;?>
