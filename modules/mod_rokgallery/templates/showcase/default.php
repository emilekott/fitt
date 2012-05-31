<?php
 /**
  * @version   $Id: default.php 41647 2011-08-30 21:56:36Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

  $slice_0 = $passed_params->slices[0];
  $slices_size = array('width' => $slice_0->xsize, 'height' => $slice_0->ysize);
  $container_size = array(
	'width' => $slices_size['width'] + ($passed_params->showcase_imgpadding * 2) . 'px',
	'height' => $slices_size['height'] + ($passed_params->showcase_imgpadding * 2) . 'px'
  );

?>

<div id="rg-<?php echo $passed_params->moduleid; ?>" class="rg-sc layout-<?php echo $passed_params->showcase_image_position; ?>">
	<div class="rg-sc-main" style="height: <?php echo $container_size['height']; ?>;">
		<div class="rg-sc-slide" style="height: <?php echo $container_size['height']; ?>;">
			<div class="rg-sc-slice-container" style="width: <?php echo $container_size['width']; ?>;height: <?php echo $container_size['height']; ?>;">
				<div class="rg-sc-img-padding" style="padding: <?php echo $passed_params->showcase_imgpadding; ?>px;">
					<div class="rg-sc-img-list" style="width: <?php echo $slices_size['width']; ?>px;height: <?php echo $slices_size['height']; ?>px;">
						<?php foreach ($passed_params->slices as $slice): ?>
						<div class="rg-sc-slice">
						<?php if ($passed_params->link!='none'):?>
		                	<a <?php echo $slice->rel;?> href="<?php echo $slice->link;?>">
		                <?php endif;?>
		            		<img title="" alt="<?php echo $slice_title;?>" src="<?php echo $slice->imageurl;?>" width="<?php echo $passed_params->image_width;?>" height="<?php echo $passed_params->image_height;?>"/>
		                <?php if ($passed_params->link!='none'):?>
		            		</a>
		            	<?php endif;?>
		            	</div>
	    		    	<?php endforeach; ?> 
					</div>
				</div>
			</div>
			<?php if (($passed_params->title)||($passed_params->caption)): ?>
			<div class="rg-sc-content" style="margin-<?php echo $passed_params->showcase_image_position;?>: <?php echo $container_size['width']; ?>;">
				<?php foreach ($passed_params->slices as $slice): ?>
				<?php 
					$slice_title = ($slice->title)?$slice->title:'';
	            	$slice_caption = ($slice->caption)?$slice->caption:'';
				?>
				<div class="rg-sc-info">
					<?php if ($passed_params->title):?>
		            <h1 class="rg-sc-title"><span class="rg-sc-title-span"><?php echo $slice_title;?></span></h1>
		            <?php endif;?>


		            <?php if ($passed_params->caption):?>
		            <div class="rg-sc-desc-surround">
		            	<span class="rg-sc-caption"><?php echo $slice_caption;?></span>
		            </div>
		            <?php endif;?>
		         </div>
				<?php endforeach; ?>
			</div>
			<?php endif;?>
		</div>

		<?php if ($passed_params->autoplay_enabled == 2): ?>
		<div class="rg-sc-loader">
			<div class="rg-sc-progress"></div>
		</div>
		<?php endif; ?>

	</div>

	<?php if ($passed_params->showcase_arrows!='no'):?>
	<div class="rg-sc-controls <?php if ($passed_params->showcase_arrows=='onhover'):?>onhover<?php endif; ?>">
		<span class="prev"></span>
		<span class="next"></span>
	</div>
	<?php endif;?>

	<?php/*
	<?php if ($passed_params->showcase_navigation == 'thumbnails'): ?>
	<div class="rg-sc-navigation">
		<div class="rg-sc-thumbs">
  			<ul class="rg-sc-thumbs-list">
				<?php $i=1; foreach ($passed_params->slices as $slice):?>
					<li>
		        	<div class="rg-sc-thumb">
		        		<img title="<?php echo $slice->title;?>" alt="<?php echo $slice->title;?>" src="<?php echo $slice->thumburl;?>" width="<?php echo $passed_params->thumb_width;?>" height="<?php echo $passed_params->thumb_height;?>" />
					</div>
					<?php $i++;?>
					</li>
				<?php endforeach;?>
  			</ul>

		</div>
	</div>
	<?php endif; ?>
	<?php if ($passed_params->showcase_navigation == 'pagination'): ?>
	<div class="rg-sc-pagination">
  		<ul class="rg-sc-pagination-list">
  			<?php for ($i=1; $i <= count($passed_params->slices); $i++): ?>
  				<li<?php echo ($i == 1) ? ' class="active"' : '' ?>><span><?php echo $i; ?></span></li>
  			<?php endfor; ?>
  		</ul>
	</div>
	<?php endif; ?>
	*/ ?>
</div>