<?php
/**
  * @version   $Id: default_settings.php 39355 2011-07-02 18:33:01Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
?>
<div id="file-settings-loader">
	<span class="loader"><span></span></span>
</div>
<div id="file-settings">
	<div class="indicator"></div>
	<div class="file-settings-wrapper">
		<div class="info edit-block">
			<div class="title">
				<h1><?php rc_e('ROKGALLERY_TITLE');?> <span><?php rc_e('ROKGALLERY_SAVING');?></span></h1>
				<input type="text" value="" />
			</div>
			<div class="slug">
				<h1><?php rc_e('ROKGALLERY_SLUG');?> <span><?php rc_e('ROKGALLERY_SAVING');?></span></h1>
				<input type="text" value="" />
			</div>
			<div class="description">
				<h1><?php rc_e('ROKGALLERY_DESCRIPTION');?> <span><?php rc_e('ROKGALLERY_SAVING');?></span></h1>
				<textarea></textarea>
			</div>
		</div>
		<div class="tags edit-block">
			<h1>tags</h1>
			<div class="tags-add">
				<input type="text" class="add-input" placeholder="<?php rc_e('ROKGALLERY_EG');?>" />
				<a class="add-tag" href="#"><span><?php rc_e('ROKGALLERY_ADD');?></span></a>
			</div>
			<div class="tags-list">
				<span class="oops"><?php rc_e('ROKGALLERY_OOPS');?></span>
			</div>
		</div>
		<div class="slices edit-block">
			<h1><?php rc_e('ROKGALLERY_SLICES');?></h1>
			<div class="slices-wrapper">
				<div class="title"></div>
				<div class="image-wrapper">
					<div class="image">
						<div class="ribbon"></div>
					</div>
				</div>
				<div class="status">
					<div class="first-row">
						<div class="left">
							<div class="left">
								<span class="slice-width"></span>x<span class="slice-height"></span> / <span class="slice-size"></span>
							</div>
						</div>
						<div class="right">
							<div class="previous">&#x25C4;</div>
							<div class="count"><span>-</span> / <span>0</span></div>
							<div class="next">&#x25BA;</div>
						</div>
						<div class="clr"></div>
					</div>
					<div class="second-row">
						<div class="left">
							<span class="gallery"></span>
						</div>
						<div class="clr"></div>
					</div>
				</div>
			</div>
			<div class="slices-controls">
				<div class="slice-new dark button"><span><?php rc_e('ROKGALLERY_NEW');?></span></div>
				<div class="slice-edit dark button"><span><?php rc_e('ROKGALLERY_EDIT');?></span></div>
				<div class="slice-share dark button"><span><?php rc_e('ROKGALLERY_SHARE');?></span></div>
				
				<div class="slice-delete dark button"><span><?php rc_e('ROKGALLERY_DELETE');?></span></div>
				
				<div class="slice-publish publish-button" title="publish"><span class="icon"></span><span class="status">&#x25CF;</span></div>
			</div>
		</div>
		<div class="clr"></div>
		<div class="statusbar">
			<div class="statusbar-wrapper">
				<div class="editfile-loader"></div>
				<div class="editfile-save ok button"><span><?php rc_e('ROKGALLERY_SAVE');?></span></div>
				<div class="editfile-close dark button"><span><?php rc_e('ROKGALLERY_CLOSE');?></span></div>
				<span class="separator">&#9679;</span>
				<div class="editfile-publish dark button"><span><span class="status">&#x25CF;</span> <span></span></span></div>
				<div class="editfile-delete dark button"><span><?php rc_e('ROKGALLERY_DELETE');?></span></div>
			</div>
		</div>
		<div class="divider"></div>
		<div class="clr"></div>
	</div>
</div>