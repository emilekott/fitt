<div class="rg-detail-pagination">
    <?php if ($that->prev_link):?>
	<a href="<?php echo $that->prev_link;?>" class="prev"><?php rc_e('ROKGALLERY_PAGINATION_PREV');?></a>
    <?php endif; ?>
    <?php if ($that->next_link):?>
	<a href="<?php echo$that->next_link;?>" class="next"><?php rc_e('ROKGALLERY_PAGINATION_NEXT');?></a>
    <?php endif; ?>
</div>
