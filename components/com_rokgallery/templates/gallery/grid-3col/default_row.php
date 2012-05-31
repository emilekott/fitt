<?php
 /**
  * @version   $Id: default_row.php 39491 2011-07-05 07:26:40Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

$that->row_number = (int)($that->item_number/$that->items_per_row)+1;
$that->row_odd_even = ($that->row_number%2 == 0)?'even':'odd';
$that->first_item_in_row = (($that->item_number % $that->items_per_row) == (($that->items_per_row-($that->items_per_row-1))%$that->items_per_row))?true:false;
$that->last_item_in_row = (($that->item_number % $that->items_per_row) == 0) || ($that->total_items == $that->item_number) || ((int)$that->total_items == $that->item_number) ?true:false;
?>
<?php if ($that->first_item_in_row):?>
<div class="grid-row row<?php echo $that->row_number;?> <?php echo $that->row_odd_even;?>">
<?php endif; ?>
<?php echo RokCommon_Composite::get($that->context)->load('default_item.php', array('that'=>$that)); ?>
<?php if ($that->last_item_in_row): ?>
</div>
<?php endif;?>