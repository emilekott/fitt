<?php if ($that->pages):?>
<div class="rg-view-pagination">
    <?php if ($that->prev_page):?>
	<a href="<?php echo $that->prev_page->link;?>" class="prev"><?php rc_e('ROKGALLERY_PAGINATION_PREV');?></a>
    <?php endif; ?>
	<ul class="rg-view-pagination-list">
        <?php foreach($that->pages as $page): ?>
		<li <?php if ($page->active):?>class="active"<?php endif;?>><a href="<?php echo $page->link;?>"><span><?php echo $page->page_num;?></span></a></li>
        <?php endforeach; ?>
	</ul>
    <?php if ($that->next_page):?>
	<a href="<?php echo $that->next_page->link;?>" class="next"><?php rc_e('ROKGALLERY_PAGINATION_NEXT');?></a>
    <?php endif; ?>
</div>
<?php endif;?>