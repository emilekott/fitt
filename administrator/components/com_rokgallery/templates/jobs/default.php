<div class="jobs-batch">
	<div class="left">
		<div class="jobs-counter"><?php rc_e('ROKGALLERY_THERE_ARE_CURRENTLY');?> <span><?php echo $jobs->count(); ?></span> <?php rc_e('ROKGALLERY_JOBS');?></div>
	</div>
	
	<div class="right">
		<div class="button refresh"><span><?php rc_e('ROKGALLERY_REFRESH');?></span></div>
		<div class="button clean"><span><?php rc_e('ROKGALLERY_CLEAN');?></span></div>
		<div class="button wipeall"><span><?php rc_e('ROKGALLERY_WIPEALL');?></span></div>
	</div>
</div>

<div class="clr"></div>

<div class="jobs-wrapper" style="max-height: 205px; overflow: hidden; position: relative;">
	<div class="jobs-list">
		<?php
		    foreach($jobs as &$job){
                echo RokCommon_Composite::get('com_rokgallery.jobs')->load('default_single.php',array('job'=>$job));
            }
        ?>
	</div>
</div>

<div class="clr"></div>

<div id="jobs-wipe-warning">
	<p>The "Wipe All" operation is going to remove all the existing jobs from your database. All running Jobs will be terminated and deleted.</p>
	<p>Are you sure you want to continue? <div class="button wipe-yes ok" style="margin: 0 5px 0 0"><span><?php rc_e('ROKGALLERY_YES');?></span></div> <div class="button wipe-no" style="margin: 0 5px 0 0"><span><?php rc_e('ROKGALLERY_NO');?></span></div></p>
	
	<div class="clr"></div>
</div>