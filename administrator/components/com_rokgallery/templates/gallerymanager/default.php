<?php
/**
  * @version   $Id: default.php 39578 2011-07-06 11:24:25Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

// 
//$this->assign('default_name', $default_name);
//$this->assign('force_fixed_size', $force_fixed_size);
//
?>
<div id="popup">

<div class="content">
<div class="galleries-header">
	<input type="hidden" id="load-gallery" value="<?php echo $that->current_gallery_id;?>" />
	<input type="hidden" id="fixed-gallery" value="<?php echo $that->force_fixed_size;?>" />
	<div class="left">
		<div class="galleries-list">
			<span class="title"><?php rc_e('ROKGALLERY_SELECT_A_GALLERY');?></span><span class="arrow">&#9660;</span>
			<div class="galleries-dropdown">
				<ul>
					<li><span><?php rc_e('ROKGALLERY_CREATE_NEW');?></span></li>
                    <?php foreach($that->galleries as $gallery): ?>
					<li data-id="<?php echo $gallery->id;?>"><span><?php echo $gallery->name;?></span></li>
                    <?php endforeach; ?>
				</ul>
			</div>
		</div>	
	</div>
	<div class="right">
		<button class="button base-on-gallery"><span><?php rc_e('ROKGALLERY_NEW_BASED_ON');?></span></button>
	</div>
	
	<div class="clr"></div>
</div>
<div class="galleries-content-container">
	<div class="galleries-mini-thumbs">
		<div class="mini-thumbs-loading"></div>
		<div class="mini-thumbs-header"><?php rc_e('ROKGALLERY_DRAG_TO_REORDER'); ?></div>
		<div class="mini-thumbs-list">
		</div>
		<div class="mini-thumbs-footer">
			<button class="button ok" style="display: block;"><?php rc_e('ROKGALLERY_APPLY'); ?></button>
			<button class="button cancel" style="display: block;"><?php rc_e('ROKGALLERY_CLOSE'); ?></button>
		</div>
	</div>
	<div class="galleries-inputs">
		<p>
			<label class="gallery-label" for="gallery-name"><?php rc_e('ROKGALLERY_NAME');?></label>
			<input type="text" class="gallery-input" id="gallery-name" value="<?php echo $that->default_name; ?>"/>
		</p>
		
		<p>
			<label class="gallery-label" for="gallery-filetags"><?php rc_e('ROKGALLERY_TAGS');?></label>
			<input type="text" class="gallery-input" id="gallery-filetags" placeholder="<?php rc_e('ROKGALLERY_EG');?>"/>
		</p>
		
		<p class="left" style="margin-right: 55px !important;">
			<label class="gallery-label" for="gallery-width"><?php rc_e('ROKGALLERY_IMAGES');?></label>
			
			<span class="separator block-title"><?php rc_e('ROKGALLERY_SIZE');?></span>
			<input class="gallery-input" id="gallery-width" placeholder="900" />
			<span class="separator">x</span>
			<input class="gallery-input" id="gallery-height" placeholder="500" />
			<span class="separator">px</span>
			
			<label class="gallery-minilabel">
				<input type="checkbox" class="gallery-checkbox" id="gallery-keep_aspect" /> <?php rc_e('ROKGALLERY_KEEP_ASPECT_RATIO');?>
			</label>
			<label class="gallery-minilabel">
				<input type="checkbox" class="gallery-checkbox" id="gallery-force_image_size" /> <?php rc_e('ROKGALLERY_FORCE_IMAGE_SIZE');?>
			</label>
		</p>
		
		<p class="left">
			<label class="gallery-label" for="gallery-thumb_xsize"><?php rc_e('ROKGALLERY_THUMBNAILS_SIZE');?></label>
			<span class="separator block-title"><?php rc_e('ROKGALLERY_SIZE');?></span>
			<input class="gallery-input" id="gallery-thumb_xsize" placeholder="190" />
			<span class="separator">x</span>
			<input class="gallery-input" id="gallery-thumb_ysize" placeholder="150" />
			<span class="separator">px</span>
			
			<span class="separator block-title"><?php rc_e('ROKGALLERY_BACKGROUND_COLOR');?></span>
			<input type="text" class="gallery-input" id="gallery-thumb_background" placeholder="#f9f9f9 / transparent"/>
			
			<span class="separator block-title"><?php rc_e('ROKGALLERY_ASPECT_RATIO');?></span>
			<label class="gallery-minilabel">
				<input type="checkbox" class="gallery-checkbox" id="gallery-thumb_keep_aspect" /> <?php rc_e('ROKGALLERY_KEEP_ASPECT_RATIO');?>
			</label>
		</p>
		
		<div class="clr"></div>
		
		<p style="margin-top: 20px;">
			<label class="gallery-label"><?php rc_e('ROKGALLERY_ADVANCED_OPTIONS');?></label>
			
			<div class="manual-order-wrapper">
				<button class="button manual-order-gallery"><span><?php rc_e('ROKGALLERY_MANUAL_ORDER');?></span></button>
				<label class="gallery-minilabel gallery-ordering-label">
					<?php rc_e('ROKGALLERY_MANUAL_ORDER_DESC');?>
				</label>

				<div class="clr"></div>
			</div>

			<button class="button publish-gallery"><span><?php rc_e('ROKGALLERY_PUBLISH');?></span></button>
			<label class="gallery-minilabel publish-label">
				<input type="checkbox" class="gallery-checkbox" id="gallery-auto_publish" <?php echo (RokGallery_Config::DEFAULT_GALLERY_AUTOPUBLISH) ? 'checked="checked"' : ''; ?> /> <?php rc_e('ROKGALLERY_AUTOMATICALLY_DESC');?>
			</label>
		</p>
		
		<div class="clr"></div>
		
	</div>
</div>


<div class="clr"></div>
</div>
<div class="statusbar">
	<div class="wrapper">
		<button class="button ok" style="display: block; "><?php rc_e('ROKGALLERY_SAVE'); ?></button>
		<button class="button cancel" style="display: block; "><?php rc_e('ROKGALLERY_CLOSE'); ?></button>
	</div>
	<div class="loading" style="display: none; "></div>
	<div class="galleries-info"></div><div class="clr"></div>
</div>
		
		
</div>