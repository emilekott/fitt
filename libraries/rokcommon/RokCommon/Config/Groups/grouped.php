<?php
/**
 * @version   1.6 October 30, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKCOMMON') or die;



class RTConfigGroupGrouped extends RokCommon_Config_Group
{
    protected $type = 'grouped';
    protected $baseetype = 'group';

    public function getInput(){
        $buffer = '';

		$buffer .= "<div class='wrapper'>";
        foreach ($this->fields as $field) {
            $buffer .= '<div class="gantry-field">';
            if ($field->show_label) $buffer .= $this->preLabel($field).$field->getLabel().$this->postLabel($field)."\n";
            $buffer .= $field->getInput();
            $buffer .= "<div class='clr'></div>\n";
            $buffer .= "</div>";

        }
		$buffer .= "</div>";
        return $buffer;
    }
}