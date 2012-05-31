<?php
/**
  * @version   $Id: default.php 39784 2011-07-08 00:54:59Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

?>
<div id="rokgallerypicker">
	<input type="hidden" name="gallery_id" value="<?php echo $that->gallery_id; ?>" />
	<input type="hidden" name="file_id" value="<?php echo $that->file_id; ?>" />

	<div id="gallerypicker-header">
		<div class="gallerypicker-tabs-wrapper">
			<ul id="gallerypicker-tabs">
				<li class="active" data-panel="filelist" data-type="files"><span>Files</span></li>
				<li data-panel="gallerylist" data-type="gallery"><span>Galleries</span></li>
				<?php if ($that->show_menuitems): ?><li data-panel="menuitems" data-type="menuitems"><span>Menu Items</span></li><?php endif; ?>
			</ul>

			<div class="right">
				<button class="button back-button" style="display: block;"><span class="back-icon"></span><span style="float: right;display:block;line-height:1.5em;"><?php rc_e('ROKGALLERY_PICKER_BACK');?></span></button>
			</div>
			<div class="clr"></div>
		</div>
		<div class="clr"></div>
	</div>

	<div id="panels">
		<div class="panel filelist">
			<div class="instructions"><?php rc_e('ROKGALLERY_PICKER_INSTRUCTIONS', '<img src="components/com_rokgallery/assets/images/jinsert.png" width="16" height="16" alt="" title="" style="vertical-align:middle;" />'); ?></div>

			<ul id="gallerypicker-fileslist">
				<?php
					if (!$that->file_id){
			            foreach($that->files as $that->file):
			                echo RokCommon_Composite::get('com_rokgallery.gallerypicker')->load('default_file.php', array('that'=>$that));
						endforeach;
					} else {
						foreach($that->files->Slices as $that->slice):
							echo RokCommon_Composite::get('com_rokgallery.gallerypicker')->load('default_slices_view.php', array('that'=>$that));
						endforeach;
					}
				?>
			</ul>
			<div class="clr"></div>
			
			<div id="load-more"><span><span class="text">load more</span><span class="info">HOLD <strong>SHIFT</strong> KEY TO LOAD ALL</span></span></div>
		</div>

		<div class="panel gallerylist">
			<div class="instructions"><?php rc_e('ROKGALLERY_PICKER_INSTRUCTIONS', '<img src="components/com_rokgallery/assets/images/jinsert.png" width="16" height="16" alt="" title="" style="vertical-align:middle;" />'); ?></div>

			<ul id="gallerypicker-gallerylist">
				<?php
					if (!$that->gallery_id){
			            foreach($that->galleries as $that->gallery):
			                echo RokCommon_Composite::get('com_rokgallery.gallerypicker')->load('default_gallery.php', array('that'=>$that));
						endforeach;
					} else {
						foreach($that->galleries->Slices as $that->slice):
							echo RokCommon_Composite::get('com_rokgallery.gallerypicker')->load('default_slices_view.php', array('that'=>$that));
						endforeach;
					}
				?>
			</ul>
			<div class="clr"></div>
		</div>

		<?php if ($that->show_menuitems): ?>
		<div class="panel menuitems">
			<?php echo RokCommon_Composite::get('com_rokgallery.gallerypicker')->load('default_menuitems.php', array('that'=>$that)); ?>
			<div class="clr"></div>
		</div>
		<?php endif; ?>
	</div>
</div>

<div class="clr"></div>
