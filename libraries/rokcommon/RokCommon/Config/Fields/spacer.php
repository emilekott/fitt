<?php
/**
 * @version   1.6 October 30, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

gantry_import('core.config.gantryformfield');


class RTConfigFieldSpacer extends RokCommon_Config_Field
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'spacer';
    protected $basetype = 'none';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	public function getInput()
	{
		return ' ';
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return	string	The field label markup.
	 * @since	1.6
	 */
	public function getLabel()
	{
		echo '<div class="clr"></div>';
		if ((string) $this->element['hr'] == 'true') {
			return '<hr />';
		}
		else {
			return parent::getLabel();
		}
		echo '<div class="clr"></div>';
	}

}