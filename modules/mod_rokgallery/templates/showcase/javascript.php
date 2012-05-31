<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
echo "window.addEvent('domready', function(){ 
	new RokGallery.Showcase('rg-".$passed_params->moduleid."', {
		animation: '".$passed_params->showcase_animation_type."',
		duration: ".$passed_params->showcase_animation_duration.",
		autoplay: {
			enabled: ".$passed_params->showcase_autoplay_enabled.",
			delay: ".$passed_params->showcase_autoplay_delay."
		},
		imgpadding: ".$passed_params->showcase_imgpadding.",
		captions:{
			fixedheight: ".$passed_params->showcase_fixedheight.",
			animated: ".$passed_params->showcase_animatedheight.",
			animation: '".$passed_params->showcase_captionsanimation."'
		}
	}); 
});";