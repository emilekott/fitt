/**
 * RokTwittie Module
 *
 * @package RocketTheme
 * @subpackage roktwittie
 * @version   1.5 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

var OAuthToggle = {
	init: function() {
		var no = document.id('jform_params_use_oauth0'), yes = document.id('jform_params_use_oauth1');
		
		OAuthToggle.rows = no.getParent('.adminformlist').getElements('#jform_params_consumer_key, #jform_params_consumer_secret, #signin-key').getParent('li');
		[yes, no].each(function(radio, i) {
			radio.addEvent('click', function() {
				if (!i && radio.checked) OAuthToggle.show();
				if (i && radio.checked) OAuthToggle.hide();	
			});
		});
		
		if (no.checked) no.fireEvent('click');
		if (yes.checked) yes.fireEvent('click');
	},
	
	show: function() {
		OAuthToggle.rows.setStyle('display', 'inline');
	},
	
	hide: function() {
		OAuthToggle.rows.setStyle('display', 'none');		
	}
};

window.addEvent('domready', OAuthToggle.init);