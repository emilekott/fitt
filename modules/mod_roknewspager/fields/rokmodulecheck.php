<?php
/**
 * RokModule Check, Custom Param
 *
 * @package RocketTheme
 * @subpackage roknewspager.elements
 * @version   1.5 November 11, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
// no direct access
defined('_JEXEC') or die();

/**
 * @package RocketTheme
 * @subpackage roknewspager.elements
 */
class JFormFieldRokmodulecheck extends JFormField
{
    /**
     * @param  $name
     * @param  $value
     * @param  $node
     * @param  $control_name
     * @return string containing the rendered html for the element
     */
	public function getInput()
	{
		$document 	=& JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true) . '/modules/mod_roknewspager/assets/admin.css');
		$rokmodule = JPATH_SITE.DS.'components'.DS.'com_rokmodule'.DS.'rokmodule.php';
		$warning_style = "style='background: #FFF3A3;border: 1px solid #E7BD72;color: #B79000;display: block;padding: 8px 10px;'";
		$success_style = "style='background: #d2edc9;border: 1px solid #90e772;color: #2b7312;display: block;padding: 8px 10px;'";
		
		if (file_exists($rokmodule)) {
			return "<span $success_style>You successfully passed the RokModule check.</span>";
		}
		else {
			return "<span $warning_style>You failed the RokModule check. In order to properly use this module, it is necessary that you install the latest RokModule version. Please <a target='_blank' href='http://www.rockettheme.com/extensions-downloads/free/1012-rokmodule'>click here</a> to download it.</span>";
		}
	}
	
}

?>