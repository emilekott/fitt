<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
echo "window.addEvent('domready', function(){ 
	new RokGallery.Slideshow('rg-".$passed_params->moduleid."', {
		animation: '".$passed_params->animation_type."',
		duration: ".$passed_params->animation_duration.",
		autoplay: {
			enabled: ".$passed_params->autoplay_enabled.",
			delay: ".$passed_params->autoplay_delay."
		}
	}); 
});";