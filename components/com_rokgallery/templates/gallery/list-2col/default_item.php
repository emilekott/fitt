<?php
 /**
  * @version   $Id: default_item.php 39530 2011-07-05 19:21:53Z kevin $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
?>
<div class="rg-list">
    <div class="rg-block">
        <div class="rg-list-thumb">
            <a  <?php echo $that->image->rel;?> href="<?php echo $that->image->link;?>" class="rg-list-thumb-link"><img src="<?php echo $that->image->thumburl;?>" alt="image" class="rg-list-thumb-img"/></a>
            <?php if (($that->show_views) or ($that->show_loves) or ($that->show_tags_count)):?>
            <div class="gallery-data">
            	<?php if ($that->show_tags_count):?><span class="tags-count"><?php echo count($that->image->tags);?></span><?php endif;?>
            	<?php if ($that->show_loves):?><span class="loves-count action-<?php echo ($that->image->doilove) ? 'unlove' : 'love'; ?> id-<?php echo $that->image->id; ?>"><span class="rg-item-loves-counter id-<?php echo $that->image->id; ?>"><?php echo $that->image->loves;?></span></span><?php endif;?>
            	<?php if ($that->show_views):?><span class="views-count"><?php echo $that->image->views;?></span><?php endif;?>
            </div>
            <?php endif;?>
        </div>
        <div class="rg-list-info">
	        <?php if ($that->show_title):?><span class="item-title"><?php echo $that->image->title;?></span><?php endif;?>
	        <?php if ($that->show_created_at):?><span class="creation-date"><span><?php rc_e('ROKGALLERY_LIST_CREATED');?>:</span> <?php echo $that->image->created_at;?></span><?php endif;?>
	        <?php if ($that->show_tags):?>
	        <div class="item-tags">
		        <?php foreach($that->image->tags as $tag):?>
		        <span class="tag"><?php echo $tag;?></span>
		        <?php endforeach; ?>
		    </div>
		    <?php endif;?>
	        <?php if ($that->show_caption):?><span class="item-file-desc"><?php echo $that->image->caption;?></span><?php endif;?>
	     </div>
    </div>
</div>