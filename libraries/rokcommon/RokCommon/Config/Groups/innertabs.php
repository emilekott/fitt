<?php
/**
 * @version   1.6 October 30, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;



class RTConfigGroupInnerTabs extends RokCommon_Config_Group {

    protected $type = 'innertabs';
    protected $baseetype = 'group';

    public function getInput() {

        foreach ($this->fields as $field) {
            if ( is_subclass_of($field,'RokCommon_Config_Group'))
                $field->setLabelWrapperFunctions($this->prelabel_function, $this->postlabel_function);
        }

        $buffer = '';
        $buffer .= <<< EOS
<div>
	<div class="inner-tabs">
		<ul>
EOS;
        $i = 0;
        foreach ($this->fields as $field) {
            $classes = '';
            if (!$i) $classes .= "first active";
            if ($i == count($this->fields) - 1) $classes .= 'last';
            $buffer .= '<li class="' . $classes . '"><span>' . rc__($field->getLabel()) . '</span></li>'."\n";
            $i++;
        }
        $buffer .= <<< EOS
        </ul>
    </div>
    <div class="inner-panels">
EOS;
		$i = 0;
        foreach ($this->fields as $field) {
			$i++;
            $buffer .=  '<div class="inner-panel inner-panel-'.$i.'">'."\n";
            $buffer .= $field->getInput();
            $buffer .= '</div>'."\n";
        }
        $buffer .= <<< EOS
	</div>
</div>
EOS;
        return $buffer;
    }
}
