<?php
/**
  * @version   $Id: default_edit.php 39428 2011-07-04 07:11:27Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
?>
<div id="file-edit">
	<div class="ribbon admin"></div>
	<div id="file-edit-wrapper">
		<div class="edit-header">
			<h1><?php rc_e('ROKGALLERY_SLICE_EDITOR');?></h1>
			<div class="panel-actions">
				<div class="loader"></div>
				<div class="file-edit-save ok button"><span><?php rc_e('ROKGALLERY_SAVE');?></span></div>
				<div class="file-edit-publish dark button"><span><span class="status">&#x25CF;</span> <span><?php rc_e('ROKGALLERY_PUBLISH');?></span></span></div>
				<div class="file-gallery dark button galleries-list">
					<span class="title"><?php rc_e('ROKGALLERY_SELECT_GALLERY');?></span><span class="arrow">&#9660;</span>
					<div class="file-gallery-list galleries-dropdown">
						<ul>
							<li data-key=""><span><em><?php rc_e('ROKGALLERY_NO_GALLERY');?></em></span></li>
                            <?php foreach($that->galleries as $gallery): ?>
							<li data-key="<?php echo $gallery->id;?>"><span><?php echo $gallery->name;?></span></li>
                            <?php endforeach; ?>
						</ul>
					</div>
				</div>
				<div class="file-edit-delete dark button"><span><?php rc_e('ROKGALLERY_DELETE');?></span></div>
				<div class="file-edit-close dark button"><span><?php rc_e('ROKGALLERY_CLOSE');?></span></div>
			</div>
			
			<div class="clr"></div>
		</div>
		
		<div class="info edit-block">
			<div class="column">
				<div class="title">
					<h1><?php rc_e('ROKGALLERY_TITLE');?></h1>
					<input type="text" value="" />
				</div>
				<div class="slug">
					<h1><?php rc_e('ROKGALLERY_SLUG');?></h1>
					<input type="text" value="" />
				</div>
				<div class="link">
					<h1><?php rc_e('ROKGALLERY_LINK');?></h1>
					<input id="slice-linkdata" type="text" value="" />
                    <input id="slice-link" type="hidden" value="" />
                    <a class="link-clear-input" href=""><span>x</span></a>
                    <a class="modal link-select-article" title="Select or Change Article" href="index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_jform_request_id" rel="{handler: 'iframe', size: {x: 800, y: 450}}"><span><?php rc_e('ROKGALLERY_SELECT_ARTICLE');?></span></a>
				</div>
				<div class="clr"></div>
			</div>
			<div class="column">
				<div class="caption">
					<h1><?php rc_e('ROKGALLERY_CAPTION');?></h1>
					<textarea></textarea>
				</div>
				
				<div class="clr"></div>
			</div>
			<div class="column tags">
				<h1><?php rc_e('ROKGALLERY_TAGS');?></h1>
				<div class="tags-wrapper">
					<div class="tags-add">
						<input type="text" class="add-input" placeholder="<?php rc_e('ROKGALLERY_EG');?>" />
						<a class="add-tag" href="#"><span><?php rc_e('ROKGALLERY_ADD');?></span></a>
					</div>
					<div class="tags-list">
						<span class="oops"><?php rc_e('ROKGALLERY_OOPS');?></span>
					</div>
				</div>
			</div>
			<div class="column thumb">
				<div class="thumb-size">
					<h1>Thumbnail <sup><a id="reset-thumb-sizes" href="#">(defaults)</a></sup></h1>
					<span class="column-title">Thumb Size</span>
					<input name="thumb_xsize" type="text" value="<?php echo (RokGallery_Config::DEFAULT_DEFAULT_THUMB_XSIZE); ?>" placeholder="width" /> x <input name="thumb_ysize" type="text" value="<?php echo (RokGallery_Config::DEFAULT_DEFAULT_THUMB_YSIZE); ?>" placeholder="height" />
					<div class="thumb-aspect">
						<label>Keep Aspect <input name="thumb-aspect-ratio" class="checkbox" type="checkbox" value="" <?php echo (RokGallery_Config::DEFAULT_DEFAULT_THUMB_KEEP_ASPECT) ? 'checked="checked"' : ''; ?>/></label>
					</div>
				</div>
				<div class="thumb-background">
					<span class="column-title">Background</span>
					<input name="thumb_background" type="text" value="<?php echo (RokGallery_Config::DEFAULT_DEFAULT_THUMB_BACKGROUND); ?>" placeholder="#000 / transparent" />
				</div>

				<div class="clr"></div>
			</div>
			<div class="clr"></div>
		</div>
	
		<div class="edit-image">
			<div class="image-infos">
				<div class="left">
					<div class="toolbar">
						<div class="push-button dark handtool first"><span><span><?php rc_e('ROKGALLERY_HAND_TOOL');?></span></span></div>
						<div class="push-button dark marquee"><span><span><?php rc_e('ROKGALLERY_MARQUEE_TOOL');?></span></span></div>
						<div class="zoom-wrapper">
							<div class="push-button dark zoomin"><span><span><?php rc_e('ROKGALLERY_ZOOMIN');?></span></span></div>
							<div class="push-button dark zoomout last"><span><span><?php rc_e('ROKGALLERY_ZOOMOUT');?></span></span></div>
							<div class="push-button dark zoom100 last"><span>1 : 1</span></div>
						</div>
					</div>
					<div class="resize">
						<div class="marquee">
							<span><?php rc_e('ROKGALLERY_MARQUEE');?>:</span>
							<span class="separator">x:</span>
							<input type="text" class="input" />
							<span class="separator">y:</span>
							<input type="text" class="input" />
							<span class="separator">w:</span>
							<input type="text" class="input" />
							<span class="separator">h:</span>
							<input type="text" class="input" />
						</div>
						<div class="size">
							<span>Size:</span>
							<input type="text" class="input" />
							<span class="separator">x</span>
							<input type="text" class="input" />
							<div class="size-slider">
								<div class="size-slider-knob"></div>
							</div>
							<div class="file-edit-apply ok button"><span><?php rc_e('ROKGALLERY_APPLY');?></span></div>
							<div class="file-edit-revert dark button"><span><?php rc_e('ROKGALLERY_REVERT');?></span></div>
							<div class="locked"></div>
						</div>
					</div>
				</div>
				<div class="right"></div>
			</div>
			<div class="image-wrapper">
				<div class="image-overlay"></div>
			</div>
			<div class="image-status">
				<div class="left"></div>
				<div class="right">
					<div class="scale"><?php rc_e('ROKGALLERY_SCALE');?>: <span>100%</span></div>
					<div class="navigation">
						<span class="separator"> &#9679; </span>
						<div class="previous-slice">&#9664;</div>
						<span class="slice-current-no">1</span>
						<span class="separator"> / </span>
						<span class="slice-total-no">10</span>
						<div class="next-slice">&#9654;</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>