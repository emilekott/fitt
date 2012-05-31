<?php
 /**
  * @version   $Id: default.php 39490 2011-07-05 06:50:31Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
?>

<?php if ($that->show_page_heading): ?>
<h1><?php echo $that->page_heading; ?></h1>
<?php endif; ?>

<div class="rg-grid-view-container<?php echo $that->pageclass_sfx; ?>">
    <?php echo RokCommon_Composite::get($that->context)->load('header.php', array('that' => $that));?>
    <div class="rg-grid-view rg-col3">
        <?php
        foreach ($that->images as $that->image):
            $that->slice = $that->slices[$that->image->id];
            echo RokCommon_Composite::get($that->context)->load('default_row.php', array('that' => $that));
            $that->item_number++;
        endforeach;
        ?>
    </div>
</div>
<?php echo RokCommon_Composite::get($that->context)->load('pagination.php', array('that' => $that));?>