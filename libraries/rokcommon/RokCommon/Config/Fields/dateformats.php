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

require_once('list.php');

class RTConfigFieldDateFormats extends RTConfigFieldList {
    var $_name = 'DateFormats';

    protected $type = 'dateformats';
    protected $basetype = 'select';

    protected function getOptions() {
        $now = new RokCommon_Date();

        // Initialize variables.
        $options = array();
        $translation = $this->element['translation'] ? $this->element['translation'] : true;

        foreach ($this->element->children() as $option) {

            // Only add <option /> elements.
            if ($option->getName() != 'option') {
                continue;
            }


            // Create a new option object based on the <option /> element.
            $tmp = RokCommon_Config_HTML_Select::option((string) $option['value'], (string) $now->toFormat($option['value']), 'value', 'text', ((string) $option['disabled'] == 'true'));

            // Set some option attributes.
            $tmp->class = (string) $option['class'];

            // Set some JavaScript option attributes.
            $tmp->onclick = (string) $option['onclick'];

            // Add the option object to the result set.
            $options[] = $tmp;
        }
        reset($options);

        return $options;
    }
}

?>