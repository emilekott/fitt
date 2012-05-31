<?php
 /**
  * @version   $Id: default.php 39601 2011-07-06 20:00:59Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
?>
<div id="rg-<?php echo $passed_params->moduleid; ?>" class="rokgallery-wrapper">
	<div class="rg-gm-container cols<?php echo $passed_params->columns; ?>">
		<div class="rg-gm-slice-container">
			<ul class="rg-gm-slice-list">
                <?php $i=1; foreach ($passed_params->slices as $slice):
                $slice_title = ($slice->title)?$slice->title:'';
                $slice_caption = ($slice->caption)?$slice->caption:'';?>
				<li>
					<div class="rg-gm-slice-item">
		                <div class="rg-gm-slice">	               
		                	<?php if ($passed_params->link!='none'):?>
			                	<a <?php echo $slice->rel;?> href="<?php echo $slice->link;?>">
			                <?php endif;?>
		                		<img title="<?php echo $slice_title;?>" alt="<?php echo $slice_title;?>" src="<?php echo $slice->thumburl;?>" width="<?php echo $passed_params->thumb_width;?>" height="<?php echo $passed_params->thumb_height;?>"/>
			                <?php if ($passed_params->link!='none'):?>
		                		</a>
		                	<?php endif;?>
		                </div>
		                <?php if (($passed_params->title)||($passed_params->caption)):?>
		                <div class="rg-gm-info">
			                <?php if ($passed_params->title):?>
			                	<span class="rg-gm-title"><?php echo $slice_title;?></span>
			                <?php endif;?>
			                <?php if ($passed_params->caption):?>
			                	<span class="rg-gm-caption"><?php echo $slice_caption;?></span>
			                <?php endif;?>
		                </div>
		                <?php endif;?>
		        	</div>
                </li>
			<?php if($i==count($passed_params->slices)):?> 
			</ul> 
			<?php elseif(($i % $passed_params->columns)==0):?> 
			</ul><ul class="rg-gm-slice-list">
			<?php endif; $i++;?>
            <?php endforeach; ?>            
    	</div>
	</div>
</div>