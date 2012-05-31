<?php
/**
 * @package   Tachyon Template - RocketTheme
 * @version   1.5.2 November 11, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Rockettheme Tachyon Template uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');

class GantryFeatureModuleScroller extends GantryFeature {
    var $_feature_name = 'modulescroller';
	var $_list = array('content-top', 'content-bottom');

	function init(){
		global $gantry;
		
		$enabled = false;
		
		foreach($this->_list as $list){
			$enabled |= $gantry->get('scrolling'.$list.'-enabled');
		}
		
		if ($enabled) {
			$gantry->addScript('gantry-module-scroller.js');
			$gantry->addInlineScript('window.addEvent("domready", function() {
				'.$this->_initJS().'
			});');
		}
	}
	
	function _initJS(){
		global $gantry;
		$js = "";
		
		foreach($this->_list as $position){
			$enabled = $gantry->get('scrolling' . $position . '-enabled');
			$duration = $gantry->get('scrolling' . $position . '-duration');
			$animation = $gantry->get('scrolling' . $position . '-animation');
			$autoplay = $gantry->get('scrolling' . $position . '-autoplay');
			$delay = $gantry->get('scrolling' . $position . '-delay');
			
			if ($enabled){
				$js .= "new ScrollModules('rt-".$position."', {duration: ".$duration.", transition: Fx.Transitions.".$animation.", autoplay: ".$autoplay.", delay: ".$delay."});\n";
			}
		}
		
		return $js;
		
	}
}