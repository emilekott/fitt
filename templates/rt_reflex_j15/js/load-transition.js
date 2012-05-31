/**
 * @package		Gantry Template Framework - RocketTheme
 * @version		1.5.2 November 11, 2011
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license		http://www.rockettheme.com/legal/license.php RocketTheme Proprietary Use License
 */

((function(){

var animation = function(){
	var body = $('rt-container-content');
	
	if ((window.gecko && document.getElementsByClassName) || (window.trident && !document.querySelectorAll)){
		if (body){
			body.setStyles({'visibility': 'hidden', 'opacity': 0});
			body.removeClass('rt-hidden').effect('opacity', {duration: 800, transition: Fx.Transitions.Quad.easeOut}).start(1);
		}
		
		return;
	}
	
	if (body) body.removeClass('rt-hidden').addClass('rt-visible');
};

window.addEvent('load', animation);

})());
