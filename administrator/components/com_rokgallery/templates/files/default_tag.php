<?php
/**
  * @version   $Id: default_tag.php 39355 2011-07-02 18:33:01Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
?>
<div id="mass-file-tags">
	<div class="mass-tags-inputs">
		<div class="galleries-list">
			<span class="title"><?php rc_e('ROKGALLERY_SELECT_A_GALLERY');?></span><span class="arrow">&#9660;</span>
			<div class="galleries-dropdown">
				<ul>
					<li data-tags=""><span><?php rc_e('ROKGALLERY_NO_GALLERY');?></span></li>
                    <?php foreach($galleries as $gallery): ?>
					<li data-id="<?php echo $gallery->id;?>" data-tags="<?php echo implode(', ', $gallery->filetags);?>"><span><?php echo $gallery->name;?></span></li>
                    <?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div class="selected-files"><span>0</span> files selected.</div>
		<div class="clr"></div>
		<p>
			<label for="mass-tags-list" class="gallery-label">Tags</label>
			<input type="text" placeholder="e.g. travel, europe" class="gallery-input" id="mass-tags-list" />
		</p>
		<p>
			<label for="mass-tags-action" class="gallery-label">Action to perform</label>
			<label class="gallery-minilabel">
				<input type="radio" class="gallery-radio" name="mass-tags-action" value="add" checked="checked" />
				Add
			</label>
			<label class="gallery-minilabel">
				<input type="radio" class="gallery-radio" name="mass-tags-action" value="remove" />
				Remove
			</label>
		</p>
		<div class="clr"></div>
	</div>
</div>