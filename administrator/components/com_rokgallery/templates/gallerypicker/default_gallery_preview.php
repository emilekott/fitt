<?php
/**
  * @version   $Id: default_file.php 39412 2011-07-03 18:34:26Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

$gallery = $that->gallery;
$slices = $gallery->Slices;
$slices_count = count($slices);

// create 3 random miniatures
$slices = $slices->getData();
shuffle($slices);
$slices = array_slice($slices, 0, 3);
$count = 1;

?>

<?php if (!$slices_count):?>
	<div class="empty-gallery"></div>

<?php else: ?>

<?php 
	foreach($slices as $slice):
?>
	
	<img src="<?php echo $slice->miniadminthumburl;?>" class="img-<?php echo $count; ?> img-tot-<?php echo count($slices); ?>" width="<?php echo (RokGallery_Config::DEFAULT_MINI_ADMIN_THUMB_XSIZE); ?>" height="<?php echo (RokGallery_Config::DEFAULT_MINI_ADMIN_THUMB_YSIZE); ?>" alt="" />

<?php $count++; endforeach; ?>
<?php endif; ?>

<div class="clr"></div>
<div class="badge-count"><span><?php echo $slices_count; ?></span></div>
