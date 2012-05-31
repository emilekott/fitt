<?php
/**
 * @version   1.6 October 30, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;


class RTConfigGroupColumns extends RokCommon_Config_Group
{
    protected $type = 'columns';
    protected $baseetype = 'group';

    public function getInput(){
        $buffer = '';
		
		$class = $this->element['class'];
		$name = $this->id;
		
		$buffer .= "<div class=\"wrapper ".$class."\">\n";
		
		// Columns
		$leftOpen = "<div class='group-left'>\n";
		$rightOpen = "<div class='group-right'>\n";
		$noneOpen = "<div class='group-none'>\n";
		
		$divClose = "</div>\n";
		
        foreach ($this->fields as $field) {

			$position = ($field->element['position']) ? (string) $field->element['position'] : 'none';
			$position .= "Open";
			$bufferItem = "";

			$fieldName = $this->fieldname."-".$field->element['name'];
			
			$bufferItem .= "<div class=\"group ".$fieldName." group-".$field->type."\">\n";
            if ($field->show_label) $bufferItem .= "<span class=\"group-label\">".$field->getLabel()."</span>\n";
            $bufferItem .= $field->getInput();
            $bufferItem .= "</div>\n";
			
			$$position .= $bufferItem;
        }

		$buffer .= $leftOpen . $divClose . $rightOpen . $divClose . $noneOpen . $divClose;
		$buffer .= "</div>\n";

        return $buffer;

    }
}