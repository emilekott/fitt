<?php
/**
 * @version   1.6 October 30, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKCOMMON') or die;


class RTConfigFieldCheckbox extends RokCommon_Config_Field
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'checkbox';
    protected $basetype = 'checkbox';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	public function getInput()
	{
		// Initialize some field attributes.
		$class		= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$checked	= ((string) $this->element['value'] == $this->value) ? ' checked="checked"' : '';

		// Initialize JavaScript field attributes.
		$onclick	= $this->element['onclick'] ? ' onclick="'.(string) $this->element['onclick'].'"' : '';

		return '<input type="checkbox" name="'.$this->name.'" id="'.$this->id.'"' .
				' value="'.htmlspecialchars((string) $this->element['value'], ENT_COMPAT, 'UTF-8').'"' .
				$class.$checked.$disabled.$onclick.'/>';
	}
}
