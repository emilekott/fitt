<?php
 /**
  * @version   $Id: default.php 39672 2011-07-07 09:54:40Z kevin $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
?>
<?php if ($that->gallery_name):?><span class="rg-detail-gallery-name"><?php rc_e('ROKGALLERY_DETAIL_BACK_TO_GALLERY');?>: <a href="<?php echo $that->gallery_link;?>"><?php echo $that->gallery_name;?></a></span><?php endif;?>
<div class="rg-detail-header">
	<?php if ($that->show_title):?><span class="rg-detail-item-title"><?php echo $that->image->title;?></span><?php endif;?>
	<?php if ($that->show_created_at):?><span class="rg-detail-creation-date"><?php echo $that->image->created_at;?></span><?php endif;?>
</div>
<div class="rg-detail-img-container">
	<div class="rg-detail-img-bg">
		<img src="<?php echo $that->image->imageurl;?>" alt="<?php echo $that->image->title;?>" class="rg-detail-img" />
		<?php if ($that->show_caption):?><span class="rg-detail-item-caption"><?php echo $that->image->caption;?></span><?php endif;?>
		<?php if ($that->show_tags):?>
		<div class="rg-detail-slicetag">
			<?php foreach ($that->image->tags as $tag): ?>
			<span class="tag"><?php echo $tag;?></span>
			<?php endforeach; ?>
		</div>
		<?php endif;?>
	</div>
</div>
<div class="rg-detail-info-table-container">
	<div class="rg-detail-info-table">
		<div class="rg-detail-info-container">
			<div class="rg-detail-file-main">
				<?php if ($that->show_title):?><span class="rg-detail-item-title rg-detail-item-file-title"><?php echo $that->image->title;?></span><?php endif;?>
				<?php if ($that->gallery_name):?><span class="rg-detail-gallery-name"><?php rc_e('ROKGALLERY_DETAIL_GALLERY_NAME');?>: <a href="<?php echo $that->gallery_link;?>"><?php echo $that->gallery_name;?></a></span><?php endif;?>
				<span class="rg-detail-item-file-desc"><span><?php rc_e('ROKGALLERY_DETAIL_FILE_DESC');?>: </span><?php echo $that->image->caption;?></span>
				<?php if (($that->show_views) or ($that->show_loves) or ($that->show_tags_count)):?>
				<div class="rg-detail-item-views-icons">
					<?php if ($that->show_views):?><span class="rg-detail-item-views-count"><span><?php rc_e('ROKGALLERY_DETAIL_VIEWS_COUNT');?>: </span><strong><?php echo $that->image->views;?></strong></span><?php endif;?>
					<?php if ($that->show_loves):?><span class="rg-detail-item-loves-count action-<?php echo $that->image->doilove ? 'unlove' : 'love';?> id-<?php echo $that->image->id; ?>"><span><?php rc_e('ROKGALLERY_DETAIL_LOVES_COUNT');?>: </span><span class="rg-item-loves-counter id-<?php echo $that->image->id; ?>"><?php echo $that->image->loves;?></span> <?php if ($that->image->doilove){?> <span class="action-text id-<?php echo $that->image->id;?>"><?php echo $that->unlove_text;?></span>!<?php }else{?> <span class="action-text id-<?php echo $that->image->id;?>"><?php echo $that->love_text;?></span><?php }?></span><?php endif;?>
					<?php if ($that->show_tags_count):?><span class="rg-detail-item-tags-count"><span><?php rc_e('ROKGALLERY_DETAIL_TAGS_COUNT');?>: </span><strong><?php echo count($that->image->tags);?></strong></span><?php endif;?>
				</div>
				<?php endif;?>
				<?php if ($that->show_download_full):?>
				<div class="rg-detail-item-file-imageurl"><a href="<?php echo $that->image->fullimageurl;?>" class="readon"><span><?php rc_e('ROKGALLERY_DETAIL_DOWNLOAD');?></span></a></div>
				<?php endif;?>
			</div>
			<div class="rg-detail-file-info">
				<?php if ($that->show_title):?><span class="rg-detail-item-title"><?php rc_e('ROKGALLERY_DETAIL_ADD_INFO');?></span><?php endif;?>
				<?php if ($that->show_filesize):?><span class="rg-detail-item-file-filesize"><span><?php rc_e('ROKGALLERY_DETAIL_FILE_SIZE');?>: </span><?php echo $that->image->filesize;?></span><?php endif;?>
				<?php if ($that->show_dimensions):?><span class="rg-detail-item-file-dimensions"><span><?php rc_e('ROKGALLERY_DETAIL_FILE_DIMENSIONS');?>: </span><?php echo $that->image->xsize;?>x<?php echo $that->image->ysize;?></span><?php endif;?>
				<?php if ($that->show_created_at):?><span class="rg-detail-item-file-created_at"><span><?php rc_e('ROKGALLERY_DETAIL_FILE_CREATED');?>: </span><?php echo $that->image->created_at;?></span><?php endif;?>
				<?php if ($that->show_updated_at):?><span class="rg-detail-item-file-updated_at"><span><?php rc_e('ROKGALLERY_DETAIL_FILE_UPDATED');?>: </span><?php echo $that->image->updated_at;?></span><?php endif;?>
			</div>
		</div>
	</div>
</div>
<?php echo RokCommon_Composite::get($that->context)->load('pagination.php', array('that' => $that));?>