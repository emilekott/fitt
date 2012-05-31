<?php if (($that->show_sorts) or $that->show_available_layouts) : ?>
<div class="rg-view-header">
	<?php if ($that->show_sorts): ?>
    <div class="rg-sort">
        <span class="desc"><?php rc_e('ROKGALLERY_SORT_GALLERY_DESC');?>:</span>
        <ul class="rg-sort-list">
            <?php foreach($that->available_sorts as $sort_item):?>
            <li <?php if($that->sort_bys[$sort_item]->active):?>class="active"<?php endif;?>><a href="<?php echo $that->sort_bys[$sort_item]->link;?>"><?php echo $that->sort_bys[$sort_item]->label;?><span class="indicator"></span></a><?php if($that->sort_dir->field == $sort_item):?><a href="<?php echo $that->sort_dir->link;?>" class="sort-arrow <?php echo $that->sort_dir->class;?>"></a><?php endif; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <?php if ($that->show_available_layouts): ?>
    <div class="rg-view-selector">
        <span class="desc"><?php rc_e('ROKGALLERY_VIEW_SELECTOR_DESC');?>:</span>
        <ul class="rg-view-selector-list">
            <?php foreach($that->available_layouts as $layout_item): ?>
            <li class="<?php echo $that->layouts[$layout_item]->name;?><?php if($that->layouts[$layout_item]->active):?> active<?php endif;?>"><a href="<?php echo $that->layouts[$layout_item]->link;?>"></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <div class="clear"></div>
</div>
<?php endif; ?>