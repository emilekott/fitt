<?php
/**
  * @version   $Id: default_file.php 39412 2011-07-03 18:34:26Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

$file = $that->file;
$slices_count = count($file->Slices);
$publish = !$file->published ? 'publish' : 'unpublish';
$published = $file->published ? 'published' : 'unpublished';

?>

<li data-id="file-<?php echo $file->id; ?>" class="file file-<?php echo $published; ?> total-slices-<?php echo $slices_count; ?>">
    <div class="badge-count"><span><?php echo $slices_count; ?></span></div>
    <div class="wrapper">
        <img src="<?php echo $file->miniadminthumburl; ?>" alt="" width="<?php echo (RokGallery_Config::DEFAULT_MINI_ADMIN_THUMB_XSIZE); ?>" height="<?php echo (RokGallery_Config::DEFAULT_MINI_ADMIN_THUMB_YSIZE); ?>" />
    </div>
</li>

