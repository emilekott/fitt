<?php
/**
  * @version   $Id: default.php 39778 2011-07-08 00:02:29Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
?>
<div id="rokgallery">
	<div id="header">
		<div class="left">
			<div class="filtering">
				<div class="query">
					<label for="filter-query"><?php rc_e('ROKGALLERY_FILTER');?></label>
					<div data-key="type" class="filter-types filters-list">
						<span class="title"><?php rc_e('ROKGALLERY_SELECT_TYPE');?></span><span class="arrow">&#9660;</span>
						<div class="filters-types-list filters-dropdown">
							<ul>
								<li data-key="title" data-ignore-list="gallery" data-ignores="greater_than,lesser_than" data-input="true"><span><?php rc_e('ROKGALLERY_TITLE');?></span></li>
								<li data-key="tags" data-ignore-list="gallery" data-ignores="greater_than,lesser_than"data-input="true"><span><?php rc_e('ROKGALLERY_TAGS');?></span></li>
								<li data-key="gallery" data-ignores="contains,contains_not,is_not,greater_than,lesser_than" data-input="false"><span><?php rc_e('ROKGALLERY_GALLERY');?></span></li>
								<li data-key="published" data-ignore-list="gallery" data-ignores="greater_than,contains,contains_not,lesser_than" data-input="false"><span><?php rc_e('ROKGALLERY_PUBLISHED');?></span></li>
                                <li data-key="filesize" data-ignore-list="gallery" data-ignores="is_not,contains,contains_not" data-input="true"><span><?php rc_e('ROKGALLERY_DETAIL_FILE_SIZE');?></span></li>
                                <li data-key="xsize" data-ignore-list="gallery" data-ignores="is_not,contains,contains_not" data-input="true"><span><?php rc_e('ROKGALLERY_FILE_WIDTH');?></span></li>
                                <li data-key="ysize" data-ignore-list="gallery" data-ignores="is_not,contains,contains_not" data-input="true"><span><?php rc_e('ROKGALLERY_FILE_HEIGHT');?></span></li>

							</ul>
						</div>
					</div>
					<div data-key="operator" class="filter-operator filters-list">
						<span class="title"><?php rc_e('ROKGALLERY_SELECT_OPERATOR');?></span><span class="arrow">&#9660;</span>
						<div class="filters-operator-list filters-dropdown">
							<ul>
								<li data-key="is"><span><?php rc_e('ROKGALLERY_IS');?></span></li>
								<li data-key="is_not"><span><?php rc_e('ROKGALLERY_IS_NOT');?></span></li>
								<li data-key="contains"><span><?php rc_e('ROKGALLERY_CONTAINS');?></span></li>
								<li data-key="contains_not"><span><?php rc_e('ROKGALLERY_CONTAINS_NOT');?></span></li>
                                <li data-key="greater_than"><span><?php rc_e('ROKGALLERY_GREATER_THAN');?></span></li>
                                <li data-key="lesser_than"><span><?php rc_e('ROKGALLERY_LESSER_THAN');?></span></li>
							</ul>
						</div>
					</div>
					<div data-key="gallery" class="filter-gallery filters-list">
						<span class="title"><?php rc_e('ROKGALLERY_SELECT_GALLERY');?></span><span class="arrow">&#9660;</span>
						<div class="filters-gallery-list filters-dropdown">
							<ul>
                                <?php foreach($that->galleries as $gallery): ?>
								<li data-key="<?php echo $gallery->id;?>"><span><?php echo $gallery->name;?></span></li>
                                <?php endforeach; ?>
							</ul>
						</div>
					</div>
					<input id="filter-query" type="text" class="input" name="query" value="" />
					<!--<div data-key="order" class="filter-order filters-list">
						<span class="title"><?php rc_e('ROKGALLERY_SELECT_ORDER');?></span><span class="arrow">&#9660;</span>
						<div class="filters-order-list filters-dropdown">
							<ul>
								<li data-key="asc" data-independent="true"><span>&uarr; ascendent</span></li>
								<li data-key="desc" data-independent="true"><span>&darr; descendent</span></li>
							</ul>
						</div>
					</div>-->
					<div class="filter-submit button"><span><?php rc_e('ROKGALLERY_FILTER');?></span></div>
					
					<div data-key="order_by" class="filter-orderby filters-list">
						<span class="title"><?php rc_e('ROKGALLERY_SELECT_ORDERBY');?></span><span class="arrow">&#9660;</span>
						<div class="filters-orderby-list filters-dropdown">
							<ul>
								<li data-key="order-title"><span><?php rc_e('ROKGALLERY_TITLE');?></span></li>
								<li data-key="order-created_at"><span><?php rc_e('ROKGALLERY_CREATED_AT');?></span></li>
								<li data-key="order-updated_at"><span><?php rc_e('ROKGALLERY_UPDATED_AT');?></span></li>
								<li data-key="order-filesize"><span><?php rc_e('ROKGALLERY_FILE_SIZE');?></span></li>
								<li data-key="order-xsize"><span><?php rc_e('ROKGALLERY_FILE_WIDTH');?></span></li>
								<li data-key="order-ysize"><span><?php rc_e('ROKGALLERY_FILE_HEIGHT');?></span></li>
                                <li data-key="order-gallery_ordering"><span><?php rc_e('ROKGALLERY_GALLERY_ORDERING');?></span></li>
							</ul>
						</div>
					</div>
					<div data-key="order_direction" class="filter-orderdirection filters-list">
						<span class="title"><?php rc_e('ROKGALLERY_SELECT_ORDERDIRECTION');?></span><span class="arrow">&#9660;</span>
						<div class="filters-orderdirection-list filters-dropdown">
							<ul>
								<li data-key="order-asc"><span>&uarr; <?php rc_e('ROKGALLERY_ASCENDING');?></span></li>
								<li data-key="order-desc"><span>&darr; <?php rc_e('ROKGALLERY_DESCENDING');?></span></li>
							</ul>
						</div>
					</div>
					<div class="filter-sort button"><span><?php rc_e('ROKGALLERY_SORT');?></span></div>
					
					<div class="clr"></div>
				</div>
			</div>
		</div>
		<div class="right">
			<div class="total-count">
				<span class="total-selected"><span>0</span> <?php rc_e('ROKGALLERY_N_FILES_SELECTED');?> (<a class="total-select-all" href="#"><?php rc_e('ROKGALLERY_N_FILES_SELECTALL'); ?></a>) / </span>
				<span class="total-viewing"><span><?php echo $that->currently_shown_items; ?></span> of <span><?php echo $that->total_items_in_filter; ?></span> <?php rc_ne('ROKGALLERY_N_FILES_SHOWING', $that->total_items_in_filter);?></span> /
				<span class="total-files"><span><?php echo $that->totalFilesCount; ?></span> <?php rc_ne('ROKGALLERY_N_FILES_TOTAL', $that->totalFilesCount);?></span>
			</div>
		</div>
		<div class="filters-generated empty">
			<div class="filters-wrapper">
			</div>
			<div class="clr"></div>
		</div>
		<div class="clr"></div>
	</div>

	<div id="gallery-list">
		<?php
            $that->row_entry_number = 0;
            $that->item_number = 1;
            foreach($that->files as $that->file):
                echo RokCommon_Composite::get('com_rokgallery.default')->load('default_row.php', array('that'=>$that));
                $that->row_entry_number++;
                $that->item_number++;
			endforeach;
		?>
		
		<div class="clr"></div>
	</div>
	<div id="load-more"><span><span class="text">load more</span><span class="info">HOLD <strong>SHIFT</strong> KEY TO LOAD ALL</span></span></div>
</div>

<?php
	echo RokCommon_Composite::get('com_rokgallery.default')->load('default_settings.php', array('that'=>$that));
    echo RokCommon_Composite::get('com_rokgallery.default')->load('default_edit.php', array('that'=>$that));
?>

<div id="overlay"></div>
<div id="popup" class="popup">
	<div class="container">
		<div class="topbar">
			<span></span>
			<span class="icon"></span>
		</div>
		
		<div class="content">
		</div>
		
		<div class="statusbar">
			<div class="wrapper">
				<div class="button ok"><span></span></div>
				<div class="button cancel"><span></span></div>
			</div>
			<div class="loading"></div>
			<div class="clr"></div>
		</div>
	</div>
</div>