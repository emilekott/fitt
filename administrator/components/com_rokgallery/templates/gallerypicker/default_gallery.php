<?php
/**
  * @version   $Id: default_file.php 39412 2011-07-03 18:34:26Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

$gallery = $that->gallery;
$count = 'total-slices-' . count($gallery->Slices);
?>

<li data-id="gallery-<?php echo $gallery->id; ?>" class="gallery <?php echo $count;?>">
    <div class="wrapper">
        <?php echo RokCommon_Composite::get('com_rokgallery.gallerypicker')->load('default_gallery_preview.php', array('that'=>$that)); ?>
        <div class="clr"></div>
    </div>
    <div class="gallery-title">
        <span><?php echo $gallery->name; ?></span>
    </div>
    <div class="clr"></div>
</li>
