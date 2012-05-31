<?php
/**
 * @version   1.6 October 30, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die();
/**
 * @package     gantry
 * @subpackage  admin.elements
 */

class RTConfigFieldHTML extends RokCommon_Config_Field {
	

    protected $type = 'html';
    protected $basetype = 'none';

	public function getInput(){
		global $gantry;
		
		$html = (string)$this->element->html;
				
		
		return "<div class='html'>".$html."</div>";
	}
	
	public function getLabel(){
        return "";
    }
	
}