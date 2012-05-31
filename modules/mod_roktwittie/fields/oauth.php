<?php
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

// no direct access
defined('_JEXEC') or die();

class JFormFieldOauth extends JFormFieldRadio
{
	public function getInput()
	{
		$html = '<p>Enabling this requires registering your website as Twitter application, more about it <a href="http://www.rockettheme.com/extensions-joomla/roktwittie#registration" target="_blank">here</a>.</p>';

		$html .= parent::getInput();

		return $html;
	}
}