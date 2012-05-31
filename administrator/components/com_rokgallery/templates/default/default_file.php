<?php
/**
  * @version   $Id: default_file.php 39412 2011-07-03 18:34:26Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

$file = $that->file;
$publish = !$file->published ? 'publish' : 'unpublish';
$published = $file->published ? 'published' : 'unpublished';

?>
<div class="gallery-block-wrapper">
    <div class="gallery-block"  id="file-<?php echo $file->id;?>" >
        <div class="front-view">
            <div class="indicator-<?php echo $published; ?>"></div>
            <div class="gallery-thumb">
				<div class="gallery-thumb-wrapper">
					<div class="gallery-thumb-wrapper-transparency">
	                	<img src="<?php echo $file->adminthumburl ;?>" width="300" height="180" />
	                	<div class="info-switcher"><span>i</span></div>
					</div>
				</div>
            </div>
            <div class="gallery-data">
                <div class="views"><?php echo number_format($file->Views->count);?></div>
                <div class="loves"><?php echo number_format($file->Loves->count);?></div>
                <div class="tags"><?php echo number_format($file->Tags->count());?> </div>
            </div>
            <div class="image-actions">
                    <div class="image-<?php echo $publish; ?>" title="<?php echo $publish; ?>"><span><?php echo $publish; ?></span></div>
                    <div class="image-edit" title="edit"><span><?php rc_e('ROKGALLERY_EDIT');?></span></div>
                    <div class="image-delete" title="delete"><span><?php rc_e('ROKGALLERY_DELETE');?></span></div>
            </div>
            <div class="clr"></div>
        </div>

        <div class="back-view">
            <div class="gallery-description">
                <h1 class="image-title"><?php echo $file->title;?></h1>
                <div class="image-pixels"><strong><?php echo $file->xsize;?></strong>x<strong><?php echo $file->ysize;?></strong> (<?php echo $file->xsize*$file->ysize;?>  pixels)  / <strong><?php echo RokGallery_Helper::decodeSize($file->filesize);?></strong></div>
                <div class="image-description"><?php echo $file->description; ?></div>
                <div class="image-statusbar">
                    <div class="image-date"><?php echo date('j M Y (H:i)', strtotime($file->created_at));?></div>
                    <div class="image-close dark button"><span><?php rc_e('ROKGALLERY_CLOSE');?></span></div>
                </div>
                <div class="image-actions">
                        <div class="image-<?php echo $publish; ?>" title="<?php echo $publish; ?>"><span><?php echo $publish; ?></span></div>
                        <div class="image-edit" title="edit"><span><?php rc_e('ROKGALLERY_EDIT');?></span></div>
                        <div class="image-delete" title="delete"><span><?php rc_e('ROKGALLERY_DELETE');?></span></div>
                </div>
            </div>

            <div class="clr"></div>
        </div>
    </div>
</div>
 
